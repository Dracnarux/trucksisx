<?php
require_once __DIR__ . '/../models/Alert.php';
require_once __DIR__ . '/../models/User.php';

class AlertController {
    // Obtener lista de conductores para el formulario
    public function getConductores() {
        require_once __DIR__ . '/../config/db.php';
        $database = new Database();
        $db = $database->getConnection();
        $query = "SELECT id, cargo FROM cond ORDER BY cargo ASC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $conductores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'conductores' => $conductores]);
    }
    // Obtener lista de vehículos para el formulario
    public function getVehicles() {
        require_once __DIR__ . '/../config/db.php';
        $database = new Database();
        $db = $database->getConnection();
        $query = "SELECT id, placa, marca_vehiculo FROM regis_vehic ORDER BY placa";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'vehicles' => $vehicles]);
    }
    private $alert;
    private $user;

    public function __construct() {
        $this->alert = new Alert();
        $this->user = new User();
    }

    // Crear nueva alerta con orden de trabajo automática
    public function create() {
        if ($_POST) {
            // Validar datos requeridos
            if (!isset($_POST['descripcion']) || !isset($_POST['cond_id']) || 
                !isset($_POST['posicion_llanta']) || !isset($_POST['regis_vehic_id'])) {
                echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
                return;
            }

            // Validar existencia de conductor y vehículo
            require_once __DIR__ . '/../config/db.php';
            $database = new Database();
            $db = $database->getConnection();
            $cond_id = $_POST['cond_id'];
            $vehic_id = $_POST['regis_vehic_id'];
            $stmtCond = $db->prepare("SELECT id FROM cond WHERE id = :id");
            $stmtCond->bindParam(':id', $cond_id);
            $stmtCond->execute();
            $condExists = $stmtCond->fetch(PDO::FETCH_ASSOC);
            $stmtVehic = $db->prepare("SELECT id FROM regis_vehic WHERE id = :id");
            $stmtVehic->bindParam(':id', $vehic_id);
            $stmtVehic->execute();
            $vehicExists = $stmtVehic->fetch(PDO::FETCH_ASSOC);
            if (!$condExists) {
                echo json_encode(['success' => false, 'message' => 'El conductor seleccionado no existe.']);
                return;
            }
            if (!$vehicExists) {
                echo json_encode(['success' => false, 'message' => 'El vehículo seleccionado no existe.']);
                return;
            }

            // Datos de la alerta
            $alertData = [
                'descripcion' => $_POST['descripcion'],
                'prioridad' => $_POST['prioridad'] ?? 'media',
                'estado' => 'activa',
                'tipo_alerta' => 'llanta',
                'posicion_llanta' => $_POST['posicion_llanta'],
                'codigo_conductor' => '', // Ya no se usa, pero se mantiene por compatibilidad
                'observaciones' => $_POST['observaciones'] ?? '',
                'imagen_evidencia' => $this->handleImageUpload(),
                'cond_id' => $_POST['cond_id'],
                'regis_vehic_id' => $_POST['regis_vehic_id']
            ];

            // Datos de la orden de trabajo
            $workOrderData = [
                'nombre_trabajo' => 'Revisión de llanta - ' . $this->formatTirePosition($_POST['posicion_llanta']),
                'descripcion' => 'Orden generada automáticamente por alerta de llanta: ' . $_POST['descripcion'],
                'fecha_estimada' => date('Y-m-d', strtotime('+3 days')),
                'estado' => 'pendiente',
                'prioridad' => $_POST['prioridad'] ?? 'media',
                'cond_id' => $cond_id,
                'users_id' => $_SESSION['user_id'] ?? 1 // Usuario técnico asignado
            ];

            $result = $this->alert->createWithWorkOrder($alertData, $workOrderData);
            
            if ($result) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Alerta creada exitosamente y orden de trabajo generada',
                    'alert_id' => $result['alert_id'],
                    'work_order_id' => $result['work_order_id']
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear la alerta. Verifica que el conductor esté vinculado correctamente al vehículo y que ambos existan.']);
            }
        }
    }

    // Obtener todas las alertas
    public function getAll() {
        $alerts = $this->alert->getAll();
        echo json_encode(['success' => true, 'data' => $alerts]);
    }

    // Obtener alertas por vehículo
    public function getByVehicle() {
        if (isset($_GET['vehicle_id'])) {
            $alerts = $this->alert->getByVehicle($_GET['vehicle_id']);
            echo json_encode(['success' => true, 'data' => $alerts]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID de vehículo requerido']);
        }
    }

    // Obtener alertas de llantas con estadísticas
    public function getTireAlerts() {
        $vehicleId = $_GET['vehicle_id'] ?? null;
        $alerts = $this->alert->getTireAlerts($vehicleId);
        $stats = $this->alert->getTirePositionStats($vehicleId);
        
        echo json_encode([
            'success' => true, 
            'alerts' => $alerts,
            'statistics' => $stats,
            'tire_positions' => $this->alert->getTirePositions()
        ]);
    }

    // Obtener alerta por ID
    public function getById() {
        if (isset($_GET['id'])) {
            $alert = $this->alert->getById($_GET['id']);
            if ($alert) {
                echo json_encode(['success' => true, 'data' => $alert]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Alerta no encontrada']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
        }
    }

    // Actualizar estado de alerta
    public function updateStatus() {
        if ($_POST && isset($_POST['id']) && isset($_POST['estado'])) {
            $alertId = $_POST['id'];
            $nuevoEstado = $_POST['estado'];
            
            require_once __DIR__ . '/../config/db.php';
            $database = new Database();
            $db = $database->getConnection();
            
            try {
                // Iniciar transacción
                $db->beginTransaction();
                
                // Actualizar estado de la alerta
                $result = $this->alert->updateStatus($alertId, $nuevoEstado);
                
                if ($result) {
                    // Actualizar órdenes de trabajo relacionadas
                    $estadoOrden = '';
                    switch ($nuevoEstado) {
                        case 'resuelta':
                            $estadoOrden = 'completada';
                            break;
                        case 'en_proceso':
                            $estadoOrden = 'en_proceso';
                            break;
                        case 'activa':
                            $estadoOrden = 'pendiente';
                            break;
                        case 'cancelada':
                            $estadoOrden = 'cancelada';
                            break;
                        default:
                            $estadoOrden = 'pendiente';
                    }
                    
                    // Actualizar órdenes de trabajo que tienen esta alerta asociada
                    $updateOrdenQuery = "UPDATE ord_trabj SET estado = :estado WHERE alert_id = :alert_id";
                    $updateStmt = $db->prepare($updateOrdenQuery);
                    $updateStmt->bindParam(':estado', $estadoOrden, PDO::PARAM_STR);
                    $updateStmt->bindParam(':alert_id', $alertId, PDO::PARAM_INT);
                    $updateStmt->execute();
                    
                    $ordenesCambiadas = $updateStmt->rowCount();
                    
                    $db->commit();
                    
                    $mensaje = 'Estado actualizado correctamente';
                    if ($ordenesCambiadas > 0) {
                        $mensaje .= ". Se actualizaron $ordenesCambiadas orden(es) de trabajo relacionada(s)";
                    }
                    
                    echo json_encode(['success' => true, 'message' => $mensaje]);
                } else {
                    $db->rollback();
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado']);
                }
            } catch (Exception $e) {
                $db->rollback();
                echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Datos insuficientes']);
        }
    }

    // Actualizar alerta completa
    public function update() {
        if ($_POST && isset($_POST['id'])) {
            $data = [
                'descripcion' => $_POST['descripcion'],
                'prioridad' => $_POST['prioridad'],
                'estado' => $_POST['estado'],
                'observaciones' => $_POST['observaciones'] ?? ''
            ];

            $result = $this->alert->update($_POST['id'], $data);
                // Definir cond_id desde POST
                $cond_id = $_POST['cond_id'];
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Alerta actualizada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar la alerta']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Datos insuficientes']);
        }
    }

    // Eliminar alerta
    public function delete() {
        // Asegurar que devolvemos JSON
        header('Content-Type: application/json');
        
        if ($_POST && isset($_POST['id'])) {
            $alertId = intval($_POST['id']);
            
            // Validar que el ID sea válido
            if ($alertId <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de alerta inválido']);
                return;
            }
            
            try {
                // Verificar que la alerta existe antes de eliminar
                $alertaExistente = $this->alert->getById($alertId);
                if (!$alertaExistente) {
                    echo json_encode(['success' => false, 'message' => 'La alerta no existe']);
                    return;
                }
                
                // Log para debugging
                error_log("Intentando eliminar alerta ID: {$alertId}");
                
                // Usar directamente el método delete del modelo que ya maneja las FK constraints
                $result = $this->alert->delete($alertId);
                
                if ($result === true) {
                    error_log("Alerta ID {$alertId} eliminada exitosamente");
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Alerta eliminada correctamente. Las órdenes de trabajo relacionadas han sido desvinculadas automáticamente.'
                    ]);
                } else {
                    error_log("Falló la eliminación de alerta ID {$alertId} - resultado: " . var_export($result, true));
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar la alerta: operación no completada']);
                }
                
            } catch (Exception $e) {
                // Log detallado del error
                error_log("Exception eliminando alerta ID {$alertId}: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                
                // Mensaje de error más específico
                $errorMessage = $e->getMessage();
                
                if (strpos($errorMessage, 'Cannot delete or update a parent row') !== false || 
                    strpos($errorMessage, 'foreign key constraint fails') !== false) {
                    $errorMessage = 'No se puede eliminar la alerta porque tiene dependencias activas. Verifique las órdenes de trabajo asociadas.';
                } else if (strpos($errorMessage, 'desvincular') !== false) {
                    $errorMessage = 'Error al desvincular las órdenes de trabajo relacionadas: ' . $errorMessage;
                } else if (strpos($errorMessage, 'doesn\'t exist') !== false) {
                    $errorMessage = 'La alerta que intenta eliminar no existe.';
                }
                
                echo json_encode(['success' => false, 'message' => $errorMessage]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID de alerta no proporcionado. Método: ' . $_SERVER['REQUEST_METHOD']]);
        }
    }

    // Validar código de conductor
    public function validateConductor() {
        if (isset($_POST['codigo_conductor'])) {
            $conductor = $this->user->getByDocumento($_POST['codigo_conductor']);
            
            if ($conductor && $conductor['rol'] === 'conductor') {
                echo json_encode([
                    'success' => true, 
                    'conductor' => [
                        'id' => $conductor['id'],
                        'nombre' => $conductor['nombre'],
                        'apellido' => $conductor['apellido'],
                        'documento' => $conductor['num_documento'],
                        'regis_vehic_id' => $conductor['regis_vehic_id'] ?? null
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Código de conductor no válido']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Código de conductor requerido']);
        }
    }

    // Obtener dashboard de alertas
    public function getDashboard() {
        $vehicleId = $_GET['vehicle_id'] ?? null;
        
        // Obtener alertas filtradas por vehículo si se especifica
        $allAlerts = $vehicleId ? $this->alert->getByVehicle($vehicleId) : $this->alert->getAll();
        $tireAlerts = $this->alert->getTireAlerts($vehicleId);
        $stats = $this->alert->getTirePositionStats($vehicleId);
        
        // Estadísticas generales
        $totalAlerts = count($allAlerts);
        $activeAlerts = count(array_filter($allAlerts, function($alert) {
            return $alert['estado'] === 'activa';
        }));
        $criticalAlerts = count(array_filter($allAlerts, function($alert) {
            return in_array($alert['prioridad'], ['alta', 'critica']);
        }));
        
        echo json_encode([
            'success' => true,
            'dashboard' => [
                'total_alerts' => $totalAlerts,
                'active_alerts' => $activeAlerts,
                'critical_alerts' => $criticalAlerts,
                'tire_alerts' => count($tireAlerts),
                'tire_statistics' => $stats,
                'recent_alerts' => array_slice($allAlerts, 0, 10)
            ]
        ]);
    }

    // Métodos auxiliares privados
    private function handleImageUpload() {
        if (isset($_FILES['imagen_evidencia']) && $_FILES['imagen_evidencia']['error'] === 0) {
            $uploadDir = '../uploads/alertas/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . $_FILES['imagen_evidencia']['name'];
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['imagen_evidencia']['tmp_name'], $uploadPath)) {
                return $fileName;
            }
        }
        return null;
    }

    private function formatTirePosition($position) {
        $positions = [
            'direccion_izquierda' => 'Dirección Izquierda',
            'direccion_derecha' => 'Dirección Derecha',
            'traccion1_izquierda' => 'Tracción 1 - Izquierda',
            'traccion1_derecha' => 'Tracción 1 - Derecha',
            'traccion1_izquierda2' => 'Tracción 1 - Izquierda 2',
            'traccion1_derecha2' => 'Tracción 1 - Derecha 2',
            'traccion2_izquierda' => 'Tracción 2 - Izquierda',
            'traccion2_derecha' => 'Tracción 2 - Derecha'
        ];
        
        return $positions[$position] ?? $position;
    }


    // Buscar el id de la tabla cond a partir del id de usuario
    private function getCondIdByUserId($userId) {
        // Buscar en la tabla cond el registro que tenga el mismo num_documento que el usuario
        $db = new Database();
        $conn = $db->getConnection();
        $sql = "SELECT c.id FROM cond c JOIN users u ON c.regis_vehic_id = c.regis_vehic_id WHERE u.id = :user_id LIMIT 1";
        // Si hay un campo de relación directa, ajustar el JOIN y el WHERE
        // Alternativamente, si cond tiene un campo user_id, usar WHERE c.user_id = :user_id
        // Aquí se asume que la relación es por documento
        $sql = "SELECT c.id FROM cond c JOIN users u ON c.cargo = u.nombre WHERE u.id = :user_id LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['id'] : null;
    }

    // Enrutador principal
    public function handleRequest() {
        $action = $_GET['action'] ?? 'getAll';
        switch ($action) {
            case 'create':
                $this->create();
                break;
            case 'getVehicles':
                $this->getVehicles();
                break;
            case 'getConductores':
                $this->getConductores();
                break;
            case 'getAll':
                $this->getAll();
                break;
            case 'getByVehicle':
                $this->getByVehicle();
                break;
            case 'getTireAlerts':
                $this->getTireAlerts();
                break;
            case 'getById':
                $this->getById();
                break;
            case 'updateStatus':
                $this->updateStatus();
                break;
            case 'update':
                $this->update();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'validateConductor':
                $this->validateConductor();
                break;
            case 'getDashboard':
                $this->getDashboard();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
                break;
        }
    }
}

// Si el archivo es llamado directamente, procesar la solicitud
if (basename($_SERVER['PHP_SELF']) == 'AlertController.php') {
    session_start();
    $controller = new AlertController();
    $controller->handleRequest();
}
?>