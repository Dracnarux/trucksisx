<?php
// Siempre devolver JSON válido, incluso ante errores fatales
set_exception_handler(function($e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
    exit;
});
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => "Error PHP ($errno): $errstr en $errfile:$errline"]);
    exit;
});
ini_set('display_errors', 0);
error_reporting(E_ALL);

// ...existing code...
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID de orden de trabajo no proporcionado']);
    exit;
}

$ordenId = intval($_GET['id']);
$db = conectarDB();

// Obtener detalles completos de la orden de trabajo con sus relaciones
$query = "SELECT 
    ot.id,
    ot.nombre_trabajo,
    ot.descripcion,
    ot.nombre_repuesto,
    ot.fecha_creacion,
    ot.fecha_estimada,
    ot.estado,
    ot.prioridad,
    ot.cond_id,
    ot.users_id,
    ot.alert_id,
    -- Datos de la alerta
    a.id as alerta_id,
    a.descripcion as alerta_descripcion,
    a.tipo_alerta,
    a.prioridad as alerta_prioridad,
    a.regis_vehic_id,
    -- Datos del vehículo
    v.id as vehiculo_id,
    v.placa as numero_placa,
    v.modelo,
    v.marca_vehiculo as marca,
    v.color,
    NULL as ano_fabricacion,
    v.subcat_vehic_id,
    -- Datos del conductor
    c.id as conductor_id,
    c.cargo as conductor_nombre,
    u.nombre as conductor_nombre_real,
    u.apellido as conductor_apellido
FROM ord_trabj ot
LEFT JOIN alert a ON ot.alert_id = a.id
LEFT JOIN regis_vehic v ON a.regis_vehic_id = v.id
LEFT JOIN cond c ON ot.cond_id = c.id
LEFT JOIN users u ON c.cargo = u.nombre
WHERE ot.id = ?";

$stmt = $db->prepare($query);
$stmt->bind_param('i', $ordenId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $orden = $result->fetch_assoc();
    
    // Formatear el nombre completo del conductor
    if ($orden['conductor_nombre_real'] && $orden['conductor_apellido']) {
        $orden['conductor_nombre_completo'] = $orden['conductor_nombre_real'] . ' ' . $orden['conductor_apellido'];
    } else {
        $orden['conductor_nombre_completo'] = $orden['conductor_nombre'];
    }
    
    echo json_encode($orden);
} else {
    echo json_encode(['error' => 'Orden de trabajo no encontrada']);
}

$stmt->close();
$db->close();
