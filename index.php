<?php
session_start();
require_once 'config/db.php';
require_once 'controllers/LoginController.php';

$controller = new LoginController();
$controller->handleRequest();
