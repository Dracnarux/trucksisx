<?php
class Repor {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    public function getAll() {
        $result = $this->db->query("SELECT * FROM repor");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    // Otros métodos según necesidad
}
