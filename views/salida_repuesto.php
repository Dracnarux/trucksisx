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
        :root{
            --bg-primary: #0F172A;
            --card-bg: #111827;
            --card-radius: 12px;
            --text-primary: #F1F5F9;
            --text-secondary: #94A3B8;
            --border: #1E293B;
            --accent: #F97316;
            --accent-amber: #F59E0B;
            --danger: #EF4444;
            --success: #10B981;
        }
        
        /* Sidebar visual igual al dashboard */
        .sidebar {
            background: var(--card-bg);
            color: var(--text-primary);
            border-right: 1px solid var(--border);
            box-shadow: 4px 0 20px rgba(2,6,23,0.3);
            height: 100vh;
            min-width: 220px;
            max-width: 340px;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 1050;
            border-radius: 0 1rem 1rem 0;
            transform: translateX(-100%);
            transition: transform 0.25s;
        }

        .sidebar.show-mobile {
            transform: translateX(0) !important;
        }

        .sidebar .nav-link {
            color: var(--text-primary) !important;
            font-weight: 500;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            background: rgba(249,115,22,0.1);
            color: var(--accent) !important;
        }

        .sidebar .nav-link.active, .sidebar .nav-link.bg-primary, .sidebar .nav-link.text-white {
            background: var(--accent);
            color: #FFFFFF !important;
            font-weight: 600;
        }

        .sidebar .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
        }

        .sidebar .btn-outline-secondary {
            color: var(--text-primary);
            border-color: var(--text-secondary);
        }

        .sidebar .btn-outline-secondary:hover {
            background: var(--text-secondary);
            color: var(--bg-primary);
        }

        .sidebar h5 {
            color: var(--text-primary);
            font-weight: 700;
        }

        .sidebar .nav-link.text-danger {
            color: var(--danger) !important;
        }

        .sidebar .nav-link.text-danger:hover {
            background: rgba(239,68,68,0.1);
            color: var(--danger) !important;
        }

        @media (max-width: 1024px) {
            .sidebar {
                position: fixed !important;
                top: 0; left: 0; bottom: 0;
                width: 85vw;
                max-width: 340px;
                height: 100vh;
                z-index: 1050;
                border-radius: 0 1rem 1rem 0;
                box-shadow: 0 8px 32px rgba(0,0,0,0.4);
                transform: translateX(-100%);
                transition: transform 0.25s;
            }
            .sidebar.show-mobile {
                transform: translateX(0);
            }
        }
        
        body {
            background: linear-gradient(135deg, var(--bg-primary) 0%, rgba(17,24,39,0.95) 100%);
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
        }
        
        .main-header {
            background: linear-gradient(135deg, rgba(15,23,42,0.9) 0%, rgba(17,24,39,0.85) 100%);
            border-radius: var(--card-radius);
            box-shadow: 0 18px 50px rgba(2,6,23,0.6);
            color: var(--text-primary);
            margin-bottom: 2rem;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .main-header h2 {
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .main-header .lead {
            font-size: 1.1rem;
            opacity: 0.9;
            color: var(--text-secondary);
        }

        .card, .form-section {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid var(--border);
            border-radius: var(--card-radius);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .card:hover, .form-section:hover {
            box-shadow: 0 8px 32px rgba(249, 115, 22, 0.15);
            transform: translateY(-2px);
            border-color: var(--accent);
        }

        .card-header {
            background: var(--accent);
            color: #fff;
            font-weight: bold;
            padding: 1.25rem;
        }

        .card-body {
            padding: 1.5rem;
        }
        .card, .form-section {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid var(--border);
            border-radius: var(--card-radius);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .card:hover, .form-section:hover {
            box-shadow: 0 8px 32px rgba(249, 115, 22, 0.15);
            transform: translateY(-2px);
            border-color: var(--accent);
        }
        
        .form-label {
            color: #000;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            background: #FFFFFF;
            border: 2px solid #D1D5DB;
            border-radius: 8px;
            color: #000;
            font-size: 16px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
            outline: none;
        }

        .form-section {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid var(--border);
            border-radius: var(--card-radius);
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }

        .form-section h6 {
            border-bottom: 2px solid var(--accent);
            color: #000;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
        }
        .btn {
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            min-height: 44px;
            padding: 0.75rem 1.5rem;
            position: relative;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: var(--accent);
            color: white;
        }
        
        .btn-primary:hover {
            background: #E65100;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }
        
        .btn-secondary {
            background: transparent;
            border: 1px solid var(--text-secondary);
            color: var(--text-secondary);
        }
        
        .btn-secondary:hover {
            background: var(--text-secondary);
            color: var(--bg-primary);
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #DC2626;
            transform: translateY(-1px);
        }
        
        .btn:focus {
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.3);
            outline: none;
        }
        
        .btn-outline-primary {
            background: #FFFFFF;
            border: 2px solid var(--accent);
            color: var(--accent) !important;
        }
        
        .btn-outline-primary:hover {
            background: var(--accent);
            color: #FFFFFF !important;
            transform: translateY(-1px);
        }
        
        .table-responsive {
            border-radius: var(--card-radius);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            overflow-y: hidden;
            max-width: 100%;
        }

        .table thead th {
            background: var(--accent);
            border: none;
            color: #FFFFFF;
            font-weight: 600;
            padding: 0.75rem;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
            min-width: 120px;
        }

        .table tbody td {
            border-bottom: 1px solid var(--border);
            color: #000;
            padding: 0.75rem;
            vertical-align: middle;
            white-space: nowrap;
            min-width: 120px;
        }

        .table tbody td.wrap-text {
            white-space: normal;
            max-width: 200px;
            word-wrap: break-word;
        }

        .table-hover tbody tr:hover {
            background: rgba(249, 115, 22, 0.05);
        }

        .badge {
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }

        .badge.bg-success {
            background: var(--success) !important;
        }

        .badge.bg-warning {
            background: var(--accent-amber) !important;
            color: #FFFFFF !important;
        }

        .badge.bg-danger {
            background: var(--danger) !important;
        }
        
        /* Alert styling to match theme */
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
        }
        
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
        }

        @media (max-width: 768px) {
            .container {
                padding: 0.5rem !important;
            }
            .main-header {
                padding: 1rem;
                text-align: center;
            }
            .card-body {
                padding: 1rem;
            }

            /* Ocultar tabla en móvil y mostrar cards */
            .table-container {
                display: none;
            }
            .mobile-card-view {
                display: block;
            }

            /* Estilos para las cards móviles */
            .mobile-vehicle-card {
                background: rgba(255, 255, 255, 0.95);
                border: 1px solid var(--border);
                border-radius: var(--card-radius);
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
                margin-bottom: 1rem;
                overflow: hidden;
            }

            .mobile-card-header {
                background: var(--accent);
                color: white;
                padding: 1rem;
                font-weight: 600;
            }

            .mobile-card-body {
                padding: 1rem;
            }

            .mobile-info-row {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                padding: 0.5rem 0;
                border-bottom: 1px solid var(--border);
            }

            .mobile-info-row:last-child {
                border-bottom: none;
            }

            .mobile-info-label {
                font-weight: 500;
                color: var(--text-secondary);
                font-size: 0.85rem;
                flex: 0 0 40%;
            }

            .mobile-info-value {
                color: #000;
                font-size: 0.85rem;
                text-align: right;
                flex: 1;
                word-wrap: break-word;
            }

            .mobile-actions {
                padding: 1rem;
                background: rgba(255, 255, 255, 0.95);
                border-top: 1px solid var(--border);
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 0.5rem !important;
            }
            h2, h4 {
                font-size: 1.25rem;
            }
            .btn {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }

            /* Cards móviles más compactas */
            .mobile-vehicle-card {
                margin-bottom: 0.75rem;
            }

            .mobile-card-header {
                padding: 0.75rem;
                font-size: 0.9rem;
            }

            .mobile-card-body {
                padding: 0.75rem;
            }

            .mobile-info-row {
                padding: 0.4rem 0;
            }

            .mobile-info-label, .mobile-info-value {
                font-size: 0.8rem;
            }

            .mobile-actions {
                padding: 0.75rem;
            }
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
    <style>
        /* Forzar texto negro dentro del modal de registrar salida */
        #modalRegistrarSalida .modal-content,
        #modalRegistrarSalida .modal-header,
        #modalRegistrarSalida .modal-body,
        #modalRegistrarSalida .modal-footer,
        #modalRegistrarSalida .modal-title,
        #modalRegistrarSalida label,
        #modalRegistrarSalida .form-label,
        #modalRegistrarSalida .form-control,
        #modalRegistrarSalida .form-select,
        #modalRegistrarSalida .btn {
            color: #000 !important;
        }
        #modalRegistrarSalida .form-control::placeholder { color: #666 !important; }
    </style>
    <div class="modal fade" id="modalRegistrarSalida" tabindex="-1" aria-labelledby="modalRegistrarSalidaLabel" aria-modal="true" role="dialog">
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
                
                <!-- Información adicional autocompletada -->
                <div id="info-autocompletado" class="mt-3" style="display: none;">
                    <div class="alert alert-success">
                        <h6 class="mb-2"><i class="bi bi-check-circle"></i> Datos Autocompletados</h6>
                        <div class="row g-2 small">
                            <div class="col-md-6" id="info-vehiculo-container" style="display: none;">
                                <strong>Vehículo:</strong> <span id="info-vehiculo"></span>
                            </div>
                            <div class="col-md-6" id="info-conductor-container" style="display: none;">
                                <strong>Conductor:</strong> <span id="info-conductor"></span>
                            </div>
                            <div class="col-md-6" id="info-tipo-alerta-container" style="display: none;">
                                <strong>Tipo de Alerta:</strong> <span id="info-tipo-alerta"></span>
                            </div>
                            <div class="col-md-6" id="info-prioridad-container" style="display: none;">
                                <strong>Prioridad:</strong> <span id="info-prioridad"></span>
                            </div>
                            <div class="col-12" id="info-repuesto-ot-container" style="display: none;">
                                <strong>Repuesto especificado en OT:</strong> <span id="info-repuesto-ot"></span>
                            </div>
                        </div>
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
            $salidas = $db->query("SELECT sr.id, sr.fecha_salida, sr.cantidad, r.nombre AS repuesto, ot.nombre_trabajo, a.descripcion AS alerta, sr.ord_trabj_id, sr.alerta_id FROM sali_repue sr LEFT JOIN repue r ON sr.repue_id = r.id LEFT JOIN ord_trabj ot ON sr.ord_trabj_id = ot.id LEFT JOIN alert a ON sr.alerta_id = a.id ORDER BY sr.id ASC");
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
// Variables globales para el autocompletado
let vehiculoIdActual = null;
let tipoAlertaActual = null;
let ordenTrabajoSeleccionada = null;

// Autocompletar campos al seleccionar Orden de Trabajo
document.addEventListener('DOMContentLoaded', function() {
    const ordenSelect = document.querySelector('select[name="ord_trabj_id"]');
    const alertaSelect = document.querySelector('select[name="alerta_id"]');
    const repuestoSelect = document.querySelector('select[name="repue_id"]');
    
    // Corregir problema de aria-hidden con el modal
    const modal = document.getElementById('modalRegistrarSalida');
    if (modal) {
        // Prevenir que Bootstrap agregue aria-hidden
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'aria-hidden') {
                    if (modal.hasAttribute('aria-hidden')) {
                        modal.removeAttribute('aria-hidden');
                    }
                }
            });
        });
        
        observer.observe(modal, {
            attributes: true,
            attributeFilter: ['aria-hidden']
        });
        
        modal.addEventListener('hidden.bs.modal', function() {
            // Resetear el formulario y la información autocompletada
            const infoContainer = document.getElementById('info-autocompletado');
            if (infoContainer) {
                infoContainer.style.display = 'none';
            }
            // Limpiar selects
            if (ordenSelect) ordenSelect.value = '';
            if (alertaSelect) alertaSelect.value = '';
            resetRepuestoSelect();
        });
        
        modal.addEventListener('show.bs.modal', function() {
            // Asegurar que tenga aria-modal
            this.setAttribute('aria-modal', 'true');
            this.setAttribute('role', 'dialog');
        });
    }
    
    if (ordenSelect) {
        ordenSelect.addEventListener('change', function() {
            const ordenId = this.value;
            
            if (!ordenId) {
                // Limpiar campos si no hay selección
                if (alertaSelect) alertaSelect.value = '';
                resetRepuestoSelect();
                return;
            }
            
            // Mostrar indicador de carga
            const loadingHtml = '<option value="">Cargando datos...</option>';
            if (alertaSelect) alertaSelect.innerHTML = loadingHtml;
            
            // Obtener detalles de la orden de trabajo
            fetch(`../api/orden_trabajo_detalles.php?id=${ordenId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.error);
                        alert('Error al cargar los datos de la orden de trabajo');
                        return;
                    }
                    
                    // Guardar datos globales
                    ordenTrabajoSeleccionada = data;
                    vehiculoIdActual = data.vehiculo_id;
                    tipoAlertaActual = data.tipo_alerta;
                    
                    // Rellenar y actualizar el select de alerta automáticamente
                    if (alertaSelect) {
                        if (data.alerta_id && data.alerta_descripcion) {
                            // Solo mostrar la alerta asociada a la orden
                            alertaSelect.innerHTML = `<option value="${data.alerta_id}">#${data.alerta_id} - ${data.alerta_descripcion}</option>`;
                            alertaSelect.value = data.alerta_id;
                        } else {
                            // Si no hay alerta asociada, mostrar opción por defecto
                            alertaSelect.innerHTML = '<option value="">Sin alerta asociada</option>';
                        }
                    }
                    
                    // Cargar repuestos sugeridos si hay vehículo
                    if (vehiculoIdActual) {
                        cargarRepuestosSugeridos(vehiculoIdActual, tipoAlertaActual);
                    }
                    
                    // Mostrar información adicional al usuario
                    mostrarInfoOrdenTrabajo(data);
                })
                .catch(error => {
                    console.error('Error al obtener detalles:', error);
                    alert('Error de conexión al obtener los datos');
                });
        });
    }
    
    // Auto-hide alerts después de 5 segundos
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Cargar repuestos sugeridos según el vehículo
function cargarRepuestosSugeridos(vehiculoId, tipoAlerta = null) {
    const repuestoSelect = document.querySelector('select[name="repue_id"]');
    if (!repuestoSelect) return;
    
    // Mostrar indicador de carga
    repuestoSelect.innerHTML = '<option value="">Cargando repuestos sugeridos...</option>';
    
    let url = `../api/repuestos_sugeridos.php?vehiculo_id=${vehiculoId}`;
    if (tipoAlerta) {
        url += `&tipo_alerta=${tipoAlerta}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(repuestos => {
            if (repuestos.error) {
                console.error('Error:', repuestos.error);
                resetRepuestoSelect();
                return;
            }
            
            // Construir opciones del select
            let html = '<option value="">Seleccione un repuesto...</option>';
            
            if (repuestos.length === 0) {
                // Si no hay sugeridos, restaurar todas las opciones originales
                const opcionesOriginales = repuestoSelect.getAttribute('data-opciones-originales');
                if (opcionesOriginales) {
                    repuestoSelect.innerHTML = opcionesOriginales;
                } else {
                    repuestoSelect.innerHTML = '<option value="">Seleccione...</option>';
                }
            } else {
                // Agrupar repuestos sugeridos primero
                let html = '<option value="">Seleccione un repuesto...</option>';
                html += '<optgroup label="⭐ Repuestos Sugeridos">';
                repuestos.forEach(repuesto => {
                    const stock = repuesto.cant_stock ? ` (Stock: ${repuesto.cant_stock})` : '';
                    const categoria = repuesto.categoria_nombre ? ` - ${repuesto.categoria_nombre}` : '';
                    html += `<option value="${repuesto.id}">${repuesto.nombre}${categoria}${stock}</option>`;
                });
                html += '</optgroup>';
                repuestoSelect.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error al cargar repuestos:', error);
            resetRepuestoSelect();
        });
}

// Resetear el select de repuestos al estado original
function resetRepuestoSelect() {
    const repuestoSelect = document.querySelector('select[name="repue_id"]');
    if (!repuestoSelect) return;
    
    // Restaurar todas las opciones originales
    const opcionesOriginales = repuestoSelect.getAttribute('data-opciones-originales');
    if (opcionesOriginales) {
        repuestoSelect.innerHTML = opcionesOriginales;
    } else {
        // Si no hay backup, al menos poner la opción por defecto
        repuestoSelect.innerHTML = '<option value="">Seleccione...</option>';
    }
}

// Mostrar información de la orden de trabajo
function mostrarInfoOrdenTrabajo(data) {
    const infoContainer = document.getElementById('info-autocompletado');
    if (!infoContainer) return;
    let hasInfo = false;

    // Vehículo
    const infoVehiculo = document.getElementById('info-vehiculo');
    const infoVehiculoContainer = document.getElementById('info-vehiculo-container');
    if (data.vehiculo_id && infoVehiculo && infoVehiculoContainer) {
        const vehiculoInfo = `${data.marca || ''} ${data.modelo || ''} ${data.numero_placa ? `(${data.numero_placa})` : ''}`.trim();
        infoVehiculo.textContent = vehiculoInfo;
        infoVehiculoContainer.style.display = 'block';
        hasInfo = true;
    } else if (infoVehiculoContainer) {
        infoVehiculoContainer.style.display = 'none';
    }

    // Conductor
    const infoConductor = document.getElementById('info-conductor');
    const infoConductorContainer = document.getElementById('info-conductor-container');
    if (data.conductor_nombre_completo && infoConductor && infoConductorContainer) {
        infoConductor.textContent = data.conductor_nombre_completo;
        infoConductorContainer.style.display = 'block';
        hasInfo = true;
    } else if (infoConductorContainer) {
        infoConductorContainer.style.display = 'none';
    }

    // Tipo de alerta
    const infoTipoAlerta = document.getElementById('info-tipo-alerta');
    const infoTipoAlertaContainer = document.getElementById('info-tipo-alerta-container');
    if (data.tipo_alerta && infoTipoAlerta && infoTipoAlertaContainer) {
        const tipoAlertaLabel = {
            'llanta': '🔧 Llantas',
            'motor': '⚙️ Motor',
            'frenos': '🛑 Frenos',
            'general': '📋 General'
        };
        infoTipoAlerta.textContent = tipoAlertaLabel[data.tipo_alerta] || data.tipo_alerta;
        infoTipoAlertaContainer.style.display = 'block';
        hasInfo = true;
    } else if (infoTipoAlertaContainer) {
        infoTipoAlertaContainer.style.display = 'none';
    }

    // Prioridad
    const infoPrioridad = document.getElementById('info-prioridad');
    const infoPrioridadContainer = document.getElementById('info-prioridad-container');
    if (data.alerta_prioridad && infoPrioridad && infoPrioridadContainer) {
        const prioridadHTML = {
            'baja': '<span class="badge bg-info">BAJA</span>',
            'media': '<span class="badge bg-warning text-dark">MEDIA</span>',
            'alta': '<span class="badge bg-danger">ALTA</span>',
            'critica': '<span class="badge bg-dark">CRÍTICA</span>'
        };
        infoPrioridad.innerHTML = prioridadHTML[data.alerta_prioridad] || data.alerta_prioridad;
        infoPrioridadContainer.style.display = 'block';
        hasInfo = true;
    } else if (infoPrioridadContainer) {
        infoPrioridadContainer.style.display = 'none';
    }

    // Repuesto especificado en OT
    const infoRepuestoOt = document.getElementById('info-repuesto-ot');
    const infoRepuestoOtContainer = document.getElementById('info-repuesto-ot-container');
    if (data.nombre_repuesto && infoRepuestoOt && infoRepuestoOtContainer) {
        infoRepuestoOt.textContent = data.nombre_repuesto;
        infoRepuestoOtContainer.style.display = 'block';
        hasInfo = true;
    } else if (infoRepuestoOtContainer) {
        infoRepuestoOtContainer.style.display = 'none';
    }

    // Mostrar u ocultar el contenedor completo
    infoContainer.style.display = hasInfo ? 'block' : 'none';
}

// Guardar opciones originales del select de repuestos al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const repuestoSelect = document.querySelector('select[name="repue_id"]');
    if (repuestoSelect) {
        repuestoSelect.setAttribute('data-opciones-originales', repuestoSelect.innerHTML);
    }
});

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
</script>

</body>
</html>
