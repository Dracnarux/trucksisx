<?php
require_once '../config/db.php';
session_start();
$db = conectarDB();
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
// Consulta para salidas de repuestos
$salidas_repue = $db->query("SELECT sr.id, sr.fecha_salida, sr.cantidad, r.nombre AS repuesto, ot.nombre_trabajo, a.descripcion AS alerta, sr.ord_trabj_id, sr.alerta_id
FROM sali_repue sr
LEFT JOIN repue r ON sr.repue_id = r.id
LEFT JOIN ord_trabj ot ON sr.ord_trabj_id = ot.id
LEFT JOIN alert a ON sr.alerta_id = a.id
ORDER BY sr.fecha_salida DESC");
// Consulta para salidas de vehículos
$salidas_vehi = $db->query("SELECT sv.id, sv.id_flotas, sv.segui_monitoreo, sv.control_combustible, sv.cump_regulaciones, sv.protocolo_seguridad, sv.gest_conductores, ot.nombre_trabajo, a.descripcion AS alerta, sv.ord_trabj_id, sv.alerta_id
FROM sali_vehi sv
LEFT JOIN ord_trabj ot ON sv.ord_trabj_id = ot.id
LEFT JOIN alert a ON sv.alerta_id = a.id
ORDER BY sv.id DESC");
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Reporte de Salidas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%);
            color: #374151;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            min-height: 100vh;
        }
        h2, h3, h4, h5, h6 {
            color: #1E3A8A;
            font-weight: 600;
        }
        .container-fluid {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem;
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
        .main-header h2 {
            color: #fff;
            margin-bottom: 0.5rem;
        }
        .main-header .lead {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .card {
            background: #FFFFFF;
            border: 1px solid rgba(209, 213, 219, 0.3);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .card-header {
            background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%);
            border-bottom: 1px solid #D1D5DB;
            color: #1E3A8A;
            font-weight: 600;
            padding: 1.25rem;
        }
        .card-body {
            padding: 1.5rem;
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
        .btn-outline-primary, .btn-secondary {
            background: #FFFFFF;
            border: 2px solid #1E3A8A;
            color: #1E3A8A !important;
        }
        .btn-outline-primary:hover, .btn-secondary:hover {
            background: #1E3A8A;
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }
        .btn-success {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: #fff !important;
        }
        .btn-warning {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            color: #1E3A8A !important;
        }
        .btn-danger {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            color: #fff !important;
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
        .form-label {
            color: #1E3A8A;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .table-responsive {
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        .table {
            margin-bottom: 0;
            font-size: 13px;
        }
        .table thead th {
            background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);
            border: none;
            color: #FFFFFF;
            font-weight: 700;
            padding: 0.7rem 0.5rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .table-warning th {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%) !important;
            color: #1E3A8A !important;
        }
        .table tbody td {
            border-bottom: 1px solid #E5E7EB;
            color: #374151;
            padding: 0.6rem 0.5rem;
            vertical-align: middle;
        }
        .table-hover tbody tr:hover {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.08) 0%, rgba(30, 58, 138, 0.08) 100%);
        }
        .alert {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        .alert-info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
            color: #2563EB;
        }
        @media (max-width: 768px) {
            .container-fluid {
                padding: 1rem;
            }
            .main-header {
                padding: 1.5rem;
                text-align: center;
            }
            .card-body {
                padding: 1rem;
            }
            .btn {
                font-size: 16px;
                min-height: 44px;
                width: 100%;
            }
            .btn + .btn {
                margin-top: 0.5rem;
            }
            .table-responsive {
                font-size: 14px;
            }
        }
        @media (max-width: 576px) {
            .container-fluid {
                padding: 0.5rem;
            }
            h2 {
                font-size: 1.5rem;
            }
            .main-header {
                padding: 1rem;
            }
            .table thead th,
            .table tbody td {
                font-size: 12px;
                padding: 0.5rem;
            }
            .btn {
                padding: 0.75rem 1rem;
            }
        }
    </style>
</head>
<body class="bg-light">
<div class="container-fluid py-4">
    <div class="main-header animate-fade-in mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2 class="h3 mb-1"><i class="fas fa-file-alt text-accent"></i> Reporte Consolidado de Salidas</h2>
                <p class="mb-0 opacity-75">Visualización y gestión de reportes de salidas de repuestos y vehículos</p>
            </div>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <a href="dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Volver al dashboard
                </a>
            </div>
        </div>
    </div>
    <div class="card animate-slide-up shadow-corporate mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-gear text-accent"></i> Salidas de Repuestos</h5>
        </div>
        <div class="card-body">
            <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap">
                <form class="d-flex flex-grow-1 me-2" method="get" action="">
                    <input type="text" name="filtro_repue" class="form-control form-control-sm me-2" placeholder="Filtrar por repuesto, orden o alerta" value="<?= isset($_GET['filtro_repue']) ? htmlspecialchars($_GET['filtro_repue']) : '' ?>">
                    <button class="btn btn-sm btn-outline-primary" type="submit">Filtrar</button>
                </form>
                <?php if (!$rol_conductor): ?>
                <a href="../views/salida_repuesto.php" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> Crear nueva salida</a>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle table-hover mb-0">
                    <thead>
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
                    <?php
                    // Filtro simple en PHP
                    $salidas_repue->data_seek(0);
                    $filtro = isset($_GET['filtro_repue']) ? strtolower($_GET['filtro_repue']) : '';
                    while($row = $salidas_repue->fetch_assoc()):
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
                            <td class="text-center">
                                <?php if (!$rol_conductor): ?>
                                <a href="../views/editar_salida_repuesto.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i></a>
                                <a href="../controllers/SaliRepueController.php?action=eliminar&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta salida?')"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <div class="card animate-slide-up shadow-corporate mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-truck text-accent"></i> Salidas de Vehículos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle table-hover mb-0">
                    <thead class="table-warning">
                        <tr>
                            <th>ID</th>
                            <th>ID Flotas</th>
                            <th>Seguimiento/Monitoreo</th>
                            <th>Combustible</th>
                            <th>Regulaciones</th>
                            <th>Protocolos</th>
                            <th>Conductores</th>
                            <th>Orden de Trabajo</th>
                            <th>Alerta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($row = $salidas_vehi->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['id_flotas'] ?></td>
                            <td><?= $row['segui_monitoreo'] ?></td>
                            <td><?= $row['control_combustible'] ?></td>
                            <td><?= $row['cump_regulaciones'] ?></td>
                            <td><?= $row['protocolo_seguridad'] ?></td>
                            <td><?= $row['gest_conductores'] ?></td>
                            <td><?= $row['nombre_trabajo'] ?></td>
                            <td><?= $row['alerta'] ?></td>
                            <td class="text-center">
                                <?php if (!$rol_conductor): ?>
                                <a href="../views/editar_salida_vehiculo.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i></a>
                                <a href="../controllers/SaliVehiController.php?action=eliminar&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta salida de vehículo?')"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if (!$rol_conductor): ?>
    <div class="mt-4 d-flex justify-content-end">
        <form method="get" action="">
            <button type="submit" name="reporte_completo" value="1" class="btn btn-lg btn-primary"><i class="fas fa-file-alt me-1"></i> Generar reporte completo</button>
        </form>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['reporte_completo'])): ?>
    <hr>
    <div class="card animate-slide-up shadow-corporate mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-file-alt text-accent"></i> Reporte Completo de Salidas</h5>
        </div>
        <div class="card-body">
            <h6 class="mb-3"><i class="bi bi-gear"></i> Salidas de Repuestos</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm align-middle table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Cantidad</th>
                            <th>Repuesto</th>
                            <th>Orden de Trabajo</th>
                            <th>Alerta</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $salidas_repue->data_seek(0); while($row = $salidas_repue->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['fecha_salida'] ?></td>
                            <td><?= $row['cantidad'] ?></td>
                            <td><?= $row['repuesto'] ?></td>
                            <td><?= $row['nombre_trabajo'] ?></td>
                            <td><?= $row['alerta'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <h6 class="mb-3 mt-4"><i class="bi bi-truck"></i> Salidas de Vehículos</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle table-hover mb-0">
                    <thead class="table-warning">
                        <tr>
                            <th>ID</th>
                            <th>ID Flotas</th>
                            <th>Seguimiento/Monitoreo</th>
                            <th>Combustible</th>
                            <th>Regulaciones</th>
                            <th>Protocolos</th>
                            <th>Conductores</th>
                            <th>Orden de Trabajo</th>
                            <th>Alerta</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $salidas_vehi->data_seek(0); while($row = $salidas_vehi->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['id_flotas'] ?></td>
                            <td><?= $row['segui_monitoreo'] ?></td>
                            <td><?= $row['control_combustible'] ?></td>
                            <td><?= $row['cump_regulaciones'] ?></td>
                            <td><?= $row['protocolo_seguridad'] ?></td>
                            <td><?= $row['gest_conductores'] ?></td>
                            <td><?= $row['nombre_trabajo'] ?></td>
                            <td><?= $row['alerta'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
