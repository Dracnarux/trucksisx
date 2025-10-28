<?php
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Obtener todas las alertas
    $alerts_sql = "SELECT id, descripcion, estado, prioridad FROM alert ORDER BY id";
    $alerts_stmt = $db->prepare($alerts_sql);
    $alerts_stmt->execute();
    $alerts = $alerts_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $dependencies = [];
    
    // Para cada alerta, verificar sus dependencias
    foreach ($alerts as $alert) {
        $alertId = $alert['id'];
        
        // Verificar ord_trabj
        $ord_sql = "SELECT COUNT(*) as count FROM ord_trabj WHERE alert_id = ?";
        $ord_stmt = $db->prepare($ord_sql);
        $ord_stmt->execute([$alertId]);
        $ord_count = $ord_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Verificar sali_repue
        $repue_sql = "SELECT COUNT(*) as count FROM sali_repue WHERE alerta_id = ?";
        $repue_stmt = $db->prepare($repue_sql);
        $repue_stmt->execute([$alertId]);
        $repue_count = $repue_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Verificar sali_vehi
        $vehi_sql = "SELECT COUNT(*) as count FROM sali_vehi WHERE alerta_id = ?";
        $vehi_stmt = $db->prepare($vehi_sql);
        $vehi_stmt->execute([$alertId]);
        $vehi_count = $vehi_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $dependencies[$alertId] = [
            'ord_trabj' => (int)$ord_count,
            'sali_repue' => (int)$repue_count,
            'sali_vehi' => (int)$vehi_count
        ];
    }
    
    echo json_encode([
        'success' => true,
        'alerts' => $alerts,
        'dependencies' => $dependencies,
        'total_alerts' => count($alerts)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>