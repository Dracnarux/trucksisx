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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <nav class="bg-dark text-white p-3" style="min-width:220px; height:100vh;">
            <h4 class="mb-4">Menú</h4>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a class="nav-link text-white" href="#">Gestión de Órdenes</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white" href="#">Gestión de Vehículos</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white" href="#">Gestión de Conductores</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white" href="categoriarepu.php">Gestión de Categorías</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white" href="subcategoriarepu.php">Gestión de Subcategorías</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white" href="repuestos.php">Gestión de Repuestos</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white" href="#">Gestión de Proveedores</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white" href="#">Gestión de Alertas</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white" href="../index.php?logout=1">Cerrar sesión</a></li>
            </ul>
        </nav>
        <main class="flex-fill p-4">
            <h2>Bienvenido, <?= $usuario['nombre'] ?> (<?= $usuario['rol'] ?>)</h2>
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Órdenes de Trabajo</h5>
                            <p class="card-text">Acceso rápido a la gestión de órdenes.</p>
                            <a href="#" class="btn btn-primary">Ir</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Estadísticas</h5>
                            <p class="card-text">Ver estadísticas de registros.</p>
                            <a href="#" class="btn btn-success">Ver</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Gestiones</h5>
                            <p class="card-text">Acceso a todas las gestiones.</p>
                            <a href="#" class="btn btn-info">Acceder</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
