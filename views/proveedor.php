<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
require_once '../controllers/ProveedorController.php';

// Manejo de acciones POST y DELETE
$controller = new ProveedorController();

if (!$rol_conductor) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->store($_POST);
        // El controlador maneja la redirección
    }
    if (isset($_GET['delete'])) {
        $controller->delete($_GET['delete']);
        // El controlador maneja la redirección
    }
}

// Mensajes de éxito y error
$mensaje_success = '';
$mensaje_error = '';

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'creado':
            $mensaje_success = 'Proveedor creado exitosamente';
            break;
        case 'actualizado':
            $mensaje_success = 'Proveedor actualizado exitosamente';
            break;
        case 'eliminado':
            $mensaje_success = 'Proveedor eliminado exitosamente';
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'no_eliminar':
            $mensaje_error = 'No se pudo eliminar el proveedor';
            break;
        case 'error_eliminar':
            $mensaje_error = 'Error al eliminar el proveedor';
            break;
        default:
            $mensaje_error = urldecode($_GET['error']);
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proveedores - TruckSISX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
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
            background: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\"><circle cx=\"50\" cy=\"50\" r=\"40\" fill=\"none\" stroke=\"rgba(255,255,255,0.1)\" stroke-width=\"2\"/></svg>');
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
        .card, .table-responsive {
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
    <div class="main-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">
                    <i class="bi bi-truck"></i> Gestión de Proveedores
                </h1>
                <p class="mb-0 opacity-75">Administración de proveedores de repuestos y servicios</p>
            </div>
            <?php if (!$rol_conductor): ?>
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-plus-circle"></i> Agregar Proveedor
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mensajes de alerta -->
    <?php if (!empty($mensaje_success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> <?= htmlspecialchars($mensaje_success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (!empty($mensaje_error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($mensaje_error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">
                <i class="bi bi-funnel"></i> Filtros de Búsqueda
            </h5>
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Nombre del Proveedor</label>
                    <input type="text" name="nom_proveedor" class="form-control" value="<?= htmlspecialchars($_GET['nom_proveedor'] ?? '') ?>" placeholder="Buscar por nombre...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Marca que Distribuye</label>
                    <input type="text" name="mar_distribuye" class="form-control" value="<?= htmlspecialchars($_GET['mar_distribuye'] ?? '') ?>" placeholder="Toyota, Ford, etc...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ciudad/Departamento</label>
                    <input type="text" name="ciudad_depar" class="form-control" value="<?= htmlspecialchars($_GET['ciudad_depar'] ?? '') ?>" placeholder="Bogotá, Medellín...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">País</label>
                    <select name="pais" class="form-select">
                        <option value="">Todos los países</option>
                        <option value="Colombia" <?= ($_GET['pais'] ?? '') === 'Colombia' ? 'selected' : '' ?>>Colombia</option>
                        <option value="México" <?= ($_GET['pais'] ?? '') === 'México' ? 'selected' : '' ?>>México</option>
                        <option value="Brasil" <?= ($_GET['pais'] ?? '') === 'Brasil' ? 'selected' : '' ?>>Brasil</option>
                        <option value="Argentina" <?= ($_GET['pais'] ?? '') === 'Argentina' ? 'selected' : '' ?>>Argentina</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="proveedor.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla simplificada -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>NIT</th>
                            <th>Contacto</th>
                            <th>Ciudad</th>
                            <th>País</th>
                            <th>Tipo Repuesto</th>
                            <th>Repuestos</th>
                            <?php if (!$rol_conductor): ?>
                            <th class="text-center" width="180">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $controller = new ProveedorController();
                    $filtros = [
                        'nom_proveedor' => $_GET['nom_proveedor'] ?? '',
                        'mar_distribuye' => $_GET['mar_distribuye'] ?? '',
                        'ciudad_depar' => $_GET['ciudad_depar'] ?? '',
                        'pais' => $_GET['pais'] ?? ''
                    ];
                    $proveedores = $controller->index($filtros);
                    while ($row = $proveedores->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($row['nom_proveedor']) ?></strong>
                                <?php if (!empty($row['mar_distribuye'])): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($row['mar_distribuye']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['nit_num_identi']) ?></td>
                            <td>
                                <?php if (!empty($row['tel_contacto'])): ?>
                                <i class="bi bi-telephone"></i> <?= htmlspecialchars($row['tel_contacto']) ?><br>
                                <?php endif; ?>
                                <?php if (!empty($row['correo'])): ?>
                                <i class="bi bi-envelope"></i> <?= htmlspecialchars($row['correo']) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['ciudad_depar']) ?></td>
                            <td><?= htmlspecialchars($row['pais']) ?></td>
                            <td><?= htmlspecialchars($row['tip_repuesto']) ?></td>
                            <td>
                                <?php 
                                $totalRepuestos = $row['total_repuestos'] ?? 0;
                                if ($totalRepuestos > 0): ?>
                                    <span class="badge bg-success"><?= $totalRepuestos ?> repuesto(s)</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Sin repuestos</span>
                                <?php endif; ?>
                            </td>
                            <?php if (!$rol_conductor): ?>
                            <td class="text-center">
                                <button type="button" class="btn btn-info btn-sm me-1" onclick="viewProvider(<?= $row['id'] ?>)" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-warning btn-sm me-1" onclick="editProvider(<?= $row['id'] ?>)" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteProvider(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nom_proveedor'], ENT_QUOTES) ?>')" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 text-end">
        <a href="gestiones.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Panel de Gestiones
        </a>
    </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">
                    <i class="bi bi-plus-circle"></i> Agregar Nuevo Proveedor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createForm" method="post">
                <div class="modal-body">
                    <div class="form-section">
                        <h6><i class="bi bi-building"></i> Información de la Empresa</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">NIT/Identificación <span class="text-danger">*</span></label>
                                <input type="text" name="nit_num_identi" class="form-control" required placeholder="123456789-0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nombre del Proveedor <span class="text-danger">*</span></label>
                                <input type="text" name="nom_proveedor" class="form-control" required placeholder="Nombre de la empresa">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo de Repuesto</label>
                                <input type="text" name="tip_repuesto" class="form-control" placeholder="Motor, frenos, transmisión...">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Marca que Distribuye</label>
                                <input type="text" name="mar_distribuye" class="form-control" placeholder="Toyota, Ford, Chevrolet...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Zonas de Cobertura</label>
                                <input type="text" name="zon_cobertura" class="form-control" placeholder="Nacional, Regional, Local...">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h6><i class="bi bi-geo-alt"></i> Ubicación</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Dirección</label>
                                <input type="text" name="direccion" class="form-control" placeholder="Dirección completa">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Ciudad/Departamento</label>
                                <input type="text" name="ciudad_depar" class="form-control" placeholder="Bogotá D.C.">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">País</label>
                                <select name="pais" class="form-select">
                                    <option value="Colombia">Colombia</option>
                                    <option value="México">México</option>
                                    <option value="Brasil">Brasil</option>
                                    <option value="Argentina">Argentina</option>
                                    <option value="Chile">Chile</option>
                                    <option value="Perú">Perú</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h6><i class="bi bi-person-badge"></i> Información de Contacto</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Teléfono de Contacto</label>
                                <input type="text" name="tel_contacto" class="form-control" placeholder="+57 123 456 7890">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" name="correo" class="form-control" placeholder="contacto@proveedor.com">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cargo del Contacto</label>
                                <input type="text" name="carg_contacto" class="form-control" placeholder="Gerente de Ventas">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h6><i class="bi bi-credit-card"></i> Información Comercial</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Tiempo de Entrega</label>
                                <select name="tiem_entrega" class="form-select">
                                    <option value="">Seleccionar...</option>
                                    <option value="Inmediato">Inmediato</option>
                                    <option value="1-3 días">1-3 días</option>
                                    <option value="1 semana">1 semana</option>
                                    <option value="2 semanas">2 semanas</option>
                                    <option value="1 mes">1 mes</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Forma de Pago</label>
                                <select name="for_pago" class="form-select">
                                    <option value="">Seleccionar...</option>
                                    <option value="Contado">Contado</option>
                                    <option value="Crédito 30 días">Crédito 30 días</option>
                                    <option value="Crédito 60 días">Crédito 60 días</option>
                                    <option value="Crédito 90 días">Crédito 90 días</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Crédito Disponible</label>
                                <input type="text" name="cred_disponible" class="form-control" placeholder="$0.00">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label">Cuenta Bancaria</label>
                                <input type="text" name="cuen_bancaria" class="form-control" placeholder="Banco - Número de cuenta">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Guardar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">
                    <i class="bi bi-eye"></i> Detalles del Proveedor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-section">
                    <h6><i class="bi bi-building"></i> Información de la Empresa</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <p><strong>ID:</strong> <span id="view-id"></span></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>NIT:</strong> <span id="view-nit"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> <span id="view-nombre"></span></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Tipo de Repuesto:</strong> <span id="view-tipo"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Marca que Distribuye:</strong> <span id="view-marca"></span></p>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h6><i class="bi bi-geo-alt"></i> Ubicación</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Dirección:</strong> <span id="view-direccion"></span></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Ciudad:</strong> <span id="view-ciudad"></span></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>País:</strong> <span id="view-pais"></span></p>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h6><i class="bi bi-person-badge"></i> Contacto</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Teléfono:</strong> <span id="view-telefono"></span></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Email:</strong> <span id="view-email"></span></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Cargo:</strong> <span id="view-cargo"></span></p>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h6><i class="bi bi-credit-card"></i> Información Comercial</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Tiempo Entrega:</strong> <span id="view-tiempo"></span></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Forma de Pago:</strong> <span id="view-pago"></span></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Crédito:</strong> <span id="view-credito"></span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">
                    <i class="bi bi-pencil"></i> Editar Proveedor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" method="post">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="form-section">
                        <h6><i class="bi bi-building"></i> Información de la Empresa</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">NIT/Identificación <span class="text-danger">*</span></label>
                                <input type="text" name="nit_num_identi" id="edit-nit" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nombre del Proveedor <span class="text-danger">*</span></label>
                                <input type="text" name="nom_proveedor" id="edit-nombre" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo de Repuesto</label>
                                <input type="text" name="tip_repuesto" id="edit-tipo" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Marca que Distribuye</label>
                                <input type="text" name="mar_distribuye" id="edit-marca" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Zonas de Cobertura</label>
                                <input type="text" name="zon_cobertura" id="edit-zona" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h6><i class="bi bi-geo-alt"></i> Ubicación</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Dirección</label>
                                <input type="text" name="direccion" id="edit-direccion" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Ciudad/Departamento</label>
                                <input type="text" name="ciudad_depar" id="edit-ciudad" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">País</label>
                                <select name="pais" id="edit-pais" class="form-select">
                                    <option value="Colombia">Colombia</option>
                                    <option value="México">México</option>
                                    <option value="Brasil">Brasil</option>
                                    <option value="Argentina">Argentina</option>
                                    <option value="Chile">Chile</option>
                                    <option value="Perú">Perú</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h6><i class="bi bi-person-badge"></i> Información de Contacto</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Teléfono de Contacto</label>
                                <input type="text" name="tel_contacto" id="edit-telefono" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" name="correo" id="edit-email" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cargo del Contacto</label>
                                <input type="text" name="carg_contacto" id="edit-cargo" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h6><i class="bi bi-credit-card"></i> Información Comercial</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Tiempo de Entrega</label>
                                <select name="tiem_entrega" id="edit-tiempo" class="form-select">
                                    <option value="">Seleccionar...</option>
                                    <option value="Inmediato">Inmediato</option>
                                    <option value="1-3 días">1-3 días</option>
                                    <option value="1 semana">1 semana</option>
                                    <option value="2 semanas">2 semanas</option>
                                    <option value="1 mes">1 mes</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Forma de Pago</label>
                                <select name="for_pago" id="edit-pago" class="form-select">
                                    <option value="">Seleccionar...</option>
                                    <option value="Contado">Contado</option>
                                    <option value="Crédito 30 días">Crédito 30 días</option>
                                    <option value="Crédito 60 días">Crédito 60 días</option>
                                    <option value="Crédito 90 días">Crédito 90 días</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Crédito Disponible</label>
                                <input type="text" name="cred_disponible" id="edit-credito" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label">Cuenta Bancaria</label>
                                <input type="text" name="cuen_bancaria" id="edit-cuenta" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle"></i> Actualizar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Función para ver proveedor
function viewProvider(id) {
    fetch(`proveedor.php?ajax=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('view-id').textContent = data.id || '';
            document.getElementById('view-nit').textContent = data.nit_num_identi || '';
            document.getElementById('view-nombre').textContent = data.nom_proveedor || '';
            document.getElementById('view-tipo').textContent = data.tip_repuesto || 'N/A';
            document.getElementById('view-marca').textContent = data.mar_distribuye || 'N/A';
            document.getElementById('view-direccion').textContent = data.direccion || 'N/A';
            document.getElementById('view-ciudad').textContent = data.ciudad_depar || 'N/A';
            document.getElementById('view-pais').textContent = data.pais || 'N/A';
            document.getElementById('view-telefono').textContent = data.tel_contacto || 'N/A';
            document.getElementById('view-email').textContent = data.correo || 'N/A';
            document.getElementById('view-cargo').textContent = data.carg_contacto || 'N/A';
            document.getElementById('view-tiempo').textContent = data.tiem_entrega || 'N/A';
            document.getElementById('view-pago').textContent = data.for_pago || 'N/A';
            document.getElementById('view-credito').textContent = data.cred_disponible || 'N/A';
            
            new bootstrap.Modal(document.getElementById('viewModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos');
        });
}

// Función para editar proveedor
function editProvider(id) {
    fetch(`proveedor.php?ajax=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit-id').value = data.id || '';
            document.getElementById('edit-nit').value = data.nit_num_identi || '';
            document.getElementById('edit-nombre').value = data.nom_proveedor || '';
            document.getElementById('edit-tipo').value = data.tip_repuesto || '';
            document.getElementById('edit-marca').value = data.mar_distribuye || '';
            document.getElementById('edit-zona').value = data.zon_cobertura || '';
            document.getElementById('edit-direccion').value = data.direccion || '';
            document.getElementById('edit-ciudad').value = data.ciudad_depar || '';
            document.getElementById('edit-pais').value = data.pais || '';
            document.getElementById('edit-telefono').value = data.tel_contacto || '';
            document.getElementById('edit-email').value = data.correo || '';
            document.getElementById('edit-cargo').value = data.carg_contacto || '';
            document.getElementById('edit-tiempo').value = data.tiem_entrega || '';
            document.getElementById('edit-pago').value = data.for_pago || '';
            document.getElementById('edit-credito').value = data.cred_disponible || '';
            document.getElementById('edit-cuenta').value = data.cuen_bancaria || '';
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos');
        });
}

// Función para eliminar proveedor
function deleteProvider(id, nombre) {
    if (confirm(`¿Está seguro de eliminar el proveedor "${nombre}"?`)) {
        window.location.href = `proveedor.php?delete=${id}`;
    }
}

// Validación del formulario de crear
document.getElementById('createForm').addEventListener('submit', function(e) {
    const nit = this.querySelector('[name="nit_num_identi"]').value.trim();
    const nombre = this.querySelector('[name="nom_proveedor"]').value.trim();
    
    if (!nit) {
        e.preventDefault();
        alert('El NIT/Identificación es obligatorio');
        return false;
    }
    
    if (!nombre) {
        e.preventDefault();
        alert('El nombre del proveedor es obligatorio');
        return false;
    }
});

// Validación del formulario de editar
document.getElementById('editForm').addEventListener('submit', function(e) {
    const nit = this.querySelector('[name="nit_num_identi"]').value.trim();
    const nombre = this.querySelector('[name="nom_proveedor"]').value.trim();
    
    if (!nit) {
        e.preventDefault();
        alert('El NIT/Identificación es obligatorio');
        return false;
    }
    
    if (!nombre) {
        e.preventDefault();
        alert('El nombre del proveedor es obligatorio');
        return false;
    }
});
</script>

</body>
</html>