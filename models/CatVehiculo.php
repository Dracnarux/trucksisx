<?php
class CatVehiculo {
    private $conn;
    public function __construct($db) {
        $this->conn = $db;
    }
    public function getAll() {
        $sql = "SELECT * FROM cat_vehic ORDER BY id DESC";
        $result = $this->conn->query($sql);
        return $result;
    }
    public function getById($id) {
        $sql = "SELECT * FROM cat_vehic WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function create($nombre) {
        $sql = "INSERT INTO cat_vehic (nombre) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $nombre);
        return $stmt->execute();
    }
    public function update($id, $nombre) {
        $sql = "UPDATE cat_vehic SET nombre = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $nombre, $id);
        return $stmt->execute();
    }
    public function delete($id) {
        $sql = "DELETE FROM cat_vehic WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
