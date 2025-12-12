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
        :root{
            --bg-primary: #0F172A;
            --card-bg: #111827;
            --card-radius: 12px;
            --text-primary: #F1F5F9;
            --text-secondary: #94A3B8;
            --border: #1E293B;
            --accent: #F97316;
            --accent-amber: #F59E0B;
            --danger: #EF4444;
            --success: #10B981;
        }

        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            min-height: 100vh;
        }

        .container-fluid {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Sidebar visual igual al dashboard */
        .sidebar {
            background: var(--card-bg);
            color: var(--text-primary);
            border-right: 1px solid var(--border);
            box-shadow: 4px 0 20px rgba(2,6,23,0.3);
            height: 100vh;
            min-width: 220px;
            max-width: 340px;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 1050;
            border-radius: 0 1rem 1rem 0;
            transform: translateX(-100%);
            transition: transform 0.25s;
        }

        .sidebar.show-mobile {
            transform: translateX(0) !important;
        }

        .sidebar .nav-link {
            color: var(--text-primary) !important;
            font-weight: 500;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            background: rgba(249,115,22,0.1);
            color: var(--accent) !important;
        }

        .sidebar .nav-link.active, .sidebar .nav-link.bg-primary, .sidebar .nav-link.text-white {
            background: var(--accent);
            color: #FFFFFF !important;
            font-weight: 600;
        }

        .sidebar .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
        }

        .sidebar .btn-outline-secondary {
            color: var(--text-primary);
            border-color: var(--text-secondary);
        }

        .sidebar .btn-outline-secondary:hover {
            background: var(--text-secondary);
            color: var(--bg-primary);
        }

        .sidebar h5 {
            color: var(--text-primary);
            font-weight: 700;
        }

        .sidebar .nav-link.text-danger {
            color: var(--danger) !important;
        }

        .sidebar .nav-link.text-danger:hover {
            background: rgba(239,68,68,0.1);
            color: var(--danger) !important;
        }

        @media (max-width: 1024px) {
            .sidebar {
                position: fixed !important;
                top: 0; left: 0; bottom: 0;
                width: 85vw;
                max-width: 340px;
                height: 100vh;
                z-index: 1050;
                border-radius: 0 1rem 1rem 0;
                box-shadow: 0 8px 32px rgba(0,0,0,0.4);
                transform: translateX(-100%);
                transition: transform 0.25s;
            }
            .sidebar.show-mobile {
                transform: translateX(0);
            }
        }

        .main-header {
            background: linear-gradient(135deg, rgba(15,23,42,0.9) 0%, rgba(17,24,39,0.85) 100%);
            border-radius: var(--card-radius);
            box-shadow: 0 18px 50px rgba(2,6,23,0.6);
            color: var(--text-primary);
            margin-bottom: 2rem;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .main-header h1 {
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .main-header .lead {
            font-size: 1.1rem;
            opacity: 0.9;
            color: var(--text-secondary);
        }

        .card, .form-section {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid var(--border);
            border-radius: var(--card-radius);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .card:hover, .form-section:hover {
            box-shadow: 0 8px 32px rgba(249, 115, 22, 0.15);
            transform: translateY(-2px);
            border-color: var(--accent);
        }

        .card-header {
            background: var(--accent);
            color: #fff;
            font-weight: bold;
            padding: 1.25rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-label {
            color: #000;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            background: #FFFFFF;
            border: 2px solid #D1D5DB;
            border-radius: 8px;
            color: #000;
            font-size: 16px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
            outline: none;
        }

        .form-section {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid var(--border);
            border-radius: var(--card-radius);
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }

        .form-section h6 {
            border-bottom: 2px solid var(--accent);
            color: #000;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
        }

        .btn {
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            min-height: 44px;
            padding: 0.75rem 1.5rem;
            position: relative;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background: #E65100;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .btn-outline-primary {
            background: #FFFFFF;
            border: 2px solid var(--accent);
            color: var(--accent) !important;
        }

        .btn-outline-primary:hover {
            background: var(--accent);
            color: #FFFFFF !important;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid var(--text-secondary);
            color: var(--text-secondary);
        }

        .btn-secondary:hover {
            background: var(--text-secondary);
            color: var(--bg-primary);
        }

        .btn-warning {
            background: var(--accent-amber);
            color: #FFFFFF !important;
        }

        .btn-danger {
            background: var(--danger);
            color: #FFFFFF !important;
        }

        .btn-info {
            background: var(--text-secondary);
            color: #FFFFFF !important;
        }

        .btn-info:hover {
            background: var(--accent);
            color: #FFFFFF !important;
        }

        .table-responsive {
            border-radius: var(--card-radius);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            overflow-y: hidden;
            max-width: 100%;
        }

        .table thead th {
            background: var(--accent);
            border: none;
            color: #FFFFFF;
            font-weight: 600;
            padding: 0.75rem;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
            min-width: 120px;
        }

        .table tbody td {
            border-bottom: 1px solid var(--border);
            color: #000;
            padding: 0.75rem;
            vertical-align: middle;
            white-space: nowrap;
            min-width: 120px;
        }

        .table tbody td.wrap-text {
            white-space: normal;
            max-width: 200px;
            word-wrap: break-word;
        }

        .table-hover tbody tr:hover {
            background: rgba(249, 115, 22, 0.05);
        }

        .badge {
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }

        .badge.bg-success {
            background: var(--success) !important;
        }

        .badge.bg-warning {
            background: var(--accent-amber) !important;
            color: #FFFFFF !important;
        }

        .badge.bg-danger {
            background: var(--danger) !important;
        }

        .badge.bg-info {
            background: var(--text-secondary) !important;
        }

        .badge.bg-secondary {
            background: #6B7280 !important;
        }

        .alert {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .alert-info {
            background: rgba(249, 115, 22, 0.1);
            color: var(--accent);
        }

        .btn-sm {
            font-size: 0.8rem;
            padding: 0.5rem 0.75rem;
            min-height: auto;
        }

        .d-flex.flex-column.gap-1 .btn + .btn {
            margin-top: 0.25rem;
        }

        /* Scroll horizontal personalizado */
        .table-container {
            position: relative;
        }

        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: var(--accent);
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #E65100;
        }

        .scroll-indicator {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            background: rgba(249, 115, 22, 0.8);
            color: white;
            padding: 0.5rem;
            border-radius: 50%;
            z-index: 5;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* Vista móvil para tabla */
        .mobile-card-view {
            display: none;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0.5rem !important;
            }
            .main-header {
                padding: 1rem;
                text-align: center;
            }
            .card-body {
                padding: 1rem;
            }

            /* Ocultar tabla en móvil y mostrar cards */
            .table-container {
                display: none;
            }
            .mobile-card-view {
                display: block;
            }

            /* Estilos para las cards móviles */
            .mobile-vehicle-card {
                background: rgba(255, 255, 255, 0.95);
                border: 1px solid var(--border);
                border-radius: var(--card-radius);
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
                margin-bottom: 1rem;
                overflow: hidden;
            }

            .mobile-card-header {
                background: var(--accent);
                color: white;
                padding: 1rem;
                font-weight: 600;
            }

            .mobile-card-body {
                padding: 1rem;
            }

            .mobile-info-row {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                padding: 0.5rem 0;
                border-bottom: 1px solid var(--border);
            }

            .mobile-info-row:last-child {
                border-bottom: none;
            }

            .mobile-info-label {
                font-weight: 500;
                color: var(--text-secondary);
                font-size: 0.85rem;
                flex: 0 0 40%;
            }

            .mobile-info-value {
                color: #000;
                font-size: 0.85rem;
                text-align: right;
                flex: 1;
                word-wrap: break-word;
            }

            .mobile-actions {
                padding: 1rem;
                background: rgba(255, 255, 255, 0.95);
                border-top: 1px solid var(--border);
            }

            .mobile-actions .btn {
                width: 100%;
                margin-bottom: 0.5rem;
                font-size: 0.85rem;
                padding: 0.6rem;
            }

            .mobile-actions .btn:last-child {
                margin-bottom: 0;
            }

            /* Ajustes generales para móvil */
            .btn {
                font-size: 14px;
                min-height: 40px;
            }

            .form-control, .form-select {
                font-size: 16px; /* Evita zoom en iOS */
            }

            /* Header responsive */
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }

            .d-flex.justify-content-between .btn {
                width: 100%;
            }

            /* Filtros responsivos */
            .row.g-3.mb-3 {
                margin: 0;
            }

            .row.g-3.mb-3 .col-md-2 {
                margin-bottom: 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 0.5rem !important;
            }
            h2, h4 {
                font-size: 1.25rem;
            }
            .btn {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }

            /* Cards móviles más compactas */
            .mobile-vehicle-card {
                margin-bottom: 0.75rem;
            }

            .mobile-card-header {
                padding: 0.75rem;
                font-size: 0.9rem;
            }

            .mobile-card-body {
                padding: 0.75rem;
            }

            .mobile-info-row {
                padding: 0.4rem 0;
            }

            .mobile-info-label, .mobile-info-value {
                font-size: 0.8rem;
            }

            .mobile-actions {
                padding: 0.75rem;
            }

            .mobile-actions .btn {
                padding: 0.5rem;
                font-size: 0.8rem;
                margin-bottom: 0.4rem;
            }

            /* Filtros más compactos */
            .card-body {
                padding: 0.75rem;
            }

            .form-label {
                font-size: 0.85rem;
                margin-bottom: 0.25rem;
            }

            .form-control {
                padding: 0.5rem 0.75rem;
                font-size: 14px;
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-slide-up {
            animation: slideInUp 0.6s ease-out;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        .animate-fade-in {
            animation: fadeIn 0.4s ease-out;
        }

        .info-row {
            border-bottom: 1px solid var(--border);
            padding: 0.75rem 0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: var(--text-secondary);
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        .info-value {
            color: #000;
            font-weight: 400;
        }
        .section-icon {
            color: var(--accent);
            margin-right: 0.5rem;
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