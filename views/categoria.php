<?php
require_once __DIR__ . '/../controllers/CategoriaController.php';
$categoriaController = new CategoriaController();

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear'])) {
        $categoriaController->crear($_POST['nombre']);
    } elseif (isset($_POST['modificar'])) {
        $categoriaController->modificar($_POST['id'], $_POST['nombre']);
    } elseif (isset($_POST['eliminar'])) {
        $categoriaController->eliminar($_POST['id']);
    }
}

$categorias = $categoriaController->consultar();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Categorías</title>
</head>
<body>
    <h1>Gestión de Categorías</h1>
    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre de la categoría" required>
        <button type="submit" name="crear">Crear</button>
    </form>
    <h2>Listado de Categorías</h2>
    <table border="1">
        <tr><th>ID</th><th>Nombre</th><th>Acciones</th></tr>
        <?php foreach ($categorias as $cat): ?>
        <tr>
            <form method="POST">
                <td><?= $cat['id'] ?></td>
                <td><input type="text" name="nombre" value="<?= htmlspecialchars($cat['nombre']) ?>"></td>
                <td>
                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                    <button type="submit" name="modificar">Modificar</button>
                    <button type="submit" name="eliminar" onclick="return confirm('¿Eliminar esta categoría?')">Eliminar</button>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
