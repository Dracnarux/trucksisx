<?php
class Subcategoriarepue {
    private $conn;
    public function __construct($db) {
        $this->conn = $db;
    }
    // Crear nueva subcategoría
    public function crearSubcategoria($nombre, $categoria_id) {
    $sql = "INSERT INTO subcat_repu (nombre, cat_repu_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nombre, $categoria_id]);
    }
    // Modificar subcategoría
    public function modificarSubcategoria($id, $nombre, $categoria_id) {
    $sql = "UPDATE subcat_repu SET nombre = ?, cat_repu_id = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nombre, $categoria_id, $id]);
    }
    // Consultar todas las subcategorías con nombre de categoría
    public function consultarSubcategorias() {
    $sql = "SELECT s.*, c.nombre AS categoria_nombre FROM subcat_repu s JOIN cat_repu c ON s.cat_repu_id = c.id";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Eliminar subcategoría
    public function eliminarSubcategoria($id) {
    $sql = "DELETE FROM subcat_repu WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>
