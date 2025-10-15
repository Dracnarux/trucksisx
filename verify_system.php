<?php
/**
 * Script de Verificación del Sistema TruckSISX
 * Ejecuta una serie de pruebas para validar el estado del código
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Verificación del Sistema TruckSISX</h1>\n";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Verificar archivos críticos
echo "<h2>📁 Verificación de Archivos Críticos</h2>\n";

$critical_files = [
    'config/db.php' => 'Configuración de base de datos',
    'index.php' => 'Página de inicio',
    'controllers/LoginController.php' => 'Controlador de login',
    'models/User.php' => 'Modelo de usuario',
    'views/login.php' => 'Vista de login',
    'views/dashboard.php' => 'Panel principal',
    'views/crear_usuario.php' => 'Gestión de usuarios',
    'views/truck_alerts.php' => 'Sistema de alertas',
    'db.sql' => 'Estructura de base de datos'
];

foreach ($critical_files as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        if ($size > 0) {
            $success[] = "✅ $description ($file) - $size bytes";
        } else {
            $warnings[] = "⚠️ $description ($file) está vacío";
        }
    } else {
        $errors[] = "❌ $description ($file) no existe";
    }
}

// 2. Verificar sintaxis PHP
echo "<h2>🔧 Verificación de Sintaxis PHP</h2>\n";

$php_files = glob('*.php');
$php_files = array_merge($php_files, glob('*/*.php'));
$php_files = array_merge($php_files, glob('*/*/*.php'));

$syntax_errors = 0;
foreach ($php_files as $file) {
    if (is_file($file)) {
        $output = [];
        $return_var = 0;
        exec("php -l \"$file\" 2>&1", $output, $return_var);
        
        if ($return_var !== 0) {
            $syntax_errors++;
            $errors[] = "❌ Error de sintaxis en $file: " . implode(' ', $output);
        }
    }
}

if ($syntax_errors === 0) {
    $success[] = "✅ Todos los archivos PHP tienen sintaxis correcta";
}

// 3. Verificar conexión a base de datos (simulada)
echo "<h2>🗄️ Verificación de Configuración de Base de Datos</h2>\n";

if (file_exists('config/db.php')) {
    $db_content = file_get_contents('config/db.php');
    
    // Verificar que tenga las clases necesarias
    if (strpos($db_content, 'class Database') !== false) {
        $success[] = "✅ Clase Database encontrada";
    } else {
        $errors[] = "❌ Clase Database no encontrada";
    }
    
    if (strpos($db_content, 'function conectarDB') !== false) {
        $success[] = "✅ Función conectarDB encontrada";
    } else {
        $warnings[] = "⚠️ Función conectarDB no encontrada";
    }
    
    // Verificar configuración
    if (strpos($db_content, 'localhost') !== false && strpos($db_content, 'trucksisx') !== false) {
        $success[] = "✅ Configuración de base de datos parece correcta";
    } else {
        $warnings[] = "⚠️ Revisar configuración de base de datos";
    }
}

// 4. Verificar estructura MVC
echo "<h2>🏗️ Verificación de Estructura MVC</h2>\n";

$mvc_structure = [
    'controllers' => 'Controladores',
    'models' => 'Modelos',
    'views' => 'Vistas',
    'config' => 'Configuración',
    'assets' => 'Recursos estáticos'
];

foreach ($mvc_structure as $dir => $description) {
    if (is_dir($dir)) {
        $files_count = count(glob("$dir/*"));
        $success[] = "✅ Directorio $description ($dir) existe con $files_count archivos";
    } else {
        $errors[] = "❌ Directorio $description ($dir) no existe";
    }
}

// 5. Verificar SQL
echo "<h2>🗃️ Verificación de Estructura SQL</h2>\n";

if (file_exists('db.sql')) {
    $sql_content = file_get_contents('db.sql');
    
    // Verificar tablas principales
    $required_tables = ['users', 'alert', 'ord_trabj', 'regis_vehic', 'cond', 'repue'];
    $missing_tables = [];
    
    foreach ($required_tables as $table) {
        if (strpos($sql_content, "CREATE TABLE $table") !== false) {
            $success[] = "✅ Tabla $table definida correctamente";
        } else {
            $missing_tables[] = $table;
        }
    }
    
    if (!empty($missing_tables)) {
        $errors[] = "❌ Tablas faltantes: " . implode(', ', $missing_tables);
    }
    
    // Verificar usuarios por defecto
    if (strpos($sql_content, 'INSERT INTO users') !== false) {
        $success[] = "✅ Usuarios por defecto incluidos";
    } else {
        $warnings[] = "⚠️ No se encontraron usuarios por defecto en el SQL";
    }
}

// 6. Verificar seguridad básica
echo "<h2>🔒 Verificación de Seguridad Básica</h2>\n";

// Verificar que el LoginController tenga protección CSRF
if (file_exists('controllers/LoginController.php')) {
    $login_content = file_get_contents('controllers/LoginController.php');
    
    if (strpos($login_content, 'csrf_token') !== false) {
        $success[] = "✅ Protección CSRF implementada";
    } else {
        $warnings[] = "⚠️ Protección CSRF no detectada";
    }
    
    if (strpos($login_content, 'hash') !== false || strpos($login_content, 'SHA') !== false) {
        $success[] = "✅ Hash de contraseñas implementado";
    } else {
        $errors[] = "❌ Hash de contraseñas no detectado";
    }
    
    if (strpos($login_content, 'session_regenerate_id') !== false) {
        $success[] = "✅ Regeneración de ID de sesión implementada";
    } else {
        $warnings[] = "⚠️ Regeneración de ID de sesión no detectada";
    }
}

// 7. Mostrar resultados
echo "<h2>📋 Resumen de la Verificación</h2>\n";

echo "<h3 style='color: green;'>✅ Elementos Correctos (" . count($success) . ")</h3>\n";
foreach ($success as $item) {
    echo "<p>$item</p>\n";
}

if (!empty($warnings)) {
    echo "<h3 style='color: orange;'>⚠️ Advertencias (" . count($warnings) . ")</h3>\n";
    foreach ($warnings as $item) {
        echo "<p>$item</p>\n";
    }
}

if (!empty($errors)) {
    echo "<h3 style='color: red;'>❌ Errores Críticos (" . count($errors) . ")</h3>\n";
    foreach ($errors as $item) {
        echo "<p>$item</p>\n";
    }
}

// 8. Puntuación general
$total_checks = count($success) + count($warnings) + count($errors);
$score = round((count($success) / $total_checks) * 100);

echo "<h2>🎯 Puntuación General del Sistema</h2>\n";
echo "<div style='font-size: 2em; text-align: center; padding: 20px;'>";

if ($score >= 90) {
    echo "<span style='color: green;'>🟢 $score% - EXCELENTE</span>";
    echo "<br><small>El sistema está en muy buen estado</small>";
} elseif ($score >= 75) {
    echo "<span style='color: orange;'>🟡 $score% - BUENO</span>";
    echo "<br><small>El sistema funciona bien con algunas mejoras menores</small>";
} elseif ($score >= 60) {
    echo "<span style='color: orange;'>🟠 $score% - ACEPTABLE</span>";
    echo "<br><small>El sistema requiere algunas correcciones</small>";
} else {
    echo "<span style='color: red;'>🔴 $score% - REQUIERE ATENCIÓN</span>";
    echo "<br><small>El sistema necesita correcciones importantes</small>";
}

echo "</div>\n";

echo "<hr>\n";
echo "<p><em>Verificación completada el " . date('Y-m-d H:i:s') . "</em></p>\n";
?>