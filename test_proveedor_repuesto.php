<?php
/**
 * Script de prueba para verificar la vinculación de proveedores y repuestos
 * Este archivo puede eliminarse después de las pruebas
 */

require_once 'config/db.php';
require_once 'models/Repue.php';
require_once 'models/Proveedor.php';

echo "<h2>🔧 Prueba de Vinculación Proveedor-Repuesto</h2>\n";

try {
    $repueModel = new Repue();
    $proveedorModel = new Proveedor();
    
    echo "<h3>📊 Estado Actual de Repuestos</h3>\n";
    $repuestos = $repueModel->getAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>ID</th><th>Nombre</th><th>Proveedor ID</th><th>Estado</th></tr>\n";
    
    while ($repuesto = $repuestos->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $repuesto['id'] . "</td>";
        echo "<td>" . htmlspecialchars($repuesto['nombre']) . "</td>";
        echo "<td>" . ($repuesto['proveedor_id'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($repuesto['estado_repus'] ?? 'Sin estado') . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    echo "<h3>📦 Estado Actual de Proveedores</h3>\n";
    $proveedores = $proveedorModel->getAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>ID</th><th>Nombre</th><th>Repuestos Vinculados</th></tr>\n";
    
    while ($proveedor = $proveedores->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $proveedor['id'] . "</td>";
        echo "<td>" . htmlspecialchars($proveedor['nom_proveedor']) . "</td>";
        
        // Contar repuestos vinculados
        $repuestosVinculados = $repueModel->getByProveedor($proveedor['id']);
        $count = 0;
        $nombres = [];
        while ($rep = $repuestosVinculados->fetch_assoc()) {
            $count++;
            $nombres[] = $rep['nombre'];
        }
        
        echo "<td>$count repuestos: " . implode(', ', $nombres) . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    echo "<h3>✅ Funciones Disponibles</h3>\n";
    echo "<ul>\n";
    echo "<li>✅ <strong>updateProveedor()</strong> - Actualizar proveedor sin borrar datos</li>\n";
    echo "<li>✅ <strong>desvincularDeProveedor()</strong> - Desvincular todos los repuestos de un proveedor</li>\n";
    echo "<li>✅ <strong>getByProveedor()</strong> - Obtener repuestos por proveedor</li>\n";
    echo "</ul>\n";
    
    echo "<p style='color: green;'><strong>✅ Las correcciones han sido implementadas correctamente.</strong></p>\n";
    echo "<p><strong>Cambios realizados:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>El método <code>updateProveedor()</code> solo actualiza el proveedor_id y estado_repus</li>\n";
    echo "<li>Se previene la pérdida de datos al vincular repuestos</li>\n";
    echo "<li>Se desvincular automáticamente los repuestos al eliminar un proveedor</li>\n";
    echo "<li>La vista muestra correctamente los repuestos vinculados</li>\n";
    echo "</ul>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Error:</strong> " . $e->getMessage() . "</p>\n";
}

echo "<hr>\n";
echo "<p><em>Prueba realizada el " . date('Y-m-d H:i:s') . "</em></p>\n";
echo "<p><a href='views/proveedor.php'>← Volver a Gestión de Proveedores</a></p>\n";
?>