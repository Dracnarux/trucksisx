<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
require_once '../config/db.php';
$db = conectarDB();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo '<div class="alert alert-danger">ID de salida no válido.</div>';
    exit;
}

$sql = "SELECT sr.*, r.nombre AS repuesto, ot.nombre_trabajo, a.descripcion AS alerta
        FROM sali_repue sr
        LEFT JOIN repue r ON sr.repue_id = r.id
        LEFT JOIN ord_trabj ot ON sr.ord_trabj_id = ot.id
        LEFT JOIN alert a ON sr.alerta_id = a.id
        WHERE sr.id = $id";
$res = $db->query($sql);
$salida = $res ? $res->fetch_assoc() : null;
if (!$salida) {
    echo '<div class="alert alert-danger">No se encontró la salida de repuesto.</div>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Salida de Repuesto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%); }
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
        .main-header h1 { color: #fff; }
        .card { border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .table th { background: #1E3A8A; color: #fff; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="main-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
            <div>
                <h1 class="mb-2"><i class="bi bi-eye"></i> Detalle de Salida de Repuesto</h1>
                <span class="lead">Consulta detallada de la salida registrada</span>
            </div>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <a href="salida_repuesto.php" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left"></i> Volver a Salidas
                </a>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-clipboard-data"></i> Información de la Salida</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered mb-0">
                <tr><th>ID</th><td><?= $salida['id'] ?></td></tr>
                <tr><th>Fecha de Salida</th><td><?= $salida['fecha_salida'] ?></td></tr>
                <tr><th>Cantidad</th><td><?= $salida['cantidad'] ?></td></tr>
                <tr><th>Repuesto</th><td><?= $salida['repuesto'] ?></td></tr>
                <tr><th>Orden de Trabajo</th><td><?= $salida['nombre_trabajo'] ?></td></tr>
                <tr><th>Alerta</th><td><?= $salida['alerta'] ?></td></tr>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
