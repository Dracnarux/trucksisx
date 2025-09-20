<?php
require_once __DIR__ . '/../models/Categoriarepue.php';
require_once __DIR__ . '/../config/db.php';

class CategoriaController {
    private $categoriaModel;

    public function __construct() {
        $this->categoriaModel = new Categoriarepue($GLOBALS['db']);
    }

    public function crear($nombre) {
        return $this->categoriaModel->crearCategoria($nombre);
    }

    public function modificar($id, $nombre) {
        return $this->categoriaModel->modificarCategoria($id, $nombre);
    }

    public function consultar() {
        return $this->categoriaModel->consultarCategorias();
    }

    public function eliminar($id) {
        return $this->categoriaModel->eliminarCategoria($id);
    }
}
?>
