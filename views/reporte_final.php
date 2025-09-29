<?php
// Este archivo muestra la confirmación y resumen del proceso secuencial
require_once '../config/db.php';
require_once '../models/SaliRepue.php';
require_once '../models/SaliVehi.php';

$sali_vehi_id = isset($_GET['sali_vehi_id']) ? $_GET['sali_vehi_id'] : null;
$sali_vehi = $sali_vehi_id ? (new SaliVehi($db))->getById($sali_vehi_id) : null;
$sali_repue = $sali_vehi && isset($sali_vehi['sali_repue_id']) ? (new SaliRepue($db))->getById($sali_vehi['sali_repue_id']) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Final de Proceso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Proceso Completado</h2>
    <?php if ($sali_vehi && $sali_repue): ?>
        <div class="alert alert-success">El proceso de salida de repuestos y vehículo se ha completado correctamente.</div>
        <h4>Resumen de la Salida de Repuestos</h4>
        <ul>
            <li>Fecha de salida: <?= $sali_repue['fecha_salida'] ?></li>
            <li>Cantidad: <?= $sali_repue['cantidad'] ?></li>
            <li>ID Repuesto: <?= $sali_repue['repue_id'] ?></li>
            <li>ID Orden de Trabajo: <?= $sali_repue['ord_trabj_id'] ?></li>
            <li>Código de Reporte: <?= $sali_repue['repor_id'] ?></li>
            <li>ID Alerta: <?= $sali_repue['alerta_id'] ?></li>
        </ul>
        <h4>Resumen de la Salida de Vehículo</h4>
        <ul>
            <li>ID Flota: <?= $sali_vehi['id_flotas'] ?></li>
            <li>Seguimiento y Monitoreo: <?= $sali_vehi['segui_monitoreo'] ?></li>
            <li>Control Combustible: <?= $sali_vehi['control_combustible'] ?></li>
            <li>Cumplimiento Regulaciones: <?= $sali_vehi['cump_regulaciones'] ?></li>
            <li>Protocolos Seguridad: <?= $sali_vehi['protocolo_seguridad'] ?></li>
            <li>Gestión Conductores: <?= $sali_vehi['gest_conductores'] ?></li>
        </ul>
        <div class="alert alert-info">Se han generado las alertas automáticas y el proceso ha finalizado exitosamente.</div>
    <?php else: ?>
        <div class="alert alert-danger">No se encontró información completa del proceso.</div>
    <?php endif; ?>
</div>
</body>
</html>
