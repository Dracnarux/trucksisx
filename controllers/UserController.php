<?php
// Controlador para gestión de usuarios - Optimizado para modales
if (session_status() === PHP_SESSION_NONE) session_start();

// Validar que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}

// Determinar roles
$es_admin = $_SESSION['usuario']['rol'] === 'admin';
$es_tecnico = $_SESSION['usuario']['rol'] === 'tecnico';

// Solo administradores y técnicos pueden gestionar usuarios
if (!$es_admin && !$es_tecnico) {
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
            // Solo admins pueden crear usuarios
            if (!$es_admin) {
                redirectWithMessage(false, 'No tienes permisos para crear usuarios');
            }
            
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
                
                // Validar que solo haya un Administrador
                if ($data['rol'] === 'admin') {
                    $adminCount = $userModel->countAdministradores();
                    if ($adminCount >= 1) {
                        redirectWithMessage(false, 'Ya existe un usuario Administrador en el sistema. Solo se permite uno activo');
                    }
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
                
                // Si es técnico, verificar que solo edite su propio usuario
                if ($es_tecnico) {
                    $usuarioAEditar = $userModel->getById($id);
                    if (!$usuarioAEditar || $usuarioAEditar['num_documento'] !== $_SESSION['usuario']['num_documento']) {
                        redirectWithMessage(false, 'Solo puedes editar tu propio perfil');
                    }
                }
                
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
                
                // Validar que solo haya un Administrador (solo si se está cambiando el rol a admin)
                $usuarioActual = $userModel->getById($id);
                if ($data['rol'] === 'admin' && $usuarioActual['rol'] !== 'admin') {
                    $adminCount = $userModel->countAdministradores();
                    if ($adminCount >= 1) {
                        redirectWithMessage(false, 'Ya existe un usuario Administrador en el sistema. Solo se permite uno activo');
                    }
                }
                
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
        
    case 'check_dependencies':
        // Verificar dependencias de un usuario vía AJAX
        if (isset($_GET['id'])) {
            header('Content-Type: application/json');
            try {
                $id = intval($_GET['id']);
                $dependencies = $userModel->checkDependencies($id);
                
                echo json_encode([
                    'success' => true,
                    'dependencies' => $dependencies
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
            exit();
        }
        break;
        
    case 'delete':
        if (isset($_GET['id'])) {
            // Solo admins pueden eliminar usuarios
            if (!$es_admin) {
                redirectWithMessage(false, 'No tienes permisos para eliminar usuarios');
            }
            
            try {
                $id = intval($_GET['id']);
                
                // Obtener datos del usuario antes de eliminarlo
                $usuario = $userModel->getById($id);
                if (!$usuario) {
                    redirectWithMessage(false, 'Usuario no encontrado');
                }
                
                $nombreCompleto = $usuario['nombre'] . ' ' . $usuario['apellido'];
                
                // Verificar dependencias antes de eliminar
                $dependencies = $userModel->checkDependencies($id);
                if (!empty($dependencies)) {
                    $mensaje = "No se puede eliminar '{$nombreCompleto}' porque tiene: ";
                    $detalles = [];
                    
                    if (isset($dependencies['ord_trabj'])) {
                        $detalles[] = "{$dependencies['ord_trabj']} orden(es) de trabajo";
                    }
                    
                    $mensaje .= implode(', ', $detalles) . '. Primero debe reasignar o eliminar estos registros.';
                    redirectWithMessage(false, $mensaje);
                }
                
                $success = $userModel->delete($id);
                if ($success) {
                    redirectWithMessage(true, "Usuario '{$nombreCompleto}' eliminado exitosamente");
                } else {
                    redirectWithMessage(false, 'Error al eliminar el usuario');
                }
            } catch (Exception $e) {
                // Capturar errores de FK específicamente
                $errorMsg = $e->getMessage();
                if (strpos($errorMsg, 'foreign key constraint') !== false || strpos($errorMsg, 'Integrity constraint') !== false) {
                    redirectWithMessage(false, 'No se puede eliminar este usuario porque tiene registros asociados (órdenes de trabajo). Primero debe reasignar o eliminar esos registros.');
                } else {
                    redirectWithMessage(false, 'Error: ' . $errorMsg);
                }
            }
        }
        break;
        
    default:
        http_response_code(400);
        redirectWithMessage(false, 'Acción no válida');
        break;
}
