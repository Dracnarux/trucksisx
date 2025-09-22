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
        body {
            background: linear-gradient(120deg, #f8fafc 0%, #e3e6ed 100%);
        }
        .sidebar {
            min-width: 220px;
            height: 100vh;
            background: #212529;
            color: #fff;
            box-shadow: 2px 0 8px rgba(0,0,0,0.05);
        }
        .sidebar .nav-link {
            color: #fff;
            transition: background 0.2s, color 0.2s;
        }
        .sidebar .nav-link:hover {
            background: #495057;
            color: #ffc107;
        }
        .card {
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            border-radius: 1rem;
        }
        .card-title {
            font-weight: 600;
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
            <h4 class="mb-4"><i class="bi bi-list"></i> Menú</h4>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a class="nav-link" href="#"><i class="bi bi-file-earmark-text"></i> Gestión de Órdenes</a></li>
                <li class="nav-item mb-2"><a class="nav-link" href="#"><i class="bi bi-truck"></i> Gestión de Vehículos</a></li>
                <li class="nav-item mb-2"><a class="nav-link" href="#"><i class="bi bi-gear"></i> Gestión Vehicular</a></li>
                <li class="nav-item mb-2"><a class="nav-link" href="#"><i class="bi bi-person-badge"></i> Gestión de Conductores</a></li>
                <li class="nav-item mb-2"><a class="nav-link" href="#"><i class="bi bi-building"></i> Gestión de Proveedores</a></li>
                <li class="nav-item mb-2"><a class="nav-link" href="#"><i class="bi bi-exclamation-triangle"></i> Gestión de Alertas</a></li>
                <li class="nav-item mb-2"><a class="nav-link" href="../index.php?logout=1"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
            </ul>
        </nav>
        <main class="flex-fill p-4">
            <div class="mb-4">
                <h2 class="fw-bold">Bienvenido, <?= $usuario['nombre'] ?> <span class="badge bg-info text-dark ms-2"> <?= $usuario['rol'] ?> </span></h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card text-center h-100 border-primary">
                        <div class="card-body">
                            <h5 class="card-title text-primary"><i class="bi bi-file-earmark-text"></i> Órdenes de Trabajo</h5>
                            <p class="card-text">Acceso rápido a la gestión de órdenes.</p>
                            <a href="#" class="btn btn-primary">Ir</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center h-100 border-success">
                        <div class="card-body">
                            <h5 class="card-title text-success"><i class="bi bi-bar-chart-line"></i> Estadísticas</h5>
                            <p class="card-text">Ver estadísticas de registros.</p>
                            <a href="#" class="btn btn-success">Ver</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center h-100 border-info">
                        <div class="card-body">
                            <h5 class="card-title text-info"><i class="bi bi-collection"></i> Gestiones</h5>
                            <p class="card-text">Acceso a todas las gestiones.</p>
                            <a href="gestiones.php" class="btn btn-info">Acceder</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center h-100 border-success">
                        <div class="card-body">
                            <h5 class="card-title text-success"><i class="bi bi-truck"></i> Gestión Vehicular</h5>
                            <p class="card-text">Gestiona los vehículos y su información.</p>
                            <a href="#" class="btn btn-success">Ir</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
