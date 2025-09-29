
<?php

// Clase Database para PDO
class Database {
    private $host = 'localhost';
    private $db_name = 'trucksisx';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            die("Error de conexión PDO: " . $exception->getMessage());
        }
        return $this->conn;
    }
}

// Función mysqli para compatibilidad
function conectarDB() {
    $host = 'localhost';
    $db = 'trucksisx';
    $user = 'root';
    $pass = '';
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die('Error de conexión: ' . $conn->connect_error);
    }
    return $conn;
}
