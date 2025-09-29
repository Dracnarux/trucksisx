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
    // ...otros métodos según necesidad
}
