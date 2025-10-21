<?php
require_once '../config/db.php';
require_once '../models/SaliRepue.php';
require_once '../models/SaliVehi.php';
require_once '../models/OrdTrabj.php';
require_once '../models/Alert.php';
require_once '../models/Repor.php';

// Initialize database connection
$db = conectarDB();

$sali_repue_id = isset($_GET['sali_repue_id']) ? $_GET['sali_repue_id'] : null;
$sali_repue = $sali_repue_id ? (new SaliRepue($db))->getById($sali_repue_id) : null;
$ordenes = (new OrdTrabj($db))->getAll();
$alertas = (new Alert($db))->getAll();
$reportes = (new Repor($db))->getAll();
session_start();
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Salida de Vehículo</title>
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
        h2, h4, h5, h6 {
            color: #1E3A8A;
            font-weight: 600;
        }
        .container-fluid {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem;
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
        .main-header h2 {
            color: #fff;
            margin-bottom: 0.5rem;
        }
        .main-header .lead {
            font-size: 1.1rem;
            opacity: 0.9;
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
        .btn-outline-primary, .btn-secondary {
            background: #FFFFFF;
            border: 2px solid #1E3A8A;
            color: #1E3A8A !important;
        }
        .btn-outline-primary:hover, .btn-secondary:hover {
            background: #1E3A8A;
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }
        .btn-warning {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            color: #1E3A8A !important;
        }
        .btn-danger {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            color: #FFFFFF !important;
        }
        .form-control, .form-select {
            background: #FFFFFF;
            border: 2px solid #D1D5DB;
            border-radius: 8px;
            color: #374151;
            font-size: 16px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1E3A8A;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
            outline: none;
        }
        .form-label {
            color: #1E3A8A;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .form-section {
            background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%);
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }
        .form-section h6 {
            border-bottom: 2px solid #FBBF24;
            color: #1E3A8A;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
        }
        .table-responsive {
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        .table thead th {
            background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);
            border: none;
            color: #FFFFFF;
            font-weight: 600;
            padding: 1rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .table tbody td {
            border-bottom: 1px solid #E5E7EB;
            color: #374151;
            padding: 1rem;
            vertical-align: middle;
        }
        .table-hover tbody tr:hover {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.05) 0%, rgba(30, 58, 138, 0.05) 100%);
        }
        .badge {
            border-radius: 20px;
            font-size: 0.8rem;
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
        .alert {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        .alert-info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
            color: #2563EB;
        }
        @media (max-width: 768px) {
            .container-fluid {
                padding: 1rem;
            }
            .main-header {
                padding: 1.5rem;
                text-align: center;
            }
            .card-body {
                padding: 1rem;
            }
            .btn {
                font-size: 16px;
                min-height: 44px;
                width: 100%;
            }
            .btn + .btn {
                margin-top: 0.5rem;
            }
            .table-responsive {
                font-size: 14px;
            }
            .form-section {
                padding: 1rem;
            }
        }
        @media (max-width: 576px) {
            .container-fluid {
                padding: 0.5rem;
            }
            h2 {
                font-size: 1.5rem;
            }
            .main-header {
                padding: 1rem;
            }
            .table thead th,
            .table tbody td {
                font-size: 12px;
                padding: 0.5rem;
            }
            .btn {
                padding: 0.75rem 1rem;
            }
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="mb-3 d-flex justify-content-end">
        <a href="dashboard.php" class="btn btn-secondary">Volver al dashboard</a>
    </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Salidas de Vehículo</h2>
                <?php if (!$rol_conductor): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistrarSalida">
                        <i class="fas fa-plus-circle me-1"></i> Registrar Salida de Vehículo
                </button>
                <?php endif; ?>
        </div>

        <!-- Modal Registrar Salida de Vehículo -->
        <div class="modal fade" id="modalRegistrarSalida" tabindex="-1" aria-labelledby="modalRegistrarSalidaLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalRegistrarSalidaLabel"><i class="fas fa-truck-moving me-2"></i>Registrar Salida de Vehículo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form action="../controllers/SaliVehiController.php?action=registrar" method="POST">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="sali_repue_id" class="form-label">ID Salida de Repuesto</label>
                                    <select class="form-select" name="sali_repue_id" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ((new SaliRepue($db))->getAll() as $sr): ?>
                                            <option value="<?= $sr['id'] ?>">#<?= $sr['id'] ?> - <?= $sr['fecha_salida'] ?> (<?= $sr['cantidad'] ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="ord_trabj_id" class="form-label">ID Orden de Trabajo</label>
                                    <select class="form-select" name="ord_trabj_id" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($ordenes as $o): ?>
                                            <option value="<?= $o['id'] ?>">#<?= $o['id'] ?> - <?= $o['nombre_trabajo'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="alerta_id" class="form-label">ID Alerta</label>
                                    <select class="form-select" name="alerta_id" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($alertas as $a): ?>
                                            <option value="<?= $a['id'] ?>">#<?= $a['id'] ?> - <?= $a['descripcion'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="id_flotas" class="form-label">Vehículo de la Flota</label>
                                    <select class="form-select" name="id_flotas" required>
                                        <option value="">Seleccione un vehículo...</option>
                                        <?php 
                                        $query_vehiculos = "SELECT id, placa, marca_vehiculo, modelo, num_cha FROM regis_vehic ORDER BY placa";
                                        $stmt_vehiculos = mysqli_query($db, $query_vehiculos);
                                        while ($vehiculo = mysqli_fetch_assoc($stmt_vehiculos)): 
                                        ?>
                                            <option value="<?= $vehiculo['id'] ?>">
                                                <?= htmlspecialchars($vehiculo['placa']) ?> - <?= htmlspecialchars($vehiculo['marca_vehiculo']) ?> <?= htmlspecialchars($vehiculo['modelo']) ?> (ID: <?= $vehiculo['id'] ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="segui_monitoreo" class="form-label">Seguimiento y Monitoreo</label>
                                    <input type="text" class="form-control" name="segui_monitoreo" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="control_combustible" class="form-label">Control y Nivel de Combustible</label>
                                    <input type="text" class="form-control" name="control_combustible" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="cump_regulaciones" class="form-label">Cumplimiento de Regulaciones</label>
                                    <input type="text" class="form-control" name="cump_regulaciones" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="protocolo_seguridad" class="form-label">Protocolos de Seguridad</label>
                                    <input type="text" class="form-control" name="protocolo_seguridad" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="gest_conductores" class="form-label">Gestión y Datos de Conductores</label>
                                    <input type="text" class="form-control" name="gest_conductores" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Registrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <hr>
    <form class="row g-3 mb-3" method="get" action="">
        <div class="col-md-2">
            <input type="number" name="filtro_id" class="form-control" placeholder="ID Salida" value="<?= isset($_GET['filtro_id']) ? htmlspecialchars($_GET['filtro_id']) : '' ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="filtro_placa" class="form-control" placeholder="Placa Vehículo" value="<?= isset($_GET['filtro_placa']) ? htmlspecialchars($_GET['filtro_placa']) : '' ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="filtro_orden" class="form-control" placeholder="ID Orden" value="<?= isset($_GET['filtro_orden']) ? htmlspecialchars($_GET['filtro_orden']) : '' ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="filtro_alerta" class="form-control" placeholder="ID Alerta" value="<?= isset($_GET['filtro_alerta']) ? htmlspecialchars($_GET['filtro_alerta']) : '' ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
        <div class="col-md-2">
            <a href="salida_vehiculo.php" class="btn btn-secondary w-100">Limpiar</a>
        </div>
    </form>
    <h4 class="mt-4">Salidas de Vehículos Registradas</h4>
    <?php
    // Obtener salidas de vehículo con información del vehículo relacionado
    $query_salidas = "SELECT sv.*, rv.placa, rv.marca_vehiculo, rv.modelo 
                      FROM sali_vehi sv 
                      LEFT JOIN regis_vehic rv ON sv.id_flotas = rv.id 
                      ORDER BY sv.id DESC";
    $result_salidas = mysqli_query($db, $query_salidas);
    $salidas_vehiculo = [];
    while ($row = mysqli_fetch_assoc($result_salidas)) {
        $salidas_vehiculo[] = $row;
    }
    
    // Filtros
    if (isset($_GET['filtro_id']) && $_GET['filtro_id'] !== '') {
        $salidas_vehiculo = array_filter($salidas_vehiculo, function($sv) {
            return $sv['id'] == $_GET['filtro_id'];
        });
    }
    if (isset($_GET['filtro_placa']) && $_GET['filtro_placa'] !== '') {
        $salidas_vehiculo = array_filter($salidas_vehiculo, function($sv) {
            return stripos($sv['placa'], $_GET['filtro_placa']) !== false;
        });
    }
    if (isset($_GET['filtro_orden']) && $_GET['filtro_orden'] !== '') {
        $salidas_vehiculo = array_filter($salidas_vehiculo, function($sv) {
            return $sv['ord_trabj_id'] == $_GET['filtro_orden'];
        });
    }
    if (isset($_GET['filtro_alerta']) && $_GET['filtro_alerta'] !== '') {
        $salidas_vehiculo = array_filter($salidas_vehiculo, function($sv) {
            return $sv['alerta_id'] == $_GET['filtro_alerta'];
        });
    }
    if (count($salidas_vehiculo) > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Vehículo</th>
                    <th>Monitoreo</th>
                    <th>Combustible</th>
                    <th>Regulaciones</th>
                    <th>Seguridad</th>
                    <th>Conductores</th>
                    <th>Orden Trabajo</th>
                    <th>Alerta</th>
                    <th>Reporte</th>
                    <th>Salida Repuesto</th>
                    <th style="min-width:120px">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($salidas_vehiculo as $sv): ?>
                <tr>
                    <td><?= $sv['id'] ?></td>
                    <td>
                        <?php if ($sv['placa']): ?>
                            <strong><?= htmlspecialchars($sv['placa']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($sv['marca_vehiculo']) ?> <?= htmlspecialchars($sv['modelo']) ?></small><br>
                            <small class="text-muted">ID: <?= $sv['id_flotas'] ?></small>
                        <?php else: ?>
                            <span class="text-danger">Vehículo no encontrado (ID: <?= $sv['id_flotas'] ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($sv['segui_monitoreo']) ?></td>
                    <td><?= htmlspecialchars($sv['control_combustible']) ?></td>
                    <td><?= htmlspecialchars($sv['cump_regulaciones']) ?></td>
                    <td><?= htmlspecialchars($sv['protocolo_seguridad']) ?></td>
                    <td><?= htmlspecialchars($sv['gest_conductores']) ?></td>
                    <td><?= $sv['ord_trabj_id'] ?></td>
                    <td><?= $sv['alerta_id'] ?></td>
                    <td><?= $sv['repor_id'] ?></td>
                    <td><?= $sv['sali_repue_id'] ?></td>
                    <td class="text-center">
                        <?php if (!$rol_conductor): ?>
                        <a href="editar_salida_vehiculo.php?id=<?= $sv['id'] ?>" class="btn btn-sm btn-warning me-1">Editar</a>
                        <a href="eliminar_salida_vehiculo.php?id=<?= $sv['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar esta salida de vehículo?');">Eliminar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="alert alert-info">No hay salidas de vehículos registradas.</div>
    <?php endif; ?>
</div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</html>

