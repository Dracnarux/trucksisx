<?php
require_once '../config/db.php';
require_once '../models/Repue.php';
require_once '../models/OrdTrabj.php';
require_once '../models/Alert.php';
require_once '../models/Repor.php';

$db = conectarDB();
$repues = (new Repue($db))->getAll();
$ordenes = (new OrdTrabj($db))->getAll();
$alertas = (new Alert($db))->getAll();
$reportes = (new Repor($db))->getAll();
session_start();
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Salida de Repuestos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
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
        .main-header::before {
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2"/></svg>');
            content: '';
            height: 200px;
            opacity: 0.1;
            position: absolute;
            right: -50px;
            top: -50px;
            width: 200px;
        }
        .main-header h1 {
            color: #FFFFFF;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }
        .main-header .lead {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }
        .card, .form-section {
            background: #FFFFFF;
            border: 1px solid rgba(209, 213, 219, 0.3);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .card:hover, .form-section:hover {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        .form-label {
            color: #1E3A8A;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
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
        .btn {
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            min-height: 44px;
            padding: 0.75rem 1.5rem;
            position: relative;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn:focus {
            box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.3);
            outline: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
            color: #1E3A8A !important;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            box-shadow: 0 6px 20px rgba(251, 191, 36, 0.4);
            color: #1E3A8A !important;
            transform: translateY(-2px);
        }
        .btn-outline-primary {
            background: #FFFFFF;
            border: 2px solid #1E3A8A;
            color: #1E3A8A !important;
        }
        .btn-outline-primary:hover {
            background: #1E3A8A;
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }
        .btn-warning {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            color: #1E3A8A !important;
        }
        .btn-danger {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            color: #FFFFFF !important;
        }
        .btn-secondary {
            background: #F3F4F6 !important;
            color: #1E3A8A !important;
            border: 1px solid #D1D5DB !important;
        }
        .table-primary {
            background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%) !important;
            color: #FFFFFF !important;
            border-bottom: 2px solid #1E3A8A;
        }
        .table-bordered, .table-sm, .table th, .table td {
            color: #374151 !important;
        }
        .table-hover tbody tr:hover {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.05) 0%, rgba(30, 58, 138, 0.05) 100%);
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <?php
    // Mostrar mensajes de éxito o error
    if (isset($_GET['mensaje'])) {
        $mensaje = $_GET['mensaje'];
        $id = isset($_GET['id']) ? $_GET['id'] : '';
        
        if ($mensaje == 'eliminado') {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> Salida de repuesto eliminada correctamente' . ($id ? " (ID: $id)" : '') . '.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>';
        } elseif ($mensaje == 'actualizado') {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> Salida de repuesto actualizada correctamente' . ($id ? " (ID: $id)" : '') . '.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>';
        }
    }
    if (isset($_GET['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> Error: ' . htmlspecialchars($_GET['error']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    }
    ?>
    
    <div class="main-header animate-fade-in mb-4">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
            <div>
                <h1 class="mb-2"><i class="bi bi-box-arrow-right text-warning"></i> Salida de Repuestos</h1>
                <span class="lead">Registro y gestión de salidas de repuestos del sistema</span>
            </div>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <a href="dashboard.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </div>
    <div class="card mb-4 animate-slide-up shadow-corporate">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-clipboard-plus text-warning"></i> Registrar Salida de Repuestos
            </h5>
            <?php if (!$rol_conductor): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistrarSalida">
                <i class="bi bi-plus-circle"></i> Nueva Salida
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para registrar salida -->
    <div class="modal fade" id="modalRegistrarSalida" tabindex="-1" aria-labelledby="modalRegistrarSalidaLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header gradient-bg">
            <h5 class="modal-title" id="modalRegistrarSalidaLabel"><i class="bi bi-clipboard-plus text-warning"></i> Registrar Salida de Repuestos</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <form action="../controllers/SaliRepueController.php?action=registrar" method="POST" class="form-section mb-0" <?php if ($rol_conductor) echo 'style="display:none;"'; ?>>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="fecha_salida" class="form-label">Fecha de Salida</label>
                        <input type="date" class="form-control" name="fecha_salida" required>
                    </div>
                    <div class="col-md-4">
                        <label for="cantidad" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" name="cantidad" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <label for="repue_id" class="form-label">Repuesto</label>
                        <select class="form-select" name="repue_id" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($repues as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= $r['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="ord_trabj_id" class="form-label">Orden de Trabajo</label>
                        <select class="form-select" name="ord_trabj_id" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($ordenes as $o): ?>
                                <option value="<?= $o['id'] ?>"><?= $o['nombre_trabajo'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="alerta_id" class="form-label">Alerta del Sistema</label>
                        <select class="form-select" name="alerta_id" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($alertas as $a): ?>
                                <option value="<?= $a['id'] ?>">#<?= $a['id'] ?> - <?= $a['descripcion'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Registrar Salida
                    </button>
                </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="card mt-4 animate-slide-up shadow-corporate">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-list-check text-primary"></i> Salidas de Repuestos Registradas
            </h5>
        </div>
        <div class="card-body p-0">
            <form class="d-flex mb-2 p-3" method="get" action="">
                <input type="text" name="filtro_repue" class="form-control form-control-sm me-2" placeholder="Filtrar por repuesto, orden o alerta" value="<?= isset($_GET['filtro_repue']) ? htmlspecialchars($_GET['filtro_repue']) : '' ?>">
                <button class="btn btn-sm btn-outline-primary" type="submit">Filtrar</button>
            </form>
            <?php
            $salidas = $db->query("SELECT sr.id, sr.fecha_salida, sr.cantidad, r.nombre AS repuesto, ot.nombre_trabajo, a.descripcion AS alerta, sr.ord_trabj_id, sr.alerta_id FROM sali_repue sr LEFT JOIN repue r ON sr.repue_id = r.id LEFT JOIN ord_trabj ot ON sr.ord_trabj_id = ot.id LEFT JOIN alert a ON sr.alerta_id = a.id ORDER BY sr.fecha_salida DESC");
            $filtro = isset($_GET['filtro_repue']) ? strtolower($_GET['filtro_repue']) : '';
            ?>
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Cantidad</th>
                            <th>Repuesto</th>
                            <th>Orden de Trabajo</th>
                            <th>Alerta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($row = $salidas->fetch_assoc()): 
                        $texto = strtolower($row['repuesto'] . ' ' . $row['nombre_trabajo'] . ' ' . $row['alerta']);
                        if ($filtro && strpos($texto, $filtro) === false) continue;
                    ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['fecha_salida'] ?></td>
                            <td><?= $row['cantidad'] ?></td>
                            <td><?= $row['repuesto'] ?></td>
                            <td><?= $row['nombre_trabajo'] ?></td>
                            <td><?= $row['alerta'] ?></td>
                            <td>
                                <a href="ver_salida_repuesto.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Ver</a>
                                <?php if (!$rol_conductor): ?>
                                <a href="editar_salida_repuesto.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                <button class="btn btn-sm btn-danger" onclick="eliminarSalida(<?= $row['id'] ?>)">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function eliminarSalida(id) {
    // Confirmar eliminación
    if (!confirm('¿Estás seguro de que deseas eliminar esta salida de repuesto?\n\nEsta acción eliminará:\n• La salida de repuesto\n• El reporte asociado\n• Desvinculará salidas de vehículos relacionadas\n\n¿Continuar?')) {
        return;
    }
    
    // Crear indicador de carga
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="bi bi-hourglass-split"></i> Eliminando...';
    button.disabled = true;
    
    // En lugar de AJAX, usar navegación directa que es más confiable
    console.log('Eliminando salida ID:', id);
    
    // Crear un enlace temporal y hacer click para seguir la redirección
    window.location.href = `../controllers/SaliRepueController.php?action=eliminar&id=${id}`;
}

// Auto-hide alerts después de 5 segundos
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>

</body>
</html>
