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
    <title>Panel de Gestiones | Trucksisx</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Panel de Gestiones</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title">Categoría de Repuestos</h5>
                    <p class="card-text">Gestiona las categorías de repuestos.</p>
                    <a href="cat_repu.php" class="btn btn-warning">Ir</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title">Subcategoría de Repuestos</h5>
                    <p class="card-text">Gestiona las subcategorías de repuestos.</p>
                    <a href="subcat_repu.php" class="btn btn-secondary">Ir</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title">Repuestos</h5>
                    <p class="card-text">Gestiona los repuestos individuales.</p>
                    <a href="#" class="btn btn-info disabled">Próximamente</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title">Proveedores</h5>
                    <p class="card-text">Gestiona los proveedores.</p>
                    <a href="#" class="btn btn-success disabled">Próximamente</a>
                </div>
            </div>
        </div>
        <!-- Agrega más tarjetas según los módulos que vayas creando -->
    </div>
    <div class="mt-5 text-end">
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al Dashboard</a>
    </div>
</div>
</body>
</html>
