<?php
require_once __DIR__ . '/../models/Proveedor.php';
class ProveedorController {
    private $model;
    public function __construct() {
        $this->model = new Proveedor();
    }
    public function index($filtros = []) {
        return $this->model->getAll($filtros);
    }
    public function show($id) {
        return $this->model->getById($id);
    }
    public function store($data) {
        return $this->model->save($data);
    }
    public function delete($id) {
        return $this->model->delete($id);
    }
}
