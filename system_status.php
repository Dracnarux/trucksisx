<?php
/**
 * TruckSISX System Status Checker
 * Verifica el estado y funcionamiento de todos los componentes del sistema
 */

session_start();
require_once 'config/db.php';

// Solo admins pueden acceder a esta página
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$status = [];
$overallHealth = 'healthy';

// 1. Verificar conexión a la base de datos
try {
    $database = new Database();
    $db = $database->getConnection();
    $status['database'] = ['status' => 'OK', 'message' => 'Conexión exitosa a la base de datos'];
} catch (Exception $e) {
    $status['database'] = ['status' => 'ERROR', 'message' => 'Error de conexión: ' . $e->getMessage()];
    $overallHealth = 'error';
}

// 2. Verificar tablas principales
if (isset($db)) {
    $tables = ['users', 'alert', 'ord_trabj', 'regis_vehic', 'cond', 'repue', 'sali_repue', 'sali_vehi'];
    $tableStatus = [];
    
    foreach ($tables as $table) {
        try {
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM $table");
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            $tableStatus[$table] = ['status' => 'OK', 'count' => $count];
        } catch (Exception $e) {
            $tableStatus[$table] = ['status' => 'ERROR', 'message' => $e->getMessage()];
            $overallHealth = 'warning';
        }
    }
    $status['tables'] = $tableStatus;
}

// 3. Verificar archivos críticos
$criticalFiles = [
    'config/db.php',
    'controllers/LoginController.php',
    'controllers/AlertController.php',
    'models/User.php',
    'models/Alert.php',
    'views/dashboard.php',
    'views/truck_alerts.php'
];

$fileStatus = [];
foreach ($criticalFiles as $file) {
    if (file_exists($file) && is_readable($file)) {
        $fileStatus[$file] = ['status' => 'OK', 'size' => filesize($file)];
    } else {
        $fileStatus[$file] = ['status' => 'ERROR', 'message' => 'Archivo no encontrado o no legible'];
        $overallHealth = 'error';
    }
}
$status['files'] = $fileStatus;

// 4. Verificar directorios de uploads
$uploadDirs = ['uploads/', 'uploads/alertas/'];
$dirStatus = [];
foreach ($uploadDirs as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        $dirStatus[$dir] = ['status' => 'OK', 'writable' => true];
    } else {
        $dirStatus[$dir] = ['status' => 'WARNING', 'writable' => false];
        if ($overallHealth === 'healthy') $overallHealth = 'warning';
    }
}
$status['directories'] = $dirStatus;

// 5. Obtener estadísticas del sistema
if (isset($db)) {
    try {
        $stats = [];
        
        // Usuarios por rol
        $stmt = $db->prepare("SELECT rol, COUNT(*) as total FROM users GROUP BY rol");
        $stmt->execute();
        $stats['usuarios_por_rol'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Alertas por estado
        $stmt = $db->prepare("SELECT estado, COUNT(*) as total FROM alert GROUP BY estado");
        $stmt->execute();
        $stats['alertas_por_estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Órdenes por estado
        $stmt = $db->prepare("SELECT estado, COUNT(*) as total FROM ord_trabj GROUP BY estado");
        $stmt->execute();
        $stats['ordenes_por_estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $status['statistics'] = $stats;
    } catch (Exception $e) {
        $status['statistics'] = ['status' => 'ERROR', 'message' => $e->getMessage()];
    }
}

$statusColor = [
    'healthy' => 'success',
    'warning' => 'warning',
    'error' => 'danger'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado del Sistema - TruckSISX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(120deg, #f8fafc 0%, #e3e6ed 100%); }
        .status-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="views/dashboard.php">
                <i class="bi bi-truck"></i> TruckSISX
            </a>
            <span class="navbar-text">Estado del Sistema</span>
        </div>
    </nav>

    <div class="container">
        <!-- Estado General -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-<?= $statusColor[$overallHealth] ?> status-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-<?= $overallHealth === 'healthy' ? 'check-circle' : ($overallHealth === 'warning' ? 'exclamation-triangle' : 'x-circle') ?>" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h4 class="mb-1">Estado General del Sistema</h4>
                            <p class="mb-0">
                                <?php
                                switch($overallHealth) {
                                    case 'healthy': echo 'Sistema funcionando correctamente'; break;
                                    case 'warning': echo 'Sistema funcionando con algunas advertencias'; break;
                                    case 'error': echo 'Sistema con errores críticos'; break;
                                }
                                ?>
                            </p>
                            <small class="text-muted">Última verificación: <?= date('d/m/Y H:i:s') ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Base de Datos -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card status-card h-100">
                    <div class="card-header bg-transparent">
                        <h5><i class="bi bi-database"></i> Base de Datos</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-<?= $status['database']['status'] === 'OK' ? 'check-circle text-success' : 'x-circle text-danger' ?> me-2"></i>
                            <strong><?= $status['database']['status'] ?></strong>
                        </div>
                        <p><?= $status['database']['message'] ?></p>
                        
                        <?php if (isset($status['tables'])): ?>
                        <h6>Tablas del Sistema:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tabla</th>
                                        <th>Estado</th>
                                        <th>Registros</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($status['tables'] as $table => $tableInfo): ?>
                                    <tr>
                                        <td><code><?= $table ?></code></td>
                                        <td>
                                            <i class="bi bi-<?= $tableInfo['status'] === 'OK' ? 'check-circle text-success' : 'x-circle text-danger' ?>"></i>
                                            <?= $tableInfo['status'] ?>
                                        </td>
                                        <td><?= isset($tableInfo['count']) ? number_format($tableInfo['count']) : 'N/A' ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Archivos y Directorios -->
            <div class="col-md-6">
                <div class="card status-card h-100">
                    <div class="card-header bg-transparent">
                        <h5><i class="bi bi-files"></i> Sistema de Archivos</h5>
                    </div>
                    <div class="card-body">
                        <h6>Archivos Críticos:</h6>
                        <?php foreach ($status['files'] as $file => $fileInfo): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small><code><?= $file ?></code></small>
                            <span class="badge bg-<?= $fileInfo['status'] === 'OK' ? 'success' : 'danger' ?>">
                                <?= $fileInfo['status'] ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                        
                        <h6 class="mt-3">Directorios de Uploads:</h6>
                        <?php foreach ($status['directories'] as $dir => $dirInfo): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small><code><?= $dir ?></code></small>
                            <span class="badge bg-<?= $dirInfo['status'] === 'OK' ? 'success' : 'warning' ?>">
                                <?= $dirInfo['writable'] ? 'Escribible' : 'Solo Lectura' ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <?php if (isset($status['statistics']) && !isset($status['statistics']['status'])): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card status-card">
                    <div class="card-header bg-transparent">
                        <h5><i class="bi bi-graph-up"></i> Estadísticas del Sistema</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6>Usuarios por Rol</h6>
                                <?php foreach ($status['statistics']['usuarios_por_rol'] as $user): ?>
                                <div class="d-flex justify-content-between">
                                    <span><?= ucfirst($user['rol']) ?></span>
                                    <strong><?= $user['total'] ?></strong>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="col-md-4">
                                <h6>Alertas por Estado</h6>
                                <?php foreach ($status['statistics']['alertas_por_estado'] as $alert): ?>
                                <div class="d-flex justify-content-between">
                                    <span><?= ucfirst($alert['estado']) ?></span>
                                    <strong><?= $alert['total'] ?></strong>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="col-md-4">
                                <h6>Órdenes por Estado</h6>
                                <?php foreach ($status['statistics']['ordenes_por_estado'] as $orden): ?>
                                <div class="d-flex justify-content-between">
                                    <span><?= ucfirst($orden['estado']) ?></span>
                                    <strong><?= $orden['total'] ?></strong>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Acciones -->
        <div class="row">
            <div class="col-12 text-center">
                <a href="views/dashboard.php" class="btn btn-primary me-2">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
                <button onclick="location.reload()" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-clockwise"></i> Actualizar Estado
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>