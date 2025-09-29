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
    // ...otros métodos según necesidad
}
