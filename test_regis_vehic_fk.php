<?php
// Test FK constraint fix for regis_vehic deletion
require_once './config/db.php';
$conn = conectarDB();

// Test vehicle ID to delete (should have alerts and conductors)
$vehiculo_id = 17;

try {
    // Iniciar transacción para manejo de FK constraints
    $conn->autocommit(false);
    $conn->begin_transaction();
    
    echo "Probando eliminación del vehículo ID: $vehiculo_id\n";
    
    // PASO 1: Obtener IDs de alertas para verificar órdenes de trabajo y salidas
    $get_alerts_sql = "SELECT id FROM alert WHERE regis_vehic_id = ?";
    $get_alerts_stmt = $conn->prepare($get_alerts_sql);
    $get_alerts_stmt->bind_param('i', $vehiculo_id);
    $get_alerts_stmt->execute();
    $alerts_result = $get_alerts_stmt->get_result();
    
    $alert_ids = [];
    while ($alert_row = $alerts_result->fetch_assoc()) {
        $alert_ids[] = $alert_row['id'];
    }
    $alertas_count = count($alert_ids);
    
    echo "Encontradas $alertas_count alertas: " . implode(", ", $alert_ids) . "\n";
    
    // PASO 1A: Eliminar órdenes de trabajo que referencian estas alertas
    $total_ordenes_eliminadas = 0;
    foreach ($alert_ids as $alert_id) {
        $check_ord_sql = "SELECT COUNT(*) as count FROM ord_trabj WHERE alert_id = ?";
        $check_ord_stmt = $conn->prepare($check_ord_sql);
        $check_ord_stmt->bind_param('i', $alert_id);
        $check_ord_stmt->execute();
        $ord_result = $check_ord_stmt->get_result();
        $ord_row = $ord_result->fetch_assoc();
        $ordenes_count = $ord_row['count'];
        
        if ($ordenes_count > 0) {
            echo "Alerta $alert_id tiene $ordenes_count órdenes de trabajo\n";
            $delete_ord_sql = "DELETE FROM ord_trabj WHERE alert_id = ?";
            $delete_ord_stmt = $conn->prepare($delete_ord_sql);
            $delete_ord_stmt->bind_param('i', $alert_id);
            $delete_ord_stmt->execute();
            
            $total_ordenes_eliminadas += $ordenes_count;
            echo "Eliminadas $ordenes_count órdenes de trabajo de alerta $alert_id\n";
        }
    }
    
    // PASO 1B: Eliminar salidas de repuestos que referencian estas alertas
    $total_sali_repue_eliminadas = 0;
    foreach ($alert_ids as $alert_id) {
        $check_sali_repue_sql = "SELECT COUNT(*) as count FROM sali_repue WHERE alerta_id = ?";
        $check_sali_repue_stmt = $conn->prepare($check_sali_repue_sql);
        $check_sali_repue_stmt->bind_param('i', $alert_id);
        $check_sali_repue_stmt->execute();
        $sali_repue_result = $check_sali_repue_stmt->get_result();
        $sali_repue_row = $sali_repue_result->fetch_assoc();
        $sali_repue_count = $sali_repue_row['count'];
        
        if ($sali_repue_count > 0) {
            echo "Alerta $alert_id tiene $sali_repue_count salidas de repuestos\n";
            $delete_sali_repue_sql = "DELETE FROM sali_repue WHERE alerta_id = ?";
            $delete_sali_repue_stmt = $conn->prepare($delete_sali_repue_sql);
            $delete_sali_repue_stmt->bind_param('i', $alert_id);
            $delete_sali_repue_stmt->execute();
            
            $total_sali_repue_eliminadas += $sali_repue_count;
            echo "Eliminadas $sali_repue_count salidas de repuestos de alerta $alert_id\n";
        }
    }
    
    // PASO 1C: Eliminar salidas de vehículos que referencian estas alertas
    $total_sali_vehi_eliminadas = 0;
    foreach ($alert_ids as $alert_id) {
        $check_sali_vehi_sql = "SELECT COUNT(*) as count FROM sali_vehi WHERE alerta_id = ?";
        $check_sali_vehi_stmt = $conn->prepare($check_sali_vehi_sql);
        $check_sali_vehi_stmt->bind_param('i', $alert_id);
        $check_sali_vehi_stmt->execute();
        $sali_vehi_result = $check_sali_vehi_stmt->get_result();
        $sali_vehi_row = $sali_vehi_result->fetch_assoc();
        $sali_vehi_count = $sali_vehi_row['count'];
        
        if ($sali_vehi_count > 0) {
            echo "Alerta $alert_id tiene $sali_vehi_count salidas de vehículos\n";
            $delete_sali_vehi_sql = "DELETE FROM sali_vehi WHERE alerta_id = ?";
            $delete_sali_vehi_stmt = $conn->prepare($delete_sali_vehi_sql);
            $delete_sali_vehi_stmt->bind_param('i', $alert_id);
            $delete_sali_vehi_stmt->execute();
            
            $total_sali_vehi_eliminadas += $sali_vehi_count;
            echo "Eliminadas $sali_vehi_count salidas de vehículos de alerta $alert_id\n";
        }
    }
    
    // PASO 1D: Ahora eliminar las alertas (ya sin FK constraints)
    if ($alertas_count > 0) {
        $delete_alerts_sql = "DELETE FROM alert WHERE regis_vehic_id = ?";
        $delete_alerts_stmt = $conn->prepare($delete_alerts_sql);
        $delete_alerts_stmt->bind_param('i', $vehiculo_id);
        $delete_alerts_stmt->execute();
        
        echo "Eliminadas $alertas_count alertas del vehículo $vehiculo_id\n";
    }
    
    // PASO 2: Verificar y eliminar conductores asociados a este vehículo
    $check_cond_sql = "SELECT COUNT(*) as count FROM cond WHERE regis_vehic_id = ?";
    $check_cond_stmt = $conn->prepare($check_cond_sql);
    $check_cond_stmt->bind_param('i', $vehiculo_id);
    $check_cond_stmt->execute();
    $cond_result = $check_cond_stmt->get_result();
    $cond_row = $cond_result->fetch_assoc();
    $conductores_count = $cond_row['count'];
    
    echo "Encontrados $conductores_count conductores\n";
    
    if ($conductores_count > 0) {
        $delete_cond_sql = "DELETE FROM cond WHERE regis_vehic_id = ?";
        $delete_cond_stmt = $conn->prepare($delete_cond_sql);
        $delete_cond_stmt->bind_param('i', $vehiculo_id);
        $delete_cond_stmt->execute();
        
        echo "Eliminados $conductores_count conductores del vehículo $vehiculo_id\n";
    }
    
    // PASO 3: Ahora eliminar el vehículo (ya sin FK constraints)
    echo "Eliminando vehículo...\n";
    $sql = "DELETE FROM regis_vehic WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $vehiculo_id);
    $stmt->execute();
    
    // Confirmar transacción
    $conn->commit();
    $conn->autocommit(true);
    
    echo "ÉXITO: Vehículo $vehiculo_id eliminado exitosamente con todas sus dependencias!\n";
    
    // Mensaje informativo sobre lo que se eliminó
    $mensaje = "Vehículo eliminado correctamente.";
    $eliminations = [];
    
    if (isset($total_ordenes_eliminadas) && $total_ordenes_eliminadas > 0) {
        $eliminations[] = "$total_ordenes_eliminadas órdenes de trabajo eliminadas";
    }
    if (isset($total_sali_repue_eliminadas) && $total_sali_repue_eliminadas > 0) {
        $eliminations[] = "$total_sali_repue_eliminadas salidas de repuestos eliminadas";
    }
    if (isset($total_sali_vehi_eliminadas) && $total_sali_vehi_eliminadas > 0) {
        $eliminations[] = "$total_sali_vehi_eliminadas salidas de vehículos eliminadas";
    }
    if ($alertas_count > 0) {
        $eliminations[] = "$alertas_count alertas eliminadas";
    }
    if ($conductores_count > 0) {
        $eliminations[] = "$conductores_count conductores eliminados";
    }
    
    if (count($eliminations) > 0) {
        $mensaje .= "\n- " . implode("\n- ", $eliminations);
    }
    
    echo "\nResumen de eliminación:\n$mensaje\n";
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    $conn->autocommit(true);
    
    echo "ERROR: " . $e->getMessage() . "\n";
}

$conn->close();
?>