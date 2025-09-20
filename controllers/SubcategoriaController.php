<?php
require_once __DIR__ . '/../models/Subcategoriarepue.php';
require_once __DIR__ . '/../models/Categoriarepue.php';
require_once __DIR__ . '/../config/db.php';

class SubcategoriaController {
    private $subcategoriaModel;
    private $categoriaModel;
    public function __construct() {
        $this->subcategoriaModel = new Subcategoriarepue($GLOBALS['db']);
        $this->categoriaModel = new Categoriarepue($GLOBALS['db']);
    }
    public function crear($nombre, $categoria_id) {
        return $this->subcategoriaModel->crearSubcategoria($nombre, $categoria_id);
    }
    public function modificar($id, $nombre, $categoria_id) {
        return $this->subcategoriaModel->modificarSubcategoria($id, $nombre, $categoria_id);
    }
    public function consultar() {
        return $this->subcategoriaModel->consultarSubcategorias();
    }
    public function eliminar($id) {
        return $this->subcategoriaModel->eliminarSubcategoria($id);
    }
    public function consultarCategorias() {
        return $this->categoriaModel->consultarCategorias();
    }
}
?>
