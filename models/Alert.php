<?php
require_once __DIR__ . '/../config/db.php';

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
                   codigo_conductor, observaciones, 
                   cond_id, regis_vehic_id) 
                  VALUES 
                  (:descripcion, :prioridad, :estado, :tipo_alerta, :posicion_llanta, 
                   :codigo_conductor, :observaciones, 
                   :cond_id, :regis_vehic_id)";

        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':prioridad', $data['prioridad']);
        $stmt->bindParam(':estado', $data['estado']);
        $stmt->bindParam(':tipo_alerta', $data['tipo_alerta']);
        $stmt->bindParam(':posicion_llanta', $data['posicion_llanta']);
        $stmt->bindParam(':codigo_conductor', $data['codigo_conductor']);
        $stmt->bindParam(':observaciones', $data['observaciones']);
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
        $query = "SELECT a.*, c.cargo as conductor_cargo, r.placa as vehiculo_placa,
                         u.nombre as tecnico_nombre, ot.nombre_trabajo as orden_trabajo
                  FROM " . $this->table . " a 
                  LEFT JOIN cond c ON a.cond_id = c.id
                  LEFT JOIN regis_vehic r ON a.regis_vehic_id = r.id
                  LEFT JOIN ord_trabj ot ON a.ord_trabj_id = ot.id
                  LEFT JOIN users u ON ot.users_id = u.id
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
        
        if ($stmt->execute()) {
            // También actualizar la orden de trabajo asociada si existe
            $this->updateRelatedWorkOrder($id, $status);
            return true;
        }
        
        return false;
    }
    
    // Actualizar orden de trabajo relacionada cuando cambia el estado de la alerta
    private function updateRelatedWorkOrder($alertId, $alertStatus) {
        // Buscar órdenes de trabajo asociadas a esta alerta
        $query = "SELECT id FROM ord_trabj WHERE alert_id = :alert_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':alert_id', $alertId);
        $stmt->execute();
        $workOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($workOrders)) {
            // Mapear estados de alerta a estados de orden de trabajo
            $workOrderStatus = '';
            switch ($alertStatus) {
                case 'resuelta':
                    $workOrderStatus = 'completada';
                    break;
                case 'en_proceso':
                    $workOrderStatus = 'en_progreso';
                    break;
                case 'activa':
                    $workOrderStatus = 'pendiente';
                    break;
                case 'cancelada':
                    $workOrderStatus = 'cancelada';
                    break;
                default:
                    $workOrderStatus = 'pendiente';
            }
            
            // Actualizar todas las órdenes de trabajo relacionadas
            foreach ($workOrders as $workOrder) {
                $updateQuery = "UPDATE ord_trabj SET estado = :estado WHERE id = :id";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->bindParam(':estado', $workOrderStatus);
                $updateStmt->bindParam(':id', $workOrder['id']);
                $updateStmt->execute();
            }
        }
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
        try {
            // Iniciar transacción
            $this->db->beginTransaction();
            
            $totalActualizaciones = 0;
            
            // 1. Verificar y desvincular órdenes de trabajo
            $check_ord_sql = "SELECT COUNT(*) as count FROM ord_trabj WHERE alert_id = ?";
            $check_ord_stmt = $this->db->prepare($check_ord_sql);
            $check_ord_stmt->execute([$id]);
            $ord_result = $check_ord_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($ord_result['count'] > 0) {
                $update_ord_sql = "UPDATE ord_trabj SET alert_id = NULL WHERE alert_id = ?";
                $update_ord_stmt = $this->db->prepare($update_ord_sql);
                if (!$update_ord_stmt->execute([$id])) {
                    throw new Exception("Error al desvincular órdenes de trabajo");
                }
                $totalActualizaciones += $ord_result['count'];
            }
            
            // 2. Verificar y desvincular salidas de repuestos
            $check_repue_sql = "SELECT COUNT(*) as count FROM sali_repue WHERE alerta_id = ?";
            $check_repue_stmt = $this->db->prepare($check_repue_sql);
            $check_repue_stmt->execute([$id]);
            $repue_result = $check_repue_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($repue_result['count'] > 0) {
                $update_repue_sql = "UPDATE sali_repue SET alerta_id = NULL WHERE alerta_id = ?";
                $update_repue_stmt = $this->db->prepare($update_repue_sql);
                if (!$update_repue_stmt->execute([$id])) {
                    throw new Exception("Error al desvincular salidas de repuestos");
                }
                $totalActualizaciones += $repue_result['count'];
            }
            
            // 3. Verificar y desvincular salidas de vehículos
            $check_vehi_sql = "SELECT COUNT(*) as count FROM sali_vehi WHERE alerta_id = ?";
            $check_vehi_stmt = $this->db->prepare($check_vehi_sql);
            $check_vehi_stmt->execute([$id]);
            $vehi_result = $check_vehi_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($vehi_result['count'] > 0) {
                $update_vehi_sql = "UPDATE sali_vehi SET alerta_id = NULL WHERE alerta_id = ?";
                $update_vehi_stmt = $this->db->prepare($update_vehi_sql);
                if (!$update_vehi_stmt->execute([$id])) {
                    throw new Exception("Error al desvincular salidas de vehículos");
                }
                $totalActualizaciones += $vehi_result['count'];
            }
            
            // Log para debugging
            error_log("Alert ID {$id}: Se desvincularon {$totalActualizaciones} registros antes de eliminar");
            
            // 4. Ahora eliminar la alerta
            $delete_sql = "DELETE FROM " . $this->table . " WHERE id = ?";
            $delete_stmt = $this->db->prepare($delete_sql);
            
            if (!$delete_stmt->execute([$id])) {
                throw new Exception("Error al eliminar la alerta de la base de datos");
            }
            
            // Verificar que se eliminó correctamente
            if ($delete_stmt->rowCount() === 0) {
                throw new Exception("No se encontró la alerta para eliminar");
            }
            
            // Confirmar transacción
            $this->db->commit();
            error_log("Alert ID {$id}: Eliminación completada exitosamente");
            return true;
            
        } catch (Exception $e) {
            // Revertir transacción
            $this->db->rollback();
            error_log("Error al eliminar Alert ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    // Obtener estadísticas de alertas por posición de llanta
    public function getTirePositionStats($vehicleId = null) {
        // Obtener todas las posiciones posibles
        $positions = $this->getTirePositions();
        
        // Crear consulta con todas las posiciones usando UNION ALL
        $whereClause = $vehicleId ? "AND a.regis_vehic_id = :vehicle_id" : "";
        
        $query = "
            SELECT 
                p.posicion_llanta,
                COALESCE(COUNT(a.id), 0) as total_alertas,
                COALESCE(SUM(CASE WHEN a.estado = 'activa' THEN 1 ELSE 0 END), 0) as alertas_activas,
                COALESCE(SUM(CASE WHEN a.prioridad = 'critica' THEN 1 ELSE 0 END), 0) as alertas_criticas,
                COALESCE(SUM(CASE WHEN a.prioridad = 'alta' THEN 1 ELSE 0 END), 0) as alertas_altas,
                COALESCE(SUM(CASE WHEN a.prioridad = 'media' THEN 1 ELSE 0 END), 0) as alertas_medias,
                COALESCE(SUM(CASE WHEN a.prioridad = 'baja' THEN 1 ELSE 0 END), 0) as alertas_bajas
            FROM (";
        
        // Agregar cada posición como una fila en la subconsulta
        $positionQueries = [];
        foreach ($positions as $position_key => $position_name) {
            $positionQueries[] = "SELECT '$position_key' as posicion_llanta";
        }
        
        $query .= implode(" UNION ALL ", $positionQueries);
        
        $query .= ") p
            LEFT JOIN " . $this->table . " a ON p.posicion_llanta = a.posicion_llanta 
                AND a.tipo_alerta = 'llanta' $whereClause
            GROUP BY p.posicion_llanta
            ORDER BY p.posicion_llanta";

        $stmt = $this->db->prepare($query);
        if ($vehicleId) {
            $stmt->bindParam(':vehicle_id', $vehicleId);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir todos los valores a enteros
        foreach ($result as &$row) {
            foreach ($row as $key => $value) {
                if ($key !== 'posicion_llanta') {
                    $row[$key] = (int)$value;
                }
            }
        }
        
        // Debug: verificar cuántas posiciones se están devolviendo
        error_log("Total de posiciones en estadísticas: " . count($result));
        
        return $result;
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
            'traccion2_derecha' => 'Tracción 2 - Derecha',
            'traccion2_izquierda2' => 'Tracción 2 - Izquierda 2',
            'traccion2_derecha2' => 'Tracción 2 - Derecha 2'
        ];
    }
}
?>