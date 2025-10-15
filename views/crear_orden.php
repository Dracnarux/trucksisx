<?php
require_once '../config/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_trabajo = $_POST['nombre_trabajo'];
    $descripcion = $_POST['descripcion'];
    $nombre_repuesto = $_POST['nombre_repuesto'] ?? '';
    $fecha_estimada = $_POST['fecha_estimada'];
    $estado = $_POST['estado'];
    $prioridad = $_POST['prioridad'];
    $cond_id = $_POST['cond_id'];
    $users_id = $_POST['users_id'];
    $alert_id = $_POST['alert_id'] ?? null;
    $fecha_creacion = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO ord_trabj (nombre_trabajo, descripcion, nombre_repuesto, fecha_creacion, fecha_estimada, estado, prioridad, cond_id, users_id, alert_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssssiii', $nombre_trabajo, $descripcion, $nombre_repuesto, $fecha_creacion, $fecha_estimada, $estado, $prioridad, $cond_id, $users_id, $alert_id);
    $stmt->execute();
    $stmt->close();
    header('Location: orden_trabajo.php');
    exit;
}
?>