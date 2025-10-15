<?php
require_once __DIR__ . '/../config/db.php';

class Proveedor {
    private $conn;
    public function __construct() {
        $this->conn = conectarDB();
    }
    public function getAll($filtros = []) {
        $sql = "SELECT p.*, 
                       (SELECT COUNT(*) FROM repue r WHERE r.proveedor_id = p.id) as total_repuestos
                FROM proveedor p WHERE 1";
        $params = [];
        $types = '';
        
        // Filtros de texto con LIKE
        foreach ([
            'nom_proveedor', 'tip_repuesto', 'mar_distribuye', 'ciudad_depar', 
            'correo', 'tel_contacto', 'nit_num_identi'
        ] as $campo) {
            if (!empty($filtros[$campo])) {
                $sql .= " AND p.$campo LIKE ?";
                $params[] = "%" . $filtros[$campo] . "%";
                $types .= 's';
            }
        }
        
        // Filtro de país (exacto)
        if (!empty($filtros['pais'])) {
            $sql .= " AND p.pais = ?";
            $params[] = $filtros['pais'];
            $types .= 's';
        }
        
        // Filtro de tiempo de entrega (exacto)
        if (!empty($filtros['tiem_entrega'])) {
            $sql .= " AND p.tiem_entrega = ?";
            $params[] = $filtros['tiem_entrega'];
            $types .= 's';
        }
        
        // Filtro de forma de pago (exacto)
        if (!empty($filtros['for_pago'])) {
            $sql .= " AND p.for_pago = ?";
            $params[] = $filtros['for_pago'];
            $types .= 's';
        }
        
        // Filtro de crédito disponible
        if (!empty($filtros['tiene_credito'])) {
            if ($filtros['tiene_credito'] === 'si') {
                $sql .= " AND (p.cred_disponible IS NOT NULL AND p.cred_disponible != '' AND p.cred_disponible != '0')";
            } else {
                $sql .= " AND (p.cred_disponible IS NULL OR p.cred_disponible = '' OR p.cred_disponible = '0')";
            }
        }
        
        // Filtro de estado de repuestos
        if (!empty($filtros['estado_repuestos'])) {
            if ($filtros['estado_repuestos'] === 'con_repuestos') {
                $sql .= " AND EXISTS (SELECT 1 FROM repue r WHERE r.proveedor_id = p.id)";
            } else {
                $sql .= " AND NOT EXISTS (SELECT 1 FROM repue r WHERE r.proveedor_id = p.id)";
            }
        }
        
        // Ordenar por nombre
        $sql .= " ORDER BY p.nom_proveedor ASC";
        
        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM proveedor WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function save($data) {
        if (!empty($data['id'])) {
            $stmt = $this->conn->prepare("UPDATE proveedor SET nit_num_identi=?, nom_proveedor=?, tel_contacto=?, carg_contacto=?, correo=?, direccion=?, ciudad_depar=?, pais=?, tip_repuesto=?, mar_distribuye=?, tiem_entrega=?, zon_cobertura=?, for_pago=?, cred_disponible=?, cuen_bancaria=? WHERE id=?");
            $stmt->bind_param('sssssssssssssssi',
                $data['nit_num_identi'], $data['nom_proveedor'], $data['tel_contacto'], $data['carg_contacto'], $data['correo'], $data['direccion'], $data['ciudad_depar'], $data['pais'], $data['tip_repuesto'], $data['mar_distribuye'], $data['tiem_entrega'], $data['zon_cobertura'], $data['for_pago'], $data['cred_disponible'], $data['cuen_bancaria'], $data['id']
            );
        } else {
            $stmt = $this->conn->prepare("INSERT INTO proveedor (nit_num_identi, nom_proveedor, tel_contacto, carg_contacto, correo, direccion, ciudad_depar, pais, tip_repuesto, mar_distribuye, tiem_entrega, zon_cobertura, for_pago, cred_disponible, cuen_bancaria) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('sssssssssssssss',
                $data['nit_num_identi'], $data['nom_proveedor'], $data['tel_contacto'], $data['carg_contacto'], $data['correo'], $data['direccion'], $data['ciudad_depar'], $data['pais'], $data['tip_repuesto'], $data['mar_distribuye'], $data['tiem_entrega'], $data['zon_cobertura'], $data['for_pago'], $data['cred_disponible'], $data['cuen_bancaria']
            );
        }
        return $stmt->execute();
    }
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM proveedor WHERE id=?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    public function getLastInsertId() {
        return $this->conn->insert_id;
    }
}
