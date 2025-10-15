<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/SaliRepue.php';

// Definir la conexión global si no existe
if (!isset($GLOBALS['db']) || !$GLOBALS['db']) {
    $GLOBALS['db'] = conectarDB();
}

class SaliRepueController {
    private $model;
    public function __construct() {
        $this->model = new SaliRepue($GLOBALS['db']);
    }
    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'fecha_salida' => $_POST['fecha_salida'],
                'cantidad' => $_POST['cantidad'],
                'repue_id' => $_POST['repue_id'],
                'ord_trabj_id' => $_POST['ord_trabj_id'],
                'alerta_id' => $_POST['alerta_id']
            ];
            $id = $this->model->registrarSalida($data);
            if ($id) {
                // Generar código de reporte automáticamente
                $codigo_reporte = 'REP-' . date('Ymd-His');
                $tipo_reporte = 'Salida de Repuesto';
                $fecha_creacion = date('Y-m-d');
                $this->model->crearReporte([
                    'nombre_reporte' => $codigo_reporte,
                    'tipo_reporte' => $tipo_reporte,
                    'fecha_creacion' => $fecha_creacion,
                    'activo' => 1,
                    'sali_repue_id' => $id
                ]);
                // Redirigir o responder con éxito
                header('Location: ../views/salida_vehiculo.php?sali_repue_id=' . $id);
                exit();
            } else {
                echo 'Error al registrar la salida de repuesto.';
            }
        }
    }
}

// Enrutamiento simple
if (isset($_GET['action']) && $_GET['action'] === 'registrar') {
    $controller = new SaliRepueController();
    $controller->registrar();
}
