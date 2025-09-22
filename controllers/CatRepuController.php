<?php
require_once __DIR__ . '/../models/CatRepu.php';

$catRepu = new CatRepu();

// Filtro
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';
$categorias = $catRepu->getAll($filtro);

// Eliminar
if (isset($_GET['delete'])) {
    $catRepu->delete($_GET['delete']);
    header("Location: cat_repu.php");
    exit();
}

// Guardar (alta/edición)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_repuesto = $_POST['tipo_repuesto'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $caracteristicas = $_POST['caracteristicas'] ?? '';
    $id = $_POST['id'] ?? null;
    $catRepu->save($tipo_repuesto, $nombre, $caracteristicas, $id);
    header("Location: cat_repu.php");
    exit();
}

// Edición
$categoria = null;
if (isset($_GET['id'])) {
    $categoria = $catRepu->getById($_GET['id']);
}
