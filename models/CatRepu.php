<?php
require_once __DIR__ . '/../config/db.php';

class CatRepu {
    private $conn;

    public function __construct() {
        $this->conn = conectarDB();
    }

    public function getAll($filtro = '') {
        $sql = "SELECT * FROM cat_repu";
        if ($filtro) {
            $sql .= " WHERE nombre LIKE ? OR caracteristicas LIKE ?";
            $stmt = $this->conn->prepare($sql);
            $like = "%$filtro%";
            $stmt->bind_param('ss', $like, $like);
        } else {
            $stmt = $this->conn->prepare($sql);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM cat_repu WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function save($nombre, $caracteristicas, $id = null) {
        if ($id) {
            $stmt = $this->conn->prepare("UPDATE cat_repu SET nombre=?, caracteristicas=? WHERE id=?");
            $stmt->bind_param('ssi', $nombre, $caracteristicas, $id);
        } else {
            $stmt = $this->conn->prepare("INSERT INTO cat_repu (nombre, caracteristicas) VALUES (?, ?)");
            $stmt->bind_param('ss', $nombre, $caracteristicas);
        }
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM cat_repu WHERE id=?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
