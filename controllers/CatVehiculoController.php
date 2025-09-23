<?php
require_once '../models/CatVehiculo.php';
require_once '../config/db.php';

class CatVehiculoController {
    private $model;
    public function __construct() {
        $db = conectarDB();
        $this->model = new CatVehiculo($db);
    }
    public function index() {
        return $this->model->getAll();
    }
    public function show($id) {
        return $this->model->getById($id);
    }
    public function store($nombre) {
        return $this->model->create($nombre);
    }
    public function update($id, $nombre) {
        return $this->model->update($id, $nombre);
    }
    public function destroy($id) {
        return $this->model->delete($id);
    }
}
