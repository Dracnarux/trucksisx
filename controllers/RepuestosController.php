<?php
require_once __DIR__ . '/../models/Repuestos.php';
require_once __DIR__ . '/../models/Subcategoriarepue.php';
require_once __DIR__ . '/../models/Categoriarepue.php';
require_once __DIR__ . '/../config/db.php';

class RepuestosController {
    private $repuestoModel;
    private $subcategoriaModel;
    private $categoriaModel;
    public function __construct() {
        $this->repuestoModel = new Repuestos($GLOBALS['db']);
        $this->subcategoriaModel = new Subcategoriarepue($GLOBALS['db']);
        $this->categoriaModel = new Categoriarepue($GLOBALS['db']);
    }
    public function crear($nombre, $precio, $categoria_nombre, $subcategoria_nombre) {
        // 1. Crear o buscar categoría
        $cat = $this->buscarOCrearCategoria($categoria_nombre);
        $cat_id = $cat['id'];
        // 2. Crear o buscar subcategoría
        $subcat = $this->buscarOCrearSubcategoria($subcategoria_nombre, $cat_id);
        $subcat_id = $subcat['id'];
        // 3. Crear repuesto
        return $this->repuestoModel->crearRepuesto($nombre, $precio, $subcat_id);
    }
    public function modificar($id, $nombre, $precio, $categoria_nombre, $subcategoria_nombre) {
        // 1. Crear o buscar categoría
        $cat = $this->buscarOCrearCategoria($categoria_nombre);
        $cat_id = $cat['id'];
        // 2. Crear o buscar subcategoría
        $subcat = $this->buscarOCrearSubcategoria($subcategoria_nombre, $cat_id);
        $subcat_id = $subcat['id'];
        // 3. Modificar repuesto
        return $this->repuestoModel->modificarRepuesto($id, $nombre, $precio, $subcat_id);
    }
    private function buscarOCrearCategoria($nombre) {
        $cats = $this->categoriaModel->consultarCategorias();
        foreach ($cats as $cat) {
            if (strtolower($cat['nombre']) === strtolower($nombre)) {
                return $cat;
            }
        }
        $this->categoriaModel->crearCategoria($nombre);
        $cats = $this->categoriaModel->consultarCategorias();
        foreach ($cats as $cat) {
            if (strtolower($cat['nombre']) === strtolower($nombre)) {
                return $cat;
            }
        }
        return null;
    }
    private function buscarOCrearSubcategoria($nombre, $cat_id) {
        $subs = $this->subcategoriaModel->consultarSubcategorias();
        foreach ($subs as $sub) {
            if (strtolower($sub['nombre']) === strtolower($nombre) && $sub['cat_repu_id'] == $cat_id) {
                return $sub;
            }
        }
        $this->subcategoriaModel->crearSubcategoria($nombre, $cat_id);
        $subs = $this->subcategoriaModel->consultarSubcategorias();
        foreach ($subs as $sub) {
            if (strtolower($sub['nombre']) === strtolower($nombre) && $sub['cat_repu_id'] == $cat_id) {
                return $sub;
            }
        }
        return null;
    }
    public function consultar() {
        return $this->repuestoModel->consultarRepuestos();
    }
    public function eliminar($id) {
        return $this->repuestoModel->eliminarRepuesto($id);
    }
    public function consultarSubcategorias() {
        return $this->subcategoriaModel->consultarSubcategorias();
    }
}
?>
