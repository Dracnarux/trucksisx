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

// Obtener lista de vehículos registrados para el filtro
$vehiculos_query = "SELECT id, placa, marca_vehiculo, modelo, estado FROM regis_vehic ORDER BY placa ASC";
$vehiculos_result = $db->query($vehiculos_query);
$vehiculos_registrados = [];
if ($vehiculos_result && $vehiculos_result->num_rows > 0) {
    while ($vehiculo = $vehiculos_result->fetch_assoc()) {
        $vehiculos_registrados[] = $vehiculo;
    }
}

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

        .main-header h2 {
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
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="main-header animate-fade-in mb-4">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
            <div>
                <h1 class="mb-2"><i class="bi bi-truck-front text-warning"></i> Salida de Vehículos</h1>
                <span class="lead">Registro y gestión de salidas de vehículos del sistema</span>
            </div>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <a href="dashboard.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
                <?php if (!$rol_conductor): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistrarSalida">
                    <i class="bi bi-plus-circle"></i> Registrar Salida
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

        <!-- Modal Registrar Salida de Vehículo -->
        <div class="modal fade" id="modalRegistrarSalida" tabindex="-1" aria-labelledby="modalRegistrarSalidaLabel" aria-modal="true" role="dialog">
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
                                    <label for="sali_repue_id" class="form-label">Salida de Repuesto</label>
                                    <select class="form-select" name="sali_repue_id" required>
                                        <option value="">Seleccione...</option>
                                        <?php 
                                        // Obtener salidas de repuesto con el nombre del repuesto ordenadas por ID
                                        $query_salidas = "SELECT sr.id, sr.fecha_salida, sr.cantidad, r.nombre as repuesto_nombre 
                                                          FROM sali_repue sr 
                                                          LEFT JOIN repue r ON sr.repue_id = r.id 
                                                          ORDER BY sr.id ASC";
                                        $result_salidas = $db->query($query_salidas);
                                        while ($sr = $result_salidas->fetch_assoc()): 
                                        ?>
                                            <option value="<?= $sr['id'] ?>">
                                                #<?= $sr['id'] ?> - <?= htmlspecialchars($sr['repuesto_nombre'] ?? 'Sin repuesto') ?> 
                                                (Cant: <?= $sr['cantidad'] ?>) - <?= date('d/m/Y', strtotime($sr['fecha_salida'])) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="ord_trabj_id" class="form-label">ID Orden de Trabajo</label>
                                    <select class="form-select" name="ord_trabj_id" required>
                                        <option value="">Seleccione...</option>
                                        <?php 
                                        // Ordenar órdenes de trabajo por ID ascendente
                                        usort($ordenes, function($a, $b) {
                                            return $a['id'] - $b['id'];
                                        });
                                        foreach ($ordenes as $o): 
                                        ?>
                                            <option value="<?= $o['id'] ?>">#<?= $o['id'] ?> - <?= $o['nombre_trabajo'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="alerta_id" class="form-label">ID Alerta</label>
                                    <select class="form-select" name="alerta_id" required>
                                        <option value="">Seleccione...</option>
                                        <?php 
                                        // Ordenar alertas por ID ascendente
                                        usort($alertas, function($a, $b) {
                                            return $a['id'] - $b['id'];
                                        });
                                        foreach ($alertas as $a): 
                                        ?>
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
                            
                            <!-- Panel de información autocompletada -->
                            <div id="info-vehiculo-autocompletado" class="mt-3" style="display: none;">
                                <div class="alert alert-success">
                                    <h6 class="mb-2"><i class="bi bi-check-circle"></i> Información Autocompletada del Vehículo</h6>
                                    <div class="row g-2 small">
                                        <div class="col-md-6" id="info-placa-container" style="display: none;">
                                            <strong>🚛 Placa:</strong> <span id="info-placa"></span>
                                        </div>
                                        <div class="col-md-6" id="info-vehiculo-marca-container" style="display: none;">
                                            <strong>🔧 Vehículo:</strong> <span id="info-vehiculo-marca"></span>
                                        </div>
                                        <div class="col-md-6" id="info-conductor-vehiculo-container" style="display: none;">
                                            <strong>👤 Conductor:</strong> <span id="info-conductor-vehiculo"></span>
                                        </div>
                                        <div class="col-md-6" id="info-alerta-vehiculo-container" style="display: none;">
                                            <strong>⚠️ Alerta Activa:</strong> <span id="info-alerta-vehiculo"></span>
                                        </div>
                                        <div class="col-md-6" id="info-orden-vehiculo-container" style="display: none;">
                                            <strong>📋 Orden de Trabajo:</strong> <span id="info-orden-vehiculo"></span>
                                        </div>
                                        <div class="col-md-6" id="info-repuesto-vehiculo-container" style="display: none;">
                                            <strong>🔩 Repuesto:</strong> <span id="info-repuesto-vehiculo"></span>
                                        </div>
                                    </div>
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
    <div class="card mb-3">
        <div class="card-header">
            <i class="fas fa-filter me-2"></i>Filtros de Búsqueda
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Mejora:</strong> Ahora puedes seleccionar vehículos directamente desde la lista de vehículos registrados.
            </div>
            <form class="row g-3" method="get" action="">
                <div class="col-md-3 col-6">
                    <label class="form-label">ID Salida</label>
                    <input type="number" name="filtro_id" class="form-control" placeholder="Ej: 123" value="<?= isset($_GET['filtro_id']) ? htmlspecialchars($_GET['filtro_id']) : '' ?>">
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label">
                        Vehículo 
                        <span class="badge bg-secondary ms-1"><?= count($vehiculos_registrados) ?> registrados</span>
                    </label>
                    <select name="filtro_placa" class="form-select">
                        <option value="">📋 Todos los vehículos</option>
                        <?php foreach ($vehiculos_registrados as $vehiculo): ?>
                            <option value="<?= htmlspecialchars($vehiculo['placa']) ?>" 
                                    <?= (isset($_GET['filtro_placa']) && $_GET['filtro_placa'] === $vehiculo['placa']) ? 'selected' : '' ?>>
                                🚛 <?= htmlspecialchars($vehiculo['placa']) ?> - <?= htmlspecialchars($vehiculo['marca_vehiculo']) ?> <?= htmlspecialchars($vehiculo['modelo']) ?> 
                                <?php if (!empty($vehiculo['estado'])): ?>
                                    (<?= htmlspecialchars($vehiculo['estado']) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label">ID Orden</label>
                    <input type="number" name="filtro_orden" class="form-control" placeholder="Ej: 456" value="<?= isset($_GET['filtro_orden']) ? htmlspecialchars($_GET['filtro_orden']) : '' ?>">
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label">ID Alerta</label>
                    <input type="number" name="filtro_alerta" class="form-control" placeholder="Ej: 789" value="<?= isset($_GET['filtro_alerta']) ? htmlspecialchars($_GET['filtro_alerta']) : '' ?>">
                </div>
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-search me-1"></i> Filtrar
                        </button>
                        <a href="salida_vehiculo.php" class="btn btn-secondary flex-fill">
                            <i class="fas fa-times me-1"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <h4 class="mt-4">Salidas de Vehículos Registradas</h4>
    <!-- Mensajes de feedback -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php 
            switch($_GET['success']) {
                case 'actualizado':
                    echo '<strong>¡Éxito!</strong> La salida de vehículo ha sido actualizada correctamente.';
                    break;
                case 'eliminado':
                    echo '<strong>¡Éxito!</strong> La salida de vehículo ha sido eliminada correctamente.';
                    break;
                default:
                    echo '<strong>¡Éxito!</strong> Operación completada correctamente.';
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php 
            switch($_GET['error']) {
                case 'id_invalido':
                    echo '<strong>Error:</strong> ID de salida inválido.';
                    break;
                case 'no_encontrado':
                    echo '<strong>Error:</strong> La salida de vehículo no fue encontrada.';
                    break;
                case 'update_failed':
                    echo '<strong>Error:</strong> No se pudo actualizar la salida de vehículo.';
                    break;
                case 'delete_failed':
                    echo '<strong>Error:</strong> No se pudo eliminar la salida de vehículo.';
                    break;
                default:
                    echo '<strong>Error:</strong> Ocurrió un problema al procesar la solicitud.';
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="alert alert-info d-flex align-items-center mb-3 d-none d-md-flex">
        <i class="fas fa-info-circle me-2"></i>
        <span><strong>Consejo:</strong> La tabla tiene más columnas. Desliza horizontalmente o usa las flechas del teclado para ver toda la información disponible.</span>
    </div>
    <div class="alert alert-info d-flex align-items-center mb-3 d-md-none">
        <i class="fas fa-mobile-alt me-2"></i>
        <span><strong>Vista móvil:</strong> Los datos se muestran en tarjetas para mejor visualización en dispositivos móviles.</span>
    </div>
    <?php
    // Obtener salidas de vehículo con información completa de relaciones
    $query_salidas = "SELECT sv.*, 
                             rv.placa, rv.marca_vehiculo, rv.modelo, rv.num_cha,
                             ot.nombre_trabajo, ot.descripcion as orden_descripcion, ot.estado as orden_estado,
                             a.descripcion as alerta_descripcion, a.tipo_alerta, a.prioridad as alerta_prioridad, a.estado as alerta_estado,
                             r.nombre_reporte, r.tipo_reporte,
                             sr.fecha_salida as repuesto_fecha, sr.cantidad as repuesto_cantidad,
                             rep.nombre as repuesto_nombre
                      FROM sali_vehi sv 
                      LEFT JOIN regis_vehic rv ON sv.id_flotas = rv.id 
                      LEFT JOIN ord_trabj ot ON sv.ord_trabj_id = ot.id
                      LEFT JOIN alert a ON sv.alerta_id = a.id
                      LEFT JOIN repor r ON sv.repor_id = r.id
                      LEFT JOIN sali_repue sr ON sv.sali_repue_id = sr.id
                      LEFT JOIN repue rep ON sr.repue_id = rep.id
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
            return $sv['placa'] === $_GET['filtro_placa'];
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
    <div class="table-container">
        <div class="table-responsive" id="tableContainer">
            <table class="table table-bordered table-striped" style="min-width: 1400px;">
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
                    <th style="min-width:180px">Acciones</th>
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
                            <small class="text-muted">Chasis: <?= htmlspecialchars($sv['num_cha']) ?></small>
                        <?php else: ?>
                            <span class="text-danger">Vehículo no encontrado (ID: <?= $sv['id_flotas'] ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td class="wrap-text" title="<?= htmlspecialchars($sv['segui_monitoreo']) ?>">
                        <?= htmlspecialchars(strlen($sv['segui_monitoreo']) > 30 ? substr($sv['segui_monitoreo'], 0, 30) . '...' : $sv['segui_monitoreo']) ?>
                    </td>
                    <td class="wrap-text" title="<?= htmlspecialchars($sv['control_combustible']) ?>">
                        <?= htmlspecialchars(strlen($sv['control_combustible']) > 25 ? substr($sv['control_combustible'], 0, 25) . '...' : $sv['control_combustible']) ?>
                    </td>
                    <td class="wrap-text" title="<?= htmlspecialchars($sv['cump_regulaciones']) ?>">
                        <?= htmlspecialchars(strlen($sv['cump_regulaciones']) > 25 ? substr($sv['cump_regulaciones'], 0, 25) . '...' : $sv['cump_regulaciones']) ?>
                    </td>
                    <td class="wrap-text" title="<?= htmlspecialchars($sv['protocolo_seguridad']) ?>">
                        <?= htmlspecialchars(strlen($sv['protocolo_seguridad']) > 25 ? substr($sv['protocolo_seguridad'], 0, 25) . '...' : $sv['protocolo_seguridad']) ?>
                    </td>
                    <td class="wrap-text" title="<?= htmlspecialchars($sv['gest_conductores']) ?>">
                        <?= htmlspecialchars(strlen($sv['gest_conductores']) > 25 ? substr($sv['gest_conductores'], 0, 25) . '...' : $sv['gest_conductores']) ?>
                    </td>
                    <td>
                        <?php if ($sv['ord_trabj_id']): ?>
                            <strong>#<?= $sv['ord_trabj_id'] ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($sv['nombre_trabajo'] ?? 'N/A') ?></small><br>
                            <?php if ($sv['orden_estado']): ?>
                                <span class="badge <?= 
                                    $sv['orden_estado'] == 'resuelta' ? 'bg-success' : 
                                    ($sv['orden_estado'] == 'en_proceso' ? 'bg-warning' : 'bg-danger') 
                                ?>">
                                    <?= ucfirst(str_replace('_', ' ', $sv['orden_estado'])) ?>
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($sv['alerta_id']): ?>
                            <strong>#<?= $sv['alerta_id'] ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars(substr($sv['alerta_descripcion'] ?? 'N/A', 0, 30)) ?>...</small><br>
                            <?php if ($sv['alerta_prioridad']): ?>
                                <span class="badge <?= 
                                    $sv['alerta_prioridad'] == 'alta' ? 'bg-danger' : 
                                    ($sv['alerta_prioridad'] == 'media' ? 'bg-warning' : 'bg-info') 
                                ?>">
                                    <?= ucfirst($sv['alerta_prioridad']) ?>
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($sv['repor_id']): ?>
                            <strong>#<?= $sv['repor_id'] ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($sv['nombre_reporte'] ?? 'N/A') ?></small><br>
                            <small class="text-info"><?= htmlspecialchars($sv['tipo_reporte'] ?? 'N/A') ?></small>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($sv['sali_repue_id']): ?>
                            <strong>#<?= $sv['sali_repue_id'] ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($sv['repuesto_nombre'] ?? 'N/A') ?></small><br>
                            <?php if ($sv['repuesto_fecha']): ?>
                                <small class="text-info"><?= date('d/m/Y', strtotime($sv['repuesto_fecha'])) ?></small><br>
                            <?php endif; ?>
                            <?php if ($sv['repuesto_cantidad']): ?>
                                <span class="badge bg-secondary">Cant: <?= $sv['repuesto_cantidad'] ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div class="d-flex flex-column gap-1">
                            <?php if (!$rol_conductor): ?>
                                <a href="ver_salida_vehiculo.php?id=<?= $sv['id'] ?>" class="btn btn-sm btn-info" title="Ver detalles">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                <a href="editar_salida_vehiculo.php?id=<?= $sv['id'] ?>" class="btn btn-sm btn-primary" title="Editar">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="../controllers/SaliVehiController.php?action=eliminar&id=<?= $sv['id'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Eliminar"
                                   onclick="return confirm('¿Está seguro de eliminar esta salida de vehículo? Esta acción no se puede deshacer.');">
                                    <i class="fas fa-trash"></i> Eliminar
                                </a>
                            <?php else: ?>
                                <a href="ver_salida_vehiculo.php?id=<?= $sv['id'] ?>" class="btn btn-sm btn-info" title="Ver detalles">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            </table>
        </div>
        <div class="scroll-indicator" id="scrollIndicator" style="display: none;">
            <i class="fas fa-arrows-alt-h"></i>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-info">No hay salidas de vehículos registradas.</div>
    <?php endif; ?>

    <!-- Vista móvil con cards -->
    <?php if (count($salidas_vehiculo) > 0): ?>
    <div class="mobile-card-view">
        <?php foreach ($salidas_vehiculo as $sv): ?>
        <div class="mobile-vehicle-card">
            <div class="mobile-card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-truck me-2"></i>Salida #<?= $sv['id'] ?></span>
                    <?php if ($sv['placa']): ?>
                        <span class="badge bg-light text-dark"><?= htmlspecialchars($sv['placa']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mobile-card-body">
                <!-- Información del vehículo -->
                <?php if ($sv['placa']): ?>
                <div class="mobile-info-row">
                    <div class="mobile-info-label">Vehículo:</div>
                    <div class="mobile-info-value">
                        <strong><?= htmlspecialchars($sv['placa']) ?></strong><br>
                        <small><?= htmlspecialchars($sv['marca_vehiculo']) ?> <?= htmlspecialchars($sv['modelo']) ?></small>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Información de control (resumida) -->
                <div class="mobile-info-row">
                    <div class="mobile-info-label">Monitoreo:</div>
                    <div class="mobile-info-value">
                        <small><?= htmlspecialchars(strlen($sv['segui_monitoreo']) > 20 ? substr($sv['segui_monitoreo'], 0, 20) . '...' : $sv['segui_monitoreo']) ?></small>
                    </div>
                </div>
                
                <div class="mobile-info-row">
                    <div class="mobile-info-label">Combustible:</div>
                    <div class="mobile-info-value">
                        <small><?= htmlspecialchars(strlen($sv['control_combustible']) > 20 ? substr($sv['control_combustible'], 0, 20) . '...' : $sv['control_combustible']) ?></small>
                    </div>
                </div>
                
                <!-- Orden de trabajo -->
                <?php if ($sv['ord_trabj_id']): ?>
                <div class="mobile-info-row">
                    <div class="mobile-info-label">Orden:</div>
                    <div class="mobile-info-value">
                        <strong>#<?= $sv['ord_trabj_id'] ?></strong><br>
                        <small><?= htmlspecialchars(substr($sv['nombre_trabajo'] ?? 'N/A', 0, 25)) ?></small>
                        <?php if ($sv['orden_estado']): ?>
                            <br><span class="badge <?= 
                                $sv['orden_estado'] == 'resuelta' ? 'bg-success' : 
                                ($sv['orden_estado'] == 'en_proceso' ? 'bg-warning' : 'bg-danger') 
                            ?>" style="font-size: 0.7rem;">
                                <?= ucfirst(str_replace('_', ' ', $sv['orden_estado'])) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Alerta -->
                <?php if ($sv['alerta_id']): ?>
                <div class="mobile-info-row">
                    <div class="mobile-info-label">Alerta:</div>
                    <div class="mobile-info-value">
                        <strong>#<?= $sv['alerta_id'] ?></strong><br>
                        <small><?= htmlspecialchars(substr($sv['alerta_descripcion'] ?? 'N/A', 0, 25)) ?></small>
                        <?php if ($sv['alerta_prioridad']): ?>
                            <br><span class="badge <?= 
                                $sv['alerta_prioridad'] == 'alta' ? 'bg-danger' : 
                                ($sv['alerta_prioridad'] == 'media' ? 'bg-warning' : 'bg-info') 
                            ?>" style="font-size: 0.7rem;">
                                <?= ucfirst($sv['alerta_prioridad']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Repuesto -->
                <?php if ($sv['sali_repue_id']): ?>
                <div class="mobile-info-row">
                    <div class="mobile-info-label">Repuesto:</div>
                    <div class="mobile-info-value">
                        <strong>#<?= $sv['sali_repue_id'] ?></strong><br>
                        <small><?= htmlspecialchars(substr($sv['repuesto_nombre'] ?? 'N/A', 0, 20)) ?></small>
                        <?php if ($sv['repuesto_cantidad']): ?>
                            <br><span class="badge bg-secondary" style="font-size: 0.7rem;">Cant: <?= $sv['repuesto_cantidad'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Botones de acción -->
            <div class="mobile-actions">
                <?php if (!$rol_conductor): ?>
                    <a href="ver_salida_vehiculo.php?id=<?= $sv['id'] ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-eye me-1"></i> Ver Detalles
                    </a>
                    <a href="editar_salida_vehiculo.php?id=<?= $sv['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i> Editar
                    </a>
                    <a href="../controllers/SaliVehiController.php?action=eliminar&id=<?= $sv['id'] ?>" 
                       class="btn btn-danger btn-sm" 
                       onclick="return confirm('¿Está seguro de eliminar esta salida de vehículo?');">
                        <i class="fas fa-trash me-1"></i> Eliminar
                    </a>
                <?php else: ?>
                    <a href="ver_salida_vehiculo.php?id=<?= $sv['id'] ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-eye me-1"></i> Ver Detalles
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Abrir menú rápido al presionar el logo/nombre Trucksisx
document.addEventListener('DOMContentLoaded', function () {
    var brandSidebar = document.getElementById('brandSidebar');
    if (brandSidebar) {
        brandSidebar.addEventListener('click', function(e) {
            e.preventDefault();
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (!sidebar || !overlay) return;
            sidebar.classList.add('show-mobile');
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    }
});
// FAB para abrir menú rápido en desktop
document.addEventListener('DOMContentLoaded', function () {
    var fabSidebar = document.getElementById('fabSidebar');
    if (fabSidebar) {
        fabSidebar.addEventListener('click', function(e) {
            e.preventDefault();
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (!sidebar || !overlay) return;
            sidebar.classList.add('show-mobile');
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    }
});
// Sidebar - igual que dashboard.php
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const showSidebarBtn = document.getElementById('showSidebar');
    const closeBtn = document.getElementById('closeSidebar');
    function openSidebarMobile() {
        if (!sidebar || !overlay) return;
        sidebar.classList.add('show-mobile');
        overlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    function closeSidebarMobile() {
        if (!sidebar || !overlay) return;
        sidebar.classList.remove('show-mobile');
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    }
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            openSidebarMobile();
        });
    }
    if (showSidebarBtn) {
        showSidebarBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openSidebarMobile();
        });
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            closeSidebarMobile();
        });
    }
    if (overlay) {
        overlay.addEventListener('click', closeSidebarMobile);
    }
    window.addEventListener('resize', function () {
        if (window.innerWidth > 1024) {
            closeSidebarMobile();
        }
    });
});

// Autocompletado de salida de vehículo
document.addEventListener('DOMContentLoaded', function() {
    const ordenSelect = document.querySelector('select[name="ord_trabj_id"]');
    const vehiculoSelect = document.querySelector('select[name="id_flotas"]');
    const alertaSelect = document.querySelector('select[name="alerta_id"]');
    const salidaRepuestoSelect = document.querySelector('select[name="sali_repue_id"]');
    
    // Autocompletar al seleccionar Orden de Trabajo
    if (ordenSelect) {
        ordenSelect.addEventListener('change', function() {
            const ordenId = this.value;
            if (!ordenId) return;
            
            fetch(`../api/orden_trabajo_detalles.php?id=${ordenId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) return;
                    
                    // Autocompletar vehículo
                    if (data.vehiculo_id && vehiculoSelect) {
                        vehiculoSelect.value = data.vehiculo_id;
                        // Trigger change event para cargar datos del vehículo
                        vehiculoSelect.dispatchEvent(new Event('change'));
                    }
                    
                    // Autocompletar alerta
                    if (data.alerta_id && alertaSelect) {
                        alertaSelect.value = data.alerta_id;
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    }
    
    // Autocompletar al seleccionar Vehículo
    if (vehiculoSelect) {
        vehiculoSelect.addEventListener('change', function() {
            const vehiculoId = this.value;
            if (!vehiculoId) {
                ocultarInfoVehiculo();
                return;
            }
            
            fetch(`../api/vehiculo_detalles.php?vehiculo_id=${vehiculoId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }
                    
                    // Autocompletar alerta si existe
                    if (data.alerta_id && alertaSelect) {
                        alertaSelect.value = data.alerta_id;
                    }
                    
                    // Autocompletar orden de trabajo si existe
                    if (data.orden_trabajo_id && ordenSelect) {
                        ordenSelect.value = data.orden_trabajo_id;
                    }
                    
                    // Autocompletar salida de repuesto si existe
                    if (data.salida_repuesto_id && salidaRepuestoSelect) {
                        salidaRepuestoSelect.value = data.salida_repuesto_id;
                    } else if (data.salidas_repuesto_disponibles && data.salidas_repuesto_disponibles.length > 0) {
                        // Si hay múltiples salidas, seleccionar la más reciente
                        salidaRepuestoSelect.value = data.salidas_repuesto_disponibles[0].id;
                    }
                    
                    // Autocompletar conductor en el campo de gestión
                    const gestConductoresInput = document.querySelector('input[name="gest_conductores"]');
                    if (data.conductor_nombre_completo && gestConductoresInput) {
                        gestConductoresInput.value = data.conductor_nombre_completo;
                    }
                    
                    // Mostrar información del vehículo
                    mostrarInfoVehiculo(data);
                })
                .catch(error => {
                    console.error('Error al obtener detalles del vehículo:', error);
                });
        });
    }
});

function mostrarInfoVehiculo(data) {
    const infoContainer = document.getElementById('info-vehiculo-autocompletado');
    if (!infoContainer) return;
    
    let hasInfo = false;
    
    // Placa
    if (data.placa) {
        document.getElementById('info-placa').textContent = data.placa;
        document.getElementById('info-placa-container').style.display = 'block';
        hasInfo = true;
    } else {
        document.getElementById('info-placa-container').style.display = 'none';
    }
    
    // Marca y modelo
    if (data.marca_vehiculo || data.modelo) {
        const vehiculoInfo = `${data.marca_vehiculo || ''} ${data.modelo || ''}`.trim();
        document.getElementById('info-vehiculo-marca').textContent = vehiculoInfo;
        document.getElementById('info-vehiculo-marca-container').style.display = 'block';
        hasInfo = true;
    } else {
        document.getElementById('info-vehiculo-marca-container').style.display = 'none';
    }
    
    // Conductor
    if (data.conductor_nombre_completo) {
        document.getElementById('info-conductor-vehiculo').textContent = data.conductor_nombre_completo;
        document.getElementById('info-conductor-vehiculo-container').style.display = 'block';
        hasInfo = true;
    } else {
        document.getElementById('info-conductor-vehiculo-container').style.display = 'none';
    }
    
    // Alerta
    if (data.alerta_id) {
        const alertaInfo = `#${data.alerta_id} - ${data.alerta_descripcion || 'Sin descripción'}`;
        const prioridadBadge = {
            'baja': '<span class="badge bg-info">BAJA</span>',
            'media': '<span class="badge bg-warning text-dark">MEDIA</span>',
            'alta': '<span class="badge bg-danger">ALTA</span>',
            'critica': '<span class="badge bg-dark">CRÍTICA</span>'
        };
        const badge = data.alerta_prioridad ? prioridadBadge[data.alerta_prioridad] : '';
        document.getElementById('info-alerta-vehiculo').innerHTML = `${alertaInfo} ${badge}`;
        document.getElementById('info-alerta-vehiculo-container').style.display = 'block';
        hasInfo = true;
    } else {
        document.getElementById('info-alerta-vehiculo-container').style.display = 'none';
    }
    
    // Orden de trabajo
    if (data.orden_trabajo_id) {
        const ordenInfo = `#${data.orden_trabajo_id} - ${data.nombre_trabajo || 'Sin nombre'}`;
        document.getElementById('info-orden-vehiculo').textContent = ordenInfo;
        document.getElementById('info-orden-vehiculo-container').style.display = 'block';
        hasInfo = true;
    } else {
        document.getElementById('info-orden-vehiculo-container').style.display = 'none';
    }
    
    // Repuesto
    if (data.repuesto_nombre) {
        const repuestoInfo = `${data.repuesto_nombre} (Cant: ${data.repuesto_cantidad || 'N/A'})`;
        document.getElementById('info-repuesto-vehiculo').textContent = repuestoInfo;
        document.getElementById('info-repuesto-vehiculo-container').style.display = 'block';
        hasInfo = true;
    } else {
        document.getElementById('info-repuesto-vehiculo-container').style.display = 'none';
    }
    
    // Mostrar u ocultar el contenedor
    infoContainer.style.display = hasInfo ? 'block' : 'none';
}

function ocultarInfoVehiculo() {
    const infoContainer = document.getElementById('info-vehiculo-autocompletado');
    if (infoContainer) {
        infoContainer.style.display = 'none';
    }
}
</script>
<script>
// Corregir problema de aria-hidden con el modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalRegistrarSalida');
    if (modal) {
        // Prevenir que Bootstrap agregue aria-hidden
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'aria-hidden') {
                    if (modal.hasAttribute('aria-hidden')) {
                        modal.removeAttribute('aria-hidden');
                    }
                }
            });
        });
        
        observer.observe(modal, {
            attributes: true,
            attributeFilter: ['aria-hidden']
        });
        
        modal.addEventListener('show.bs.modal', function() {
            this.setAttribute('aria-modal', 'true');
            this.setAttribute('role', 'dialog');
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const tableContainer = document.getElementById('tableContainer');
    const scrollIndicator = document.getElementById('scrollIndicator');
    
    if (tableContainer && scrollIndicator) {
        // Verificar si necesita scroll horizontal
        function checkScrollNeed() {
            if (tableContainer.scrollWidth > tableContainer.clientWidth) {
                scrollIndicator.style.display = 'block';
            } else {
                scrollIndicator.style.display = 'none';
            }
        }
        
        // Ocultar indicador cuando se hace scroll
        tableContainer.addEventListener('scroll', function() {
            if (this.scrollLeft > 10) {
                scrollIndicator.style.display = 'none';
            } else if (this.scrollWidth > this.clientWidth) {
                scrollIndicator.style.display = 'block';
            }
        });
        
        // Verificar en carga y redimensionamiento
        checkScrollNeed();
        window.addEventListener('resize', checkScrollNeed);
        
        // Hacer click en el indicador para iniciar el scroll
        scrollIndicator.addEventListener('click', function() {
            tableContainer.scrollBy({ left: 200, behavior: 'smooth' });
        });
    }
    
    // Agregar tooltip a los elementos truncados
    const cells = document.querySelectorAll('.table tbody td');
    cells.forEach(cell => {
        if (cell.scrollWidth > cell.clientWidth) {
            cell.title = cell.textContent.trim();
        }
    });
});
</script>
</html>

