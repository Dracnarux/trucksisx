<?php
require_once '../config/db.php';

class Alert {
    private $db;
    private $table = "alert";

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Crear una nueva alerta
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (descripcion, prioridad, estado, tipo_alerta, posicion_llanta, 
                   codigo_conductor, observaciones, imagen_evidencia, 
                   cond_id, regis_vehic_id) 
                  VALUES 
                  (:descripcion, :prioridad, :estado, :tipo_alerta, :posicion_llanta, 
                   :codigo_conductor, :observaciones, :imagen_evidencia, 
                   :cond_id, :regis_vehic_id)";

        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':prioridad', $data['prioridad']);
        $stmt->bindParam(':estado', $data['estado']);
        $stmt->bindParam(':tipo_alerta', $data['tipo_alerta']);
        $stmt->bindParam(':posicion_llanta', $data['posicion_llanta']);
        $stmt->bindParam(':codigo_conductor', $data['codigo_conductor']);
        $stmt->bindParam(':observaciones', $data['observaciones']);
        $stmt->bindParam(':imagen_evidencia', $data['imagen_evidencia']);
        $stmt->bindParam(':cond_id', $data['cond_id']);
        $stmt->bindParam(':regis_vehic_id', $data['regis_vehic_id']);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // Crear orden de trabajo automáticamente cuando se crea una alerta
    public function createWithWorkOrder($alertData, $workOrderData) {
        try {
            $this->db->beginTransaction();

            // Crear la alerta
            $alertId = $this->create($alertData);
            
            if ($alertId) {
                // Crear la orden de trabajo
                $query = "INSERT INTO ord_trabj 
                          (nombre_trabajo, descripcion, fecha_creacion, fecha_estimada, 
                           estado, prioridad, cond_id, users_id, alert_id) 
                          VALUES 
                          (:nombre_trabajo, :descripcion, CURDATE(), :fecha_estimada, 
                           :estado, :prioridad, :cond_id, :users_id, :alert_id)";

                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':nombre_trabajo', $workOrderData['nombre_trabajo']);
                $stmt->bindParam(':descripcion', $workOrderData['descripcion']);
                $stmt->bindParam(':fecha_estimada', $workOrderData['fecha_estimada']);
                $stmt->bindParam(':estado', $workOrderData['estado']);
                $stmt->bindParam(':prioridad', $workOrderData['prioridad']);
                $stmt->bindParam(':cond_id', $workOrderData['cond_id']);
                $stmt->bindParam(':users_id', $workOrderData['users_id']);
                $stmt->bindParam(':alert_id', $alertId);

                if ($stmt->execute()) {
                    $workOrderId = $this->db->lastInsertId();
                    
                    // Actualizar la alerta con el ID de la orden de trabajo
                    $updateQuery = "UPDATE " . $this->table . " 
                                   SET ord_trabj_id = :ord_trabj_id 
                                   WHERE id = :alert_id";
                    $updateStmt = $this->db->prepare($updateQuery);
                    $updateStmt->bindParam(':ord_trabj_id', $workOrderId);
                    $updateStmt->bindParam(':alert_id', $alertId);
                    $updateStmt->execute();

                    $this->db->commit();
                    return ['alert_id' => $alertId, 'work_order_id' => $workOrderId];
                }
            }
            
            $this->db->rollBack();
            return false;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // Obtener todas las alertas
    public function getAll() {
        $query = "SELECT a.*, c.cargo as conductor_cargo, r.placa as vehiculo_placa,
                         u.nombre as tecnico_nombre, ot.nombre_trabajo as orden_trabajo
                  FROM " . $this->table . " a 
                  LEFT JOIN cond c ON a.cond_id = c.id
                  LEFT JOIN regis_vehic r ON a.regis_vehic_id = r.id
                  LEFT JOIN ord_trabj ot ON a.ord_trabj_id = ot.id
                  LEFT JOIN users u ON ot.users_id = u.id
                  ORDER BY a.fecha_hora DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener alertas por vehículo
    public function getByVehicle($vehicleId) {
        $query = "SELECT a.*, c.cargo as conductor_cargo, r.placa as vehiculo_placa
                  FROM " . $this->table . " a 
                  LEFT JOIN cond c ON a.cond_id = c.id
                  LEFT JOIN regis_vehic r ON a.regis_vehic_id = r.id
                  WHERE a.regis_vehic_id = :vehicle_id 
                  ORDER BY a.fecha_hora DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':vehicle_id', $vehicleId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener alertas por conductor
    public function getByConductor($conductorId) {
        $query = "SELECT a.*, c.cargo as conductor_cargo, r.placa as vehiculo_placa
                  FROM " . $this->table . " a 
                  LEFT JOIN cond c ON a.cond_id = c.id
                  LEFT JOIN regis_vehic r ON a.regis_vehic_id = r.id
                  WHERE a.cond_id = :conductor_id 
                  ORDER BY a.fecha_hora DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':conductor_id', $conductorId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener alertas de llantas específicas
    public function getTireAlerts($vehicleId = null) {
        $whereClause = $vehicleId ? "AND a.regis_vehic_id = :vehicle_id" : "";
        
        $query = "SELECT a.*, c.cargo as conductor_cargo, r.placa as vehiculo_placa
                  FROM " . $this->table . " a 
                  LEFT JOIN cond c ON a.cond_id = c.id
                  LEFT JOIN regis_vehic r ON a.regis_vehic_id = r.id
                  WHERE a.tipo_alerta = 'llanta' AND a.posicion_llanta IS NOT NULL 
                  $whereClause
                  ORDER BY a.fecha_hora DESC";

        $stmt = $this->db->prepare($query);
        if ($vehicleId) {
            $stmt->bindParam(':vehicle_id', $vehicleId);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener una alerta por ID
    public function getById($id) {
        $query = "SELECT a.*, c.cargo as conductor_cargo, r.placa as vehiculo_placa,
                         u.nombre as tecnico_nombre, ot.nombre_trabajo as orden_trabajo
                  FROM " . $this->table . " a 
                  LEFT JOIN cond c ON a.cond_id = c.id
                  LEFT JOIN regis_vehic r ON a.regis_vehic_id = r.id
                  LEFT JOIN ord_trabj ot ON a.ord_trabj_id = ot.id
                  LEFT JOIN users u ON ot.users_id = u.id
                  WHERE a.id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar estado de alerta
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table . " 
                  SET estado = :estado 
                  WHERE id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':estado', $status);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    // Actualizar alerta completa
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET 
                  descripcion = :descripcion,
                  prioridad = :prioridad,
                  estado = :estado,
                  observaciones = :observaciones
                  WHERE id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':prioridad', $data['prioridad']);
        $stmt->bindParam(':estado', $data['estado']);
        $stmt->bindParam(':observaciones', $data['observaciones']);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    // Eliminar alerta
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Obtener estadísticas de alertas por posición de llanta
    public function getTirePositionStats($vehicleId = null) {
        $whereClause = $vehicleId ? "AND regis_vehic_id = :vehicle_id" : "";
        
        $query = "SELECT posicion_llanta, COUNT(*) as total_alertas,
                         SUM(CASE WHEN estado = 'activa' THEN 1 ELSE 0 END) as alertas_activas,
                         SUM(CASE WHEN prioridad = 'alta' OR prioridad = 'critica' THEN 1 ELSE 0 END) as alertas_criticas
                  FROM " . $this->table . " 
                  WHERE tipo_alerta = 'llanta' AND posicion_llanta IS NOT NULL 
                  $whereClause
                  GROUP BY posicion_llanta";

        $stmt = $this->db->prepare($query);
        if ($vehicleId) {
            $stmt->bindParam(':vehicle_id', $vehicleId);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener posiciones de llantas disponibles
    public function getTirePositions() {
        return [
            'direccion_izquierda' => 'Dirección Izquierda',
            'direccion_derecha' => 'Dirección Derecha',
            'traccion1_izquierda' => 'Tracción 1 - Izquierda',
            'traccion1_derecha' => 'Tracción 1 - Derecha',
            'traccion1_izquierda2' => 'Tracción 1 - Izquierda 2',
            'traccion1_derecha2' => 'Tracción 1 - Derecha 2',
            'traccion2_izquierda' => 'Tracción 2 - Izquierda',
            'traccion2_derecha' => 'Tracción 2 - Derecha'
        ];
    }
}
?>