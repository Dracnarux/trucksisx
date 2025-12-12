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
        :root {
            --bg-primary: #0F172A;
            --card-bg: #111827;
            --card-radius: 12px;
            --text-primary: #F1F5F9;
            --text-secondary: #94A3B8;
            --border: #1E293B;
            --accent: #F97316;
            --accent-amber: #F59E0B;
            --danger: #EF4444;
            --success: #10B981;
        }

        body {
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        .main-header {
            background: linear-gradient(135deg, rgba(15,23,42,0.9) 0%, rgba(17,24,39,0.85) 100%);
            border-radius: var(--card-radius);
            box-shadow: 0 18px 50px rgba(2,6,23,0.6);
            color: var(--text-primary);
            margin-bottom: 2rem;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .main-header h1 {
            color: var(--text-primary);
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--card-radius);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            background: var(--accent);
            color: #fff;
            font-weight: bold;
        }

        .form-label {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
            font-weight: 600;
            border: none;
        }

        .btn-primary:hover {
            background: var(--accent-amber);
            color: #fff;
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid var(--text-secondary);
            color: var(--text-secondary);
        }

        .btn-secondary:hover {
            background: var(--text-secondary);
            color: var(--bg-primary);
        }

        .form-section {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--card-radius);
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }
    </style>
</head>

</body>
</html>

<body>
<div class="container py-4">
    <?php
    // Mostrar mensajes de error
    if (isset($_GET['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> Error: ' . htmlspecialchars($_GET['error']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    }
    ?>
    
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
