<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
$usuario = $_SESSION['usuario'];
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
            <a class="navbar-brand" href="#"><i class="bi bi-truck"></i> Trucksisx</a>
            <span class="navbar-text">Panel principal</span>
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
                 <li class="nav-item mb-2"><a class="nav-link" href="../index.php?logout=1"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
            </ul>
        </nav>
        <main class="flex-fill p-4">
            <div class="mb-4">
                <h2 class="fw-bold">Bienvenido, <?= $usuario['nombre'] ?> <span class="badge bg-info text-dark ms-2"> <?= $usuario['rol'] ?> </span></h2>
            </div>
            <div class="row g-4 module-blue">
                <div class="row g-4 mb-2">
                    <div class="col-md-4">
                        <div class="card module-blue text-center h-100 mb-4">
                            <div class="card-body">
                                <h5 class="card-title module-blue"><i class="bi bi-exclamation-triangle"></i> Sistema de Alertas</h5>
                                <p class="card-text">Monitorea y gestiona alertas de camiones doble troque. Acceso al diagrama interactivo y órdenes de trabajo generadas automáticamente.</p>
                                <a href="truck_alerts.php" class="btn btn-primary"><i class="bi bi-arrow-right-circle"></i> Ir al sistema de alertas</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card module-blue text-center h-100 mb-4">
                            <div class="card-body">
                                <h5 class="card-title module-blue"><i class="bi bi-box-arrow-in-right"></i> Salida de Repuestos</h5>
                                <p class="card-text">Registrar la salida obligatoria y secuencial de repuestos.</p>
                                <a href="salida_repuesto.php" class="btn btn-primary">Registrar Salida de Repuestos</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card module-blue text-center h-100 mb-4">
                            <div class="card-body">
                                <h5 class="card-title module-blue"><i class="bi bi-truck"></i> Salida de Vehículo</h5>
                                <p class="card-text">Registrar la salida de vehículo solo después de la salida de repuestos.</p>
                                <a href="salida_vehiculo.php" class="btn btn-primary">Registrar Salida de Vehículo</a>
                            </div>
                        </div>
                    </div>
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
                    <div class="col-md-4">
                        <div class="card module-blue text-center h-100 mb-4">
                            <div class="card-body">
                                <h5 class="card-title module-blue"><i class="bi bi-people"></i> Gestión de Usuarios</h5>
                                <p class="card-text">Crea y administra usuarios, conductores y técnicos del sistema.</p>
                                <a href="crear_usuario.php" class="btn btn-primary"><i class="bi bi-arrow-right-circle"></i> Ir a gestión de usuarios</a>
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
