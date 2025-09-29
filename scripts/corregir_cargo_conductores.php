<?php
// Script para corregir automáticamente el campo cargo en cond para que coincida con el nombre completo del usuario conductor
require_once '../config/db.php';

$db = new Database();
$conn = $db->getConnection();

// Buscar todos los conductores en cond con vehículo asignado
$sql = "SELECT c.id as cond_id, c.cargo, c.regis_vehic_id, u.nombre, u.apellido
        FROM cond c
        JOIN users u ON u.rol = 'conductor'
        WHERE c.regis_vehic_id IS NOT NULL
        AND (c.cargo != u.nombre AND c.cargo != CONCAT(u.nombre, ' ', u.apellido))";
$stmt = $conn->prepare($sql);
$stmt->execute();
$desincronizados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count = 0;
foreach ($desincronizados as $row) {
    // Actualizar cargo al nombre completo
    $nuevoCargo = $row['nombre'] . ' ' . $row['apellido'];
    $update = $conn->prepare("UPDATE cond SET cargo = ? WHERE id = ?");
    $update->execute([$nuevoCargo, $row['cond_id']]);
    $count++;
    echo "Actualizado cond.id={$row['cond_id']} a cargo='{$nuevoCargo}'<br>\n";
}
echo "<b>Corrección completada. Total actualizados: $count</b>";
