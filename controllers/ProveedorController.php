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
        
        // Gestionar vinculación de repuestos
        if (isset($data['repuestos_vinculados'])) {
            require_once __DIR__ . '/../models/Repue.php';
            $repueModel = new Repue();
            
            // Obtener el ID del proveedor correctamente
            $proveedorId = !empty($data['id']) ? $data['id'] : $this->model->getLastInsertId();
            
            // Si estamos editando un proveedor existente, primero desvincular todos los repuestos actuales
            if (!empty($data['id'])) {
                $repueModel->desvincularDeProveedor($proveedorId);
            }
            
            // Vincular los repuestos seleccionados
            if (is_array($data['repuestos_vinculados']) && count($data['repuestos_vinculados']) > 0) {
                foreach ($data['repuestos_vinculados'] as $repId) {
                    if (!empty($repId)) {
                        // Usar el nuevo método que solo actualiza el proveedor
                        $repueModel->updateProveedor($repId, $proveedorId);
                    }
                }
            }
        }
        
        return $result;
    }
    public function delete($id) {
        // Antes de eliminar el proveedor, desvincular todos sus repuestos
        require_once __DIR__ . '/../models/Repue.php';
        $repueModel = new Repue();
        $repueModel->desvincularDeProveedor($id);
        
        // Ahora eliminar el proveedor
        return $this->model->delete($id);
    }
}
