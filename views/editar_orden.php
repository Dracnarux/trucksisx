

<?php
require_once '../config/db.php';
$conn = conectarDB();
require_once '../models/User.php';
$userModel = new User();
$tecnicos = $userModel->getTecnicos();
require_once '../models/Alert.php';
$alertModel = new Alert();
$alertas = $alertModel->getAll();
require_once '../models/Repue.php';
$repueModel = new Repue();
$repues = $repueModel->getAll();
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: orden_trabajo.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_trabajo = $_POST['nombre_trabajo'];
    $descripcion = $_POST['descripcion'];
    $nombre_repuesto = $_POST['nombre_repuesto'] ?? '';
    $fecha_estimada = $_POST['fecha_estimada'];
    $estado = $_POST['estado'];
    $prioridad = $_POST['prioridad'];
    $cond_id = $_POST['cond_id'];
    $users_id = $_POST['users_id'];
    $alert_id = $_POST['alert_id'] ?? null;
    $stmt = $conn->prepare("UPDATE ord_trabj SET nombre_trabajo=?, descripcion=?, nombre_repuesto=?, fecha_estimada=?, estado=?, prioridad=?, cond_id=?, users_id=?, alert_id=? WHERE id=?");
    $stmt->bind_param('sssssssiii', $nombre_trabajo, $descripcion, $nombre_repuesto, $fecha_estimada, $estado, $prioridad, $cond_id, $users_id, $alert_id, $id);
    $stmt->execute();
    $stmt->close();
    header('Location: orden_trabajo.php');
    exit;
}
// Obtener datos actuales
$stmt = $conn->prepare("SELECT * FROM ord_trabj WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$orden = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Orden de Trabajo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/truck-alerts.css" rel="stylesheet">
</head>
<body>
    <div class="header-container">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-edit"></i> Editar Orden de Trabajo</h1>
                </div>
                <div class="col-md-4 text-end">
                    <a href="orden_trabajo.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Volver a Órdenes
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
    <?php
    // Obtener conductores antes del HTML para que no se muestre en la plataforma
    $conductores = $userModel->getConductores();
    // No imprimir ni mostrar la variable, solo usarla en el selector
    ?>
        <div class="card mt-4">
            <div class="card-header bg-warning text-dark">
                <h2 class="mb-0"><i class="fas fa-pencil-alt"></i> Modificar Orden</h2>
            </div>
            <div class="card-body">
                <form method="post" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del trabajo</label>
                        <input type="text" name="nombre_trabajo" class="form-control" value="<?= htmlspecialchars($orden['nombre_trabajo']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Repuesto (opcional)</label>
                        <select name="nombre_repuesto" class="form-select">
                            <option value="">Sin repuesto</option>
                            <?php while($repue = $repues->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($repue['nombre']) ?>" <?= $orden['nombre_repuesto']==$repue['nombre']?'selected':'' ?>>
                                    <?= htmlspecialchars($repue['nombre']) ?> (ID: <?= $repue['id'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" required><?= htmlspecialchars($orden['descripcion']) ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha Estimada</label>
                        <input type="date" name="fecha_estimada" class="form-control" value="<?= $orden['fecha_estimada'] ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="pendiente" <?= $orden['estado']=='pendiente'?'selected':'' ?>>Pendiente</option>
                            <option value="en_proceso" <?= $orden['estado']=='en_proceso'?'selected':'' ?>>En Proceso</option>
                            <option value="completada" <?= $orden['estado']=='completada'?'selected':'' ?>>Completada</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Prioridad</label>
                        <select name="prioridad" class="form-select">
                            <option value="baja" <?= $orden['prioridad']=='baja'?'selected':'' ?>>Baja</option>
                            <option value="media" <?= $orden['prioridad']=='media'?'selected':'' ?>>Media</option>
                            <option value="alta" <?= $orden['prioridad']=='alta'?'selected':'' ?>>Alta</option>
                            <option value="critica" <?= $orden['prioridad']=='critica'?'selected':'' ?>>Crítica</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Conductor</label>
                        <select name="cond_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach($conductores as $conductor): ?>
                                <option value="<?= $conductor['id'] ?>" <?= $orden['cond_id']==$conductor['id']?'selected':'' ?>>
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
                                <option value="<?= $tecnico['id'] ?>" <?= $orden['users_id']==$tecnico['id']?'selected':'' ?>>
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
                                <option value="<?= $alerta['id'] ?>" <?= $orden['alert_id']==$alerta['id']?'selected':'' ?>>
                                    <?= htmlspecialchars($alerta['descripcion']) ?> (ID: <?= $alerta['id'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
