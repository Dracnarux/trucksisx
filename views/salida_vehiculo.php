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
            overflow-x: auto;
            overflow-y: hidden;
            max-width: 100%;
        }
        .table thead th {
            background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);
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
            border-bottom: 1px solid #E5E7EB;
            color: #374151;
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
        .badge.bg-secondary {
            background: linear-gradient(135deg, #6B7280 0%, #4B5563 100%) !important;
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
        .btn-sm {
            font-size: 0.8rem;
            padding: 0.5rem 0.75rem;
            min-height: auto;
        }
        .btn-info {
            background: linear-gradient(135deg, #06B6D4 0%, #0891B2 100%);
            color: #FFFFFF !important;
        }
        .btn-info:hover {
            background: linear-gradient(135deg, #0891B2 0%, #0E7490 100%);
            color: #FFFFFF !important;
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
            background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);
            border-radius: 10px;
        }
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #3B82F6 0%, #1E3A8A 100%);
        }
        .scroll-indicator {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            background: rgba(30, 58, 138, 0.8);
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
                background: #FFFFFF;
                border: 1px solid rgba(209, 213, 219, 0.3);
                border-radius: 12px;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
                margin-bottom: 1rem;
                overflow: hidden;
            }
            
            .mobile-card-header {
                background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);
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
                border-bottom: 1px solid #E5E7EB;
            }
            
            .mobile-info-row:last-child {
                border-bottom: none;
            }
            
            .mobile-info-label {
                font-weight: 500;
                color: #6B7280;
                font-size: 0.85rem;
                flex: 0 0 40%;
            }
            
            .mobile-info-value {
                color: #374151;
                font-size: 0.85rem;
                text-align: right;
                flex: 1;
                word-wrap: break-word;
            }
            
            .mobile-actions {
                padding: 1rem;
                background: #F9FAFB;
                border-top: 1px solid #E5E7EB;
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
<div class="container-fluid mt-4">
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
                                <a href="editar_salida_vehiculo.php?id=<?= $sv['id'] ?>" class="btn btn-sm btn-warning" title="Editar">
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
                    <a href="editar_salida_vehiculo.php?id=<?= $sv['id'] ?>" class="btn btn-warning btn-sm">
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

