<?php
// Script para mostrar todos los usuarios conductores y sugerir el valor exacto para el campo cargo
require_once '../config/db.php';

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT id, nombre, apellido FROM users WHERE rol = 'conductor'");
$stmt->execute();
$conductores = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Usuarios conductores registrados</h2>";
echo '<table border="1" cellpadding="5"><tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Valor sugerido para campo cargo</th></tr>';
foreach ($conductores as $c) {
    $nombreCompleto = $c['nombre'] . ' ' . $c['apellido'];
    echo "<tr><td>{$c['id']}</td><td>{$c['nombre']}</td><td>{$c['apellido']}</td><td style='color:green;font-weight:bold'>{$nombreCompleto}</td></tr>";
}
echo '</table>';

echo '<p style="color:blue">Para que la relación funcione, el campo <b>cargo</b> en la tabla <b>cond</b> debe ser exactamente igual a la columna "Valor sugerido para campo cargo".</p>';
