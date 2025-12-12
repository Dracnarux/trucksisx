<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/SaliVehi.php';

// Definir la conexión global si no existe
if (!isset($GLOBALS['db']) || !$GLOBALS['db']) {
    $GLOBALS['db'] = conectarDB();
}

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
            // Validar que el ID de salida de repuesto existe
            require_once __DIR__ . '/../models/SaliRepue.php';
            $saliRepueModel = new SaliRepue($GLOBALS['db']);
            $saliRepue = $saliRepueModel->getById($_POST['sali_repue_id']);
            if (!$saliRepue) {
                die('El identificador de salida de repuesto seleccionado no existe.');
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
            $id = $this->model->registrarSalida($data);
            if ($id) {
                // Generar código de reporte automáticamente
                $codigo_reporte = 'REP-VEHI-' . date('Ymd-His');
                $tipo_reporte = 'Salida de Vehículo';
                $fecha_creacion = date('Y-m-d');
                $this->model->crearReporte([
                    'nombre_reporte' => $codigo_reporte,
                    'tipo_reporte' => $tipo_reporte,
                    'fecha_creacion' => $fecha_creacion,
                    'activo' => 1,
                    'sali_vehi_id' => $id
                ]);
                // Redirigir o responder con éxito
                header('Location: ../views/reporte_final.php?sali_vehi_id=' . $id);
                exit();
            } else {
                echo 'Error al registrar la salida de vehículo.';
            }
        }
    }

    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            
            if ($id <= 0) {
                header('Location: ../views/salida_vehiculo.php?error=id_invalido');
                exit();
            }

            // Verificar que la salida existe
            $salidaExistente = $this->model->getById($id);
            if (!$salidaExistente) {
                header('Location: ../views/salida_vehiculo.php?error=no_encontrado');
                exit();
            }

            $data = [
                'id_flotas' => $_POST['id_flotas'],
                'segui_monitoreo' => $_POST['segui_monitoreo'],
                'control_combustible' => $_POST['control_combustible'],
                'cump_regulaciones' => $_POST['cump_regulaciones'],
                'protocolo_seguridad' => $_POST['protocolo_seguridad'],
                'gest_conductores' => $_POST['gest_conductores']
            ];

            $resultado = $this->model->actualizarSalida($id, $data);
            
            if ($resultado) {
                header('Location: ../views/salida_vehiculo.php?success=actualizado');
                exit();
            } else {
                header('Location: ../views/editar_salida_vehiculo.php?id=' . $id . '&error=update_failed');
                exit();
            }
        } else {
            header('Location: ../views/salida_vehiculo.php');
            exit();
        }
    }

    public function eliminar() {
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            
            if ($id <= 0) {
                header('Location: ../views/salida_vehiculo.php?error=id_invalido');
                exit();
            }

            $resultado = $this->model->eliminarSalida($id);
            
            if ($resultado) {
                header('Location: ../views/salida_vehiculo.php?success=eliminado');
                exit();
            } else {
                header('Location: ../views/salida_vehiculo.php?error=delete_failed');
                exit();
            }
        } else {
            header('Location: ../views/salida_vehiculo.php');
            exit();
        }
    }
}

// Enrutamiento simple
if (isset($_GET['action'])) {
    $controller = new SaliVehiController();
    
    switch ($_GET['action']) {
        case 'registrar':
            $controller->registrar();
            break;
        case 'actualizar':
            $controller->actualizar();
            break;
        case 'eliminar':
            $controller->eliminar();
            break;
        default:
            header('Location: ../views/salida_vehiculo.php');
            exit();
    }
} else {
    header('Location: ../views/salida_vehiculo.php');
    exit();
}
