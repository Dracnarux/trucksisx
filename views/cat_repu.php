<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
require_once '../controllers/CatRepuController.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categoría de Repuestos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #f8fafc 0%, #e3e6ed 100%);
        }
        .container {
            background: rgba(100,116,139,0.10);
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10);
            padding: 32px 24px;
            margin-top: 32px;
            color: #111;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(100,116,139,0.15);
        }
        h2 {
            color: #64748b;
        }
        .form-label, .form-select, .form-control {
            color: #111 !important;
        }
        .btn-primary, .btn-outline-primary {
            background-color: #64748b !important;
            border-color: #64748b !important;
            color: #fff !important;
        }
        .btn-primary:hover, .btn-outline-primary:hover {
            background-color: #475569 !important;
            border-color: #475569 !important;
        }
        .btn-secondary {
            background-color: rgba(100,116,139,0.15) !important;
            color: #111 !important;
            border: 1px solid rgba(100,116,139,0.15) !important;
        }
        .btn-success {
            background-color: #198754 !important;
            border-color: #198754 !important;
        }
        .table-bordered, .table th, .table td {
            color: #111 !important;
        }
        thead tr {
            background: rgba(100,116,139,0.10) !important;
            color: #111 !important;
            border-bottom: 2px solid rgba(13,110,253,0.15);
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
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white !important;
            padding: 2rem;
            border-radius: 20px 20px 0 0;
        }
        .header-section h2 {
            color: white !important;
            font-weight: 700;
        }
        .header-section p {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        .header-section .btn-light {
            background: white !important;
            color: #0d6efd !important;
            border: none;
            font-weight: 600;
        }
        .header-section .btn-light:hover {
            background: rgba(255, 255, 255, 0.9) !important;
            color: #0b5ed7 !important;
        }
        .content-section {
            padding: 2rem;
        }
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .modal-header {
            border-radius: 20px 20px 0 0;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        /* Asegurar visibilidad del texto en header */
        .header-section * {
            color: inherit !important;
        }
        .header-section .bi {
            color: white !important;
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
                    <h2 class="mb-1"><i class="bi bi-tags-fill"></i> Gestión de Categorías de Repuestos</h2>
                    <p class="mb-0 opacity-75">Administra las categorías del sistema de repuestos</p>
                </div>
                <a href="gestiones.php" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Volver a Gestiones
                </a>
            </div>
        </div>
        
        <!-- Content Section -->
        <div class="content-section">
            <!-- Filtros y Búsqueda -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <form method="get" action="">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="filtro_nombre" class="form-control" 
                                       placeholder="Buscar por nombre..." 
                                       value="<?= isset($_GET['filtro_nombre']) ? htmlspecialchars($_GET['filtro_nombre']) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="filtro_caracteristicas" class="form-control" 
                                       placeholder="Buscar por características..." 
                                       value="<?= isset($_GET['filtro_caracteristicas']) ? htmlspecialchars($_GET['filtro_caracteristicas']) : '' ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                            <div class="col-md-2">
                                <?php if (isset($_GET['filtro_nombre']) || isset($_GET['filtro_caracteristicas'])): ?>
                                <a href="cat_repu.php" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-x"></i> Limpiar
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 text-end">
                    <?php if (!$rol_conductor): ?>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearCategoria">
                        <i class="bi bi-plus-circle"></i> Agregar Categoría
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Tabla de Categorías -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th><i class="bi bi-hash"></i> ID</th>
                            <th><i class="bi bi-tag"></i> Tipo de Repuesto</th>
                            <th><i class="bi bi-bookmarks"></i> Nombre</th>
                            <th><i class="bi bi-list-ul"></i> Características</th>
                            <th><i class="bi bi-box"></i> Repuestos</th>
                            <th class="text-center"><i class="bi bi-gear"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    require_once '../models/Repue.php';
                    $repueModel = new Repue();
                    
                    // Aplicar filtros
                    $categorias_filtradas = [];
                    $categorias->data_seek(0);
                    while ($row = $categorias->fetch_assoc()) {
                        $incluir = true;
                        
                        if (!empty($_GET['filtro_nombre'])) {
                            $filtro_nombre = strtolower($_GET['filtro_nombre']);
                            if (strpos(strtolower($row['nombre']), $filtro_nombre) === false) {
                                $incluir = false;
                            }
                        }
                        
                        if (!empty($_GET['filtro_caracteristicas'])) {
                            $filtro_carac = strtolower($_GET['filtro_caracteristicas']);
                            if (strpos(strtolower($row['caracteristicas'] ?? ''), $filtro_carac) === false) {
                                $incluir = false;
                            }
                        }
                        
                        if ($incluir) {
                            $categorias_filtradas[] = $row;
                        }
                    }
                    
                    if (empty($categorias_filtradas)):
                    ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                                <p class="mt-2 text-muted">No se encontraron categorías</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categorias_filtradas as $row): 
                            // Contar repuestos asociados
                            $repuestos = $repueModel->getAll(['cat_repu_id' => $row['id']]);
                            $count_repuestos = 0;
                            while ($rep = $repuestos->fetch_assoc()) {
                                $count_repuestos++;
                            }
                        ?>
                            <tr>
                                <td><strong>#<?= $row['id'] ?></strong></td>
                                <td>
                                    <?php if (!empty($row['tipo_repuesto'])): ?>
                                        <span class="badge bg-primary"><?= htmlspecialchars($row['tipo_repuesto']) ?></span>
                                    <?php else: ?>
                                        <small class="text-muted">Sin especificar</small>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($row['nombre']) ?></strong></td>
                                <td>
                                    <?php if (!empty($row['caracteristicas'])): ?>
                                        <?= htmlspecialchars(substr($row['caracteristicas'], 0, 50)) ?>
                                        <?php if (strlen($row['caracteristicas']) > 50): ?>...<?php endif; ?>
                                    <?php else: ?>
                                        <small class="text-muted">Sin características</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= $count_repuestos ?> repuesto(s)</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" 
                                                class="btn btn-outline-info" 
                                                onclick="verCategoria(<?= htmlspecialchars(json_encode($row)) ?>)"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalVerCategoria"
                                                title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if (!$rol_conductor): ?>
                                        <button type="button" 
                                                class="btn btn-outline-warning" 
                                                onclick="editarCategoria(<?= htmlspecialchars(json_encode($row)) ?>)"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEditarCategoria"
                                                title="Editar categoría">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                onclick="eliminarCategoria(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nombre']) ?>', <?= $count_repuestos ?>)"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEliminarCategoria"
                                                title="Eliminar categoría">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Modal para crear categoría -->
<div class="modal fade" id="modalCrearCategoria" tabindex="-1" aria-labelledby="modalCrearCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCrearCategoriaLabel">
                    <i class="bi bi-plus-circle"></i> Crear Nueva Categoría
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="" id="formCrearCategoria">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_repuesto_create" class="form-label">
                                <i class="bi bi-tag"></i> Tipo de Repuesto
                            </label>
                            <select class="form-select" id="tipo_repuesto_create" name="tipo_repuesto">
                                <option value="">Seleccionar tipo...</option>
                                <option value="Motor">Motor</option>
                                <option value="Transmisión">Transmisión</option>
                                <option value="Frenos">Frenos</option>
                                <option value="Suspensión">Suspensión</option>
                                <option value="Eléctrico">Eléctrico</option>
                                <option value="Carrocería">Carrocería</option>
                                <option value="Neumáticos">Neumáticos</option>
                                <option value="Filtros">Filtros</option>
                                <option value="Lubricantes">Lubricantes</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nombre_create" class="form-label">
                                <i class="bi bi-bookmarks"></i> Nombre de la Categoría *
                            </label>
                            <input type="text" class="form-control" id="nombre_create" name="nombre" 
                                   placeholder="Ej: Filtros de Aceite" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="caracteristicas_create" class="form-label">
                            <i class="bi bi-list-ul"></i> Características
                        </label>
                        <textarea class="form-control" id="caracteristicas_create" name="caracteristicas" 
                                  rows="4" placeholder="Describe las características principales de esta categoría..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Información:</strong> Esta categoría estará disponible para clasificar repuestos en el sistema.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="submit" form="formCrearCategoria" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Crear Categoría
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalles de categoría -->
<div class="modal fade" id="modalVerCategoria" tabindex="-1" aria-labelledby="modalVerCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalVerCategoriaLabel">
                    <i class="bi bi-eye"></i> Detalles de la Categoría
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <i class="bi bi-info-circle"></i> Información General
                            </div>
                            <div class="card-body">
                                <p><strong>ID:</strong> <span id="ver_id"></span></p>
                                <p><strong>Nombre:</strong> <span id="ver_nombre"></span></p>
                                <p><strong>Tipo de Repuesto:</strong> <span id="ver_tipo"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <i class="bi bi-list-ul"></i> Características
                            </div>
                            <div class="card-body">
                                <p id="ver_caracteristicas"></p>
                            </div>
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

<!-- Modal para editar categoría -->
<div class="modal fade" id="modalEditarCategoria" tabindex="-1" aria-labelledby="modalEditarCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditarCategoriaLabel">
                    <i class="bi bi-pencil-square"></i> Editar Categoría
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="" id="formEditarCategoria">
                    <input type="hidden" name="id" id="editar_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editar_tipo_repuesto" class="form-label">
                                <i class="bi bi-tag"></i> Tipo de Repuesto
                            </label>
                            <select class="form-select" id="editar_tipo_repuesto" name="tipo_repuesto">
                                <option value="">Seleccionar tipo...</option>
                                <option value="Motor">Motor</option>
                                <option value="Transmisión">Transmisión</option>
                                <option value="Frenos">Frenos</option>
                                <option value="Suspensión">Suspensión</option>
                                <option value="Eléctrico">Eléctrico</option>
                                <option value="Carrocería">Carrocería</option>
                                <option value="Neumáticos">Neumáticos</option>
                                <option value="Filtros">Filtros</option>
                                <option value="Lubricantes">Lubricantes</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editar_nombre" class="form-label">
                                <i class="bi bi-bookmarks"></i> Nombre de la Categoría *
                            </label>
                            <input type="text" class="form-control" id="editar_nombre" name="nombre" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editar_caracteristicas" class="form-label">
                            <i class="bi bi-list-ul"></i> Características
                        </label>
                        <textarea class="form-control" id="editar_caracteristicas" name="caracteristicas" rows="4"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="submit" form="formEditarCategoria" class="btn btn-warning">
                    <i class="bi bi-check-circle"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div class="modal fade" id="modalEliminarCategoria" tabindex="-1" aria-labelledby="modalEliminarCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminarCategoriaLabel">
                    <i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>¡Advertencia!</strong> Esta acción no se puede deshacer.
                </div>
                
                <p class="mb-3">¿Está seguro de que desea eliminar la categoría:</p>
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title mb-1" id="eliminar_nombre_categoria"></h6>
                        <small class="text-muted">ID: <span id="eliminar_id_categoria"></span></small>
                    </div>
                </div>
                
                <div class="mt-3" id="advertencia_repuestos" style="display: none;">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-circle"></i>
                        Esta categoría tiene <span id="count_repuestos"></span> repuesto(s) asociado(s).
                        Al eliminar la categoría, estos repuestos quedarán sin categoría.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <a href="#" id="btn_confirmar_eliminar_categoria" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Sí, Eliminar Categoría
                </a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Función para ver detalles de la categoría
function verCategoria(categoria) {
    document.getElementById('ver_id').textContent = '#' + categoria.id;
    document.getElementById('ver_nombre').textContent = categoria.nombre;
    document.getElementById('ver_tipo').innerHTML = categoria.tipo_repuesto 
        ? '<span class="badge bg-primary">' + categoria.tipo_repuesto + '</span>'
        : '<span class="text-muted">Sin especificar</span>';
    document.getElementById('ver_caracteristicas').textContent = categoria.caracteristicas || 'Sin características especificadas';
}

// Función para cargar datos en el modal de edición
function editarCategoria(categoria) {
    document.getElementById('editar_id').value = categoria.id;
    document.getElementById('editar_nombre').value = categoria.nombre;
    document.getElementById('editar_tipo_repuesto').value = categoria.tipo_repuesto || '';
    document.getElementById('editar_caracteristicas').value = categoria.caracteristicas || '';
}

// Función para configurar modal de eliminación
function eliminarCategoria(id, nombre, countRepuestos) {
    document.getElementById('eliminar_id_categoria').textContent = id;
    document.getElementById('eliminar_nombre_categoria').textContent = nombre;
    document.getElementById('btn_confirmar_eliminar_categoria').href = 'cat_repu.php?delete=' + id;
    
    // Mostrar advertencia si hay repuestos asociados
    if (countRepuestos > 0) {
        document.getElementById('count_repuestos').textContent = countRepuestos;
        document.getElementById('advertencia_repuestos').style.display = 'block';
    } else {
        document.getElementById('advertencia_repuestos').style.display = 'none';
    }
}

// Validaciones de formularios
document.getElementById('formCrearCategoria').addEventListener('submit', function(e) {
    const nombre = document.getElementById('nombre_create').value.trim();
    if (!nombre) {
        e.preventDefault();
        alert('El nombre de la categoría es obligatorio');
        return false;
    }
});

document.getElementById('formEditarCategoria').addEventListener('submit', function(e) {
    const nombre = document.getElementById('editar_nombre').value.trim();
    if (!nombre) {
        e.preventDefault();
        alert('El nombre de la categoría es obligatorio');
        return false;
    }
});

// Limpiar formularios al cerrar modales
document.getElementById('modalCrearCategoria').addEventListener('hidden.bs.modal', function () {
    document.getElementById('formCrearCategoria').reset();
});

document.getElementById('modalEditarCategoria').addEventListener('hidden.bs.modal', function () {
    document.getElementById('formEditarCategoria').reset();
});

// Mostrar mensajes de éxito/error si existen en la URL
window.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const mensaje = urlParams.get('mensaje');
    const tipo = urlParams.get('tipo');
    
    if (mensaje && tipo) {
        const alertClass = tipo === 'exito' ? 'alert-success' : 'alert-danger';
        const iconClass = tipo === 'exito' ? 'bi-check-circle' : 'bi-exclamation-triangle';
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="bi ${iconClass}"></i>
            <strong>${tipo === 'exito' ? 'Éxito:' : 'Error:'}</strong> ${decodeURIComponent(mensaje)}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        const container = document.querySelector('.content-section');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Remover parámetros de la URL
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
});
</script>

</body>
</html>
