<?php
// Script para forzar la relación correcta para el usuario 'Juan Montero'
require_once '../config/db.php';

$db = new Database();
$conn = $db->getConnection();

// Buscar el id del usuario y el id del vehículo asignado
$stmt = $conn->prepare("SELECT id FROM users WHERE nombre = 'Juan' AND apellido = 'Montero' AND rol = 'conductor' LIMIT 1");
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<b style='color:red'>No existe el usuario Juan Montero con rol conductor en la tabla users.</b>";
    exit;
}

// Buscar el registro de cond que debe actualizarse
$stmt = $conn->prepare("SELECT id FROM cond WHERE cargo != 'Juan Montero' LIMIT 1");
$stmt->execute();
$cond = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cond) {
    echo "<b style='color:green'>Ya existe un registro en cond con cargo = 'Juan Montero'.</b>";
    exit;
}

// Actualizar el campo cargo en cond
$update = $conn->prepare("UPDATE cond SET cargo = 'Juan Montero' WHERE id = ?");
$update->execute([$cond['id']]);
echo "<b style='color:green'>Actualizado cond.id={$cond['id']} a cargo='Juan Montero'. Ahora debería aparecer en el modal.</b>";
