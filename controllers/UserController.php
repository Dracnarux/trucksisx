<?php
// Controlador para gestión de usuarios y técnicos
if (session_status() === PHP_SESSION_NONE) session_start();

require_once '../models/User.php';
$userModel = new User();

if (isset($_GET['action']) && $_GET['action'] === 'update' && isset($_GET['id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_GET['id']);
        $data = [
            'nombre' => $_POST['nombre'] ?? '',
            'apellido' => $_POST['apellido'] ?? '',
            'num_celular' => $_POST['num_celular'] ?? '',
            'correo' => $_POST['correo'] ?? '',
            'contrasena' => $_POST['contrasena'] ?? ''
        ];
        if (empty($data['contrasena'])) {
            unset($data['contrasena']);
        }
        $userModel->update($id, $data);
        header('Location: ../views/crear_usuario.php?success=2');
        exit;
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $userModel->delete($id);
    header('Location: ../views/crear_usuario.php?success=3');
    exit;
} elseif (isset($_GET['action']) && $_GET['action'] === 'create_tecnico') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Recoger datos del formulario
        $especialidad = $_POST['especialidad'] ?? '';
        $nivel_experiencia = $_POST['nivel_experiencia'] ?? '';
        $categoria = $_POST['categoria'] ?? '';
        echo '<div style="margin:2em; font-family:Arial;">';
        echo '<h2>Técnico creado correctamente</h2>';
        echo '<ul>';
        echo '<li><b>Especialidad:</b> ' . htmlspecialchars($especialidad) . '</li>';
        echo '<li><b>Nivel de experiencia:</b> ' . htmlspecialchars($nivel_experiencia) . '</li>';
        echo '<li><b>Categoría:</b> ' . htmlspecialchars($categoria) . '</li>';
        echo '</ul>';
        echo '<a href="../views/dashboard.php">Volver al dashboard</a>';
        echo '</div>';
        exit;
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'create') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'num_documento' => $_POST['num_documento'] ?? '',
            'tipo_documento' => $_POST['tipo_documento'] ?? '',
            'nombre' => $_POST['nombre'] ?? '',
            'apellido' => $_POST['apellido'] ?? '',
            'num_celular' => $_POST['num_celular'] ?? '',
            'correo' => $_POST['correo'] ?? '',
            'rol' => $_POST['rol'] ?? '',
            'contrasena' => $_POST['contrasena'] ?? ''
        ];
        $userId = $userModel->create($data);
        if ($userId) {
            if ($data['rol'] === 'conductor') {
                header('Location: ../views/cond.php');
                exit;
            } elseif ($data['rol'] === 'tecnico') {
                header('Location: ../views/crear_tecnico.php');
                exit;
            } else {
                header('Location: ../views/crear_usuario.php?success=1');
                exit;
            }
        } else {
            header('Location: ../views/crear_usuario.php?error=1');
            exit;
        }
    }
}

// Si no hay acción válida, mostrar error
http_response_code(404);
echo 'Acción no válida o no encontrada.';
// Controlador para gestión de usuarios y técnicos
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_GET['action']) && $_GET['action'] === 'create_tecnico') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Recoger datos del formulario
        $especialidad = $_POST['especialidad'] ?? '';
        $nivel_experiencia = $_POST['nivel_experiencia'] ?? '';
        $categoria = $_POST['categoria'] ?? '';

        // Aquí deberías guardar en la base de datos, por ahora solo mostramos los datos
        // Puedes adaptar esto para guardar en una tabla de técnicos si la tienes
        echo '<div style="margin:2em; font-family:Arial;">';
        echo '<h2>Técnico creado correctamente</h2>';
        echo '<ul>';
        echo '<li><b>Especialidad:</b> ' . htmlspecialchars($especialidad) . '</li>';
        echo '<li><b>Nivel de experiencia:</b> ' . htmlspecialchars($nivel_experiencia) . '</li>';
        echo '<li><b>Categoría:</b> ' . htmlspecialchars($categoria) . '</li>';
        echo '</ul>';
        echo '<a href="../views/dashboard.php">Volver al dashboard</a>';
        echo '</div>';
        exit;
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'create') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once '../models/User.php';
        $userModel = new User();
        $data = [
            'num_documento' => $_POST['num_documento'] ?? '',
            'tipo_documento' => $_POST['tipo_documento'] ?? '',
            'nombre' => $_POST['nombre'] ?? '',
            'apellido' => $_POST['apellido'] ?? '',
            'num_celular' => $_POST['num_celular'] ?? '',
            'correo' => $_POST['correo'] ?? '',
            'rol' => $_POST['rol'] ?? '',
            'contrasena' => $_POST['contrasena'] ?? ''
        ];
        $userId = $userModel->create($data);
        if ($userId) {
            // Redirigir según el rol
            if ($data['rol'] === 'conductor') {
                header('Location: ../views/cond.php');
                exit;
            } elseif ($data['rol'] === 'tecnico') {
                header('Location: ../views/crear_tecnico.php');
                exit;
            } else {
                header('Location: ../views/crear_usuario.php?success=1');
                exit;
            }
        } else {
            header('Location: ../views/crear_usuario.php?error=1');
            exit;
        }
    }
}

// Aquí puedes agregar más acciones para usuarios
// ...

// Si no hay acción válida, mostrar error
http_response_code(404);
echo 'Acción no válida o no encontrada.';
