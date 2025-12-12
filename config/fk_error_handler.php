<?php
/**
 * Sistema de Manejo de Errores FK para TruckSisX
 * 
 * Este archivo contiene funciones auxiliares para el manejo de
 * constraintes de llave foránea en todo el sistema.
 */

class FKErrorHandler {
    
    /**
     * Maneja errores de FK de manera uniforme
     */
    public static function handleFKError($e, $entity_type = 'registro') {
        $error_message = $e->getMessage();
        
        // Mensajes específicos según el tipo de error FK
        if (strpos($error_message, 'foreign key constraint fails') !== false) {
            if (strpos($error_message, 'sali_repue') !== false) {
                return "No se puede eliminar este {$entity_type} porque está siendo usado en salidas de repuestos.";
            }
            if (strpos($error_message, 'sali_vehi') !== false) {
                return "No se puede eliminar este {$entity_type} porque está siendo usado en salidas de vehículos.";
            }
            if (strpos($error_message, 'alert') !== false) {
                return "No se puede eliminar este {$entity_type} porque tiene alertas asociadas.";
            }
            if (strpos($error_message, 'ord_trabj') !== false) {
                return "No se puede eliminar este {$entity_type} porque tiene órdenes de trabajo asociadas.";
            }
            if (strpos($error_message, 'regis_vehic') !== false) {
                return "No se puede eliminar este {$entity_type} porque tiene vehículos registrados asociados.";
            }
            if (strpos($error_message, 'repue') !== false) {
                return "No se puede eliminar este {$entity_type} porque tiene repuestos asociados.";
            }
            if (strpos($error_message, 'subcat_') !== false) {
                return "No se puede eliminar este {$entity_type} porque tiene subcategorías asociadas.";
            }
            
            return "No se puede eliminar este {$entity_type} porque está siendo usado por otros registros del sistema.";
        }
        
        // Error genérico
        return "Error interno del sistema: " . $error_message;
    }
    
    /**
     * Retorna respuesta JSON para errores AJAX
     */
    public static function jsonError($message, $code = 500) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'error' => true
        ]);
        exit;
    }
    
    /**
     * Retorna respuesta JSON para éxito AJAX
     */
    public static function jsonSuccess($message, $data = null) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
    
    /**
     * Verifica si una petición es AJAX
     */
    public static function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Verifica dependencias antes de eliminar un registro
     */
    public static function checkDependencies($conn, $table, $id, $dependencies = []) {
        $issues = [];
        
        foreach ($dependencies as $dep_table => $dep_column) {
            $check_sql = "SELECT COUNT(*) as count FROM {$dep_table} WHERE {$dep_column} = ?";
            
            if (is_object($conn) && method_exists($conn, 'prepare')) {
                // Para conexiones mysqli
                $stmt = $conn->prepare($check_sql);
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $count = $row['count'];
            } else {
                // Para conexiones PDO
                $stmt = $conn->prepare($check_sql);
                $stmt->execute([$id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $count = $result['count'];
            }
            
            if ($count > 0) {
                $issues[] = [
                    'table' => $dep_table,
                    'count' => $count,
                    'column' => $dep_column
                ];
            }
        }
        
        return $issues;
    }
    
    /**
     * Ejecuta actualizaciones en cascada para limpiar dependencias
     */
    public static function cascadeUpdates($conn, $id, $cascades = []) {
        foreach ($cascades as $cascade_table => $cascade_column) {
            $update_sql = "UPDATE {$cascade_table} SET {$cascade_column} = NULL WHERE {$cascade_column} = ?";
            
            if (is_object($conn) && method_exists($conn, 'prepare')) {
                // Para conexiones mysqli
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param('i', $id);
                if (!$stmt->execute()) {
                    throw new Exception("Error al actualizar tabla {$cascade_table}");
                }
            } else {
                // Para conexiones PDO
                $stmt = $conn->prepare($update_sql);
                if (!$stmt->execute([$id])) {
                    throw new Exception("Error al actualizar tabla {$cascade_table}");
                }
            }
        }
    }
}

// Configuraciones de dependencias por tabla
class FKDependencies {
    
    public static function getRepuestoDependencies() {
        return [
            'sali_repue' => 'repue_id',
            'sali_vehi' => 'repue_id'
        ];
    }
    
    public static function getConductorDependencies() {
        return [
            'regis_vehic' => 'cond_id',
            'alert' => 'cond_id',
            'ord_trabj' => 'cond_id'
        ];
    }
    
    public static function getCatRepuDependencies() {
        return [
            'repue' => 'cat_repu_id',
            'subcat_repu' => 'cat_repu_id'
        ];
    }
    
    public static function getSubCatRepuDependencies() {
        return [
            'repue' => 'subcat_repu_id'
        ];
    }
    
    public static function getCatVehiculoDependencies() {
        return [
            'regis_vehic' => 'cat_vehic_id',
            'subcat_vehic' => 'cat_vehic_id'
        ];
    }
    
    public static function getSubCatVehiculoDependencies() {
        return [
            'regis_vehic' => 'subcat_vehic_id'
        ];
    }
    
    public static function getAlertDependencies() {
        return [
            'ord_trabj' => 'alert_id'
        ];
    }
    
    public static function getProveedorDependencies() {
        return [
            'repue' => 'proveedor_id'
        ];
    }
}
?>