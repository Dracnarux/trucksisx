<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';

// Procesar peticiones AJAX antes de mostrar HTML
require_once '../config/db.php';
$conn = conectarDB();

if (!$rol_conductor && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
    header('Content-Type: application/json');
    try {
        if (!empty($_POST['id'])) {
            $sql = "UPDATE cat_vehic SET nombre = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $_POST['nombre'], $_POST['id']);
            $stmt->execute();
        } else {
            $sql = "INSERT INTO cat_vehic (nombre) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $_POST['nombre']);
            $stmt->execute();
        }
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categoría de Vehículos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
    </style>
</head>
<body>
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-icon">🚚</div>
            <div class="brand-text">TruckSisX</div>
        </div>
        <button class="sidebar-toggle" onclick="toggleSidebar()" title="Colapsar menú">
            <span id="toggleIcon">←</span>
        </button>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Principal</div>
            <a href="dashboard.php" class="nav-item">
                <span class="nav-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                </span>
                <span class="nav-item-text">Dashboard</span>
                <span class="nav-item-tooltip">Dashboard</span>
            </a>
            <a href="truck_alerts.php" class="nav-item">
                <span class="nav-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </span>
                <span class="nav-item-text">Sistema de Alertas</span>
                <span class="nav-item-tooltip">Sistema de Alertas</span>
            </a>
            <a href="orden_trabajo.php" class="nav-item">
                <span class="nav-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                </span>
                <span class="nav-item-text">Órdenes de Trabajo</span>
                <span class="nav-item-tooltip">Órdenes de Trabajo</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Gestiones</div>
            <a href="gestion_vehicular.php" class="nav-item">
                <span class="nav-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 18h-1.5a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5H18"></path><path d="M6 18H4.5a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5H6"></path><path d="M2 14h20"></path><path d="M22 11V7.414a2 2 0 0 0-.586-1.414l-1.414-1.414A2 2 0 0 0 18.586 4H5.414A2 2 0 0 0 4 4.586L2.586 6A2 2 0 0 0 2 7.414V11"></path><circle cx="6" cy="18" r="2"></circle><circle cx="18" cy="18" r="2"></circle></svg>
                </span>
                <span class="nav-item-text">Gestión Vehicular</span>
                <span class="nav-item-tooltip">Gestión Vehicular</span>
            </a>
            <a href="regis_vehic.php" class="nav-item">
                <span class="nav-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                </span>
                <span class="nav-item-text">Registro Vehículos</span>
                <span class="nav-item-tooltip">Registro Vehículos</span>
            </a>
            <a href="cond.php" class="nav-item">
                <span class="nav-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </span>
                <span class="nav-item-text">Conductores</span>
                <span class="nav-item-tooltip">Conductores</span>
            </a>
            <a href="repue.php" class="nav-item">
                <span class="nav-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                </span>
                <span class="nav-item-text">Repuestos</span>
                <span class="nav-item-tooltip">Repuestos</span>
            </a>
            <a href="proveedor.php" class="nav-item">
                <span class="nav-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </span>
                <span class="nav-item-text">Proveedores</span>
                <span class="nav-item-tooltip">Proveedores</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Registros</div>
            <a href="salida_vehiculo.php" class="nav-item">
                <span class="nav-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                </span>
                <span class="nav-item-text">Salida Vehículos</span>
                <span class="nav-item-tooltip">Salida Vehículos</span>
            </a>
            <a href="salida_repuesto.php" class="nav-item">
                <span class="nav-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                </span>
                <span class="nav-item-text">Salida Repuestos</span>
                <span class="nav-item-tooltip">Salida Repuestos</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Configuración</div>
            <a href="cat_vehiculo.php" class="nav-item active">
                <span class="nav-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                </span>
                <span class="nav-item-text">Categorías</span>
                <span class="nav-item-tooltip">Categorías</span>
            </a>
            <a href="crear_usuario.php" class="nav-item">
                <span class="nav-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                </span>
                <span class="nav-item-text">Usuarios</span>
                <span class="nav-item-tooltip">Usuarios</span>
            </a>
            <a href="../logout.php" class="nav-item">
                <span class="nav-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                </span>
                <span class="nav-item-text">Cerrar Sesión</span>
                <span class="nav-item-tooltip">Cerrar Sesión</span>
            </a>
        </div>
    </nav>
</aside>

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebarMobile()"></div>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0"><i class="bi bi-truck"></i> Categoría de Vehículos</h2>
            <?php if (!$rol_conductor): ?>
            <button type="button" class="btn btn-success" onclick="openCategoryModal()"><i class="bi bi-plus-circle"></i> Agregar Categoría</button>
            <?php endif; ?>
        </div>
        <?php
        $filtro = isset($_GET['filtro_nombre']) ? $_GET['filtro_nombre'] : '';
        $sql = "SELECT * FROM cat_vehic WHERE nombre LIKE ? ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
        $like = "%$filtro%";
        $stmt->bind_param('s', $like);
        $stmt->execute();
        $categorias = $stmt->get_result();
        ?>
        <form class="row mb-4" method="get">
            <div class="col-md-4">
                <input type="text" name="filtro_nombre" class="form-control" placeholder="Buscar por nombre" value="<?= htmlspecialchars($filtro) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filtrar</button>
            </div>
            <div class="col-md-2">
                <a href="cat_vehiculo.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle"></i> Limpiar</a>
            </div>
        </form>
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($categorias && $categorias->num_rows > 0): ?>
                        <?php while ($row = $categorias->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['nombre']) ?></td>
                            <td class="text-center">
                                <?php if (!$rol_conductor): ?>
                                <button type="button" class="btn btn-warning btn-sm mx-1" onclick="editCategory(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nombre'], ENT_QUOTES) ?>')"><i class="bi bi-pencil-square"></i> Editar</button>
                                <a href="cat_vehiculo.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm mx-1" onclick="return confirm('¿Eliminar categoría?')"><i class="bi bi-trash"></i> Eliminar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">No hay categorías registradas.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal para Agregar/Editar Categoría -->
        <div class="modal-overlay" id="categoryModal">
            <div class="modal-dialog-custom">
                <div class="modal-header-custom">
                    <h5><i class="bi bi-grid-3x3"></i> <span id="modalTitle">Agregar Categoría</span></h5>
                    <button type="button" class="modal-close-btn" onclick="closeCategoryModal()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <form id="categoryForm">
                    <input type="hidden" name="id" id="categoryId">
                    <div class="modal-body-custom">
                        <div class="mb-3">
                            <label class="form-label">Nombre de la Categoría <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="nombre" 
                                id="categoryName"
                                class="form-control" 
                                required 
                                placeholder="Ej: Camión de carga"
                            >
                            <div class="invalid-feedback" id="nameError">El nombre es obligatorio</div>
                        </div>
                    </div>
                    <div class="modal-footer-custom">
                        <button type="button" class="btn btn-secondary" onclick="closeCategoryModal()">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="saveBtn" disabled>
                            <i class="bi bi-check-lg"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Toast Notification -->
        <div class="toast-notification" id="successToast">
            <i class="bi bi-check-circle-fill"></i>
            <div>
                <div class="fw-bold">¡Éxito!</div>
                <div class="small">Categoría guardada correctamente</div>
            </div>
        </div>
        <?php
        // Eliminar categoría
        if (!$rol_conductor) {
            if (isset($_GET['delete'])) {
                try {
                    $categoria_id = $_GET['delete'];
                    // Verificar si hay subcategorías asociadas
                    $check_sql = "SELECT COUNT(*) as count FROM subcat_vehic WHERE cat_vehic_id = ?";
                    $check_stmt = $conn->prepare($check_sql);
                    $check_stmt->bind_param('i', $categoria_id);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();
                    $row = $result->fetch_assoc();
                    $subcategorias_count = $row['count'];
                                        if ($subcategorias_count > 0) {
                                                echo "<script>
                                                        function showModalErrorCat() {
                                                                var modalHtml = `<div class=\"modal fade\" id=\"modalErrorCat\" tabindex=\"-1\" aria-labelledby=\"modalErrorCatLabel\" aria-hidden=\"true\">
                                                                    <div class=\"modal-dialog modal-dialog-centered\">
                                                                        <div class=\"modal-content\">
                                                                            <div class=\"modal-header bg-danger text-white\">
                                                                                <h5 class=\"modal-title\" id=\"modalErrorCatLabel\">No se puede eliminar la categoría</h5>
                                                                                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Cerrar\"></button>
                                                                            </div>
                                                                            <div class=\"modal-body\">
                                                                                <p>La categoría seleccionada tiene <b>$subcategorias_count</b> subcategoría(s) asociada(s).<br><br>Para poder eliminar esta categoría, primero debe eliminar o reasignar todas las subcategorías desde el módulo de subcategorías de vehículo.</p>
                                                                            </div>
                                                                            <div class=\"modal-footer\">
                                                                                <button type=\"button\" class=\"btn btn-danger\" data-bs-dismiss=\"modal\">Cerrar</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>`;
                                                                document.body.insertAdjacentHTML('beforeend', modalHtml);
                                                                var modal = new bootstrap.Modal(document.getElementById('modalErrorCat'));
                                                                modal.show();
                                                                var modalEl = document.getElementById('modalErrorCat');
                                                                modalEl.addEventListener('hidden.bs.modal', function () {
                                                                        window.location.href = 'cat_vehiculo.php';
                                                                });
                                                        }
                                                        if (typeof bootstrap === 'undefined') {
                                                                var script = document.createElement('script');
                                                                script.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js';
                                                                script.onload = showModalErrorCat;
                                                                document.head.appendChild(script);
                                                        } else {
                                                                showModalErrorCat();
                                                        }
                                                </script>";
                                                exit;
                    }
                    // Si no hay subcategorías asociadas, proceder a eliminar
                    $conn->autocommit(false);
                    $conn->begin_transaction();
                    $sql = "DELETE FROM cat_vehic WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('i', $categoria_id);
                    $stmt->execute();
                    $conn->commit();
                    $conn->autocommit(true);
                    error_log("cat_vehiculo.php: Categoría $categoria_id eliminada exitosamente");
                    echo '<script>alert("Categoría eliminada correctamente."); window.location="cat_vehiculo.php";</script>';
                    exit;
                } catch (Exception $e) {
                    $conn->rollback();
                    $conn->autocommit(true);
                    $errorMsg = $e->getMessage();
                    error_log("cat_vehiculo.php: Error al eliminar categoría: " . $errorMsg);
                    // Detectar error de clave foránea (MySQL error 1451)
                    if (strpos($errorMsg, '1451') !== false || stripos($errorMsg, 'foreign key constraint') !== false) {
                        echo '<script>alert("No se puede eliminar la categoría porque está vinculada a una o más subcategorías.\n\nPrimero debe eliminar o reasignar las subcategorías asociadas desde el módulo de subcategorías de vehículo."); window.location=\'cat_vehiculo.php\';</script>';
                    } else {
                        echo '<script>alert("Error al eliminar la categoría: ' . addslashes($errorMsg) . '"); window.location="cat_vehiculo.php";</script>';
                    }
                    exit;
                }
            }
        }
        ?>
        <div class="mt-5 text-end">
            <a href="gestion_vehicular.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver a Gestión</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const categoryModal = document.getElementById('categoryModal');
        const categoryForm = document.getElementById('categoryForm');
        const categoryId = document.getElementById('categoryId');
        const categoryName = document.getElementById('categoryName');
        const modalTitle = document.getElementById('modalTitle');
        const saveBtn = document.getElementById('saveBtn');
        const successToast = document.getElementById('successToast');

        // Abrir modal para nueva categoría
        function openCategoryModal() {
            categoryId.value = '';
            categoryName.value = '';
            modalTitle.innerHTML = '<i class="bi bi-plus-circle"></i> Agregar Categoría';
            categoryModal.classList.add('active');
            document.body.classList.add('modal-active');
            setTimeout(() => categoryName.focus(), 300);
            validateForm();
        }

        // Abrir modal para editar
        function editCategory(id, nombre) {
            categoryId.value = id;
            categoryName.value = nombre;
            modalTitle.innerHTML = '<i class="bi bi-pencil-square"></i> Editar Categoría';
            categoryModal.classList.add('active');
            document.body.classList.add('modal-active');
            setTimeout(() => categoryName.focus(), 300);
            validateForm();
        }

        // Cerrar modal
        function closeCategoryModal() {
            categoryModal.classList.remove('active');
            document.body.classList.remove('modal-active');
            setTimeout(() => {
                categoryForm.reset();
                categoryName.classList.remove('is-invalid');
            }, 300);
        }

        // Cerrar modal al hacer clic fuera
        categoryModal.addEventListener('click', function(e) {
            if (e.target === categoryModal) {
                closeCategoryModal();
            }
        });

        // Cerrar con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && categoryModal.classList.contains('active')) {
                closeCategoryModal();
            }
        });

        // Validación en tiempo real
        categoryName.addEventListener('input', validateForm);
        categoryName.addEventListener('blur', function() {
            if (categoryName.value.trim() === '') {
                categoryName.classList.add('is-invalid');
            } else {
                categoryName.classList.remove('is-invalid');
            }
        });

        function validateForm() {
            const isValid = categoryName.value.trim() !== '';
            saveBtn.disabled = !isValid;
            return isValid;
        }

        // Mostrar toast
        function showToast() {
            successToast.classList.add('show');
            setTimeout(() => {
                successToast.classList.remove('show');
            }, 3000);
        }

        // Enviar formulario con AJAX
        categoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateForm()) return;

            const formData = new FormData(categoryForm);
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

            fetch('cat_vehiculo.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeCategoryModal();
                    showToast();
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    alert('Error al guardar: ' + (data.error || 'Error desconocido'));
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Guardar';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar la categoría');
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Guardar';
            });
        });
    </script>
    <script>
        // Sidebar functions
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('show-mobile');
                const overlay = document.getElementById('sidebar-overlay');
                if (overlay) {
                    overlay.style.display = sidebar.classList.contains('show-mobile') ? 'block' : 'none';
                }
                document.body.style.overflow = sidebar.classList.contains('show-mobile') ? 'hidden' : '';
            }
        }

        function closeSidebarMobile() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar) {
                sidebar.classList.remove('show-mobile');
            }
            if (overlay) {
                overlay.style.display = 'none';
            }
            document.body.style.overflow = '';
        }

        // Initialize sidebar on load
        document.addEventListener('DOMContentLoaded', function() {
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 1024) {
                    closeSidebarMobile();
                }
            });
        });
    </script>
</body>
</html>
