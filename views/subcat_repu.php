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
            background: linear-gradient(135deg, #64748b, #475569);
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
            background: linear-gradient(135deg, #64748b, #475569);
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
        }
        .btn-outline-primary {
            border-color: #64748b;
            color: #64748b;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
        }
        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #64748b, #475569);
            border-color: #64748b;
        }
        .modal-header {
            background: linear-gradient(135deg, #64748b, #475569);
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
                            <?php if (!$rol_conductor): ?>
                            <td class="text-center">
                                <button type="button" class="btn btn-info btn-sm me-1" onclick="viewSubcategory(<?= $row['id'] ?>)" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-warning btn-sm me-1" onclick="editSubcategory(<?= $row['id'] ?>)" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteSubcategory(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nombre'], ENT_QUOTES) ?>')" title="Eliminar">
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">
                    <i class="bi bi-plus-circle"></i> Agregar Nueva Subcategoría
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createForm" method="post">
                <div class="modal-body">
                    <div class="form-section">
                        <h6><i class="bi bi-info-circle"></i> Información Básica</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Sub-Repuesto</label>
                                <input type="text" name="tipo_sub_repuesto" class="form-control" placeholder="Ej: Filtros, Aceites...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" class="form-control" required placeholder="Nombre de la subcategoría">
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6><i class="bi bi-gear"></i> Detalles</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Características</label>
                                <input type="text" name="caracteristicas" class="form-control" placeholder="Características específicas">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Categoría <span class="text-danger">*</span></label>
                                <select name="categoria_id" class="form-select" required>
                                    <option value="">Seleccione una categoría...</option>
                                    <?php 
                                    $categorias_create = $catRepu->getAll();
                                    while ($cat = $categorias_create->fetch_assoc()): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Guardar Subcategoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">
                    <i class="bi bi-eye"></i> Detalles de Subcategoría
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-section">
                    <h6><i class="bi bi-info-circle"></i> Información General</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> <span id="view-id"></span></p>
                            <p><strong>Nombre:</strong> <span id="view-nombre"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tipo:</strong> <span id="view-tipo"></span></p>
                            <p><strong>Categoría:</strong> <span id="view-categoria"></span></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <p><strong>Características:</strong> <span id="view-caracteristicas"></span></p>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">
                    <i class="bi bi-pencil"></i> Editar Subcategoría
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" method="post">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="form-section">
                        <h6><i class="bi bi-info-circle"></i> Información Básica</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Sub-Repuesto</label>
                                <input type="text" name="tipo_sub_repuesto" id="edit-tipo" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="edit-nombre" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6><i class="bi bi-gear"></i> Detalles</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Características</label>
                                <input type="text" name="caracteristicas" id="edit-caracteristicas" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Categoría <span class="text-danger">*</span></label>
                                <select name="categoria_id" id="edit-categoria" class="form-select" required>
                                    <option value="">Seleccione una categoría...</option>
                                    <?php 
                                    $categorias_edit = $catRepu->getAll();
                                    while ($cat = $categorias_edit->fetch_assoc()): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle"></i> Actualizar Subcategoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Función para ver subcategoría
function viewSubcategory(id) {
    fetch(`subcat_repu.php?ajax=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('view-id').textContent = data.id || '';
            document.getElementById('view-nombre').textContent = data.nombre || '';
            document.getElementById('view-tipo').textContent = data.tipo_sub_repuesto || 'N/A';
            document.getElementById('view-categoria').textContent = data.categoria_nombre || '';
            document.getElementById('view-caracteristicas').textContent = data.caracteristicas || 'N/A';
            
            new bootstrap.Modal(document.getElementById('viewModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos');
        });
}

// Función para editar subcategoría
function editSubcategory(id) {
    fetch(`subcat_repu.php?ajax=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit-id').value = data.id || '';
            document.getElementById('edit-nombre').value = data.nombre || '';
            document.getElementById('edit-tipo').value = data.tipo_sub_repuesto || '';
            document.getElementById('edit-caracteristicas').value = data.caracteristicas || '';
            document.getElementById('edit-categoria').value = data.categoria_id || '';
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos');
        });
}

// Función para eliminar subcategoría
function deleteSubcategory(id, nombre) {
    if (confirm(`¿Está seguro de eliminar la subcategoría "${nombre}"?`)) {
        window.location.href = `subcat_repu.php?delete=${id}`;
    }
}

// Validación del formulario de crear
document.getElementById('createForm').addEventListener('submit', function(e) {
    const nombre = this.querySelector('[name="nombre"]').value.trim();
    const categoria = this.querySelector('[name="categoria_id"]').value;
    
    if (!nombre) {
        e.preventDefault();
        alert('El nombre es obligatorio');
        return false;
    }
    
    if (!categoria) {
        e.preventDefault();
        alert('Debe seleccionar una categoría');
        return false;
    }
});

// Validación del formulario de editar
document.getElementById('editForm').addEventListener('submit', function(e) {
    const nombre = this.querySelector('[name="nombre"]').value.trim();
    const categoria = this.querySelector('[name="categoria_id"]').value;
    
    if (!nombre) {
        e.preventDefault();
        alert('El nombre es obligatorio');
        return false;
    }
    
    if (!categoria) {
        e.preventDefault();
        alert('Debe seleccionar una categoría');
        return false;
    }
});
</script>

</body>
</html>