<?php
require_once '../models/User.php';
$userModel = new User();
$adminCount = $userModel->countAdministradores();
?>