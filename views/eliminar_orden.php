<?php
require_once '../config/db.php';
$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $conn->prepare("DELETE FROM ord_trabj WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}
header('Location: orden_trabajo.php');
exit;
?>