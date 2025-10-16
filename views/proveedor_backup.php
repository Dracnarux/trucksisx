<?php
require_once '../controllers/ProveedorController.php';
require_once '../models/Repue.php';
session_start();
$controller = new ProveedorController();
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
// Manejo de POST y delete antes de cualquier salida
if (!$rol_conductor) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->store($_POST);
        header('Location: proveedor.php');
        exit;
    }
    if (isset($_GET['delete'])) {
        $controller->delete($_GET['delete']);
        header('Location: proveedor.php');
        exit;
    }
}
// Filtros mejorados
$filtros = [];
foreach ([
    'nom_proveedor', 'tip_repuesto', 'mar_distribuye', 'ciudad_depar', 'pais',
    'correo', 'tel_contacto', 'nit_num_identi', 'tiem_entrega', 'for_pago',
    'tiene_credito', 'estado_repuestos'
] as $campo) {
    $filtros[$campo] = $_GET[$campo] ?? '';
}
$proveedores = $controller->index($filtros);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Proveedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #f8fafc 0%, #e3e6ed 100%);
        }
        .container {
            background: rgba(13,110,253,0.10);
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10);
            padding: 32px 24px;
            margin-top: 32px;
            color: #111;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(13,110,253,0.15);
        }
        h2 {
            color: #0d6efd;
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
            background-color: #198754 !important;
            border-color: #198754 !important;
        }
        .table-bordered, .table th, .table td {
            color: #111 !important;
        }
        thead tr {
            background: rgba(13,110,253,0.10) !important;
            color: #111 !important;
            border-bottom: 2px solid rgba(13,110,253,0.15);
        }
        
        /* Estilos mejorados para filtros */
        .card-header {
            background: linear-gradient(45deg, rgba(13,110,253,0.1), rgba(13,110,253,0.05)) !important;
            border-bottom: 1px solid rgba(13,110,253,0.15);
        }
        
        .form-label.fw-bold {
            color: #0d6efd !important;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.25);
        }
        
        .badge {
            font-size: 0.75rem;
        }
        
        .table td {
            vertical-align: middle;
            padding: 0.75rem 0.5rem;
        }
        
        .btn-group-sm > .btn, .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        /* Animación para los filtros */
        .collapse {
            transition: height 0.35s ease;
        }
        
        /* Tooltip personalizado */
        .tooltip-inner {
            background-color: #0d6efd;
        }
        
        /* Estilo para campos vacíos */
        .form-control:placeholder-shown {
            border-color: #dee2e6;
        }
        
        /* Hover effects */
        .btn:hover {
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }
        
        /* Responsive table scroll */
        @media (max-width: 992px) {
            .table-responsive {
                border-radius: 0.375rem;
                box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            }
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Gestión de Proveedores de Repuestos</h2>
    <?php 
    require_once '../models/CatRepu.php';
    require_once '../models/SubCatRepu.php';
    require_once '../models/Repue.php';
    $catModel = new CatRepu();
    $subcatModel = new SubCatRepu();
    $repueModel = new Repue();
    $categorias = $catModel->getAll();
    $subcategorias = $subcatModel->getAll();
    $repuestos = $repueModel->getAll();
    ?>
    <!-- Panel de Filtros Mejorado -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">🔍 Filtros de Búsqueda</h5>
            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosAvanzados" aria-expanded="false">
                <i class="bi bi-funnel"></i> Filtros Avanzados
            </button>
        </div>
        <div class="card-body">
            <form method="get" id="formFiltros">
                <!-- Fila 1: Filtros Principales -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">🏢 Nombre del Proveedor</label>
                        <input type="text" name="nom_proveedor" class="form-control" 
                               placeholder="Buscar por nombre..." 
                               value="<?= htmlspecialchars($filtros['nom_proveedor']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">🏷️ Marca que Distribuye</label>
                        <input type="text" name="mar_distribuye" class="form-control" 
                               placeholder="Ej: Toyota, Ford, etc..." 
                               value="<?= htmlspecialchars($filtros['mar_distribuye']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">🔧 Tipo de Repuesto</label>
                        <input type="text" name="tip_repuesto" class="form-control" 
                               placeholder="Motor, frenos, etc..." 
                               value="<?= htmlspecialchars($filtros['tip_repuesto']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">🌍 País</label>
                        <select name="pais" class="form-select">
                            <option value="">Todos los países</option>
                            <option value="Colombia" <?= $filtros['pais'] === 'Colombia' ? 'selected' : '' ?>>🇨🇴 Colombia</option>
                            <option value="México" <?= $filtros['pais'] === 'México' ? 'selected' : '' ?>>🇲🇽 México</option>
                            <option value="Brasil" <?= $filtros['pais'] === 'Brasil' ? 'selected' : '' ?>>🇧🇷 Brasil</option>
                            <option value="Argentina" <?= $filtros['pais'] === 'Argentina' ? 'selected' : '' ?>>🇦🇷 Argentina</option>
                            <option value="Chile" <?= $filtros['pais'] === 'Chile' ? 'selected' : '' ?>>🇨🇱 Chile</option>
                            <option value="Perú" <?= $filtros['pais'] === 'Perú' ? 'selected' : '' ?>>🇵🇪 Perú</option>
                            <option value="Ecuador" <?= $filtros['pais'] === 'Ecuador' ? 'selected' : '' ?>>🇪🇨 Ecuador</option>
                            <option value="Venezuela" <?= $filtros['pais'] === 'Venezuela' ? 'selected' : '' ?>>🇻🇪 Venezuela</option>
                        </select>
                    </div>
                </div>

                <!-- Filtros Avanzados (Colapsables) -->
                <div class="collapse" id="filtrosAvanzados">
                    <hr>
                    <h6 class="text-muted mb-3">📋 Filtros Avanzados</h6>
                    
                    <!-- Fila 2: Ubicación y Contacto -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">🏙️ Ciudad/Departamento</label>
                            <input type="text" name="ciudad_depar" class="form-control" 
                                   placeholder="Bogotá, Medellín, etc..." 
                                   value="<?= htmlspecialchars($filtros['ciudad_depar']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">📧 Correo Electrónico</label>
                            <input type="text" name="correo" class="form-control" 
                                   placeholder="proveedor@email.com" 
                                   value="<?= htmlspecialchars($_GET['correo'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">📞 Teléfono</label>
                            <input type="text" name="tel_contacto" class="form-control" 
                                   placeholder="Número de teléfono" 
                                   value="<?= htmlspecialchars($_GET['tel_contacto'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">🆔 NIT/Identificación</label>
                            <input type="text" name="nit_num_identi" class="form-control" 
                                   placeholder="NIT o ID" 
                                   value="<?= htmlspecialchars($_GET['nit_num_identi'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Fila 3: Condiciones Comerciales -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">⏱️ Tiempo de Entrega</label>
                            <select name="tiem_entrega" class="form-select">
                                <option value="">Cualquier tiempo</option>
                                <option value="Inmediato" <?= ($_GET['tiem_entrega'] ?? '') === 'Inmediato' ? 'selected' : '' ?>>⚡ Inmediato</option>
                                <option value="1-3 días" <?= ($_GET['tiem_entrega'] ?? '') === '1-3 días' ? 'selected' : '' ?>>📅 1-3 días</option>
                                <option value="1 semana" <?= ($_GET['tiem_entrega'] ?? '') === '1 semana' ? 'selected' : '' ?>>📆 1 semana</option>
                                <option value="2 semanas" <?= ($_GET['tiem_entrega'] ?? '') === '2 semanas' ? 'selected' : '' ?>>🗓️ 2 semanas</option>
                                <option value="1 mes" <?= ($_GET['tiem_entrega'] ?? '') === '1 mes' ? 'selected' : '' ?>>📊 1 mes o más</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">💳 Forma de Pago</label>
                            <select name="for_pago" class="form-select">
                                <option value="">Cualquier forma</option>
                                <option value="Contado" <?= ($_GET['for_pago'] ?? '') === 'Contado' ? 'selected' : '' ?>>💰 Contado</option>
                                <option value="Crédito 30 días" <?= ($_GET['for_pago'] ?? '') === 'Crédito 30 días' ? 'selected' : '' ?>>📋 Crédito 30 días</option>
                                <option value="Crédito 60 días" <?= ($_GET['for_pago'] ?? '') === 'Crédito 60 días' ? 'selected' : '' ?>>📋 Crédito 60 días</option>
                                <option value="Crédito 90 días" <?= ($_GET['for_pago'] ?? '') === 'Crédito 90 días' ? 'selected' : '' ?>>📋 Crédito 90 días</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">💰 Con Crédito Disponible</label>
                            <select name="tiene_credito" class="form-select">
                                <option value="">Todos</option>
                                <option value="si" <?= ($_GET['tiene_credito'] ?? '') === 'si' ? 'selected' : '' ?>>✅ Con crédito</option>
                                <option value="no" <?= ($_GET['tiene_credito'] ?? '') === 'no' ? 'selected' : '' ?>>❌ Sin crédito</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">🔗 Estado de Repuestos</label>
                            <select name="estado_repuestos" class="form-select">
                                <option value="">Todos</option>
                                <option value="con_repuestos" <?= ($_GET['estado_repuestos'] ?? '') === 'con_repuestos' ? 'selected' : '' ?>>✅ Con repuestos vinculados</option>
                                <option value="sin_repuestos" <?= ($_GET['estado_repuestos'] ?? '') === 'sin_repuestos' ? 'selected' : '' ?>>❌ Sin repuestos vinculados</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> 🔍 Buscar Proveedores
                            </button>
                            <a href="proveedor.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> 🧹 Limpiar Filtros
                            </a>
                            <button type="button" class="btn btn-info" onclick="exportarResultados()">
                                <i class="bi bi-download"></i> 📊 Exportar
                            </button>
                            <div class="ms-auto">
                                <small class="text-muted">
                                    📋 Total de proveedores: <strong><?= $proveedores->num_rows ?></strong>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="mb-3 text-end">
        <?php if (!$rol_conductor): ?>
        <a href="proveedor.php?form=1" class="btn btn-success">Agregar Proveedor</a>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>ID</th>
                <th>NIT / Número Identificación</th>
                <th>Nombre Proveedor</th>
                <th>Teléfono Contacto</th>
                <th>Cargo Contacto</th>
                <th>Correo</th>
                <th>Dirección</th>
                <th>Ciudad/Departamento</th>
                <th>País</th>
                <th>Tipo Repuesto</th>
                <th>Marca que Distribuye</th>
                <th>Tiempo de Entrega</th>
                <th>Zonas de Cobertura</th>
                <th>Forma de Pago</th>
                <th>Crédito Disponible</th>
                <th>Cuenta Bancaria</th>
                <th>Repuestos Vinculados</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        require_once '../models/Repue.php';
        $repueModel = new Repue();
        while ($row = $proveedores->fetch_assoc()): 
            // ...existing code...
        ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nit_num_identi']) ?></td>
                <td><?= htmlspecialchars($row['nom_proveedor']) ?></td>
                <td><?= htmlspecialchars($row['tel_contacto']) ?></td>
                <td><?= htmlspecialchars($row['carg_contacto']) ?></td>
                <td><?= htmlspecialchars($row['correo']) ?></td>
                <td><?= htmlspecialchars($row['direccion']) ?></td>
                <td><?= htmlspecialchars($row['ciudad_depar']) ?></td>
                <td><?= htmlspecialchars($row['pais']) ?></td>
                <td><?= htmlspecialchars($row['tip_repuesto']) ?></td>
                <td><?= htmlspecialchars($row['mar_distribuye']) ?></td>
                <td><?= htmlspecialchars($row['tiem_entrega']) ?></td>
                <td><?= htmlspecialchars($row['zon_cobertura']) ?></td>
                <td><?= htmlspecialchars($row['for_pago']) ?></td>
                <td><?= htmlspecialchars($row['cred_disponible']) ?></td>
                <td><?= htmlspecialchars($row['cuen_bancaria']) ?></td>
                <td>
                    <?php 
                    $totalRepuestos = $row['total_repuestos'] ?? 0;
                    if ($totalRepuestos > 0) {
                        echo '<span class="badge bg-success me-1">' . $totalRepuestos . ' repuesto(s)</span>';
                        
                        // Obtener nombres de repuestos para mostrar detalles
                        $repuestosVinculados = $repueModel->getByProveedor($row['id']);
                        $nombreRepuestos = [];
                        while ($repuesto = $repuestosVinculados->fetch_assoc()) {
                            $nombreRepuestos[] = $repuesto['nombre'];
                        }
                        if (count($nombreRepuestos) <= 3) {
                            echo '<br><small class="text-muted">' . implode(', ', $nombreRepuestos) . '</small>';
                        } else {
                            echo '<br><small class="text-muted">' . implode(', ', array_slice($nombreRepuestos, 0, 3)) . '...</small>';
                            echo '<br><small><a href="#" class="text-info" data-bs-toggle="modal" data-bs-target="#repuestosModal' . $row['id'] . '">Ver todos</a></small>';
                        }
                    } else {
                        echo '<span class="badge bg-secondary">Sin repuestos</span>';
                    }
                    ?>
                </td>
                <td class="text-center">
                    <?php if (!$rol_conductor): ?>
                    <a href="proveedor.php?form=1&id=<?= $row['id'] ?>" class="btn btn-warning mx-1">Editar</a>
                    <a href="proveedor.php?delete=<?= $row['id'] ?>" class="btn btn-danger mx-1" onclick="return confirm('¿Eliminar proveedor?')">Eliminar</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
        </table>
    </div>
    <?php if (isset($_GET['form']) && !$rol_conductor): ?>
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title"><?= isset($editData) ? 'Editar' : 'Agregar' ?> Proveedor</h5>
            <?php $editData = isset($_GET['id']) ? $controller->show($_GET['id']) : []; ?>
            <form method="post">
                <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">NIT / Número Identificación</label>
                        <input type="text" name="nit_num_identi" class="form-control" required value="<?= htmlspecialchars($editData['nit_num_identi'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre Proveedor</label>
                        <input type="text" name="nom_proveedor" class="form-control" required value="<?= htmlspecialchars($editData['nom_proveedor'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Teléfono Contacto</label>
                        <input type="text" name="tel_contacto" class="form-control" value="<?= htmlspecialchars($editData['tel_contacto'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Cargo Contacto</label>
                        <input type="text" name="carg_contacto" class="form-control" value="<?= htmlspecialchars($editData['carg_contacto'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($editData['correo'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" value="<?= htmlspecialchars($editData['direccion'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Ciudad/Departamento</label>
                        <input type="text" name="ciudad_depar" class="form-control" value="<?= htmlspecialchars($editData['ciudad_depar'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">País</label>
                        <input type="text" name="pais" class="form-control" value="<?= htmlspecialchars($editData['pais'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tipo Repuesto</label>
                        <input type="text" name="tip_repuesto" class="form-control" value="<?= htmlspecialchars($editData['tip_repuesto'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Marca que Distribuye</label>
                        <input type="text" name="mar_distribuye" class="form-control" value="<?= htmlspecialchars($editData['mar_distribuye'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tiempo de Entrega</label>
                        <input type="text" name="tiem_entrega" class="form-control" value="<?= htmlspecialchars($editData['tiem_entrega'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Zonas de Cobertura</label>
                        <input type="text" name="zon_cobertura" class="form-control" value="<?= htmlspecialchars($editData['zon_cobertura'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Forma de Pago</label>
                        <input type="text" name="for_pago" class="form-control" value="<?= htmlspecialchars($editData['for_pago'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Crédito Disponible</label>
                        <input type="text" name="cred_disponible" class="form-control" value="<?= htmlspecialchars($editData['cred_disponible'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Cuenta Bancaria</label>
                        <input type="text" name="cuen_bancaria" class="form-control" value="<?= htmlspecialchars($editData['cuen_bancaria'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <!-- Eliminado campo Categoría de Repuesto -->
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Vincular Repuestos Existentes</label>
                        <select name="repuestos_vinculados[]" class="form-control" multiple>
                            <?php $repueModel = new Repue(); $repuestos = $repueModel->getAll(); while ($rep = $repuestos->fetch_assoc()): ?>
                                <option value="<?= $rep['id'] ?>" <?= (isset($editData['id']) && $rep['proveedor_id'] == $editData['id']) ? 'selected' : '' ?>><?= htmlspecialchars($rep['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <small class="text-muted">Selecciona los repuestos que deseas vincular a este proveedor.</small>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="proveedor.php" class="btn btn-secondary mx-2">Cancelar</a>
            </form>
        </div>
    </div>
    <?php endif; ?>
    <div class="mt-5 text-end">
        <a href="gestiones.php" class="btn btn-outline-secondary">Volver al panel de gestiones</a>
    </div>
</div>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->store($_POST);
    header('Location: proveedor.php');
    exit;
}
if (isset($_GET['delete'])) {
    $controller->delete($_GET['delete']);
    header('Location: proveedor.php');
    exit;
}
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Función para exportar resultados
function exportarResultados() {
    // Crear CSV con los datos de la tabla
    let csv = 'ID,NIT,Nombre,Teléfono,Correo,Ciudad,País,Tipo Repuesto,Marca,Repuestos Vinculados\n';
    
    const filas = document.querySelectorAll('tbody tr');
    filas.forEach(fila => {
        const celdas = fila.querySelectorAll('td');
        if (celdas.length > 0) {
            const datos = [
                celdas[0].textContent.trim(), // ID
                celdas[1].textContent.trim(), // NIT
                celdas[2].textContent.trim(), // Nombre
                celdas[3].textContent.trim(), // Teléfono
                celdas[5].textContent.trim(), // Correo
                celdas[7].textContent.trim(), // Ciudad
                celdas[8].textContent.trim(), // País
                celdas[9].textContent.trim(), // Tipo Repuesto
                celdas[10].textContent.trim(), // Marca
                celdas[16].textContent.trim().replace(/\n/g, ' ') // Repuestos
            ];
            csv += datos.map(dato => `"${dato}"`).join(',') + '\n';
        }
    });
    
    // Descargar CSV
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'proveedores_' + new Date().toISOString().split('T')[0] + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Mostrar notificación
    const toast = document.createElement('div');
    toast.className = 'position-fixed top-0 end-0 p-3';
    toast.style.zIndex = '1050';
    toast.innerHTML = `
        <div class="toast show" role="alert">
            <div class="toast-header">
                <strong class="me-auto">✅ Exportación</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                Archivo CSV descargado exitosamente
            </div>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => document.body.removeChild(toast), 3000);
}

// Filtros automáticos (buscar mientras escribes)
document.addEventListener('DOMContentLoaded', function() {
    const filtrosTexto = document.querySelectorAll('input[type="text"]');
    let timeoutId;
    
    filtrosTexto.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                if (this.value.length > 2 || this.value.length === 0) {
                    // Solo buscar automáticamente si hay más de 2 caracteres o está vacío
                    // document.getElementById('formFiltros').submit();
                }
            }, 800); // Esperar 800ms después de que el usuario deje de escribir
        });
    });
    
    // Contador dinámico de resultados
    const totalResultados = document.querySelectorAll('tbody tr').length;
    const contadorElement = document.querySelector('.ms-auto small strong');
    if (contadorElement) {
        contadorElement.textContent = totalResultados;
    }
});

// Función para limpiar todos los filtros
function limpiarTodosFiltros() {
    document.querySelectorAll('#formFiltros input, #formFiltros select').forEach(element => {
        element.value = '';
    });
    document.getElementById('formFiltros').submit();
}

// Atajos de teclado
document.addEventListener('keydown', function(e) {
    // Ctrl + F para enfocar en el primer filtro
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        document.querySelector('input[name="nom_proveedor"]').focus();
    }
    // Ctrl + L para limpiar filtros
    if (e.ctrlKey && e.key === 'l') {
        e.preventDefault();
        limpiarTodosFiltros();
    }
});
</script>
</body>
</html>
