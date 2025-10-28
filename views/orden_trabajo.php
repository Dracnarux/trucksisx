<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
$usuario_nombre = $_SESSION['usuario']['nombre'] ?? 'Usuario';

require_once '../config/db.php';
$conn = conectarDB();

$estado = $_GET['estado'] ?? '';
$prioridad = $_GET['prioridad'] ?? '';
$nombre_trabajo = $_GET['nombre_trabajo'] ?? '';
$condicion = [];
if ($estado) $condicion[] = "estado = '" . $conn->real_escape_string($estado) . "'";
if ($prioridad) $condicion[] = "prioridad = '" . $conn->real_escape_string($prioridad) . "'";
if ($nombre_trabajo) $condicion[] = "nombre_trabajo LIKE '%" . $conn->real_escape_string($nombre_trabajo) . "%'";
$where = $condicion ? 'WHERE ' . implode(' AND ', $condicion) : '';

$sql = "SELECT * FROM ord_trabj $where ORDER BY fecha_creacion DESC";
$result = $conn->query($sql);

require_once '../models/User.php';
$userModel = new User();
$conductores = $userModel->getConductoresCond();
$tecnicos = $userModel->getTecnicos();
// No imprimir ni mostrar estas variables en el HTML, solo usarlas en los selectores y la tabla
require_once '../models/Alert.php';
$alertModel = new Alert();
$alertas = $alertModel->getAll();
require_once '../models/Repue.php';
$repueModel = new Repue();
$repues = $repueModel->getAll();
$conductoresById = [];
foreach($conductores as $c) {
    $conductoresById[$c['id']] = $c['nombre'];
}
$tecnicosById = [];
foreach($tecnicos as $t) {
    $tecnicosById[$t['id']] = $t['nombre'] . ' ' . $t['apellido'];
}
// No imprimir nada de estas variables, solo usarlas en los selectores y la tabla
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Órdenes de Trabajo - TruckSISX</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ========== RESET Y BASE ========== */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* ========== TIPOGRAFÍA ========== */
        body {
            background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%);
            color: #374151;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5, h6 {
            color: #1E3A8A;
            font-weight: 600;
            line-height: 1.3;
            margin-bottom: 1rem;
        }

        h1 {
            font-size: clamp(1.75rem, 4vw, 2.5rem);
            font-weight: 700;
        }

        h2 {
            font-size: clamp(1.5rem, 3vw, 2rem);
        }

        h3 {
            font-size: clamp(1.25rem, 2.5vw, 1.5rem);
        }

        /* ========== LAYOUT PRINCIPAL ========== */
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

        /* ========== COMPONENTES - CARDS ========== */
        .card {
            background: #FFFFFF;
            border: 1px solid rgba(209, 213, 219, 0.3);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
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

        /* ========== COMPONENTES - BOTONES ========== */
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

        /* Botón Principal */
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

        /* Botón Secundario */
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

        /* Botones de Estado */
        .btn-success {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: #FFFFFF !important;
        }

        .btn-warning {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            color: #1E3A8A !important;
        }

        .btn-danger {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            color: #FFFFFF !important;
        }

        .btn-info {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            color: #FFFFFF !important;
        }

        .btn:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        /* ========== COMPONENTES - FORMULARIOS ========== */
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

        .form-section {
            background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%);
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }

        .form-section h6 {
            border-bottom: 2px solid #FBBF24;
            color: #1E3A8A;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
        }

        /* ========== COMPONENTES - TABLAS ========== */

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

        .table tbody td {
            border-bottom: 1px solid #E5E7EB;
            color: #374151;
            padding: 0.6rem 0.5rem;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.08) 0%, rgba(30, 58, 138, 0.08) 100%);
        }

        /* Botones de acción en la tabla */
        .table td.text-center .btn {
            font-size: 12px;
            padding: 0.3rem 0.6rem;
            margin: 0 2px;
        }
        .table td.text-center .btn-info {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            color: #fff !important;
        }
        .table td.text-center .btn-warning {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            color: #1E3A8A !important;
        }
        .table td.text-center .btn-danger {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            color: #fff !important;
        }

        /* ========== COMPONENTES - BADGES ========== */
        .badge {
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }

        .badge.bg-success {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important;
        }

        .badge.bg-warning {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%) !important;
            color: #1E3A8A !important;
        }

        .badge.bg-danger {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%) !important;
        }

        .badge.bg-info {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%) !important;
        }

        /* ========== COMPONENTES - MODALES ========== */
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);
            border-radius: 12px 12px 0 0;
            color: #FFFFFF;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-footer {
            border-top: 1px solid #E5E7EB;
            padding: 1.5rem 2rem;
        }

        /* ========== COMPONENTES - ALERTAS ========== */
        .alert {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);
            color: #059669;
        }

        .alert-warning {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.1) 0%, rgba(245, 158, 11, 0.1) 100%);
            color: #D97706;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
            color: #DC2626;
        }

        /* ========== RESPONSIVIDAD ========== */
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

            .form-section {
                padding: 1rem;
            }

            .modal-body {
                padding: 1rem;
            }

            .d-flex.gap-2 {
                flex-direction: column;
            }

            .d-flex.gap-2 > * {
                margin-bottom: 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .container-fluid {
                padding: 0.5rem;
            }

            h1 {
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

        /* ========== UTILIDADES ========== */
        .gradient-bg {
            background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);
        }

        .text-corporate {
            color: #1E3A8A !important;
        }

        .text-accent {
            color: #FBBF24 !important;
        }

        .border-corporate {
            border-color: #1E3A8A !important;
        }

        .shadow-corporate {
            box-shadow: 0 4px 16px rgba(30, 58, 138, 0.15) !important;
        }

        /* ========== ANIMACIONES ========== */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-up {
            animation: slideInUp 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.4s ease-out;
        }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid py-4">
    <div class="main-header animate-fade-in">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">
                    <i class="fas fa-clipboard-list text-accent"></i> Órdenes de Trabajo
                    <?php if ($rol_conductor): ?><small class="opacity-75"> (Solo lectura)</small><?php endif; ?>
                </h1>
                <p class="mb-0 opacity-75">
                    <?php if ($rol_conductor): ?>
                        Visualización de órdenes del sistema
                    <?php else: ?>
                        Gestión y seguimiento de órdenes generadas en el sistema
                    <?php endif; ?>
                </p>
            </div>
            <div class="d-flex gap-2">
                <?php if (!$rol_conductor): ?>
                <button type="button" class="btn btn-primary shadow-corporate" data-bs-toggle="modal" data-bs-target="#modalCrearOrden">
                    <i class="bi bi-plus-circle"></i> Nueva Orden
                </button>
                <?php endif; ?>
                <a href="truck_alerts.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Volver a Alertas
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 animate-slide-up shadow-corporate">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-funnel text-accent"></i> Filtros de Búsqueda
                <?php 
                $filtros_activos = 0;
                if ($estado) $filtros_activos++;
                if ($prioridad) $filtros_activos++;
                if ($nombre_trabajo) $filtros_activos++;
                if ($filtros_activos > 0): ?>
                    <span class="badge bg-primary ms-2"><?= $filtros_activos ?> filtro<?= $filtros_activos > 1 ? 's' : '' ?> activo<?= $filtros_activos > 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-3">
                <i class="bi bi-lightbulb"></i>
                <strong>Mejora:</strong> Ahora puedes buscar órdenes de trabajo por nombre específico usando el campo de texto.
            </div>
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="bi bi-briefcase text-corporate"></i> Nombre del Trabajo
                    </label>
                    <input type="text" name="nombre_trabajo" class="form-control" 
                           placeholder="Buscar por nombre..." 
                           value="<?= htmlspecialchars($nombre_trabajo) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="bi bi-flag text-corporate"></i> Estado
                    </label>
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" <?= $estado === 'pendiente' ? 'selected' : '' ?>>⏳ Pendiente</option>
                        <option value="en_proceso" <?= $estado === 'en_proceso' ? 'selected' : '' ?>>🔄 En Proceso</option>
                        <option value="resuelta" <?= $estado === 'resuelta' ? 'selected' : '' ?>>✅ Resuelta</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="bi bi-exclamation-triangle text-corporate"></i> Prioridad
                    </label>
                    <select name="prioridad" class="form-select">
                        <option value="">Todas las prioridades</option>
                        <option value="baja" <?= $prioridad === 'baja' ? 'selected' : '' ?>>🟢 Baja</option>
                        <option value="media" <?= $prioridad === 'media' ? 'selected' : '' ?>>🟡 Media</option>
                        <option value="alta" <?= $prioridad === 'alta' ? 'selected' : '' ?>>🟠 Alta</option>
                        <option value="critica" <?= $prioridad === 'critica' ? 'selected' : '' ?>>🔴 Crítica</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="orden_trabajo.php" class="btn btn-outline-secondary" title="Limpiar todos los filtros">
                        <i class="bi bi-x-circle"></i>
                    </a>
                    <button type="button" class="btn btn-outline-info" onclick="limpiarTexto()" title="Limpiar solo el campo de texto">
                        <i class="bi bi-eraser"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Órdenes -->
    <div class="card animate-slide-up shadow-corporate">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list text-accent"></i> Lista de Órdenes de Trabajo
                <span class="badge bg-info ms-2"><?= $result ? $result->num_rows : 0 ?> registro<?= ($result && $result->num_rows != 1) ? 's' : '' ?></span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th><i class="bi bi-hash"></i> ID</th>
                            <th><i class="bi bi-tools"></i> Trabajo</th>
                            <th><i class="bi bi-card-text"></i> Descripción</th>
                            <th><i class="bi bi-gear"></i> Repuesto</th>
                            <th><i class="bi bi-calendar-plus"></i> F. Creación</th>
                            <th><i class="bi bi-calendar-check"></i> F. Estimada</th>
                            <th><i class="bi bi-flag"></i> Estado</th>
                            <th><i class="bi bi-exclamation-triangle"></i> Prioridad</th>
                            <th><i class="bi bi-person-check"></i> Conductor</th>
                            <th><i class="bi bi-person-gear"></i> Técnico</th>
                            <th><i class="bi bi-bell"></i> Alerta</th>
                            <th class="text-center" width="140"><i class="bi bi-three-dots"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php if($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?= $row['id'] ?></strong></td>
                                <td><?= htmlspecialchars($row['nombre_trabajo']) ?></td>
                                <td><?= htmlspecialchars($row['descripcion']) ?></td>
                                <td><?= htmlspecialchars($row['nombre_repuesto']) ?: '<span class="text-muted">Sin repuesto</span>' ?></td>
                                <td><?= $row['fecha_creacion'] ?></td>
                                <td><?= $row['fecha_estimada'] ?></td>
                                <td>
                                    <?php 
                                    $estado_badge = '';
                                    $estado_icon = '';
                                    switch($row['estado']) {
                                        case 'resuelta': 
                                            $estado_badge = 'bg-success'; 
                                            $estado_icon = '✅';
                                            break;
                                        case 'en_proceso': 
                                            $estado_badge = 'bg-info'; 
                                            $estado_icon = '🔄';
                                            break;
                                        case 'pendiente': 
                                            $estado_badge = 'bg-warning'; 
                                            $estado_icon = '⏳';
                                            break;
                                        case 'cancelada': 
                                            $estado_badge = 'bg-danger'; 
                                            $estado_icon = '❌';
                                            break;
                                        default: 
                                            $estado_badge = 'bg-secondary';
                                            $estado_icon = '❓';
                                    }
                                    ?>
                                    <span class="badge <?= $estado_badge ?>"><?= $estado_icon ?> <?= ucfirst(str_replace('_', ' ', $row['estado'])) ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $prioridad_badge = '';
                                    $prioridad_icon = '';
                                    switch($row['prioridad']) {
                                        case 'critica': 
                                            $prioridad_badge = 'bg-danger'; 
                                            $prioridad_icon = '🔴';
                                            break;
                                        case 'alta': 
                                            $prioridad_badge = 'bg-warning'; 
                                            $prioridad_icon = '🟠';
                                            break;
                                        case 'media': 
                                            $prioridad_badge = 'bg-info'; 
                                            $prioridad_icon = '🟡';
                                            break;
                                        case 'baja': 
                                            $prioridad_badge = 'bg-success'; 
                                            $prioridad_icon = '🟢';
                                            break;
                                        default: 
                                            $prioridad_badge = 'bg-secondary';
                                            $prioridad_icon = '⚪';
                                    }
                                    ?>
                                    <span class="badge <?= $prioridad_badge ?>"><?= $prioridad_icon ?> <?= ucfirst($row['prioridad']) ?></span>
                                </td>
                                <td><?= isset($conductoresById[$row['cond_id']]) ? htmlspecialchars($conductoresById[$row['cond_id']]) : ($row['cond_id'] ? $row['cond_id'] : '<span class="text-muted">Sin asignar</span>') ?></td>
                                <td><?= isset($tecnicosById[$row['users_id']]) ? htmlspecialchars($tecnicosById[$row['users_id']]) : $row['users_id'] ?></td>
                                <td><?= $row['alert_id'] ?: '<span class="text-muted">Sin alerta</span>' ?></td>
                                <?php if (!$rol_conductor): ?>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" class="btn btn-sm btn-info" onclick="verOrden(<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>)" title="Ver Detalles" data-bs-toggle="tooltip">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editarOrden(<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>)" title="Editar" data-bs-toggle="tooltip">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmarEliminar(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nombre_trabajo'], ENT_QUOTES) ?>')" title="Eliminar" data-bs-toggle="tooltip">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                                <?php else: ?>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info" onclick="verOrden(<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>)" title="Ver Detalles" data-bs-toggle="tooltip">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                </td>
                                <?php endif; ?>
                            </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-search fa-3x mb-3"></i>
                                            <h5>No se encontraron órdenes de trabajo</h5>
                                            <p>No hay órdenes que coincidan con los filtros aplicados.</p>
                                            <a href="orden_trabajo.php" class="btn btn-outline-primary">
                                                <i class="bi bi-arrow-clockwise"></i> Limpiar filtros
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Modal de Confirmación para Eliminar -->
    <div class="modal fade" id="modalEliminarOrden" tabindex="-1" aria-labelledby="modalEliminarOrdenLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalEliminarOrdenLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fas fa-trash-alt text-danger" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h6>¿Estás seguro de que deseas eliminar esta orden de trabajo?</h6>
                        <p class="text-muted mb-3">Esta acción no se puede deshacer.</p>
                        <div class="alert alert-warning">
                            <strong>Orden ID:</strong> <span id="ordenIdEliminar"></span><br>
                            <strong>Trabajo:</strong> <span id="ordenNombreEliminar"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <a href="#" id="btnConfirmarEliminar" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Eliminar Orden
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Notificaciones -->
    <div class="modal fade" id="modalNotificacion" tabindex="-1" aria-labelledby="modalNotificacionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" id="modalNotificacionHeader">
                    <h5 class="modal-title" id="modalNotificacionLabel">
                        <i class="fas fa-info-circle me-2"></i>Notificación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i id="modalNotificacionIcon" class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h6 id="modalNotificacionTitulo">Operación Exitosa</h6>
                        <p id="modalNotificacionMensaje" class="text-muted"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-check me-1"></i>Entendido
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Crear Orden -->
    <div class="modal fade" id="modalCrearOrden" tabindex="-1" aria-labelledby="modalCrearOrdenLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearOrdenLabel">
                        <i class="fas fa-plus-circle me-2"></i>Crear Nueva Orden de Trabajo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="crear_orden.php" id="formCrearOrden">
                    <div class="modal-body">
                        <!-- Información Básica -->
                        <div class="form-section">
                            <h6><i class="bi bi-info-circle"></i> Información Básica</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre del trabajo *</label>
                                    <input type="text" name="nombre_trabajo" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Estimada *</label>
                                    <input type="date" name="fecha_estimada" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descripción *</label>
                                    <textarea name="descripcion" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración -->
                        <div class="form-section">
                            <h6><i class="bi bi-gear"></i> Configuración</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Estado</label>
                                    <select name="estado" class="form-select">
                                        <option value="pendiente">Pendiente</option>
                                        <option value="en_proceso">En Proceso</option>
                                        <option value="completada">Completada</option>
                                        <option value="cancelada">Cancelada</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Prioridad</label>
                                    <select name="prioridad" class="form-select">
                                        <option value="baja">Baja</option>
                                        <option value="media">Media</option>
                                        <option value="alta">Alta</option>
                                        <option value="critica">Crítica</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Repuesto (opcional)</label>
                                    <select name="nombre_repuesto" class="form-select">
                                        <option value="">Sin repuesto</option>
                                        <?php 
                                        $repues->data_seek(0); // Reset pointer
                                        while($repue = $repues->fetch_assoc()): ?>
                                            <option value="<?= htmlspecialchars($repue['nombre']) ?>">
                                                <?= htmlspecialchars($repue['nombre']) ?> (ID: <?= $repue['id'] ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Asignaciones -->
                        <div class="form-section">
                            <h6><i class="bi bi-people"></i> Asignaciones</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Conductor</label>
                                    <select name="cond_id" class="form-select">
                                        <option value="">Sin asignar</option>
                                        <?php foreach($conductores as $conductor): ?>
                                            <option value="<?= $conductor['id'] ?>">
                                                <?= htmlspecialchars($conductor['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Técnico *</label>
                                    <select name="users_id" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach($tecnicos as $tecnico): ?>
                                            <option value="<?= $tecnico['id'] ?>">
                                                <?= htmlspecialchars($tecnico['nombre'] . ' ' . $tecnico['apellido']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Alerta</label>
                                    <select name="alert_id" class="form-select" required>
                                        <option value="">Seleccione</option>
                                        <?php foreach($alertas as $alerta): ?>
                                            <option value="<?= $alerta['id'] ?>">
                                                <?= htmlspecialchars($alerta['descripcion']) ?> (ID: <?= $alerta['id'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Crear Orden
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Editar Orden -->
    <div class="modal fade" id="modalEditarOrden" tabindex="-1" aria-labelledby="modalEditarOrdenLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarOrdenLabel">
                        <i class="fas fa-edit me-2"></i>Editar Orden de Trabajo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="editar_orden.php" id="formEditarOrden">
                    <input type="hidden" name="id" id="editar_id">
                    <div class="modal-body">
                        <!-- Información Básica -->
                        <div class="form-section">
                            <h6><i class="bi bi-info-circle"></i> Información Básica</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre del trabajo *</label>
                                    <input type="text" name="nombre_trabajo" id="editar_nombre_trabajo" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Estimada *</label>
                                    <input type="date" name="fecha_estimada" id="editar_fecha_estimada" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descripción *</label>
                                    <textarea name="descripcion" id="editar_descripcion" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración -->
                        <div class="form-section">
                            <h6><i class="bi bi-gear"></i> Configuración</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Estado</label>
                                    <select name="estado" id="editar_estado" class="form-select">
                                        <option value="pendiente">Pendiente</option>
                                        <option value="en_proceso">En Proceso</option>
                                        <option value="completada">Completada</option>
                                        <option value="cancelada">Cancelada</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Prioridad</label>
                                    <select name="prioridad" id="editar_prioridad" class="form-select">
                                        <option value="baja">Baja</option>
                                        <option value="media">Media</option>
                                        <option value="alta">Alta</option>
                                        <option value="critica">Crítica</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Repuesto (opcional)</label>
                                    <select name="nombre_repuesto" id="editar_nombre_repuesto" class="form-select">
                                        <option value="">Sin repuesto</option>
                                        <?php 
                                        $repues->data_seek(0); // Reset pointer
                                        while($repue = $repues->fetch_assoc()): ?>
                                            <option value="<?= htmlspecialchars($repue['nombre']) ?>">
                                                <?= htmlspecialchars($repue['nombre']) ?> (ID: <?= $repue['id'] ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Asignaciones -->
                        <div class="form-section">
                            <h6><i class="bi bi-people"></i> Asignaciones</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Conductor</label>
                                    <select name="cond_id" id="editar_cond_id" class="form-select">
                                        <option value="">Sin asignar</option>
                                        <?php foreach($conductores as $conductor): ?>
                                            <option value="<?= $conductor['id'] ?>">
                                                <?= htmlspecialchars($conductor['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Técnico *</label>
                                    <select name="users_id" id="editar_users_id" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach($tecnicos as $tecnico): ?>
                                            <option value="<?= $tecnico['id'] ?>">
                                                <?= htmlspecialchars($tecnico['nombre'] . ' ' . $tecnico['apellido']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Alerta (opcional)</label>
                                    <select name="alert_id" id="editar_alert_id" class="form-select">
                                        <option value="">Sin alerta</option>
                                        <?php foreach($alertas as $alerta): ?>
                                            <option value="<?= $alerta['id'] ?>">
                                                <?= htmlspecialchars($alerta['descripcion']) ?> (ID: <?= $alerta['id'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i>Actualizar Orden
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Ver Detalles -->
    <div class="modal fade" id="modalVerOrden" tabindex="-1" aria-labelledby="modalVerOrdenLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalVerOrdenLabel">
                        <i class="fas fa-eye me-2"></i>Detalles de la Orden de Trabajo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Información Principal -->
                    <div class="form-section">
                        <h6><i class="bi bi-info-circle"></i> Información Principal</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted">ID de la Orden</label>
                                <p class="fw-bold mb-0" id="ver_id">#123</p>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label text-muted">Nombre del Trabajo</label>
                                <p class="fw-bold mb-0" id="ver_nombre_trabajo">Nombre del trabajo</p>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted">Descripción</label>
                                <p class="mb-0" id="ver_descripcion">Descripción del trabajo</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Repuesto</label>
                                <p class="mb-0" id="ver_repuesto">
                                    <span class="badge bg-secondary">Sin repuesto</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Alerta Asociada</label>
                                <p class="mb-0" id="ver_alerta">
                                    <span class="badge bg-secondary">Sin alerta</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Fechas y Estado -->
                    <div class="form-section">
                        <h6><i class="bi bi-calendar"></i> Fechas y Estado</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted">Fecha de Creación</label>
                                <p class="mb-0" id="ver_fecha_creacion">2023-10-17</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted">Fecha Estimada</label>
                                <p class="mb-0" id="ver_fecha_estimada">2023-10-20</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted">Estado</label>
                                <p class="mb-0" id="ver_estado">
                                    <span class="badge badge-warning">Pendiente</span>
                                </p>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-muted">Prioridad</label>
                                <p class="mb-0" id="ver_prioridad">
                                    <span class="badge badge-info">Media</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Asignaciones -->
                    <div class="form-section">
                        <h6><i class="bi bi-people"></i> Personal Asignado</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Conductor Asignado</label>
                                <p class="mb-0" id="ver_conductor">
                                    <i class="fas fa-user me-2"></i>
                                    <span id="ver_conductor_nombre">Sin asignar</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Técnico Responsable</label>
                                <p class="mb-0" id="ver_tecnico">
                                    <i class="fas fa-user-cog me-2"></i>
                                    <span id="ver_tecnico_nombre">Técnico asignado</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Progreso Visual -->
                    <div class="form-section">
                        <h6><i class="bi bi-graph-up"></i> Progreso</h6>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar" id="ver_progreso" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                <span class="fw-bold" id="ver_progreso_texto">25% - Pendiente</span>
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block">Estado actual del trabajo</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cerrar
                    </button>
                    <div id="ver_acciones_admin" style="display: none;">
                        <button type="button" class="btn btn-warning" onclick="editarDesdeVer()">
                            <i class="fas fa-edit me-1"></i>Editar Orden
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inicializar tooltips para mejor UX
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar todos los tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Agregar efecto de carga a las tablas
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 50);
            });

            // Mejorar la experiencia de los formularios
            const inputs = document.querySelectorAll('.form-control, .form-select');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                    this.parentElement.style.transition = 'transform 0.2s ease';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
        });

        // Datos para referencias rápidas
        const conductoresData = <?= json_encode($conductoresById) ?>;
        const tecnicosData = <?= json_encode($tecnicosById) ?>;

        // Función para confirmar eliminación
        function confirmarEliminar(id, nombreTrabajo) {
            document.getElementById('ordenIdEliminar').textContent = id;
            document.getElementById('ordenNombreEliminar').textContent = nombreTrabajo;
            document.getElementById('btnConfirmarEliminar').href = 'eliminar_orden.php?id=' + id;
            
            const modal = new bootstrap.Modal(document.getElementById('modalEliminarOrden'));
            modal.show();
        }

        // Función para editar orden
        function editarOrden(orden) {
            // Llenar los campos del modal con los datos de la orden
            document.getElementById('editar_id').value = orden.id;
            document.getElementById('editar_nombre_trabajo').value = orden.nombre_trabajo;
            document.getElementById('editar_descripcion').value = orden.descripcion;
            document.getElementById('editar_nombre_repuesto').value = orden.nombre_repuesto || '';
            document.getElementById('editar_fecha_estimada').value = orden.fecha_estimada;
            
            // Asignar estado - con validación
            const estadoSelect = document.getElementById('editar_estado');
            estadoSelect.value = orden.estado;
            // Si no se seleccionó correctamente, buscar la opción correspondiente
            if (estadoSelect.value !== orden.estado) {
                const estadoOptions = estadoSelect.options;
                for (let i = 0; i < estadoOptions.length; i++) {
                    if (estadoOptions[i].value === orden.estado || estadoOptions[i].text.toLowerCase() === orden.estado.toLowerCase()) {
                        estadoSelect.selectedIndex = i;
                        break;
                    }
                }
            }
            
            // Asignar prioridad - con validación
            const prioridadSelect = document.getElementById('editar_prioridad');
            prioridadSelect.value = orden.prioridad;
            // Si no se seleccionó correctamente, buscar la opción correspondiente
            if (prioridadSelect.value !== orden.prioridad) {
                const prioridadOptions = prioridadSelect.options;
                for (let i = 0; i < prioridadOptions.length; i++) {
                    if (prioridadOptions[i].value === orden.prioridad || prioridadOptions[i].text.toLowerCase() === orden.prioridad.toLowerCase()) {
                        prioridadSelect.selectedIndex = i;
                        break;
                    }
                }
            }
            
            document.getElementById('editar_cond_id').value = orden.cond_id || '';
            document.getElementById('editar_users_id').value = orden.users_id;
            document.getElementById('editar_alert_id').value = orden.alert_id || '';
            
            // Debug - mostrar valores en consola para verificar (comentado para producción)
            // console.log('Editando orden:', orden);
            // console.log('Estado asignado:', estadoSelect.value);
            // console.log('Prioridad asignada:', prioridadSelect.value);
            
            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('modalEditarOrden'));
            modal.show();
        }

        // Variable global para almacenar la orden actual (para usar en editarDesdeVer)
        let ordenActual = null;

        // Función para ver detalles de la orden
        function verOrden(orden) {
            ordenActual = orden; // Guardar para uso posterior
            
            // Información Principal
            document.getElementById('ver_id').textContent = '#' + orden.id;
            document.getElementById('ver_nombre_trabajo').textContent = orden.nombre_trabajo;
            document.getElementById('ver_descripcion').textContent = orden.descripcion;
            
            // Repuesto
            const repuestoElement = document.getElementById('ver_repuesto');
            if (orden.nombre_repuesto && orden.nombre_repuesto.trim() !== '') {
                repuestoElement.innerHTML = '<span class="badge bg-primary">' + orden.nombre_repuesto + '</span>';
            } else {
                repuestoElement.innerHTML = '<span class="badge bg-secondary">Sin repuesto</span>';
            }
            
            // Alerta
            const alertaElement = document.getElementById('ver_alerta');
            if (orden.alert_id && orden.alert_id !== '0') {
                alertaElement.innerHTML = '<span class="badge bg-warning text-dark">Alerta ID: ' + orden.alert_id + '</span>';
            } else {
                alertaElement.innerHTML = '<span class="badge bg-secondary">Sin alerta</span>';
            }
            
            // Fechas
            document.getElementById('ver_fecha_creacion').textContent = orden.fecha_creacion;
            document.getElementById('ver_fecha_estimada').textContent = orden.fecha_estimada;
            
            // Estado con badge
            const estadoElement = document.getElementById('ver_estado');
            let estadoClass = '';
            switch(orden.estado) {
                case 'completada': estadoClass = 'badge-success'; break;
                case 'en_proceso': estadoClass = 'badge-info'; break;
                case 'pendiente': estadoClass = 'badge-warning'; break;
                case 'cancelada': estadoClass = 'badge-danger'; break;
                default: estadoClass = 'badge-secondary';
            }
            estadoElement.innerHTML = '<span class="badge ' + estadoClass + '">' + orden.estado.charAt(0).toUpperCase() + orden.estado.slice(1) + '</span>';
            
            // Prioridad con badge
            const prioridadElement = document.getElementById('ver_prioridad');
            let prioridadClass = '';
            switch(orden.prioridad) {
                case 'critica': prioridadClass = 'badge-danger'; break;
                case 'alta': prioridadClass = 'badge-warning'; break;
                case 'media': prioridadClass = 'badge-info'; break;
                case 'baja': prioridadClass = 'badge-success'; break;
                default: prioridadClass = 'badge-secondary';
            }
            prioridadElement.innerHTML = '<span class="badge ' + prioridadClass + '">' + orden.prioridad.charAt(0).toUpperCase() + orden.prioridad.slice(1) + '</span>';
            
            // Personal asignado
            const conductorElement = document.getElementById('ver_conductor_nombre');
            if (orden.cond_id && conductoresData[orden.cond_id]) {
                conductorElement.textContent = conductoresData[orden.cond_id];
            } else {
                conductorElement.textContent = 'Sin asignar';
            }
            
            const tecnicoElement = document.getElementById('ver_tecnico_nombre');
            if (orden.users_id && tecnicosData[orden.users_id]) {
                tecnicoElement.textContent = tecnicosData[orden.users_id];
            } else {
                tecnicoElement.textContent = 'Técnico ID: ' + orden.users_id;
            }
            
            // Barra de progreso
            const progresoBar = document.getElementById('ver_progreso');
            const progresoTexto = document.getElementById('ver_progreso_texto');
            let progreso = 0;
            let progresoColor = '';
            let progresoText = '';
            
            switch(orden.estado) {
                case 'pendiente':
                    progreso = 25;
                    progresoColor = 'bg-warning';
                    progresoText = '25% - Pendiente';
                    break;
                case 'en_proceso':
                    progreso = 60;
                    progresoColor = 'bg-info';
                    progresoText = '60% - En Proceso';
                    break;
                case 'completada':
                    progreso = 100;
                    progresoColor = 'bg-success';
                    progresoText = '100% - Completada';
                    break;
                case 'cancelada':
                    progreso = 0;
                    progresoColor = 'bg-danger';
                    progresoText = '0% - Cancelada';
                    break;
                default:
                    progreso = 10;
                    progresoColor = 'bg-secondary';
                    progresoText = '10% - Estado desconocido';
            }
            
            progresoBar.className = 'progress-bar ' + progresoColor;
            progresoBar.style.width = progreso + '%';
            progresoBar.setAttribute('aria-valuenow', progreso);
            progresoTexto.textContent = progresoText;
            
            // Mostrar/ocultar botones de acción según el rol
            const accionesAdmin = document.getElementById('ver_acciones_admin');
            // Usar PHP para determinar si mostrar los botones
            <?php if (!$rol_conductor): ?>
            accionesAdmin.style.display = 'block';
            <?php else: ?>
            accionesAdmin.style.display = 'none';
            <?php endif; ?>
            
            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('modalVerOrden'));
            modal.show();
        }

        // Función para editar desde el modal de ver
        function editarDesdeVer() {
            // Cerrar modal de ver
            const modalVer = bootstrap.Modal.getInstance(document.getElementById('modalVerOrden'));
            modalVer.hide();
            
            // Abrir modal de editar con los datos
            setTimeout(() => {
                editarOrden(ordenActual);
            }, 300);
        }

        // Función para mostrar notificaciones
        function mostrarNotificacion(tipo, titulo, mensaje) {
            const modal = document.getElementById('modalNotificacion');
            const header = document.getElementById('modalNotificacionHeader');
            const icon = document.getElementById('modalNotificacionIcon');
            const tituloElement = document.getElementById('modalNotificacionTitulo');
            const mensajeElement = document.getElementById('modalNotificacionMensaje');
            
            // Configurar colores y iconos según el tipo
            if (tipo === 'exito') {
                header.className = 'modal-header bg-success text-white';
                icon.className = 'fas fa-check-circle text-success';
            } else if (tipo === 'error') {
                header.className = 'modal-header bg-danger text-white';
                icon.className = 'fas fa-exclamation-triangle text-danger';
            } else if (tipo === 'info') {
                header.className = 'modal-header bg-info text-white';
                icon.className = 'fas fa-info-circle text-info';
            }
            
            tituloElement.textContent = titulo;
            mensajeElement.textContent = mensaje;
            
            const modalInstance = new bootstrap.Modal(modal);
            modalInstance.show();
        }

        // Verificar parámetros URL para mostrar notificaciones automáticamente
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const mensaje = urlParams.get('mensaje');
            const tipo = urlParams.get('tipo');
            
            if (mensaje && tipo) {
                let titulo = 'Notificación';
                if (tipo === 'exito') titulo = '¡Operación Exitosa!';
                else if (tipo === 'error') titulo = 'Error en la Operación';
                
                mostrarNotificacion(tipo, titulo, decodeURIComponent(mensaje));
                
                // Limpiar parámetros de la URL
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }

            // Limpiar formularios al cerrar modales
            document.getElementById('modalCrearOrden').addEventListener('hidden.bs.modal', function () {
                document.getElementById('formCrearOrden').reset();
            });

            document.getElementById('modalEditarOrden').addEventListener('hidden.bs.modal', function () {
                document.getElementById('formEditarOrden').reset();
            });

            // Función de debugging para verificar formularios
            window.debugFormulario = function() {
                const estado = document.getElementById('editar_estado').value;
                const prioridad = document.getElementById('editar_prioridad').value;
                console.log('Estado actual:', estado);
                console.log('Prioridad actual:', prioridad);
                console.log('Formulario completo:', new FormData(document.getElementById('formEditarOrden')));
            };
        });

        // Validaciones de formularios
        document.getElementById('formCrearOrden').addEventListener('submit', function(e) {
            const nombreTrabajo = document.querySelector('#modalCrearOrden input[name="nombre_trabajo"]').value.trim();
            const descripcion = document.querySelector('#modalCrearOrden textarea[name="descripcion"]').value.trim();
            const fechaEstimada = document.querySelector('#modalCrearOrden input[name="fecha_estimada"]').value;
            const tecnico = document.querySelector('#modalCrearOrden select[name="users_id"]').value;

            if (!nombreTrabajo) {
                e.preventDefault();
                alert('El nombre del trabajo es obligatorio');
                return false;
            }
            if (!descripcion) {
                e.preventDefault();
                alert('La descripción es obligatoria');
                return false;
            }
            if (!fechaEstimada) {
                e.preventDefault();
                alert('La fecha estimada es obligatoria');
                return false;
            }
            if (!tecnico) {
                e.preventDefault();
                alert('Debe seleccionar un técnico');
                return false;
            }
        });

        document.getElementById('formEditarOrden').addEventListener('submit', function(e) {
            const id = document.querySelector('#modalEditarOrden input[name="id"]').value;
            const nombreTrabajo = document.querySelector('#modalEditarOrden input[name="nombre_trabajo"]').value.trim();
            const descripcion = document.querySelector('#modalEditarOrden textarea[name="descripcion"]').value.trim();
            const fechaEstimada = document.querySelector('#modalEditarOrden input[name="fecha_estimada"]').value;
            const tecnico = document.querySelector('#modalEditarOrden select[name="users_id"]').value;
            const estado = document.querySelector('#modalEditarOrden select[name="estado"]').value;
            const prioridad = document.querySelector('#modalEditarOrden select[name="prioridad"]').value;

            console.log('Validando formulario de edición:', {
                id,
                nombreTrabajo,
                descripcion,
                fechaEstimada,
                tecnico,
                estado,
                prioridad
            });

            if (!id) {
                e.preventDefault();
                alert('Error: ID de orden no encontrado');
                return false;
            }
            if (!nombreTrabajo) {
                e.preventDefault();
                alert('El nombre del trabajo es obligatorio');
                return false;
            }
            if (!descripcion) {
                e.preventDefault();
                alert('La descripción es obligatoria');
                return false;
            }
            if (!fechaEstimada) {
                e.preventDefault();
                alert('La fecha estimada es obligatoria');
                return false;
            }
            if (!tecnico) {
                e.preventDefault();
                alert('Debe seleccionar un técnico');
                return false;
            }
            if (!estado) {
                e.preventDefault();
                alert('Debe seleccionar un estado');
                return false;
            }
            if (!prioridad) {
                e.preventDefault();
                alert('Debe seleccionar una prioridad');
                return false;
            }

            console.log('Formulario válido, enviando...');
            
            // Verificar todos los campos del formulario antes del envío
            const formData = new FormData(this);
            console.log('Datos del formulario completo:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
        });

        // Función para limpiar solo el campo de texto de nombre del trabajo
        function limpiarTexto() {
            const campoTexto = document.querySelector('input[name="nombre_trabajo"]');
            if (campoTexto) {
                campoTexto.value = '';
                campoTexto.focus();
                
                // Opcional: mostrar mensaje informativo
                const mensaje = document.createElement('div');
                mensaje.className = 'alert alert-success alert-dismissible fade show mt-2';
                mensaje.innerHTML = `
                    <i class="bi bi-check-circle"></i>
                    Campo de búsqueda limpiado. 
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                campoTexto.parentNode.appendChild(mensaje);
                
                // Auto-remover el mensaje después de 3 segundos
                setTimeout(() => {
                    if (mensaje.parentNode) {
                        mensaje.remove();
                    }
                }, 3000);
            }
        }

        // Agregar funcionalidad de búsqueda en tiempo real (opcional)
        document.addEventListener('DOMContentLoaded', function() {
            const campoTexto = document.querySelector('input[name="nombre_trabajo"]');
            if (campoTexto) {
                // Agregar placeholder dinámico
                const placeholders = [
                    'Buscar por nombre...',
                    'Ej: Cambio de aceite',
                    'Ej: Reparación de frenos',
                    'Ej: Mantenimiento preventivo'
                ];
                
                let currentPlaceholder = 0;
                setInterval(() => {
                    currentPlaceholder = (currentPlaceholder + 1) % placeholders.length;
                    campoTexto.placeholder = placeholders[currentPlaceholder];
                }, 3000);
            }
        });
    </script>
</body>
</html>
