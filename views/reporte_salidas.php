<?php
require_once '../config/db.php';
session_start();
$db = conectarDB();
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
// Consulta para salidas de repuestos
$salidas_repue = $db->query("SELECT sr.id, sr.fecha_salida, sr.cantidad, r.nombre AS repuesto, ot.nombre_trabajo, a.descripcion AS alerta, sr.ord_trabj_id, sr.alerta_id
FROM sali_repue sr
LEFT JOIN repue r ON sr.repue_id = r.id
LEFT JOIN ord_trabj ot ON sr.ord_trabj_id = ot.id
LEFT JOIN alert a ON sr.alerta_id = a.id
ORDER BY sr.fecha_salida DESC");
// Consulta para salidas de vehículos
$salidas_vehi = $db->query("SELECT sv.id, sv.id_flotas, sv.segui_monitoreo, sv.control_combustible, sv.cump_regulaciones, sv.protocolo_seguridad, sv.gest_conductores, ot.nombre_trabajo, a.descripcion AS alerta, sv.ord_trabj_id, sv.alerta_id
FROM sali_vehi sv
LEFT JOIN ord_trabj ot ON sv.ord_trabj_id = ot.id
LEFT JOIN alert a ON sv.alerta_id = a.id
ORDER BY sv.id DESC");
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Reporte de Salidas</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
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
        h2, h3, h4, h5 {
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
        .btn-success {
            background-color: #198754 !important;
            border-color: #198754 !important;
        }
        .btn-warning {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #111 !important;
        }
        .btn-danger {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
        }
        .table-primary, .table-warning {
            background: rgba(13,110,253,0.10) !important;
            color: #111 !important;
            border-bottom: 2px solid rgba(13,110,253,0.15);
        }
        .table-bordered, .table-sm, .table th, .table td {
            color: #111 !important;
        }
    </style>
</head>
<body>
<div class='container mt-4'>
    <div class="mb-3 d-flex justify-content-end">
        <a href="dashboard.php" class="btn btn-secondary">Volver al dashboard</a>
    </div>
    <h2>Reporte Consolidado de Salidas</h2>
    <h4 class='mt-4'>Salidas de Repuestos</h4>
    <div class="mb-2 d-flex justify-content-between align-items-center">
        <form class="d-flex" method="get" action="">
            <input type="text" name="filtro_repue" class="form-control form-control-sm me-2" placeholder="Filtrar por repuesto, orden o alerta" value="<?= isset($_GET['filtro_repue']) ? htmlspecialchars($_GET['filtro_repue']) : '' ?>">
            <button class="btn btn-sm btn-outline-primary" type="submit">Filtrar</button>
        </form>
    <?php if (!$rol_conductor): ?>
    <a href="../views/salida_repuesto.php" class="btn btn-sm btn-success">Crear nueva salida</a>
    <?php endif; ?>
    </div>
    <table class='table table-bordered table-sm align-middle'>
        <thead class='table-primary'>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Cantidad</th>
                <th>Repuesto</th>
                <th>Orden de Trabajo</th>
                <th>Alerta</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // Filtro simple en PHP
        $salidas_repue->data_seek(0);
        $filtro = isset($_GET['filtro_repue']) ? strtolower($_GET['filtro_repue']) : '';
        while($row = $salidas_repue->fetch_assoc()):
            $texto = strtolower($row['repuesto'] . ' ' . $row['nombre_trabajo'] . ' ' . $row['alerta']);
            if ($filtro && strpos($texto, $filtro) === false) continue;
        ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['fecha_salida'] ?></td>
                <td><?= $row['cantidad'] ?></td>
                <td><?= $row['repuesto'] ?></td>
                <td><?= $row['nombre_trabajo'] ?></td>
                <td><?= $row['alerta'] ?></td>
                <td>
                    <?php if (!$rol_conductor): ?>
                    <a href="../views/editar_salida_repuesto.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="../controllers/SaliRepueController.php?action=eliminar&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta salida?')">Eliminar</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <h4 class='mt-5'>Salidas de Vehículos</h4>
    <table class='table table-bordered table-sm'>
        <thead class='table-warning'>
            <tr>
                <th>ID</th>
                <th>ID Flotas</th>
                <th>Seguimiento/Monitoreo</th>
                <th>Combustible</th>
                <th>Regulaciones</th>
                <th>Protocolos</th>
                <th>Conductores</th>
                <th>Orden de Trabajo</th>
                <th>Alerta</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $salidas_vehi->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['id_flotas'] ?></td>
                <td><?= $row['segui_monitoreo'] ?></td>
                <td><?= $row['control_combustible'] ?></td>
                <td><?= $row['cump_regulaciones'] ?></td>
                <td><?= $row['protocolo_seguridad'] ?></td>
                <td><?= $row['gest_conductores'] ?></td>
                <td><?= $row['nombre_trabajo'] ?></td>
                <td><?= $row['alerta'] ?></td>
                <td class="text-center">
                    <?php if (!$rol_conductor): ?>
                    <a href="../views/editar_salida_vehiculo.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1">Editar</a>
                    <a href="../controllers/SaliVehiController.php?action=eliminar&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta salida de vehículo?')">Eliminar</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <?php if (!$rol_conductor): ?>
    <div class="mt-4 d-flex justify-content-end">
        <form method="get" action="">
            <button type="submit" name="reporte_completo" value="1" class="btn btn-lg btn-primary">Generar reporte completo</button>
        </form>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['reporte_completo'])): ?>
    <hr>
    <h3 class="mt-5 text-center">Reporte Completo de Salidas</h3>
    <h5 class="mb-3">Salidas de Repuestos</h5>
    <table class="table table-bordered table-sm align-middle">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Cantidad</th>
                <th>Repuesto</th>
                <th>Orden de Trabajo</th>
                <th>Alerta</th>
            </tr>
        </thead>
        <tbody>
        <?php $salidas_repue->data_seek(0); while($row = $salidas_repue->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['fecha_salida'] ?></td>
                <td><?= $row['cantidad'] ?></td>
                <td><?= $row['repuesto'] ?></td>
                <td><?= $row['nombre_trabajo'] ?></td>
                <td><?= $row['alerta'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <h5 class="mb-3 mt-5">Salidas de Vehículos</h5>
    <table class="table table-bordered table-sm align-middle">
        <thead class="table-warning">
            <tr>
                <th>ID</th>
                <th>ID Flotas</th>
                <th>Seguimiento/Monitoreo</th>
                <th>Combustible</th>
                <th>Regulaciones</th>
                <th>Protocolos</th>
                <th>Conductores</th>
                <th>Orden de Trabajo</th>
                <th>Alerta</th>
            </tr>
        </thead>
        <tbody>
        <?php $salidas_vehi->data_seek(0); while($row = $salidas_vehi->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['id_flotas'] ?></td>
                <td><?= $row['segui_monitoreo'] ?></td>
                <td><?= $row['control_combustible'] ?></td>
                <td><?= $row['cump_regulaciones'] ?></td>
                <td><?= $row['protocolo_seguridad'] ?></td>
                <td><?= $row['gest_conductores'] ?></td>
                <td><?= $row['nombre_trabajo'] ?></td>
                <td><?= $row['alerta'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
</body>
</html>
