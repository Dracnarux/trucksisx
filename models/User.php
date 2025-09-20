<?php
require_once __DIR__ . '/../config/db.php';

class User {
    private $db;
    public function __construct() {
        $this->db = $GLOBALS['db'];
    }
    public function login($usuario, $contrasena) {
        $sql = "SELECT * FROM users WHERE nombre = ? OR correo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario, $usuario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            if (hash('sha256', $contrasena) === $row['contrasena']) {
                return $row;
            }
        }
        return false;
    }
}
