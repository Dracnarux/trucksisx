<?php
/**
 * Script de prueba para verificar el funcionamiento del logout
 * Este archivo puede eliminarse después de las pruebas
 */

session_start();

echo "<h2>🔐 Prueba del Sistema de Logout</h2>\n";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>\n";

echo "<h3>📊 Estado Actual de la Sesión</h3>\n";

if (isset($_SESSION['usuario'])) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "<strong>✅ Sesión Activa</strong><br>\n";
    echo "Usuario: " . htmlspecialchars($_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellido']) . "<br>\n";
    echo "Rol: " . htmlspecialchars($_SESSION['usuario']['rol']) . "<br>\n";
    if (isset($_SESSION['login_time'])) {
        echo "Login: " . date('Y-m-d H:i:s', $_SESSION['login_time']) . "<br>\n";
    }
    if (isset($_SESSION['last_activity'])) {
        echo "Última actividad: " . date('Y-m-d H:i:s', $_SESSION['last_activity']) . "<br>\n";
    }
    echo "</div>\n";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "<strong>❌ Sin Sesión</strong><br>\n";
    echo "No hay usuario autenticado\n";
    echo "</div>\n";
}

if (isset($_SESSION['logout_message'])) {
    echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "<strong>💬 Mensaje de Logout:</strong> " . htmlspecialchars($_SESSION['logout_message']) . "\n";
    echo "</div>\n";
    unset($_SESSION['logout_message']);
}

echo "<h3>🔗 Enlaces de Prueba</h3>\n";

if (isset($_SESSION['usuario'])) {
    echo "<div style='margin: 15px 0;'>\n";
    echo "<a href='logout.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🚪 Cerrar Sesión (logout.php)</a>\n";
    echo "<a href='index.php?logout=1' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚪 Logout Clásico (index.php?logout=1)</a>\n";
    echo "</div>\n";
} else {
    echo "<div style='margin: 15px 0;'>\n";
    echo "<a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔑 Ir al Login</a>\n";
    echo "</div>\n";
}

echo "<h3>🔧 Información Técnica</h3>\n";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr><th style='padding: 10px; background: #f8f9fa;'>Variable</th><th style='padding: 10px; background: #f8f9fa;'>Valor</th></tr>\n";

$session_vars = [
    'Session ID' => session_id(),
    'Session Name' => session_name(),
    'Session Status' => session_status() === PHP_SESSION_ACTIVE ? 'Activa' : 'Inactiva',
    'HTTP Host' => $_SERVER['HTTP_HOST'] ?? 'No definido',
    'Script Name' => $_SERVER['SCRIPT_NAME'] ?? 'No definido',
    'Request URI' => $_SERVER['REQUEST_URI'] ?? 'No definido',
];

foreach ($session_vars as $var => $value) {
    echo "<tr>";
    echo "<td style='padding: 8px; font-weight: bold;'>$var</td>";
    echo "<td style='padding: 8px;'>" . htmlspecialchars($value) . "</td>";
    echo "</tr>\n";
}

echo "</table>\n";

echo "<h3>✅ Correcciones Implementadas</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>logout.php independiente</strong> - Archivo dedicado para cerrar sesión</li>\n";
echo "<li>✅ <strong>Limpieza completa de sesión</strong> - $_SESSION, cookies y destrucción</li>\n";
echo "<li>✅ <strong>Headers anti-caché</strong> - Evita problemas de navegador</li>\n";
echo "<li>✅ <strong>Redirección absoluta</strong> - URL completa para evitar problemas de ruta</li>\n";
echo "<li>✅ <strong>Enlaces actualizados</strong> - Dashboard usa logout.php</li>\n";
echo "<li>✅ <strong>Compatibilidad</strong> - Mantiene soporte para ?logout=1</li>\n";
echo "</ul>\n";

echo "<hr>\n";
echo "<p><em>Prueba realizada el " . date('Y-m-d H:i:s') . "</em></p>\n";

if (isset($_SESSION['usuario'])) {
    echo "<p><a href='views/dashboard.php'>← Volver al Dashboard</a></p>\n";
} else {
    echo "<p><a href='index.php'>← Ir al Login</a></p>\n";
}
?>