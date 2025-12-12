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
        :root {
            --bg-primary: #0F172A;
            --card-bg: #111827;
            --card-radius: 12px;
            --text-primary: #F1F5F9;
            --text-secondary: #94A3B8;
            --border: #1E293B;
            --accent: #F97316;
            --accent-amber: #F59E0B;
            --danger: #EF4444;
            --success: #10B981;
        }

        body {
            background: var(--bg-primary);
            color: var(--text-primary);
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

        .main-header h1 {
            color: var(--text-primary);
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--card-radius);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            background: var(--accent);
            color: #fff;
            font-weight: bold;
        }

        .table th {
            background: var(--accent);
            color: #fff;
        }

        .btn-outline-light {
            color: var(--text-primary);
            border-color: var(--text-secondary);
        }

        .btn-outline-light:hover {
            background: var(--accent);
            color: #fff;
        }
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
