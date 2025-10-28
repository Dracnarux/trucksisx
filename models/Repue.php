<?php
require_once __DIR__ . '/../config/db.php';

class Repue {
    private $conn;
    public function __construct() {
        $this->conn = conectarDB();
    }
    public function getAll($filtros = []) {
        $sql = "SELECT * FROM repue WHERE 1";
        $params = [];
        $types = '';
        foreach ([
            'nombre', 'marca_repuesto', 'proveedor_id', 'cat_repu_id', 'subcat_repu_id', 'modelo', 'medidas_espe', 'norma_estan', 'numero_parte', 'des_tecnica', 'veh_compatible', 'estado_repus', 'num_factura', 'ubi_almacen', 'dest_area', 'firma_verificacion'
        ] as $campo) {
            if (!empty($filtros[$campo])) {
                if ($campo === 'cat_repu_id' || $campo === 'subcat_repu_id' || $campo === 'proveedor_id') {
                    $sql .= " AND $campo = ?";
                    $params[] = $filtros[$campo];
                    $types .= 'i';
                } else {
                    $sql .= " AND $campo LIKE ?";
                    $params[] = "%" . $filtros[$campo] . "%";
                    $types .= 's';
                }
            }
        }
        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM repue WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    // Nuevo método para actualizar solo el proveedor
    public function updateProveedor($repuestoId, $proveedorId) {
        $estado = $proveedorId ? 'Asignado' : 'Sin proveedor';
        $stmt = $this->conn->prepare("UPDATE repue SET proveedor_id = ?, estado_repus = ? WHERE id = ?");
        $stmt->bind_param('isi', $proveedorId, $estado, $repuestoId);
        return $stmt->execute();
    }
    
    // Método para desvincular todos los repuestos de un proveedor
    public function desvincularDeProveedor($proveedorId) {
        $stmt = $this->conn->prepare("UPDATE repue SET proveedor_id = NULL, estado_repus = 'Sin proveedor' WHERE proveedor_id = ?");
        $stmt->bind_param('i', $proveedorId);
        return $stmt->execute();
    }
    
    // Método para obtener repuestos por proveedor
    public function getByProveedor($proveedorId) {
        $stmt = $this->conn->prepare("SELECT * FROM repue WHERE proveedor_id = ?");
        $stmt->bind_param('i', $proveedorId);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    public function save($data) {
        // Permitir proveedor_id nulo
        $proveedor_id = isset($data['proveedor_id']) && $data['proveedor_id'] !== '' ? (int)$data['proveedor_id'] : null;
        // Estado automático según proveedor
        if ($proveedor_id) {
            $data['estado_repus'] = 'Asignado';
        } else {
            $data['estado_repus'] = 'Sin proveedor';
        }
        $campos = [
            'nombre','marca_repuesto','proveedor_id','cat_repu_id','subcat_repu_id','modelo','medidas_espe','norma_estan','numero_parte','des_tecnica','veh_compatible','cantidad','estado_repus','fecha_ingreso','num_factura','ubi_almacen','pre_unitario','costo_total','garantia','res_ingreso','cant_stock','fecha_venci','dest_area','firma_verificacion'
        ];
        if (!empty($data['id'])) {
            $set = implode('=?, ', $campos) . '=?';
            $sql = "UPDATE repue SET $set WHERE id=?";
            $stmt = $this->conn->prepare($sql);
            $params = [];
            $types = '';
            foreach ($campos as $c) {
                if ($c === 'proveedor_id') {
                    $params[] = $proveedor_id;
                    $types .= 'i';
                } elseif (in_array($c, ['cat_repu_id','subcat_repu_id','cantidad','cant_stock'])) {
                    $params[] = isset($data[$c]) && $data[$c] !== '' ? (int)$data[$c] : null;
                    $types .= 'i';
                } elseif (in_array($c, ['pre_unitario','costo_total'])) {
                    $params[] = isset($data[$c]) && $data[$c] !== '' ? (float)$data[$c] : null;
                    $types .= 'd';
                } else {
                    $params[] = isset($data[$c]) ? $data[$c] : null;
                    $types .= 's';
                }
            }
            $params[] = (int)$data['id'];
            $types .= 'i';
            $stmt->bind_param($types, ...$params);
        } else {
            $sql = "INSERT INTO repue (".implode(',', $campos).") VALUES (".implode(',', array_fill(0, count($campos), '?')).")";
            $stmt = $this->conn->prepare($sql);
            $params = [];
            $types = '';
            foreach ($campos as $c) {
                if ($c === 'proveedor_id') {
                    $params[] = $proveedor_id;
                    $types .= 'i';
                } elseif (in_array($c, ['cat_repu_id','subcat_repu_id','cantidad','cant_stock'])) {
                    $params[] = isset($data[$c]) && $data[$c] !== '' ? (int)$data[$c] : null;
                    $types .= 'i';
                } elseif (in_array($c, ['pre_unitario','costo_total'])) {
                    $params[] = isset($data[$c]) && $data[$c] !== '' ? (float)$data[$c] : null;
                    $types .= 'd';
                } else {
                    $params[] = isset($data[$c]) ? $data[$c] : null;
                    $types .= 's';
                }
            }
            $stmt->bind_param($types, ...$params);
        }
        return $stmt->execute();
    }
    public function delete($id) {
        try {
            // Iniciar transacción
            $this->conn->begin_transaction();
            
            // Verificar si el repuesto está siendo usado en sali_repue
            $check_sql = "SELECT COUNT(*) as count FROM sali_repue WHERE repue_id = ?";
            $check_stmt = $this->conn->prepare($check_sql);
            $check_stmt->bind_param('i', $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                // Si hay salidas de repuestos que lo usan, desvincular
                $update_sql = "UPDATE sali_repue SET repue_id = NULL WHERE repue_id = ?";
                $update_stmt = $this->conn->prepare($update_sql);
                $update_stmt->bind_param('i', $id);
                if (!$update_stmt->execute()) {
                    throw new Exception("Error al desvincular salidas de repuestos");
                }
            }
            
            // Ahora eliminar el repuesto
            $delete_sql = "DELETE FROM repue WHERE id = ?";
            $delete_stmt = $this->conn->prepare($delete_sql);
            $delete_stmt->bind_param('i', $id);
            
            if (!$delete_stmt->execute()) {
                throw new Exception("Error al eliminar el repuesto");
            }
            
            // Confirmar transacción
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            // Revertir transacción
            $this->conn->rollback();
            throw $e;
        }
    }
}
