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
}
