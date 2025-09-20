<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
require_once '../controllers/CatRepuController.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categoría de Repuestos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <a href="cat_repu.php?form=1" class="btn btn-success w-100">Agregar Categoría</a>
        </div>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Características</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $categorias->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= htmlspecialchars($row['caracteristicas'] ?? '') ?></td>
                <td class="text-center">
                    <a href="cat_repu.php?form=1&id=<?= $row['id'] ?>" class="btn btn-warning mx-1">Editar</a>
                    <a href="cat_repu.php?delete=<?= $row['id'] ?>" class="btn btn-danger mx-1" onclick="return confirm('¿Eliminar categoría?')">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <?php if (isset($_GET['form'])): ?>
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title"><?= isset($categoria) ? 'Editar' : 'Agregar' ?> Categoría</h5>
            <form method="post">
                <input type="hidden" name="id" value="<?= $categoria['id'] ?? '' ?>">
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
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al Dashboard</a>
    </div>
</div>
</body>
</html>
