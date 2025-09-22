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
            $sql .= " WHERE nombre LIKE ? OR caracteristicas LIKE ? OR tipo_repuesto LIKE ?";
            $stmt = $this->conn->prepare($sql);
            $like = "%$filtro%";
            $stmt->bind_param('sss', $like, $like, $like);
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

    public function save($tipo_repuesto, $nombre, $caracteristicas, $id = null) {
        if ($id) {
            $stmt = $this->conn->prepare("UPDATE cat_repu SET tipo_repuesto=?, nombre=?, caracteristicas=? WHERE id=?");
            $stmt->bind_param('sssi', $tipo_repuesto, $nombre, $caracteristicas, $id);
        } else {
            $stmt = $this->conn->prepare("INSERT INTO cat_repu (tipo_repuesto, nombre, caracteristicas) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $tipo_repuesto, $nombre, $caracteristicas);
        }
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM cat_repu WHERE id=?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
