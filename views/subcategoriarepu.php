<?php
require_once __DIR__ . '/../controllers/SubcategoriaController.php';
$subcategoriaController = new SubcategoriaController();
$categorias = $subcategoriaController->consultarCategorias();

if (count($categorias) === 0) {
    echo '<h2>Primero debes crear una categoría.</h2><a href="categoriarepu.php">Ir a Categorías</a>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear'])) {
        $subcategoriaController->crear($_POST['nombre'], $_POST['cat_repu_id']);
    } elseif (isset($_POST['modificar'])) {
        $subcategoriaController->modificar($_POST['id'], $_POST['nombre'], $_POST['cat_repu_id']);
    } elseif (isset($_POST['eliminar'])) {
        $subcategoriaController->eliminar($_POST['id']);
    }
}
$subcategorias = $subcategoriaController->consultar();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestionar Subcategoría</title>
</head>
<body>
    <h1>Crear Subcategoría</h1>
    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre de la subcategoría" required>
        <select name="cat_repu_id" required>
            <option value="">Seleccione categoría</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="crear">Crear</button>
    </form>
    <h2>Subcategorías existentes</h2>
    <table border="1">
        <tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Acciones</th></tr>
        <?php foreach ($subcategorias as $sub): ?>
        <tr>
            <form method="POST">
                <td><?= $sub['id'] ?></td>
                <td><input type="text" name="nombre" value="<?= htmlspecialchars($sub['nombre']) ?>"></td>
                <td>
                    <select name="cat_repu_id">
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $sub['cat_repu_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <input type="hidden" name="id" value="<?= $sub['id'] ?>">
                    <button type="submit" name="modificar">Modificar</button>
                    <button type="submit" name="eliminar" onclick="return confirm('¿Eliminar esta subcategoría?')">Eliminar</button>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </table>
    <a href="repuestos.php">Ir a Repuestos</a>
</body>
</html>
