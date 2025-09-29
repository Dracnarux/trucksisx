<?php
require_once '../models/User.php';
require_once '../config/db.php';

header('Content-Type: application/json');

$userModel = new User();
$db = new Database();
$conn = $db->getConnection();

// Obtener conductores activos y sus vehículos asignados
$sql = "SELECT c.id as cond_id, u.id as user_id, u.nombre, u.apellido, v.id as vehiculo_id, v.placa
        FROM cond c
        JOIN users u ON (u.nombre = c.cargo OR CONCAT(u.nombre, ' ', u.apellido) = c.cargo)
        JOIN regis_vehic v ON v.id = c.regis_vehic_id
        WHERE u.rol = 'conductor'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$conductores = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'conductores' => $conductores]);
