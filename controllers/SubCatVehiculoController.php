<?php
require_once '../models/SubCatVehiculo.php';
require_once '../config/db.php';

class SubCatVehiculoController {
    private $model;
    public function __construct() {
        $db = conectarDB();
        $this->model = new SubCatVehiculo($db);
    }
    public function index($cat_vehic_id = null) {
        return $this->model->getAll($cat_vehic_id);
    }
    public function show($id) {
        return $this->model->getById($id);
    }
    public function store($nombre, $cat_vehic_id) {
        return $this->model->create($nombre, $cat_vehic_id);
    }
    public function update($id, $nombre, $cat_vehic_id) {
        return $this->model->update($id, $nombre, $cat_vehic_id);
    }
    public function destroy($id) {
        return $this->model->delete($id);
    }
}
