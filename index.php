<?php
session_start();
require_once 'config/db.php';
require_once 'controllers/LoginController.php';

// Handle logout (mantener compatibilidad con enlaces antiguos)
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    $controller = new LoginController();
    $controller->logout();
    exit();
}

// Redirect if already logged in
if (isset($_SESSION['usuario'])) {
    // Validar que la sesión sea válida
    if (isset($_SESSION['login_time'])) {
        header('Location: views/dashboard.php');
        exit();
    } else {
        // Sesión inválida, limpiar y continuar con login
        session_unset();
        session_destroy();
        session_start();
    }
}

$controller = new LoginController();
$controller->handleRequest();
