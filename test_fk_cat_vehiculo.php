<?php
// Test FK constraint fix for cat_vehiculo deletion
require_once './config/db.php';
$conn = conectarDB();

// Test category ID to delete
$categoria_id = 12;

try {
    // Iniciar transacción
    $conn->autocommit(false);
    $conn->begin_transaction();
    
    echo "Testing deletion of category ID: $categoria_id\n";
    
    // Verificar si hay subcategorías asociadas
    $check_sql = "SELECT COUNT(*) as count FROM subcat_vehic WHERE cat_vehic_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('i', $categoria_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();
    $subcategorias_count = $row['count'];
    
    echo "Found $subcategorias_count subcategories\n";
    
    if ($subcategorias_count > 0) {
        // Eliminar primero las subcategorías asociadas
        echo "Deleting subcategories first...\n";
        $delete_subcat_sql = "DELETE FROM subcat_vehic WHERE cat_vehic_id = ?";
        $delete_subcat_stmt = $conn->prepare($delete_subcat_sql);
        $delete_subcat_stmt->bind_param('i', $categoria_id);
        $delete_subcat_stmt->execute();
        
        echo "Deleted $subcategorias_count subcategories\n";
    }
    
    // Ahora eliminar la categoría
    echo "Deleting category...\n";
    $sql = "DELETE FROM cat_vehic WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $categoria_id);
    $stmt->execute();
    
    // Confirmar transacción
    $conn->commit();
    $conn->autocommit(true);
    
    echo "SUCCESS: Category $categoria_id deleted successfully\n";
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    $conn->autocommit(true);
    
    echo "ERROR: " . $e->getMessage() . "\n";
}

$conn->close();
?>