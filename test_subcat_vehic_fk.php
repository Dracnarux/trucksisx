<?php
// Test FK constraint fix for subcat_vehiculo deletion
require_once './config/db.php';
$conn = conectarDB();

// Test subcategory ID to delete (should have vehicles with all dependencies)
$subcat_id = 25;

try {
    // Iniciar transacción para manejo de FK constraints
    $conn->autocommit(false);
    $conn->begin_transaction();
    
    echo "Probando eliminación de subcategoría ID: $subcat_id\n";
    
    // PASO 1: Obtener IDs de vehículos que pertenecen a esta subcategoría
    $get_vehiculos_sql = "SELECT id FROM regis_vehic WHERE subcat_vehic_id = ?";
    $get_vehiculos_stmt = $conn->prepare($get_vehiculos_sql);
    $get_vehiculos_stmt->bind_param('i', $subcat_id);
    $get_vehiculos_stmt->execute();
    $vehiculos_result = $get_vehiculos_stmt->get_result();
    
    $vehiculo_ids = [];
    while ($vehiculo_row = $vehiculos_result->fetch_assoc()) {
        $vehiculo_ids[] = $vehiculo_row['id'];
    }
    $vehiculos_count = count($vehiculo_ids);
    
    echo "Encontrados $vehiculos_count vehículos: " . implode(", ", $vehiculo_ids) . "\n";
    
    $total_ordenes_eliminadas = 0;
    $total_sali_repue_eliminadas = 0;
    $total_sali_vehi_eliminadas = 0;
    $total_alertas_eliminadas = 0;
    $total_conductores_eliminados = 0;
    
    // PASO 2: Para cada vehículo, eliminar todas sus dependencias
    foreach ($vehiculo_ids as $vehiculo_id) {
        
        echo "\n-- Procesando vehículo $vehiculo_id --\n";
        
        // PASO 2A: Obtener alertas de este vehículo
        $get_alerts_sql = "SELECT id FROM alert WHERE regis_vehic_id = ?";
        $get_alerts_stmt = $conn->prepare($get_alerts_sql);
        $get_alerts_stmt->bind_param('i', $vehiculo_id);
        $get_alerts_stmt->execute();
        $alerts_result = $get_alerts_stmt->get_result();
        
        $alert_ids = [];
        while ($alert_row = $alerts_result->fetch_assoc()) {
            $alert_ids[] = $alert_row['id'];
        }
        
        echo "Alertas del vehículo $vehiculo_id: " . implode(", ", $alert_ids) . "\n";
        
        // PASO 2B: Primero limpiar referencias circulares en alert
        echo "Limpiando referencias circulares en alertas...\n";
        foreach ($alert_ids as $alert_id) {
            $clear_ord_ref_sql = "UPDATE alert SET ord_trabj_id = NULL WHERE id = ?";
            $clear_ord_ref_stmt = $conn->prepare($clear_ord_ref_sql);
            $clear_ord_ref_stmt->bind_param('i', $alert_id);
            $clear_ord_ref_stmt->execute();
            if ($clear_ord_ref_stmt->affected_rows > 0) {
                echo "Limpiada referencia circular en alerta $alert_id\n";
            }
        }
        
        // PASO 2C: Primero obtener IDs de órdenes de trabajo para limpiar referencias en sali_repue
        $orden_ids = [];
        foreach ($alert_ids as $alert_id) {
            $get_ordenes_sql = "SELECT id FROM ord_trabj WHERE alert_id = ?";
            $get_ordenes_stmt = $conn->prepare($get_ordenes_sql);
            $get_ordenes_stmt->bind_param('i', $alert_id);
            $get_ordenes_stmt->execute();
            $ordenes_result = $get_ordenes_stmt->get_result();
            
            while ($orden_row = $ordenes_result->fetch_assoc()) {
                $orden_ids[] = $orden_row['id'];
            }
        }
        
        if (count($orden_ids) > 0) {
            echo "Órdenes de trabajo encontradas: " . implode(", ", $orden_ids) . "\n";
            
            // PASO 2D: Limpiar referencias de sali_repue a órdenes de trabajo
            foreach ($orden_ids as $orden_id) {
                $clear_sali_repue_ord_sql = "UPDATE sali_repue SET ord_trabj_id = NULL WHERE ord_trabj_id = ?";
                $clear_sali_repue_ord_stmt = $conn->prepare($clear_sali_repue_ord_sql);
                $clear_sali_repue_ord_stmt->bind_param('i', $orden_id);
                $clear_sali_repue_ord_stmt->execute();
                if ($clear_sali_repue_ord_stmt->affected_rows > 0) {
                    echo "Limpiada referencia sali_repue->ord_trabj para orden $orden_id\n";
                }
            }
        }
        
        // PASO 2E: Obtener IDs de sali_repue para limpiar sus dependencias
        $sali_repue_ids = [];
        foreach ($alert_ids as $alert_id) {
            $get_sali_repue_sql = "SELECT id FROM sali_repue WHERE alerta_id = ?";
            $get_sali_repue_stmt = $conn->prepare($get_sali_repue_sql);
            $get_sali_repue_stmt->bind_param('i', $alert_id);
            $get_sali_repue_stmt->execute();
            $sali_repue_result = $get_sali_repue_stmt->get_result();
            
            while ($sali_repue_row = $sali_repue_result->fetch_assoc()) {
                $sali_repue_ids[] = $sali_repue_row['id'];
            }
        }
        
        if (count($sali_repue_ids) > 0) {
            echo "Salidas de repuestos encontradas: " . implode(", ", $sali_repue_ids) . "\n";
            
            // PASO 2F: Eliminar reportes que referencian sali_repue
            foreach ($sali_repue_ids as $sali_repue_id) {
                $delete_repor_sql = "DELETE FROM repor WHERE sali_repue_id = ?";
                $delete_repor_stmt = $conn->prepare($delete_repor_sql);
                $delete_repor_stmt->bind_param('i', $sali_repue_id);
                $delete_repor_stmt->execute();
                if ($delete_repor_stmt->affected_rows > 0) {
                    echo "Eliminados reportes que referencian sali_repue $sali_repue_id\n";
                }
            }
            
            // PASO 2G: Limpiar referencias de sali_vehi a sali_repue
            foreach ($sali_repue_ids as $sali_repue_id) {
                $clear_sali_vehi_ref_sql = "UPDATE sali_vehi SET sali_repue_id = NULL WHERE sali_repue_id = ?";
                $clear_sali_vehi_ref_stmt = $conn->prepare($clear_sali_vehi_ref_sql);
                $clear_sali_vehi_ref_stmt->bind_param('i', $sali_repue_id);
                $clear_sali_vehi_ref_stmt->execute();
                if ($clear_sali_vehi_ref_stmt->affected_rows > 0) {
                    echo "Limpiada referencia sali_vehi->sali_repue para $sali_repue_id\n";
                }
            }
        }
        
        // PASO 2H: Ahora eliminar salidas que referencian las alertas
        foreach ($alert_ids as $alert_id) {
            $delete_sali_repue_sql = "DELETE FROM sali_repue WHERE alerta_id = ?";
            $delete_sali_repue_stmt = $conn->prepare($delete_sali_repue_sql);
            $delete_sali_repue_stmt->bind_param('i', $alert_id);
            $delete_sali_repue_stmt->execute();
            $sali_repue_eliminadas = $delete_sali_repue_stmt->affected_rows;
            $total_sali_repue_eliminadas += $sali_repue_eliminadas;
            if ($sali_repue_eliminadas > 0) {
                echo "Eliminadas $sali_repue_eliminadas salidas de repuestos de alerta $alert_id\n";
            }
            
            $delete_sali_vehi_sql = "DELETE FROM sali_vehi WHERE alerta_id = ?";
            $delete_sali_vehi_stmt = $conn->prepare($delete_sali_vehi_sql);
            $delete_sali_vehi_stmt->bind_param('i', $alert_id);
            $delete_sali_vehi_stmt->execute();
            $sali_vehi_eliminadas = $delete_sali_vehi_stmt->affected_rows;
            $total_sali_vehi_eliminadas += $sali_vehi_eliminadas;
            if ($sali_vehi_eliminadas > 0) {
                echo "Eliminadas $sali_vehi_eliminadas salidas de vehículos de alerta $alert_id\n";
            }
        }
        
        // PASO 2J: Ahora eliminar órdenes de trabajo (ya sin referencias de sali_repue)
        foreach ($alert_ids as $alert_id) {
            $delete_ord_sql = "DELETE FROM ord_trabj WHERE alert_id = ?";
            $delete_ord_stmt = $conn->prepare($delete_ord_sql);
            $delete_ord_stmt->bind_param('i', $alert_id);
            $delete_ord_stmt->execute();
            $ordenes_eliminadas = $delete_ord_stmt->affected_rows;
            $total_ordenes_eliminadas += $ordenes_eliminadas;
            if ($ordenes_eliminadas > 0) {
                echo "Eliminadas $ordenes_eliminadas órdenes de trabajo de alerta $alert_id\n";
            }
        }
        
        // PASO 2K: Eliminar alertas de este vehículo (ahora sin referencias circulares)
        if (count($alert_ids) > 0) {
            $delete_alerts_sql = "DELETE FROM alert WHERE regis_vehic_id = ?";
            $delete_alerts_stmt = $conn->prepare($delete_alerts_sql);
            $delete_alerts_stmt->bind_param('i', $vehiculo_id);
            $delete_alerts_stmt->execute();
            $alertas_eliminadas = $delete_alerts_stmt->affected_rows;
            $total_alertas_eliminadas += $alertas_eliminadas;
            echo "Eliminadas $alertas_eliminadas alertas del vehículo $vehiculo_id\n";
        }
        
        // PASO 2H: Limpiar referencia circular regis_vehic -> cond ANTES de eliminar conductores
        $clear_cond_ref_sql = "UPDATE regis_vehic SET cond_id = NULL WHERE id = ?";
        $clear_cond_ref_stmt = $conn->prepare($clear_cond_ref_sql);
        $clear_cond_ref_stmt->bind_param('i', $vehiculo_id);
        $clear_cond_ref_stmt->execute();
        if ($clear_cond_ref_stmt->affected_rows > 0) {
            echo "Limpiada referencia circular regis_vehic->cond en vehículo $vehiculo_id\n";
        }
        
        // PASO 2I: Ahora eliminar conductores de este vehículo
        $delete_cond_sql = "DELETE FROM cond WHERE regis_vehic_id = ?";
        $delete_cond_stmt = $conn->prepare($delete_cond_sql);
        $delete_cond_stmt->bind_param('i', $vehiculo_id);
        $delete_cond_stmt->execute();
        $conductores_eliminados = $delete_cond_stmt->affected_rows;
        $total_conductores_eliminados += $conductores_eliminados;
        if ($conductores_eliminados > 0) {
            echo "Eliminados $conductores_eliminados conductores del vehículo $vehiculo_id\n";
        }
    }
    
    // PASO 3: Eliminar los vehículos de esta subcategoría (ya sin referencias circulares)
    if ($vehiculos_count > 0) {
        echo "\nEliminando $vehiculos_count vehículos de la subcategoría...\n";
        $delete_vehiculos_sql = "DELETE FROM regis_vehic WHERE subcat_vehic_id = ?";
        $delete_vehiculos_stmt = $conn->prepare($delete_vehiculos_sql);
        $delete_vehiculos_stmt->bind_param('i', $subcat_id);
        $delete_vehiculos_stmt->execute();
    }
    
    // PASO 4: Finalmente eliminar la subcategoría
    echo "Eliminando subcategoría...\n";
    $sql = "DELETE FROM subcat_vehic WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $subcat_id);
    $stmt->execute();
    
    // Confirmar transacción
    $conn->commit();
    $conn->autocommit(true);
    
    echo "\nÉXITO: Subcategoría $subcat_id eliminada exitosamente con todas sus dependencias!\n";
    
    // Mensaje informativo sobre lo que se eliminó
    $mensaje = "Subcategoría eliminada correctamente.";
    $eliminations = [];
    
    if ($vehiculos_count > 0) {
        $eliminations[] = "$vehiculos_count vehículos eliminados";
    }
    if ($total_ordenes_eliminadas > 0) {
        $eliminations[] = "$total_ordenes_eliminadas órdenes de trabajo eliminadas";
    }
    if ($total_sali_repue_eliminadas > 0) {
        $eliminations[] = "$total_sali_repue_eliminadas salidas de repuestos eliminadas";
    }
    if ($total_sali_vehi_eliminadas > 0) {
        $eliminations[] = "$total_sali_vehi_eliminadas salidas de vehículos eliminadas";
    }
    if ($total_alertas_eliminadas > 0) {
        $eliminations[] = "$total_alertas_eliminadas alertas eliminadas";
    }
    if ($total_conductores_eliminados > 0) {
        $eliminations[] = "$total_conductores_eliminados conductores eliminados";
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