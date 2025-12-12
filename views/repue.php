<?php
require_once '../controllers/RepueController.php';
$controller = new RepueController();
session_start();
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';

// Manejo de petición AJAX para obtener datos de repuesto
if (isset($_GET['ajax']) && $_GET['ajax'] == '1' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    try {
        $repuesto = $controller->show($_GET['id']);
        if ($repuesto) {
            echo json_encode([
                'success' => true,
                'repuesto' => $repuesto
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Repuesto no encontrado'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener los datos: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Manejo de POST y delete antes de cualquier salida
if (!$rol_conductor) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // El método save del modelo maneja tanto creación como edición
        $controller->store($_POST);
        header('Location: repue.php');
        exit;
    }
    if (isset($_GET['delete'])) {
        try {
            $controller->delete($_GET['delete']);
            
            // Si es una petición AJAX, devolver respuesta JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['success' => true, 'message' => 'Repuesto eliminado correctamente']);
                exit;
            }
            
            header('Location: repue.php');
            exit;
        } catch (Exception $e) {
            // Si es una petición AJAX, devolver error JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
            
            // Para peticiones normales, redirigir con error
            header('Location: repue.php?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}
// Filtros
$filtros = [];
foreach ([
    'nombre', 'marca_repuesto', 'proveedor_id', 'cat_repu_id', 'subcat_repu_id', 'modelo', 'medidas_espe', 'norma_estan', 'numero_parte', 'des_tecnica', 'veh_compatible', 'estado_repus', 'num_factura', 'ubi_almacen', 'dest_area', 'firma_verificacion'
] as $campo) {
    if (isset($_GET[$campo]) && $_GET[$campo] !== '') {
        $filtros[$campo] = $_GET[$campo];
    } else {
        $filtros[$campo] = '';
    }
}
$repuestos = $controller->index($filtros);

// Cargar proveedores para el modal de edición
require_once '../models/Proveedor.php';
$provModel = new Proveedor();
$proveedoresResult = $provModel->getAll();
$proveedoresArray = [];
while ($prov = $proveedoresResult->fetch_assoc()) {
    $proveedoresArray[] = $prov;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Repuestos</title>
    <script>
        // Datos de proveedores para el modal de edición
        window.proveedoresData = <?= json_encode($proveedoresArray) ?>;
        // Datos de categorías y subcategorías para el formulario de edición
        window.categoriasData = <?php
            require_once '../models/CatRepu.php';
            $catModelJS = new CatRepu();
            $categoriasJS = $catModelJS->getAll();
            $arrCats = [];
            while ($cat = $categoriasJS->fetch_assoc()) $arrCats[] = $cat;
            echo json_encode($arrCats);
        ?>;
        window.subcategoriasData = <?php
            require_once '../models/SubCatRepu.php';
            $subcatModelJS = new SubCatRepu();
            $subcatsJS = $subcatModelJS->getAll();
            $arrSubcats = [];
            while ($subcat = $subcatsJS->fetch_assoc()) $arrSubcats[] = $subcat;
            echo json_encode($arrSubcats);
        ?>;
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
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
            color: #475569;
            font-weight: 600;
            line-height: 1.3;
            margin-bottom: 1rem;
        }
        h1 {
            font-size: clamp(1.75rem, 4vw, 2.5rem);
            font-weight: 700;
        }
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem;
        }
        .main-container {
            background: #FFFFFF;
            border: 1px solid rgba(209, 213, 219, 0.3);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-top: 2rem;
            margin-bottom: 2rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .main-container:hover {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        .header-section {
            background: linear-gradient(135deg, #475569 0%, #334155 100%);
            color: #FFFFFF !important;
            padding: 2rem;
            border-radius: 12px 12px 0 0;
            position: relative;
            overflow: hidden;
        }
        .header-section::before {
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2"/></svg>');
            content: '';
            height: 200px;
            opacity: 0.1;
            position: absolute;
            right: -50px;
            top: -50px;
            width: 200px;
        }
        .header-section h2 {
            color: #FFFFFF;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }
        .header-section .lead {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }
        .content-section {
            padding: 2rem;
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
            color: #475569;
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
            background: linear-gradient(135deg, #475569 0%, #334155 100%);
            box-shadow: 0 4px 12px rgba(71, 85, 105, 0.3);
            color: #FFFFFF !important;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
            box-shadow: 0 6px 20px rgba(71, 85, 105, 0.4);
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }
        .btn-outline-primary, .btn-secondary {
            background: #FFFFFF;
            border: 2px solid #475569;
            color: #475569 !important;
        }
        .btn-outline-primary:hover, .btn-secondary:hover {
            background: #475569;
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
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
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
            border-color: #475569;
            box-shadow: 0 0 0 3px rgba(71, 85, 105, 0.1);
            outline: none;
        }
        .form-label {
            color: #475569;
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
            border-bottom: 2px solid #64748b;
            color: #475569;
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
            background: linear-gradient(135deg, #475569 0%, #334155 100%);
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
            background: linear-gradient(135deg, rgba(71, 85, 105, 0.08) 0%, rgba(51, 65, 85, 0.08) 100%);
        }
        .table td.text-center .btn {
            font-size: 12px;
            padding: 0.3rem 0.6rem;
            margin: 0 2px;
        }
        .table td.text-center .btn-info {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
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
            .container {
                padding: 1rem;
            }
            .main-container {
                margin-top: 1rem;
                margin-bottom: 1rem;
            }
            .header-section {
                padding: 1.5rem;
                text-align: center;
            }
            .content-section {
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
            .container {
                padding: 0.5rem;
            }
            h1 {
                font-size: 1.5rem;
            }
            .header-section {
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
<body>
<div class="container">
    <div class="main-container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><i class="bi bi-gear-fill"></i> Gestión de Repuestos</h2>
                    <p class="mb-0 opacity-75">Administra el inventario completo de repuestos del sistema</p>
                </div>
                <a href="gestiones.php" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Volver a Gestiones
                </a>
            </div>
        </div>
        
        <!-- Content Section -->
        <div class="content-section">
            <!-- Botón Crear Repuesto -->
            <div class="row mb-4">
                <div class="col-12 text-end">
                    <?php if (!$rol_conductor): ?>
                    <button type="button" class="btn btn-success btn-lg" onclick="abrirModal('modalCrearRepuesto')">
                        <i class="bi bi-plus-circle"></i> Nuevo Repuesto
                    </button>
                    <?php endif; ?>
                </div>
            </div>
    <?php 
    require_once '../models/CatRepu.php';
    require_once '../models/SubCatRepu.php';
    $catModel = new CatRepu();
    $subcatModel = new SubCatRepu();
    $categorias = $catModel->getAll();
    $subcategorias = $subcatModel->getAll();
    ?>
    <?php 
    require_once '../models/Proveedor.php';
    $provModel = new Proveedor();
    $proveedores = $provModel->getAll();
    ?>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title"><i class="bi bi-funnel"></i> Filtros de Búsqueda</h5>
            <form method="GET" action="repue.php">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Buscar por nombre" value="<?= isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Modelo</label>
                        <input type="text" name="modelo" class="form-control" placeholder="Buscar por modelo" value="<?= isset($_GET['modelo']) ? htmlspecialchars($_GET['modelo']) : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <input type="text" name="estado_repus" class="form-control" placeholder="Buscar por estado" value="<?= isset($_GET['estado_repus']) ? htmlspecialchars($_GET['estado_repus']) : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Categoría</label>
                        <select name="cat_repu_id" class="form-select">
                            <option value="">Todas las categorías</option>
                            <?php 
                            $categorias->data_seek(0);
                            while ($cat = $categorias->fetch_assoc()): 
                            ?>
                                <option value="<?= $cat['id'] ?>" <?= (isset($_GET['cat_repu_id']) && $_GET['cat_repu_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nombre']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Subcategoría</label>
                        <select name="subcat_repu_id" class="form-select">
                            <option value="">Todas las subcategorías</option>
                            <?php 
                            $subcategorias->data_seek(0);
                            while ($subcat = $subcategorias->fetch_assoc()): 
                            ?>
                                <option value="<?= $subcat['id'] ?>" <?= (isset($_GET['subcat_repu_id']) && $_GET['subcat_repu_id'] == $subcat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subcat['nombre']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Proveedor</label>
                        <select name="proveedor_id" class="form-select">
                            <option value="">Todos los proveedores</option>
                            <?php 
                            $proveedores->data_seek(0);
                            while ($prov = $proveedores->fetch_assoc()): 
                            ?>
                                <option value="<?= $prov['id'] ?>" <?= (isset($_GET['proveedor_id']) && $_GET['proveedor_id'] == $prov['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prov['nom_proveedor']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                            <a href="repue.php" class="btn btn-secondary flex-fill">
                                <i class="bi bi-x-circle"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    
    <!-- Tabla de Repuestos -->
    <div class="table-responsive">
        <div style="overflow-x:auto;">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Categoría y Subcategoría</th>
                        <th>Marca/Modelo</th>
                        <th>Proveedor</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Precio</th>
                        <th>Ubicación</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
            <tbody>
            <?php 
            require_once '../models/CatRepu.php';
            require_once '../models/SubCatRepu.php';
            require_once '../models/Proveedor.php';
            $catModel = new CatRepu();
            $subcatModel = new SubCatRepu();
            $provModel = new Proveedor();
            while ($row = $repuestos->fetch_assoc()): 
                $cat = isset($row['cat_repu_id']) ? $catModel->getById($row['cat_repu_id']) : null;
                $subcat = isset($row['subcat_repu_id']) ? $subcatModel->getById($row['subcat_repu_id']) : null;
                $prov = $provModel->getById($row['proveedor_id']);
            ?>
                <tr>
                    <td><strong>#<?= $row['id'] ?></strong></td>
                    <td>
                        <strong><?= htmlspecialchars($row['nombre']) ?></strong>
                        <?php if ($row['numero_parte']): ?>
                            <br><small class="text-muted">Parte: <?= htmlspecialchars($row['numero_parte']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($cat): ?>
                            <span class="badge bg-primary"><?= htmlspecialchars($cat['nombre']) ?></span>
                        <?php endif; ?>
                        <?php if ($subcat): ?>
                            <br><span class="badge bg-secondary mt-1"><?= htmlspecialchars($subcat['nombre']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['marca_repuesto']): ?>
                            <strong><?= htmlspecialchars($row['marca_repuesto']) ?></strong>
                        <?php endif; ?>
                        <?php if ($row['modelo']): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($row['modelo']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['proveedor_id']): ?>
                            <?php $prov = $provModel->getById($row['proveedor_id']); ?>
                            <?= $prov ? htmlspecialchars($prov['nom_proveedor']) : 'ID: ' . $row['proveedor_id'] ?>
                            <br><span class="badge bg-success">Asignado</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Sin proveedor</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?= $row['cant_stock'] > 5 ? 'bg-success' : ($row['cant_stock'] > 0 ? 'bg-warning' : 'bg-danger') ?>">
                            <?= $row['cant_stock'] ?>
                        </span>
                        <br><small class="text-muted"><?= $row['cant_stock'] > 5 ? 'Disponible' : ($row['cant_stock'] > 0 ? 'Stock bajo' : 'Agotado') ?></small>
                    </td>
                    <td>
                        <?php
                        $estado_class = [
                            'Nuevo' => 'bg-success',
                            'Usado' => 'bg-info', 
                            'Reacondicionado' => 'bg-warning text-dark',
                            'Dañado' => 'bg-danger'
                        ];
                        $class = $estado_class[$row['estado_repus']] ?? 'bg-secondary';
                        ?>
                        <span class="badge <?= $class ?>"><?= htmlspecialchars($row['estado_repus']) ?></span>
                    </td>
                    <td>
                        <?php if ($row['pre_unitario'] > 0): ?>
                            $<?= number_format($row['pre_unitario'], 2) ?>
                            <?php if ($row['costo_total'] > 0): ?>
                                <br><small class="text-muted">Total: $<?= number_format($row['costo_total'], 2) ?></small>
                            <?php endif; ?>
                        <?php else: ?>
                            <small class="text-muted">Sin precio</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['ubi_almacen']): ?>
                            <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($row['ubi_almacen']) ?>
                        <?php else: ?>
                            <small class="text-muted">Sin ubicación</small>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" 
                                    class="btn btn-outline-info" 
                                    onclick="verRepuesto(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)"
                                    title="Ver detalles">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php if (!$rol_conductor): ?>
                            <button type="button" 
                                    class="btn btn-outline-warning" 
                                    onclick="editarRepuesto(<?= $row['id'] ?>)"
                                    title="Editar repuesto">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-danger" 
                                    onclick="eliminarRepuesto(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8') ?>')"
                                    title="Eliminar repuesto">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
        </div>
    </div>
</div>

<!-- Modal Crear Repuesto -->
<div class="modal fade" id="modalCrearRepuesto" tabindex="-1" aria-labelledby="modalCrearRepuestoLabel">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCrearRepuestoLabel">
                    <i class="bi bi-plus-circle me-2"></i>Nuevo Repuesto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="repue.php">
                <div class="modal-body">
                    <!-- Sección 1: Información Básica -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold text-success mb-3">
                                <i class="bi bi-info-circle me-2"></i>Información Básica
                            </h6>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-gear me-1"></i>Nombre *</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-tag me-1"></i>Marca</label>
                            <input type="text" name="marca_repuesto" class="form-control">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-diagram-3 me-1"></i>Modelo</label>
                            <input type="text" name="modelo" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-hash me-1"></i>Número de Parte</label>
                            <input type="text" name="numero_parte" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-check-circle me-1"></i>Estado</label>
                            <select name="estado_repus" class="form-select">
                                <option value="Nuevo">Nuevo</option>
                                <option value="Usado">Usado</option>
                                <option value="Reacondicionado">Reacondicionado</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Sección 2: Categorización -->
                    <div class="row mb-4 mt-4">
                        <div class="col-12">
                            <h6 class="fw-bold text-success mb-3">
                                <i class="bi bi-tags me-2"></i>Categorización
                            </h6>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-collection me-1"></i>Categoría de repuesto</label>
                            <input type="text" id="categoria_search" class="form-control mb-2" placeholder="Buscar categoría..." autocomplete="off">
                            <input type="hidden" name="cat_repu_id" id="cat_repu_id">
                            <select id="categoria_dropdown" class="form-select" size="5" style="display: none;">
                                <option value="">Seleccione una categoría de repuesto</option>
                                <?php 
                                $categorias = $catModel->getAll();
                                while ($cat = $categorias->fetch_assoc()): 
                                ?>
                                    <option value="<?= $cat['id'] ?>" data-nombre="<?= htmlspecialchars($cat['nombre']) ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-bookmark me-1"></i>Subcategoría de repuesto</label>
                            <select name="subcat_repu_id" id="subcat_repu_id" class="form-select" disabled>
                                <option value="">Primero selecciona una categoría de repuesto</option>
                                <?php 
                                $subcategorias = $subcatModel->getAll();
                                while ($subcat = $subcategorias->fetch_assoc()): 
                                ?>
                                    <option value="<?= $subcat['id'] ?>" data-cat="<?= $subcat['cat_repu_id'] ?>"><?= htmlspecialchars($subcat['nombre']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <div id="subcatHelp" class="form-text text-muted">Primero selecciona una categoría de repuesto.</div>
                        </div>
                    </div>
                    
                    <!-- Sección 3: Especificaciones Técnicas -->
                    <div class="row mb-4 mt-4">
                        <div class="col-12">
                            <h6 class="fw-bold text-success mb-3">
                                <i class="bi bi-wrench me-2"></i>Especificaciones Técnicas
                            </h6>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-rulers me-1"></i>Medidas Específicas</label>
                            <input type="text" name="medidas_espe" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-award me-1"></i>Norma/Estándar</label>
                            <input type="text" name="norma_estan" class="form-control">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label"><i class="bi bi-file-text me-1"></i>Descripción Técnica</label>
                            <textarea name="des_tecnica" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label"><i class="bi bi-truck me-1"></i>Vehículo Compatible</label>
                            <input type="text" name="veh_compatible" class="form-control">
                        </div>
                    </div>
                    
                    <!-- Sección 4: Inventario y Proveedor -->
                    <div class="row mb-4 mt-4">
                        <div class="col-12">
                            <h6 class="fw-bold text-success mb-3">
                                <i class="bi bi-boxes me-2"></i>Inventario y Proveedor
                            </h6>
                        </div>
                    </div>
                    
                    <?php 
                    require_once '../models/Proveedor.php';
                    $provModel = new Proveedor();
                    $proveedores = $provModel->getAll();
                    ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-building me-1"></i>Proveedor</label>
                            <select name="proveedor_id" class="form-select">
                                <option value="">Seleccione un proveedor</option>
                                <?php while ($prov = $proveedores->fetch_assoc()): ?>
                                    <option value="<?= $prov['id'] ?>"><?= htmlspecialchars($prov['nom_proveedor']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label"><i class="bi bi-123 me-1"></i>Cantidad</label>
                            <input type="number" name="cantidad" class="form-control" value="1" min="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label"><i class="bi bi-box-seam me-1"></i>Stock</label>
                            <input type="number" name="cant_stock" class="form-control" value="0" min="0">
                        </div>
                    </div>
                    
                    <!-- Sección 5: Información Financiera -->
                    <div class="row mb-4 mt-4">
                        <div class="col-12">
                            <h6 class="fw-bold text-success mb-3">
                                <i class="bi bi-currency-dollar me-2"></i>Información Financiera
                            </h6>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-tag me-1"></i>Precio Unitario</label>
                            <input type="number" step="0.01" name="pre_unitario" class="form-control" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-calculator me-1"></i>Costo Total</label>
                            <input type="number" step="0.01" name="costo_total" class="form-control" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-receipt me-1"></i>Número Factura</label>
                            <input type="text" name="num_factura" class="form-control">
                        </div>
                    </div>
                    
                    <!-- Sección 6: Información Adicional -->
                    <div class="row mb-4 mt-4">
                        <div class="col-12">
                            <h6 class="fw-bold text-success mb-3">
                                <i class="bi bi-info-square me-2"></i>Información Adicional
                            </h6>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-calendar-plus me-1"></i>Fecha Ingreso</label>
                            <input type="date" name="fecha_ingreso" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-calendar-x me-1"></i>Fecha Vencimiento</label>
                            <input type="date" name="fecha_venci" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-geo-alt me-1"></i>Ubicación Almacén</label>
                            <input type="text" name="ubi_almacen" class="form-control">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-shield-check me-1"></i>Garantía</label>
                            <input type="text" name="garantia" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-person-check me-1"></i>Responsable Ingreso</label>
                            <input type="text" name="res_ingreso" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-geo me-1"></i>Área Destino</label>
                            <input type="text" name="dest_area" class="form-control">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-pen me-1"></i>Firma Verificación</label>
                            <input type="text" name="firma_verificacion" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Crear Repuesto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Repuesto -->
<div class="modal fade" id="modalVerRepuesto" tabindex="-1" aria-labelledby="modalVerRepuestoLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalVerRepuestoLabel">
                    <i class="bi bi-eye me-2"></i>Detalles del Repuesto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detallesRepuesto">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Repuesto -->
<div class="modal fade" id="modalEditarRepuesto" tabindex="-1" aria-labelledby="modalEditarRepuestoLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditarRepuestoLabel">
                    <i class="bi bi-pencil me-2"></i>Editar Repuesto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="formularioEditar">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-warning" onclick="guardarEdicion()">
                    <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar Repuesto -->
<div class="modal fade" id="modalEliminarRepuesto" tabindex="-1" aria-labelledby="modalEliminarRepuestoLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminarRepuestoLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
                <h4 class="mt-3">¿Estás seguro?</h4>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
                <div class="alert alert-warning mt-3">
                    <strong>Repuesto a eliminar:</strong>
                    <p id="nombreRepuestoEliminar" class="mb-0"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmarEliminacion()" id="btnConfirmarEliminar">
                    <i class="bi bi-trash me-1"></i>Eliminar Repuesto
                </button>
            </div>
        </div>
    </div>
</div>

<?php if (false && isset($_GET['form']) && !$rol_conductor): ?>
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title"><?= isset($editData) ? 'Editar' : 'Agregar' ?> Repuesto</h5>
            <?php 
            $editData = isset($_GET['id']) ? $controller->show($_GET['id']) : [];
            require_once '../models/CatRepu.php';
            require_once '../models/SubCatRepu.php';
            $catModel = new CatRepu();
            $subcatModel = new SubCatRepu();
            $categorias = $catModel->getAll();
            $subcategorias = $subcatModel->getAll();
            ?>
            <form method="post">
                <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Categoría de repuesto</label>
                        <select name="cat_repu_id" id="edit_cat_repu_id" class="form-control" required>
                            <option value="">Seleccione una categoría de repuesto</option>
                            <?php 
                            $categorias->data_seek(0); // Reiniciar puntero
                            while ($cat = $categorias->fetch_assoc()): ?>
                                <option value="<?= $cat['id'] ?>" <?= (isset($editData['cat_repu_id']) && $editData['cat_repu_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Subcategoría de repuesto</label>
                        <select name="subcat_repu_id" id="edit_subcat_repu_id" class="form-control" required>
                            <option value="">Seleccione una subcategoría de repuesto</option>
                            <?php 
                            $subcategorias->data_seek(0); // Reiniciar puntero
                            while ($subcat = $subcategorias->fetch_assoc()): ?>
                                <option value="<?= $subcat['id'] ?>" data-cat="<?= $subcat['cat_repu_id'] ?>" <?= (isset($editData['subcat_repu_id']) && $editData['subcat_repu_id'] == $subcat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($subcat['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <div id="edit_subcatHelp" class="form-text text-muted"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($editData['nombre'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Marca</label>
                        <input type="text" name="marca_repuesto" class="form-control" value="<?= htmlspecialchars($editData['marca_repuesto'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Proveedor</label>
                        <select name="proveedor_id" class="form-control">
                            <option value="">Sin proveedor</option>
                            <?php $provModel2 = new Proveedor(); $proveedores2 = $provModel2->getAll(); while ($prov = $proveedores2->fetch_assoc()): ?>
                                <option value="<?= $prov['id'] ?>" <?= (isset($editData['proveedor_id']) && $editData['proveedor_id'] == $prov['id']) ? 'selected' : '' ?>><?= htmlspecialchars($prov['nom_proveedor']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Modelo</label>
                        <input type="text" name="modelo" class="form-control" value="<?= htmlspecialchars($editData['modelo'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Medidas Específicas</label>
                        <input type="text" name="medidas_espe" class="form-control" value="<?= htmlspecialchars($editData['medidas_espe'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Norma/Estándar</label>
                        <input type="text" name="norma_estan" class="form-control" value="<?= htmlspecialchars($editData['norma_estan'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Número de Parte</label>
                        <input type="text" name="numero_parte" class="form-control" value="<?= htmlspecialchars($editData['numero_parte'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Descripción Técnica</label>
                        <input type="text" name="des_tecnica" class="form-control" value="<?= htmlspecialchars($editData['des_tecnica'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Vehículo Compatible</label>
                        <input type="text" name="veh_compatible" class="form-control" value="<?= htmlspecialchars($editData['veh_compatible'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" name="cantidad" class="form-control" value="<?= $editData['cantidad'] ?? '' ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Estado</label>
                        <input type="text" name="estado_repus" class="form-control" value="<?= htmlspecialchars($editData['estado_repus'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha de Ingreso</label>
                        <input type="date" name="fecha_ingreso" class="form-control" value="<?= htmlspecialchars($editData['fecha_ingreso'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Número de Factura</label>
                        <input type="text" name="num_factura" class="form-control" value="<?= htmlspecialchars($editData['num_factura'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Ubicación en Almacén</label>
                        <input type="text" name="ubi_almacen" class="form-control" value="<?= htmlspecialchars($editData['ubi_almacen'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Precio Unitario</label>
                        <input type="number" step="0.01" name="pre_unitario" class="form-control" value="<?= $editData['pre_unitario'] ?? '' ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Costo Total</label>
                        <input type="number" step="0.01" name="costo_total" class="form-control" value="<?= $editData['costo_total'] ?? '' ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Garantía</label>
                        <input type="text" name="garantia" class="form-control" value="<?= htmlspecialchars($editData['garantia'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Responsable de Ingreso</label>
                        <input type="text" name="res_ingreso" class="form-control" value="<?= htmlspecialchars($editData['res_ingreso'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="cant_stock" class="form-control" value="<?= $editData['cant_stock'] ?? '' ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha de Vencimiento</label>
                        <input type="date" name="fecha_venci" class="form-control" value="<?= htmlspecialchars($editData['fecha_venci'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Área de Destino</label>
                        <input type="text" name="dest_area" class="form-control" value="<?= htmlspecialchars($editData['dest_area'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Firma de Verificación</label>
                        <input type="text" name="firma_verificacion" class="form-control" value="<?= htmlspecialchars($editData['firma_verificacion'] ?? '') ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="repue.php" class="btn btn-secondary mx-2">Cancelar</a>
            </form>
        </div>
    </div>
    <?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Mejorar UX de filtros en móvil
document.addEventListener('DOMContentLoaded', function() {
    // Cambiar icono del colapso
    const toggleBtn = document.querySelector('[data-bs-target="#filtrosMobile"]');
    const collapse = document.getElementById('filtrosMobile');
    
    if (collapse && toggleBtn) {
        collapse.addEventListener('show.bs.collapse', function () {
            toggleBtn.querySelector('i').className = 'bi bi-chevron-up';
        });
        
        collapse.addEventListener('hide.bs.collapse', function () {
            toggleBtn.querySelector('i').className = 'bi bi-chevron-down';
        });
    }
    
    // Auto-expandir filtros si hay filtros activos
    const hasActiveFilters = <?= (!empty($filtros['nombre']) || !empty($filtros['modelo']) || !empty($filtros['estado_repus']) || !empty($filtros['cat_repu_id']) || !empty($filtros['subcat_repu_id']) || !empty($filtros['proveedor_id'])) ? 'true' : 'false' ?>;
    
    if (hasActiveFilters && collapse) {
        const bsCollapse = new bootstrap.Collapse(collapse, { show: true });
    }
});
    let repuestoIdEliminar = null;
    
    // Función para abrir modales
    function abrirModal(modalId) {
        const modalElement = document.getElementById(modalId);
        
        // Remove aria-hidden before creating modal instance
        modalElement.removeAttribute('aria-hidden');
        
        const modal = new bootstrap.Modal(modalElement);
        
        // Monitor and remove aria-hidden continuously
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'aria-hidden') {
                    if (modalElement.hasAttribute('aria-hidden')) {
                        modalElement.removeAttribute('aria-hidden');
                    }
                }
            });
        });
        
        observer.observe(modalElement, { attributes: true });
        
        // Stop observing when modal is hidden
        modalElement.addEventListener('hidden.bs.modal', function() {
            observer.disconnect();
        }, { once: true });
        
        modal.show();
    }
    
    // Función para cerrar modales
    function cerrarModal(modalId) {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            } else {
                // Si no hay instancia, crear una nueva y cerrarla
                const newModal = new bootstrap.Modal(modalElement);
                newModal.hide();
            }
        }
    }
    
    // Función para ver detalles del repuesto
    function verRepuesto(repuesto) {
        const detalles = document.getElementById('detallesRepuesto');
        
        // Determinar estado del proveedor
        let proveedorInfo = '';
        if (repuesto.proveedor_id > 0) {
            proveedorInfo = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong><i class="bi bi-building me-1"></i>ID Proveedor:</strong> ${repuesto.proveedor_id}
                    </div>
                </div>
            `;
        } else {
            proveedorInfo = `
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>Sin proveedor asignado
                </div>
            `;
        }
        
        detalles.innerHTML = `
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h4 class="card-title text-success">
                                    <i class="bi bi-gear me-2"></i>${repuesto.nombre}
                                </h4>
                                <p class="text-muted mb-0">ID: #${repuesto.id}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-success mb-3">
                            <i class="bi bi-info-circle me-2"></i>Información General
                        </h6>
                        <div class="mb-2">
                            <strong><i class="bi bi-tag me-1"></i>Marca:</strong> 
                            <span>${repuesto.marca_repuesto || 'No especificada'}</span>
                        </div>
                        <div class="mb-2">
                            <strong><i class="bi bi-diagram-3 me-1"></i>Modelo:</strong> 
                            <span>${repuesto.modelo || 'No especificado'}</span>
                        </div>
                        <div class="mb-2">
                            <strong><i class="bi bi-hash me-1"></i>Número de Parte:</strong> 
                            <span>${repuesto.numero_parte || 'No disponible'}</span>
                        </div>
                        <div class="mb-2">
                            <strong><i class="bi bi-check-circle me-1"></i>Estado:</strong> 
                            <span class="badge bg-info">${repuesto.estado_repus}</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="fw-bold text-success mb-3">
                            <i class="bi bi-boxes me-2"></i>Inventario
                        </h6>
                        <div class="mb-2">
                            <strong><i class="bi bi-123 me-1"></i>Cantidad:</strong> 
                            <span>${repuesto.cantidad}</span>
                        </div>
                        <div class="mb-2">
                            <strong><i class="bi bi-box-seam me-1"></i>Stock:</strong> 
                            <span class="badge ${repuesto.cant_stock > 0 ? 'bg-success' : 'bg-danger'}">${repuesto.cant_stock}</span>
                        </div>
                        <div class="mb-2">
                            <strong><i class="bi bi-geo-alt me-1"></i>Ubicación:</strong> 
                            <span>${repuesto.ubi_almacen || 'No especificada'}</span>
                        </div>
                        ${proveedorInfo}
                    </div>
                </div>
                
                ${repuesto.des_tecnica ? `
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-success mb-3">
                            <i class="bi bi-file-text me-2"></i>Descripción Técnica
                        </h6>
                        <p class="bg-light p-3 rounded">${repuesto.des_tecnica}</p>
                    </div>
                </div>
                ` : ''}
                
                ${repuesto.pre_unitario > 0 ? `
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-success mb-3">
                            <i class="bi bi-currency-dollar me-2"></i>Información Financiera
                        </h6>
                        <div class="row">
                            <div class="col-md-4">
                                <strong><i class="bi bi-tag me-1"></i>Precio Unitario:</strong>
                                <span class="text-success fw-bold">$${parseFloat(repuesto.pre_unitario).toFixed(2)}</span>
                            </div>
                            ${repuesto.costo_total > 0 ? `
                            <div class="col-md-4">
                                <strong><i class="bi bi-calculator me-1"></i>Costo Total:</strong>
                                <span class="text-info fw-bold">$${parseFloat(repuesto.costo_total).toFixed(2)}</span>
                            </div>
                            ` : ''}
                            ${repuesto.num_factura ? `
                            <div class="col-md-4">
                                <strong><i class="bi bi-receipt me-1"></i>N° Factura:</strong>
                                <span>${repuesto.num_factura}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
                ` : ''}
                
                ${repuesto.fecha_ingreso || repuesto.garantia || repuesto.res_ingreso ? `
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-success mb-3">
                            <i class="bi bi-calendar-event me-2"></i>Información Adicional
                        </h6>
                        <div class="row">
                            ${repuesto.fecha_ingreso ? `
                            <div class="col-md-4">
                                <strong><i class="bi bi-calendar-plus me-1"></i>Fecha Ingreso:</strong>
                                <span>${repuesto.fecha_ingreso}</span>
                            </div>
                            ` : ''}
                            ${repuesto.garantia ? `
                            <div class="col-md-4">
                                <strong><i class="bi bi-shield-check me-1"></i>Garantía:</strong>
                                <span>${repuesto.garantia}</span>
                            </div>
                            ` : ''}
                            ${repuesto.res_ingreso ? `
                            <div class="col-md-4">
                                <strong><i class="bi bi-person-check me-1"></i>Responsable:</strong>
                                <span>${repuesto.res_ingreso}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        
        abrirModal('modalVerRepuesto');
    }
    
    // Función para editar repuesto
    function editarRepuesto(id) {
        const formulario = document.getElementById('formularioEditar');
        formulario.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-warning" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2">Cargando datos del repuesto...</p>
            </div>
        `;
        
        abrirModal('modalEditarRepuesto');
        
        // Cargar datos del repuesto
        fetch(`repue.php?ajax=1&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cargarFormularioEdicion(data.repuesto);
                } else {
                    formulario.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Error al cargar los datos del repuesto: ${data.message || 'Error desconocido'}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                formulario.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Error al cargar los datos del repuesto. Por favor, intenta nuevamente.
                    </div>
                `;
            });
    }
    
    // Función para cargar el formulario de edición
    function cargarFormularioEdicion(repuesto) {
        const formulario = document.getElementById('formularioEditar');
        formulario.innerHTML = `
            <form id="formEditar" method="POST" action="repue.php">
                <input type="hidden" name="id" value="${repuesto.id}">
                <input type="hidden" name="editar" value="1">
                <!-- Información Básica -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-warning mb-3">
                            <i class="bi bi-info-circle me-2"></i>Información Básica
                        </h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-collection me-1"></i>Categoría</label>
                        <select name="cat_repu_id" class="form-select" required>
                            <option value="">Seleccione una categoría</option>
                            ${window.categoriasData.map(cat => `<option value="${cat.id}" ${repuesto.cat_repu_id == cat.id ? 'selected' : ''}>${cat.nombre}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-bookmark me-1"></i>Subcategoría</label>
                        <select name="subcat_repu_id" class="form-select" required>
                            <option value="">Seleccione una subcategoría</option>
                            ${window.subcategoriasData.map(subcat => `<option value="${subcat.id}" ${repuesto.subcat_repu_id == subcat.id ? 'selected' : ''}>${subcat.nombre}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-gear me-1"></i>Nombre *</label>
                        <input type="text" name="nombre" class="form-control" value="${repuesto.nombre || ''}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="bi bi-tag me-1"></i>Marca</label>
                        <input type="text" name="marca_repuesto" class="form-control" value="${repuesto.marca_repuesto || ''}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-diagram-3 me-1"></i>Modelo</label>
                        <input type="text" name="modelo" class="form-control" value="${repuesto.modelo || ''}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-hash me-1"></i>Número de Parte</label>
                        <input type="text" name="numero_parte" class="form-control" value="${repuesto.numero_parte || ''}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-check-circle me-1"></i>Estado</label>
                        <select name="estado_repus" class="form-select">
                            <option value="Nuevo" ${repuesto.estado_repus === 'Nuevo' ? 'selected' : ''}>Nuevo</option>
                            <option value="Usado" ${repuesto.estado_repus === 'Usado' ? 'selected' : ''}>Usado</option>
                            <option value="Reacondicionado" ${repuesto.estado_repus === 'Reacondicionado' ? 'selected' : ''}>Reacondicionado</option>
                            <option value="Dañado" ${repuesto.estado_repus === 'Dañado' ? 'selected' : ''}>Dañado</option>
                        </select>
                    </div>
                </div>
                
                <!-- Especificaciones -->
                <div class="row mb-4 mt-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-warning mb-3">
                            <i class="bi bi-wrench me-2"></i>Especificaciones
                        </h6>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="bi bi-rulers me-1"></i>Medidas Específicas</label>
                        <input type="text" name="medidas_espe" class="form-control" value="${repuesto.medidas_espe || ''}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="bi bi-award me-1"></i>Norma/Estándar</label>
                        <input type="text" name="norma_estan" class="form-control" value="${repuesto.norma_estan || ''}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label"><i class="bi bi-file-text me-1"></i>Descripción Técnica</label>
                        <textarea name="des_tecnica" class="form-control" rows="3">${repuesto.des_tecnica || ''}</textarea>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label"><i class="bi bi-truck me-1"></i>Vehículo Compatible</label>
                        <input type="text" name="veh_compatible" class="form-control" value="${repuesto.veh_compatible || ''}">
                    </div>
                </div>
                
                <!-- Inventario -->
                <div class="row mb-4 mt-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-warning mb-3">
                            <i class="bi bi-boxes me-2"></i>Inventario
                        </h6>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-123 me-1"></i>Cantidad</label>
                        <input type="number" name="cantidad" class="form-control" value="${repuesto.cantidad || 0}" min="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-box-seam me-1"></i>Stock</label>
                        <input type="number" name="cant_stock" class="form-control" value="${repuesto.cant_stock || 0}" min="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-building me-1"></i>Proveedor</label>
                        <select name="proveedor_id" class="form-select">
                            <option value="">Sin proveedor</option>
                            ${window.proveedoresData ? window.proveedoresData.map(prov => 
                                `<option value="${prov.id}" ${repuesto.proveedor_id == prov.id ? 'selected' : ''}>${prov.nom_proveedor}</option>`
                            ).join('') : ''}
                        </select>
                    </div>
                </div>
                
                <!-- Precios -->
                <div class="row mb-4 mt-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-warning mb-3">
                            <i class="bi bi-currency-dollar me-2"></i>Información Financiera
                        </h6>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-tag me-1"></i>Precio Unitario</label>
                        <input type="number" step="0.01" name="pre_unitario" class="form-control" value="${repuesto.pre_unitario || ''}" min="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-calculator me-1"></i>Costo Total</label>
                                               <input type="number" step="0.01" name="costo_total" class="form-control" value="${repuesto.costo_total || ''}" min="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-receipt me-1"></i>Número Factura</label>
                        <input type="text" name="num_factura" class="form-control" value="${repuesto.num_factura || ''}">
                    </div>
                </div>
                
                <!-- Información Adicional -->
                <div class="row mb-4 mt-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-warning mb-3">
                            <i class="bi bi-info-square me-2"></i>Información Adicional
                        </h6>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-calendar-plus me-1"></i>Fecha Ingreso</label>
                        <input type="date" name="fecha_ingreso" class="form-control" value="${repuesto.fecha_ingreso || ''}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-calendar-x me-1"></i>Fecha Vencimiento</label>
                        <input type="date" name="fecha_venci" class="form-control" value="${repuesto.fecha_venci || ''}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-geo-alt me-1"></i>Ubicación</label>
                        <input type="text" name="ubi_almacen" class="form-control" value="${repuesto.ubi_almacen || ''}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="bi bi-shield-check me-1"></i>Garantía</label>
                        <input type="text" name="garantia" class="form-control" value="${repuesto.garantia || ''}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="bi bi-person-check me-1"></i>Responsable</label>
                        <input type="text" name="res_ingreso" class="form-control" value="${repuesto.res_ingreso || ''}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="bi bi-geo me-1"></i>Área Destino</label>
                        <input type="text" name="dest_area" class="form-control" value="${repuesto.dest_area || ''}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="bi bi-pen me-1"></i>Firma Verificación</label>
                        <input type="text" name="firma_verificacion" class="form-control" value="${repuesto.firma_verificacion || ''}">
                    </div>
                </div>
            </form>
        `;
    }
    
    // Función para eliminar repuesto
    function eliminarRepuesto(id, nombre) {
        repuestoIdEliminar = id;
        document.getElementById('nombreRepuestoEliminar').textContent = nombre;
        abrirModal('modalEliminarRepuesto');
    }
    
    // Función para confirmar eliminación
    function confirmarEliminacion() {
        if (repuestoIdEliminar) {
            // Mostrar indicador de carga
            const confirmBtn = document.querySelector('#modalEliminarRepuesto .btn-danger');
            const originalText = confirmBtn.textContent;
            confirmBtn.textContent = 'Eliminando...';
            confirmBtn.disabled = true;
            
            // Realizar eliminación vía AJAX con manejo de errores FK
            fetch(`repue.php?delete=${repuestoIdEliminar}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(result => {
                // Cerrar modal
                cerrarModal('modalEliminarRepuesto');
                
                // Mostrar mensaje de éxito
                alert('Repuesto eliminado correctamente');
                
                // Recargar página
                window.location.reload();
            })
            .catch(error => {
                console.error('Error al eliminar:', error);
                
                // Restaurar botón
                confirmBtn.textContent = originalText;
                confirmBtn.disabled = false;
                
                // Mostrar mensaje de error
                alert('Error al eliminar el repuesto. Es posible que esté siendo usado en salidas de repuestos.');
            });
        }
    }
    
    // Función para guardar edición
    function guardarEdicion() {
        const form = document.getElementById('formEditar');
        if (form) {
            // Validar campos requeridos
            const nombre = form.querySelector('input[name="nombre"]');
            if (!nombre || !nombre.value.trim()) {
                alert('El nombre del repuesto es requerido');
                if (nombre) nombre.focus();
                return;
            }
            
            // Enviar formulario
            form.submit();
        } else {
            alert('Error: No se pudo encontrar el formulario de edición');
        }
    }
</script>
</body>
<script>
// Filtrado y validación en edición
document.addEventListener('DOMContentLoaded', function() {
    const editCatSelect = document.getElementById('edit_cat_repu_id');
    const editSubcatSelect = document.getElementById('edit_subcat_repu_id');
    const editSubcatHelp = document.getElementById('edit_subcatHelp');
    if (editCatSelect && editSubcatSelect) {
        const allEditSubcatOptions = Array.from(editSubcatSelect.querySelectorAll('option[data-cat]'));
        function filtrarEditSubcategorias(catId) {
            if (!catId) {
                editSubcatSelect.innerHTML = '<option value="">Primero selecciona una categoría de repuesto</option>';
                editSubcatSelect.disabled = true;
                editSubcatHelp.textContent = 'Primero selecciona una categoría de repuesto.';
                return;
            }
            editSubcatSelect.innerHTML = '<option value="">Seleccione una subcategoría de repuesto</option>';
            let found = false;
            allEditSubcatOptions.forEach(opt => {
                if (opt.getAttribute('data-cat') == catId) {
                    editSubcatSelect.appendChild(opt.cloneNode(true));
                    found = true;
                }
            });
            editSubcatSelect.disabled = !found;
            editSubcatHelp.textContent = found ? '' : 'No hay subcategorías para esta categoría.';
        }
        // Inicializar según valor actual
        filtrarEditSubcategorias(editCatSelect.value);
        // Si la subcategoría actual no corresponde, limpiar y mostrar mensaje
        if (editSubcatSelect.value && editSubcatSelect.querySelector('option[value="'+editSubcatSelect.value+'"]') === null) {
            editSubcatSelect.value = '';
            editSubcatHelp.textContent = 'La subcategoría seleccionada no pertenece a la nueva categoría. Por favor, elige una subcategoría válida.';
        }
        editCatSelect.addEventListener('change', function() {
            filtrarEditSubcategorias(this.value);
            editSubcatSelect.value = '';
        });
        // Validación al enviar
        const editForm = editCatSelect.closest('form');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                const catId = editCatSelect.value;
                const subcatId = editSubcatSelect.value;
                let valid = true;
                if (!catId) {
                    editCatSelect.classList.add('is-invalid');
                    valid = false;
                } else {
                    editCatSelect.classList.remove('is-invalid');
                }
                if (!subcatId || !Array.from(editSubcatSelect.options).some(opt => opt.value === subcatId)) {
                    editSubcatSelect.classList.add('is-invalid');
                    editSubcatHelp.textContent = 'Selecciona una subcategoría válida.';
                    valid = false;
                } else {
                    editSubcatSelect.classList.remove('is-invalid');
                }
                if (!valid) e.preventDefault();
            });
        }
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filterable search for category
    const categoriaSearchInput = document.getElementById('categoria_search');
    const categoriaDropdown = document.getElementById('categoria_dropdown');
    const catRepuIdInput = document.getElementById('cat_repu_id');
    const subcatSelect = document.getElementById('subcat_repu_id');
    const subcatHelp = document.getElementById('subcatHelp');

    if (categoriaSearchInput && categoriaDropdown) {
        const allCategoryOptions = Array.from(categoriaDropdown.options);

        // Function to show all or filtered categories
        function mostrarCategorias(searchTerm = '') {
            const filteredOptions = allCategoryOptions.filter(option => {
                if (!searchTerm) return true; // Show all if no search term
                const nombre = option.getAttribute('data-nombre');
                return nombre && nombre.toLowerCase().includes(searchTerm.toLowerCase());
            });

            categoriaDropdown.innerHTML = '';
            filteredOptions.forEach(option => {
                categoriaDropdown.appendChild(option.cloneNode(true));
            });

            if (filteredOptions.length > 0) {
                categoriaDropdown.style.display = 'block';
            } else {
                categoriaDropdown.style.display = 'none';
            }
        }

        // Filter categories on input
        categoriaSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            mostrarCategorias(searchTerm);
        });

        // Show all categories on focus
        categoriaSearchInput.addEventListener('focus', function() {
            mostrarCategorias(this.value);
        });

        // Select category on click
        categoriaDropdown.addEventListener('click', function(e) {
            if (e.target.tagName === 'OPTION' && e.target.value) {
                const selectedValue = e.target.value;
                const selectedText = e.target.getAttribute('data-nombre');
                
                categoriaSearchInput.value = selectedText;
                catRepuIdInput.value = selectedValue;
                categoriaDropdown.style.display = 'none';
                
                // Trigger subcategory filtering and enable subcategory select
                filtrarSubcategorias(selectedValue);
            }
        });

        // Clear subcategory when category search is cleared
        categoriaSearchInput.addEventListener('input', function() {
            if (this.value === '') {
                catRepuIdInput.value = '';
                subcatSelect.value = '';
                subcatSelect.innerHTML = '<option value="">Primero selecciona una categoría de repuesto</option>';
                subcatSelect.disabled = true;
                subcatHelp.textContent = 'Primero selecciona una categoría de repuesto.';
            }
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!categoriaSearchInput.contains(e.target) && !categoriaDropdown.contains(e.target)) {
                categoriaDropdown.style.display = 'none';
            }
        });
    }

    // Guardar todas las opciones de subcategoría
    const allSubcatOptions = Array.from(subcatSelect.querySelectorAll('option[data-cat]'));

    function filtrarSubcategorias(catId) {
        // Limpiar y desactivar si no hay categoría
        if (!catId) {
            subcatSelect.innerHTML = '<option value="">Primero selecciona una categoría de repuesto</option>';
            subcatSelect.disabled = true;
            subcatHelp.textContent = 'Primero selecciona una categoría de repuesto.';
            return;
        }
        // Filtrar opciones válidas
        subcatSelect.innerHTML = '<option value="">Seleccione una subcategoría de repuesto</option>';
        let found = false;
        allSubcatOptions.forEach(opt => {
            if (opt.getAttribute('data-cat') == catId) {
                subcatSelect.appendChild(opt.cloneNode(true));
                found = true;
            }
        });
        subcatSelect.disabled = !found;
        subcatHelp.textContent = found ? '' : 'No hay subcategorías para esta categoría.';
    }

    // Validación al enviar el formulario
    const form = catRepuIdInput ? catRepuIdInput.closest('form') : null;
    if (form) {
        form.addEventListener('submit', function(e) {
            const catId = catRepuIdInput.value;
            const subcatId = subcatSelect.value;
            let valid = true;
            if (!catId) {
                categoriaSearchInput.classList.add('is-invalid');
                valid = false;
            } else {
                categoriaSearchInput.classList.remove('is-invalid');
            }
            if (!subcatId || !Array.from(subcatSelect.options).some(opt => opt.value === subcatId)) {
                subcatSelect.classList.add('is-invalid');
                subcatHelp.textContent = 'Selecciona una subcategoría válida.';
                valid = false;
            } else {
                subcatSelect.classList.remove('is-invalid');
            }
            if (!valid) e.preventDefault();
        });
    }
});
</script>
</html>
