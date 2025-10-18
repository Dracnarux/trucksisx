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
        $controller->delete($_GET['delete']);
        header('Location: repue.php');
        exit;
    }
}
// Filtros
$filtros = [];
foreach ([
    'nombre', 'marca_repuesto', 'proveedor', 'cat_repu_id', 'subcat_repu_id', 'modelo', 'medidas_espe', 'norma_estan', 'numero_parte', 'des_tecnica', 'veh_compatible', 'estado_repus', 'num_factura', 'ubi_almacen', 'dest_area', 'firma_verificacion'
] as $campo) {
    $filtros[$campo] = $_GET[$campo] ?? '';
}
$repuestos = $controller->index($filtros);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Repuestos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #f8fafc 0%, #e3e6ed 100%);
        }
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        
        .header-section {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: white !important;
            padding: 2rem;
            border-radius: 20px 20px 0 0;
        }
        
        .content-section {
            padding: 2rem;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            border-radius: 15px 15px 0 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #64748b;
            box-shadow: 0 0 0 0.2rem rgba(100, 116, 139, 0.25);
        }
        
        .btn {
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .table thead th {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: white;
            border: none;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .table-responsive {
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .table td {
            vertical-align: middle;
            max-width: 200px;
            word-wrap: break-word;
        }
        
        .table td:first-child {
            min-width: 60px;
        }
        
        .table td:last-child {
            min-width: 120px;
        }
        
        @media (max-width: 768px) {
            .table td {
                font-size: 0.85rem;
                padding: 0.5rem;
            }
            
            .btn-group-sm .btn {
                padding: 0.25rem 0.5rem;
            }
        }
        .form-label, .form-select, .form-control {
            color: #111 !important;
        }
        .btn-primary, .btn-outline-primary {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #fff !important;
        }
        .btn-primary:hover, .btn-outline-primary:hover {
            background-color: #0b5ed7 !important;
            border-color: #0b5ed7 !important;
        }
        .btn-secondary {
            background-color: rgba(13,110,253,0.15) !important;
            color: #111 !important;
            border: 1px solid rgba(13,110,253,0.15) !important;
        }
        .btn-success {
            background-color: #64748b !important;
            border-color: #64748b !important;
        }
        .table-bordered, .table th, .table td {
            color: #111 !important;
        }
        thead tr {
            background: rgba(13,110,253,0.10) !important;
            color: #111 !important;
            border-bottom: 2px solid rgba(13,110,253,0.15);
        }

        /* Mejoras para móviles */
        @media (max-width: 768px) {
            .main-container {
                margin-top: 1rem;
                margin-bottom: 1rem;
            }
            
            .header-section {
                padding: 1.5rem;
            }
            
            .header-section .d-flex {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .content-section {
                padding: 1rem;
            }
            
            /* Filtros responsivos - Stack en móvil */
            .row.mb-4 form .col-md-2 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 0.75rem;
            }
            
            /* Tabla responsiva mejorada */
            .table-responsive {
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                margin-top: 1rem;
            }
            
            .table {
                font-size: 0.8rem;
                margin-bottom: 0;
            }
            
            .table th,
            .table td {
                padding: 0.5rem 0.25rem;
                vertical-align: middle;
            }
            
            /* Ocultar columnas menos importantes en tablet */
            .table th:nth-child(1),
            .table td:nth-child(1),
            .table th:nth-child(5),
            .table td:nth-child(5),
            .table th:nth-child(9),
            .table td:nth-child(9) {
                display: none;
            }
            
            /* Botones de acción más compactos */
            .btn-group-sm .btn {
                padding: 0.25rem 0.4rem;
                font-size: 0.75rem;
            }
            
            /* Modales responsivos */
            .modal-dialog.modal-xl {
                max-width: 95%;
                margin: 0.5rem auto;
            }
            
            .modal-body {
                padding: 1rem;
            }
            
            .modal-body .row .col-md-6,
            .modal-body .row .col-md-4,
            .modal-body .row .col-md-3 {
                margin-bottom: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .header-section h2 {
                font-size: 1.25rem;
            }
            
            .header-section p {
                font-size: 0.875rem;
            }
            
            /* En móviles pequeños, convertir tabla a tarjetas */
            .table-responsive {
                overflow-x: visible;
            }
            
            .table thead {
                display: none;
            }
            
            .table tbody tr {
                display: block;
                border: 1px solid #dee2e6;
                border-radius: 12px;
                margin-bottom: 1rem;
                padding: 1rem;
                background: white;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                position: relative;
            }
            
            .table tbody tr:hover {
                background: #f8f9fa;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transition: all 0.3s ease;
            }
            
            .table tbody td {
                display: block;
                text-align: left !important;
                border: none;
                padding: 0.5rem 0;
                white-space: normal;
                max-width: none;
            }
            
            .table tbody td:before {
                content: attr(data-label) ": ";
                font-weight: bold;
                color: #64748b;
                display: inline-block;
                min-width: 120px;
            }
            
            .table tbody td:nth-child(1):before { content: "ID: "; }
            .table tbody td:nth-child(2):before { content: "Nombre: "; }
            .table tbody td:nth-child(3):before { content: "Categoría: "; }
            .table tbody td:nth-child(4):before { content: "Marca/Modelo: "; }
            .table tbody td:nth-child(5):before { content: "Proveedor: "; }
            .table tbody td:nth-child(6):before { content: "Stock: "; }
            .table tbody td:nth-child(7):before { content: "Estado: "; }
            .table tbody td:nth-child(8):before { content: "Precio: "; }
            .table tbody td:nth-child(9):before { content: "Ubicación: "; }
            .table tbody td:nth-child(10):before { content: "Acciones: "; }
            
            /* Restaurar columnas ocultas en vista de tarjeta */
            .table tbody td {
                display: block !important;
            }
            
            /* Estilo especial para badges en tarjetas */
            .table tbody td .badge {
                margin-top: 0.25rem;
            }
            
            /* Botones de acción centrados en tarjetas */
            .table tbody td:last-child {
                text-align: center !important;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid #dee2e6;
            }
            
            .table tbody td:last-child:before {
                display: none;
            }
            
            /* Botón crear repuesto responsive */
            .btn-lg {
                width: 100%;
                margin-bottom: 1rem;
            }
            
            /* Filtros en acordeón para móvil */
            .filtros-mobile {
                background: #f8f9fa;
                border-radius: 10px;
                padding: 1rem;
                margin-bottom: 1rem;
            }
            
            .filtros-mobile .form-control,
            .filtros-mobile .form-select {
                margin-bottom: 0.75rem;
                font-size: 0.9rem;
            }
            
            .filtros-mobile .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
        
        @media (max-width: 320px) {
            .content-section {
                padding: 0.5rem;
            }
            
            .table tbody tr {
                padding: 0.75rem;
            }
            
            .table tbody td {
                padding: 0.25rem 0;
                font-size: 0.85rem;
            }
            
            .table tbody td:before {
                min-width: 100px;
                font-size: 0.8rem;
            }
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
    <form class="row mb-4" method="get">
        <!-- Filtros Desktop -->
        <div class="d-none d-md-block col-12">
            <div class="row">
                <div class="col-md-2 mb-2">
                    <input type="text" name="nombre" class="form-control" placeholder="Buscar por nombre" value="<?= htmlspecialchars($filtros['nombre']) ?>">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="text" name="modelo" class="form-control" placeholder="Buscar por modelo" value="<?= htmlspecialchars($filtros['modelo']) ?>">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="text" name="estado_repus" class="form-control" placeholder="Buscar por estado" value="<?= htmlspecialchars($filtros['estado_repus']) ?>">
                </div>
                <div class="col-md-2 mb-2">
                    <select name="cat_repu_id" class="form-control" aria-label="Filtrar por categoría">
                        <option value="">Filtrar por categoría</option>
                        <?php $catModel2 = new CatRepu(); $categorias2 = $catModel2->getAll(); while ($cat = $categorias2->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($_GET['cat_repu_id']) && $_GET['cat_repu_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="subcat_repu_id" class="form-control" aria-label="Filtrar por subcategoría">
                        <option value="">Filtrar por subcategoría</option>
                        <?php $subcatModel2 = new SubCatRepu(); $subcategorias2 = $subcatModel2->getAll(); while ($subcat = $subcategorias2->fetch_assoc()): ?>
                            <option value="<?= $subcat['id'] ?>" <?= (isset($_GET['subcat_repu_id']) && $_GET['subcat_repu_id'] == $subcat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($subcat['nombre']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="proveedor_id" class="form-control" aria-label="Filtrar por proveedor">
                        <option value="">Filtrar por proveedor</option>
                        <?php $proveedores->data_seek(0); while ($prov = $proveedores->fetch_assoc()): ?>
                            <option value="<?= $prov['id'] ?>" <?= (isset($_GET['proveedor_id']) && $_GET['proveedor_id'] == $prov['id']) ? 'selected' : '' ?>><?= htmlspecialchars($prov['nom_proveedor']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
                <div class="col-md-2 mb-2">
                    <a href="repue.php" class="btn btn-secondary w-100">
                        <i class="bi bi-x"></i> Limpiar
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Filtros Mobile -->
        <div class="d-md-none col-12">
            <div class="filtros-mobile">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h6>
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosMobile" aria-expanded="false">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
                
                <div class="collapse" id="filtrosMobile">
                    <input type="text" name="nombre" class="form-control" placeholder="🔍 Buscar por nombre" value="<?= htmlspecialchars($filtros['nombre']) ?>">
                    
                    <input type="text" name="modelo" class="form-control" placeholder="🔍 Buscar por modelo" value="<?= htmlspecialchars($filtros['modelo']) ?>">
                    
                    <input type="text" name="estado_repus" class="form-control" placeholder="🔍 Buscar por estado" value="<?= htmlspecialchars($filtros['estado_repus']) ?>">
                    
                    <select name="cat_repu_id" class="form-select" aria-label="Filtrar por categoría">
                        <option value="">📁 Todas las categorías</option>
                        <?php $catModel3 = new CatRepu(); $categorias3 = $catModel3->getAll(); while ($cat = $categorias3->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($_GET['cat_repu_id']) && $_GET['cat_repu_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    
                    <select name="subcat_repu_id" class="form-select" aria-label="Filtrar por subcategoría">
                        <option value="">📂 Todas las subcategorías</option>
                        <?php $subcatModel3 = new SubCatRepu(); $subcategorias3 = $subcatModel3->getAll(); while ($subcat = $subcategorias3->fetch_assoc()): ?>
                            <option value="<?= $subcat['id'] ?>" <?= (isset($_GET['subcat_repu_id']) && $_GET['subcat_repu_id'] == $subcat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($subcat['nombre']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    
                    <select name="proveedor_id" class="form-select" aria-label="Filtrar por proveedor">
                        <option value="">🏢 Todos los proveedores</option>
                        <?php $provModel2 = new Proveedor(); $proveedores2 = $provModel2->getAll(); while ($prov = $proveedores2->fetch_assoc()): ?>
                            <option value="<?= $prov['id'] ?>" <?= (isset($_GET['proveedor_id']) && $_GET['proveedor_id'] == $prov['id']) ? 'selected' : '' ?>><?= htmlspecialchars($prov['nom_proveedor']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    
                    <div class="row">
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                        <div class="col-6">
                            <a href="repue.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </form>
    
    <!-- Tabla de Repuestos -->
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
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

<!-- Modal Crear Repuesto -->
<div class="modal fade" id="modalCrearRepuesto" tabindex="-1" aria-labelledby="modalCrearRepuestoLabel" aria-hidden="true">
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
                            <label class="form-label"><i class="bi bi-collection me-1"></i>Categoría</label>
                            <select name="cat_repu_id" class="form-select">
                                <option value="">Seleccione una categoría</option>
                                <?php 
                                $categorias = $catModel->getAll();
                                while ($cat = $categorias->fetch_assoc()): 
                                ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-bookmark me-1"></i>Subcategoría</label>
                            <select name="subcat_repu_id" class="form-select">
                                <option value="">Seleccione una subcategoría</option>
                                <?php 
                                $subcategorias = $subcatModel->getAll();
                                while ($subcat = $subcategorias->fetch_assoc()): 
                                ?>
                                    <option value="<?= $subcat['id'] ?>"><?= htmlspecialchars($subcat['nombre']) ?></option>
                                <?php endwhile; ?>
                            </select>
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
<div class="modal fade" id="modalVerRepuesto" tabindex="-1" aria-labelledby="modalVerRepuestoLabel" aria-hidden="true">
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
<div class="modal fade" id="modalEditarRepuesto" tabindex="-1" aria-labelledby="modalEditarRepuestoLabel" aria-hidden="true">
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
<div class="modal fade" id="modalEliminarRepuesto" tabindex="-1" aria-labelledby="modalEliminarRepuestoLabel" aria-hidden="true">
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
                        <label class="form-label">Categoría</label>
                        <select name="cat_repu_id" class="form-control" required>
                            <option value="">Seleccione una categoría</option>
                            <?php while ($cat = $categorias->fetch_assoc()): ?>
                                <option value="<?= $cat['id'] ?>" <?= (isset($editData['cat_repu_id']) && $editData['cat_repu_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Subcategoría</label>
                        <select name="subcat_repu_id" class="form-control" required>
                            <option value="">Seleccione una subcategoría</option>
                            <?php while ($subcat = $subcategorias->fetch_assoc()): ?>
                                <option value="<?= $subcat['id'] ?>" <?= (isset($editData['subcat_repu_id']) && $editData['subcat_repu_id'] == $subcat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($subcat['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
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
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();
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
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="bi bi-gear me-1"></i>Nombre *</label>
                        <input type="text" name="nombre" class="form-control" value="${repuesto.nombre || ''}" required>
                    </div>
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
                        <label class="form-label"><i class="bi bi-building me-1"></i>ID Proveedor</label>
                        <input type="number" name="proveedor_id" class="form-control" value="${repuesto.proveedor_id || ''}" min="0">
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
            // Redirigir a la eliminación
            window.location.href = `repue.php?delete=${repuestoIdEliminar}`;
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
</html>
