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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Animación flip para los módulos */
        .module-blue {
            perspective: 800px;
        }
        .card.module-blue {
            transform: rotateY(90deg);
            opacity: 0;
            animation: flipIn 0.7s cubic-bezier(0.4,0,0.2,1) forwards;
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
        body {
            background: linear-gradient(120deg, #f8fafc 0%, #e3e6ed 100%);
        }
        .sidebar {
            min-width: 220px;
            height: 100vh;
            background: rgba(13,110,253,0.25);
            color: #111;
            box-shadow: 2px 0 8px rgba(0,0,0,0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(13,110,253,0.3);
            transition: transform 0.3s ease, margin-left 0.3s ease;
        }
        
        /* Sidebar oculto en desktop */
        .sidebar.hidden-desktop {
            margin-left: -220px;
        }
        
        /* Ajustar contenido principal cuando sidebar está oculto */
        .content-expanded {
            margin-left: 0 !important;
            width: 100% !important;
        }
        
        /* Sidebar más opaco para móviles */
        @media (max-width: 991px) {
            .sidebar {
                background: rgba(255,255,255,0.98) !important;
                backdrop-filter: blur(10px) !important;
                -webkit-backdrop-filter: blur(10px) !important;
                border-right: 1px solid rgba(13,110,253,0.4) !important;
                box-shadow: 2px 0 20px rgba(0,0,0,0.2) !important;
                min-width: 250px !important;
                width: 250px !important;
            }
            
            .sidebar .nav-link {
                background: rgba(13,110,253,0.1) !important;
                color: #111 !important;
                font-weight: 500 !important;
                border: 1px solid rgba(13,110,253,0.2) !important;
                padding: 0.75rem 1rem !important;
                border-radius: 0.5rem !important;
                margin-bottom: 0.5rem !important;
                display: flex !important;
                align-items: center !important;
                gap: 0.75rem !important;
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
            
            .sidebar #closeSidebar:hover {
                background: rgba(220,53,69,0.2) !important;
                border-color: rgba(220,53,69,0.5) !important;
            }
        }
        
        /* Estilos para desktop */
        @media (min-width: 992px) {
            /* Botón de ocultar sidebar en desktop */
            .sidebar #closeSidebar {
                background: rgba(13,110,253,0.1) !important;
                border-color: rgba(13,110,253,0.3) !important;
                color: #0d6efd !important;
            }
            
            .sidebar #closeSidebar:hover {
                background: rgba(13,110,253,0.2) !important;
                border-color: rgba(13,110,253,0.5) !important;
            }
            
            /* Botón mostrar menú en navbar */
            #showSidebar {
                transition: all 0.3s ease;
            }
            
            #showSidebar:hover {
                background: rgba(255,255,255,0.2) !important;
                transform: scale(1.05);
            }
            
            /* Iconos más visibles */
            .sidebar .nav-link i {
                color: #0d6efd !important;
                font-weight: 600 !important;
            }
            
            /* Texto del menú móvil más visible */
            .sidebar .nav-link span {
                color: #111 !important;
                font-weight: 600 !important;
                text-shadow: none !important;
                font-size: 0.95rem !important;
                white-space: nowrap !important;
            }
            
            .sidebar .nav-link:hover span {
                color: #0d6efd !important;
                font-weight: 700 !important;
            }
            
            /* Fondo del contenedor del sidebar */
            .sidebar {
                backdrop-filter: blur(5px) !important;
                border-right: 2px solid rgba(13,110,253,0.3) !important;
                box-shadow: 2px 0 10px rgba(0,0,0,0.1) !important;
            }
            
            /* Mostrar texto del menú en móvil */
            .sidebar .nav-link .d-none.d-lg-inline {
                display: inline !important;
            }
            
            /* Ocultar solo los iconos si es necesario para dar espacio al texto */
            .sidebar h5 .d-none.d-lg-inline {
                display: inline !important;
            }
        }
        
        .sidebar .nav {
            display: flex !important;
            flex-direction: column !important;
        }
        
        .sidebar .nav-link {
            color: #111 !important;
            transition: background 0.2s, color 0.2s;
            display: flex !important;
            align-items: center !important;
            padding: 0.75rem 1rem !important;
            border-radius: 0.5rem !important;
            margin: 0.25rem 0 !important;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(13,110,253,0.15);
            color: #0d6efd !important;
        }
        
        .sidebar .nav-link i {
            margin-right: 0.75rem !important;
            font-size: 1.2rem !important;
        }
        .card.module-blue {
            background: rgba(13,110,253,0.25);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            border-radius: 1rem;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(13,110,253,0.3);
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-0">
        <div class="container-fluid">
            <!-- Botón para mostrar menú (cuando esté oculto en desktop) -->
            <button class="btn btn-outline-light me-2" type="button" id="showSidebar" style="display: none;" title="Mostrar menú">
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
        <nav class="sidebar p-3" id="sidebar">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-grid-3x3-gap"></i> <span class="d-none d-lg-inline">Menú Rápido</span></h5>
                <button class="btn btn-sm btn-outline-primary" id="closeSidebar" title="Ocultar menú">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>
            <ul class="nav flex-column" id="menu-options">
                 <li class="nav-item mb-2">
                     <a class="nav-link" href="cond.php">
                         <i class="bi bi-person-badge"></i> 
                         <span class="d-none d-lg-inline">Conductores</span>
                     </a>
                 </li>
                 <li class="nav-item mb-2">
                     <a class="nav-link" href="gestiones.php">
                         <i class="bi bi-collection"></i> 
                         <span class="d-none d-lg-inline">Gestiones</span>
                     </a>
                 </li>
                 <?php if($usuario['rol'] == 'admin'): ?>
                 <li class="nav-item mb-2">
                     <a class="nav-link" href="crear_usuario.php">
                         <i class="bi bi-people"></i> 
                         <span class="d-none d-lg-inline">Usuarios</span>
                     </a>
                 </li>
                 <?php endif; ?>
                 <li class="nav-item mb-2">
                     <a class="nav-link" href="truck_alerts.php">
                         <i class="bi bi-exclamation-triangle"></i> 
                         <span class="d-none d-lg-inline">Alertas</span>
                     </a>
                 </li>
                 <li class="nav-item mb-2">
                     <a class="nav-link text-danger" href="../logout.php">
                         <i class="bi bi-box-arrow-right"></i> 
                         <span class="d-none d-lg-inline">Salir</span>
                     </a>
                 </li>
            </ul>
        </nav>
        <main class="flex-fill p-4">
            <div class="mb-4">
                <h2 class="fw-bold">Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?> <span class="badge bg-info text-dark ms-2"><?= htmlspecialchars($usuario['rol']) ?></span></h2>
                <p class="text-muted">Último acceso: <?= isset($_SESSION['last_activity']) ? date('d/m/Y H:i:s', $_SESSION['last_activity']) : 'Ahora' ?></p>
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
            
            <!-- Cards de Estadísticas -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="card-title text-white-50 mb-0">Vehículos</h6>
                                    <h3 class="mb-0"><?= $stats['vehiculos'] ?></h3>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-truck" style="font-size: 2rem; opacity: 0.6;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="card-title mb-0" style="opacity: 0.7;">Alertas Activas</h6>
                                    <h3 class="mb-0"><?= $stats['alertas_activas'] ?></h3>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-exclamation-triangle" style="font-size: 2rem; opacity: 0.6;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="card-title text-white-50 mb-0">Órdenes Pendientes</h6>
                                    <h3 class="mb-0"><?= $stats['ordenes_pendientes'] ?></h3>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-clipboard-check" style="font-size: 2rem; opacity: 0.6;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="card-title text-white-50 mb-0">Conductores</h6>
                                    <h3 class="mb-0"><?= $stats['conductores'] ?></h3>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-person-badge" style="font-size: 2rem; opacity: 0.6;"></i>
                                </div>
                            </div>
                        </div>
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
                            <i class="bi bi-exclamation-triangle card-icon text-danger"></i>
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
                            <i class="bi bi-box-arrow-right card-icon text-primary"></i>
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
                            <i class="bi bi-truck card-icon text-success"></i>
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
                            <i class="bi bi-clipboard-check card-icon text-warning"></i>
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
                            <i class="bi bi-people card-icon text-info"></i>
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
                            <i class="bi bi-file-earmark-text card-icon text-secondary"></i>
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
                            <i class="bi bi-collection card-icon" style="color: #6f42c1;"></i>
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
                                            <i class="bi bi-truck card-icon-small text-success"></i>
                                            <h6 class="card-title">Categorías</h6>
                                            <p class="card-text small">Gestiona categorías de vehículos</p>
                                            <a href="cat_vehiculo.php" class="btn btn-success btn-sm">
                                                <i class="bi bi-arrow-right-circle"></i> Acceder
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="card uniform-small-card text-center border-success">
                                        <div class="card-body">
                                            <i class="bi bi-truck-flatbed card-icon-small text-success"></i>
                                            <h6 class="card-title">Subcategorías</h6>
                                            <p class="card-text small">Gestiona subcategorías de vehículos</p>
                                            <a href="subcat_vehiculo.php" class="btn btn-success btn-sm">
                                                <i class="bi bi-arrow-right-circle"></i> Acceder
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="card uniform-small-card text-center border-success">
                                        <div class="card-body">
                                            <i class="bi bi-journal-plus card-icon-small text-success"></i>
                                            <h6 class="card-title">Registro</h6>
                                            <p class="card-text small">Registra vehículos individuales</p>
                                            <a href="regis_vehic.php" class="btn btn-success btn-sm">
                                                <i class="bi bi-arrow-right-circle"></i> Acceder
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="card uniform-small-card text-center border-success">
                                        <div class="card-body">
                                            <i class="bi bi-person-badge card-icon-small text-success"></i>
                                            <h6 class="card-title">Conductores</h6>
                                            <p class="card-text small">Gestiona conductores y datos</p>
                                            <a href="cond.php" class="btn btn-success btn-sm">
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
        // ===== FUNCIONALIDAD DEL MENÚ LATERAL =====
        
        document.addEventListener('DOMContentLoaded', function() {
            // Elementos del menú
            const sidebarToggle = document.getElementById('sidebarToggle');
            const closeSidebar = document.getElementById('closeSidebar');
            const showSidebar = document.getElementById('showSidebar');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('main.flex-fill');
            
            // Función para mostrar el sidebar en móviles
            function showMobileSidebar() {
                if (sidebar) {
                    sidebar.classList.add('show-mobile');
                    document.body.style.overflow = 'hidden';
                    console.log('Sidebar móvil mostrado');
                }
            }
            
            // Función para ocultar el sidebar en móviles
            function hideMobileSidebar() {
                if (sidebar) {
                    sidebar.classList.remove('show-mobile');
                    document.body.style.overflow = 'auto';
                    console.log('Sidebar móvil oculto');
                }
            }
            
            // Función para ocultar el sidebar en desktop
            function hideDesktopSidebar() {
                if (sidebar && window.innerWidth > 991) {
                    sidebar.classList.add('hidden-desktop');
                    if (mainContent) {
                        mainContent.classList.add('content-expanded');
                    }
                    if (showSidebar) {
                        showSidebar.style.display = 'inline-block';
                    }
                    console.log('Sidebar desktop oculto');
                }
            }
            
            // Función para mostrar el sidebar en desktop
            function showDesktopSidebar() {
                if (sidebar) {
                    sidebar.classList.remove('hidden-desktop');
                    if (mainContent) {
                        mainContent.classList.remove('content-expanded');
                    }
                    if (showSidebar) {
                        showSidebar.style.display = 'none';
                    }
                    console.log('Sidebar desktop mostrado');
                }
            }
            
            // Event listener para abrir sidebar (móvil)
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    showMobileSidebar();
                });
                console.log('Botón hamburger móvil configurado');
            }
            
            // Event listener para mostrar sidebar (desktop)
            if (showSidebar) {
                showSidebar.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    showDesktopSidebar();
                });
                console.log('Botón mostrar desktop configurado');
            }
            
            // Event listener para cerrar/ocultar sidebar
            if (closeSidebar) {
                closeSidebar.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (window.innerWidth <= 991) {
                        // En móvil: ocultar sidebar móvil
                        hideMobileSidebar();
                    } else {
                        // En desktop: ocultar sidebar desktop
                        hideDesktopSidebar();
                    }
                });
                console.log('Botón cerrar/ocultar configurado');
            }
            
            // Cerrar sidebar al hacer clic fuera (solo móviles)
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 991 && 
                    sidebar && sidebar.classList.contains('show-mobile') && 
                    !sidebar.contains(e.target) && 
                    sidebarToggle && !sidebarToggle.contains(e.target)) {
                    hideMobileSidebar();
                }
            });
            
            // Manejar cambios de tamaño de ventana
            window.addEventListener('resize', function() {
                if (window.innerWidth > 991 && sidebar) {
                    // Al cambiar a desktop, ocultar sidebar móvil y restaurar desktop
                    hideMobileSidebar();
                    
                    // Si el sidebar no está oculto en desktop, asegurar que esté visible
                    if (!sidebar.classList.contains('hidden-desktop')) {
                        showDesktopSidebar();
                    }
                } else if (window.innerWidth <= 991 && sidebar) {
                    // Al cambiar a móvil, restaurar comportamiento móvil
                    sidebar.classList.remove('hidden-desktop');
                    if (mainContent) {
                        mainContent.classList.remove('content-expanded');
                    }
                    if (showSidebar) {
                        showSidebar.style.display = 'none';
                    }
                }
            });
            
            console.log('🚛 TruckSISX - Menú lateral configurado correctamente');
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
        
        /* Mejorar la visualización del botón hamburger */
        #sidebarToggle {
            display: none;
        }
        
        @media (max-width: 991px) {
            #sidebarToggle {
                display: inline-block !important;
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
