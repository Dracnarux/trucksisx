<?php
require_once '../config/db.php';
require_once '../models/SaliVehi.php';
$db = conectarDB();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$salida = (new SaliVehi($db))->getById($id);
if (!$salida) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>No se encontró la salida de vehículo.</div></div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Salida de Vehículo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .form-label, .form-select, .form-control {
            color: #111 !important;
        }
        .btn-primary, .btn-outline-primary {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #fff !important;
        }
        .btn-primary:hover, .btn-outline-primary:hover {
            background-color: #0b5ed7 !important;
            border-color: #0b5ed7 !important;
        }
        .btn-secondary {
            background-color: rgba(13,110,253,0.15) !important;
            color: #111 !important;
            border: 1px solid rgba(13,110,253,0.15) !important;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2>Editar Salida de Vehículo</h2>
    <form action="../controllers/SaliVehiController.php?action=actualizar&id=<?= $salida['id'] ?>" method="POST">
        <div class="mb-3">
            <label for="id_flotas" class="form-label">ID Flota</label>
            <input type="number" class="form-control" name="id_flotas" value="<?= $salida['id_flotas'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="segui_monitoreo" class="form-label">Seguimiento y Monitoreo</label>
            <input type="text" class="form-control" name="segui_monitoreo" value="<?= htmlspecialchars($salida['segui_monitoreo']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="control_combustible" class="form-label">Control y Nivel de Combustible</label>
            <input type="text" class="form-control" name="control_combustible" value="<?= htmlspecialchars($salida['control_combustible']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="cump_regulaciones" class="form-label">Cumplimiento de Regulaciones</label>
            <input type="text" class="form-control" name="cump_regulaciones" value="<?= htmlspecialchars($salida['cump_regulaciones']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="protocolo_seguridad" class="form-label">Protocolos de Seguridad</label>
            <input type="text" class="form-control" name="protocolo_seguridad" value="<?= htmlspecialchars($salida['protocolo_seguridad']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="gest_conductores" class="form-label">Gestión y Datos de Conductores</label>
            <input type="text" class="form-control" name="gest_conductores" value="<?= htmlspecialchars($salida['gest_conductores']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="reporte_salidas.php" class="btn btn-secondary ms-2">Cancelar</a>
    </form>
</div>
</body>
</html>
