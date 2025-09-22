<?php
require_once '../controllers/RepueController.php';
$controller = new RepueController();
// Manejo de POST y delete antes de cualquier salida
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->store($_POST);
    header('Location: repue.php');
    exit;
}
if (isset($_GET['delete'])) {
    $controller->delete($_GET['delete']);
    header('Location: repue.php');
    exit;
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Gestión de Repuestos</h2>
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
                <?php while ($prov = $proveedores->fetch_assoc()): ?>
                    <option value="<?= $prov['id'] ?>" <?= (isset($_GET['proveedor_id']) && $_GET['proveedor_id'] == $prov['id']) ? 'selected' : '' ?>><?= htmlspecialchars($prov['nom_proveedor']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
        <div class="col-md-2 mb-2">
            <a href="repue.php" class="btn btn-secondary w-100">Limpiar</a>
        </div>
    </form>
    <div class="mb-3 text-end">
        <a href="repue.php?form=1" class="btn btn-success">Agregar Repuesto</a>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Subcategoría</th>
                <th>Marca</th>
                <th>Proveedor</th>
                <th>Modelo</th>
                <th>Medidas</th>
                <th>Norma/Estándar</th>
                <th>Número de Parte</th>
                <th>Descripción Técnica</th>
                <th>Vehículo Compatible</th>
                <th>Cantidad</th>
                <th>Estado</th>
                <th>Fecha Ingreso</th>
                <th>Número Factura</th>
                <th>Ubicación Almacén</th>
                <th>Precio Unitario</th>
                <th>Costo Total</th>
                <th>Garantía</th>
                <th>Responsable Ingreso</th>
                <th>Stock</th>
                <th>Fecha Vencimiento</th>
                <th>Área Destino</th>
                <th>Firma Verificación</th>
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
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= $cat ? htmlspecialchars($cat['nombre']) : '' ?></td>
                <td><?= $subcat ? htmlspecialchars($subcat['nombre']) : '' ?></td>
                <td><?= htmlspecialchars($row['marca_repuesto']) ?></td>
                <td><?= $prov ? htmlspecialchars($prov['nom_proveedor']) : '' ?></td>
                <td><?= htmlspecialchars($row['modelo']) ?></td>
                <td><?= htmlspecialchars($row['medidas_espe']) ?></td>
                <td><?= htmlspecialchars($row['norma_estan']) ?></td>
                <td><?= htmlspecialchars($row['numero_parte']) ?></td>
                <td><?= htmlspecialchars($row['des_tecnica']) ?></td>
                <td><?= htmlspecialchars($row['veh_compatible']) ?></td>
                <td><?= $row['cantidad'] ?></td>
                <td><?= htmlspecialchars($row['estado_repus']) ?></td>
                <td><?= htmlspecialchars($row['fecha_ingreso']) ?></td>
                <td><?= htmlspecialchars($row['num_factura']) ?></td>
                <td><?= htmlspecialchars($row['ubi_almacen']) ?></td>
                <td><?= $row['pre_unitario'] ?></td>
                <td><?= $row['costo_total'] ?></td>
                <td><?= htmlspecialchars($row['garantia']) ?></td>
                <td><?= htmlspecialchars($row['res_ingreso']) ?></td>
                <td><?= $row['cant_stock'] ?></td>
                <td><?= htmlspecialchars($row['fecha_venci']) ?></td>
                <td><?= htmlspecialchars($row['dest_area']) ?></td>
                <td><?= htmlspecialchars($row['firma_verificacion']) ?></td>
                <td class="text-center">
                    <a href="repue.php?form=1&id=<?= $row['id'] ?>" class="btn btn-warning mx-1">Editar</a>
                    <a href="repue.php?delete=<?= $row['id'] ?>" class="btn btn-danger mx-1" onclick="return confirm('¿Eliminar repuesto?')">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php if (isset($_GET['form'])): ?>
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
                        <select name="proveedor_id" class="form-control" required>
                            <option value="">Seleccione un proveedor</option>
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
    <div class="mt-5 text-end">
        <a href="gestiones.php" class="btn btn-outline-secondary">Volver al panel de gestiones</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
