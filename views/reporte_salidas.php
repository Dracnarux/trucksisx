<?php
require_once '../config/db.php';
$db = conectarDB();
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
        <a href="../views/salida_repuesto.php" class="btn btn-sm btn-success">Crear nueva salida</a>
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
                    <a href="../views/editar_salida_repuesto.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="../controllers/SaliRepueController.php?action=eliminar&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta salida?')">Eliminar</a>
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
                <th>ID Flota</th>
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
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
