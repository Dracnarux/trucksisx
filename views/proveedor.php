<?php
require_once '../controllers/ProveedorController.php';
$controller = new ProveedorController();
// Manejo de POST y delete antes de cualquier salida
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
// Filtros
$filtros = [];
foreach ([
    'nom_proveedor', 'tip_repuesto', 'mar_distribuye', 'ciudad_depar', 'pais'
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
    <form class="row mb-4" method="get">
        <div class="col-md-2 mb-2">
            <input type="text" name="pais" class="form-control" placeholder="Filtrar por país" value="<?= htmlspecialchars($filtros['pais']) ?>">
        </div>
        <div class="col-md-2 mb-2">
            <select name="cat_repu_id" class="form-control" aria-label="Filtrar por categoría">
                <option value="">Filtrar por categoría</option>
                <?php while ($cat = $categorias->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>" <?= (isset($_GET['cat_repu_id']) && $_GET['cat_repu_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <select name="subcat_repu_id" class="form-control" aria-label="Filtrar por subcategoría">
                <option value="">Filtrar por subcategoría</option>
                <?php while ($subcat = $subcategorias->fetch_assoc()): ?>
                    <option value="<?= $subcat['id'] ?>" <?= (isset($_GET['subcat_repu_id']) && $_GET['subcat_repu_id'] == $subcat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($subcat['nombre']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <select name="repue_id" class="form-control" aria-label="Filtrar por repuesto">
                <option value="">Filtrar por repuesto</option>
                <?php while ($rep = $repuestos->fetch_assoc()): ?>
                    <option value="<?= $rep['id'] ?>" <?= (isset($_GET['repue_id']) && $_GET['repue_id'] == $rep['id']) ? 'selected' : '' ?>><?= htmlspecialchars($rep['nombre']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <input type="text" name="mar_distribuye" class="form-control" placeholder="Filtrar por marca" value="<?= htmlspecialchars($filtros['mar_distribuye']) ?>">
        </div>
        <div class="col-md-1 mb-2">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
        <div class="col-md-1 mb-2">
            <a href="proveedor.php" class="btn btn-secondary w-100">Limpiar</a>
        </div>
    </form>
    <div class="mb-3 text-end">
        <a href="proveedor.php?form=1" class="btn btn-success">Agregar Proveedor</a>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Cargo Contacto</th>
                <th>Correo</th>
                <th>Dirección</th>
                <th>Ciudad/Departamento</th>
                <th>País</th>
                <th>Categoría de Repuesto</th>
                <th>Marca que Distribuye</th>
                <th>Repuestos</th>
                <th>Tiempo de Entrega</th>
                <th>Zonas de Cobertura</th>
                <th>Forma de Pago</th>
                <th>Crédito Disponible</th>
                <th>Cuenta Bancaria</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        require_once '../models/Repue.php';
        $repueModel = new Repue();
        while ($row = $proveedores->fetch_assoc()): 
            $productos = [];
            $repuestosProveedor = $repueModel->getAll(['proveedor_id' => $row['id']]);
            while ($rep = $repuestosProveedor->fetch_assoc()) {
                $productos[] = htmlspecialchars($rep['nombre']);
            }
        ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nom_proveedor']) ?></td>
                <td><?= htmlspecialchars($row['tel_contacto']) ?></td>
                <td><?= htmlspecialchars($row['carg_contacto']) ?></td>
                <td><?= htmlspecialchars($row['correo']) ?></td>
                <td><?= htmlspecialchars($row['direccion']) ?></td>
                <td><?= htmlspecialchars($row['ciudad_depar']) ?></td>
                <td><?= htmlspecialchars($row['pais']) ?></td>
                <td><?= htmlspecialchars($row['tip_repuesto']) ?></td>
                <td><?= htmlspecialchars($row['mar_distribuye']) ?></td>
                <td><?= $productos ? implode(', ', $productos) : '' ?></td> <!-- Repuestos -->
                <td><?= htmlspecialchars($row['tiem_entrega']) ?></td>
                <td><?= htmlspecialchars($row['zon_cobertura']) ?></td>
                <td><?= htmlspecialchars($row['for_pago']) ?></td>
                <td><?= htmlspecialchars($row['cred_disponible']) ?></td>
                <td><?= htmlspecialchars($row['cuen_bancaria']) ?></td>
                <td class="text-center">
                    <a href="proveedor.php?form=1&id=<?= $row['id'] ?>" class="btn btn-warning mx-1">Editar</a>
                    <a href="proveedor.php?delete=<?= $row['id'] ?>" class="btn btn-danger mx-1" onclick="return confirm('¿Eliminar proveedor?')">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php if (isset($_GET['form'])): ?>
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title"><?= isset($editData) ? 'Editar' : 'Agregar' ?> Proveedor</h5>
            <?php $editData = isset($_GET['id']) ? $controller->show($_GET['id']) : []; ?>
            <form method="post">
                <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nom_proveedor" class="form-control" required value="<?= htmlspecialchars($editData['nom_proveedor'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Teléfono de Contacto</label>
                        <input type="text" name="tel_contacto" class="form-control" value="<?= htmlspecialchars($editData['tel_contacto'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Cargo del Contacto</label>
                        <input type="text" name="carg_contacto" class="form-control" value="<?= htmlspecialchars($editData['carg_contacto'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Correo Electrónico</label>
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
                        <label class="form-label">Categoría de Repuesto</label>
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
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Repuestos asociados</label>
                        <select name="repue_id[]" class="form-control" multiple style="height: 38px; min-height: 38px; max-height: 38px;">
                            <?php foreach ($repuestos as $rep): ?>
                                <option value="<?= $rep['id'] ?>" <?= (isset($editData['repue_id']) && in_array($rep['id'], explode(',', $editData['repue_id'])) ? 'selected' : '') ?>><?= htmlspecialchars($rep['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
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
</body>
</html>
