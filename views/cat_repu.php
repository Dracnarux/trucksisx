<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
require_once '../controllers/CatRepuController.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categoría de Repuestos</title>
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
        h2 {
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
        .table-bordered, .table th, .table td {
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
    <h2 class="mb-4">Categoría de Repuestos</h2>
    <form class="row mb-4" method="get">
        <div class="col-md-3">
            <input type="text" name="filtro_nombre" class="form-control" placeholder="Buscar por nombre" value="<?= isset($_GET['filtro_nombre']) ? htmlspecialchars($_GET['filtro_nombre']) : '' ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="filtro_caracteristicas" class="form-control" placeholder="Buscar por características" value="<?= isset($_GET['filtro_caracteristicas']) ? htmlspecialchars($_GET['filtro_caracteristicas']) : '' ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
        <div class="col-md-2">
            <a href="cat_repu.php" class="btn btn-secondary w-100">Limpiar</a>
        </div>
        <div class="col-md-2 text-end">
            <?php if (!$rol_conductor): ?>
            <a href="cat_repu.php?form=1" class="btn btn-success w-100">Agregar Categoría</a>
            <?php endif; ?>
        </div>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tipo de Repuesto</th>
                <th>Nombre</th>
                <th>Características</th>
                <!-- <th>Productos</th> -->
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        require_once '../models/Repue.php';
        $repueModel = new Repue();
        while ($row = $categorias->fetch_assoc()): 
            $productos = [];
            $repuestos = $repueModel->getAll(['cat_repu_id' => $row['id']]);
            while ($rep = $repuestos->fetch_assoc()) {
                $productos[] = htmlspecialchars($rep['nombre']);
            }
        ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['tipo_repuesto'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= htmlspecialchars($row['caracteristicas'] ?? '') ?></td>
                <!-- <td><?= $productos ? implode(', ', $productos) : '' ?></td> -->
                <td class="text-center">
                    <?php if (!$rol_conductor): ?>
                    <a href="cat_repu.php?form=1&id=<?= $row['id'] ?>" class="btn btn-warning mx-1">Editar</a>
                    <a href="cat_repu.php?delete=<?= $row['id'] ?>" class="btn btn-danger mx-1" onclick="return confirm('¿Eliminar categoría?')">Eliminar</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <?php if (isset($_GET['form']) && !$rol_conductor): ?>
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title"><?= isset($categoria) ? 'Editar' : 'Agregar' ?> Categoría</h5>
            <form method="post">
                <input type="hidden" name="id" value="<?= $categoria['id'] ?? '' ?>">
                <div class="mb-3">
                    <label class="form-label">Tipo de Repuesto</label>
                    <input type="text" name="tipo_repuesto" class="form-control" value="<?= htmlspecialchars($categoria['tipo_repuesto'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($categoria['nombre'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Características</label>
                    <input type="text" name="caracteristicas" class="form-control" value="<?= htmlspecialchars($categoria['caracteristicas'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="cat_repu.php" class="btn btn-secondary mx-2">Cancelar</a>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="mt-5 text-end">
        <a href="gestiones.php" class="btn btn-outline-secondary">Volver al Panel de Gestiones</a>
    </div>
</div>
</body>
</html>
