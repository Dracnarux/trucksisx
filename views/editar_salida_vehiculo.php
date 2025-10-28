

<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
require_once '../config/db.php';
require_once '../models/SaliVehi.php';
$db = conectarDB();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$salida = (new SaliVehi($db))->getById($id);
if (!$salida) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>No se encontró la salida de vehículo.</div></div>";
    exit;
}

// Obtener lista de vehículos disponibles
$query_vehiculos = "SELECT id, placa, marca_vehiculo, modelo, num_cha FROM regis_vehic ORDER BY placa";
$stmt_vehiculos = mysqli_query($db, $query_vehiculos);
$vehiculos = [];
while ($vehiculo = mysqli_fetch_assoc($stmt_vehiculos)) {
    $vehiculos[] = $vehiculo;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Salida de Vehículo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%);
        }
        .main-header {
            background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(30, 58, 138, 0.2);
            color: #FFFFFF;
            margin-bottom: 2rem;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        .main-header h1 { color: #fff; }
        .card { border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .card-header { background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%); color: #1E3A8A; font-weight: 600; }
        .form-label { color: #1E3A8A; font-weight: 500; }
        .form-control, .form-select {
            background: #FFFFFF;
            border: 2px solid #D1D5DB;
            border-radius: 8px;
            color: #374151;
            font-size: 16px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1E3A8A;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
            outline: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            color: #1E3A8A !important;
            font-weight: 600;
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            color: #1E3A8A !important;
        }
        .btn-outline-primary {
            background: #FFFFFF;
            border: 2px solid #1E3A8A;
            color: #1E3A8A !important;
        }
        .btn-outline-primary:hover {
            background: #1E3A8A;
            color: #FFFFFF !important;
        }
        .btn-secondary {
            background: #FFFFFF;
            border: 2px solid #1E3A8A;
            color: #1E3A8A !important;
        }
        .btn-secondary:hover {
            background: #1E3A8A;
            color: #FFFFFF !important;
        }
        .card { margin-bottom: 2rem; }
        .form-section {
            background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%);
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }
    </style>
</head>
</body>
</html>

<body>
<div class="container py-4">
    <div class="main-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
            <div>
                <h1 class="mb-2"><i class="bi bi-pencil-square"></i> Editar Salida de Vehículo</h1>
                <span class="lead">Modifica los datos de la salida registrada</span>
            </div>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <a href="salida_vehiculo.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Volver a Salidas
                </a>
            </div>
        </div>
    </div>
    <!-- Mensajes de error -->
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php 
            switch($_GET['error']) {
                case 'update_failed':
                    echo '<strong>Error:</strong> No se pudo actualizar la salida de vehículo. Inténtelo nuevamente.';
                    break;
                default:
                    echo '<strong>Error:</strong> Ocurrió un problema al procesar la solicitud.';
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card animate-slide-up">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-clipboard-data"></i> Información de la Salida</h5>
        </div>
        <div class="card-body">
            <form action="../controllers/SaliVehiController.php?action=actualizar&id=<?= $salida['id'] ?>" method="POST">
                <div class="form-section">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="id_flotas" class="form-label">Vehículo de la Flota</label>
                            <select class="form-select" name="id_flotas" id="vehiculoSelect" required>
                                <option value="">Seleccione un vehículo...</option>
                                <?php foreach ($vehiculos as $vehiculo): ?>
                                    <option value="<?= $vehiculo['id'] ?>" 
                                            data-placa="<?= htmlspecialchars($vehiculo['placa']) ?>"
                                            data-marca="<?= htmlspecialchars($vehiculo['marca_vehiculo']) ?>"
                                            data-modelo="<?= htmlspecialchars($vehiculo['modelo']) ?>"
                                            data-chasis="<?= htmlspecialchars($vehiculo['num_cha']) ?>"
                                            <?= $vehiculo['id'] == $salida['id_flotas'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($vehiculo['placa']) ?> - <?= htmlspecialchars($vehiculo['marca_vehiculo']) ?> <?= htmlspecialchars($vehiculo['modelo']) ?> (ID: <?= $vehiculo['id'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="mt-2" id="vehiculoInfo" style="display: none;">
                                <small class="text-muted">
                                    <strong>Chasis:</strong> <span id="vehiculoChasis"></span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="segui_monitoreo" class="form-label">Seguimiento y Monitoreo</label>
                            <input type="text" class="form-control" name="segui_monitoreo" value="<?= htmlspecialchars($salida['segui_monitoreo']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="control_combustible" class="form-label">Control y Nivel de Combustible</label>
                            <input type="text" class="form-control" name="control_combustible" value="<?= htmlspecialchars($salida['control_combustible']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="cump_regulaciones" class="form-label">Cumplimiento de Regulaciones</label>
                            <input type="text" class="form-control" name="cump_regulaciones" value="<?= htmlspecialchars($salida['cump_regulaciones']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="protocolo_seguridad" class="form-label">Protocolos de Seguridad</label>
                            <input type="text" class="form-control" name="protocolo_seguridad" value="<?= htmlspecialchars($salida['protocolo_seguridad']) ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label for="gest_conductores" class="form-label">Gestión y Datos de Conductores</label>
                            <input type="text" class="form-control" name="gest_conductores" value="<?= htmlspecialchars($salida['gest_conductores']) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar Cambios
                    </button>
                    <a href="salida_vehiculo.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const vehiculoSelect = document.getElementById('vehiculoSelect');
    const vehiculoInfo = document.getElementById('vehiculoInfo');
    const vehiculoChasis = document.getElementById('vehiculoChasis');
    
    function updateVehicleInfo() {
        const selectedOption = vehiculoSelect.options[vehiculoSelect.selectedIndex];
        
        if (selectedOption.value && selectedOption.dataset.chasis) {
            vehiculoChasis.textContent = selectedOption.dataset.chasis;
            vehiculoInfo.style.display = 'block';
        } else {
            vehiculoInfo.style.display = 'none';
        }
    }
    
    // Mostrar información del vehículo seleccionado inicialmente
    updateVehicleInfo();
    
    // Escuchar cambios en el select
    vehiculoSelect.addEventListener('change', updateVehicleInfo);
});
</script>
</body>
</html>
