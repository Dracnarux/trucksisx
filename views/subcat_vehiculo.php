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
    <title>Subcategoría de Vehículos</title>
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
            max-width: 900px;
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
            <span class="navbar-text">Subcategoría de Vehículos</span>
        </div>
    </nav>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0"><i class="bi bi-truck"></i> Subcategoría de Vehículos</h2>
            <?php if (!$rol_conductor): ?>
            <a href="subcat_vehiculo.php?form=1" class="btn btn-success"><i class="bi bi-plus-circle"></i> Agregar Subcategoría</a>
            <?php endif; ?>
        </div>
        <?php
        require_once '../config/db.php';
        $conn = conectarDB();
        // Obtener categorías para filtro y formulario
        $catQuery = $conn->query("SELECT id, nombre FROM cat_vehic ORDER BY nombre ASC");
        $categorias = [];
        while ($cat = $catQuery->fetch_assoc()) {
            $categorias[$cat['id']] = $cat['nombre'];
        }
        $filtro_nombre = isset($_GET['filtro_nombre']) ? $_GET['filtro_nombre'] : '';
        $filtro_cat = isset($_GET['filtro_cat']) ? $_GET['filtro_cat'] : '';
        $sql = "SELECT s.id, s.nombre, c.nombre AS categoria FROM subcat_vehic s JOIN cat_vehic c ON s.cat_vehic_id = c.id WHERE s.nombre LIKE ?";
        $params = ["%$filtro_nombre%"];
        if ($filtro_cat) {
            $sql .= " AND s.cat_vehic_id = ?";
            $params[] = $filtro_cat;
        }
        $sql .= " ORDER BY s.id DESC";
        $stmt = $conn->prepare($sql);
        if ($filtro_cat) {
            $stmt->bind_param('si', $params[0], $params[1]);
        } else {
            $stmt->bind_param('s', $params[0]);
        }
        $stmt->execute();
        $subcategorias = $stmt->get_result();
        ?>
        <form class="row mb-4" method="get">
            <div class="col-md-4">
                <input type="text" name="filtro_nombre" class="form-control" placeholder="Buscar por nombre" value="<?= htmlspecialchars($filtro_nombre) ?>">
            </div>
            <div class="col-md-4">
                <select name="filtro_cat" class="form-select">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $id => $nombre): ?>
                        <option value="<?= $id ?>" <?= $filtro_cat == $id ? 'selected' : '' ?>><?= htmlspecialchars($nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filtrar</button>
            </div>
            <div class="col-md-2">
                <a href="subcat_vehiculo.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle"></i> Limpiar</a>
            </div>
        </form>
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Categoría de Vehículo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($subcategorias && $subcategorias->num_rows > 0): ?>
                        <?php while ($row = $subcategorias->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['nombre']) ?></td>
                            <td><?= htmlspecialchars($row['categoria']) ?></td>
                            <td class="text-center">
                                <?php if (!$rol_conductor): ?>
                                <a href="subcat_vehiculo.php?form=1&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm mx-1"><i class="bi bi-pencil-square"></i> Editar</a>
                                <a href="subcat_vehiculo.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm mx-1" onclick="return confirm('¿Eliminar subcategoría?')"><i class="bi bi-trash"></i> Eliminar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay subcategorías registradas.</td>
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
                $sql = "SELECT * FROM subcat_vehic WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $_GET['id']);
                $stmt->execute();
                $editData = $stmt->get_result()->fetch_assoc();
            }
        ?>
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-pencil-square"></i> <?= isset($editData['id']) ? 'Editar' : 'Agregar' ?> Subcategoría</h5>
                <form method="post" action="subcat_vehiculo.php">
                    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($editData['nombre'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoría de Vehículo</label>
                        <select name="cat_vehic_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($categorias as $id => $nombre): ?>
                                <option value="<?= $id ?>" <?= (isset($editData['cat_vehic_id']) && $editData['cat_vehic_id'] == $id) ? 'selected' : '' ?>><?= htmlspecialchars($nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Guardar</button>
                    <a href="subcat_vehiculo.php" class="btn btn-secondary mx-2"><i class="bi bi-x-lg"></i> Cancelar</a>
                </form>
            </div>
        </div>
        <?php endif; ?>
        <?php
        // Guardar/editar subcategoría
        if (!$rol_conductor) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'], $_POST['cat_vehic_id'])) {
                if (!empty($_POST['id'])) {
                    $sql = "UPDATE subcat_vehic SET nombre = ?, cat_vehic_id = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('sii', $_POST['nombre'], $_POST['cat_vehic_id'], $_POST['id']);
                    $stmt->execute();
                } else {
                    $sql = "INSERT INTO subcat_vehic (nombre, cat_vehic_id) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('si', $_POST['nombre'], $_POST['cat_vehic_id']);
                    $stmt->execute();
                }
                echo '<script>window.location="subcat_vehiculo.php";</script>';
                exit;
            }
            // Eliminar subcategoría
            if (isset($_GET['delete'])) {
                try {
                    $subcat_id = $_GET['delete'];
                    
                    // PASO 1: Verificar si la subcategoría tiene vehículos asociados
                    $check_vehiculos_sql = "SELECT COUNT(*) as total FROM regis_vehic WHERE subcat_vehic_id = ?";
                    $check_vehiculos_stmt = $conn->prepare($check_vehiculos_sql);
                    $check_vehiculos_stmt->bind_param('i', $subcat_id);
                    $check_vehiculos_stmt->execute();
                    $check_result = $check_vehiculos_stmt->get_result();
                    $vehiculos_count = $check_result->fetch_assoc()['total'];
                    
                    // Si hay vehículos asociados, no permitir la eliminación
                    if ($vehiculos_count > 0) {
                        echo "<script>
                            alert('No se puede eliminar la subcategoría porque tiene $vehiculos_count vehículo(s) asociado(s).\\n\\nPrimero debe:\\n- Reasignar los vehículos a otra subcategoría, o\\n- Eliminar los vehículos individualmente desde el módulo de vehículos');
                            window.location.href = 'subcat_vehiculo.php';
                        </script>";
                        exit;
                    }
                    
                    // PASO 2: Eliminar directamente la subcategoría (ya verificamos que no tiene vehículos)
                    $sql = "DELETE FROM subcat_vehic WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('i', $subcat_id);
                    $stmt->execute();
                    
                    if ($stmt->affected_rows > 0) {
                        error_log("subcat_vehiculo.php: Subcategoría $subcat_id eliminada exitosamente (eliminación simple)");
                        echo '<script>alert("Subcategoría eliminada correctamente."); window.location="subcat_vehiculo.php";</script>';
                    } else {
                        error_log("subcat_vehiculo.php: No se pudo eliminar la subcategoría $subcat_id");
                        echo '<script>alert("Error: No se pudo eliminar la subcategoría."); window.location="subcat_vehiculo.php";</script>';
                    }
                    exit;
                    
                } catch (Exception $e) {
                    error_log("subcat_vehiculo.php: Error al eliminar subcategoría: " . $e->getMessage());
                    echo '<script>alert("Error al eliminar la subcategoría: ' . addslashes($e->getMessage()) . '"); window.location="subcat_vehiculo.php";</script>';
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
