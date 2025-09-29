<?php
// Script diagnóstico y corrección total para mostrar por qué NO aparecen conductores en el modal
require_once '../config/db.php';

$db = new Database();
$conn = $db->getConnection();

echo "<h2>Tabla users (solo conductores)</h2>";
$stmt = $conn->prepare("SELECT * FROM users WHERE rol = 'conductor'");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo '<pre>' . print_r($users, true) . '</pre>';

echo "<h2>Tabla regis_vehic</h2>";
$stmt = $conn->prepare("SELECT * FROM regis_vehic");
$stmt->execute();
$vehics = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo '<pre>' . print_r($vehics, true) . '</pre>';

echo "<h2>Tabla cond</h2>";
$stmt = $conn->prepare("SELECT * FROM cond");
$stmt->execute();
$conds = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo '<pre>' . print_r($conds, true) . '</pre>';

// Mostrar resultado del JOIN que usa la ventana emergente
$sql = "SELECT c.id as cond_id, c.cargo, u.id as user_id, u.nombre, u.apellido, v.id as vehiculo_id, v.placa
        FROM cond c
        JOIN users u ON (u.nombre = c.cargo OR CONCAT(u.nombre, ' ', u.apellido) = c.cargo)
        JOIN regis_vehic v ON v.id = c.regis_vehic_id
        WHERE u.rol = 'conductor'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$join = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<h2>Resultado del JOIN (lo que debería salir en el modal)</h2>";
echo '<pre>' . print_r($join, true) . '</pre>';

// Si el JOIN está vacío, sugerir corrección automática
if (empty($join)) {
    echo '<h3 style="color:red">No hay coincidencias. Intentando corregir automáticamente...</h3>';
    // Buscar posibles matches por regis_vehic_id
    $sql = "SELECT c.id as cond_id, u.nombre, u.apellido FROM cond c JOIN users u ON u.rol = 'conductor' WHERE c.regis_vehic_id IS NOT NULL";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($candidatos as $row) {
        $nuevoCargo = $row['nombre'] . ' ' . $row['apellido'];
        $update = $conn->prepare("UPDATE cond SET cargo = ? WHERE id = ?");
        $update->execute([$nuevoCargo, $row['cond_id']]);
        echo "Actualizado cond.id={$row['cond_id']} a cargo='{$nuevoCargo}'<br>\n";
    }
    echo '<b>Corrección aplicada. Recarga esta página y luego prueba el modal nuevamente.</b>';
}
