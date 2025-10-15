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
}
