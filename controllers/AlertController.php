<?php
require_once '../models/Alert.php';
require_once '../models/User.php';

class AlertController {
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
                echo json_encode(['success' => false, 'message' => 'Error al crear la alerta']);
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
            $result = $this->alert->updateStatus($_POST['id'], $_POST['estado']);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado']);
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
        if ($_POST && isset($_POST['id'])) {
            $result = $this->alert->delete($_POST['id']);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Alerta eliminada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar la alerta']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
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
                        'documento' => $conductor['num_documento']
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
        
        $allAlerts = $this->alert->getAll();
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