<?php
require_once __DIR__ . '/../models/SubCatRepu.php';
require_once __DIR__ . '/../models/CatRepu.php';

$subCatRepu = new SubCatRepu();
$catRepu = new CatRepu();

// AJAX - Obtener datos de una subcategoría
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $subcategoria = $subCatRepu->getById($_GET['id']);
    if ($subcategoria) {
        echo json_encode($subcategoria);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Subcategoría no encontrada']);
    }
    exit();
}

// Filtros
$filtro_nombre = $_GET['filtro_nombre'] ?? '';
$filtro_caracteristicas = $_GET['filtro_caracteristicas'] ?? '';
$filtro_categoria = $_GET['filtro_categoria'] ?? '';
$subcategorias = $subCatRepu->getAll($filtro_nombre, $filtro_caracteristicas, $filtro_categoria);
$categorias = $catRepu->getAll();

// Eliminar
if (isset($_GET['delete'])) {
    try {
        $resultado = $subCatRepu->delete($_GET['delete']);
        if ($resultado) {
            header("Location: subcat_repu.php?success=eliminado");
        } else {
            header("Location: subcat_repu.php?error=no_eliminar");
        }
    } catch (Exception $e) {
        header("Location: subcat_repu.php?error=error_eliminar");
    }
    exit();
}

// Guardar (alta/edición)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $tipo_sub_repuesto = trim($_POST['tipo_sub_repuesto'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $caracteristicas = trim($_POST['caracteristicas'] ?? '');
        $categoria_id = $_POST['categoria_id'] ?? '';
        $id = $_POST['id'] ?? null;
        
        // Validaciones
        if (empty($nombre)) {
            throw new Exception("El nombre es obligatorio");
        }
        if (empty($categoria_id)) {
            throw new Exception("La categoría es obligatoria");
        }
        
        $resultado = $subCatRepu->save($tipo_sub_repuesto, $nombre, $caracteristicas, $categoria_id, $id);
        
        if ($resultado) {
            $mensaje = $id ? 'actualizado' : 'creado';
            header("Location: subcat_repu.php?success=$mensaje");
        } else {
            throw new Exception("Error al guardar la subcategoría");
        }
    } catch (Exception $e) {
        $error = urlencode($e->getMessage());
        header("Location: subcat_repu.php?error=$error");
    }
    exit();
}

// Edición
$subcategoria = null;
if (isset($_GET['id'])) {
    $subcategoria = $subCatRepu->getById($_GET['id']);
}

// Mensajes de éxito y error
$mensaje_success = '';
$mensaje_error = '';

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'creado':
            $mensaje_success = 'Subcategoría creada exitosamente';
            break;
        case 'actualizado':
            $mensaje_success = 'Subcategoría actualizada exitosamente';
            break;
        case 'eliminado':
            $mensaje_success = 'Subcategoría eliminada exitosamente';
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'no_eliminar':
            $mensaje_error = 'No se pudo eliminar la subcategoría';
            break;
        case 'error_eliminar':
            $mensaje_error = 'Error al eliminar la subcategoría';
            break;
        default:
            $mensaje_error = urldecode($_GET['error']);
            break;
    }
}
