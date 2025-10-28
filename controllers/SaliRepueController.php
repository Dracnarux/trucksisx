<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/SaliRepue.php';

// Definir la conexión global si no existe
if (!isset($GLOBALS['db']) || !$GLOBALS['db']) {
    $GLOBALS['db'] = conectarDB();
}

class SaliRepueController {
    private $model;
    public function __construct() {
        $this->model = new SaliRepue($GLOBALS['db']);
    }
    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'fecha_salida' => $_POST['fecha_salida'],
                'cantidad' => $_POST['cantidad'],
                'repue_id' => $_POST['repue_id'],
                'ord_trabj_id' => $_POST['ord_trabj_id'],
                'alerta_id' => $_POST['alerta_id']
            ];
            $id = $this->model->registrarSalida($data);
            if ($id) {
                // Generar código de reporte automáticamente
                $codigo_reporte = 'REP-' . date('Ymd-His');
                $tipo_reporte = 'Salida de Repuesto';
                $fecha_creacion = date('Y-m-d');
                $this->model->crearReporte([
                    'nombre_reporte' => $codigo_reporte,
                    'tipo_reporte' => $tipo_reporte,
                    'fecha_creacion' => $fecha_creacion,
                    'activo' => 1,
                    'sali_repue_id' => $id
                ]);
                // Redirigir o responder con éxito
                header('Location: ../views/salida_vehiculo.php?sali_repue_id=' . $id);
                exit();
            } else {
                echo 'Error al registrar la salida de repuesto.';
            }
        }
    }

    public function eliminar() {
        // Obtener ID desde GET o POST
        $id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
        
        // Detectar si es petición AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        $isPostAction = isset($_POST['action']) && $_POST['action'] === 'eliminar';
        
        // Log del inicio
        error_log("SaliRepueController::eliminar - Iniciando eliminación de ID: $id, AJAX: " . ($isAjax ? 'SI' : 'NO') . ", POST: " . ($isPostAction ? 'SI' : 'NO'));
        
        if ($id <= 0) {
            error_log("SaliRepueController::eliminar - ERROR: ID inválido: $id");
            if ($isAjax || $isPostAction) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'ID de salida inválido']);
                exit();
            } else {
                header('Location: ../views/salida_repuesto.php?error=' . urlencode('ID de salida inválido'));
                exit();
            }
        }
        
        try {
            error_log("SaliRepueController::eliminar - Llamando al modelo para eliminar ID: $id");
            $result = $this->model->eliminar($id);
            
            if ($result === true) {
                error_log("SaliRepueController::eliminar - ÉXITO: Salida ID $id eliminada correctamente");
                if ($isAjax || $isPostAction) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Salida eliminada correctamente', 'id' => $id]);
                    exit();
                } else {
                    header('Location: ../views/salida_repuesto.php?mensaje=eliminado&id=' . $id);
                    exit();
                }
            } else {
                error_log("SaliRepueController::eliminar - ERROR: El modelo retornó: " . var_export($result, true));
                $errorMsg = 'No se pudo eliminar la salida - resultado inesperado';
                if ($isAjax || $isPostAction) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => $errorMsg]);
                    exit();
                } else {
                    header('Location: ../views/salida_repuesto.php?error=' . urlencode($errorMsg));
                    exit();
                }
            }
        } catch (Exception $e) {
            error_log("SaliRepueController::eliminar - EXCEPCIÓN: " . $e->getMessage());
            error_log("SaliRepueController::eliminar - Stack trace: " . $e->getTraceAsString());
            $errorMsg = 'Error al eliminar: ' . $e->getMessage();
            if ($isAjax || $isPostAction) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $errorMsg]);
                exit();
            } else {
                header('Location: ../views/salida_repuesto.php?error=' . urlencode($errorMsg));
                exit();
            }
        }
        
        // Si llegamos aquí sin ID, error
        if ($id == 0) {
            error_log("SaliRepueController::eliminar - ERROR: ID no proporcionado");
            if ($isAjax || $isPostAction) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'ID de salida no especificado']);
                exit();
            } else {
                header('Location: ../views/salida_repuesto.php?error=' . urlencode('ID no proporcionado'));
                exit();
            }
        }
    }

    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            
            error_log("SaliRepueController::actualizar - Iniciando actualización de ID: $id");
            
            if ($id <= 0) {
                error_log("SaliRepueController::actualizar - ERROR: ID inválido: $id");
                header('Location: ../views/salida_repuesto.php?error=' . urlencode('ID de salida inválido'));
                exit();
            }
            
            try {
                // Validar datos requeridos
                $requiredFields = ['fecha_salida', 'cantidad', 'repue_id', 'ord_trabj_id'];
                foreach ($requiredFields as $field) {
                    if (!isset($_POST[$field]) || empty($_POST[$field])) {
                        throw new Exception("Campo requerido faltante: $field");
                    }
                }
                
                $data = [
                    'fecha_salida' => $_POST['fecha_salida'],
                    'cantidad' => intval($_POST['cantidad']),
                    'repue_id' => intval($_POST['repue_id']),
                    'ord_trabj_id' => intval($_POST['ord_trabj_id']),
                    'alerta_id' => !empty($_POST['alerta_id']) ? intval($_POST['alerta_id']) : null
                ];
                
                // Validaciones adicionales
                if ($data['cantidad'] <= 0) {
                    throw new Exception("La cantidad debe ser mayor que cero");
                }
                
                error_log("SaliRepueController::actualizar - Datos a actualizar: " . json_encode($data));
                
                $result = $this->model->actualizar($id, $data);
                
                if ($result === true) {
                    error_log("SaliRepueController::actualizar - ÉXITO: Salida ID $id actualizada correctamente");
                    header('Location: ../views/salida_repuesto.php?mensaje=actualizado&id=' . $id);
                    exit();
                } else {
                    error_log("SaliRepueController::actualizar - ERROR: El modelo retornó: " . var_export($result, true));
                    header('Location: ../views/editar_salida_repuesto.php?id=' . $id . '&error=' . urlencode('No se pudo actualizar la salida'));
                    exit();
                }
                
            } catch (Exception $e) {
                error_log("SaliRepueController::actualizar - EXCEPCIÓN: " . $e->getMessage());
                header('Location: ../views/editar_salida_repuesto.php?id=' . $id . '&error=' . urlencode($e->getMessage()));
                exit();
            }
        } else {
            error_log("SaliRepueController::actualizar - ERROR: Método incorrecto o ID faltante");
            header('Location: ../views/salida_repuesto.php?error=' . urlencode('Método incorrecto o ID faltante'));
            exit();
        }
    }

    public function listar() {
        try {
            $salidas = $this->model->getAllWithDetails();
            return $salidas;
        } catch (Exception $e) {
            error_log("SaliRepueController::listar - ERROR: " . $e->getMessage());
            return [];
        }
    }
}

// Enrutamiento simple
if (isset($_GET['action'])) {
    $controller = new SaliRepueController();
    
    switch ($_GET['action']) {
        case 'registrar':
            $controller->registrar();
            break;
        case 'eliminar':
            $controller->eliminar();
            break;
        case 'actualizar':
            $controller->actualizar();
            break;
        case 'listar':
            echo json_encode($controller->listar());
            break;
        default:
            error_log("SaliRepueController - Acción no válida: " . $_GET['action']);
            header('Location: ../views/salida_repuesto.php?error=' . urlencode('Acción no válida: ' . $_GET['action']));
            exit();
    }
} else {
    // Si no hay acción, redirigir a la vista principal
    header('Location: ../views/salida_repuesto.php');
    exit();
}
