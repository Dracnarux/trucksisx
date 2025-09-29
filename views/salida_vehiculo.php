<?php
require_once '../config/db.php';
require_once '../models/SaliRepue.php';
require_once '../models/OrdTrabj.php';
require_once '../models/Alert.php';
require_once '../models/Repor.php';

$sali_repue_id = isset($_GET['sali_repue_id']) ? $_GET['sali_repue_id'] : null;
$sali_repue = $sali_repue_id ? (new SaliRepue($db))->getById($sali_repue_id) : null;
$ordenes = (new OrdTrabj($db))->getAll();
$alertas = (new Alert($db))->getAll();
$reportes = (new Repor($db))->getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Salida de Vehículo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Registrar Salida de Vehículo</h2>
    <?php if (!$sali_repue): ?>
        <div class="alert alert-danger">Debe registrar primero la salida de repuestos.</div>
    <?php else: ?>
    <form action="../controllers/SaliVehiController.php?action=registrar" method="POST">
        <input type="hidden" name="sali_repue_id" value="<?= $sali_repue['id'] ?>">
        <input type="hidden" name="ord_trabj_id" value="<?= $sali_repue['ord_trabj_id'] ?>">
        <input type="hidden" name="alerta_id" value="<?= $sali_repue['alerta_id'] ?>">
        <input type="hidden" name="repor_id" value="<?= $sali_repue['repor_id'] ?>">
        <div class="mb-3">
            <label for="id_flotas" class="form-label">ID Flota</label>
            <input type="number" class="form-control" name="id_flotas" required>
        </div>
        <div class="mb-3">
            <label for="segui_monitoreo" class="form-label">Seguimiento y Monitoreo</label>
            <input type="text" class="form-control" name="segui_monitoreo" required>
        </div>
        <div class="mb-3">
            <label for="control_combustible" class="form-label">Control y Nivel de Combustible</label>
            <input type="text" class="form-control" name="control_combustible" required>
        </div>
        <div class="mb-3">
            <label for="cump_regulaciones" class="form-label">Cumplimiento de Regulaciones</label>
            <input type="text" class="form-control" name="cump_regulaciones" required>
        </div>
        <div class="mb-3">
            <label for="protocolo_seguridad" class="form-label">Protocolos de Seguridad</label>
            <input type="text" class="form-control" name="protocolo_seguridad" required>
        </div>
        <div class="mb-3">
            <label for="gest_conductores" class="form-label">Gestión y Datos de Conductores</label>
            <input type="text" class="form-control" name="gest_conductores" required>
        </div>
        <button type="submit" class="btn btn-primary">Registrar Salida de Vehículo</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
