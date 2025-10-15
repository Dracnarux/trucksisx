<?php
require_once '../config/db.php';
require_once '../models/SaliRepue.php';
require_once '../models/Repue.php';
require_once '../models/OrdTrabj.php';
require_once '../models/Alert.php';
$db = conectarDB();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$salida = (new SaliRepue($db))->getById($id);
if (!$salida) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>No se encontró la salida de repuesto.</div></div>";
    exit;
}
$repues = (new Repue($db))->getAll();
$ordenes = (new OrdTrabj($db))->getAll();
$alertas = (new Alert($db))->getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Salida de Repuesto</title>
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
    <h2>Editar Salida de Repuesto</h2>
    <form action="../controllers/SaliRepueController.php?action=actualizar&id=<?= $salida['id'] ?>" method="POST">
        <div class="mb-3">
            <label for="fecha_salida" class="form-label">Fecha de Salida</label>
            <input type="date" class="form-control" name="fecha_salida" value="<?= $salida['fecha_salida'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="cantidad" class="form-label">Cantidad</label>
            <input type="number" class="form-control" name="cantidad" min="1" value="<?= $salida['cantidad'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="repue_id" class="form-label">Repuesto</label>
            <select class="form-select" name="repue_id" required>
                <?php foreach ($repues as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= $salida['repue_id'] == $r['id'] ? 'selected' : '' ?>><?= $r['nombre'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="ord_trabj_id" class="form-label">Orden de Trabajo</label>
            <select class="form-select" name="ord_trabj_id" required>
                <?php foreach ($ordenes as $o): ?>
                    <option value="<?= $o['id'] ?>" <?= $salida['ord_trabj_id'] == $o['id'] ? 'selected' : '' ?>><?= $o['nombre_trabajo'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="alerta_id" class="form-label">Alerta</label>
            <select class="form-select" name="alerta_id" required>
                <?php foreach ($alertas as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= $salida['alerta_id'] == $a['id'] ? 'selected' : '' ?>><?= $a['descripcion'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="reporte_salidas.php" class="btn btn-secondary ms-2">Cancelar</a>
    </form>
</div>
</body>
</html>
