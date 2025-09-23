<?php
class SubCatVehiculo {
    private $conn;
    public function __construct($db) {
        $this->conn = $db;
    }
    public function getAll($cat_vehic_id = null) {
        $sql = "SELECT s.*, c.nombre AS categoria FROM subcat_vehic s JOIN cat_vehic c ON s.cat_vehic_id = c.id";
        if ($cat_vehic_id) {
            $sql .= " WHERE s.cat_vehic_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('i', $cat_vehic_id);
        } else {
            $stmt = $this->conn->prepare($sql);
        }
        $stmt->execute();
        return $stmt->get_result();
    }
    public function getById($id) {
        $sql = "SELECT * FROM subcat_vehic WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function create($nombre, $cat_vehic_id) {
        $sql = "INSERT INTO subcat_vehic (nombre, cat_vehic_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $nombre, $cat_vehic_id);
        return $stmt->execute();
    }
    public function update($id, $nombre, $cat_vehic_id) {
        $sql = "UPDATE subcat_vehic SET nombre = ?, cat_vehic_id = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sii', $nombre, $cat_vehic_id, $id);
        return $stmt->execute();
    }
    public function delete($id) {
        $sql = "DELETE FROM subcat_vehic WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
