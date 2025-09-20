<?php
require_once 'config/db.php';

class User {
    private $db;
    public function __construct() {
        $this->db = conectarDB();
    }
    public function login($usuario, $contrasena) {
        $sql = "SELECT * FROM users WHERE nombre = ? OR correo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ss', $usuario, $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (hash('sha256', $contrasena) === $row['contrasena']) {
                return $row;
            }
        }
        return false;
    }
}
