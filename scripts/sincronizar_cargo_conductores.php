<?php
// Script para sincronizar el campo cargo de la tabla cond con el nombre real del usuario conductor según el vehículo asignado
require_once '../config/db.php';

$db = new Database();
$conn = $db->getConnection();

// Buscar todos los conductores en cond con vehículo asignado
$sql = "SELECT c.id as cond_id, c.regis_vehic_id, u.nombre as user_nombre
        FROM cond c
        JOIN users u ON u.rol = 'conductor'
        WHERE c.regis_vehic_id IS NOT NULL
        AND (c.cargo != u.nombre OR c.cargo IS NULL)
        AND u.id IN (
            SELECT u2.id FROM users u2 WHERE u2.rol = 'conductor'
        )";
$stmt = $conn->prepare($sql);
$stmt->execute();
$desincronizados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Actualizar los desincronizados para que cargo = nombre correcto
$count = 0;
foreach ($desincronizados as $row) {
    $update = $conn->prepare("UPDATE cond SET cargo = ? WHERE id = ?");
    $update->execute([$row['user_nombre'], $row['cond_id']]);
    $count++;
    echo "Actualizado cond.id={$row['cond_id']} a cargo='{$row['user_nombre']}'<br>\n";
}
echo "<b>Sincronización completada. Total actualizados: $count</b>";
