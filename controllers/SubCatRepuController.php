<?php
require_once __DIR__ . '/../models/SubCatRepu.php';
require_once __DIR__ . '/../models/CatRepu.php';

$subCatRepu = new SubCatRepu();
$catRepu = new CatRepu();

// Filtros
$filtro_nombre = $_GET['filtro_nombre'] ?? '';
$filtro_caracteristicas = $_GET['filtro_caracteristicas'] ?? '';
$filtro_categoria = $_GET['filtro_categoria'] ?? '';
$subcategorias = $subCatRepu->getAll($filtro_nombre, $filtro_caracteristicas, $filtro_categoria);
$categorias = $catRepu->getAll();

// Eliminar
if (isset($_GET['delete'])) {
    $subCatRepu->delete($_GET['delete']);
    header("Location: subcat_repu.php");
    exit();
}

// Guardar (alta/edición)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $caracteristicas = $_POST['caracteristicas'] ?? '';
    $categoria_id = $_POST['categoria_id'] ?? '';
    $id = $_POST['id'] ?? null;
    $subCatRepu->save($nombre, $caracteristicas, $categoria_id, $id);
    header("Location: subcat_repu.php");
    exit();
}

// Edición
$subcategoria = null;
if (isset($_GET['id'])) {
    $subcategoria = $subCatRepu->getById($_GET['id']);
}
