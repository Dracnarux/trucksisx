<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
require_once '../controllers/SubCatRepuController.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subcategorías de Repuestos - TruckSISX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .main-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
        }
        .btn-outline-primary {
            border-color: #007bff;
            color: #007bff;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
        }
        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #007bff, #0056b3);
            border-color: #007bff;
        }
        .modal-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .form-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .form-section h6 {
            color: #495057;
            margin-bottom: 0.8rem;
            font-weight: 600;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .table thead {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }
        .table-responsive {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid py-4">
    <div class="main-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">
                    <i class="bi bi-collection"></i> Subcategorías de Repuestos
                </h1>
                <p class="mb-0 opacity-75">Gestión de subcategorías para clasificación de repuestos</p>
            </div>
            <?php if (!$rol_conductor): ?>
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-plus-circle"></i> Agregar Subcategoría
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">
                <i class="bi bi-funnel"></i> Filtros de Búsqueda
            </h5>
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="filtro_nombre" class="form-control" value="<?= htmlspecialchars($filtro_nombre) ?>" placeholder="Buscar por nombre...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Características</label>
                    <input type="text" name="filtro_caracteristicas" class="form-control" value="<?= htmlspecialchars($filtro_caracteristicas) ?>" placeholder="Buscar por características...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Categoría</label>
                    <select name="filtro_categoria" class="form-select">
                        <option value="">Todas las categorías</option>
                        <?php 
                        $categorias_filtro = $catRepu->getAll();
                        while ($cat = $categorias_filtro->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>" <?= $filtro_categoria == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="subcat_repu.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Nombre</th>
                            <th>Características</th>
                            <th>Categoría</th>
                            <?php if (!$rol_conductor): ?>
                            <th class="text-center" width="180">Acciones</th>
                            <?php endif; ?>
        }
        thead tr {
            background: rgba(13,110,253,0.10) !important;
            color: #111 !important;
            border-bottom: 2px solid rgba(13,110,253,0.15);
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Subcategoría de Repuestos</h2>
    <form class="row mb-4" method="get">
        <div class="col-md-3">
            <input type="text" name="filtro_nombre" class="form-control" placeholder="Buscar por nombre" value="<?= htmlspecialchars($filtro_nombre) ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="filtro_caracteristicas" class="form-control" placeholder="Buscar por características" value="<?= htmlspecialchars($filtro_caracteristicas) ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="filtro_categoria" class="form-control" placeholder="Buscar por categoría" value="<?= htmlspecialchars($filtro_categoria) ?>">
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
        <div class="col-md-1">
            <a href="subcat_repu.php" class="btn btn-secondary w-100">Limpiar</a>
        </div>
        <div class="col-md-1 text-end">
            <?php if (!$rol_conductor): ?>
            <a href="subcat_repu.php?form=1" class="btn btn-success w-100">Agregar</a>
            <?php endif; ?>
        </div>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tipo de Sub-Repuesto</th>
                <th>Nombre</th>
                <th>Características</th>
                <th>Categoría</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $subcategorias->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['tipo_sub_repuesto'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= htmlspecialchars($row['caracteristicas'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['categoria_nombre'] ?? '') ?></td>
                <td class="text-center">
                    <?php if (!$rol_conductor): ?>
                    <a href="subcat_repu.php?form=1&id=<?= $row['id'] ?>" class="btn btn-warning mx-1">Editar</a>
                    <a href="subcat_repu.php?delete=<?= $row['id'] ?>" class="btn btn-danger mx-1" onclick="return confirm('¿Eliminar subcategoría?')">Eliminar</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <?php if (isset($_GET['form']) && !$rol_conductor): ?>
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title"><?= isset($subcategoria) ? 'Editar' : 'Agregar' ?> Subcategoría</h5>
            <form method="post">
                <input type="hidden" name="id" value="<?= $subcategoria['id'] ?? '' ?>">
                <div class="mb-3">
                    <label class="form-label">Tipo de Sub-Repuesto</label>
                    <input type="text" name="tipo_sub_repuesto" class="form-control" value="<?= htmlspecialchars($subcategoria['tipo_sub_repuesto'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($subcategoria['nombre'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Características</label>
                    <input type="text" name="caracteristicas" class="form-control" value="<?= htmlspecialchars($subcategoria['caracteristicas'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Categoría</label>
                    <select name="categoria_id" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php while ($cat = $categorias->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($subcategoria['categoria_id']) && $subcategoria['categoria_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="subcat_repu.php" class="btn btn-secondary mx-2">Cancelar</a>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="mt-5 text-end">
        <a href="gestiones.php" class="btn btn-outline-secondary">Volver al Panel de Gestiones</a>
    </div>
</div>
</body>
</html>
