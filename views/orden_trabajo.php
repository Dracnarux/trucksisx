<?php
require_once '../config/db.php';
$conn = conectarDB();

$estado = $_GET['estado'] ?? '';
$prioridad = $_GET['prioridad'] ?? '';
$condicion = [];
if ($estado) $condicion[] = "estado = '" . $conn->real_escape_string($estado) . "'";
if ($prioridad) $condicion[] = "prioridad = '" . $conn->real_escape_string($prioridad) . "'";
$where = $condicion ? 'WHERE ' . implode(' AND ', $condicion) : '';

$sql = "SELECT * FROM ord_trabj $where ORDER BY fecha_creacion DESC";
$result = $conn->query($sql);

require_once '../models/User.php';
$userModel = new User();
$conductores = $userModel->getConductores();
$tecnicos = $userModel->getTecnicos();
// No imprimir ni mostrar estas variables en el HTML, solo usarlas en los selectores y la tabla
require_once '../models/Alert.php';
$alertModel = new Alert();
$alertas = $alertModel->getAll();
require_once '../models/Repue.php';
$repueModel = new Repue();
$repues = $repueModel->getAll();
$conductoresById = [];
foreach($conductores as $c) {
    $conductoresById[$c['id']] = $c['nombre'] . ' ' . $c['apellido'];
}
$tecnicosById = [];
foreach($tecnicos as $t) {
    $tecnicosById[$t['id']] = $t['nombre'] . ' ' . $t['apellido'];
}
// No imprimir nada de estas variables, solo usarlas en los selectores y la tabla
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Órdenes de Trabajo - TruckSISX</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/truck-alerts.css" rel="stylesheet">
    <style>
        .header-container {
            background: rgba(13,110,253,0.25);
            color: #111;
            padding: 20px 0;
            margin-bottom: 30px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 2px solid rgba(13,110,253,0.3);
        }
        .control-panel {
            background: rgba(13,110,253,0.10);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10);
            margin-bottom: 30px;
            color: #111;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(13,110,253,0.15);
        }
        .card {
            background: rgba(13,110,253,0.15);
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            color: #111;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(13,110,253,0.2);
        }
        .card-header {
            background: rgba(13,110,253,0.25) !important;
            color: #111 !important;
            border-bottom: 1px solid rgba(13,110,253,0.2);
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
        .form-select, .form-label, .table, .table th, .table td {
            color: #111 !important;
        }
        .table-dark {
            background: rgba(13,110,253,0.10) !important;
            color: #111 !important;
            border-bottom: 2px solid rgba(13,110,253,0.15);
        }
        .badge {
            background: #0d6efd !important;
            color: #fff !important;
        }
    </style>
</head>
<body>
    <div class="header-container">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-clipboard-list"></i> Órdenes de Trabajo</h1>
                    <p class="mb-0">Gestión y seguimiento de órdenes generadas en el sistema</p>
                </div>
                <div class="col-md-4 text-end d-flex flex-column align-items-end gap-2">
                    <a href="truck_alerts.php" class="btn btn-outline-primary mb-2">
                        <i class="fas fa-arrow-left"></i> Volver a Alertas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="control-panel mb-4">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="completada">Completada</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Prioridad</label>
                    <select name="prioridad" class="form-select">
                        <option value="">Todas</option>
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                        <option value="critica">Crítica</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h2 class="mb-0"><i class="fas fa-plus"></i> Crear Nueva Orden</h2>
            </div>
            <div class="card-body">
                <form method="post" action="crear_orden.php" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del trabajo</label>
                        <input type="text" name="nombre_trabajo" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Repuesto (opcional)</label>
                        <select name="nombre_repuesto" class="form-select">
                            <option value="">Sin repuesto</option>
                            <?php while($repue = $repues->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($repue['nombre']) ?>">
                                    <?= htmlspecialchars($repue['nombre']) ?> (ID: <?= $repue['id'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" required></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha Estimada</label>
                        <input type="date" name="fecha_estimada" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="completada">Completada</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Prioridad</label>
                        <select name="prioridad" class="form-select">
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                            <option value="critica">Crítica</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Conductor</label>
                        <select name="cond_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach($conductores as $conductor): ?>
                                <option value="<?= $conductor['id'] ?>">
                                    <?= htmlspecialchars($conductor['nombre'] . ' ' . $conductor['apellido'] . ' (' . $conductor['num_documento'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Técnico</label>
                        <select name="users_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach($tecnicos as $tecnico): ?>
                                <option value="<?= $tecnico['id'] ?>">
                                    <?= htmlspecialchars($tecnico['nombre'] . ' ' . $tecnico['apellido'] . ' (' . $tecnico['num_documento'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Alerta (opcional)</label>
                        <select name="alert_id" class="form-select">
                            <option value="">Sin alerta</option>
                            <?php foreach($alertas as $alerta): ?>
                                <option value="<?= $alerta['id'] ?>">
                                    <?= htmlspecialchars($alerta['descripcion']) ?> (ID: <?= $alerta['id'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Crear Orden
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="fas fa-list"></i> Listado de Órdenes</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Trabajo</th>
                                <th>Descripción</th>
                                <th>Repuesto</th>
                                <th>Fecha Creación</th>
                                <th>Fecha Estimada</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Conductor</th>
                                <th>Usuario</th>
                                <th>Alerta</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['nombre_trabajo']) ?></td>
                                <td><?= htmlspecialchars($row['descripcion']) ?></td>
                                <td><?= htmlspecialchars($row['nombre_repuesto']) ?></td>
                                <td><?= $row['fecha_creacion'] ?></td>
                                <td><?= $row['fecha_estimada'] ?></td>
                                <td><span class="badge"><?= $row['estado'] ?></span></td>
                                <td><span class="badge"><?= $row['prioridad'] ?></span></td>
                                <td><?= isset($conductoresById[$row['cond_id']]) ? htmlspecialchars($conductoresById[$row['cond_id']]) : $row['cond_id'] ?></td>
                                <td><?= isset($tecnicosById[$row['users_id']]) ? htmlspecialchars($tecnicosById[$row['users_id']]) : $row['users_id'] ?></td>
                                <td><?= $row['alert_id'] ?></td>
                                <td>
                                    <a href="editar_orden.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <a href="eliminar_orden.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar esta orden?');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
