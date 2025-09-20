<?php
class Repuestos {
    private $conn;
    public function __construct($db) {
        $this->conn = $db;
    }
    // Crear nuevo repuesto
    public function crearRepuesto($nombre, $precio, $subcat_repu_id) {
        $sql = "INSERT INTO repue (nombre, precio, subcat_repu_id) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nombre, $precio, $subcat_repu_id]);
    }
    // Modificar repuesto
    public function modificarRepuesto($id, $nombre, $precio, $subcat_repu_id) {
        $sql = "UPDATE repue SET nombre = ?, precio = ?, subcat_repu_id = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nombre, $precio, $subcat_repu_id, $id]);
    }
    // Consultar todos los repuestos con nombre de subcategoría
    public function consultarRepuestos() {
    $sql = "SELECT r.*, s.nombre AS subcategoria_nombre FROM repue r JOIN subcat_repu s ON r.subcat_repu_id = s.id";
    $stmt = $this->conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Eliminar repuesto
    public function eliminarRepuesto($id) {
    $sql = "DELETE FROM repue WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>
