<?php
// Este archivo muestra la confirmación y resumen del proceso secuencial
require_once '../config/db.php';
require_once '../models/SaliRepue.php';
require_once '../models/SaliVehi.php';
// Definir la conexión $db
$db = conectarDB();

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%);
            color: #374151;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            padding: 2rem 0;
        }
        h2, h4 {
            color: #475569;
            font-weight: 600;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        .card-header {
            background: linear-gradient(135deg, #475569 0%, #334155 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.5rem;
        }
        .card-body {
            padding: 2rem;
        }
        .btn-secondary {
            background: linear-gradient(135deg, #475569 0%, #334155 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(71, 85, 105, 0.3);
        }
        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #047857;
            border-radius: 8px;
        }
        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #dc2626;
            border-radius: 8px;
        }
        .alert-info {
            background: rgba(71, 85, 105, 0.1);
            border: 1px solid rgba(71, 85, 105, 0.2);
            color: #334155;
            border-radius: 8px;
        }
        ul {
            list-style: none;
            padding-left: 0;
        }
        ul li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        ul li:last-child {
            border-bottom: none;
        }
        ul li strong {
            color: #475569;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0" style="color: white;"><i class="bi bi-check-circle"></i> Proceso Completado</h2>
                <a href="salida_repuesto.php" class="btn btn-light">
                    <i class="bi bi-plus-circle"></i> Nueva Salida de Repuesto
                </a>
            </div>
        </div>
        <div class="card-body">
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
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Error:</strong> No se encontró información completa del proceso.
        </div>
    <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
