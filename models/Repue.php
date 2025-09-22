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
    public function save($data) {
    $types = 'ssiiissssissssddsssisisss'; // 24 parámetros para INSERT
        if (!empty($data['id'])) {
            $stmt = $this->conn->prepare("UPDATE repue SET nombre=?, marca_repuesto=?, proveedor_id=?, cat_repu_id=?, subcat_repu_id=?, modelo=?, medidas_espe=?, norma_estan=?, numero_parte=?, des_tecnica=?, veh_compatible=?, cantidad=?, estado_repus=?, fecha_ingreso=?, num_factura=?, ubi_almacen=?, pre_unitario=?, costo_total=?, garantia=?, res_ingreso=?, cant_stock=?, fecha_venci=?, dest_area=?, firma_verificacion=? WHERE id=?");
            $nombre = (string)$data['nombre'];
            $marca_repuesto = (string)$data['marca_repuesto'];
            $proveedor_id = (int)$data['proveedor_id'];
            $cat_repu_id = (int)$data['cat_repu_id'];
            $subcat_repu_id = (int)$data['subcat_repu_id'];
            $modelo = (string)$data['modelo'];
            $medidas_espe = (string)$data['medidas_espe'];
            $norma_estan = (string)$data['norma_estan'];
            $numero_parte = (string)$data['numero_parte'];
            $des_tecnica = (string)$data['des_tecnica'];
            $veh_compatible = (string)$data['veh_compatible'];
            $cantidad = (int)$data['cantidad'];
            $estado_repus = (string)$data['estado_repus'];
            $fecha_ingreso = (string)$data['fecha_ingreso'];
            $num_factura = (string)$data['num_factura'];
            $ubi_almacen = (string)$data['ubi_almacen'];
            $pre_unitario = (float)$data['pre_unitario'];
            $costo_total = (float)$data['costo_total'];
            $garantia = (string)$data['garantia'];
            $res_ingreso = (string)$data['res_ingreso'];
            $cant_stock = (int)$data['cant_stock'];
            $fecha_venci = (string)$data['fecha_venci'];
            $dest_area = (string)$data['dest_area'];
            $firma_verificacion = (string)$data['firma_verificacion'];
            $id = (int)$data['id'];
            $stmt->bind_param('ssiiissssissssddsssisissi',
                $nombre, $marca_repuesto, $proveedor_id, $cat_repu_id, $subcat_repu_id, $modelo, $medidas_espe, $norma_estan, $numero_parte, $des_tecnica, $veh_compatible, $cantidad, $estado_repus, $fecha_ingreso, $num_factura, $ubi_almacen, $pre_unitario, $costo_total, $garantia, $res_ingreso, $cant_stock, $fecha_venci, $dest_area, $firma_verificacion, $id
            );
        } else {
            $stmt = $this->conn->prepare("INSERT INTO repue (nombre, marca_repuesto, proveedor_id, cat_repu_id, subcat_repu_id, modelo, medidas_espe, norma_estan, numero_parte, des_tecnica, veh_compatible, cantidad, estado_repus, fecha_ingreso, num_factura, ubi_almacen, pre_unitario, costo_total, garantia, res_ingreso, cant_stock, fecha_venci, dest_area, firma_verificacion) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $nombre = (string)$data['nombre'];
            $marca_repuesto = (string)$data['marca_repuesto'];
            $proveedor_id = (int)$data['proveedor_id'];
            $cat_repu_id = (int)$data['cat_repu_id'];
            $subcat_repu_id = (int)$data['subcat_repu_id'];
            $modelo = (string)$data['modelo'];
            $medidas_espe = (string)$data['medidas_espe'];
            $norma_estan = (string)$data['norma_estan'];
            $numero_parte = (string)$data['numero_parte'];
            $des_tecnica = (string)$data['des_tecnica'];
            $veh_compatible = (string)$data['veh_compatible'];
            $cantidad = (int)$data['cantidad'];
            $estado_repus = (string)$data['estado_repus'];
            $fecha_ingreso = (string)$data['fecha_ingreso'];
            $num_factura = (string)$data['num_factura'];
            $ubi_almacen = (string)$data['ubi_almacen'];
            $pre_unitario = (float)$data['pre_unitario'];
            $costo_total = (float)$data['costo_total'];
            $garantia = (string)$data['garantia'];
            $res_ingreso = (string)$data['res_ingreso'];
            $cant_stock = (int)$data['cant_stock'];
            $fecha_venci = (string)$data['fecha_venci'];
            $dest_area = (string)$data['dest_area'];
            $firma_verificacion = (string)$data['firma_verificacion'];
            $stmt->bind_param('ssiiissssissssddsssisissss',
                $nombre, $marca_repuesto, $proveedor_id, $cat_repu_id, $subcat_repu_id, $modelo, $medidas_espe, $norma_estan, $numero_parte, $des_tecnica, $veh_compatible, $cantidad, $estado_repus, $fecha_ingreso, $num_factura, $ubi_almacen, $pre_unitario, $costo_total, $garantia, $res_ingreso, $cant_stock, $fecha_venci, $dest_area, $firma_verificacion
            );
        }
        return $stmt->execute();
    }
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM repue WHERE id=?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
