<?php
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        $db = conectarDB();
        
        // Verificar si la salida existe antes de eliminar
        $checkSalida = $db->prepare("SELECT * FROM sali_repue WHERE id = ?");
        $checkSalida->bind_param('i', $id);
        $checkSalida->execute();
        $salida = $checkSalida->get_result()->fetch_assoc();
        
        if (!$salida) {
            echo json_encode(['success' => false, 'message' => 'La salida no existe']);
            exit;
        }
        
        // Verificar FK constraints
        $constraints = [];
        
        // Verificar repor
        if ($salida['repor_id']) {
            $checkRepor = $db->prepare("SELECT COUNT(*) as count FROM repor WHERE id = ?");
            $checkRepor->bind_param('i', $salida['repor_id']);
            $checkRepor->execute();
            $reporCount = $checkRepor->get_result()->fetch_assoc()['count'];
            $constraints['repor'] = $reporCount;
        }
        
        // Verificar sali_vehi
        $checkSaliVehi = $db->prepare("SELECT COUNT(*) as count FROM sali_vehi WHERE sali_repue_id = ?");
        $checkSaliVehi->bind_param('i', $id);
        $checkSaliVehi->execute();
        $saliVehiCount = $checkSaliVehi->get_result()->fetch_assoc()['count'];
        $constraints['sali_vehi'] = $saliVehiCount;
        
        echo json_encode([
            'success' => true,
            'salida' => $salida,
            'constraints' => $constraints,
            'can_delete' => true
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
}
?>