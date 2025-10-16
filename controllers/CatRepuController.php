<?php
require_once __DIR__ . '/../models/CatRepu.php';

$catRepu = new CatRepu();

// Función para redireccionar con mensajes
function redirectWithMessage($success, $message) {
    $tipo = $success ? 'exito' : 'error';
    $encodedMessage = urlencode($message);
    header("Location: cat_repu.php?tipo={$tipo}&mensaje={$encodedMessage}");
    exit();
}

// Obtener todas las categorías (sin filtro inicial)
$categorias = $catRepu->getAll('');

// Manejar eliminación
if (isset($_GET['delete'])) {
    try {
        $id = intval($_GET['delete']);
        
        // Verificar si existe la categoría
        $categoria = $catRepu->getById($id);
        if (!$categoria) {
            redirectWithMessage(false, 'La categoría no existe');
        }
        
        $nombreCategoria = $categoria['nombre'];
        
        // Verificar si hay repuestos asociados
        require_once __DIR__ . '/../models/Repue.php';
        $repueModel = new Repue();
        $repuestos = $repueModel->getAll(['cat_repu_id' => $id]);
        $countRepuestos = 0;
        while ($repuestos->fetch_assoc()) {
            $countRepuestos++;
        }
        
        $success = $catRepu->delete($id);
        if ($success) {
            $mensaje = "Categoría '{$nombreCategoria}' eliminada exitosamente";
            if ($countRepuestos > 0) {
                $mensaje .= ". {$countRepuestos} repuesto(s) quedaron sin categoría";
            }
            redirectWithMessage(true, $mensaje);
        } else {
            redirectWithMessage(false, 'Error al eliminar la categoría');
        }
    } catch (Exception $e) {
        redirectWithMessage(false, 'Error interno: ' . $e->getMessage());
    }
}

// Manejar creación y edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $tipo_repuesto = trim($_POST['tipo_repuesto'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $caracteristicas = trim($_POST['caracteristicas'] ?? '');
        $id = $_POST['id'] ?? null;
        
        // Validar datos requeridos
        if (empty($nombre)) {
            redirectWithMessage(false, 'El nombre de la categoría es obligatorio');
        }
        
        // Verificar si ya existe una categoría con el mismo nombre (excepto la actual si estamos editando)
        $categorias_existentes = $catRepu->getAll('');
        while ($cat_existente = $categorias_existentes->fetch_assoc()) {
            if (strtolower($cat_existente['nombre']) === strtolower($nombre) && 
                ($id === null || $cat_existente['id'] != $id)) {
                redirectWithMessage(false, "Ya existe una categoría con el nombre '{$nombre}'");
            }
        }
        
        $success = $catRepu->save($tipo_repuesto, $nombre, $caracteristicas, $id);
        if ($success) {
            $accion = $id ? 'actualizada' : 'creada';
            redirectWithMessage(true, "Categoría '{$nombre}' {$accion} exitosamente");
        } else {
            redirectWithMessage(false, 'Error al guardar la categoría');
        }
    } catch (Exception $e) {
        redirectWithMessage(false, 'Error interno: ' . $e->getMessage());
    }
}

// Recargar categorías después de cualquier operación
$categorias = $catRepu->getAll('');
