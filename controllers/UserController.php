<?php
// Controlador para gestión de usuarios - Optimizado para modales
if (session_status() === PHP_SESSION_NONE) session_start();

// Validar que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}

// Solo administradores pueden gestionar usuarios
if ($_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../views/dashboard.php?error=no_permission');
    exit();
}

require_once '../models/User.php';
$userModel = new User();

// Función para redireccionar con mensajes
function redirectWithMessage($success, $message) {
    $tipo = $success ? 'exito' : 'error';
    $encodedMessage = urlencode($message);
    header("Location: ../views/crear_usuario.php?tipo={$tipo}&mensaje={$encodedMessage}");
    exit();
}

// Manejar acciones
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validar datos requeridos
                $requiredFields = ['num_documento', 'tipo_documento', 'nombre', 'apellido', 'rol', 'contrasena'];
                foreach ($requiredFields as $field) {
                    if (empty($_POST[$field])) {
                        redirectWithMessage(false, "El campo {$field} es obligatorio");
                    }
                }
                
                $data = [
                    'num_documento' => trim($_POST['num_documento']),
                    'tipo_documento' => $_POST['tipo_documento'],
                    'nombre' => trim($_POST['nombre']),
                    'apellido' => trim($_POST['apellido']),
                    'num_celular' => trim($_POST['num_celular'] ?? ''),
                    'correo' => trim($_POST['correo'] ?? ''),
                    'rol' => $_POST['rol'],
                    'contrasena' => $_POST['contrasena']
                ];
                
                // Validar longitud de contraseña
                if (strlen($data['contrasena']) < 6) {
                    redirectWithMessage(false, 'La contraseña debe tener al menos 6 caracteres');
                }
                
                $userId = $userModel->create($data);
                if ($userId) {
                    $nombreCompleto = $data['nombre'] . ' ' . $data['apellido'];
                    redirectWithMessage(true, "Usuario '{$nombreCompleto}' creado exitosamente");
                } else {
                    redirectWithMessage(false, 'Error al crear el usuario. Verifique que el documento no esté duplicado');
                }
            } catch (Exception $e) {
                redirectWithMessage(false, 'Error interno: ' . $e->getMessage());
            }
        }
        break;
        
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            try {
                $id = intval($_POST['id']);
                
                // Validar datos requeridos
                if (empty($_POST['nombre']) || empty($_POST['apellido']) || empty($_POST['rol'])) {
                    redirectWithMessage(false, 'Los campos nombre, apellido y rol son obligatorios');
                }
                
                $data = [
                    'nombre' => trim($_POST['nombre']),
                    'apellido' => trim($_POST['apellido']),
                    'num_celular' => trim($_POST['num_celular'] ?? ''),
                    'correo' => trim($_POST['correo'] ?? ''),
                    'rol' => $_POST['rol']
                ];
                
                // Solo actualizar contraseña si se proporciona
                if (!empty($_POST['contrasena'])) {
                    if (strlen($_POST['contrasena']) < 6) {
                        redirectWithMessage(false, 'La nueva contraseña debe tener al menos 6 caracteres');
                    }
                    $data['contrasena'] = $_POST['contrasena'];
                }
                
                $success = $userModel->update($id, $data);
                if ($success) {
                    $nombreCompleto = $data['nombre'] . ' ' . $data['apellido'];
                    redirectWithMessage(true, "Usuario '{$nombreCompleto}' actualizado exitosamente");
                } else {
                    redirectWithMessage(false, 'Error al actualizar el usuario');
                }
            } catch (Exception $e) {
                redirectWithMessage(false, 'Error interno: ' . $e->getMessage());
            }
        }
        break;
        
    case 'delete':
        if (isset($_GET['id'])) {
            try {
                $id = intval($_GET['id']);
                
                // Obtener datos del usuario antes de eliminarlo
                $usuario = $userModel->getById($id);
                if (!$usuario) {
                    redirectWithMessage(false, 'Usuario no encontrado');
                }
                
                $nombreCompleto = $usuario['nombre'] . ' ' . $usuario['apellido'];
                
                $success = $userModel->delete($id);
                if ($success) {
                    redirectWithMessage(true, "Usuario '{$nombreCompleto}' eliminado exitosamente");
                } else {
                    redirectWithMessage(false, 'Error al eliminar el usuario');
                }
            } catch (Exception $e) {
                redirectWithMessage(false, 'Error interno: ' . $e->getMessage());
            }
        }
        break;
        
    default:
        http_response_code(400);
        redirectWithMessage(false, 'Acción no válida');
        break;
}
