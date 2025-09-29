<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/SaliRepue.php';

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
                'repor_id' => $_POST['repor_id'],
                'alerta_id' => $_POST['alerta_id']
            ];
            $id = $this->model->registrarSalida($data);
            if ($id) {
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
