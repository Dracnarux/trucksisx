<?php
require_once '../config/db.php';
require_once '../models/Repue.php';
require_once '../models/OrdTrabj.php';
require_once '../models/Alert.php';
require_once '../models/Repor.php';

$db = conectarDB();
$repues = (new Repue($db))->getAll();
$ordenes = (new OrdTrabj($db))->getAll();
$alertas = (new Alert($db))->getAll();
$reportes = (new Repor($db))->getAll();
session_start();
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Salida de Repuestos</title>
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
        h2, h3 {
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
        .btn-warning {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #111 !important;
        }
        .btn-danger {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
        }
        .table-primary {
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
<div class="container mt-4">
    <div class="mb-3 d-flex justify-content-end">
        <a href="dashboard.php" class="btn btn-secondary">Volver al dashboard</a>
    </div>
    <h2>Registrar Salida de Repuestos</h2>
    <?php if (!$rol_conductor): ?>
    <form action="../controllers/SaliRepueController.php?action=registrar" method="POST">
        <div class="mb-3">
            <label for="fecha_salida" class="form-label">Fecha de Salida</label>
            <input type="date" class="form-control" name="fecha_salida" required>
        </div>
        <div class="mb-3">
            <label for="cantidad" class="form-label">Cantidad</label>
            <input type="number" class="form-control" name="cantidad" min="1" required>
        </div>
        <div class="mb-3">
            <label for="repue_id" class="form-label">Repuesto</label>
            <select class="form-select" name="repue_id" required>
                <option value="">Seleccione...</option>
                <?php foreach ($repues as $r): ?>
                    <option value="<?= $r['id'] ?>"><?= $r['nombre'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="ord_trabj_id" class="form-label">Orden de Trabajo</label>
            <select class="form-select" name="ord_trabj_id" required>
                <option value="">Seleccione...</option>
                <?php foreach ($ordenes as $o): ?>
                    <option value="<?= $o['id'] ?>"><?= $o['nombre_trabajo'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <!-- El código de reporte ahora se genera automáticamente, no se selecciona manualmente -->
        </div>
        <div class="mb-3">
            <label for="alerta_id" class="form-label">Alerta del Sistema</label>
            <select class="form-select" name="alerta_id" required>
                <option value="">Seleccione...</option>
                <?php foreach ($alertas as $a): ?>
                    <option value="<?= $a['id'] ?>">#<?= $a['id'] ?> - <?= $a['descripcion'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Registrar Salida</button>

    </form>
    <?php endif; ?>
    <hr>
    <h3 class="mb-4">Salidas de Repuestos Registradas</h3>
    <form class="d-flex mb-2" method="get" action="">
        <input type="text" name="filtro_repue" class="form-control form-control-sm me-2" placeholder="Filtrar por repuesto, orden o alerta" value="<?= isset($_GET['filtro_repue']) ? htmlspecialchars($_GET['filtro_repue']) : '' ?>">
        <button class="btn btn-sm btn-outline-primary" type="submit">Filtrar</button>
    </form>
    <?php
    $salidas = $db->query("SELECT sr.id, sr.fecha_salida, sr.cantidad, r.nombre AS repuesto, ot.nombre_trabajo, a.descripcion AS alerta, sr.ord_trabj_id, sr.alerta_id FROM sali_repue sr LEFT JOIN repue r ON sr.repue_id = r.id LEFT JOIN ord_trabj ot ON sr.ord_trabj_id = ot.id LEFT JOIN alert a ON sr.alerta_id = a.id ORDER BY sr.fecha_salida DESC");
    $filtro = isset($_GET['filtro_repue']) ? strtolower($_GET['filtro_repue']) : '';
    ?>
    <table class="table table-bordered table-sm align-middle">
        <thead class="table-primary">
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
        <?php while($row = $salidas->fetch_assoc()): 
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
                    <a href="editar_salida_repuesto.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="../controllers/SaliRepueController.php?action=eliminar&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta salida?')">Eliminar</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <hr>
    </form>
