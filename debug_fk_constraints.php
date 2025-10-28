<?php
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json');

try {
    $db = conectarDB();
    
    // Buscar todas las salidas que tengan repor_id no nulo
    $query = "SELECT * FROM sali_repue WHERE repor_id IS NOT NULL LIMIT 5";
    $result = $db->query($query);
    
    $salidas_con_reporte = [];
    while ($row = $result->fetch_assoc()) {
        $salidas_con_reporte[] = $row;
    }
    
    // También mostrar todas las salidas para referencia
    $query_all = "SELECT * FROM sali_repue ORDER BY id";
    $result_all = $db->query($query_all);
    
    $todas_salidas = [];
    while ($row = $result_all->fetch_assoc()) {
        $todas_salidas[] = $row;
    }
    
    // Verificar las FK constraints de la tabla sali_repue
    $constraints_query = "
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = 'trucksisx' 
        AND TABLE_NAME = 'sali_repue' 
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ";
    
    $constraints_result = $db->query($constraints_query);
    $constraints = [];
    while ($row = $constraints_result->fetch_assoc()) {
        $constraints[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'salidas_con_reporte' => $salidas_con_reporte,
        'total_salidas' => count($todas_salidas),
        'todas_salidas' => $todas_salidas,
        'fk_constraints' => $constraints,
        'mensaje' => 'Datos obtenidos correctamente'
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>