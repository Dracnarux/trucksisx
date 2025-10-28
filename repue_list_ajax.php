<?php
// Establecer header JSON primero
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/models/Repue.php';
    
    $repue = new Repue();
    $res = $repue->getAll();
    $lista = [];
    
    if ($res && $res->num_rows > 0) {
        while ($r = $res->fetch_assoc()) {
            $lista[] = [
                'id' => (int)$r['id'],
                'nombre' => $r['nombre'] ?? 'Sin nombre',
                'marca_repuesto' => $r['marca_repuesto'] ?? 'Sin marca'
            ];
        }
    }
    
    echo json_encode($lista, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // En caso de error, devolver mensaje de error en español
    echo json_encode(['error' => 'Error al cargar repuestos: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
