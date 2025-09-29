<?php
// Script para sincronizar el campo cargo de la tabla cond con el nombre real del usuario conductor
require_once '../config/db.php';

$db = new Database();
$conn = $db->getConnection();

// Buscar todos los conductores en cond
$sql = "SELECT c.id, u.nombre FROM cond c JOIN users u ON c.regis_vehic_id IS NOT NULL AND u.rol = 'conductor' AND u.id = (
    SELECT id FROM users WHERE rol = 'conductor' AND nombre = c.cargo LIMIT 1
)";
$stmt = $conn->prepare($sql);
$stmt->execute();
$conductores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si no hay coincidencias exactas, buscar por vehículo asignado
$sql2 = "SELECT c.id, u.nombre FROM cond c JOIN users u ON u.rol = 'conductor' AND c.regis_vehic_id IS NOT NULL AND u.nombre != c.cargo LIMIT 100";
$stmt2 = $conn->prepare($sql2);
$stmt2->execute();
$desincronizados = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Actualizar los desincronizados para que cargo = nombre correcto
echo "<h3>Sincronizando conductores...</h3>";
foreach ($desincronizados as $row) {
    $update = $conn->prepare("UPDATE cond SET cargo = ? WHERE id = ?");
    $update->execute([$row['nombre'], $row['id']]);
    echo "Actualizado cond.id={$row['id']} a cargo='{$row['nombre']}'<br>";
}
echo "<b>Sincronización completada.</b>";
