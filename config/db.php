<?php
$host = 'localhost';
$dbname = 'trucksisx';
$user = 'root';
$pass = '';
try {
    $GLOBALS['db'] = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $GLOBALS['db']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
