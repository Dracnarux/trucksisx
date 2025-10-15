<?php
require_once '../config/db.php';
require_once '../models/SaliRepue.php';
require_once '../models/SaliVehi.php';
require_once '../models/OrdTrabj.php';
require_once '../models/Alert.php';
require_once '../models/Repor.php';

// Initialize database connection
$db = conectarDB();

$sali_repue_id = isset($_GET['sali_repue_id']) ? $_GET['sali_repue_id'] : null;
$sali_repue = $sali_repue_id ? (new SaliRepue($db))->getById($sali_repue_id) : null;
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
    <title>Salida de Vehículo</title>
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
        h2, h4 {
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
        .table-bordered, .table-striped, .table th, .table td {
            color: #111 !important;
        }
        thead tr {
            background: rgba(13,110,253,0.10) !important;
            color: #111 !important;
            border-bottom: 2px solid rgba(13,110,253,0.15);
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="mb-3 d-flex justify-content-end">
        <a href="dashboard.php" class="btn btn-secondary">Volver al dashboard</a>
    </div>
    <h2>Registrar Salida de Vehículo</h2>
    <?php if (!$rol_conductor): ?>
    <form action="../controllers/SaliVehiController.php?action=registrar" method="POST">
        <div class="mb-3">
            <label for="sali_repue_id" class="form-label">ID Salida de Repuesto</label>
            <select class="form-select" name="sali_repue_id" required>
                <option value="">Seleccione...</option>
                <?php foreach ((new SaliRepue($db))->getAll() as $sr): ?>
                    <option value="<?= $sr['id'] ?>">#<?= $sr['id'] ?> - <?= $sr['fecha_salida'] ?> (<?= $sr['cantidad'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="ord_trabj_id" class="form-label">ID Orden de Trabajo</label>
            <select class="form-select" name="ord_trabj_id" required>
                <option value="">Seleccione...</option>
                <?php foreach ($ordenes as $o): ?>
                    <option value="<?= $o['id'] ?>">#<?= $o['id'] ?> - <?= $o['nombre_trabajo'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="alerta_id" class="form-label">ID Alerta</label>
            <select class="form-select" name="alerta_id" required>
                <option value="">Seleccione...</option>
                <?php foreach ($alertas as $a): ?>
                    <option value="<?= $a['id'] ?>">#<?= $a['id'] ?> - <?= $a['descripcion'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
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
    <hr>
    <form class="row g-3 mb-3" method="get" action="">
        <div class="col-md-3">
            <input type="number" name="filtro_id" class="form-control" placeholder="ID Salida Vehículo" value="<?= isset($_GET['filtro_id']) ? htmlspecialchars($_GET['filtro_id']) : '' ?>">
        </div>
        <div class="col-md-3">
            <input type="number" name="filtro_orden" class="form-control" placeholder="ID Orden de Trabajo" value="<?= isset($_GET['filtro_orden']) ? htmlspecialchars($_GET['filtro_orden']) : '' ?>">
        </div>
        <div class="col-md-3">
            <input type="number" name="filtro_alerta" class="form-control" placeholder="ID Alerta" value="<?= isset($_GET['filtro_alerta']) ? htmlspecialchars($_GET['filtro_alerta']) : '' ?>">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>
    <h4 class="mt-4">Salidas de Vehículos Registradas</h4>
    <?php
    $salidas_vehiculo = (new SaliVehi($db))->getAll();
    // Filtro por ID, orden de trabajo y alerta
    if (isset($_GET['filtro_id']) && $_GET['filtro_id'] !== '') {
        $salidas_vehiculo = array_filter($salidas_vehiculo, function($sv) {
            return $sv['id'] == $_GET['filtro_id'];
        });
    }
    if (isset($_GET['filtro_orden']) && $_GET['filtro_orden'] !== '') {
        $salidas_vehiculo = array_filter($salidas_vehiculo, function($sv) {
            return $sv['ord_trabj_id'] == $_GET['filtro_orden'];
        });
    }
    if (isset($_GET['filtro_alerta']) && $_GET['filtro_alerta'] !== '') {
        $salidas_vehiculo = array_filter($salidas_vehiculo, function($sv) {
            return $sv['alerta_id'] == $_GET['filtro_alerta'];
        });
    }
    if (count($salidas_vehiculo) > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID Flota</th>
                    <th>Monitoreo</th>
                    <th>Combustible</th>
                    <th>Regulaciones</th>
                    <th>Seguridad</th>
                    <th>Conductores</th>
                    <th>Orden Trabajo</th>
                    <th>Alerta</th>
                    <th>Reporte</th>
                    <th>Salida Repuesto</th>
                    <th style="min-width:120px">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($salidas_vehiculo as $sv): ?>
                <tr>
                    <td><?= $sv['id'] ?></td>
                    <td><?= $sv['id_flotas'] ?></td>
                    <td><?= htmlspecialchars($sv['segui_monitoreo']) ?></td>
                    <td><?= htmlspecialchars($sv['control_combustible']) ?></td>
                    <td><?= htmlspecialchars($sv['cump_regulaciones']) ?></td>
                    <td><?= htmlspecialchars($sv['protocolo_seguridad']) ?></td>
                    <td><?= htmlspecialchars($sv['gest_conductores']) ?></td>
                    <td><?= $sv['ord_trabj_id'] ?></td>
                    <td><?= $sv['alerta_id'] ?></td>
                    <td><?= $sv['repor_id'] ?></td>
                    <td><?= $sv['sali_repue_id'] ?></td>
                    <td class="text-center">
                        <?php if (!$rol_conductor): ?>
                        <a href="editar_salida_vehiculo.php?id=<?= $sv['id'] ?>" class="btn btn-sm btn-warning me-1">Editar</a>
                        <a href="eliminar_salida_vehiculo.php?id=<?= $sv['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar esta salida de vehículo?');">Eliminar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="alert alert-info">No hay salidas de vehículos registradas.</div>
    <?php endif; ?>
</div>
</body>
</html>
