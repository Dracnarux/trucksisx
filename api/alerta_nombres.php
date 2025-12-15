<?php
require_once '../models/Alert.php';
header('Content-Type: application/json');
$alertModel = new Alert();
$alertas = $alertModel->getAll();
$opciones = [];
foreach ($alertas as $alerta) {
    $nombre = $alerta['posicion_llanta'] ?? $alerta['descripcion'] ?? $alerta['titulo'] ?? '';
    if ($nombre && !in_array($nombre, $opciones)) {
        $opciones[] = $nombre;
    }
}
echo json_encode(['success' => true, 'data' => $opciones]);
