<?php
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_GET['vehiculo_id'])) {
    echo json_encode(['error' => 'ID de vehículo no proporcionado']);
    exit;
}

$vehiculoId = intval($_GET['vehiculo_id']);
$tipoAlerta = isset($_GET['tipo_alerta']) ? $_GET['tipo_alerta'] : null;
$db = conectarDB();

// Obtener repuestos compatibles con el vehículo
$query = "SELECT 
    r.id,
    r.nombre,
    r.marca_repuesto,
    r.modelo,
    r.cantidad,
    r.cant_stock,
    r.estado_repus,
    r.cat_repu_id,
    r.subcat_repu_id,
    cr.nombre as categoria_nombre,
    scr.nombre as subcategoria_nombre
FROM repue r
LEFT JOIN cat_repu cr ON r.cat_repu_id = cr.id
LEFT JOIN subcat_repu scr ON r.subcat_repu_id = scr.id
LEFT JOIN regis_vehic v ON r.veh_compatible = v.modelo OR r.veh_compatible LIKE CONCAT('%', v.marca, '%')
WHERE (v.id = ? OR r.veh_compatible IS NULL OR r.veh_compatible = '')
AND r.cant_stock > 0
AND r.estado_repus = 'disponible'";

// Si hay tipo de alerta, filtrar repuestos relevantes
if ($tipoAlerta) {
    switch ($tipoAlerta) {
        case 'llanta':
            $query .= " AND (cr.nombre LIKE '%llanta%' OR cr.nombre LIKE '%neumatico%' OR scr.nombre LIKE '%llanta%')";
            break;
        case 'motor':
            $query .= " AND (cr.nombre LIKE '%motor%' OR scr.nombre LIKE '%motor%' OR r.nombre LIKE '%motor%')";
            break;
        case 'frenos':
            $query .= " AND (cr.nombre LIKE '%freno%' OR scr.nombre LIKE '%freno%' OR r.nombre LIKE '%freno%')";
            break;
    }
}

$query .= " ORDER BY 
    CASE 
        WHEN r.veh_compatible LIKE CONCAT('%', (SELECT modelo FROM regis_vehic WHERE id = ?), '%') THEN 1
        WHEN r.veh_compatible LIKE CONCAT('%', (SELECT marca FROM regis_vehic WHERE id = ?), '%') THEN 2
        ELSE 3
    END,
    r.nombre ASC
LIMIT 50";

$stmt = $db->prepare($query);
$stmt->bind_param('iii', $vehiculoId, $vehiculoId, $vehiculoId);
$stmt->execute();
$result = $stmt->get_result();

$repuestos = [];
while ($row = $result->fetch_assoc()) {
    $repuestos[] = $row;
}

echo json_encode($repuestos);

$stmt->close();
$db->close();
