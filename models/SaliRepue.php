<?php
class SaliRepue {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    public function registrarSalida($data) {
        $stmt = $this->db->prepare("INSERT INTO sali_repue (fecha_salida, cantidad, repue_id, ord_trabj_id, repor_id, alerta_id, sali_vehi_id) VALUES (?, ?, ?, ?, ?, ?, NULL)");
        $stmt->bind_param('siiiis', $data['fecha_salida'], $data['cantidad'], $data['repue_id'], $data['ord_trabj_id'], $data['repor_id'], $data['alerta_id']);
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM sali_repue WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function crearReporte($data) {
        $stmt = $this->db->prepare("INSERT INTO repor (nombre_reporte, tipo_reporte, fecha_creacion, activo, sali_repue_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssii', $data['nombre_reporte'], $data['tipo_reporte'], $data['fecha_creacion'], $data['activo'], $data['sali_repue_id']);
        if ($stmt->execute()) {
            // Actualizar el registro de salida con el nuevo repor_id
            $repor_id = $this->db->insert_id;
            $update = $this->db->prepare("UPDATE sali_repue SET repor_id = ? WHERE id = ?");
            $update->bind_param('ii', $repor_id, $data['sali_repue_id']);
            $update->execute();
            return $repor_id;
        }
        return false;
    }

    public function getAll() {
        $result = $this->db->query("SELECT * FROM sali_repue ORDER BY id DESC");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function eliminar($id) {
        try {
            // Log inicio del proceso
            error_log("SaliRepue::eliminar - Iniciando eliminación de ID: $id");
            
            // Deshabilitar autocommit para manejar transacción manualmente
            $this->db->autocommit(false);
            
            // Obtener información de la salida antes de eliminar
            error_log("SaliRepue::eliminar - Obteniendo información de la salida");
            $salida = $this->getById($id);
            if (!$salida) {
                error_log("SaliRepue::eliminar - ERROR: La salida ID $id no existe");
                throw new Exception("La salida de repuesto no existe");
            }
            
            error_log("SaliRepue::eliminar - Salida encontrada: " . json_encode($salida));
            
            $operaciones = 0;
            $reporteId = $salida['repor_id']; // Guardar el ID del reporte para manejarlo después
            
            // PASO 1: Verificar si hay salidas de vehículos que referencian esta salida
            error_log("SaliRepue::eliminar - Verificando salidas de vehículos relacionadas");
            $checkSaliVehi = $this->db->prepare("SELECT COUNT(*) as count FROM sali_vehi WHERE sali_repue_id = ?");
            $checkSaliVehi->bind_param('i', $id);
            $checkSaliVehi->execute();
            $result = $checkSaliVehi->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                error_log("SaliRepue::eliminar - Desvinculando {$row['count']} salidas de vehículos");
                $updateSaliVehi = $this->db->prepare("UPDATE sali_vehi SET sali_repue_id = NULL WHERE sali_repue_id = ?");
                $updateSaliVehi->bind_param('i', $id);
                if (!$updateSaliVehi->execute()) {
                    error_log("SaliRepue::eliminar - ERROR al desvincular salidas de vehículos: " . $this->db->error);
                    throw new Exception("Error al desvincular salidas de vehículos: " . $this->db->error);
                }
                $operaciones++;
                error_log("SaliRepue::eliminar - Salidas de vehículos desvinculadas exitosamente");
            }
            
            // PASO 2: Manejar TODAS las FK constraints que pueden impedir la eliminación
            if (!empty($reporteId)) {
                error_log("SaliRepue::eliminar - Manejando relación circular con reporte ID: $reporteId");
                
                // CRÍTICO: Verificar si hay otras referencias al reporte antes de eliminarlo
                error_log("SaliRepue::eliminar - Verificando otras referencias al reporte ID: $reporteId");
                $checkOtrasRefs = $this->db->prepare("SELECT COUNT(*) as count FROM sali_repue WHERE repor_id = ? AND id != ?");
                $checkOtrasRefs->bind_param('ii', $reporteId, $id);
                $checkOtrasRefs->execute();
                $result = $checkOtrasRefs->get_result();
                $row = $result->fetch_assoc();
                $otrasReferencias = $row['count'];
                error_log("SaliRepue::eliminar - Otras salidas que referencian el reporte: $otrasReferencias");
                
                // A) SIEMPRE eliminar la FK del reporte hacia la salida actual
                error_log("SaliRepue::eliminar - Paso A: Desvinculando reporte->salida (repor.sali_repue_id = NULL WHERE id = $reporteId)");
                $updateReporte = $this->db->prepare("UPDATE repor SET sali_repue_id = NULL WHERE id = ? AND sali_repue_id = ?");
                $updateReporte->bind_param('ii', $reporteId, $id);
                if (!$updateReporte->execute()) {
                    error_log("SaliRepue::eliminar - ERROR al desvincular reporte->salida: " . $this->db->error);
                    throw new Exception("Error al desvincular reporte->salida: " . $this->db->error);
                }
                $reporteRowsUpdated = $updateReporte->affected_rows;
                error_log("SaliRepue::eliminar - Filas de reporte actualizadas: $reporteRowsUpdated");
                $operaciones++;
                
                // B) Eliminar la FK de la salida hacia el reporte
                error_log("SaliRepue::eliminar - Paso B: Desvinculando salida->reporte (sali_repue.repor_id = NULL WHERE id = $id)");
                $updateSalida = $this->db->prepare("UPDATE sali_repue SET repor_id = NULL WHERE id = ?");
                $updateSalida->bind_param('i', $id);
                if (!$updateSalida->execute()) {
                    error_log("SaliRepue::eliminar - ERROR al desvincular salida->reporte: " . $this->db->error);
                    throw new Exception("Error al desvincular salida->reporte: " . $this->db->error);
                }
                $salidaRowsUpdated = $updateSalida->affected_rows;
                error_log("SaliRepue::eliminar - Filas de salida actualizadas: $salidaRowsUpdated");
                $operaciones++;
                
                // C) CRÍTICO: Forzar commit intermedio para que MySQL reconozca las FK eliminadas
                error_log("SaliRepue::eliminar - Confirmando desvinculaciones FK antes de continuar");
                if (!$this->db->commit()) {
                    error_log("SaliRepue::eliminar - ERROR en commit intermedio: " . $this->db->error);
                    throw new Exception("Error al confirmar desvinculaciones FK: " . $this->db->error);
                }
                
                // Reiniciar transacción para continuar
                $this->db->autocommit(false);
                if (!$this->db->begin_transaction()) {
                    error_log("SaliRepue::eliminar - ERROR al reiniciar transacción: " . $this->db->error);
                    throw new Exception("Error al reiniciar transacción: " . $this->db->error);
                }
                
                error_log("SaliRepue::eliminar - FK constraints confirmadas y transacción reiniciada");
                
                // D) Si no hay otras referencias, marcar reporte para eliminación completa
                if ($otrasReferencias == 0) {
                    error_log("SaliRepue::eliminar - Reporte $reporteId será eliminado (no hay otras referencias)");
                } else {
                    error_log("SaliRepue::eliminar - Reporte $reporteId se mantiene (tiene $otrasReferencias otras referencias)");
                }
            } else {
                error_log("SaliRepue::eliminar - No hay reporte asociado para desvincular");
            }
            
            // PASO 3: ELIMINAR TODOS los reportes que referencien esta salida
            error_log("SaliRepue::eliminar - PASO 3A: Buscando TODOS los reportes que referencian la salida ID: $id");
            
            // Buscar todos los reportes que referencian esta salida
            $findAllReports = $this->db->prepare("SELECT id, nombre_reporte FROM repor WHERE sali_repue_id = ?");
            $findAllReports->bind_param('i', $id);
            $findAllReports->execute();
            $result = $findAllReports->get_result();
            
            $reportesEncontrados = [];
            while ($row = $result->fetch_assoc()) {
                $reportesEncontrados[] = $row;
            }
            
            error_log("SaliRepue::eliminar - Reportes encontrados que referencian salida $id: " . count($reportesEncontrados));
            
            // Eliminar cada reporte encontrado
            foreach ($reportesEncontrados as $reporte) {
                $repId = $reporte['id'];
                $repNombre = $reporte['nombre_reporte'];
                error_log("SaliRepue::eliminar - Eliminando reporte ID: $repId ($repNombre)");
                
                $deleteReporte = $this->db->prepare("DELETE FROM repor WHERE id = ?");
                $deleteReporte->bind_param('i', $repId);
                if (!$deleteReporte->execute()) {
                    error_log("SaliRepue::eliminar - ERROR al eliminar reporte $repId: " . $this->db->error);
                    throw new Exception("Error al eliminar el reporte $repId ($repNombre): " . $this->db->error);
                } else {
                    $reporteRowsAffected = $deleteReporte->affected_rows;
                    if ($reporteRowsAffected > 0) {
                        $operaciones++;
                        error_log("SaliRepue::eliminar - Reporte $repId eliminado exitosamente");
                    } else {
                        error_log("SaliRepue::eliminar - ADVERTENCIA: Reporte $repId no encontrado");
                    }
                }
            }
            
            // También eliminar el reporte referenciado por repor_id si existe y es diferente
            if (!empty($reporteId)) {
                $yaEliminado = false;
                foreach ($reportesEncontrados as $reporte) {
                    if ($reporte['id'] == $reporteId) {
                        $yaEliminado = true;
                        break;
                    }
                }
                
                if (!$yaEliminado) {
                    error_log("SaliRepue::eliminar - Eliminando reporte adicional ID: $reporteId (referenciado por repor_id)");
                    $deleteReporte = $this->db->prepare("DELETE FROM repor WHERE id = ?");
                    $deleteReporte->bind_param('i', $reporteId);
                    if (!$deleteReporte->execute()) {
                        error_log("SaliRepue::eliminar - ERROR al eliminar reporte adicional $reporteId: " . $this->db->error);
                        // No lanzar excepción, intentar continuar
                    } else {
                        $reporteRowsAffected = $deleteReporte->affected_rows;
                        if ($reporteRowsAffected > 0) {
                            $operaciones++;
                            error_log("SaliRepue::eliminar - Reporte adicional $reporteId eliminado exitosamente");
                        }
                    }
                }
            }
            
            // PASO 3B: AHORA eliminar la salida de repuesto (sin FK constraints hacia repor)
            error_log("SaliRepue::eliminar - PASO 3B: Eliminando salida de repuesto ID: $id");
            $deleteSalida = $this->db->prepare("DELETE FROM sali_repue WHERE id = ?");
            $deleteSalida->bind_param('i', $id);
            
            if (!$deleteSalida->execute()) {
                error_log("SaliRepue::eliminar - ERROR al eliminar salida: " . $this->db->error);
                throw new Exception("Error al eliminar la salida de repuesto: " . $this->db->error);
            }
            
            // Verificar que realmente se eliminó
            $rowsAffected = $deleteSalida->affected_rows;
            error_log("SaliRepue::eliminar - Filas afectadas en DELETE sali_repue: $rowsAffected");
            
            if ($rowsAffected === 0) {
                error_log("SaliRepue::eliminar - ERROR: No se eliminó ninguna fila de sali_repue");
                throw new Exception("No se pudo eliminar la salida de repuesto - no se encontró el registro");
            }
            
            $operaciones++;
            error_log("SaliRepue::eliminar - Salida de repuesto eliminada exitosamente");
            
            // Confirmar transacción
            if (!$this->db->commit()) {
                error_log("SaliRepue::eliminar - ERROR en commit: " . $this->db->error);
                throw new Exception("Error al confirmar la eliminación: " . $this->db->error);
            }
            
            // Restaurar autocommit
            $this->db->autocommit(true);
            
            error_log("SaliRepue::eliminar - ÉXITO: Eliminación completada. Total operaciones: $operaciones");
            return true;
            
        } catch (Exception $e) {
            error_log("SaliRepue::eliminar - EXCEPCIÓN: " . $e->getMessage());
            
            // Revertir transacción
            $this->db->rollback();
            
            // Restaurar autocommit
            $this->db->autocommit(true);
            
            throw $e;
        }
    }

    public function actualizar($id, $data) {
        try {
            error_log("SaliRepue::actualizar - Iniciando actualización de ID: $id");
            
            // Verificar que la salida existe
            $salida = $this->getById($id);
            if (!$salida) {
                throw new Exception("La salida de repuesto no existe");
            }
            
            // Preparar la consulta de actualización
            $sql = "UPDATE sali_repue SET 
                    fecha_salida = ?, 
                    cantidad = ?, 
                    repue_id = ?, 
                    ord_trabj_id = ?, 
                    alerta_id = ? 
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error preparando consulta: " . $this->db->error);
            }
            
            $stmt->bind_param('siiiis', 
                $data['fecha_salida'], 
                $data['cantidad'], 
                $data['repue_id'], 
                $data['ord_trabj_id'], 
                $data['alerta_id'], 
                $id
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Error ejecutando actualización: " . $stmt->error);
            }
            
            $rowsAffected = $stmt->affected_rows;
            error_log("SaliRepue::actualizar - Filas afectadas: $rowsAffected");
            
            if ($rowsAffected === 0) {
                // Puede ser que no cambió nada, no necesariamente un error
                error_log("SaliRepue::actualizar - No se modificaron datos (posiblemente valores iguales)");
            }
            
            error_log("SaliRepue::actualizar - Actualización completada exitosamente");
            return true;
            
        } catch (Exception $e) {
            error_log("SaliRepue::actualizar - ERROR: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAllWithDetails() {
        $sql = "SELECT 
                    sr.*,
                    r.nombre as repuesto_nombre,
                    ot.nombre_trabajo as orden_nombre,
                    rep.nombre_reporte as reporte_nombre
                FROM sali_repue sr
                LEFT JOIN repue r ON sr.repue_id = r.id
                LEFT JOIN ord_trabj ot ON sr.ord_trabj_id = ot.id
                LEFT JOIN repor rep ON sr.repor_id = rep.id
                ORDER BY sr.id DESC";
        
        $result = $this->db->query($sql);
        if (!$result) {
            error_log("SaliRepue::getAllWithDetails - Error en consulta: " . $this->db->error);
            return [];
        }
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
}
