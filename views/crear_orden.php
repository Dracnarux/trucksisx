<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
// Verificar que no sea conductor
if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor') {
    header('Location: orden_trabajo.php');
    exit();
}

require_once '../config/db.php';
$conn = conectarDB();
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
    
    // Validar que el conductor existe si se seleccionó uno
    if (!empty($cond_id)) {
        $check_stmt = $conn->prepare("SELECT id FROM cond WHERE id = ?");
        $check_stmt->bind_param('i', $cond_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows == 0) {
            $cond_id = null; // Si no existe, lo ponemos como null
        }
        $check_stmt->close();
    } else {
        $cond_id = null;
    }

    $stmt = $conn->prepare("INSERT INTO ord_trabj (nombre_trabajo, descripcion, nombre_repuesto, fecha_creacion, fecha_estimada, estado, prioridad, cond_id, users_id, alert_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssssiiii', $nombre_trabajo, $descripcion, $nombre_repuesto, $fecha_creacion, $fecha_estimada, $estado, $prioridad, $cond_id, $users_id, $alert_id);
    
    if ($stmt->execute()) {
        // Si hay una alerta asociada, actualizar su estado según el estado de la orden
        if ($alert_id && !empty($alert_id)) {
            $alert_estado = '';
            switch ($estado) {
                case 'completada':
                    $alert_estado = 'resuelta';
                    break;
                case 'en_progreso':
                    $alert_estado = 'en_proceso';
                    break;
                case 'pendiente':
                    $alert_estado = 'activa';
                    break;
                case 'cancelada':
                    $alert_estado = 'cancelada';
                    break;
                default:
                    $alert_estado = 'activa';
            }
            
            $alert_stmt = $conn->prepare("UPDATE alert SET estado=? WHERE id=?");
            $alert_stmt->bind_param('si', $alert_estado, $alert_id);
            $alert_stmt->execute();
            $alert_stmt->close();
        }
        
        $stmt->close();
        $conn->close();
        
        // Redireccionar con mensaje de éxito
        $mensaje = urlencode("La orden de trabajo '$nombre_trabajo' ha sido creada exitosamente.");
        header("Location: orden_trabajo.php?mensaje=$mensaje&tipo=exito");
        exit;
    } else {
        $stmt->close();
        $conn->close();
        
        // Redireccionar con mensaje de error
        $mensaje = urlencode("Error al crear la orden de trabajo. Por favor, inténtelo nuevamente.");
        header("Location: orden_trabajo.php?mensaje=$mensaje&tipo=error");
        exit;
    }
}
?>