<?php
require_once 'models/User.php';

class LoginController {
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = new User();
            $login = $user->login($_POST['usuario'], $_POST['contrasena']);
            if ($login) {
                $_SESSION['usuario'] = $login;
                header('Location: views/dashboard.php');
                exit();
            } else {
                $error = 'Usuario o contraseña incorrectos';
                include 'views/login.php';
            }
        } else {
            include 'views/login.php';
        }
    }
}
