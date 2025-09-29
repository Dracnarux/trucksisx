<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/SaliVehi.php';

class SaliVehiController {
    private $model;
    public function __construct() {
        $this->model = new SaliVehi($GLOBALS['db']);
    }
    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validación obligatoria: debe existir sali_repue_id y coincidir ord_trabj_id
            if (empty($_POST['sali_repue_id'])) {
                die('Debe registrar primero la salida de repuestos.');
            }
            $data = [
                'id_flotas' => $_POST['id_flotas'],
                'segui_monitoreo' => $_POST['segui_monitoreo'],
                'control_combustible' => $_POST['control_combustible'],
                'cump_regulaciones' => $_POST['cump_regulaciones'],
                'protocolo_seguridad' => $_POST['protocolo_seguridad'],
                'gest_conductores' => $_POST['gest_conductores'],
                'repor_id' => $_POST['repor_id'],
                'ord_trabj_id' => $_POST['ord_trabj_id'],
                'alerta_id' => $_POST['alerta_id'],
                'sali_repue_id' => $_POST['sali_repue_id']
            ];
            // Aquí se puede agregar validación de coincidencia de ord_trabj_id con la salida de repuestos
            $id = $this->model->registrarSalida($data);
            if ($id) {
                // Redirigir o responder con éxito
                header('Location: ../views/reporte_final.php?sali_vehi_id=' . $id);
                exit();
            } else {
                echo 'Error al registrar la salida de vehículo.';
            }
        }
    }
}

// Enrutamiento simple
if (isset($_GET['action']) && $_GET['action'] === 'registrar') {
    $controller = new SaliVehiController();
    $controller->registrar();
}
