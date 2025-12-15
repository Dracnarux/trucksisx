<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión Vehicular | TruckSISX</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
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

        .module-card {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid var(--border);
            border-radius: var(--card-radius);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(249, 115, 22, 0.15);
            border-color: var(--accent);
        }
        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent);
            border-radius: var(--card-radius) var(--card-radius) 0 0;
        }
        .module-card .card-body {
            padding: 2rem;
            text-align: center;
        }
        .module-icon {
            background: var(--accent);
            border-radius: 50%;
            color: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            height: 80px;
            margin-bottom: 1rem;
            width: 80px;
            box-shadow: 0 8px 20px rgba(249, 115, 22, 0.3);
        }
        .module-card h5 {
            color: #000;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        .module-card p {
            color: #374151;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
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
            <a href="gestion_vehicular.php" class="nav-item active">
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
            <a href="cat_vehiculo.php" class="nav-item">
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

    <div class="container-fluid py-4">
        <div class="main-header">
            <h1><i class="bi bi-gear-wide-connected me-2"></i>Gestión Vehicular</h1>
            <p class="lead mb-0">Administra categorías, subcategorías, vehículos y conductores</p>
        </div>

        <div class="row g-4">
            <!-- Categorías de Vehículos -->
            <div class="col-lg-4 col-md-6">
                <div class="card module-card h-100">
                    <div class="card-body">
                        <div class="module-icon">
                            <i class="bi bi-folder2-open"></i>
                        </div>
                        <h5>Categorías de Vehículos</h5>
                        <p>Administra las categorías principales de vehículos de la flota</p>
                        <a href="cat_vehiculo.php" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Acceder
                        </a>
                    </div>
                </div>
            </div>

            <!-- Subcategorías de Vehículos -->
            <div class="col-lg-4 col-md-6">
                <div class="card module-card h-100">
                    <div class="card-body">
                        <div class="module-icon">
                            <i class="bi bi-diagram-3"></i>
                        </div>
                        <h5>Subcategorías de Vehículos</h5>
                        <p>Gestiona las subcategorías y especificaciones de vehículos</p>
                        <a href="subcat_vehiculo.php" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Acceder
                        </a>
                    </div>
                </div>
            </div>

            <!-- Registro de Vehículos -->
            <div class="col-lg-4 col-md-6">
                <div class="card module-card h-100">
                    <div class="card-body">
                        <div class="module-icon">
                            <i class="bi bi-truck"></i>
                        </div>
                        <h5>Registro de Vehículos</h5>
                        <p>Registra y administra todos los vehículos de la flota</p>
                        <a href="regis_vehic.php" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Acceder
                        </a>
                    </div>
                </div>
            </div>

            <!-- Conductores -->
            <div class="col-lg-4 col-md-6">
                <div class="card module-card h-100">
                    <div class="card-body">
                        <div class="module-icon">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <h5>Conductores</h5>
                        <p>Gestiona conductores y asignación de vehículos</p>
                        <a href="cond.php" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Acceder
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Volver al Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
