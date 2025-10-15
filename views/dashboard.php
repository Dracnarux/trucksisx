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
        /* Animación slide para el menú lateral */
        #menu-options {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.4s cubic-bezier(0.4,0,0.2,1), opacity 0.3s ease;
        }
        #menu-options.show {
            max-height: 500px;
            opacity: 1;
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
        }
        .sidebar .nav-link {
            color: #111 !important;
            transition: background 0.2s, color 0.2s;
        }
        .sidebar .nav-link:hover {
            background: rgba(13,110,253,0.15);
            color: #0d6efd !important;
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-0">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><i class="bi bi-truck"></i> Trucksisx</a>
            <span class="navbar-text">Panel principal</span>
            <div class="dropdown ms-auto">
                <a class="nav-link dropdown-toggle text-white" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>
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
        <nav class="sidebar p-3">
            <h4 class="mb-4" id="menu-toggle" style="cursor:pointer;"><i class="bi bi-list"></i> Menú</h4>
            <ul class="nav flex-column" id="menu-options">
                 <li class="nav-item mb-2"><a class="nav-link" href="cond.php"><i class="bi bi-person-badge"></i> Gestión de Conductores</a></li>
                 <li class="nav-item mb-2"><a class="nav-link" href="gestiones.php"><i class="bi bi-collection"></i> Gestiones</a></li>
                 <li class="nav-item mb-2"><a class="nav-link" href="crear_usuario.php"><i class="bi bi-people"></i> Gestión de Usuarios</a></li>
                 <li class="nav-item mb-2"><a class="nav-link" href="truck_alerts.php"><i class="bi bi-exclamation-triangle"></i> Sistema de Alertas</a></li>
                 <li class="nav-item mb-2"><a class="nav-link" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
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
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <h4 class="fw-bold mb-3"><i class="bi bi-grid-3x3-gap"></i> Módulos Principales</h4>
                </div>
                
                <!-- Sistema de Alertas -->
                <div class="col-md-4">
                    <div class="card module-blue text-center h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-exclamation-triangle" style="font-size: 3rem; color: #0d6efd;"></i>
                            </div>
                            <h5 class="card-title module-blue">Sistema de Alertas</h5>
                            <p class="card-text">Monitorea y gestiona alertas de camiones doble troque. Diagrama interactivo y órdenes de trabajo automáticas.</p>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="truck_alerts.php" class="btn btn-primary">
                                    <i class="bi bi-arrow-right-circle"></i> Acceder
                                </a>
                                <?php if($usuario['rol'] == 'admin'): ?>
                                <a href="../system_status.php" class="btn btn-outline-primary" title="Estado del Sistema">
                                    <i class="bi bi-gear"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Salida de Repuestos -->
                <?php if($usuario['rol'] != 'conductor'): ?>
                <div class="col-md-4">
                    <div class="card module-blue text-center h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-box-arrow-right" style="font-size: 3rem; color: #0d6efd;"></i>
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
                <div class="col-md-4">
                    <div class="card module-blue text-center h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-truck" style="font-size: 3rem; color: #0d6efd;"></i>
                            </div>
                            <h5 class="card-title module-blue">Salida de Vehículo</h5>
                            <p class="card-text">Registrar la salida de vehículo después de completar la salida de repuestos.</p>
                            <a href="salida_vehiculo.php" class="btn btn-primary">
                                <i class="bi bi-truck-front"></i> Registrar Salida
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Órdenes de Trabajo -->
                <div class="col-md-4">
                    <div class="card module-blue text-center h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-clipboard-check" style="font-size: 3rem; color: #0d6efd;"></i>
                            </div>
                            <h5 class="card-title module-blue">Órdenes de Trabajo</h5>
                            <p class="card-text">Gestiona las órdenes de trabajo generadas automáticamente por las alertas.</p>
                            <a href="orden_trabajo.php" class="btn btn-primary">
                                <i class="bi bi-list-task"></i> Ver Órdenes
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Gestión de Usuarios (Solo Admin) -->
                <?php if($usuario['rol'] == 'admin'): ?>
                <div class="col-md-4">
                    <div class="card module-blue text-center h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-people" style="font-size: 3rem; color: #0d6efd;"></i>
                            </div>
                            <h5 class="card-title module-blue">Gestión de Usuarios</h5>
                            <p class="card-text">Administra usuarios del sistema: conductores, técnicos y administradores.</p>
                            <a href="crear_usuario.php" class="btn btn-primary">
                                <i class="bi bi-person-plus"></i> Gestionar
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
                <div class="row g-4 mb-2 module-blue">
                    <div class="col-md-4">
                        <div class="card module-blue text-center h-100 mb-4">
                            <div class="card-body">
                                <h5 class="card-title module-blue"><i class="bi bi-file-earmark-text"></i> Reportes</h5>
                                <p class="card-text">Ver reporte consolidado de salidas de repuestos y vehículos.</p>
                                <a href="reporte_salidas.php" class="btn btn-primary">Ver Reportes</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card module-blue text-center h-100 mb-4">
                            <div class="card-body">
                                <h5 class="card-title module-blue"><i class="bi bi-collection"></i> Gestiones</h5>
                                <p class="card-text">Acceso a todas las gestiones de repuestos.</p>
                                <a href="gestiones.php" class="btn btn-primary">Acceder</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 module-blue">
                    <div class="card module-blue h-100 mb-4">
                        <div class="card-body">
                            <h5 class="card-title text-success"><i class="bi bi-truck"></i> Gestión Vehicular</h5>
                            <p class="card-text">Gestiona los vehículos y su información.</p>
                            <div class="row g-3 mt-3">
                                <div class="col-md-3 module-blue">
                                    <div class="card module-blue h-100 text-center">
                                        <div class="card-body">
                                            <h6 class="card-title module-blue"><i class="bi bi-truck"></i> Categoría de Vehículos</h6>
                                            <p class="card-text">Gestiona las categorías de vehículos.</p>
                                            <a href="cat_vehiculo.php" class="btn btn-primary btn-sm"><i class="bi bi-arrow-right-circle"></i> Ir</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 module-blue">
                                    <div class="card module-blue h-100 text-center">
                                        <div class="card-body">
                                            <h6 class="card-title module-blue"><i class="bi bi-truck-flatbed"></i> Subcategoría de Vehículo</h6>
                                            <p class="card-text">Gestiona las subcategorías de vehículos.</p>
                                        <a href="subcat_vehiculo.php" class="btn btn-primary btn-sm"><i class="bi bi-arrow-right-circle"></i> Ir</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 module-blue">
                                    <div class="card module-blue h-100 text-center">
                                        <div class="card-body">
                                            <h6 class="card-title module-blue"><i class="bi bi-journal-plus"></i> Registro de Vehículo</h6>
                                            <p class="card-text">Registra y gestiona vehículos individuales.</p>
                                            <a href="regis_vehic.php" class="btn btn-primary btn-sm"><i class="bi bi-arrow-right-circle"></i> Ir</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 module-blue">
                                    <div class="card module-blue h-100 text-center">
                                        <div class="card-body">
                                            <h6 class="card-title module-blue"><i class="bi bi-person-badge"></i> Conductor</h6>
                                            <p class="card-text">Gestiona los conductores y sus datos.</p>
                                            <a href="cond.php" class="btn btn-primary btn-sm"><i class="bi bi-arrow-right-circle"></i> Ir</a>
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
        // Animación slide para mostrar/ocultar el menú
        const menuToggle = document.getElementById('menu-toggle');
        const menuOptions = document.getElementById('menu-options');
        let menuOpen = false;
        menuOptions.classList.remove('show');
        menuToggle.addEventListener('click', function() {
            menuOpen = !menuOpen;
            if(menuOpen) {
                menuOptions.classList.add('show');
            } else {
                menuOptions.classList.remove('show');
            }
        });
    </script>
</body>
</html>
