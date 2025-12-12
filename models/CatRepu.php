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
        try {
            // Iniciar transacción
            $this->conn->begin_transaction();
            
            // Verificar si la categoría está siendo usada en repue
            $check_sql = "SELECT COUNT(*) as count FROM repue WHERE cat_repu_id = ?";
            $check_stmt = $this->conn->prepare($check_sql);
            $check_stmt->bind_param('i', $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                // Si hay repuestos que usan esta categoría, desvincular
                $update_sql = "UPDATE repue SET cat_repu_id = NULL WHERE cat_repu_id = ?";
                $update_stmt = $this->conn->prepare($update_sql);
                $update_stmt->bind_param('i', $id);
                if (!$update_stmt->execute()) {
                    throw new Exception("Error al desvincular repuestos de la categoría");
                }
            }
            
            // Verificar si hay subcategorías que referencian esta categoría
            $check_subcat_sql = "SELECT COUNT(*) as count FROM subcat_repu WHERE cat_repu_id = ?";
            $check_subcat_stmt = $this->conn->prepare($check_subcat_sql);
            $check_subcat_stmt->bind_param('i', $id);
            $check_subcat_stmt->execute();
            $subcat_result = $check_subcat_stmt->get_result();
            $subcat_row = $subcat_result->fetch_assoc();
            
            if ($subcat_row['count'] > 0) {
                // Si hay subcategorías, desvincular
                $update_subcat_sql = "UPDATE subcat_repu SET cat_repu_id = NULL WHERE cat_repu_id = ?";
                $update_subcat_stmt = $this->conn->prepare($update_subcat_sql);
                $update_subcat_stmt->bind_param('i', $id);
                if (!$update_subcat_stmt->execute()) {
                    throw new Exception("Error al desvincular subcategorías");
                }
            }
            
            // Ahora eliminar la categoría
            $delete_sql = "DELETE FROM cat_repu WHERE id = ?";
            $delete_stmt = $this->conn->prepare($delete_sql);
            $delete_stmt->bind_param('i', $id);
            
            if (!$delete_stmt->execute()) {
                throw new Exception("Error al eliminar la categoría");
            }
            
            // Confirmar transacción
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            // Revertir transacción
            $this->conn->rollback();
            throw $e;
        }
    }
}
