<?php
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_GET['vehiculo_id'])) {
    echo json_encode(['error' => 'ID de vehículo no proporcionado']);
    exit;
}

$vehiculoId = intval($_GET['vehiculo_id']);
$db = conectarDB();

// Obtener información completa del vehículo y sus relaciones
$query = "SELECT 
    v.id as vehiculo_id,
    v.placa,
    v.marca_vehiculo,
    v.modelo,
    v.num_cha,
    v.color,
    v.ano_fabricacion,
    v.cond_id,
    -- Datos del conductor
    c.id as conductor_id,
    c.cargo as conductor_nombre,
    u.nombre as conductor_nombre_real,
    u.apellido as conductor_apellido,
    -- Alertas activas del vehículo
    a.id as alerta_id,
    a.descripcion as alerta_descripcion,
    a.tipo_alerta,
    a.prioridad as alerta_prioridad,
    a.estado as alerta_estado,
    a.ord_trabj_id,
    -- Orden de trabajo asociada
    ot.id as orden_trabajo_id,
    ot.nombre_trabajo,
    ot.descripcion as orden_descripcion,
    ot.nombre_repuesto,
    ot.estado as orden_estado,
    ot.prioridad as orden_prioridad,
    -- Salida de repuesto relacionada
    sr.id as salida_repuesto_id,
    sr.fecha_salida,
    sr.cantidad as repuesto_cantidad,
    r.nombre as repuesto_nombre
FROM regis_vehic v
LEFT JOIN cond c ON v.cond_id = c.id
LEFT JOIN users u ON c.cargo = u.nombre
LEFT JOIN alert a ON v.id = a.regis_vehic_id AND a.estado IN ('activa', 'en_proceso')
LEFT JOIN ord_trabj ot ON a.ord_trabj_id = ot.id
LEFT JOIN sali_repue sr ON (sr.ord_trabj_id = ot.id OR sr.alerta_id = a.id)
LEFT JOIN repue r ON sr.repue_id = r.id
WHERE v.id = ?
ORDER BY a.prioridad DESC, a.fecha_hora DESC
LIMIT 1";

$stmt = $db->prepare($query);
$stmt->bind_param('i', $vehiculoId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    
    // Formatear el nombre completo del conductor
    if ($data['conductor_nombre_real'] && $data['conductor_apellido']) {
        $data['conductor_nombre_completo'] = $data['conductor_nombre_real'] . ' ' . $data['conductor_apellido'];
    } else {
        $data['conductor_nombre_completo'] = $data['conductor_nombre'];
    }
    
    // Obtener todas las salidas de repuesto relacionadas
    $query_repuestos = "SELECT 
        sr.id,
        sr.fecha_salida,
        sr.cantidad,
        r.nombre as repuesto_nombre
    FROM sali_repue sr
    LEFT JOIN repue r ON sr.repue_id = r.id
    WHERE sr.ord_trabj_id = ? OR sr.alerta_id = ?
    ORDER BY sr.fecha_salida DESC";
    
    $stmt_repuestos = $db->prepare($query_repuestos);
    $stmt_repuestos->bind_param('ii', $data['orden_trabajo_id'], $data['alerta_id']);
    $stmt_repuestos->execute();
    $result_repuestos = $stmt_repuestos->get_result();
    
    $salidas_repuesto = [];
    while ($row = $result_repuestos->fetch_assoc()) {
        $salidas_repuesto[] = $row;
    }
    
    $data['salidas_repuesto_disponibles'] = $salidas_repuesto;
    
    echo json_encode($data);
} else {
    // Si no hay alerta activa, devolver solo datos del vehículo
    $query_simple = "SELECT 
        v.id as vehiculo_id,
        v.placa,
        v.marca_vehiculo,
        v.modelo,
        v.num_cha,
        v.color,
        v.ano_fabricacion,
        v.cond_id,
        c.id as conductor_id,
        c.cargo as conductor_nombre,
        u.nombre as conductor_nombre_real,
        u.apellido as conductor_apellido
    FROM regis_vehic v
    LEFT JOIN cond c ON v.cond_id = c.id
    LEFT JOIN users u ON c.cargo = u.nombre
    WHERE v.id = ?";
    
    $stmt_simple = $db->prepare($query_simple);
    $stmt_simple->bind_param('i', $vehiculoId);
    $stmt_simple->execute();
    $result_simple = $stmt_simple->get_result();
    
    if ($result_simple->num_rows > 0) {
        $data = $result_simple->fetch_assoc();
        
        if ($data['conductor_nombre_real'] && $data['conductor_apellido']) {
            $data['conductor_nombre_completo'] = $data['conductor_nombre_real'] . ' ' . $data['conductor_apellido'];
        } else {
            $data['conductor_nombre_completo'] = $data['conductor_nombre'];
        }
        
        $data['salidas_repuesto_disponibles'] = [];
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'Vehículo no encontrado']);
    }
}

$stmt->close();
$db->close();
