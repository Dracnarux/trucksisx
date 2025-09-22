<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
require_once '../controllers/SubCatRepuController.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subcategoría de Repuestos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Subcategoría de Repuestos</h2>
    <form class="row mb-4" method="get">
        <div class="col-md-3">
            <input type="text" name="filtro_nombre" class="form-control" placeholder="Buscar por nombre" value="<?= htmlspecialchars($filtro_nombre) ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="filtro_caracteristicas" class="form-control" placeholder="Buscar por características" value="<?= htmlspecialchars($filtro_caracteristicas) ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="filtro_categoria" class="form-control" placeholder="Buscar por categoría" value="<?= htmlspecialchars($filtro_categoria) ?>">
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
        <div class="col-md-1">
            <a href="subcat_repu.php" class="btn btn-secondary w-100">Limpiar</a>
        </div>
        <div class="col-md-1 text-end">
            <a href="subcat_repu.php?form=1" class="btn btn-success w-100">Agregar</a>
        </div>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tipo de Sub-Repuesto</th>
                <th>Nombre</th>
                <th>Características</th>
                <th>Categoría</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $subcategorias->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['tipo_sub_repuesto'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= htmlspecialchars($row['caracteristicas'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['categoria_nombre'] ?? '') ?></td>
                <td class="text-center">
                    <a href="subcat_repu.php?form=1&id=<?= $row['id'] ?>" class="btn btn-warning mx-1">Editar</a>
                    <a href="subcat_repu.php?delete=<?= $row['id'] ?>" class="btn btn-danger mx-1" onclick="return confirm('¿Eliminar subcategoría?')">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <?php if (isset($_GET['form'])): ?>
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title"><?= isset($subcategoria) ? 'Editar' : 'Agregar' ?> Subcategoría</h5>
            <form method="post">
                <input type="hidden" name="id" value="<?= $subcategoria['id'] ?? '' ?>">
                <div class="mb-3">
                    <label class="form-label">Tipo de Sub-Repuesto</label>
                    <input type="text" name="tipo_sub_repuesto" class="form-control" value="<?= htmlspecialchars($subcategoria['tipo_sub_repuesto'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($subcategoria['nombre'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Características</label>
                    <input type="text" name="caracteristicas" class="form-control" value="<?= htmlspecialchars($subcategoria['caracteristicas'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Categoría</label>
                    <select name="categoria_id" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php while ($cat = $categorias->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($subcategoria['categoria_id']) && $subcategoria['categoria_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="subcat_repu.php" class="btn btn-secondary mx-2">Cancelar</a>
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
