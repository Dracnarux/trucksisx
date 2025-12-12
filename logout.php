<?php
/**
 * Logout Controller - Maneja el cierre de sesión de forma independiente
 */

session_start();

// Función para redirección segura
function redirect_to_login($message = '') {
    // Limpiar completamente la sesión
    $_SESSION = array();
    
    // Eliminar la cookie de sesión si existe
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destruir la sesión
    session_destroy();
    
    // Iniciar nueva sesión para el mensaje
    session_start();
    if (!empty($message)) {
        $_SESSION['logout_message'] = $message;
    }
    
    // Headers para evitar caché
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    header("Pragma: no-cache");
    
    // Redirección absoluta
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base_path = dirname($_SERVER['SCRIPT_NAME']);
    $base_path = rtrim($base_path, '/');
    
    $redirect_url = $protocol . '://' . $host . $base_path . '/index.php';
    
    header("Location: " . $redirect_url);
    exit();
}

// Verificar si hay sesión activa
if (!isset($_SESSION['usuario'])) {
    redirect_to_login('No hay sesión activa');
}

// Procesar logout
redirect_to_login('Sesión cerrada correctamente');
?>