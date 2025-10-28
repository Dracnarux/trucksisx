<?php
session_start();

// Validar sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}

// Validar tiempo de sesión (opcional: 8 horas)
$session_timeout = 8 * 60 * 60; // 8 horas
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $session_timeout) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['logout_message'] = 'Sesión expirada por inactividad';
    header('Location: ../index.php');
    exit();
}

// Validar IP de sesión (seguridad adicional)
if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['logout_message'] = 'Sesión invalidada por razones de seguridad';
    header('Location: ../index.php');
    exit();
}

$usuario = $_SESSION['usuario'];

// Actualizar último acceso
$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Trucksisx</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ========== RESET Y BASE ========== */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* ========== TIPOGRAFÍA ========== */
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
            line-height: 1.3;
            margin-bottom: 1rem;
        }

        h1 {
            font-size: clamp(1.75rem, 4vw, 2.5rem);
            font-weight: 700;
        }

        h2 {
            font-size: clamp(1.5rem, 3vw, 2rem);
        }

        h3 {
            font-size: clamp(1.25rem, 2.5vw, 1.5rem);
        }

        /* ========== LAYOUT - SIDEBAR ========== */
        .sidebar {
            background: linear-gradient(180deg, #1E3A8A 0%, #3B82F6 100%);
            border-right: 1px solid rgba(30, 58, 138, 0.2);
            box-shadow: 4px 0 20px rgba(30, 58, 138, 0.15);
            color: #FFFFFF;
            height: 100vh;
            min-width: 260px;
            position: fixed;
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .sidebar.hidden-desktop {
            transform: translateX(-100%);
        }

        .sidebar-header {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.2) 0%, rgba(245, 158, 11, 0.2) 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
        }

        .sidebar-brand {
            color: #FBBF24;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
        }

        .sidebar-brand:hover {
            color: #F59E0B;
        }

        .nav-link {
            border-radius: 8px;
            color: rgba(255, 255, 255, 0.8) !important;
            font-weight: 500;
            margin-bottom: 0.25rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(251, 191, 36, 0.2);
            color: #FFFFFF !important;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            color: #1E3A8A !important;
            font-weight: 600;
        }

        .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
        }

        /* ========== LAYOUT - CONTENIDO PRINCIPAL ========== */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        .content-expanded {
            margin-left: 0 !important;
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

        .main-header::before {
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2"/></svg>');
            content: '';
            height: 200px;
            opacity: 0.1;
            position: absolute;
            right: -50px;
            top: -50px;
            width: 200px;
        }

        /* ========== COMPONENTES - CARDS ========== */
        .card {
            background: #FFFFFF;
            border: 1px solid rgba(209, 213, 219, 0.3);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            transform: translateY(-4px);
        }

        .module-card {
            cursor: pointer;
            position: relative;
        }

        .module-card::before {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.1) 0%, rgba(30, 58, 138, 0.1) 100%);
            border-radius: 12px;
            content: '';
            height: 100%;
            left: 0;
            opacity: 0;
            position: absolute;
            top: 0;
            transition: opacity 0.3s ease;
            width: 100%;
            z-index: 1;
        }

        .module-card:hover::before {
            opacity: 1;
        }

        .card-body {
            padding: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .card-title {
            color: #1E3A8A;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .card-text {
            color: #6B7280;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        /* ========== COMPONENTES - BOTONES ========== */
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

        .btn-outline-primary {
            background: #FFFFFF;
            border: 2px solid #1E3A8A;
            color: #1E3A8A !important;
        }

        .btn-outline-primary:hover {
            background: #1E3A8A;
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }

        /* ========== ANIMACIONES ========== */
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

        @keyframes flipIn {
            0% {
                transform: rotateY(90deg);
                opacity: 0;
            }
            60% {
                transform: rotateY(-10deg);
                opacity: 1;
            }
            80% {
                transform: rotateY(10deg);
            }
            100% {
                transform: rotateY(0deg);
                opacity: 1;
            }
        }

        .module-card {
            animation: flipIn 0.7s cubic-bezier(0.4,0,0.2,1) forwards;
            opacity: 0;
            transform: rotateY(90deg);
        }

        /* ========== RESPONSIVIDAD ========== */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
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
                width: 100%;
            }

            h1 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 0.5rem;
            }

            .main-header {
                padding: 1rem;
            }

            .card {
                margin-bottom: 1rem;
            }

            .module-card .card-body {
                padding: 1rem;
                text-align: center;
            }
        }

        /* ========== UTILIDADES ========== */
        .text-corporate {
            color: #1E3A8A !important;
        }

        .text-accent {
            color: #FBBF24 !important;
        }

        .bg-corporate {
            background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%) !important;
        }

        .shadow-corporate {
            box-shadow: 0 4px 16px rgba(30, 58, 138, 0.15) !important;
        }

        .border-corporate {
            border-color: #1E3A8A !important;
        }

        /* ========== COMPONENTES ESPECIALES ========== */
        .user-info {
            background: rgba(251, 191, 36, 0.1);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 8px;
            color: #1E3A8A;
            padding: 0.75rem 1rem;
        }

        .user-role {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            border-radius: 20px;
            color: #1E3A8A;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
        }

        .module-icon {
            color: #FBBF24;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .stats-number {
            color: #1E3A8A;
            font-size: 2rem;
            font-weight: 700;
        }

        .overlay {
            background: rgba(0, 0, 0, 0.5);
            display: none;
            height: 100vh;
            left: 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 999;
        }

        .overlay.show {
            display: block;
        }
            
            .sidebar .nav-link:hover {
                background: rgba(13,110,253,0.2) !important;
                color: #0d6efd !important;
                font-weight: 600 !important;
            }
            
            /* Encabezado del sidebar móvil */
            .sidebar h5 {
                color: #111 !important;
                font-weight: 700 !important;
                background: rgba(13,110,253,0.15) !important;
                padding: 0.75rem 1rem !important;
                border-radius: 0.5rem !important;
                margin-bottom: 1rem !important;
                border: 1px solid rgba(13,110,253,0.3) !important;
            }
            
            /* Botón de cerrar sidebar móvil */
            .sidebar #closeSidebar {
                background: rgba(220,53,69,0.1) !important;
                border-color: rgba(220,53,69,0.3) !important;
                color: #dc3545 !important;
        }
        
        .card-title.module-blue,
        .card.module-blue .card-text,
        .card.module-blue .btn,
        .card.module-blue a,
        .card.module-blue h6,
        .card.module-blue h5 {
            color: #111 !important;
        }
        .navbar-brand {
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        /* ===== DISEÑO RESPONSIVE PARA MÓVILES ===== */
        
        /* Breakpoints para móviles */
        @media (max-width: 768px) {
            /* Layout principal para móviles */
            .sidebar {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 280px !important;
                height: 100vh !important;
                z-index: 1050 !important;
                transform: translateX(-100%) !important;
                transition: transform 0.3s ease !important;
            }
            
            /* Sidebar visible en móviles */
            .sidebar.show-mobile {
                transform: translateX(0) !important;
            }
            
            /* Main content ajuste para móviles */
            main.flex-fill {
                width: 100% !important;
                margin-left: 0 !important;
            }
            
            /* Backdrop para sidebar móvil */
            .sidebar.show-mobile::before {
                content: '';
                position: fixed;
                top: 0;
                left: 280px;
                width: calc(100vw - 280px);
                height: 100vh;
                background: rgba(0,0,0,0.5);
                z-index: -1;
            }
            
            /* Main content */
            .flex-fill {
                padding: 1rem !important;
            }
            
            /* Navbar móvil */
            .navbar-text {
                display: none !important;
            }
            
            .dropdown .nav-link {
                font-size: 0.9rem !important;
                padding: 0.5rem !important;
            }
            
            .badge {
                font-size: 0.7rem !important;
            }
            
            /* Cards uniformes en móvil */
            .uniform-card {
                min-height: 180px !important;
                margin-bottom: 1rem !important;
            }
            
            .uniform-small-card {
                min-height: 140px !important;
                margin-bottom: 1rem !important;
            }
            
            .card-icon {
                font-size: 2.5rem !important;
                margin-bottom: 0.75rem !important;
            }
            
            .card-icon-small {
                font-size: 1.8rem !important;
                margin-bottom: 0.5rem !important;
            }
            
            /* Espaciado móvil */
            .dashboard-section {
                margin-bottom: 1.5rem !important;
            }
            
            .uniform-row {
                margin-bottom: 1rem !important;
            }
            
            /* Títulos móviles */
            h2 {
                font-size: 1.5rem !important;
            }
            
            h4 {
                font-size: 1.25rem !important;
                margin-bottom: 1rem !important;
            }
            
            h5.card-title {
                font-size: 1.1rem !important;
            }
            
            h6.card-title {
                font-size: 1rem !important;
            }
            
            /* Cards de estadísticas */
            .card-body h3 {
                font-size: 1.8rem !important;
            }
            
            .card-body h6 {
                font-size: 0.9rem !important;
            }
            
            /* Botones móviles */
            .btn {
                padding: 0.5rem 1rem !important;
                font-size: 0.9rem !important;
            }
            
            .btn-sm {
                padding: 0.375rem 0.75rem !important;
                font-size: 0.8rem !important;
            }
            
            /* Texto móvil */
            .card-text {
                font-size: 0.9rem !important;
                line-height: 1.4 !important;
            }
            
            /* Grid móvil - 1 columna para cards principales */
            .row.uniform-row .col-md-4 {
                margin-bottom: 1rem !important;
            }
            
            /* Grid móvil - 2 columnas para cards pequeños */
            .row g-3 .col-lg-3 {
                width: 50% !important;
                padding: 0.5rem !important;
            }
        }
        
        /* Tablets */
        @media (min-width: 769px) and (max-width: 992px) {
            .sidebar {
                min-width: 180px !important;
            }
            
            .flex-fill {
                padding: 2rem !important;
            }
            
            .uniform-card {
                min-height: 200px !important;
            }
            
            .card-icon {
                font-size: 3rem !important;
            }
        }
        
        /* Estilos base para cards uniformes */
        .uniform-card {
            min-height: 220px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid rgba(13,110,253,0.2);
        }
        
        .uniform-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        
        .uniform-card .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            padding: 1.5rem;
        }
        
        .uniform-card .card-text {
            flex-grow: 1;
            margin-bottom: 1rem;
        }
        
        .uniform-small-card {
            min-height: 160px;
            transition: transform 0.2s ease;
        }
        
        .uniform-small-card:hover {
            transform: translateY(-2px);
        }
        
        .uniform-small-card .card-body {
            padding: 1.25rem;
        }
        
        .dashboard-section {
            margin-bottom: 2.5rem;
        }
        
        .card-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            opacity: 0.8;
            display: block;
        }
        
        .card-icon-small {
            font-size: 2.2rem;
            margin-bottom: 0.75rem;
            opacity: 0.8;
        }
        
        .uniform-row {
            margin-bottom: 2rem;
        }
        
        .uniform-row .col-md-4 {
            margin-bottom: 1rem;
        }
        
        /* ===== NUEVAS UTILIDADES PARA ESTADÍSTICAS Y ICONOS ===== */
        .stats-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: stretch;
            justify-content: center;
        }

        .stat-card {
            min-width: 200px;
            flex: 1 1 220px;
            border-radius: 1rem;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            color: #fff;
        }

        .stat-icon {
            font-size: 2.25rem;
            margin-bottom: 0.5rem;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.12));
            opacity: 0.95;
        }

        .stat-label {
            font-size: 0.95rem;
            font-weight: 600;
            opacity: 0.95;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
        }

        /* Ajustes para módulos: iconos más pequeños y centrados */
        .module-card .card-body { padding: 1.25rem; }
        .module-badge {
            width: 64px;
            height: 64px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            margin-bottom: 0.75rem;
        }
        .module-icon-sm { font-size: 1.35rem; }
    .badge-gradient-yellow { background: linear-gradient(135deg,#fbbf24 0%,#f59e0b 100%); }
    .badge-gradient-blue { background: linear-gradient(135deg,#3b82f6 0%,#1e3a8a 100%); }
    .badge-gradient-green { background: linear-gradient(135deg,#22c55e 0%,#16a34a 100%); }
    .badge-gradient-teal { background: linear-gradient(135deg,#22d3ee 0%,#0ea5e9 100%); }
    .badge-gradient-danger { background: linear-gradient(135deg,#f87171 0%,#ef4444 100%); }
    .badge-gradient-gray { background: linear-gradient(135deg,#a3a3a3 0%,#525252 100%); }
    .badge-gradient-purple { background: linear-gradient(135deg,#a78bfa 0%,#7c3aed 100%); }
    .header-truck { height: 80px; filter: drop-shadow(0 2px 8px rgba(0,0,0,0.12)); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-0">
        <div class="container-fluid">
            <!-- Botón para mostrar menú (cuando esté oculto en desktop) -->
            <button class="btn btn-outline-light me-2 d-inline-block" type="button" id="showSidebar" title="Mostrar menú">
                <i class="bi bi-chevron-right"></i>
            </button>
            
            <a class="navbar-brand" href="dashboard.php"><i class="bi bi-truck"></i> Trucksisx</a>
            <span class="navbar-text d-none d-md-inline">Panel principal</span>
            
            <!-- Botón hamburger para menú lateral en móviles -->
            <button class="btn btn-outline-light d-lg-none" type="button" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            
            <div class="dropdown ms-auto">
                <a class="nav-link dropdown-toggle text-white" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle"></i> 
                    <span class="d-none d-sm-inline"><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></span>
                    <span class="d-sm-none"><?= htmlspecialchars($usuario['nombre']) ?></span>
                    <span class="badge bg-light text-primary ms-1"><?= htmlspecialchars($usuario['rol']) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> Mi Perfil</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> Configuración</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="d-flex">
        <nav class="sidebar p-3 hidden-desktop" id="sidebar">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-grid-3x3-gap"></i> <span>Menú Rápido</span></h5>
                <button class="btn btn-sm btn-outline-primary" id="closeSidebar" title="Ocultar menú">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>
            <ul class="nav flex-column" id="menu-options">
                <li class="nav-item mb-2">
                    <a class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='cond.php') echo ' active'; ?>" href="cond.php">
                        <i class="bi bi-person-badge"></i> 
                        <span>Conductores</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='gestiones.php') echo ' active'; ?>" href="gestiones.php">
                        <i class="bi bi-collection"></i> 
                        <span>Gestiones</span>
                    </a>
                </li>
                <?php if($usuario['rol'] == 'admin'): ?>
                <li class="nav-item mb-2">
                    <a class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='crear_usuario.php') echo ' active'; ?>" href="crear_usuario.php">
                        <i class="bi bi-people"></i> 
                        <span>Usuarios</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item mb-2">
                    <a class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='truck_alerts.php') echo ' active'; ?>" href="truck_alerts.php">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <span>Alertas</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link text-danger" href="../logout.php">
                        <i class="bi bi-box-arrow-right"></i> 
                        <span>Salir</span>
                    </a>
                </li>
            </ul>
        </nav>
        <main class="flex-fill p-4">
            <!-- Banner visual de bienvenida -->
            <div class="main-header animate-slide-up mb-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
                    <div>
                        <h1 class="mb-2">¡Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?>!</h1>
                        <span class="user-role ms-1">Rol: <?= htmlspecialchars($usuario['rol']) ?></span>
                        <p class="mt-2 mb-0 text-white-50">Último acceso: <?= isset($_SESSION['last_activity']) ? date('d/m/Y H:i:s', $_SESSION['last_activity']) : 'Ahora' ?></p>
                    </div>
                        <div class="d-none d-md-block">
                        <img src="https://cdn-icons-png.flaticon.com/512/1995/1995476.png" alt="Truck" class="img-fluid header-truck">
                    </div>
                </div>
            </div>
            
            <!-- Estadísticas del Dashboard -->
            <?php
            try {
                require_once '../config/db.php';
                $database = new Database();
                $db = $database->getConnection();
                
                // Obtener estadísticas
                $stats = [];
                
                // Total de vehículos
                $stmt = $db->prepare("SELECT COUNT(*) as total FROM regis_vehic");
                $stmt->execute();
                $stats['vehiculos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Total de alertas activas
                $stmt = $db->prepare("SELECT COUNT(*) as total FROM alert WHERE estado = 'activa'");
                $stmt->execute();
                $stats['alertas_activas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Total de órdenes de trabajo pendientes
                $stmt = $db->prepare("SELECT COUNT(*) as total FROM ord_trabj WHERE estado IN ('pendiente', 'en_proceso')");
                $stmt->execute();
                $stats['ordenes_pendientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Total de conductores
                $stmt = $db->prepare("SELECT COUNT(*) as total FROM cond");
                $stmt->execute();
                $stats['conductores'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
            } catch (Exception $e) {
                // Si hay error de conexión, usar valores por defecto
                $stats = [
                    'vehiculos' => 0,
                    'alertas_activas' => 0,
                    'ordenes_pendientes' => 0,
                    'conductores' => 0
                ];
            }
            ?>
            
            <!-- Cards de Estadísticas con nuevo layout -->
            <div class="mb-4">
                <div class="stats-grid">
                    <div class="stat-card bg-primary text-white">
                        <div class="stat-icon"><i class="bi bi-truck"></i></div>
                        <div class="stat-label">Vehículos</div>
                        <div class="stat-number"><?= $stats['vehiculos'] ?></div>
                    </div>
                    <div class="stat-card bg-danger text-white">
                        <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                        <div class="stat-label">Alertas Activas</div>
                        <div class="stat-number"><?= $stats['alertas_activas'] ?></div>
                    </div>
                    <div class="stat-card bg-warning text-dark">
                        <div class="stat-icon"><i class="bi bi-clipboard-check"></i></div>
                        <div class="stat-label">Órdenes Pendientes</div>
                        <div class="stat-number"><?= $stats['ordenes_pendientes'] ?></div>
                    </div>
                    <div class="stat-card bg-success text-white">
                        <div class="stat-icon"><i class="bi bi-person-badge"></i></div>
                        <div class="stat-label">Conductores</div>
                        <div class="stat-number"><?= $stats['conductores'] ?></div>
                    </div>
                </div>
            </div>
            <!-- Módulos Principales -->
            <div class="row g-4 dashboard-section uniform-row">
                <div class="col-12">
                    <h4 class="fw-bold mb-4"><i class="bi bi-grid-3x3-gap"></i> Módulos Principales</h4>
                </div>
                
                <!-- Sistema de Alertas -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card module-blue text-center uniform-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-center mb-2">
                                <span class="module-badge badge-gradient-yellow">
                                    <i class="bi bi-exclamation-triangle module-icon-sm text-white"></i>
                                </span>
                            </div>
                            <h5 class="card-title module-blue">Sistema de Alertas</h5>
                            <p class="card-text">Monitorea y gestiona alertas de camiones doble troque. Diagrama interactivo y órdenes de trabajo automáticas.</p>
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <a href="truck_alerts.php" class="btn btn-danger">
                                    <i class="bi bi-arrow-right-circle"></i> Acceder
                                </a>
                                <?php if($usuario['rol'] == 'admin'): ?>
                                <a href="../system_status.php" class="btn btn-outline-danger" title="Estado del Sistema">
                                    <i class="bi bi-gear"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Salida de Repuestos -->
                <?php if($usuario['rol'] != 'conductor'): ?>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card module-blue text-center uniform-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-center mb-2">
                                <span class="module-badge badge-gradient-blue">
                                    <i class="bi bi-box-arrow-right module-icon-sm text-white"></i>
                                </span>
                            </div>
                            <h5 class="card-title module-blue">Salida de Repuestos</h5>
                            <p class="card-text">Registrar la salida obligatoria y secuencial de repuestos del inventario.</p>
                            <a href="salida_repuesto.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Registrar Salida
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Salida de Vehículo -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card module-blue text-center uniform-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-center mb-2">
                                <span class="module-badge badge-gradient-green">
                                    <i class="bi bi-truck module-icon-sm text-white"></i>
                                </span>
                            </div>
                            <h5 class="card-title module-blue">Salida de Vehículo</h5>
                            <p class="card-text">Registrar la salida de vehículo después de completar la salida de repuestos.</p>
                            <a href="salida_vehiculo.php" class="btn btn-success">
                                <i class="bi bi-truck-front"></i> Registrar Salida
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Órdenes de Trabajo -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card module-blue text-center uniform-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-center mb-2">
                                <span class="module-badge badge-gradient-yellow">
                                    <i class="bi bi-clipboard-check module-icon-sm text-white"></i>
                                </span>
                            </div>
                            <h5 class="card-title module-blue">Órdenes de Trabajo</h5>
                            <p class="card-text">Gestiona las órdenes de trabajo generadas automáticamente por las alertas.</p>
                            <a href="orden_trabajo.php" class="btn btn-warning">
                                <i class="bi bi-list-task"></i> Ver Órdenes
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Gestión de Usuarios (Solo Admin) -->
                <?php if($usuario['rol'] == 'admin'): ?>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card module-blue text-center uniform-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-center mb-2">
                                <span class="module-badge badge-gradient-teal">
                                    <i class="bi bi-people module-icon-sm text-white"></i>
                                </span>
                            </div>
                            <h5 class="card-title module-blue">Gestión de Usuarios</h5>
                            <p class="card-text">Administra usuarios del sistema: conductores, técnicos y administradores.</p>
                            <a href="crear_usuario.php" class="btn btn-info">
                                <i class="bi bi-person-plus"></i> Gestionar
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Sección de Reportes y Gestiones -->
            <div class="row g-4 dashboard-section uniform-row">
                <div class="col-12">
                    <h4 class="fw-bold mb-4"><i class="bi bi-bar-chart"></i> Reportes y Gestiones</h4>
                </div>
                
                <div class="col-lg-6 col-md-12">
                    <div class="card module-blue text-center uniform-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-center mb-2">
                                <span class="module-badge badge-gradient-gray">
                                    <i class="bi bi-file-earmark-text module-icon-sm text-white"></i>
                                </span>
                            </div>
                            <h5 class="card-title module-blue">Reportes del Sistema</h5>
                            <p class="card-text">Ver reportes consolidados de salidas de repuestos y vehículos del sistema.</p>
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <a href="reporte_salidas.php" class="btn btn-secondary">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> Salidas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 col-md-12">
                    <div class="card module-blue text-center uniform-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-center mb-2">
                                <span class="module-badge badge-gradient-purple">
                                    <i class="bi bi-collection module-icon-sm text-white"></i>
                                </span>
                            </div>
                            <h5 class="card-title module-blue">Panel de Gestiones</h5>
                            <p class="card-text">Acceso centralizado a todas las gestiones de repuestos, categorías y configuraciones.</p>
                            <a href="gestiones.php" class="btn btn-outline-primary">
                                <i class="bi bi-gear"></i> Ir a Gestiones
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Gestión Vehicular -->
            <div class="row g-4 dashboard-section">
                <div class="col-12">
                    <h4 class="fw-bold mb-4 text-success"><i class="bi bi-truck"></i> Gestión Vehicular</h4>
                    <p class="text-muted mb-4">Administra vehículos, categorías y conductores del sistema de manera integral.</p>
                </div>
                
                <div class="col-12">
                    <div class="card module-blue">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="card uniform-small-card text-center border-success">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-center mb-2">
                                                <span class="module-badge badge-gradient-green" style="width:48px; height:48px;">
                                                    <i class="bi bi-bookmarks-fill module-icon-sm text-white"></i>
                                                </span>
                                            </div>
                                            <h6 class="card-title">Categorías</h6>
                                            <p class="card-text small">Gestiona categorías de vehículos</p>
                                            <a href="cat_vehiculo.php" class="btn btn-outline-success btn-sm">
                                                <i class="bi bi-arrow-right-circle"></i> Acceder
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="card uniform-small-card text-center border-success">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-center mb-2">
                                                <span class="module-badge badge-gradient-teal" style="width:48px; height:48px;">
                                                    <i class="bi bi-diagram-3-fill module-icon-sm text-white"></i>
                                                </span>
                                            </div>
                                            <h6 class="card-title">Subcategorías</h6>
                                            <p class="card-text small">Gestiona subcategorías de vehículos</p>
                                            <a href="subcat_vehiculo.php" class="btn btn-outline-info btn-sm">
                                                <i class="bi bi-arrow-right-circle"></i> Acceder
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="card uniform-small-card text-center border-success">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-center mb-2">
                                                <span class="module-badge badge-gradient-yellow" style="width:48px; height:48px;">
                                                    <i class="bi bi-journal-plus module-icon-sm text-white"></i>
                                                </span>
                                            </div>
                                            <h6 class="card-title">Registro</h6>
                                            <p class="card-text small">Registra vehículos individuales</p>
                                            <a href="regis_vehic.php" class="btn btn-outline-warning btn-sm">
                                                <i class="bi bi-arrow-right-circle"></i> Acceder
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="card uniform-small-card text-center border-success">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-center mb-2">
                                                <span class="module-badge badge-gradient-danger" style="width:48px; height:48px;">
                                                    <i class="bi bi-person-badge module-icon-sm text-white"></i>
                                                </span>
                                            </div>
                                            <h6 class="card-title">Conductores</h6>
                                            <p class="card-text small">Gestiona conductores y datos</p>
                                            <a href="cond.php" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-arrow-right-circle"></i> Acceder
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar - lógica simplificada y robusta
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const showSidebar = document.getElementById('showSidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const closeSidebar = document.getElementById('closeSidebar');
            const mainContent = document.querySelector('main.flex-fill');

            function updateShowButton() {
                if (!showSidebar) return;
                // Mostrar el botón solo cuando el sidebar está oculto en desktop
                const isHiddenDesktop = sidebar && sidebar.classList.contains('hidden-desktop');
                showSidebar.style.display = (window.innerWidth > 991 && isHiddenDesktop) ? 'inline-block' : 'none';
            }

            function openDesktopSidebar() {
                if (!sidebar) return;
                sidebar.classList.remove('hidden-desktop');
                if (mainContent) mainContent.classList.remove('content-expanded');
                updateShowButton();
            }

            function closeDesktopSidebar() {
                if (!sidebar) return;
                sidebar.classList.add('hidden-desktop');
                if (mainContent) mainContent.classList.add('content-expanded');
                updateShowButton();
            }

            function openMobileSidebar() {
                if (!sidebar) return;
                sidebar.classList.add('show-mobile');
                document.body.style.overflow = 'hidden';
            }

            function closeMobileSidebar() {
                if (!sidebar) return;
                sidebar.classList.remove('show-mobile');
                document.body.style.overflow = 'auto';
            }

            // Click en el botón mostrar (desktop)
            if (showSidebar) {
                showSidebar.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openDesktopSidebar();
                });
            }

            // Click en el botón hamburger (móvil)
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openMobileSidebar();
                });
            }

            // Click en cerrar sidebar
            if (closeSidebar) {
                closeSidebar.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (window.innerWidth <= 991) closeMobileSidebar(); else closeDesktopSidebar();
                });
            }

            // Clic fuera del sidebar cierra (móvil)
            document.addEventListener('click', function (e) {
                if (window.innerWidth <= 991 && sidebar && sidebar.classList.contains('show-mobile')) {
                    if (!sidebar.contains(e.target) && !(sidebarToggle && sidebarToggle.contains(e.target))) {
                        closeMobileSidebar();
                    }
                }
            });

            // Al cambiar tamaño de ventana, actualizar estado
            window.addEventListener('resize', function () {
                if (window.innerWidth > 991) {
                    // cerrar mobile overlay si hay
                    closeMobileSidebar();
                    // mantener comportamiento por defecto: si no tiene hidden-desktop, mostrar sidebar
                    if (!sidebar.classList.contains('hidden-desktop')) {
                        openDesktopSidebar();
                    }
                } else {
                    // en móvil, ocultar el botón showSidebar
                    updateShowButton();
                }
                updateShowButton();
            });

            // Inicializar visibilidad del botón
            updateShowButton();
            console.log('🚛 TruckSISX - Sidebar control inicializado');
        });
    </script>
    
    <style>
        /* ===== ESTILOS ADICIONALES MÓVILES ===== */
        
        /* Asegurar que el menú sea visible por defecto */
        #menu-options {
            display: flex !important;
            flex-direction: column !important;
            opacity: 1 !important;
            max-height: none !important;
            overflow: visible !important;
        }
        
        /* Mejorar la visualización del botón hamburger y textos del menú */
        #sidebarToggle {
            display: none;
        }
        
        @media (max-width: 991px) {
            #sidebarToggle {
                display: inline-block !important;
            }
            
            /* Hacer visible el texto del menú en móviles */
            .sidebar .nav-link span {
                display: inline-block !important;
                color: #FFFFFF !important;
                font-size: 1rem !important;
                margin-left: 0.5rem !important;
            }
            
            /* Mejorar el contraste de los íconos */
            .sidebar .nav-link i {
                font-size: 1.2rem !important;
                color: #FFFFFF !important;
            }
            
            /* Aumentar el espacio entre elementos del menú */
            .sidebar .nav-item {
                margin-bottom: 0.75rem !important;
            }
            
            /* Mejorar el estilo del título del menú */
            .sidebar h5 {
                color: #FFFFFF !important;
                font-size: 1.1rem !important;
                margin-bottom: 1.5rem !important;
            }
        }
        
        .animate-in {
            animation: slideInUp 0.6s ease-out forwards;
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
        
        .slow-connection * {
            animation-duration: 0.1s !important;
            transition-duration: 0.1s !important;
        }
    </style>
</body>
</html>
