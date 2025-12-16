<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
require_once '../controllers/SubCatRepuController.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subcategorías de Repuestos - TruckSISX</title>
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

    <div class="container-fluid py-4">
    <div class="main-header animate-fade-in">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">
                    <i class="fas fa-layer-group text-accent"></i> Subcategorías de Repuestos
                </h1>
                <p class="mb-0 opacity-75">Gestión de subcategorías para clasificación de repuestos</p>
            </div>
            <div class="d-flex gap-2">
            <?php if (!$rol_conductor): ?>
                <button type="button" class="btn btn-primary shadow-corporate" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="bi bi-plus-circle"></i> Agregar Subcategoría
                </button>
            <?php endif; ?>
            <a href="repue.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Volver a Repuestos
            </a>
            </div>
        </div>
    </div>
    <!-- Mensajes de alerta -->
    <?php if (!empty($mensaje_success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> <?= htmlspecialchars($mensaje_success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (!empty($mensaje_error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($mensaje_error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">
                <i class="bi bi-funnel"></i> Filtros de Búsqueda
            </h5>
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="filtro_nombre" class="form-control" value="<?= htmlspecialchars($filtro_nombre) ?>" placeholder="Buscar por nombre...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Características</label>
                    <input type="text" name="filtro_caracteristicas" class="form-control" value="<?= htmlspecialchars($filtro_caracteristicas) ?>" placeholder="Buscar por características...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Categoría</label>
                    <select name="filtro_categoria" class="form-select">
                        <option value="">Todas las categorías</option>
                        <?php 
                        $categorias_filtro = $catRepu->getAll();
                        while ($cat = $categorias_filtro->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>" <?= $filtro_categoria == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="subcat_repu.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Características</th>
                            <th>Categoría</th>
                            <?php if (!$rol_conductor): ?>
                            <th class="text-center" width="180">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $subcategorias->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['nombre']) ?></td>
                            <td><?= htmlspecialchars($row['caracteristicas'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['categoria_nombre'] ?? '') ?></td>
                            <?php if (!$rol_conductor): ?>
                            <td class="text-center">
                                <button type="button" class="btn btn-info btn-sm me-1" onclick="viewSubcategory(<?= $row['id'] ?>)" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-warning btn-sm me-1" onclick="editSubcategory(<?= $row['id'] ?>)" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteSubcategory(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nombre'], ENT_QUOTES) ?>')" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 text-end">
        <a href="gestiones.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Panel de Gestiones
        </a>
    </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">
                    <i class="bi bi-plus-circle"></i> Agregar Nueva Subcategoría
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createForm" method="post">
                <div class="modal-body">
                    <div class="form-section">
                        <h6><i class="bi bi-info-circle"></i> Información Básica</h6>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" class="form-control" required placeholder="Nombre de la subcategoría">
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6><i class="bi bi-gear"></i> Detalles</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Características</label>
                                <input type="text" name="caracteristicas" class="form-control" placeholder="Características específicas">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Categoría <span class="text-danger">*</span></label>
                                <select name="categoria_id" class="form-select" required>
                                    <option value="">Seleccione una categoría...</option>
                                    <?php 
                                    $categorias_create = $catRepu->getAll();
                                    while ($cat = $categorias_create->fetch_assoc()): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Guardar Subcategoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">
                    <i class="bi bi-eye"></i> Detalles de Subcategoría
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-section">
                    <h6><i class="bi bi-info-circle"></i> Información General</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> <span id="view-id"></span></p>
                            <p><strong>Nombre:</strong> <span id="view-nombre"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Categoría:</strong> <span id="view-categoria"></span></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <p><strong>Características:</strong> <span id="view-caracteristicas"></span></p>
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

<!-- Modal Editar -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">
                    <i class="bi bi-pencil"></i> Editar Subcategoría
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" method="post">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="form-section">
                        <h6><i class="bi bi-info-circle"></i> Información Básica</h6>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="edit-nombre" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6><i class="bi bi-gear"></i> Detalles</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Características</label>
                                <input type="text" name="caracteristicas" id="edit-caracteristicas" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Categoría <span class="text-danger">*</span></label>
                                <select name="categoria_id" id="edit-categoria" class="form-select" required>
                                    <option value="">Seleccione una categoría...</option>
                                    <?php 
                                    $categorias_edit = $catRepu->getAll();
                                    while ($cat = $categorias_edit->fetch_assoc()): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle"></i> Actualizar Subcategoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Función para ver subcategoría
function viewSubcategory(id) {
    fetch(`subcat_repu.php?ajax=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('view-id').textContent = data.id || '';
            document.getElementById('view-nombre').textContent = data.nombre || '';
            document.getElementById('view-categoria').textContent = data.categoria_nombre || '';
            document.getElementById('view-caracteristicas').textContent = data.caracteristicas || 'N/A';
            
            new bootstrap.Modal(document.getElementById('viewModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos');
        });
}

// Función para editar subcategoría
function editSubcategory(id) {
    fetch(`subcat_repu.php?ajax=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit-id').value = data.id || '';
            document.getElementById('edit-nombre').value = data.nombre || '';
            document.getElementById('edit-caracteristicas').value = data.caracteristicas || '';
            document.getElementById('edit-categoria').value = data.categoria_id || '';
            // --- Solución accesibilidad: quitar aria-hidden si existe ---
            const modal = document.getElementById('editModal');
            if (modal.hasAttribute('aria-hidden')) {
                modal.removeAttribute('aria-hidden');
            }
            new bootstrap.Modal(modal).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos');
        });
}

// Función para eliminar subcategoría
function deleteSubcategory(id, nombre) {
    if (confirm(`¿Está seguro de eliminar la subcategoría "${nombre}"?`)) {
        window.location.href = `subcat_repu.php?delete=${id}`;
    }
}

// Validación del formulario de crear
document.getElementById('createForm').addEventListener('submit', function(e) {
    const nombre = this.querySelector('[name="nombre"]').value.trim();
    const categoria = this.querySelector('[name="categoria_id"]').value;
    
    if (!nombre) {
        e.preventDefault();
        alert('El nombre es obligatorio');
        return false;
    }
    
    if (!categoria) {
        e.preventDefault();
        alert('Debe seleccionar una categoría');
        return false;
    }
});

// Validación del formulario de editar
document.getElementById('editForm').addEventListener('submit', function(e) {
    const nombre = this.querySelector('[name="nombre"]').value.trim();
    const categoria = this.querySelector('[name="categoria_id"]').value;
    
    if (!nombre) {
        e.preventDefault();
        alert('El nombre es obligatorio');
        return false;
    }
    
    if (!categoria) {
        e.preventDefault();
        alert('Debe seleccionar una categoría');
        return false;
    }
});
</script>

</body>
</html>