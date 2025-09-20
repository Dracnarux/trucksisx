<?php
require_once __DIR__ . '/../controllers/RepuestosController.php';
$repuestosController = new RepuestosController();
$subcategorias = $repuestosController->consultarSubcategorias();

if (count($subcategorias) === 0) {
    echo '<h2>Primero debes crear una subcategoría.</h2><a href="subcategoriarepu.php">Ir a Subcategorías</a>';
    exit;
}

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear'])) {
        $repuestosController->crear(
            $_POST['nombre'],
            $_POST['precio'],
            $_POST['categoria_nombre'],
            $_POST['subcategoria_nombre']
        );
    } elseif (isset($_POST['modificar'])) {
        $repuestosController->modificar(
            $_POST['id'],
            $_POST['nombre'],
            $_POST['precio'],
            $_POST['categoria_nombre'],
            $_POST['subcategoria_nombre']
        );
    } elseif (isset($_POST['eliminar'])) {
        $repuestosController->eliminar($_POST['id']);
    }
}

$repuestos = $repuestosController->consultar();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Repuestos</title>
</head>
<body>
    <h1>Gestión de Repuestos</h1>
    <form method="POST">
        <input type="text" name="categoria_nombre" placeholder="Nombre de la categoría" required>
        <input type="text" name="subcategoria_nombre" placeholder="Nombre de la subcategoría" required>
        <input type="text" name="nombre" placeholder="Nombre del repuesto" required>
        <input type="number" step="0.01" name="precio" placeholder="Precio" required>
        <button type="submit" name="crear">Crear</button>
    </form>
    <h2>Listado de Repuestos</h2>
    <table border="1">
        <tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Categoría</th><th>Subcategoría</th><th>Acciones</th></tr>
        <?php foreach ($repuestos as $rep): ?>
        <tr>
            <form method="POST">
                <td><?= $rep['id'] ?></td>
                <td><input type="text" name="nombre" value="<?= htmlspecialchars($rep['nombre']) ?>"></td>
                <td><input type="number" step="0.01" name="precio" value="<?= $rep['precio'] ?>"></td>
                <td><input type="text" name="categoria_nombre" value="<?= htmlspecialchars($rep['categoria_nombre'] ?? '') ?>" required></td>
                <td><input type="text" name="subcategoria_nombre" value="<?= htmlspecialchars($rep['subcategoria_nombre'] ?? '') ?>" required></td>
                <td>
                    <input type="hidden" name="id" value="<?= $rep['id'] ?>">
                    <button type="submit" name="modificar">Modificar</button>
                    <button type="submit" name="eliminar" onclick="return confirm('¿Eliminar este repuesto?')">Eliminar</button>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
