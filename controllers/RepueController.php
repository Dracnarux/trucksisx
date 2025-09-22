<?php
require_once __DIR__ . '/../models/Repue.php';
class RepueController {
    private $model;
    public function __construct() {
        $this->model = new Repue();
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
