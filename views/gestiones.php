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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #f8fafc 0%, #e3e6ed 100%);
        }
        .container {
            background: rgba(13,110,253,0.10);
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10);
            padding: 32px 24px;
            margin-top: 32px;
            color: #111;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(13,110,253,0.15);
        }
        h2 {
            color: #0d6efd;
        }
        .card.module-blue {
            background: rgba(13,110,253,0.15);
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            color: #111;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(13,110,253,0.2);
        }
        .card-title.module-blue,
        .card.module-blue .card-text,
        .card.module-blue .btn,
        .card.module-blue a,
        .card.module-blue h5 {
            color: #111 !important;
        }
        .btn-warning, .btn-secondary, .btn-info, .btn-success {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #fff !important;
        }
        .btn-warning:hover, .btn-secondary:hover, .btn-info:hover, .btn-success:hover {
            background-color: #0b5ed7 !important;
            border-color: #0b5ed7 !important;
        }
        .btn-outline-secondary {
            background-color: rgba(13,110,253,0.15) !important;
            color: #111 !important;
            border: 1px solid rgba(13,110,253,0.15) !important;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-0">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><i class="bi bi-truck"></i> Trucksisx</a>
            <span class="navbar-text">Panel de gestiones</span>
        </div>
    </nav>
    <div class="container py-5">
        <h2 class="mb-4 fw-bold"><i class="bi bi-collection"></i> Panel de Gestiones</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card module-blue h-100 text-center">
                    <div class="card-body">
                        <h5 class="card-title module-blue"><i class="bi bi-box"></i> Categoría de Repuestos</h5>
                        <p class="card-text">Gestiona las categorías de repuestos.</p>
                        <a href="cat_repu.php" class="btn btn-warning"><i class="bi bi-arrow-right-circle"></i> Ir</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card module-blue h-100 text-center">
                    <div class="card-body">
                        <h5 class="card-title module-blue"><i class="bi bi-boxes"></i> Subcategoría de Repuestos</h5>
                        <p class="card-text">Gestiona las subcategorías de repuestos.</p>
                        <a href="subcat_repu.php" class="btn btn-secondary"><i class="bi bi-arrow-right-circle"></i> Ir</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card module-blue h-100 text-center">
                    <div class="card-body">
                        <h5 class="card-title module-blue"><i class="bi bi-tools"></i> Repuestos</h5>
                        <p class="card-text">Gestiona los repuestos individuales: registro, edición, eliminación y consulta de repuestos con todos sus detalles.</p>
                        <a href="repue.php" class="btn btn-info"><i class="bi bi-arrow-right-circle"></i> Ir</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card module-blue h-100 text-center">
                    <div class="card-body">
                        <h5 class="card-title module-blue"><i class="bi bi-building"></i> Proveedores de Repuestos</h5>
                        <p class="card-text">Gestiona proveedores: registro, edición, eliminación y consulta de datos completos, incluyendo contacto, cobertura y condiciones comerciales.</p>
                        <a href="proveedor.php" class="btn btn-success"><i class="bi bi-arrow-right-circle"></i> Ir</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-5 text-end">
            <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver al Dashboard</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
