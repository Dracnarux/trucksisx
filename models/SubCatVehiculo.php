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
        try {
            // Iniciar transacción
            $this->conn->begin_transaction();
            
            // Verificar si la subcategoría está siendo usada en regis_vehic
            $check_sql = "SELECT COUNT(*) as count FROM regis_vehic WHERE subcat_vehic_id = ?";
            $check_stmt = $this->conn->prepare($check_sql);
            $check_stmt->bind_param('i', $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                // Si hay vehículos que usan esta subcategoría, desvincular
                $update_sql = "UPDATE regis_vehic SET subcat_vehic_id = NULL WHERE subcat_vehic_id = ?";
                $update_stmt = $this->conn->prepare($update_sql);
                $update_stmt->bind_param('i', $id);
                if (!$update_stmt->execute()) {
                    throw new Exception("Error al desvincular vehículos de la subcategoría");
                }
            }
            
            // Ahora eliminar la subcategoría
            $delete_sql = "DELETE FROM subcat_vehic WHERE id = ?";
            $delete_stmt = $this->conn->prepare($delete_sql);
            $delete_stmt->bind_param('i', $id);
            
            if (!$delete_stmt->execute()) {
                throw new Exception("Error al eliminar la subcategoría de vehículo");
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
