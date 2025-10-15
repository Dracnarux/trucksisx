<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

require_once '../config/db.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Obtener estadísticas del dashboard
    $stats = [];
    
    // Total de vehículos
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM regis_vehic");
    $stmt->execute();
    $stats['vehiculos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total de alertas activas
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM alert WHERE estado = 'activa'");
    $stmt->execute();
    $stats['alertas_activas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Alertas por prioridad
    $stmt = $db->prepare("SELECT prioridad, COUNT(*) as total FROM alert WHERE estado = 'activa' GROUP BY prioridad");
    $stmt->execute();
    $alertas_prioridad = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stats['alertas_por_prioridad'] = [];
    foreach ($alertas_prioridad as $ap) {
        $stats['alertas_por_prioridad'][$ap['prioridad']] = $ap['total'];
    }
    
    // Total de órdenes de trabajo pendientes
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM ord_trabj WHERE estado IN ('pendiente', 'en_proceso')");
    $stmt->execute();
    $stats['ordenes_pendientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Órdenes por estado
    $stmt = $db->prepare("SELECT estado, COUNT(*) as total FROM ord_trabj GROUP BY estado");
    $stmt->execute();
    $ordenes_estado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stats['ordenes_por_estado'] = [];
    foreach ($ordenes_estado as $oe) {
        $stats['ordenes_por_estado'][$oe['estado']] = $oe['total'];
    }
    
    // Total de conductores
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM cond");
    $stmt->execute();
    $stats['conductores'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total de repuestos disponibles
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM repue WHERE cant_stock > 0");
    $stmt->execute();
    $stats['repuestos_disponibles'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Alertas recientes (últimas 5)
    $stmt = $db->prepare("
        SELECT a.*, rv.placa, rv.marca_vehiculo 
        FROM alert a 
        LEFT JOIN regis_vehic rv ON a.regis_vehic_id = rv.id 
        ORDER BY a.fecha_hora DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $stats['alertas_recientes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Actividad por día (últimos 7 días)
    $stmt = $db->prepare("
        SELECT DATE(fecha_hora) as fecha, COUNT(*) as total 
        FROM alert 
        WHERE fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(fecha_hora)
        ORDER BY fecha DESC
    ");
    $stmt->execute();
    $stats['actividad_semanal'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Información del usuario actual
    $stats['usuario'] = [
        'nombre' => $_SESSION['usuario']['nombre'],
        'apellido' => $_SESSION['usuario']['apellido'],
        'rol' => $_SESSION['usuario']['rol'],
        'ultimo_acceso' => $_SESSION['last_activity'] ?? time()
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $stats,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage()
    ]);
}
?>