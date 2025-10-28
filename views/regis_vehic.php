<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Vehículo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
        .container {
            max-width: 1200px;
            background: #FFFFFF;
            border: 1px solid rgba(209, 213, 219, 0.3);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-top: 2rem;
            margin-bottom: 2rem;
            padding: 2rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .container:hover {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
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
        .main-header h2 {
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
            .container {
                padding: 1rem;
            }
            .main-header {
                padding: 1.5rem;
                text-align: center;
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
            <?php if (!$rol_conductor): ?>
            <a href="regis_vehic.php?form=1" class="btn btn-success"><i class="bi bi-plus-circle"></i> Agregar Vehículo</a>
            <?php endif; ?>
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
                            <th>Estado</th>
                            <th>Conductor Asignado</th>
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
                            <td><?= htmlspecialchars($row['estado']) ?></td>
                            <td>
                                <?php
                                if (!empty($row['cond_id'])) {
                                    $stmtCond = $conn->prepare("SELECT cargo FROM cond WHERE id = ?");
                                    $stmtCond->bind_param('i', $row['cond_id']);
                                    $stmtCond->execute();
                                    $resCond = $stmtCond->get_result();
                                    if ($resCond && $resCond->num_rows > 0) {
                                        $conductor = $resCond->fetch_assoc();
                                        echo htmlspecialchars($conductor['cargo']);
                                    } else {
                                        echo '<span class="text-muted">No asignado</span>';
                                    }
                                } else {
                                    echo '<span class="text-muted">No asignado</span>';
                                }
                                ?>
                            </td>
                            <td class="text-center">
                                <?php if (!$rol_conductor): ?>
                                <a href="regis_vehic.php?form=1&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm mx-1"><i class="bi bi-pencil-square"></i> Editar</a>
                                <a href="regis_vehic.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm mx-1" onclick="return confirm('¿Eliminar vehículo?')"><i class="bi bi-trash"></i> Eliminar</a>
                                <?php endif; ?>
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
    if (isset($_GET['form']) && !$rol_conductor):
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
        if (!$rol_conductor) {
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
            // Asignar conductor a vehículo
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignar_conductor'], $_POST['vehiculo_id'], $_POST['conductor_id'])) {
                $vehiculo_id = intval($_POST['vehiculo_id']);
                $conductor_id = intval($_POST['conductor_id']);
                // Actualizar cond_id y estado
                $sql = "UPDATE regis_vehic SET cond_id = ?, estado = 'Asignado' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ii', $conductor_id, $vehiculo_id);
                $stmt->execute();
                echo '<script>window.location="regis_vehic.php";</script>';
                exit;
            }
            // Eliminar vehículo
            if (isset($_GET['delete'])) {
                try {
                    $vehiculo_id = $_GET['delete'];
                    
                    // PASO 1: Verificar dependencias del vehículo
                    
                    // Verificar alertas
                    $check_alerts_sql = "SELECT COUNT(*) as total FROM alert WHERE regis_vehic_id = ?";
                    $check_alerts_stmt = $conn->prepare($check_alerts_sql);
                    $check_alerts_stmt->bind_param('i', $vehiculo_id);
                    $check_alerts_stmt->execute();
                    $alerts_count = $check_alerts_stmt->get_result()->fetch_assoc()['total'];
                    
                    // Verificar conductor asignado
                    $check_conductor_sql = "SELECT cond_id FROM regis_vehic WHERE id = ?";
                    $check_conductor_stmt = $conn->prepare($check_conductor_sql);
                    $check_conductor_stmt->bind_param('i', $vehiculo_id);
                    $check_conductor_stmt->execute();
                    $conductor_id = $check_conductor_stmt->get_result()->fetch_assoc()['cond_id'];
                    
                    // Si hay dependencias, informar al usuario y cancelar
                    $dependencias = [];
                    if ($alerts_count > 0) {
                        $dependencias[] = "$alerts_count alerta(s) activa(s)";
                    }
                    if ($conductor_id) {
                        $dependencias[] = "1 conductor asignado";
                    }
                    
                    if (count($dependencias) > 0) {
                        $mensaje_dependencias = implode(" y ", $dependencias);
                        echo "<script>
                            alert('No se puede eliminar el vehículo porque tiene: $mensaje_dependencias\\n\\nPrimero debe:\\n- Resolver o cerrar las alertas desde el módulo de alertas\\n- Desasignar el conductor desde este mismo módulo\\n\\nDespués podrá eliminar el vehículo de forma segura.');
                            window.location.href = 'regis_vehic.php';
                        </script>";
                        exit;
                    }
                    
                    // PASO 2: Eliminar directamente el vehículo (ya verificamos que no tiene dependencias)
                    $sql = "DELETE FROM regis_vehic WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('i', $vehiculo_id);
                    $stmt->execute();
                    
                    if ($stmt->affected_rows > 0) {
                        error_log("regis_vehic.php: Vehículo $vehiculo_id eliminado exitosamente (eliminación simple)");
                        echo '<script>alert("Vehículo eliminado correctamente."); window.location="regis_vehic.php";</script>';
                    } else {
                        error_log("regis_vehic.php: No se pudo eliminar el vehículo $vehiculo_id");
                        echo '<script>alert("Error: No se pudo eliminar el vehículo."); window.location="regis_vehic.php";</script>';
                    }
                    exit;
                    
                } catch (Exception $e) {
                    error_log("regis_vehic.php: Error al eliminar vehículo: " . $e->getMessage());
                    echo '<script>alert("Error al eliminar el vehículo: ' . addslashes($e->getMessage()) . '"); window.location="regis_vehic.php";</script>';
                    exit;
                }
            }
        }
        ?>
        <div class="mt-5 text-end">
            <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver al Dashboard</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
