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

if (!$rol_conductor && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'], $_POST['cat_vehic_id'])) {
    header('Content-Type: application/json');
    try {
        if (!empty($_POST['id'])) {
            $sql = "UPDATE subcat_vehic SET nombre = ?, cat_vehic_id = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sii', $_POST['nombre'], $_POST['cat_vehic_id'], $_POST['id']);
            $stmt->execute();
        } else {
            $sql = "INSERT INTO subcat_vehic (nombre, cat_vehic_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $_POST['nombre'], $_POST['cat_vehic_id']);
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
    <title>Subcategoría de Vehículos</title>
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

        .btn:focus {
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.3);
            outline: none;
        }

        .btn-primary {
            background: var(--accent);
            color: #FFFFFF !important;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: #E65100;
            color: #FFFFFF !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
        }

        .btn-success {
            background: var(--success);
            color: #FFFFFF !important;
        }

        .btn-success:hover {
            background: #059669;
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }

        .btn-warning {
            background: var(--accent-amber);
            color: #FFFFFF !important;
        }

        .btn-warning:hover {
            background: #D97706;
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: var(--danger);
            color: #FFFFFF !important;
        }

        .btn-danger:hover {
            background: #DC2626;
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6B7280;
            color: #FFFFFF !important;
        }

        .btn-secondary:hover {
            background: #4B5563;
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }

        .table-responsive {
            border-radius: var(--card-radius);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
            font-size: 14px;
        }

        .table thead th {
            background: var(--accent);
            border: none;
            color: #FFFFFF;
            font-weight: 700;
            padding: 0.75rem 0.5rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table tbody td {
            border-bottom: 1px solid var(--border);
            color: #000;
            padding: 0.75rem 0.5rem;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background: rgba(249, 115, 22, 0.05);
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

        /* Responsive */
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
            .modal-body-custom {
                padding: 1rem;
            }
            .d-flex.gap-2 {
                flex-direction: column;
            }
            .d-flex.gap-2 > * {
                margin-bottom: 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .container-fluid {
                padding: 0.5rem;
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
            <a href="cat_vehiculo.php" class="nav-item">
                <span class="nav-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                </span>
                <span class="nav-item-text">Categorías</span>
                <span class="nav-item-tooltip">Categorías</span>
            </a>
            <a href="subcat_vehiculo.php" class="nav-item active">
                <span class="nav-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path><line x1="12" y1="11" x2="12" y2="17"></line><line x1="9" y1="14" x2="15" y2="14"></line></svg>
                </span>
                <span class="nav-item-text">Subcategorías</span>
                <span class="nav-item-tooltip">Subcategorías</span>
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

    <div class="container-fluid py-5">
        <div class="main-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="bi bi-diagram-3 me-2"></i>Subcategoría de Vehículos</h1>
                    <p class="lead mb-0">Administra las subcategorías de vehículos del sistema</p>
                </div>
                <?php if (!$rol_conductor): ?>
                <button type="button" class="btn btn-success" onclick="openSubcategoryModal()">
                    <i class="bi bi-plus-circle"></i> Agregar Subcategoría
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php
        // Obtener categorías para filtro y formulario
        $catQuery = $conn->query("SELECT id, nombre FROM cat_vehic ORDER BY nombre ASC");
        $categorias = [];
        while ($cat = $catQuery->fetch_assoc()) {
            $categorias[$cat['id']] = $cat['nombre'];
        }
        $filtro_nombre = isset($_GET['filtro_nombre']) ? $_GET['filtro_nombre'] : '';
        $filtro_cat = isset($_GET['filtro_cat']) ? $_GET['filtro_cat'] : '';
        $sql = "SELECT s.id, s.nombre, s.cat_vehic_id, c.nombre AS categoria FROM subcat_vehic s JOIN cat_vehic c ON s.cat_vehic_id = c.id WHERE s.nombre LIKE ?";
        $params = ["%$filtro_nombre%"];
        if ($filtro_cat) {
            $sql .= " AND s.cat_vehic_id = ?";
            $params[] = $filtro_cat;
        }
        $sql .= " ORDER BY s.id DESC";
        $stmt = $conn->prepare($sql);
        if ($filtro_cat) {
            $stmt->bind_param('si', $params[0], $params[1]);
        } else {
            $stmt->bind_param('s', $params[0]);
        }
        $stmt->execute();
        $subcategorias = $stmt->get_result();
        ?>
        <form class="row mb-4" method="get">
            <div class="col-md-4">
                <input type="text" name="filtro_nombre" class="form-control" placeholder="Buscar por nombre" value="<?= htmlspecialchars($filtro_nombre) ?>">
            </div>
            <div class="col-md-4">
                <select name="filtro_cat" class="form-select">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $id => $nombre): ?>
                        <option value="<?= $id ?>" <?= $filtro_cat == $id ? 'selected' : '' ?>><?= htmlspecialchars($nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filtrar</button>
            </div>
            <div class="col-md-2">
                <a href="subcat_vehiculo.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle"></i> Limpiar</a>
            </div>
        </form>
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Categoría de Vehículo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($subcategorias && $subcategorias->num_rows > 0): ?>
                        <?php while ($row = $subcategorias->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['nombre']) ?></td>
                            <td><?= htmlspecialchars($row['categoria']) ?></td>
                            <td class="text-center">
                                <?php if (!$rol_conductor): ?>
                                <button type="button" class="btn btn-warning btn-sm mx-1" onclick="editSubcategory(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nombre'], ENT_QUOTES) ?>', <?= $row['cat_vehic_id'] ?>)"><i class="bi bi-pencil-square"></i> Editar</button>
                                <a href="subcat_vehiculo.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm mx-1" onclick="return confirm('¿Eliminar subcategoría?')"><i class="bi bi-trash"></i> Eliminar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay subcategorías registradas.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal para Agregar/Editar Subcategoría -->
        <div class="modal-overlay" id="subcategoryModal">
            <div class="modal-dialog-custom">
                <div class="modal-header-custom">
                    <h5><i class="bi bi-diagram-3"></i> <span id="modalTitle">Agregar Subcategoría</span></h5>
                    <button type="button" class="modal-close-btn" onclick="closeSubcategoryModal()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <form id="subcategoryForm">
                    <input type="hidden" name="id" id="subcategoryId">
                    <div class="modal-body-custom">
                        <div class="mb-3">
                            <label class="form-label">Nombre de la Subcategoría <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="nombre" 
                                id="subcategoryName"
                                class="form-control" 
                                required 
                                placeholder="Ej: Vehículo de 2 toneladas"
                            >
                            <div class="invalid-feedback" id="nameError">El nombre es obligatorio</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Categoría de Vehículo <span class="text-danger">*</span></label>
                            <select name="cat_vehic_id" id="subcategoryCategory" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($categorias as $id => $nombre): ?>
                                    <option value="<?= $id ?>"><?= htmlspecialchars($nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="categoryError">Debe seleccionar una categoría</div>
                        </div>
                    </div>
                    <div class="modal-footer-custom">
                        <button type="button" class="btn btn-secondary" onclick="closeSubcategoryModal()">
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
                <div class="small">Subcategoría guardada correctamente</div>
            </div>
        </div>
        <?php
        // Eliminar subcategoría
        if (!$rol_conductor) {
            if (isset($_GET['delete'])) {
                try {
                    $subcat_id = $_GET['delete'];
                    
                    // PASO 1: Verificar si la subcategoría tiene vehículos asociados
                    $check_vehiculos_sql = "SELECT COUNT(*) as total FROM regis_vehic WHERE subcat_vehic_id = ?";
                    $check_vehiculos_stmt = $conn->prepare($check_vehiculos_sql);
                    $check_vehiculos_stmt->bind_param('i', $subcat_id);
                    $check_vehiculos_stmt->execute();
                    $check_result = $check_vehiculos_stmt->get_result();
                    $vehiculos_count = $check_result->fetch_assoc()['total'];
                    
                    // Si hay vehículos asociados, no permitir la eliminación
                    if ($vehiculos_count > 0) {
                        echo "<script>
                            alert('No se puede eliminar la subcategoría porque tiene $vehiculos_count vehículo(s) asociado(s).\\n\\nPrimero debe:\\n- Reasignar los vehículos a otra subcategoría, o\\n- Eliminar los vehículos individualmente desde el módulo de vehículos');
                            window.location.href = 'subcat_vehiculo.php';
                        </script>";
                        exit;
                    }
                    
                    // PASO 2: Eliminar directamente la subcategoría (ya verificamos que no tiene vehículos)
                    $sql = "DELETE FROM subcat_vehic WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('i', $subcat_id);
                    $stmt->execute();
                    
                    if ($stmt->affected_rows > 0) {
                        error_log("subcat_vehiculo.php: Subcategoría $subcat_id eliminada exitosamente (eliminación simple)");
                        echo '<script>alert("Subcategoría eliminada correctamente."); window.location="subcat_vehiculo.php";</script>';
                    } else {
                        error_log("subcat_vehiculo.php: No se pudo eliminar la subcategoría $subcat_id");
                        echo '<script>alert("Error: No se pudo eliminar la subcategoría."); window.location="subcat_vehiculo.php";</script>';
                    }
                    exit;
                    
                } catch (Exception $e) {
                    error_log("subcat_vehiculo.php: Error al eliminar subcategoría: " . $e->getMessage());
                    echo '<script>alert("Error al eliminar la subcategoría: ' . addslashes($e->getMessage()) . '"); window.location="subcat_vehiculo.php";</script>';
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
        const subcategoryModal = document.getElementById('subcategoryModal');
        const subcategoryForm = document.getElementById('subcategoryForm');
        const subcategoryId = document.getElementById('subcategoryId');
        const subcategoryName = document.getElementById('subcategoryName');
        const subcategoryCategory = document.getElementById('subcategoryCategory');
        const modalTitle = document.getElementById('modalTitle');
        const saveBtn = document.getElementById('saveBtn');
        const successToast = document.getElementById('successToast');

        function openSubcategoryModal() {
            subcategoryId.value = '';
            subcategoryName.value = '';
            subcategoryCategory.value = '';
            modalTitle.innerHTML = '<i class="bi bi-plus-circle"></i> Agregar Subcategoría';
            subcategoryModal.classList.add('active');
            setTimeout(() => subcategoryName.focus(), 300);
            validateForm();
        }

        function editSubcategory(id, nombre, catId) {
            subcategoryId.value = id;
            subcategoryName.value = nombre;
            subcategoryCategory.value = catId;
            modalTitle.innerHTML = '<i class="bi bi-pencil-square"></i> Editar Subcategoría';
            subcategoryModal.classList.add('active');
            setTimeout(() => subcategoryName.focus(), 300);
            validateForm();
        }

        function closeSubcategoryModal() {
            subcategoryModal.classList.remove('active');
            setTimeout(() => {
                subcategoryForm.reset();
                subcategoryName.classList.remove('is-invalid');
                subcategoryCategory.classList.remove('is-invalid');
            }, 300);
        }

        subcategoryModal.addEventListener('click', function(e) {
            if (e.target === subcategoryModal) {
                closeSubcategoryModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && subcategoryModal.classList.contains('active')) {
                closeSubcategoryModal();
            }
        });

        subcategoryName.addEventListener('input', validateForm);
        subcategoryCategory.addEventListener('change', validateForm);

        subcategoryName.addEventListener('blur', function() {
            if (subcategoryName.value.trim() === '') {
                subcategoryName.classList.add('is-invalid');
            } else {
                subcategoryName.classList.remove('is-invalid');
            }
        });

        subcategoryCategory.addEventListener('blur', function() {
            if (subcategoryCategory.value === '') {
                subcategoryCategory.classList.add('is-invalid');
            } else {
                subcategoryCategory.classList.remove('is-invalid');
            }
        });

        function validateForm() {
            const isValid = subcategoryName.value.trim() !== '' && subcategoryCategory.value !== '';
            saveBtn.disabled = !isValid;
            return isValid;
        }

        function showToast() {
            successToast.classList.add('show');
            setTimeout(() => {
                successToast.classList.remove('show');
            }, 3000);
        }

        subcategoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateForm()) return;

            const formData = new FormData(subcategoryForm);
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

            fetch('subcat_vehiculo.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeSubcategoryModal();
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
                alert('Error al guardar la subcategoría');
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
