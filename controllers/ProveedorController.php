<?php
require_once __DIR__ . '/../models/Proveedor.php';
class ProveedorController {
    private $model;
    public function __construct() {
        $this->model = new Proveedor();
        
        // AJAX - Manejo de solicitudes AJAX
        if (isset($_GET['ajax']) && $_GET['ajax'] === 'get' && isset($_GET['id'])) {
            header('Content-Type: application/json');
            $proveedor = $this->model->getById($_GET['id']);
            if ($proveedor) {
                echo json_encode($proveedor);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Proveedor no encontrado']);
            }
            exit();
        }
    }
    public function index($filtros = []) {
        return $this->model->getAll($filtros);
    }
    public function show($id) {
        return $this->model->getById($id);
    }
    public function store($data) {
        try {
            // Validaciones básicas
            if (empty(trim($data['nit_num_identi'] ?? ''))) {
                throw new Exception("El NIT/Identificación es obligatorio");
            }
            if (empty(trim($data['nom_proveedor'] ?? ''))) {
                throw new Exception("El nombre del proveedor es obligatorio");
            }
            
            $result = $this->model->save($data);
            
            if (!$result) {
                throw new Exception("Error al guardar el proveedor");
            }
            
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
            
            $mensaje = !empty($data['id']) ? 'actualizado' : 'creado';
            header("Location: proveedor.php?success=$mensaje");
            exit();
            
        } catch (Exception $e) {
            $error = urlencode($e->getMessage());
            header("Location: proveedor.php?error=$error");
            exit();
        }
    }
    
    public function delete($id) {
        try {
            // Antes de eliminar el proveedor, desvincular todos sus repuestos
            require_once __DIR__ . '/../models/Repue.php';
            $repueModel = new Repue();
            $repueModel->desvincularDeProveedor($id);
            
            // Ahora eliminar el proveedor
            $result = $this->model->delete($id);
            
            if ($result) {
                header("Location: proveedor.php?success=eliminado");
            } else {
                header("Location: proveedor.php?error=no_eliminar");
            }
        } catch (Exception $e) {
            header("Location: proveedor.php?error=error_eliminar");
        }
        exit();
    }
}
