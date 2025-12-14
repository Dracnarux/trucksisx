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
        $query = "SELECT a.*, COALESCE(a.posicion_llanta, ot.nombre_trabajo, a.descripcion) as titulo, c.cargo as conductor_cargo, r.placa as vehiculo_placa,
             u.nombre as tecnico_nombre, ot.nombre_trabajo as orden_trabajo
              FROM " . $this->table . " a 
              LEFT JOIN cond c ON a.cond_id = c.id
              LEFT JOIN regis_vehic r ON a.regis_vehic_id = r.id
              LEFT JOIN ord_trabj ot ON a.ord_trabj_id = ot.id
              LEFT JOIN users u ON ot.users_id = u.id
              ORDER BY a.fecha_hora DESC, a.id DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener alertas por vehículo
    public function getByVehicle($vehicleId) {
        $query = "SELECT a.*, COALESCE(a.posicion_llanta, ot.nombre_trabajo, a.descripcion) as titulo, c.cargo as conductor_cargo, r.placa as vehiculo_placa,
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
        $query = "SELECT a.*, COALESCE(a.posicion_llanta, ot.nombre_trabajo, a.descripcion) as titulo, c.cargo as conductor_cargo, r.placa as vehiculo_placa
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

    // Obtener todas las alertas (de cualquier tipo)
    public function getTireAlerts($vehicleId = null) {
        $whereClause = $vehicleId ? "WHERE a.regis_vehic_id = :vehicle_id" : "";
        
        // Incluir nombre de la orden de trabajo relacionada para que el buscador pueda
        // filtrar también por la orden (si existe).
        $query = "SELECT a.*, COALESCE(a.posicion_llanta, ot.nombre_trabajo, a.descripcion) as titulo, c.cargo as conductor_cargo, r.placa as vehiculo_placa, ot.nombre_trabajo as orden_trabajo
                  FROM " . $this->table . " a 
                  LEFT JOIN cond c ON a.cond_id = c.id
                  LEFT JOIN regis_vehic r ON a.regis_vehic_id = r.id
                  LEFT JOIN ord_trabj ot ON a.ord_trabj_id = ot.id
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
        $query = "SELECT a.*, COALESCE(a.posicion_llanta, ot.nombre_trabajo, a.descripcion) as titulo, c.cargo as conductor_cargo, r.placa as vehiculo_placa,
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
        // Construir query dinámicamente según los campos presentes
        $fields = [];
        $params = [];
        
        if (isset($data['descripcion'])) {
            $fields[] = "descripcion = :descripcion";
            $params[':descripcion'] = $data['descripcion'];
        }
        if (isset($data['prioridad'])) {
            $fields[] = "prioridad = :prioridad";
            $params[':prioridad'] = $data['prioridad'];
        }
        if (isset($data['estado'])) {
            $fields[] = "estado = :estado";
            $params[':estado'] = $data['estado'];
        }
        if (isset($data['observaciones'])) {
            $fields[] = "observaciones = :observaciones";
            $params[':observaciones'] = $data['observaciones'];
        }
        if (isset($data['posicion_llanta'])) {
            $fields[] = "posicion_llanta = :posicion_llanta";
            $params[':posicion_llanta'] = $data['posicion_llanta'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
        $params[':id'] = $id;
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
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

    // ========== SISTEMA DE RESOLUCIÓN DE ALERTAS ==========

    /**
     * Resolver una alerta
     * @param int $alertId ID de la alerta
     * @param int $userId ID del usuario que resuelve
     * @param string $notas Notas de resolución
     * @return array Resultado de la operación
     */
    public function resolve($alertId, $userId, $notas = '') {
        try {
            $this->db->beginTransaction();

            // 1. Verificar que la alerta existe y NO está ya resuelta
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $alertId);
            $stmt->execute();
            $alert = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$alert) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'La alerta no existe.'
                ];
            }

            if ($alert['estado'] === 'resuelta') {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Esta alerta ya fue resuelta anteriormente.'
                ];
            }

            // 2. Actualizar la alerta
            $updateQuery = "UPDATE " . $this->table . " 
                           SET estado = 'resuelta',
                               fecha_resolucion = NOW(),
                               usuario_resuelve_id = :user_id,
                               notas_resolucion = :notas
                           WHERE id = :id";
            
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':user_id', $userId);
            $updateStmt->bindParam(':notas', $notas);
            $updateStmt->bindParam(':id', $alertId);
            $updateStmt->execute();

            // 3. Actualizar orden de trabajo relacionada (si existe)
            if ($alert['ord_trabj_id']) {
                $ordQuery = "UPDATE ord_trabj SET estado = 'completada' WHERE id = :ord_id";
                $ordStmt = $this->db->prepare($ordQuery);
                $ordStmt->bindParam(':ord_id', $alert['ord_trabj_id']);
                $ordStmt->execute();
            }

            // 4. Desvincular órdenes de trabajo del conductor (si tiene conductor asociado)
            if ($alert['cond_id'] && $alert['ord_trabj_id']) {
                $unlinkCondQuery = "UPDATE ord_trabj SET cond_id = NULL WHERE id = :ord_id";
                $unlinkCondStmt = $this->db->prepare($unlinkCondQuery);
                $unlinkCondStmt->bindParam(':ord_id', $alert['ord_trabj_id']);
                $unlinkCondStmt->execute();
            }

            $this->db->commit();

            // Obtener información del usuario que resolvió
            $userQuery = "SELECT nombre FROM users WHERE id = :user_id";
            $userStmt = $this->db->prepare($userQuery);
            $userStmt->bindParam(':user_id', $userId);
            $userStmt->execute();
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'message' => 'Alerta resuelta exitosamente. Ahora puedes eliminar el conductor/vehículo asociado si lo deseas.',
                'data' => [
                    'alert_id' => $alertId,
                    'fecha_resolucion' => date('Y-m-d H:i:s'),
                    'usuario_resuelve' => $user['nombre'] ?? 'Usuario'
                ]
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al resolver la alerta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar si se puede eliminar un conductor
     * @param int $conductorId ID del conductor
     * @return array Resultado con información de alertas pendientes
     */
    public function canDeleteConductor($conductorId) {
        try {
            // Contar alertas no resueltas
            $query = "SELECT COUNT(*) as total 
                     FROM " . $this->table . " 
                     WHERE cond_id = :conductor_id 
                     AND estado IN ('activa', 'en_proceso', 'cancelada')";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':conductor_id', $conductorId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['total'] > 0) {
                // Obtener detalles de las alertas pendientes
                $detailQuery = "SELECT id, descripcion, estado, fecha_hora, prioridad
                               FROM " . $this->table . " 
                               WHERE cond_id = :conductor_id 
                               AND estado != 'resuelta'
                               ORDER BY fecha_hora DESC
                               LIMIT 10";
                
                $detailStmt = $this->db->prepare($detailQuery);
                $detailStmt->bindParam(':conductor_id', $conductorId);
                $detailStmt->execute();
                $alerts = $detailStmt->fetchAll(PDO::FETCH_ASSOC);

                return [
                    'success' => true,
                    'can_delete' => false,
                    'message' => 'No se puede eliminar el conductor porque tiene ' . $result['total'] . ' alertas pendientes o en proceso. Por favor, resuélvelas primero.',
                    'pending_alerts' => (int)$result['total'],
                    'alerts' => $alerts
                ];
            }

            return [
                'success' => true,
                'can_delete' => true,
                'message' => 'El conductor puede ser eliminado.',
                'pending_alerts' => 0
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al verificar alertas: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar si se puede eliminar un vehículo
     * @param int $vehicleId ID del vehículo
     * @return array Resultado con información de alertas pendientes
     */
    public function canDeleteVehicle($vehicleId) {
        try {
            // Contar alertas no resueltas
            $query = "SELECT COUNT(*) as total 
                     FROM " . $this->table . " 
                     WHERE regis_vehic_id = :vehicle_id 
                     AND estado IN ('activa', 'en_proceso', 'cancelada')";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':vehicle_id', $vehicleId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['total'] > 0) {
                // Obtener detalles de las alertas pendientes
                $detailQuery = "SELECT id, descripcion, estado, fecha_hora, prioridad
                               FROM " . $this->table . " 
                               WHERE regis_vehic_id = :vehicle_id 
                               AND estado != 'resuelta'
                               ORDER BY fecha_hora DESC
                               LIMIT 10";
                
                $detailStmt = $this->db->prepare($detailQuery);
                $detailStmt->bindParam(':vehicle_id', $vehicleId);
                $detailStmt->execute();
                $alerts = $detailStmt->fetchAll(PDO::FETCH_ASSOC);

                return [
                    'success' => true,
                    'can_delete' => false,
                    'message' => 'No se puede eliminar el vehículo porque tiene ' . $result['total'] . ' alertas pendientes o en proceso. Por favor, resuélvelas primero.',
                    'pending_alerts' => (int)$result['total'],
                    'alerts' => $alerts
                ];
            }

            return [
                'success' => true,
                'can_delete' => true,
                'message' => 'El vehículo puede ser eliminado.',
                'pending_alerts' => 0
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al verificar alertas: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Desvincular alertas resueltas antes de eliminar conductor
     * @param int $conductorId ID del conductor
     * @return bool
     */
    public function unlinkResolvedAlertsConductor($conductorId) {
        try {
            $query = "UPDATE " . $this->table . " 
                     SET cond_id = NULL 
                     WHERE cond_id = :conductor_id 
                     AND estado = 'resuelta'";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':conductor_id', $conductorId);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Desvincular alertas resueltas antes de eliminar vehículo
     * @param int $vehicleId ID del vehículo
     * @return bool
     */
    public function unlinkResolvedAlertsVehicle($vehicleId) {
        try {
            $query = "UPDATE " . $this->table . " 
                     SET regis_vehic_id = NULL 
                     WHERE regis_vehic_id = :vehicle_id 
                     AND estado = 'resuelta'";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':vehicle_id', $vehicleId);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
?>