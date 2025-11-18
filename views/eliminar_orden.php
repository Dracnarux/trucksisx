<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
// Verificar que no sea conductor
if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor') {
    header('Location: orden_trabajo.php');
    exit();
}

require_once '../config/db.php';
$conn = conectarDB();
$id = $_GET['id'] ?? null;
if ($id) {
    // Primero obtener la alerta asociada antes de eliminar
    $select_stmt = $conn->prepare("SELECT alert_id FROM ord_trabj WHERE id=?");
    $select_stmt->bind_param('i', $id);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $orden = $result->fetch_assoc();
    $alert_id = $orden['alert_id'] ?? null;
    $select_stmt->close();
    
    // También verificar si hay alertas que referencian a esta orden en ord_trabj_id
    $check_alert_stmt = $conn->prepare("SELECT id FROM alert WHERE ord_trabj_id=?");
    $check_alert_stmt->bind_param('i', $id);
    $check_alert_stmt->execute();
    $alert_result = $check_alert_stmt->get_result();
    $alertas_referenciadas = [];
    while ($row = $alert_result->fetch_assoc()) {
        $alertas_referenciadas[] = $row['id'];
    }
    $check_alert_stmt->close();
    
    // Primero, quitar la referencia de las alertas que apuntan a esta orden
    if (!empty($alertas_referenciadas)) {
        $update_alert_stmt = $conn->prepare("UPDATE alert SET ord_trabj_id=NULL WHERE ord_trabj_id=?");
        $update_alert_stmt->bind_param('i', $id);
        $update_alert_stmt->execute();
        $update_alert_stmt->close();
    }
    
    // ORDEN CORRECTO DE ELIMINACIÓN (de dependencias más profundas a menos profundas):
    // 
    // Jerarquía de dependencias (MÚLTIPLES RELACIONES CIRCULARES COMPLEJAS):
    // ord_trabj (padre)
    //   ├── sali_repue (hijo de ord_trabj)
    //   │   ├── repor (nieto - depende de sali_repue_id)
    //   │   ├── sali_vehi (nieto - depende de sali_repue_id)
    //   │   └── ⬆️ CIRCULAR: sali_repue.repor_id → repor.id
    //   ├── sali_vehi (hijo de ord_trabj)  
    //   │   ├── repor (nieto - depende de sali_vehi_id)
    //   │   ├── ⬆️ DEPENDE DE: sali_vehi.sali_repue_id → sali_repue.id
    //   │   └── ⬆️ CIRCULAR: sali_vehi.repor_id → repor.id ⭐ NUEVA CIRCULAR
    //   ├── repor (hijo directo - puede tener ord_trabj_id)
    //   │   ├── ⬇️ CIRCULAR: repor.id ← sali_repue.repor_id
    //   │   └── ⬇️ CIRCULAR: repor.id ← sali_vehi.repor_id ⭐ NUEVA CIRCULAR
    //   └── alert (hijo - referencia a ord_trabj)
    //
    // Orden de eliminación: repor directo → romper TODOS los círculos → repor → sali_vehi → sali_repue → ord_trabj
    //
    // ESTRATEGIA PARA RELACIONES CIRCULARES MÚLTIPLES:
    // 1. Eliminar repor que depende directamente de ord_trabj
    // 2. Para cada sali_repue: UPDATE SET repor_id=NULL → DELETE repor WHERE sali_repue_id
    // 3. Para cada sali_vehi: UPDATE SET repor_id=NULL → DELETE repor WHERE sali_vehi_id  
    // 4. Solo entonces eliminar sali_vehi y sali_repue
    
    // 1. Verificar si existe columna ord_trabj_id en repor y limpiarla primero
    $check_repor_ord = $conn->query("SHOW COLUMNS FROM repor LIKE 'ord_trabj_id'");
    if ($check_repor_ord && $check_repor_ord->num_rows > 0) {
        $delete_repor_direct_stmt = $conn->prepare("DELETE FROM repor WHERE ord_trabj_id=?");
        $delete_repor_direct_stmt->bind_param('i', $id);
        $delete_repor_direct_stmt->execute();
        $delete_repor_direct_stmt->close();
    }
    
    // 2. Primero obtener los IDs de sali_repue que dependen de esta orden
    $get_sali_repue_stmt = $conn->prepare("SELECT id FROM sali_repue WHERE ord_trabj_id=?");
    $get_sali_repue_stmt->bind_param('i', $id);
    $get_sali_repue_stmt->execute();
    $sali_repue_result = $get_sali_repue_stmt->get_result();
    $sali_repue_ids = [];
    while ($row = $sali_repue_result->fetch_assoc()) {
        $sali_repue_ids[] = $row['id'];
    }
    $get_sali_repue_stmt->close();
    
    // 3. Manejar relación circular entre sali_repue y repor
    // Primero romper las referencias circulares
    if (!empty($sali_repue_ids)) {
        // Paso 3a: Actualizar repor_id a NULL en sali_repue para romper la referencia circular
        foreach ($sali_repue_ids as $sali_repue_id) {
            $update_sali_repue_stmt = $conn->prepare("UPDATE sali_repue SET repor_id=NULL WHERE id=?");
            $update_sali_repue_stmt->bind_param('i', $sali_repue_id);
            $update_sali_repue_stmt->execute();
            $update_sali_repue_stmt->close();
        }
        
        // Paso 3b: Ahora eliminar registros de repor que dependen de sali_repue
        foreach ($sali_repue_ids as $sali_repue_id) {
            $delete_repor_stmt = $conn->prepare("DELETE FROM repor WHERE sali_repue_id=?");
            $delete_repor_stmt->bind_param('i', $sali_repue_id);
            $delete_repor_stmt->execute();
            $delete_repor_stmt->close();
        }
    }
    
    // 4. Manejar la relación circular entre sali_repue y sali_vehi
    if (!empty($sali_repue_ids)) {
        foreach ($sali_repue_ids as $sali_repue_id) {
            // Primero romper la relación desde sali_vehi hacia sali_repue
            $update_sali_vehi_repue_stmt = $conn->prepare("UPDATE sali_vehi SET sali_repue_id=NULL WHERE sali_repue_id=?");
            $update_sali_vehi_repue_stmt->bind_param('i', $sali_repue_id);
            $update_sali_vehi_repue_stmt->execute();
            $update_sali_vehi_repue_stmt->close();
        }
        
        // Ahora sí podemos eliminar los registros de sali_repue
        foreach ($sali_repue_ids as $sali_repue_id) {
            $delete_sali_repue_stmt = $conn->prepare("DELETE FROM sali_repue WHERE id=?");
            $delete_sali_repue_stmt->bind_param('i', $sali_repue_id);
            $delete_sali_repue_stmt->execute();
            $delete_sali_repue_stmt->close();
        }
    }
    
    // 5. También eliminar sali_vehi que depende directamente de ord_trabj_id
    // Primero obtener IDs de estos sali_vehi
    $get_sali_vehi_by_orden_stmt = $conn->prepare("SELECT id FROM sali_vehi WHERE ord_trabj_id=?");
    $get_sali_vehi_by_orden_stmt->bind_param('i', $id);
    $get_sali_vehi_by_orden_stmt->execute();
    $sali_vehi_by_orden_result = $get_sali_vehi_by_orden_stmt->get_result();
    $sali_vehi_by_orden_ids = [];
    while ($row = $sali_vehi_by_orden_result->fetch_assoc()) {
        $sali_vehi_by_orden_ids[] = $row['id'];
    }
    $get_sali_vehi_by_orden_stmt->close();
    
    // Eliminar dependencias de repor hacia estos sali_vehi
    if (!empty($sali_vehi_by_orden_ids)) {
        foreach ($sali_vehi_by_orden_ids as $sali_vehi_id) {
            // Primero romper la relación circular sali_vehi.repor_id → repor.id
            $update_sali_vehi_stmt = $conn->prepare("UPDATE sali_vehi SET repor_id=NULL WHERE id=?");
            $update_sali_vehi_stmt->bind_param('i', $sali_vehi_id);
            $update_sali_vehi_stmt->execute();
            $update_sali_vehi_stmt->close();
            
            // Ahora eliminar repor que depende de este sali_vehi_id
            $check_repor_vehi = $conn->query("SHOW COLUMNS FROM repor LIKE 'sali_vehi_id'");
            if ($check_repor_vehi && $check_repor_vehi->num_rows > 0) {
                $delete_repor_vehi_stmt = $conn->prepare("DELETE FROM repor WHERE sali_vehi_id=?");
                $delete_repor_vehi_stmt->bind_param('i', $sali_vehi_id);
                $delete_repor_vehi_stmt->execute();
                $delete_repor_vehi_stmt->close();
            }
        }
    }
    
    // Ahora eliminar los sali_vehi que dependen directamente de ord_trabj_id
    $delete_sali_vehi_by_orden_stmt = $conn->prepare("DELETE FROM sali_vehi WHERE ord_trabj_id=?");
    $delete_sali_vehi_by_orden_stmt->bind_param('i', $id);
    $delete_sali_vehi_by_orden_stmt->execute();
    $delete_sali_vehi_by_orden_stmt->close();
    
    // 6. Los registros de sali_repue ya fueron eliminados en el paso 4
    
    // 7. Verificar y limpiar otras posibles dependencias de nivel superior
    // Lista de tablas que pueden tener referencias directas a ord_trabj (excluyendo las ya procesadas)
    $tablas_dependientes = [
        'reporte_final' => 'ord_trabj_id',
        'reporte_salidas' => 'ord_trabj_id'
    ];
    
    foreach ($tablas_dependientes as $tabla => $columna) {
        // Verificar si la tabla existe
        $check_table = $conn->query("SHOW TABLES LIKE '$tabla'");
        if ($check_table && $check_table->num_rows > 0) {
            // Verificar si la columna existe
            $check_column = $conn->query("SHOW COLUMNS FROM $tabla LIKE '$columna'");
            if ($check_column && $check_column->num_rows > 0) {
                // Eliminar registros dependientes
                $delete_stmt = $conn->prepare("DELETE FROM $tabla WHERE $columna=?");
                $delete_stmt->bind_param('i', $id);
                $delete_stmt->execute();
                $delete_stmt->close();
            }
        }
    }
    
    // 8. Verificación final: buscar cualquier otra tabla que pueda referenciar ord_trabj
    // Esto es una verificación adicional de seguridad
    $check_foreign_keys = $conn->query("
        SELECT TABLE_NAME, COLUMN_NAME 
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE REFERENCED_TABLE_NAME = 'ord_trabj' 
        AND REFERENCED_COLUMN_NAME = 'id'
        AND TABLE_SCHEMA = DATABASE()
    ");
    
    if ($check_foreign_keys && $check_foreign_keys->num_rows > 0) {
        while ($fk_row = $check_foreign_keys->fetch_assoc()) {
            $fk_table = $fk_row['TABLE_NAME'];
            $fk_column = $fk_row['COLUMN_NAME'];
            
            // Solo procesar si no hemos manejado ya esta tabla
            if (!in_array($fk_table, ['sali_repue', 'sali_vehi', 'alert', 'reporte_final', 'reporte_salidas'])) {
                // Verificar si hay registros antes de intentar eliminar
                $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM $fk_table WHERE $fk_column=?");
                $count_stmt->bind_param('i', $id);
                $count_stmt->execute();
                $count_result = $count_stmt->get_result();
                $count_row = $count_result->fetch_assoc();
                $count_stmt->close();
                
                if ($count_row['count'] > 0) {
                    $delete_fk_stmt = $conn->prepare("DELETE FROM $fk_table WHERE $fk_column=?");
                    $delete_fk_stmt->bind_param('i', $id);
                    $delete_fk_stmt->execute();
                    $delete_fk_stmt->close();
                }
            }
        }
    }
    
    // Ahora eliminar la orden de trabajo
    $stmt = $conn->prepare("DELETE FROM ord_trabj WHERE id=?");
    $stmt->bind_param('i', $id);
    
    try {
        if ($stmt->execute()) {
            $stmt->close();
            
            // Si había una alerta asociada (alert_id), volver a activarla
            if ($alert_id && !empty($alert_id)) {
                $alert_stmt = $conn->prepare("UPDATE alert SET estado='activa' WHERE id=?");
                $alert_stmt->bind_param('i', $alert_id);
                $alert_stmt->execute();
                $alert_stmt->close();
            }
            
            // También reactivar las alertas que estaban referenciando esta orden
            if (!empty($alertas_referenciadas)) {
                foreach ($alertas_referenciadas as $alerta_id) {
                    $reactivar_stmt = $conn->prepare("UPDATE alert SET estado='activa' WHERE id=?");
                    $reactivar_stmt->bind_param('i', $alerta_id);
                    $reactivar_stmt->execute();
                    $reactivar_stmt->close();
                }
            }
            
            $conn->close();
            
            // Redireccionar con mensaje de éxito
            $mensaje = urlencode("La orden de trabajo ha sido eliminada exitosamente.");
            header("Location: orden_trabajo.php?mensaje=$mensaje&tipo=exito");
            exit;
        } else {
            throw new Exception("Error al ejecutar la eliminación");
        }
    } catch (Exception $e) {
        $stmt->close();
        $conn->close();
        
        // Redireccionar con mensaje de error detallado
        $mensaje = urlencode("Error al eliminar la orden de trabajo: " . $e->getMessage() . ". Puede que existan registros dependientes.");
        header("Location: orden_trabajo.php?mensaje=$mensaje&tipo=error");
        exit;
    }
} else {
    // No se proporcionó un ID válido
    $mensaje = urlencode("No se especificó una orden válida para eliminar.");
    header("Location: orden_trabajo.php?mensaje=$mensaje&tipo=error");
    exit;
}
$conn->close();
header('Location: orden_trabajo.php');
exit;
?>