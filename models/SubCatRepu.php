<?php
require_once __DIR__ . '/../config/db.php';

class SubCatRepu {
    private $conn;

    public function __construct() {
        $this->conn = conectarDB();
    }

    public function getAll($filtro_nombre = '', $filtro_caracteristicas = '', $filtro_categoria = '', $filtro_tipo = '') {
        $sql = "SELECT s.*, c.nombre AS categoria_nombre FROM subcat_repu s LEFT JOIN cat_repu c ON s.cat_repu_id = c.id WHERE 1";
        $params = [];
        $types = '';
        if ($filtro_nombre) {
            $sql .= " AND s.nombre LIKE ?";
            $params[] = "%$filtro_nombre%";
            $types .= 's';
        }
        if ($filtro_caracteristicas) {
            $sql .= " AND s.caracteristicas LIKE ?";
            $params[] = "%$filtro_caracteristicas%";
            $types .= 's';
        }
        if ($filtro_categoria) {
            $sql .= " AND c.nombre LIKE ?";
            $params[] = "%$filtro_categoria%";
            $types .= 's';
        }
        if ($filtro_tipo) {
            $sql .= " AND s.tipo_sub_repuesto LIKE ?";
            $params[] = "%$filtro_tipo%";
            $types .= 's';
        }
        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM subcat_repu WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function save($tipo_sub_repuesto, $nombre, $caracteristicas, $cat_repu_id, $id = null) {
        if ($id) {
            $stmt = $this->conn->prepare("UPDATE subcat_repu SET tipo_sub_repuesto=?, nombre=?, caracteristicas=?, cat_repu_id=? WHERE id=?");
            $stmt->bind_param('sssii', $tipo_sub_repuesto, $nombre, $caracteristicas, $cat_repu_id, $id);
        } else {
            $stmt = $this->conn->prepare("INSERT INTO subcat_repu (tipo_sub_repuesto, nombre, caracteristicas, cat_repu_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('sssi', $tipo_sub_repuesto, $nombre, $caracteristicas, $cat_repu_id);
        }
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM subcat_repu WHERE id=?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
