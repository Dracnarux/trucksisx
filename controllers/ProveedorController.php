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
        $result = $this->model->save($data);
        // Vincular repuestos seleccionados
        if (isset($data['repuestos_vinculados']) && is_array($data['repuestos_vinculados'])) {
            require_once __DIR__ . '/../models/Repue.php';
            $repueModel = new Repue();
            // Obtener el ID del proveedor correctamente
            $proveedorId = !empty($data['id']) ? $data['id'] : $this->model->getLastInsertId();
            foreach ($data['repuestos_vinculados'] as $repId) {
                $repueModel->save(['id' => $repId, 'proveedor_id' => $proveedorId]);
            }
        }
        return $result;
    }
    public function delete($id) {
        return $this->model->delete($id);
    }
}
