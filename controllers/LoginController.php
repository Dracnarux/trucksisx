<?php
require_once 'models/User.php';

class LoginController {
    public function handleRequest() {
        // Generar token CSRF si no existe
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar token CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $error = 'Token de seguridad inválido. Por favor, intente de nuevo.';
                include 'views/login.php';
                return;
            }
            
            // Validar datos de entrada
            if (empty($_POST['usuario']) || empty($_POST['contrasena'])) {
                $error = 'Por favor, complete todos los campos';
                include 'views/login.php';
                return;
            }
            
            // Protección contra ataques de fuerza bruta
            $max_attempts = 5;
            $lockout_time = 900; // 15 minutos
            
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 0;
                $_SESSION['last_attempt'] = 0;
            }
            
            if ($_SESSION['login_attempts'] >= $max_attempts) {
                $time_remaining = $lockout_time - (time() - $_SESSION['last_attempt']);
                if ($time_remaining > 0) {
                    $error = "Demasiados intentos fallidos. Intente de nuevo en " . ceil($time_remaining/60) . " minutos.";
                    include 'views/login.php';
                    return;
                } else {
                    $_SESSION['login_attempts'] = 0;
                }
            }
            
            $user = new User();
            $login = $user->login($_POST['usuario'], $_POST['contrasena']);
            
            if ($login) {
                // Regenerar session ID por seguridad
                session_regenerate_id(true);
                
                // Limpiar intentos de login
                $_SESSION['login_attempts'] = 0;
                unset($_SESSION['last_attempt']);
                
                // Guardar información del usuario
                $_SESSION['usuario'] = $login;
                $_SESSION['login_time'] = time();
                $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
                
                // Generar nuevo token CSRF
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
                header('Location: views/dashboard.php');
                exit();
            } else {
                // Incrementar intentos fallidos
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt'] = time();
                
                $error = 'Usuario o contraseña incorrectos';
                include 'views/login.php';
            }
        } else {
            include 'views/login.php';
        }
    }
    
    public function logout() {
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
        $_SESSION['logout_message'] = 'Sesión cerrada correctamente';
        
        // Headers para evitar caché
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        header("Pragma: no-cache");
        
        // Redirección
        header('Location: ' . $this->getBaseUrl() . '/index.php');
        exit();
    }
    
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $script_path = $_SERVER['SCRIPT_NAME'];
        $base_path = dirname(dirname($script_path)); // Subir dos niveles desde controllers/
        $base_path = rtrim($base_path, '/');
        
        return $protocol . '://' . $host . $base_path;
    }
}
