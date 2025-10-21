

<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
require_once '../config/db.php';
require_once '../models/SaliRepue.php';
require_once '../models/Repue.php';
require_once '../models/OrdTrabj.php';
require_once '../models/Alert.php';
$db = conectarDB();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$salida = (new SaliRepue($db))->getById($id);
if (!$salida) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>No se encontró la salida de repuesto.</div></div>";
    exit;
}
$repues = (new Repue($db))->getAll();
$ordenes = (new OrdTrabj($db))->getAll();
$alertas = (new Alert($db))->getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Salida de Repuesto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%);
        }
        .main-header {
            background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(30, 58, 138, 0.2);
            color: #FFFFFF;
            margin-bottom: 2rem;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        .main-header h1 { color: #fff; }
        .card { border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .card-header { background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%); color: #1E3A8A; font-weight: 600; }
        .form-label { color: #1E3A8A; font-weight: 500; }
        .btn-primary {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            color: #1E3A8A !important;
            font-weight: 600;
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            color: #1E3A8A !important;
        }
        .btn-outline-primary {
            background: #FFFFFF;
            border: 2px solid #1E3A8A;
            color: #1E3A8A !important;
        }
        .btn-outline-primary:hover {
            background: #1E3A8A;
            color: #FFFFFF !important;
        }
        .btn-secondary {
            background: #FFFFFF;
            border: 2px solid #1E3A8A;
            color: #1E3A8A !important;
        }
        .btn-secondary:hover {
            background: #1E3A8A;
            color: #FFFFFF !important;
        }
        .card { margin-bottom: 2rem; }
        .form-section {
            background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%);
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }
    </style>
</head>

</body>
</html>

<body>
<div class="container py-4">
    <div class="main-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
            <div>
                <h1 class="mb-2"><i class="bi bi-pencil-square"></i> Editar Salida de Repuesto</h1>
                <span class="lead">Modifica los datos de la salida registrada</span>
            </div>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <a href="salida_repuesto.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Volver a Salidas
                </a>
            </div>
        </div>
    </div>
    <div class="card animate-slide-up">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-clipboard-data"></i> Información de la Salida</h5>
        </div>
        <div class="card-body">
            <form action="../controllers/SaliRepueController.php?action=actualizar&id=<?= $salida['id'] ?>" method="POST">
                <div class="form-section">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="fecha_salida" class="form-label">Fecha de Salida</label>
                            <input type="date" class="form-control" name="fecha_salida" value="<?= $salida['fecha_salida'] ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="cantidad" class="form-label">Cantidad</label>
                            <input type="number" class="form-control" name="cantidad" min="1" value="<?= $salida['cantidad'] ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="repue_id" class="form-label">Repuesto</label>
                            <select class="form-select" name="repue_id" required>
                                <?php foreach ($repues as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= $salida['repue_id'] == $r['id'] ? 'selected' : '' ?>><?= $r['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="ord_trabj_id" class="form-label">Orden de Trabajo</label>
                            <select class="form-select" name="ord_trabj_id" required>
                                <?php foreach ($ordenes as $o): ?>
                                    <option value="<?= $o['id'] ?>" <?= $salida['ord_trabj_id'] == $o['id'] ? 'selected' : '' ?>><?= $o['nombre_trabajo'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="alerta_id" class="form-label">Alerta</label>
                            <select class="form-select" name="alerta_id" required>
                                <?php foreach ($alertas as $a): ?>
                                    <option value="<?= $a['id'] ?>" <?= $salida['alerta_id'] == $a['id'] ? 'selected' : '' ?>><?= $a['descripcion'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar Cambios
                    </button>
                    <a href="salida_repuesto.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
