<?php
class CatVehiculo {
    private $conn;
    public function __construct($db) {
        $this->conn = $db;
    }
    public function getAll($nombre = '') {
        $sql = "SELECT * FROM cat_vehic";
        if (!empty($nombre)) {
            $sql .= " WHERE nombre LIKE '%" . $this->conn->real_escape_string($nombre) . "%'";
        }
        $sql .= " ORDER BY id DESC";
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
        try {
            // Iniciar transacción
            $this->conn->begin_transaction();
            
            // Verificar si la categoría está siendo usada en regis_vehic
            $check_sql = "SELECT COUNT(*) as count FROM regis_vehic WHERE cat_vehic_id = ?";
            $check_stmt = $this->conn->prepare($check_sql);
            $check_stmt->bind_param('i', $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                // Si hay vehículos que usan esta categoría, desvincular
                $update_sql = "UPDATE regis_vehic SET cat_vehic_id = NULL WHERE cat_vehic_id = ?";
                $update_stmt = $this->conn->prepare($update_sql);
                $update_stmt->bind_param('i', $id);
                if (!$update_stmt->execute()) {
                    throw new Exception("Error al desvincular vehículos de la categoría");
                }
            }
            
            // Verificar si hay subcategorías que referencian esta categoría
            $check_subcat_sql = "SELECT COUNT(*) as count FROM subcat_vehic WHERE cat_vehic_id = ?";
            $check_subcat_stmt = $this->conn->prepare($check_subcat_sql);
            $check_subcat_stmt->bind_param('i', $id);
            $check_subcat_stmt->execute();
            $subcat_result = $check_subcat_stmt->get_result();
            $subcat_row = $subcat_result->fetch_assoc();
            
            if ($subcat_row['count'] > 0) {
                // Si hay subcategorías, desvincular
                $update_subcat_sql = "UPDATE subcat_vehic SET cat_vehic_id = NULL WHERE cat_vehic_id = ?";
                $update_subcat_stmt = $this->conn->prepare($update_subcat_sql);
                $update_subcat_stmt->bind_param('i', $id);
                if (!$update_subcat_stmt->execute()) {
                    throw new Exception("Error al desvincular subcategorías");
                }
            }
            
            // Ahora eliminar la categoría
            $delete_sql = "DELETE FROM cat_vehic WHERE id = ?";
            $delete_stmt = $this->conn->prepare($delete_sql);
            $delete_stmt->bind_param('i', $id);
            
            if (!$delete_stmt->execute()) {
                throw new Exception("Error al eliminar la categoría de vehículo");
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
