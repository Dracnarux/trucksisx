<?php
class Categoriarepue {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear nueva categoría
    public function crearCategoria($nombre) {
    $sql = "INSERT INTO cat_repu (nombre) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nombre]);
    }

    // Modificar categoría existente
    public function modificarCategoria($id, $nuevoNombre) {
    $sql = "UPDATE cat_repu SET nombre = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nuevoNombre, $id]);
    }

    // Consultar todas las categorías
    public function consultarCategorias() {
    $sql = "SELECT * FROM cat_repu";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Eliminar categoría
    public function eliminarCategoria($id) {
    $sql = "DELETE FROM cat_repu WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>
