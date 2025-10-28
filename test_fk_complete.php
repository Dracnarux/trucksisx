<?php
// Test complete 3-level FK constraint fix for cat_vehiculo deletion
require_once './config/db.php';
$conn = conectarDB();

// Test category ID to delete (should have subcategories and vehicle registrations)
$categoria_id = 14;

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
        // PASO 1: Obtener IDs de subcategorías para verificar registros de vehículos
        $get_subcat_sql = "SELECT id FROM subcat_vehic WHERE cat_vehic_id = ?";
        $get_subcat_stmt = $conn->prepare($get_subcat_sql);
        $get_subcat_stmt->bind_param('i', $categoria_id);
        $get_subcat_stmt->execute();
        $subcat_result = $get_subcat_stmt->get_result();
        
        $subcat_ids = [];
        while ($subcat_row = $subcat_result->fetch_assoc()) {
            $subcat_ids[] = $subcat_row['id'];
        }
        
        echo "Subcategory IDs found: " . implode(", ", $subcat_ids) . "\n";
        
        // PASO 2: Eliminar registros de vehículos que referencian estas subcategorías
        $total_registros_eliminados = 0;
        foreach ($subcat_ids as $subcat_id) {
            // Verificar cuántos registros hay para esta subcategoría
            $check_regis_sql = "SELECT COUNT(*) as count FROM regis_vehic WHERE subcat_vehic_id = ?";
            $check_regis_stmt = $conn->prepare($check_regis_sql);
            $check_regis_stmt->bind_param('i', $subcat_id);
            $check_regis_stmt->execute();
            $regis_result = $check_regis_stmt->get_result();
            $regis_row = $regis_result->fetch_assoc();
            $registros_count = $regis_row['count'];
            
            echo "Subcategory $subcat_id has $registros_count vehicle registrations\n";
            
            if ($registros_count > 0) {
                // Eliminar registros de vehículos de esta subcategoría
                $delete_regis_sql = "DELETE FROM regis_vehic WHERE subcat_vehic_id = ?";
                $delete_regis_stmt = $conn->prepare($delete_regis_sql);
                $delete_regis_stmt->bind_param('i', $subcat_id);
                $delete_regis_stmt->execute();
                
                $total_registros_eliminados += $registros_count;
                echo "Deleted $registros_count vehicle registrations from subcategory $subcat_id\n";
            }
        }
        
        if ($total_registros_eliminados > 0) {
            echo "Total vehicle registrations deleted: $total_registros_eliminados\n";
        }
        
        // PASO 3: Ahora eliminar las subcategorías (ya sin FK constraints de regis_vehic)
        $delete_subcat_sql = "DELETE FROM subcat_vehic WHERE cat_vehic_id = ?";
        $delete_subcat_stmt = $conn->prepare($delete_subcat_sql);
        $delete_subcat_stmt->bind_param('i', $categoria_id);
        $delete_subcat_stmt->execute();
        
        echo "Deleted $subcategorias_count subcategories\n";
    }
    
    // PASO 4: Ahora eliminar la categoría
    echo "Deleting category...\n";
    $sql = "DELETE FROM cat_vehic WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $categoria_id);
    $stmt->execute();
    
    // Confirmar transacción
    $conn->commit();
    $conn->autocommit(true);
    
    echo "SUCCESS: Category $categoria_id deleted successfully with all dependencies!\n";
    
    // Mensaje informativo sobre lo que se eliminó
    $mensaje = "Category deleted correctly.";
    if ($subcategorias_count > 0) {
        $mensaje .= "\n- $subcategorias_count subcategories deleted";
        if (isset($total_registros_eliminados) && $total_registros_eliminados > 0) {
            $mensaje .= "\n- $total_registros_eliminados vehicle registrations deleted";
        }
    }
    
    echo "\nDeletion summary:\n$mensaje\n";
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    $conn->autocommit(true);
    
    echo "ERROR: " . $e->getMessage() . "\n";
}

$conn->close();
?>