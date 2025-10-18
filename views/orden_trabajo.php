<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
$usuario_nombre = $_SESSION['usuario']['nombre'] ?? 'Usuario';

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
$conductores = $userModel->getConductoresCond();
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
    $conductoresById[$c['id']] = $c['nombre'];
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .main-header {
            background: linear-gradient(135deg, #64748b, #475569);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #64748b, #475569);
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .btn-outline-primary {
            border-color: #64748b;
            color: #64748b;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s ease;
        }
        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #64748b, #475569);
            border-color: #64748b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .btn-success {
            background: linear-gradient(135deg, #198754, #146c43);
            border: none;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #f0ad4e);
            border: none;
            border-radius: 25px;
            color: #000;
            transition: all 0.3s ease;
        }
        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .modal-header {
            background: linear-gradient(135deg, #64748b, #475569);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .form-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .form-section h6 {
            color: #495057;
            margin-bottom: 0.8rem;
            font-weight: 600;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .table thead {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }
        .table-responsive {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #64748b;
            box-shadow: 0 0 0 0.2rem rgba(100, 116, 139, 0.25);
        }
        .badge {
            border-radius: 20px;
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
        .badge-success {
            background: linear-gradient(135deg, #198754, #146c43);
        }
        .badge-warning {
            background: linear-gradient(135deg, #ffc107, #f0ad4e);
            color: #000;
        }
        .badge-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }
        .badge-info {
            background: linear-gradient(135deg, #0dcaf0, #31d2f2);
            color: #000;
        }
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .table-hover tbody tr:hover {
            background-color: rgba(100, 116, 139, 0.1);
        }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid py-4">
    <div class="main-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">
                    <i class="fas fa-clipboard-list"></i> Órdenes de Trabajo
                    <?php if ($rol_conductor): ?><small class="opacity-75"> (Solo lectura)</small><?php endif; ?>
                </h1>
                <p class="mb-0 opacity-75">
                    <?php if ($rol_conductor): ?>
                        Visualización de órdenes del sistema
                    <?php else: ?>
                        Gestión y seguimiento de órdenes generadas en el sistema
                    <?php endif; ?>
                </p>
            </div>
            <div class="d-flex gap-2">
                <?php if (!$rol_conductor): ?>
                <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalCrearOrden">
                    <i class="bi bi-plus-circle"></i> Nueva Orden
                </button>
                <?php endif; ?>
                <a href="truck_alerts.php" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Volver a Alertas
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">
                <i class="bi bi-funnel"></i> Filtros de Búsqueda
            </h5>
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" <?= $estado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="en_proceso" <?= $estado === 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                        <option value="completada" <?= $estado === 'completada' ? 'selected' : '' ?>>Completada</option>
                    
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Prioridad</label>
                    <select name="prioridad" class="form-select">
                        <option value="">Todas las prioridades</option>
                        <option value="baja" <?= $prioridad === 'baja' ? 'selected' : '' ?>>Baja</option>
                        <option value="media" <?= $prioridad === 'media' ? 'selected' : '' ?>>Media</option>
                        <option value="alta" <?= $prioridad === 'alta' ? 'selected' : '' ?>>Alta</option>
                        <option value="critica" <?= $prioridad === 'critica' ? 'selected' : '' ?>>Crítica</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="orden_trabajo.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Órdenes -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
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
                            <th>Técnico</th>
                            <th>Alerta</th>
                            <th class="text-center" width="140">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?= $row['id'] ?></strong></td>
                                <td><?= htmlspecialchars($row['nombre_trabajo']) ?></td>
                                <td><?= htmlspecialchars($row['descripcion']) ?></td>
                                <td><?= htmlspecialchars($row['nombre_repuesto']) ?: '<span class="text-muted">Sin repuesto</span>' ?></td>
                                <td><?= $row['fecha_creacion'] ?></td>
                                <td><?= $row['fecha_estimada'] ?></td>
                                <td>
                                    <?php 
                                    $estado_class = '';
                                    switch($row['estado']) {
                                        case 'completada': $estado_class = 'badge-success'; break;
                                        case 'en_proceso': $estado_class = 'badge-info'; break;
                                        case 'pendiente': $estado_class = 'badge-warning'; break;
                                        case 'cancelada': $estado_class = 'badge-danger'; break;
                                        default: $estado_class = 'badge-secondary';
                                    }
                                    ?>
                                    <span class="badge <?= $estado_class ?>"><?= ucfirst($row['estado']) ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $prioridad_class = '';
                                    switch($row['prioridad']) {
                                        case 'critica': $prioridad_class = 'badge-danger'; break;
                                        case 'alta': $prioridad_class = 'badge-warning'; break;
                                        case 'media': $prioridad_class = 'badge-info'; break;
                                        case 'baja': $prioridad_class = 'badge-success'; break;
                                        default: $prioridad_class = 'badge-secondary';
                                    }
                                    ?>
                                    <span class="badge <?= $prioridad_class ?>"><?= ucfirst($row['prioridad']) ?></span>
                                </td>
                                <td><?= isset($conductoresById[$row['cond_id']]) ? htmlspecialchars($conductoresById[$row['cond_id']]) : ($row['cond_id'] ? $row['cond_id'] : '<span class="text-muted">Sin asignar</span>') ?></td>
                                <td><?= isset($tecnicosById[$row['users_id']]) ? htmlspecialchars($tecnicosById[$row['users_id']]) : $row['users_id'] ?></td>
                                <td><?= $row['alert_id'] ?: '<span class="text-muted">Sin alerta</span>' ?></td>
                                <?php if (!$rol_conductor): ?>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info" onclick="verOrden(<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>)" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editarOrden(<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmarEliminar(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nombre_trabajo'], ENT_QUOTES) ?>')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                                <?php else: ?>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info" onclick="verOrden(<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Modal de Confirmación para Eliminar -->
    <div class="modal fade" id="modalEliminarOrden" tabindex="-1" aria-labelledby="modalEliminarOrdenLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalEliminarOrdenLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fas fa-trash-alt text-danger" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h6>¿Estás seguro de que deseas eliminar esta orden de trabajo?</h6>
                        <p class="text-muted mb-3">Esta acción no se puede deshacer.</p>
                        <div class="alert alert-warning">
                            <strong>Orden ID:</strong> <span id="ordenIdEliminar"></span><br>
                            <strong>Trabajo:</strong> <span id="ordenNombreEliminar"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <a href="#" id="btnConfirmarEliminar" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Eliminar Orden
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Notificaciones -->
    <div class="modal fade" id="modalNotificacion" tabindex="-1" aria-labelledby="modalNotificacionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" id="modalNotificacionHeader">
                    <h5 class="modal-title" id="modalNotificacionLabel">
                        <i class="fas fa-info-circle me-2"></i>Notificación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i id="modalNotificacionIcon" class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h6 id="modalNotificacionTitulo">Operación Exitosa</h6>
                        <p id="modalNotificacionMensaje" class="text-muted"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-check me-1"></i>Entendido
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Crear Orden -->
    <div class="modal fade" id="modalCrearOrden" tabindex="-1" aria-labelledby="modalCrearOrdenLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearOrdenLabel">
                        <i class="fas fa-plus-circle me-2"></i>Crear Nueva Orden de Trabajo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="crear_orden.php" id="formCrearOrden">
                    <div class="modal-body">
                        <!-- Información Básica -->
                        <div class="form-section">
                            <h6><i class="bi bi-info-circle"></i> Información Básica</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre del trabajo *</label>
                                    <input type="text" name="nombre_trabajo" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Estimada *</label>
                                    <input type="date" name="fecha_estimada" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descripción *</label>
                                    <textarea name="descripcion" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración -->
                        <div class="form-section">
                            <h6><i class="bi bi-gear"></i> Configuración</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Estado</label>
                                    <select name="estado" class="form-select">
                                        <option value="pendiente">Pendiente</option>
                                        <option value="en_proceso">En Proceso</option>
                                        <option value="completada">Completada</option>
                                        <option value="cancelada">Cancelada</option>
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
                                    <label class="form-label">Repuesto (opcional)</label>
                                    <select name="nombre_repuesto" class="form-select">
                                        <option value="">Sin repuesto</option>
                                        <?php 
                                        $repues->data_seek(0); // Reset pointer
                                        while($repue = $repues->fetch_assoc()): ?>
                                            <option value="<?= htmlspecialchars($repue['nombre']) ?>">
                                                <?= htmlspecialchars($repue['nombre']) ?> (ID: <?= $repue['id'] ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Asignaciones -->
                        <div class="form-section">
                            <h6><i class="bi bi-people"></i> Asignaciones</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Conductor</label>
                                    <select name="cond_id" class="form-select">
                                        <option value="">Sin asignar</option>
                                        <?php foreach($conductores as $conductor): ?>
                                            <option value="<?= $conductor['id'] ?>">
                                                <?= htmlspecialchars($conductor['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Técnico *</label>
                                    <select name="users_id" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach($tecnicos as $tecnico): ?>
                                            <option value="<?= $tecnico['id'] ?>">
                                                <?= htmlspecialchars($tecnico['nombre'] . ' ' . $tecnico['apellido']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Alerta</label>
                                    <select name="alert_id" class="form-select" required>
                                        <option value="">Seleccione</option>
                                        <?php foreach($alertas as $alerta): ?>
                                            <option value="<?= $alerta['id'] ?>">
                                                <?= htmlspecialchars($alerta['descripcion']) ?> (ID: <?= $alerta['id'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Crear Orden
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Editar Orden -->
    <div class="modal fade" id="modalEditarOrden" tabindex="-1" aria-labelledby="modalEditarOrdenLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarOrdenLabel">
                        <i class="fas fa-edit me-2"></i>Editar Orden de Trabajo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="editar_orden.php" id="formEditarOrden">
                    <input type="hidden" name="id" id="editar_id">
                    <div class="modal-body">
                        <!-- Información Básica -->
                        <div class="form-section">
                            <h6><i class="bi bi-info-circle"></i> Información Básica</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre del trabajo *</label>
                                    <input type="text" name="nombre_trabajo" id="editar_nombre_trabajo" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Estimada *</label>
                                    <input type="date" name="fecha_estimada" id="editar_fecha_estimada" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descripción *</label>
                                    <textarea name="descripcion" id="editar_descripcion" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración -->
                        <div class="form-section">
                            <h6><i class="bi bi-gear"></i> Configuración</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Estado</label>
                                    <select name="estado" id="editar_estado" class="form-select">
                                        <option value="pendiente">Pendiente</option>
                                        <option value="en_proceso">En Proceso</option>
                                        <option value="completada">Completada</option>
                                        <option value="cancelada">Cancelada</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Prioridad</label>
                                    <select name="prioridad" id="editar_prioridad" class="form-select">
                                        <option value="baja">Baja</option>
                                        <option value="media">Media</option>
                                        <option value="alta">Alta</option>
                                        <option value="critica">Crítica</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Repuesto (opcional)</label>
                                    <select name="nombre_repuesto" id="editar_nombre_repuesto" class="form-select">
                                        <option value="">Sin repuesto</option>
                                        <?php 
                                        $repues->data_seek(0); // Reset pointer
                                        while($repue = $repues->fetch_assoc()): ?>
                                            <option value="<?= htmlspecialchars($repue['nombre']) ?>">
                                                <?= htmlspecialchars($repue['nombre']) ?> (ID: <?= $repue['id'] ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Asignaciones -->
                        <div class="form-section">
                            <h6><i class="bi bi-people"></i> Asignaciones</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Conductor</label>
                                    <select name="cond_id" id="editar_cond_id" class="form-select">
                                        <option value="">Sin asignar</option>
                                        <?php foreach($conductores as $conductor): ?>
                                            <option value="<?= $conductor['id'] ?>">
                                                <?= htmlspecialchars($conductor['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Técnico *</label>
                                    <select name="users_id" id="editar_users_id" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach($tecnicos as $tecnico): ?>
                                            <option value="<?= $tecnico['id'] ?>">
                                                <?= htmlspecialchars($tecnico['nombre'] . ' ' . $tecnico['apellido']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Alerta (opcional)</label>
                                    <select name="alert_id" id="editar_alert_id" class="form-select">
                                        <option value="">Sin alerta</option>
                                        <?php foreach($alertas as $alerta): ?>
                                            <option value="<?= $alerta['id'] ?>">
                                                <?= htmlspecialchars($alerta['descripcion']) ?> (ID: <?= $alerta['id'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i>Actualizar Orden
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Ver Detalles -->
    <div class="modal fade" id="modalVerOrden" tabindex="-1" aria-labelledby="modalVerOrdenLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalVerOrdenLabel">
                        <i class="fas fa-eye me-2"></i>Detalles de la Orden de Trabajo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Información Principal -->
                    <div class="form-section">
                        <h6><i class="bi bi-info-circle"></i> Información Principal</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted">ID de la Orden</label>
                                <p class="fw-bold mb-0" id="ver_id">#123</p>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label text-muted">Nombre del Trabajo</label>
                                <p class="fw-bold mb-0" id="ver_nombre_trabajo">Nombre del trabajo</p>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted">Descripción</label>
                                <p class="mb-0" id="ver_descripcion">Descripción del trabajo</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Repuesto</label>
                                <p class="mb-0" id="ver_repuesto">
                                    <span class="badge bg-secondary">Sin repuesto</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Alerta Asociada</label>
                                <p class="mb-0" id="ver_alerta">
                                    <span class="badge bg-secondary">Sin alerta</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Fechas y Estado -->
                    <div class="form-section">
                        <h6><i class="bi bi-calendar"></i> Fechas y Estado</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted">Fecha de Creación</label>
                                <p class="mb-0" id="ver_fecha_creacion">2023-10-17</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted">Fecha Estimada</label>
                                <p class="mb-0" id="ver_fecha_estimada">2023-10-20</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted">Estado</label>
                                <p class="mb-0" id="ver_estado">
                                    <span class="badge badge-warning">Pendiente</span>
                                </p>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-muted">Prioridad</label>
                                <p class="mb-0" id="ver_prioridad">
                                    <span class="badge badge-info">Media</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Asignaciones -->
                    <div class="form-section">
                        <h6><i class="bi bi-people"></i> Personal Asignado</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Conductor Asignado</label>
                                <p class="mb-0" id="ver_conductor">
                                    <i class="fas fa-user me-2"></i>
                                    <span id="ver_conductor_nombre">Sin asignar</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Técnico Responsable</label>
                                <p class="mb-0" id="ver_tecnico">
                                    <i class="fas fa-user-cog me-2"></i>
                                    <span id="ver_tecnico_nombre">Técnico asignado</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Progreso Visual -->
                    <div class="form-section">
                        <h6><i class="bi bi-graph-up"></i> Progreso</h6>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar" id="ver_progreso" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                <span class="fw-bold" id="ver_progreso_texto">25% - Pendiente</span>
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block">Estado actual del trabajo</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cerrar
                    </button>
                    <div id="ver_acciones_admin" style="display: none;">
                        <button type="button" class="btn btn-warning" onclick="editarDesdeVer()">
                            <i class="fas fa-edit me-1"></i>Editar Orden
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Datos para referencias rápidas
        const conductoresData = <?= json_encode($conductoresById) ?>;
        const tecnicosData = <?= json_encode($tecnicosById) ?>;

        // Función para confirmar eliminación
        function confirmarEliminar(id, nombreTrabajo) {
            document.getElementById('ordenIdEliminar').textContent = id;
            document.getElementById('ordenNombreEliminar').textContent = nombreTrabajo;
            document.getElementById('btnConfirmarEliminar').href = 'eliminar_orden.php?id=' + id;
            
            const modal = new bootstrap.Modal(document.getElementById('modalEliminarOrden'));
            modal.show();
        }

        // Función para editar orden
        function editarOrden(orden) {
            // Llenar los campos del modal con los datos de la orden
            document.getElementById('editar_id').value = orden.id;
            document.getElementById('editar_nombre_trabajo').value = orden.nombre_trabajo;
            document.getElementById('editar_descripcion').value = orden.descripcion;
            document.getElementById('editar_nombre_repuesto').value = orden.nombre_repuesto || '';
            document.getElementById('editar_fecha_estimada').value = orden.fecha_estimada;
            
            // Asignar estado - con validación
            const estadoSelect = document.getElementById('editar_estado');
            estadoSelect.value = orden.estado;
            // Si no se seleccionó correctamente, buscar la opción correspondiente
            if (estadoSelect.value !== orden.estado) {
                const estadoOptions = estadoSelect.options;
                for (let i = 0; i < estadoOptions.length; i++) {
                    if (estadoOptions[i].value === orden.estado || estadoOptions[i].text.toLowerCase() === orden.estado.toLowerCase()) {
                        estadoSelect.selectedIndex = i;
                        break;
                    }
                }
            }
            
            // Asignar prioridad - con validación
            const prioridadSelect = document.getElementById('editar_prioridad');
            prioridadSelect.value = orden.prioridad;
            // Si no se seleccionó correctamente, buscar la opción correspondiente
            if (prioridadSelect.value !== orden.prioridad) {
                const prioridadOptions = prioridadSelect.options;
                for (let i = 0; i < prioridadOptions.length; i++) {
                    if (prioridadOptions[i].value === orden.prioridad || prioridadOptions[i].text.toLowerCase() === orden.prioridad.toLowerCase()) {
                        prioridadSelect.selectedIndex = i;
                        break;
                    }
                }
            }
            
            document.getElementById('editar_cond_id').value = orden.cond_id || '';
            document.getElementById('editar_users_id').value = orden.users_id;
            document.getElementById('editar_alert_id').value = orden.alert_id || '';
            
            // Debug - mostrar valores en consola para verificar (comentado para producción)
            // console.log('Editando orden:', orden);
            // console.log('Estado asignado:', estadoSelect.value);
            // console.log('Prioridad asignada:', prioridadSelect.value);
            
            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('modalEditarOrden'));
            modal.show();
        }

        // Variable global para almacenar la orden actual (para usar en editarDesdeVer)
        let ordenActual = null;

        // Función para ver detalles de la orden
        function verOrden(orden) {
            ordenActual = orden; // Guardar para uso posterior
            
            // Información Principal
            document.getElementById('ver_id').textContent = '#' + orden.id;
            document.getElementById('ver_nombre_trabajo').textContent = orden.nombre_trabajo;
            document.getElementById('ver_descripcion').textContent = orden.descripcion;
            
            // Repuesto
            const repuestoElement = document.getElementById('ver_repuesto');
            if (orden.nombre_repuesto && orden.nombre_repuesto.trim() !== '') {
                repuestoElement.innerHTML = '<span class="badge bg-primary">' + orden.nombre_repuesto + '</span>';
            } else {
                repuestoElement.innerHTML = '<span class="badge bg-secondary">Sin repuesto</span>';
            }
            
            // Alerta
            const alertaElement = document.getElementById('ver_alerta');
            if (orden.alert_id && orden.alert_id !== '0') {
                alertaElement.innerHTML = '<span class="badge bg-warning text-dark">Alerta ID: ' + orden.alert_id + '</span>';
            } else {
                alertaElement.innerHTML = '<span class="badge bg-secondary">Sin alerta</span>';
            }
            
            // Fechas
            document.getElementById('ver_fecha_creacion').textContent = orden.fecha_creacion;
            document.getElementById('ver_fecha_estimada').textContent = orden.fecha_estimada;
            
            // Estado con badge
            const estadoElement = document.getElementById('ver_estado');
            let estadoClass = '';
            switch(orden.estado) {
                case 'completada': estadoClass = 'badge-success'; break;
                case 'en_proceso': estadoClass = 'badge-info'; break;
                case 'pendiente': estadoClass = 'badge-warning'; break;
                case 'cancelada': estadoClass = 'badge-danger'; break;
                default: estadoClass = 'badge-secondary';
            }
            estadoElement.innerHTML = '<span class="badge ' + estadoClass + '">' + orden.estado.charAt(0).toUpperCase() + orden.estado.slice(1) + '</span>';
            
            // Prioridad con badge
            const prioridadElement = document.getElementById('ver_prioridad');
            let prioridadClass = '';
            switch(orden.prioridad) {
                case 'critica': prioridadClass = 'badge-danger'; break;
                case 'alta': prioridadClass = 'badge-warning'; break;
                case 'media': prioridadClass = 'badge-info'; break;
                case 'baja': prioridadClass = 'badge-success'; break;
                default: prioridadClass = 'badge-secondary';
            }
            prioridadElement.innerHTML = '<span class="badge ' + prioridadClass + '">' + orden.prioridad.charAt(0).toUpperCase() + orden.prioridad.slice(1) + '</span>';
            
            // Personal asignado
            const conductorElement = document.getElementById('ver_conductor_nombre');
            if (orden.cond_id && conductoresData[orden.cond_id]) {
                conductorElement.textContent = conductoresData[orden.cond_id];
            } else {
                conductorElement.textContent = 'Sin asignar';
            }
            
            const tecnicoElement = document.getElementById('ver_tecnico_nombre');
            if (orden.users_id && tecnicosData[orden.users_id]) {
                tecnicoElement.textContent = tecnicosData[orden.users_id];
            } else {
                tecnicoElement.textContent = 'Técnico ID: ' + orden.users_id;
            }
            
            // Barra de progreso
            const progresoBar = document.getElementById('ver_progreso');
            const progresoTexto = document.getElementById('ver_progreso_texto');
            let progreso = 0;
            let progresoColor = '';
            let progresoText = '';
            
            switch(orden.estado) {
                case 'pendiente':
                    progreso = 25;
                    progresoColor = 'bg-warning';
                    progresoText = '25% - Pendiente';
                    break;
                case 'en_proceso':
                    progreso = 60;
                    progresoColor = 'bg-info';
                    progresoText = '60% - En Proceso';
                    break;
                case 'completada':
                    progreso = 100;
                    progresoColor = 'bg-success';
                    progresoText = '100% - Completada';
                    break;
                case 'cancelada':
                    progreso = 0;
                    progresoColor = 'bg-danger';
                    progresoText = '0% - Cancelada';
                    break;
                default:
                    progreso = 10;
                    progresoColor = 'bg-secondary';
                    progresoText = '10% - Estado desconocido';
            }
            
            progresoBar.className = 'progress-bar ' + progresoColor;
            progresoBar.style.width = progreso + '%';
            progresoBar.setAttribute('aria-valuenow', progreso);
            progresoTexto.textContent = progresoText;
            
            // Mostrar/ocultar botones de acción según el rol
            const accionesAdmin = document.getElementById('ver_acciones_admin');
            // Usar PHP para determinar si mostrar los botones
            <?php if (!$rol_conductor): ?>
            accionesAdmin.style.display = 'block';
            <?php else: ?>
            accionesAdmin.style.display = 'none';
            <?php endif; ?>
            
            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('modalVerOrden'));
            modal.show();
        }

        // Función para editar desde el modal de ver
        function editarDesdeVer() {
            // Cerrar modal de ver
            const modalVer = bootstrap.Modal.getInstance(document.getElementById('modalVerOrden'));
            modalVer.hide();
            
            // Abrir modal de editar con los datos
            setTimeout(() => {
                editarOrden(ordenActual);
            }, 300);
        }

        // Función para mostrar notificaciones
        function mostrarNotificacion(tipo, titulo, mensaje) {
            const modal = document.getElementById('modalNotificacion');
            const header = document.getElementById('modalNotificacionHeader');
            const icon = document.getElementById('modalNotificacionIcon');
            const tituloElement = document.getElementById('modalNotificacionTitulo');
            const mensajeElement = document.getElementById('modalNotificacionMensaje');
            
            // Configurar colores y iconos según el tipo
            if (tipo === 'exito') {
                header.className = 'modal-header bg-success text-white';
                icon.className = 'fas fa-check-circle text-success';
            } else if (tipo === 'error') {
                header.className = 'modal-header bg-danger text-white';
                icon.className = 'fas fa-exclamation-triangle text-danger';
            } else if (tipo === 'info') {
                header.className = 'modal-header bg-info text-white';
                icon.className = 'fas fa-info-circle text-info';
            }
            
            tituloElement.textContent = titulo;
            mensajeElement.textContent = mensaje;
            
            const modalInstance = new bootstrap.Modal(modal);
            modalInstance.show();
        }

        // Verificar parámetros URL para mostrar notificaciones automáticamente
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const mensaje = urlParams.get('mensaje');
            const tipo = urlParams.get('tipo');
            
            if (mensaje && tipo) {
                let titulo = 'Notificación';
                if (tipo === 'exito') titulo = '¡Operación Exitosa!';
                else if (tipo === 'error') titulo = 'Error en la Operación';
                
                mostrarNotificacion(tipo, titulo, decodeURIComponent(mensaje));
                
                // Limpiar parámetros de la URL
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }

            // Limpiar formularios al cerrar modales
            document.getElementById('modalCrearOrden').addEventListener('hidden.bs.modal', function () {
                document.getElementById('formCrearOrden').reset();
            });

            document.getElementById('modalEditarOrden').addEventListener('hidden.bs.modal', function () {
                document.getElementById('formEditarOrden').reset();
            });

            // Función de debugging para verificar formularios
            window.debugFormulario = function() {
                const estado = document.getElementById('editar_estado').value;
                const prioridad = document.getElementById('editar_prioridad').value;
                console.log('Estado actual:', estado);
                console.log('Prioridad actual:', prioridad);
                console.log('Formulario completo:', new FormData(document.getElementById('formEditarOrden')));
            };
        });

        // Validaciones de formularios
        document.getElementById('formCrearOrden').addEventListener('submit', function(e) {
            const nombreTrabajo = document.querySelector('#modalCrearOrden input[name="nombre_trabajo"]').value.trim();
            const descripcion = document.querySelector('#modalCrearOrden textarea[name="descripcion"]').value.trim();
            const fechaEstimada = document.querySelector('#modalCrearOrden input[name="fecha_estimada"]').value;
            const tecnico = document.querySelector('#modalCrearOrden select[name="users_id"]').value;

            if (!nombreTrabajo) {
                e.preventDefault();
                alert('El nombre del trabajo es obligatorio');
                return false;
            }
            if (!descripcion) {
                e.preventDefault();
                alert('La descripción es obligatoria');
                return false;
            }
            if (!fechaEstimada) {
                e.preventDefault();
                alert('La fecha estimada es obligatoria');
                return false;
            }
            if (!tecnico) {
                e.preventDefault();
                alert('Debe seleccionar un técnico');
                return false;
            }
        });

        document.getElementById('formEditarOrden').addEventListener('submit', function(e) {
            const id = document.querySelector('#modalEditarOrden input[name="id"]').value;
            const nombreTrabajo = document.querySelector('#modalEditarOrden input[name="nombre_trabajo"]').value.trim();
            const descripcion = document.querySelector('#modalEditarOrden textarea[name="descripcion"]').value.trim();
            const fechaEstimada = document.querySelector('#modalEditarOrden input[name="fecha_estimada"]').value;
            const tecnico = document.querySelector('#modalEditarOrden select[name="users_id"]').value;
            const estado = document.querySelector('#modalEditarOrden select[name="estado"]').value;
            const prioridad = document.querySelector('#modalEditarOrden select[name="prioridad"]').value;

            console.log('Validando formulario de edición:', {
                id,
                nombreTrabajo,
                descripcion,
                fechaEstimada,
                tecnico,
                estado,
                prioridad
            });

            if (!id) {
                e.preventDefault();
                alert('Error: ID de orden no encontrado');
                return false;
            }
            if (!nombreTrabajo) {
                e.preventDefault();
                alert('El nombre del trabajo es obligatorio');
                return false;
            }
            if (!descripcion) {
                e.preventDefault();
                alert('La descripción es obligatoria');
                return false;
            }
            if (!fechaEstimada) {
                e.preventDefault();
                alert('La fecha estimada es obligatoria');
                return false;
            }
            if (!tecnico) {
                e.preventDefault();
                alert('Debe seleccionar un técnico');
                return false;
            }
            if (!estado) {
                e.preventDefault();
                alert('Debe seleccionar un estado');
                return false;
            }
            if (!prioridad) {
                e.preventDefault();
                alert('Debe seleccionar una prioridad');
                return false;
            }

            console.log('Formulario válido, enviando...');
            
            // Verificar todos los campos del formulario antes del envío
            const formData = new FormData(this);
            console.log('Datos del formulario completo:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
        });
    </script>
</body>
</html>
