<?php
class OrdTrabj {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    public function getAll() {
        $result = $this->db->query("SELECT * FROM ord_trabj");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    // Otros métodos según necesidad
}
