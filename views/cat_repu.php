<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
require_once '../controllers/CatRepuController.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categoría de Repuestos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0F172A;
            --bg-secondary: #1E293B;
            --card-bg: #1E293B;
            --text-primary: #F1F5F9;
            --text-secondary: #94A3B8;
            --border: #334155;
            --accent: #F97316;
            --accent-amber: #F59E0B;
            --danger: #EF4444;
            --success: #10B981;
            --card-radius: 12px;
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

        .btn-dark {
            background: var(--text-secondary);
            color: #FFFFFF !important;
        }

        .btn-dark:hover {
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

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1060;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(4px);
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-dialog-custom {
            background: var(--card-bg);
            border-radius: var(--card-radius);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.9) translateY(-20px);
            transition: all 0.3s ease;
            border: 1px solid var(--border);
        }

        .modal-overlay.active .modal-dialog-custom {
            transform: scale(1) translateY(0);
        }

        .modal-header-custom {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header-custom h5 {
            margin: 0;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.25rem;
        }

        .modal-close-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
        }

        .modal-close-btn:hover {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .modal-body-custom {
            padding: 1.5rem;
        }

        .modal-footer-custom {
            padding: 1rem 1.5rem 1.5rem;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .modal-active {
            overflow: hidden;
        }

        /* Toast Notification */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--success);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: var(--card-radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 1070;
            transform: translateX(400px);
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .toast-notification.show {
            transform: translateX(0);
        }

        .toast-notification i {
            font-size: 1.5rem;
        }

        /* Bootstrap Modal Styles */
        .modal-content {
            background: white;
            color: black;
            border-radius: var(--card-radius);
            border: 1px solid #dee2e6;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background: white;
            color: black;
            border-bottom: 1px solid #e2c108ff;
            border-radius: var(--card-radius) var(--card-radius) 0 0;
        }

        .modal-header .btn-close {
            /* Sin filtro, ya que el fondo es blanco */
        }

        .modal-body {
            background: white;
            color: black;
        }

        .modal-footer {
            background: white;
            border-top: 1px solid #dee2e6;
            border-radius: 0 0 var(--card-radius) var(--card-radius);
        }

        .modal-backdrop {
            background: rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body>
<div class="container"> 
    <div class="main-container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><i class="bi bi-tags-fill"></i> Gestión de Categorías de Repuestos</h2>
                    <p class="mb-0 opacity-75">Administra las categorías del sistema de repuestos</p>
                </div>
                <a href="gestiones.php" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Volver a Gestiones
                </a>
            </div>
        </div>
        
        <!-- Content Section -->
        <div class="content-section">
            <!-- Filtros y Búsqueda -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <form method="get" action="">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="filtro_nombre" class="form-control" 
                                       placeholder="Buscar por nombre..." 
                                       value="<?= isset($_GET['filtro_nombre']) ? htmlspecialchars($_GET['filtro_nombre']) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="filtro_caracteristicas" class="form-control" 
                                       placeholder="Buscar por características..." 
                                       value="<?= isset($_GET['filtro_caracteristicas']) ? htmlspecialchars($_GET['filtro_caracteristicas']) : '' ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                            <div class="col-md-2">
                                <?php if (isset($_GET['filtro_nombre']) || isset($_GET['filtro_caracteristicas'])): ?>
                                <a href="cat_repu.php" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-x"></i> Limpiar
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 text-end">
                    <?php if (!$rol_conductor): ?>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearCategoria">
                        <i class="bi bi-plus-circle"></i> Agregar Categoría
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Tabla de Categorías -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th><i class="bi bi-hash"></i> ID</th>
                            <th><i class="bi bi-bookmarks"></i> Nombre</th>
                            <th><i class="bi bi-list-ul"></i> Características</th>
                            <th><i class="bi bi-box"></i> Repuestos</th>
                            <th class="text-center"><i class="bi bi-gear"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    require_once '../models/Repue.php';
                    $repueModel = new Repue();
                    
                    // Aplicar filtros
                    $categorias_filtradas = [];
                    $categorias->data_seek(0);
                    while ($row = $categorias->fetch_assoc()) {
                        $incluir = true;
                        
                        if (!empty($_GET['filtro_nombre'])) {
                            $filtro_nombre = strtolower($_GET['filtro_nombre']);
                            if (strpos(strtolower($row['nombre']), $filtro_nombre) === false) {
                                $incluir = false;
                            }
                        }
                        
                        if (!empty($_GET['filtro_caracteristicas'])) {
                            $filtro_carac = strtolower($_GET['filtro_caracteristicas']);
                            if (strpos(strtolower($row['caracteristicas'] ?? ''), $filtro_carac) === false) {
                                $incluir = false;
                            }
                        }
                        
                        if ($incluir) {
                            $categorias_filtradas[] = $row;
                        }
                    }
                    
                    if (empty($categorias_filtradas)):
                    ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                                <p class="mt-2 text-muted">No se encontraron categorías</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categorias_filtradas as $row): 
                            // Contar repuestos asociados
                            $repuestos = $repueModel->getAll(['cat_repu_id' => $row['id']]);
                            $count_repuestos = 0;
                            while ($rep = $repuestos->fetch_assoc()) {
                                $count_repuestos++;
                            }
                        ?>
                            <tr>
                                <td><strong>#<?= $row['id'] ?></strong></td>
                                <td><strong><?= htmlspecialchars($row['nombre']) ?></strong></td>
                                <td>
                                    <?php if (!empty($row['caracteristicas'])): ?>
                                        <?= htmlspecialchars(substr($row['caracteristicas'], 0, 50)) ?>
                                        <?php if (strlen($row['caracteristicas']) > 50): ?>...<?php endif; ?>
                                    <?php else: ?>
                                        <small class="text-muted">Sin características</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= $count_repuestos ?> repuesto(s)</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" 
                                                class="btn btn-outline-info" 
                                                onclick="verCategoria(<?= htmlspecialchars(json_encode($row)) ?>)"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalVerCategoria"
                                                title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if (!$rol_conductor): ?>
                                        <button type="button" 
                                                class="btn btn-outline-warning" 
                                                onclick="editarCategoria(<?= htmlspecialchars(json_encode($row)) ?>)"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEditarCategoria"
                                                title="Editar categoría">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                onclick="eliminarCategoria(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nombre']) ?>', <?= $count_repuestos ?>)"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEliminarCategoria"
                                                title="Eliminar categoría">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Modal para crear categoría -->
<div class="modal fade" id="modalCrearCategoria" tabindex="-1" aria-labelledby="modalCrearCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCrearCategoriaLabel">
                    <i class="bi bi-plus-circle"></i> Crear Nueva Categoría
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="" id="formCrearCategoria">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nombre_create" class="form-label">
                                <i class="bi bi-bookmarks"></i> Nombre de la Categoría *
                            </label>
                            <input type="text" class="form-control" id="nombre_create" name="nombre" 
                                   placeholder="Ej: Filtros de Aceite" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="caracteristicas_create" class="form-label">
                            <i class="bi bi-list-ul"></i> Características
                        </label>
                        <textarea class="form-control" id="caracteristicas_create" name="caracteristicas" 
                                  rows="4" placeholder="Describe las características principales de esta categoría..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Información:</strong> Esta categoría estará disponible para clasificar repuestos en el sistema.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="submit" form="formCrearCategoria" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Crear Categoría
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalles de categoría -->
<div class="modal fade" id="modalVerCategoria" tabindex="-1" aria-labelledby="modalVerCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light text-dark">
                <h5 class="modal-title" id="modalVerCategoriaLabel">
                    <i class="bi bi-eye"></i> Detalles de la Categoría
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-dark text-white">
                                <i class="bi bi-info-circle"></i> Información General
                            </div>
                            <div class="card-body">
                                <p><strong>ID:</strong> <span id="ver_id"></span></p>
                                <p><strong>Nombre:</strong> <span id="ver_nombre"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-dark text-white">
                                <i class="bi bi-list-ul"></i> Características
                            </div>
                            <div class="card-body">
                                <p id="ver_caracteristicas"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar categoría -->
<div class="modal fade" id="modalEditarCategoria" tabindex="-1" aria-labelledby="modalEditarCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditarCategoriaLabel">
                    <i class="bi bi-pencil-square"></i> Editar Categoría
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="" id="formEditarCategoria">
                    <input type="hidden" name="id" id="editar_id">
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="editar_nombre" class="form-label">
                                <i class="bi bi-bookmarks"></i> Nombre de la Categoría *
                            </label>
                            <input type="text" class="form-control" id="editar_nombre" name="nombre" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editar_caracteristicas" class="form-label">
                            <i class="bi bi-list-ul"></i> Características
                        </label>
                        <textarea class="form-control" id="editar_caracteristicas" name="caracteristicas" rows="4"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="submit" form="formEditarCategoria" class="btn btn-warning">
                    <i class="bi bi-check-circle"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div class="modal fade" id="modalEliminarCategoria" tabindex="-1" aria-labelledby="modalEliminarCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminarCategoriaLabel">
                    <i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>¡Advertencia!</strong> Esta acción no se puede deshacer.
                </div>
                
                <p class="mb-3">¿Está seguro de que desea eliminar la categoría:</p>
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title mb-1" id="eliminar_nombre_categoria"></h6>
                        <small class="text-muted">ID: <span id="eliminar_id_categoria"></span></small>
                    </div>
                </div>
                
                <div class="mt-3" id="advertencia_repuestos" style="display: none;">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-circle"></i>
                        Esta categoría tiene <span id="count_repuestos"></span> repuesto(s) asociado(s).
                        Al eliminar la categoría, estos repuestos quedarán sin categoría.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <a href="#" id="btn_confirmar_eliminar_categoria" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Sí, Eliminar Categoría
                </a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Función para ver detalles de la categoría
function verCategoria(categoria) {
    document.getElementById('ver_id').textContent = '#' + categoria.id;
    document.getElementById('ver_nombre').textContent = categoria.nombre;
    document.getElementById('ver_caracteristicas').textContent = categoria.caracteristicas || 'Sin características especificadas';
}

// Función para cargar datos en el modal de edición
function editarCategoria(categoria) {
    document.getElementById('editar_id').value = categoria.id;
    document.getElementById('editar_nombre').value = categoria.nombre;
    document.getElementById('editar_caracteristicas').value = categoria.caracteristicas || '';
    // --- Solución accesibilidad: quitar aria-hidden si existe ---
    const modal = document.getElementById('modalEditarCategoria');
    if (modal.hasAttribute('aria-hidden')) {
        modal.removeAttribute('aria-hidden');
    }
    // Mostrar el modal usando Bootstrap (si no se usa data-bs-toggle)
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modal);
        modalInstance.show();
    }
}

// Función para configurar modal de eliminación
function eliminarCategoria(id, nombre, countRepuestos) {
    document.getElementById('eliminar_id_categoria').textContent = id;
    document.getElementById('eliminar_nombre_categoria').textContent = nombre;
    document.getElementById('btn_confirmar_eliminar_categoria').href = 'cat_repu.php?delete=' + id;
    
    // Mostrar advertencia si hay repuestos asociados
    if (countRepuestos > 0) {
        document.getElementById('count_repuestos').textContent = countRepuestos;
        document.getElementById('advertencia_repuestos').style.display = 'block';
    } else {
        document.getElementById('advertencia_repuestos').style.display = 'none';
    }
}

// Validaciones de formularios
document.getElementById('formCrearCategoria').addEventListener('submit', function(e) {
    const nombre = document.getElementById('nombre_create').value.trim();
    if (!nombre) {
        e.preventDefault();
        alert('El nombre de la categoría es obligatorio');
        return false;
    }
});

document.getElementById('formEditarCategoria').addEventListener('submit', function(e) {
    const nombre = document.getElementById('editar_nombre').value.trim();
    if (!nombre) {
        e.preventDefault();
        alert('El nombre de la categoría es obligatorio');
        return false;
    }
});

// Limpiar formularios al cerrar modales
document.getElementById('modalCrearCategoria').addEventListener('hidden.bs.modal', function () {
    document.getElementById('formCrearCategoria').reset();
});

document.getElementById('modalEditarCategoria').addEventListener('hidden.bs.modal', function () {
    document.getElementById('formEditarCategoria').reset();
});

// Mostrar mensajes de éxito/error si existen en la URL
window.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const mensaje = urlParams.get('mensaje');
    const tipo = urlParams.get('tipo');
    
    if (mensaje && tipo) {
        const alertClass = tipo === 'exito' ? 'alert-success' : 'alert-danger';
        const iconClass = tipo === 'exito' ? 'bi-check-circle' : 'bi-exclamation-triangle';
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="bi ${iconClass}"></i>
            <strong>${tipo === 'exito' ? 'Éxito:' : 'Error:'}</strong> ${decodeURIComponent(mensaje)}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        const container = document.querySelector('.content-section');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Remover parámetros de la URL
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
    
    // Mejorar UX en móvil - colapsar modales automáticamente
    if (window.innerWidth <= 768) {
        // Hacer que los modales se adapten mejor en móvil
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('show.bs.modal', function() {
                document.body.style.paddingRight = '0';
            });
        });
    }
});
</script>

</body>
</html>
