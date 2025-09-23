<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Vehículo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #f8fafc 0%, #e3e6ed 100%);
        }
        .card {
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            border-radius: 1rem;
        }
        .card-title {
            font-weight: 600;
        }
        .navbar-brand {
            font-weight: bold;
            letter-spacing: 1px;
        }
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-0">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><i class="bi bi-truck"></i> Trucksisx</a>
            <span class="navbar-text">Registro de Vehículo</span>
        </div>
    </nav>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0"><i class="bi bi-journal-plus"></i> Registro de Vehículo</h2>
            <a href="regis_vehic.php?form=1" class="btn btn-success"><i class="bi bi-plus-circle"></i> Agregar Vehículo</a>
        </div>
        <?php
        require_once '../config/db.php';
        $conn = conectarDB();
        // Obtener categorías y subcategorías para filtros y formulario
        $catQuery = $conn->query("SELECT id, nombre FROM cat_vehic ORDER BY nombre ASC");
        $categorias = [];
        while ($cat = $catQuery->fetch_assoc()) {
            $categorias[$cat['id']] = $cat['nombre'];
        }
        $subcatQuery = $conn->query("SELECT s.id, s.nombre, c.nombre AS categoria FROM subcat_vehic s JOIN cat_vehic c ON s.cat_vehic_id = c.id ORDER BY s.nombre ASC");
        $subcategorias = [];
        while ($subcat = $subcatQuery->fetch_assoc()) {
            $subcategorias[$subcat['id']] = [
                'nombre' => $subcat['nombre'],
                'categoria' => $subcat['categoria']
            ];
        }
        // Filtros
        $filtro_placa = isset($_GET['filtro_placa']) ? $_GET['filtro_placa'] : '';
        $filtro_marca = isset($_GET['filtro_marca']) ? $_GET['filtro_marca'] : '';
        $filtro_cat = isset($_GET['filtro_cat']) ? $_GET['filtro_cat'] : '';
        $filtro_subcat = isset($_GET['filtro_subcat']) ? $_GET['filtro_subcat'] : '';
        $sql = "SELECT v.*, s.nombre AS subcat_nombre, c.nombre AS cat_nombre FROM regis_vehic v JOIN subcat_vehic s ON v.subcat_vehic_id = s.id JOIN cat_vehic c ON s.cat_vehic_id = c.id WHERE v.placa LIKE ? AND v.marca_vehiculo LIKE ?";
        $params = ["%$filtro_placa%", "%$filtro_marca%"];
        $types = 'ss';
        if ($filtro_cat) {
            $sql .= " AND c.id = ?";
            $params[] = $filtro_cat;
            $types .= 'i';
        }
        if ($filtro_subcat) {
            $sql .= " AND s.id = ?";
            $params[] = $filtro_subcat;
            $types .= 'i';
        }
        $sql .= " ORDER BY v.id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $vehiculos = $stmt->get_result();
        ?>
        <form class="row mb-4" method="get">
            <div class="col-md-3">
                <input type="text" name="filtro_placa" class="form-control" placeholder="Buscar por placa" value="<?= htmlspecialchars($filtro_placa) ?>">
            </div>
            <div class="col-md-3">
                <input type="text" name="filtro_marca" class="form-control" placeholder="Buscar por marca" value="<?= htmlspecialchars($filtro_marca) ?>">
            </div>
            <div class="col-md-3">
                <select name="filtro_cat" class="form-select">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $id => $nombre): ?>
                        <option value="<?= $id ?>" <?= $filtro_cat == $id ? 'selected' : '' ?>><?= htmlspecialchars($nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="filtro_subcat" class="form-select">
                    <option value="">Todas las subcategorías</option>
                    <?php foreach ($subcategorias as $id => $subcat): ?>
                        <option value="<?= $id ?>" <?= $filtro_subcat == $id ? 'selected' : '' ?>><?= htmlspecialchars($subcat['nombre']) ?> (<?= htmlspecialchars($subcat['categoria']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 mt-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
                <a href="regis_vehic.php" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Limpiar</a>
            </div>
        </form>
        <div class="card">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Placa</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Color</th>
                            <th>Cilindraje</th>
                            <th>Cap. Carga</th>
                            <th>Subcategoría</th>
                            <th>Categoría</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($vehiculos && $vehiculos->num_rows > 0): ?>
                        <?php while ($row = $vehiculos->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['placa']) ?></td>
                            <td><?= htmlspecialchars($row['marca_vehiculo']) ?></td>
                            <td><?= htmlspecialchars($row['modelo']) ?></td>
                            <td><?= htmlspecialchars($row['color']) ?></td>
                            <td><?= htmlspecialchars($row['cilindraje']) ?></td>
                            <td><?= htmlspecialchars($row['cap_carga']) ?></td>
                            <td><?= htmlspecialchars($row['subcat_nombre']) ?></td>
                            <td><?= htmlspecialchars($row['cat_nombre']) ?></td>
                            <td class="text-center">
                                <a href="regis_vehic.php?form=1&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm mx-1"><i class="bi bi-pencil-square"></i> Editar</a>
                                <a href="regis_vehic.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm mx-1" onclick="return confirm('¿Eliminar vehículo?')"><i class="bi bi-trash"></i> Eliminar</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">No hay vehículos registrados.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        // Formulario alta/edición
        if (isset($_GET['form'])):
            $editData = [];
            if (isset($_GET['id'])) {
                $sql = "SELECT * FROM regis_vehic WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $_GET['id']);
                $stmt->execute();
                $editData = $stmt->get_result()->fetch_assoc();
            }
        ?>
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-pencil-square"></i> <?= isset($editData['id']) ? 'Editar' : 'Agregar' ?> Vehículo</h5>
                <form method="post" action="regis_vehic.php">
                    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Placa</label>
                            <input type="text" name="placa" class="form-control" required value="<?= htmlspecialchars($editData['placa'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Marca</label>
                            <input type="text" name="marca_vehiculo" class="form-control" required value="<?= htmlspecialchars($editData['marca_vehiculo'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Modelo</label>
                            <input type="text" name="modelo" class="form-control" required value="<?= htmlspecialchars($editData['modelo'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Color</label>
                            <input type="text" name="color" class="form-control" value="<?= htmlspecialchars($editData['color'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cilindraje</label>
                            <input type="text" name="cilindraje" class="form-control" value="<?= htmlspecialchars($editData['cilindraje'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Capacidad de Carga</label>
                            <input type="text" name="cap_carga" class="form-control" value="<?= htmlspecialchars($editData['cap_carga'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Subcategoría de Vehículo</label>
                            <select name="subcat_vehic_id" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($subcategorias as $id => $subcat): ?>
                                    <option value="<?= $id ?>" <?= (isset($editData['subcat_vehic_id']) && $editData['subcat_vehic_id'] == $id) ? 'selected' : '' ?>><?= htmlspecialchars($subcat['nombre']) ?> (<?= htmlspecialchars($subcat['categoria']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Número de Chasis</label>
                            <input type="text" name="num_cha" class="form-control" value="<?= htmlspecialchars($editData['num_cha'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Distribución de Ejes</label>
                            <input type="text" name="distru_ejes" class="form-control" value="<?= htmlspecialchars($editData['distru_ejes'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Línea Marca</label>
                            <input type="text" name="linea_marca" class="form-control" value="<?= htmlspecialchars($editData['linea_marca'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tecnomecánica</label>
                            <input type="text" name="tecnomecanica" class="form-control" value="<?= htmlspecialchars($editData['tecnomecanica'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">SOAT</label>
                            <input type="text" name="soat" class="form-control" value="<?= htmlspecialchars($editData['soat'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo de Unidad</label>
                            <input type="text" name="tipo_unidad" class="form-control" value="<?= htmlspecialchars($editData['tipo_unidad'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo de Combustible</label>
                            <input type="text" name="tipo_combustible" class="form-control" value="<?= htmlspecialchars($editData['tipo_combustible'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">RUNT</label>
                            <input type="text" name="RUNT" class="form-control" value="<?= htmlspecialchars($editData['RUNT'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cert. Homologación</label>
                            <input type="text" name="cert_homologacion" class="form-control" value="<?= htmlspecialchars($editData['cert_homologacion'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cert. Matrícula</label>
                            <input type="text" name="cert_matricula" class="form-control" value="<?= htmlspecialchars($editData['cert_matricula'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tarjeta de Propiedad</label>
                            <input type="text" name="tarje_propiedad" class="form-control" value="<?= htmlspecialchars($editData['tarje_propiedad'] ?? '') ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success mt-3"><i class="bi bi-check-lg"></i> Guardar</button>
                    <a href="regis_vehic.php" class="btn btn-secondary mx-2 mt-3"><i class="bi bi-x-lg"></i> Cancelar</a>
                </form>
            </div>
        </div>
        <?php endif; ?>
        <?php
        // Guardar/editar vehículo
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['placa'], $_POST['marca_vehiculo'], $_POST['modelo'], $_POST['subcat_vehic_id'])) {
            $fields = [
                'num_cha','placa','distru_ejes','marca_vehiculo','modelo','color','cilindraje','cap_carga','linea_marca','tecnomecanica','soat','tipo_unidad','tipo_combustible','RUNT','cert_homologacion','cert_matricula','tarje_propiedad','subcat_vehic_id'
            ];
            $values = [];
            foreach ($fields as $f) {
                $values[] = $_POST[$f] ?? '';
            }
            if (!empty($_POST['id'])) {
                 $sql = "UPDATE regis_vehic SET num_cha=?, placa=?, distru_ejes=?, marca_vehiculo=?, modelo=?, color=?, cilindraje=?, cap_carga=?, linea_marca=?, tecnomecanica=?, soat=?, tipo_unidad=?, tipo_combustible=?, RUNT=?, cert_homologacion=?, cert_matricula=?, tarje_propiedad=?, subcat_vehic_id=? WHERE id=?";
                 $stmt = $conn->prepare($sql);
                 $values_update = $values;
                 $values_update[] = $_POST['id'];
                 $stmt->bind_param('ssssssssssssssssiii', ...$values_update);
                 $stmt->execute();
            } else {
                $sql = "INSERT INTO regis_vehic (num_cha, placa, distru_ejes, marca_vehiculo, modelo, color, cilindraje, cap_carga, linea_marca, tecnomecanica, soat, tipo_unidad, tipo_combustible, RUNT, cert_homologacion, cert_matricula, tarje_propiedad, subcat_vehic_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssssssssssssssssii', ...$values);
                $stmt->execute();
            }
            echo '<script>window.location="regis_vehic.php";</script>';
            exit;
        }
        // Eliminar vehículo
        if (isset($_GET['delete'])) {
            $sql = "DELETE FROM regis_vehic WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $_GET['delete']);
            $stmt->execute();
            echo '<script>window.location="regis_vehic.php";</script>';
            exit;
        }
        ?>
        <div class="mt-5 text-end">
            <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver al Dashboard</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
