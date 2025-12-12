<?php
class SaliVehi {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    public function registrarSalida($data) {
        $stmt = $this->db->prepare("INSERT INTO sali_vehi (id_flotas, segui_monitoreo, control_combustible, cump_regulaciones, protocolo_seguridad, gest_conductores, repor_id, ord_trabj_id, alerta_id, sali_repue_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('issssssiii', $data['id_flotas'], $data['segui_monitoreo'], $data['control_combustible'], $data['cump_regulaciones'], $data['protocolo_seguridad'], $data['gest_conductores'], $data['repor_id'], $data['ord_trabj_id'], $data['alerta_id'], $data['sali_repue_id']);
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM sali_vehi WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function getAll() {
        $result = $this->db->query("SELECT * FROM sali_vehi ORDER BY id DESC");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function crearReporte($data) {
        $stmt = $this->db->prepare("INSERT INTO repor (nombre_reporte, tipo_reporte, fecha_creacion, activo, sali_vehi_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssii', $data['nombre_reporte'], $data['tipo_reporte'], $data['fecha_creacion'], $data['activo'], $data['sali_vehi_id']);
        if ($stmt->execute()) {
            $repor_id = $this->db->insert_id;
            $update = $this->db->prepare("UPDATE sali_vehi SET repor_id = ? WHERE id = ?");
            $update->bind_param('ii', $repor_id, $data['sali_vehi_id']);
            $update->execute();
            return $repor_id;
        }
        return false;
    }

    public function actualizarSalida($id, $data) {
        $stmt = $this->db->prepare("UPDATE sali_vehi SET id_flotas = ?, segui_monitoreo = ?, control_combustible = ?, cump_regulaciones = ?, protocolo_seguridad = ?, gest_conductores = ? WHERE id = ?");
        $stmt->bind_param('isssssi', $data['id_flotas'], $data['segui_monitoreo'], $data['control_combustible'], $data['cump_regulaciones'], $data['protocolo_seguridad'], $data['gest_conductores'], $id);
        return $stmt->execute();
    }

    public function eliminarSalida($id) {
        try {
            // Iniciar transacción
            $this->db->autocommit(false);
            if (!$this->db->begin_transaction()) {
                error_log("SaliVehi::eliminarSalida - ERROR al iniciar transacción: " . $this->db->error);
                throw new Exception("Error al iniciar transacción para eliminación: " . $this->db->error);
            }
            
            error_log("SaliVehi::eliminarSalida - Iniciando eliminación de salida vehículo ID: $id");
            
            // Obtener información de la salida
            error_log("SaliVehi::eliminarSalida - Obteniendo información de la salida");
            $getSalida = $this->db->prepare("SELECT * FROM sali_vehi WHERE id = ?");
            $getSalida->bind_param('i', $id);
            $getSalida->execute();
            $result = $getSalida->get_result();
            $salida = $result->fetch_assoc();
            
            if (!$salida) {
                error_log("SaliVehi::eliminarSalida - ERROR: No se encontró la salida ID: $id");
                throw new Exception("No se encontró la salida de vehículo especificada");
            }
            
            error_log("SaliVehi::eliminarSalida - Salida encontrada: " . json_encode($salida));
            
            $operaciones = 0;
            $reporteId = $salida['repor_id'];
            
            // PASO 1: Verificar y desvincular salidas de repuesto que referencian esta salida
            error_log("SaliVehi::eliminarSalida - Verificando salidas de repuesto relacionadas");
            $checkSaliRepue = $this->db->prepare("SELECT COUNT(*) as count FROM sali_repue WHERE sali_vehi_id = ?");
            $checkSaliRepue->bind_param('i', $id);
            $checkSaliRepue->execute();
            $result = $checkSaliRepue->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                error_log("SaliVehi::eliminarSalida - Desvinculando {$row['count']} salidas de repuesto");
                $updateSaliRepue = $this->db->prepare("UPDATE sali_repue SET sali_vehi_id = NULL WHERE sali_vehi_id = ?");
                $updateSaliRepue->bind_param('i', $id);
                if (!$updateSaliRepue->execute()) {
                    error_log("SaliVehi::eliminarSalida - ERROR al desvincular salidas de repuesto: " . $this->db->error);
                    throw new Exception("Error al desvincular salidas de repuesto: " . $this->db->error);
                }
                $operaciones++;
                error_log("SaliVehi::eliminarSalida - Salidas de repuesto desvinculadas exitosamente");
            }
            
            // PASO 2: Manejar FK circular con reportes si existe
            if (!empty($reporteId)) {
                error_log("SaliVehi::eliminarSalida - Manejando relación circular con reporte ID: $reporteId");
                
                // A) Desvincular reporte->salida
                error_log("SaliVehi::eliminarSalida - Desvinculando reporte->salida (repor.sali_vehi_id = NULL)");
                $updateReporte = $this->db->prepare("UPDATE repor SET sali_vehi_id = NULL WHERE id = ? AND sali_vehi_id = ?");
                $updateReporte->bind_param('ii', $reporteId, $id);
                if (!$updateReporte->execute()) {
                    error_log("SaliVehi::eliminarSalida - ERROR al desvincular reporte->salida: " . $this->db->error);
                    throw new Exception("Error al desvincular reporte->salida: " . $this->db->error);
                }
                $operaciones++;
                error_log("SaliVehi::eliminarSalida - Reporte->salida desvinculado exitosamente");
                
                // B) Desvincular salida->reporte
                error_log("SaliVehi::eliminarSalida - Desvinculando salida->reporte (sali_vehi.repor_id = NULL)");
                $updateSalida = $this->db->prepare("UPDATE sali_vehi SET repor_id = NULL WHERE id = ?");
                $updateSalida->bind_param('i', $id);
                if (!$updateSalida->execute()) {
                    error_log("SaliVehi::eliminarSalida - ERROR al desvincular salida->reporte: " . $this->db->error);
                    throw new Exception("Error al desvincular salida->reporte: " . $this->db->error);
                }
                $operaciones++;
                error_log("SaliVehi::eliminarSalida - Salida->reporte desvinculado exitosamente");
                
                // C) Commit intermedio para confirmar desvinculaciones
                error_log("SaliVehi::eliminarSalida - Confirmando desvinculaciones FK");
                if (!$this->db->commit()) {
                    error_log("SaliVehi::eliminarSalida - ERROR en commit intermedio: " . $this->db->error);
                    throw new Exception("Error al confirmar desvinculaciones FK: " . $this->db->error);
                }
                
                // Reiniciar transacción
                $this->db->autocommit(false);
                if (!$this->db->begin_transaction()) {
                    error_log("SaliVehi::eliminarSalida - ERROR al reiniciar transacción: " . $this->db->error);
                    throw new Exception("Error al reiniciar transacción: " . $this->db->error);
                }
                
                error_log("SaliVehi::eliminarSalida - FK constraints confirmadas y transacción reiniciada");
            }
            
            // PASO 3: Eliminar TODOS los reportes que referencian esta salida
            error_log("SaliVehi::eliminarSalida - Buscando TODOS los reportes que referencian la salida ID: $id");
            $findAllReports = $this->db->prepare("SELECT id, nombre_reporte FROM repor WHERE sali_vehi_id = ?");
            $findAllReports->bind_param('i', $id);
            $findAllReports->execute();
            $result = $findAllReports->get_result();
            
            $reportesEncontrados = [];
            while ($row = $result->fetch_assoc()) {
                $reportesEncontrados[] = $row;
            }
            
            error_log("SaliVehi::eliminarSalida - Reportes encontrados: " . count($reportesEncontrados));
            
            // Eliminar cada reporte encontrado
            foreach ($reportesEncontrados as $reporte) {
                $repId = $reporte['id'];
                $repNombre = $reporte['nombre_reporte'];
                error_log("SaliVehi::eliminarSalida - Eliminando reporte ID: $repId ($repNombre)");
                
                $deleteReporte = $this->db->prepare("DELETE FROM repor WHERE id = ?");
                $deleteReporte->bind_param('i', $repId);
                if (!$deleteReporte->execute()) {
                    error_log("SaliVehi::eliminarSalida - ERROR al eliminar reporte $repId: " . $this->db->error);
                    throw new Exception("Error al eliminar el reporte $repId: " . $this->db->error);
                } else {
                    $reporteRowsAffected = $deleteReporte->affected_rows;
                    if ($reporteRowsAffected > 0) {
                        $operaciones++;
                        error_log("SaliVehi::eliminarSalida - Reporte $repId eliminado exitosamente");
                    }
                }
            }
            
            // También eliminar el reporte referenciado por repor_id si es diferente
            if (!empty($reporteId)) {
                $yaEliminado = false;
                foreach ($reportesEncontrados as $reporte) {
                    if ($reporte['id'] == $reporteId) {
                        $yaEliminado = true;
                        break;
                    }
                }
                
                if (!$yaEliminado) {
                    error_log("SaliVehi::eliminarSalida - Eliminando reporte adicional ID: $reporteId");
                    $deleteReporte = $this->db->prepare("DELETE FROM repor WHERE id = ?");
                    $deleteReporte->bind_param('i', $reporteId);
                    if ($deleteReporte->execute() && $deleteReporte->affected_rows > 0) {
                        $operaciones++;
                        error_log("SaliVehi::eliminarSalida - Reporte adicional $reporteId eliminado");
                    }
                }
            }
            
            // PASO 4: Eliminar la salida de vehículo
            error_log("SaliVehi::eliminarSalida - Eliminando salida de vehículo ID: $id");
            $deleteSalida = $this->db->prepare("DELETE FROM sali_vehi WHERE id = ?");
            $deleteSalida->bind_param('i', $id);
            
            if (!$deleteSalida->execute()) {
                error_log("SaliVehi::eliminarSalida - ERROR al eliminar salida: " . $this->db->error);
                throw new Exception("Error al eliminar la salida de vehículo: " . $this->db->error);
            }
            
            $rowsAffected = $deleteSalida->affected_rows;
            error_log("SaliVehi::eliminarSalida - Filas afectadas: $rowsAffected");
            
            if ($rowsAffected === 0) {
                error_log("SaliVehi::eliminarSalida - ERROR: No se eliminó ninguna fila");
                throw new Exception("No se pudo eliminar la salida de vehículo");
            }
            
            $operaciones++;
            
            // Confirmar transacción
            if (!$this->db->commit()) {
                error_log("SaliVehi::eliminarSalida - ERROR en commit final: " . $this->db->error);
                throw new Exception("Error al confirmar eliminación: " . $this->db->error);
            }
            
            // Restaurar autocommit
            $this->db->autocommit(true);
            
            error_log("SaliVehi::eliminarSalida - ÉXITO: Eliminación completada. Total operaciones: $operaciones");
            return true;
            
        } catch (Exception $e) {
            error_log("SaliVehi::eliminarSalida - EXCEPCIÓN: " . $e->getMessage());
            
            // Revertir transacción
            $this->db->rollback();
            
            // Restaurar autocommit
            $this->db->autocommit(true);
            
            throw $e;
        }
    }
}
