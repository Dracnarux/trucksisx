<?php
require_once __DIR__ . '/../controllers/SubcategoriaController.php';
$subcategoriaController = new SubcategoriaController();

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear'])) {
        $subcategoriaController->crear($_POST['nombre'], $_POST['categoria_id']);
    } elseif (isset($_POST['modificar'])) {
        $subcategoriaController->modificar($_POST['id'], $_POST['nombre'], $_POST['categoria_id']);
    } elseif (isset($_POST['eliminar'])) {
        $subcategoriaController->eliminar($_POST['id']);
    }
}

$subcategorias = $subcategoriaController->consultar();
$categorias = $subcategoriaController->consultarCategorias();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Subcategorías</title>
</head>
<body>
    <h1>Gestión de Subcategorías</h1>
    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre de la subcategoría" required>
        <select name="categoria_id" required>
            <option value="">Seleccione categoría</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="crear">Crear</button>
    </form>
    <h2>Listado de Subcategorías</h2>
    <table border="1">
        <tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Acciones</th></tr>
        <?php foreach ($subcategorias as $sub): ?>
        <tr>
            <form method="POST">
                <td><?= $sub['id'] ?></td>
                <td><input type="text" name="nombre" value="<?= htmlspecialchars($sub['nombre']) ?>"></td>
                <td>
                    <select name="categoria_id">
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $sub['categoria_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
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
</body>
</html>
