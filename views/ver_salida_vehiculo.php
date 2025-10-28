<?php
require_once '../config/db.php';
require_once '../models/SaliVehi.php';
require_once '../models/SaliRepue.php';
require_once '../models/OrdTrabj.php';
require_once '../models/Alert.php';
require_once '../models/Repor.php';

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Initialize database connection
$db = conectarDB();

// Obtener el ID de la salida de vehículo
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: salida_vehiculo.php');
    exit();
}

// Obtener información completa de la salida de vehículo
$query = "SELECT sv.*, 
                 rv.placa, rv.marca_vehiculo, rv.modelo, rv.num_cha, rv.color, rv.cilindraje, rv.cap_carga,
                 rv.tecnomecanica, rv.soat, rv.tipo_unidad, rv.tipo_combustible,
                 ot.nombre_trabajo, ot.descripcion as orden_descripcion, ot.estado as orden_estado, 
                 ot.prioridad as orden_prioridad, ot.fecha_creacion as orden_fecha_creacion,
                 ot.fecha_estimada as orden_fecha_estimada,
                 a.descripcion as alerta_descripcion, a.tipo_alerta, a.prioridad as alerta_prioridad, 
                 a.estado as alerta_estado, a.fecha_hora as alerta_fecha, a.posicion_llanta,
                 a.codigo_conductor, a.observaciones as alerta_observaciones,
                 r.nombre_reporte, r.tipo_reporte, r.costo_individual_vehiculo, r.frecuencia,
                 r.fecha_creacion as reporte_fecha_creacion, r.activo as reporte_activo,
                 sr.fecha_salida as repuesto_fecha, sr.cantidad as repuesto_cantidad,
                 rep.nombre as repuesto_nombre, rep.marca_repuesto, rep.modelo as repuesto_modelo,
                 rep.pre_unitario, rep.costo_total,
                 c.cargo as conductor_cargo, c.horas_trabajadas, c.tareas_completadas, c.efeciencia,
                 c.descripcion as conductor_descripcion,
                 u.nombre as usuario_nombre, u.apellido as usuario_apellido, u.correo as usuario_correo
          FROM sali_vehi sv 
          LEFT JOIN regis_vehic rv ON sv.id_flotas = rv.id 
          LEFT JOIN ord_trabj ot ON sv.ord_trabj_id = ot.id
          LEFT JOIN alert a ON sv.alerta_id = a.id
          LEFT JOIN repor r ON sv.repor_id = r.id
          LEFT JOIN sali_repue sr ON sv.sali_repue_id = sr.id
          LEFT JOIN repue rep ON sr.repue_id = rep.id
          LEFT JOIN cond c ON rv.cond_id = c.id
          LEFT JOIN users u ON ot.users_id = u.id
          WHERE sv.id = ?";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$salida = mysqli_fetch_assoc($result);

if (!$salida) {
    header('Location: salida_vehiculo.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Salida de Vehículo #<?= $salida['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%);
            color: #374151;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            min-height: 100vh;
        }
        h1, h2, h3, h4, h5, h6 {
            color: #1E3A8A;
            font-weight: 600;
        }
        .main-header {
            background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(30, 58, 138, 0.2);
            color: #FFFFFF;
            margin-bottom: 2rem;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        .main-header h1 {
            color: #fff;
            margin-bottom: 0.5rem;
        }
        .card {
            background: #FFFFFF;
            border: 1px solid rgba(209, 213, 219, 0.3);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .card-header {
            background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%);
            border-bottom: 1px solid #D1D5DB;
            color: #1E3A8A;
            font-weight: 600;
            padding: 1.25rem;
        }
        .card-body {
            padding: 1.5rem;
        }
        .btn {
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            min-height: 44px;
            padding: 0.75rem 1.5rem;
            position: relative;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
            color: #1E3A8A !important;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            box-shadow: 0 6px 20px rgba(251, 191, 36, 0.4);
            color: #1E3A8A !important;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #FFFFFF;
            border: 2px solid #1E3A8A;
            color: #1E3A8A !important;
        }
        .btn-secondary:hover {
            background: #1E3A8A;
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }
        .badge {
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }
        .badge.bg-success {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important;
        }
        .badge.bg-warning {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%) !important;
            color: #1E3A8A !important;
        }
        .badge.bg-danger {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%) !important;
        }
        .badge.bg-info {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%) !important;
        }
        .badge.bg-secondary {
            background: linear-gradient(135deg, #6B7280 0%, #4B5563 100%) !important;
        }
        .info-row {
            border-bottom: 1px solid #E5E7EB;
            padding: 0.75rem 0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #6B7280;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        .info-value {
            color: #374151;
            font-weight: 400;
        }
        .section-icon {
            color: #FBBF24;
            margin-right: 0.5rem;
        }
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            .main-header {
                padding: 1.5rem;
                text-align: center;
            }
            .card-body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <!-- Header Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="salida_vehiculo.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver a Salidas
        </a>
        <?php if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] !== 'conductor'): ?>
        <a href="editar_salida_vehiculo.php?id=<?= $salida['id'] ?>" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Editar Salida
        </a>
        <?php endif; ?>
    </div>

    <!-- Main Header -->
    <div class="main-header">
        <h1><i class="fas fa-truck-moving me-3"></i>Salida de Vehículo #<?= $salida['id'] ?></h1>
        <p class="lead mb-0">Información detallada de la salida de vehículo registrada</p>
    </div>

    <div class="row">
        <!-- Información del Vehículo -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-truck section-icon"></i>Información del Vehículo
                </div>
                <div class="card-body">
                    <?php if ($salida['placa']): ?>
                    <div class="info-row">
                        <div class="info-label">Placa</div>
                        <div class="info-value"><strong><?= htmlspecialchars($salida['placa']) ?></strong></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Marca y Modelo</div>
                        <div class="info-value"><?= htmlspecialchars($salida['marca_vehiculo']) ?> <?= htmlspecialchars($salida['modelo']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Número de Chasis</div>
                        <div class="info-value"><?= htmlspecialchars($salida['num_cha']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Color</div>
                        <div class="info-value"><?= htmlspecialchars($salida['color']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Cilindraje</div>
                        <div class="info-value"><?= htmlspecialchars($salida['cilindraje']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Capacidad de Carga</div>
                        <div class="info-value"><?= htmlspecialchars($salida['cap_carga']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Tipo de Combustible</div>
                        <div class="info-value"><?= htmlspecialchars($salida['tipo_combustible']) ?></div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Vehículo no encontrado (ID: <?= $salida['id_flotas'] ?>)
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Información de Control -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-cogs section-icon"></i>Información de Control
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="info-label">Seguimiento y Monitoreo</div>
                        <div class="info-value"><?= htmlspecialchars($salida['segui_monitoreo']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Control de Combustible</div>
                        <div class="info-value"><?= htmlspecialchars($salida['control_combustible']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Cumplimiento de Regulaciones</div>
                        <div class="info-value"><?= htmlspecialchars($salida['cump_regulaciones']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Protocolos de Seguridad</div>
                        <div class="info-value"><?= htmlspecialchars($salida['protocolo_seguridad']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Gestión de Conductores</div>
                        <div class="info-value"><?= htmlspecialchars($salida['gest_conductores']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Orden de Trabajo -->
        <?php if ($salida['ord_trabj_id']): ?>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clipboard-list section-icon"></i>Orden de Trabajo
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="info-label">ID de Orden</div>
                        <div class="info-value"><strong>#<?= $salida['ord_trabj_id'] ?></strong></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Nombre del Trabajo</div>
                        <div class="info-value"><?= htmlspecialchars($salida['nombre_trabajo'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Descripción</div>
                        <div class="info-value"><?= htmlspecialchars($salida['orden_descripcion'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Estado</div>
                        <div class="info-value">
                            <?php if ($salida['orden_estado']): ?>
                                <span class="badge <?= 
                                    $salida['orden_estado'] == 'resuelta' ? 'bg-success' : 
                                    ($salida['orden_estado'] == 'en_proceso' ? 'bg-warning' : 'bg-danger') 
                                ?>">
                                    <?= ucfirst(str_replace('_', ' ', $salida['orden_estado'])) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Prioridad</div>
                        <div class="info-value">
                            <?php if ($salida['orden_prioridad']): ?>
                                <span class="badge <?= 
                                    $salida['orden_prioridad'] == 'alta' ? 'bg-danger' : 
                                    ($salida['orden_prioridad'] == 'media' ? 'bg-warning' : 'bg-info') 
                                ?>">
                                    <?= ucfirst($salida['orden_prioridad']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($salida['orden_fecha_creacion']): ?>
                    <div class="info-row">
                        <div class="info-label">Fecha de Creación</div>
                        <div class="info-value"><?= date('d/m/Y H:i', strtotime($salida['orden_fecha_creacion'])) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($salida['usuario_nombre']): ?>
                    <div class="info-row">
                        <div class="info-label">Responsable</div>
                        <div class="info-value"><?= htmlspecialchars($salida['usuario_nombre'] . ' ' . $salida['usuario_apellido']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Alerta -->
        <?php if ($salida['alerta_id']): ?>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-exclamation-triangle section-icon"></i>Alerta Asociada
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="info-label">ID de Alerta</div>
                        <div class="info-value"><strong>#<?= $salida['alerta_id'] ?></strong></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Descripción</div>
                        <div class="info-value"><?= htmlspecialchars($salida['alerta_descripcion'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Tipo de Alerta</div>
                        <div class="info-value"><?= htmlspecialchars($salida['tipo_alerta'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Prioridad</div>
                        <div class="info-value">
                            <?php if ($salida['alerta_prioridad']): ?>
                                <span class="badge <?= 
                                    $salida['alerta_prioridad'] == 'alta' ? 'bg-danger' : 
                                    ($salida['alerta_prioridad'] == 'media' ? 'bg-warning' : 'bg-info') 
                                ?>">
                                    <?= ucfirst($salida['alerta_prioridad']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Estado</div>
                        <div class="info-value">
                            <?php if ($salida['alerta_estado']): ?>
                                <span class="badge <?= 
                                    $salida['alerta_estado'] == 'resuelta' ? 'bg-success' : 'bg-warning'
                                ?>">
                                    <?= ucfirst($salida['alerta_estado']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($salida['alerta_fecha']): ?>
                    <div class="info-row">
                        <div class="info-label">Fecha y Hora</div>
                        <div class="info-value"><?= date('d/m/Y H:i', strtotime($salida['alerta_fecha'])) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($salida['posicion_llanta']): ?>
                    <div class="info-row">
                        <div class="info-label">Posición de Llanta</div>
                        <div class="info-value"><?= htmlspecialchars($salida['posicion_llanta']) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($salida['alerta_observaciones']): ?>
                    <div class="info-row">
                        <div class="info-label">Observaciones</div>
                        <div class="info-value"><?= htmlspecialchars($salida['alerta_observaciones']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="row">
        <!-- Salida de Repuesto -->
        <?php if ($salida['sali_repue_id']): ?>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-box section-icon"></i>Salida de Repuesto
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="info-label">ID de Salida</div>
                        <div class="info-value"><strong>#<?= $salida['sali_repue_id'] ?></strong></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Repuesto</div>
                        <div class="info-value"><?= htmlspecialchars($salida['repuesto_nombre'] ?? 'N/A') ?></div>
                    </div>
                    <?php if ($salida['marca_repuesto']): ?>
                    <div class="info-row">
                        <div class="info-label">Marca</div>
                        <div class="info-value"><?= htmlspecialchars($salida['marca_repuesto']) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($salida['repuesto_modelo']): ?>
                    <div class="info-row">
                        <div class="info-label">Modelo</div>
                        <div class="info-value"><?= htmlspecialchars($salida['repuesto_modelo']) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <div class="info-label">Cantidad</div>
                        <div class="info-value">
                            <span class="badge bg-secondary"><?= $salida['repuesto_cantidad'] ?? 'N/A' ?></span>
                        </div>
                    </div>
                    <?php if ($salida['repuesto_fecha']): ?>
                    <div class="info-row">
                        <div class="info-label">Fecha de Salida</div>
                        <div class="info-value"><?= date('d/m/Y', strtotime($salida['repuesto_fecha'])) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($salida['pre_unitario']): ?>
                    <div class="info-row">
                        <div class="info-label">Precio Unitario</div>
                        <div class="info-value">$<?= number_format($salida['pre_unitario'], 2) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($salida['costo_total']): ?>
                    <div class="info-row">
                        <div class="info-label">Costo Total</div>
                        <div class="info-value"><strong>$<?= number_format($salida['costo_total'], 2) ?></strong></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Reporte -->
        <?php if ($salida['repor_id']): ?>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar section-icon"></i>Reporte Asociado
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="info-label">ID de Reporte</div>
                        <div class="info-value"><strong>#<?= $salida['repor_id'] ?></strong></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Nombre del Reporte</div>
                        <div class="info-value"><?= htmlspecialchars($salida['nombre_reporte'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Tipo de Reporte</div>
                        <div class="info-value"><?= htmlspecialchars($salida['tipo_reporte'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Frecuencia</div>
                        <div class="info-value"><?= htmlspecialchars($salida['frecuencia'] ?? 'N/A') ?></div>
                    </div>
                    <?php if ($salida['reporte_fecha_creacion']): ?>
                    <div class="info-row">
                        <div class="info-label">Fecha de Creación</div>
                        <div class="info-value"><?= date('d/m/Y', strtotime($salida['reporte_fecha_creacion'])) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <div class="info-label">Estado</div>
                        <div class="info-value">
                            <span class="badge <?= $salida['reporte_activo'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $salida['reporte_activo'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </div>
                    </div>
                    <?php if ($salida['costo_individual_vehiculo'] > 0): ?>
                    <div class="info-row">
                        <div class="info-label">Costo Individual</div>
                        <div class="info-value">$<?= number_format($salida['costo_individual_vehiculo'], 2) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Información del Conductor (si existe) -->
    <?php if ($salida['conductor_cargo']): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user section-icon"></i>Información del Conductor
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-label">Cargo</div>
                            <div class="info-value"><?= htmlspecialchars($salida['conductor_cargo']) ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Horas Trabajadas</div>
                            <div class="info-value"><?= $salida['horas_trabajadas'] ?> hrs</div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Tareas Completadas</div>
                            <div class="info-value"><?= $salida['tareas_completadas'] ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Eficiencia</div>
                            <div class="info-value">
                                <span class="badge <?= $salida['efeciencia'] >= 90 ? 'bg-success' : ($salida['efeciencia'] >= 75 ? 'bg-warning' : 'bg-danger') ?>">
                                    <?= $salida['efeciencia'] ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php if ($salida['conductor_descripcion']): ?>
                    <div class="info-row mt-3">
                        <div class="info-label">Descripción</div>
                        <div class="info-value"><?= htmlspecialchars($salida['conductor_descripcion']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Botones de Acción -->
    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="salida_vehiculo.php" class="btn btn-secondary me-2">
                <i class="fas fa-list me-2"></i>Volver a la Lista
            </a>
            <?php if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] !== 'conductor'): ?>
            <a href="editar_salida_vehiculo.php?id=<?= $salida['id'] ?>" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Editar Salida
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>