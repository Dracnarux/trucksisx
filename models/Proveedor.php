<?php
require_once __DIR__ . '/../config/db.php';

class Proveedor {
    private $conn;
    public function __construct() {
        $this->conn = conectarDB();
    }
    public function getAll($filtros = []) {
        $sql = "SELECT * FROM proveedor WHERE 1";
        $params = [];
        $types = '';
        foreach ([
            'nom_proveedor', 'tip_repuesto', 'mar_distribuye', 'ciudad_depar', 'pais'
        ] as $campo) {
            if (!empty($filtros[$campo])) {
                $sql .= " AND $campo LIKE ?";
                $params[] = "%" . $filtros[$campo] . "%";
                $types .= 's';
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
        $stmt = $this->conn->prepare("SELECT * FROM proveedor WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function save($data) {
        if (!empty($data['id'])) {
            $stmt = $this->conn->prepare("UPDATE proveedor SET nom_proveedor=?, tel_contacto=?, carg_contacto=?, correo=?, direccion=?, ciudad_depar=?, pais=?, tip_repuesto=?, mar_distribuye=?, tiem_entrega=?, zon_cobertura=?, for_pago=?, cred_disponible=?, cuen_bancaria=? WHERE id=?");
            $stmt->bind_param('ssssssssssssssi',
                $data['nom_proveedor'], $data['tel_contacto'], $data['carg_contacto'], $data['correo'], $data['direccion'], $data['ciudad_depar'], $data['pais'], $data['tip_repuesto'], $data['mar_distribuye'], $data['tiem_entrega'], $data['zon_cobertura'], $data['for_pago'], $data['cred_disponible'], $data['cuen_bancaria'], $data['id']
            );
        } else {
            $stmt = $this->conn->prepare("INSERT INTO proveedor (nom_proveedor, tel_contacto, carg_contacto, correo, direccion, ciudad_depar, pais, tip_repuesto, mar_distribuye, tiem_entrega, zon_cobertura, for_pago, cred_disponible, cuen_bancaria) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('ssssssssssssss',
                $data['nom_proveedor'], $data['tel_contacto'], $data['carg_contacto'], $data['correo'], $data['direccion'], $data['ciudad_depar'], $data['pais'], $data['tip_repuesto'], $data['mar_distribuye'], $data['tiem_entrega'], $data['zon_cobertura'], $data['for_pago'], $data['cred_disponible'], $data['cuen_bancaria']
            );
        }
        return $stmt->execute();
    }
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM proveedor WHERE id=?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
