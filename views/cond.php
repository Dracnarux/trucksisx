<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
$rol_tecnico = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'tecnico';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Conductores</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #f8fafc 0%, #e3e6ed 100%);
        }
        .container {
            background: rgba(13,110,253,0.10);
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10);
            padding: 32px 24px;
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
        .table-bordered, .table-responsive, .table th, .table td {
            color: #111 !important;
        }
        thead tr {
            background: rgba(13,110,253,0.10) !important;
            color: #111 !important;
            border-bottom: 2px solid rgba(13,110,253,0.15);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-0">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><i class="bi bi-truck"></i> Trucksisx</a>
            <span class="navbar-text">Gestión de Conductores</span>
        </div>
    </nav>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0"><i class="bi bi-person-badge"></i> Conductores</h2>
            <?php if (!$rol_conductor && !$rol_tecnico): ?>
            <a href="cond.php?form=1" class="btn btn-success"><i class="bi bi-plus-circle"></i> Agregar Conductor</a>
            <?php endif; ?>
        </div>

    <!-- Modernizado: Bootstrap 5.3.2, Inter, paleta azul/amarillo, tarjetas, botones y tablas modernas -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
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
        h2 { font-size: clamp(1.5rem, 3vw, 2rem); }
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem;
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
        .main-header h2 { color: #fff; margin-bottom: 0.5rem; position: relative; z-index: 2; }
        .main-header .lead { font-size: 1.1rem; opacity: 0.9; position: relative; z-index: 2; }
        .card {
            background: #FFFFFF;
            border: 1px solid rgba(209, 213, 219, 0.3);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        .card-header {
            background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%);
            border-bottom: 1px solid #D1D5DB;
            color: #1E3A8A;
            font-weight: 600;
            padding: 1.25rem;
        }
        .card-body { padding: 1.5rem; }
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
            z-index: 1;
        }
        .table tbody tr {
            background: #FFFFFF;
            border-bottom: 1px solid #E5E7EB;
            transition: background 0.2s;
        }
        .table tbody tr:hover {
            background: #F3F4F6;
        }
        .table td, .table th {
            vertical-align: middle;
            padding: 0.7rem 0.5rem;
        }
    </style>
        <?php
        require_once '../config/db.php';
        $conn = conectarDB();
        // Obtener vehículos para filtro y formulario
        $vehicQuery = $conn->query("SELECT id, placa, marca_vehiculo FROM regis_vehic ORDER BY placa ASC");
        $vehiculos = [];
        while ($vehic = $vehicQuery->fetch_assoc()) {
            $vehiculos[$vehic['id']] = $vehic['placa'] . ' (' . $vehic['marca_vehiculo'] . ')';
        }
        // Filtros
        $filtro_cargo = isset($_GET['filtro_cargo']) ? $_GET['filtro_cargo'] : '';
        $filtro_vehic = isset($_GET['filtro_vehic']) ? $_GET['filtro_vehic'] : '';
        $sql = "SELECT c.*, v.placa, v.marca_vehiculo FROM cond c JOIN regis_vehic v ON c.regis_vehic_id = v.id WHERE c.cargo LIKE ?";
        $params = ["%$filtro_cargo%"];
        $types = 's';
        if ($filtro_vehic) {
            $sql .= " AND v.id = ?";
            $params[] = $filtro_vehic;
            $types .= 'i';
        }
        $sql .= " ORDER BY c.id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $conductores = $stmt->get_result();
        ?>
        <form class="row mb-4" method="get">
            <div class="col-md-4">
                <input type="text" name="filtro_cargo" class="form-control" placeholder="Buscar por cargo" value="<?= htmlspecialchars($filtro_cargo) ?>">
            </div>
            <div class="col-md-4">
                <select name="filtro_vehic" class="form-select">
                    <option value="">Todos los vehículos</option>
                    <?php foreach ($vehiculos as $id => $desc): ?>
                        <option value="<?= $id ?>" <?= $filtro_vehic == $id ? 'selected' : '' ?>><?= htmlspecialchars($desc) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filtrar</button>
            </div>
            <div class="col-md-2">
                <a href="cond.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle"></i> Limpiar</a>
            </div>
        </form>
        <div class="card">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Cargo</th>
                            <th>Horas Trabajadas</th>
                            <th>Tareas Completadas</th>
                            <th>Eficiencia</th>
                            <th>Descripción</th>
                            <th>Vehículo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($conductores && $conductores->num_rows > 0): ?>
                        <?php while ($row = $conductores->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['cargo']) ?></td>
                            <td><?= htmlspecialchars($row['horas_trabajadas']) ?></td>
                            <td><?= htmlspecialchars($row['tareas_completadas']) ?></td>
                            <td><?= htmlspecialchars($row['efeciencia']) ?></td>
                            <td><?= htmlspecialchars($row['descripcion']) ?></td>
                            <td><?= htmlspecialchars($row['placa']) ?> (<?= htmlspecialchars($row['marca_vehiculo']) ?>)</td>
                            <td class="text-center">
                                <?php if (!$rol_conductor && !$rol_tecnico): ?>
                                <a href="cond.php?form=1&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm mx-1"><i class="bi bi-pencil-square"></i> Editar</a>
                                <button type="button" class="btn btn-danger btn-sm mx-1" onclick="confirmarEliminacionAvanzada(<?= $row['id'] ?>, 'Conductor ID <?= $row['id'] ?> (<?= addslashes($row['cargo'] ?? 'Sin cargo') ?>)')"><i class="bi bi-trash"></i> Eliminar</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No hay conductores registrados.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        // Formulario alta/edición
    if (isset($_GET['form']) && !$rol_conductor && !$rol_tecnico):
            $editData = [];
            if (isset($_GET['id'])) {
                $sql = "SELECT * FROM cond WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $_GET['id']);
                $stmt->execute();
                $editData = $stmt->get_result()->fetch_assoc();
            }
        ?>
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-pencil-square"></i> <?= isset($editData['id']) ? 'Editar' : 'Agregar' ?> Conductor</h5>
                <form method="post" action="cond.php">
                    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nombre (debe coincidir con el usuario)</label>
                            <input type="text" name="cargo" class="form-control" required value="<?= htmlspecialchars($editData['cargo'] ?? '') ?>">
                            <div class="form-text text-danger">Debe ser igual al nombre del usuario registrado en el sistema para que se relacione correctamente.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Horas Trabajadas</label>
                            <input type="number" name="horas_trabajadas" class="form-control" required value="<?= htmlspecialchars($editData['horas_trabajadas'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tareas Completadas</label>
                            <input type="number" name="tareas_completadas" class="form-control" required value="<?= htmlspecialchars($editData['tareas_completadas'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Eficiencia</label>
                            <input type="number" step="0.01" name="efeciencia" class="form-control" required value="<?= htmlspecialchars($editData['efeciencia'] ?? '') ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2"><?= htmlspecialchars($editData['descripcion'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Vehículo Registrado</label>
                            <select name="regis_vehic_id" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($vehiculos as $id => $desc): ?>
                                    <option value="<?= $id ?>" <?= (isset($editData['regis_vehic_id']) && $editData['regis_vehic_id'] == $id) ? 'selected' : '' ?>><?= htmlspecialchars($desc) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success mt-3"><i class="bi bi-check-lg"></i> Guardar</button>
                    <a href="cond.php" class="btn btn-secondary mx-2 mt-3"><i class="bi bi-x-lg"></i> Cancelar</a>
                </form>
            </div>
        </div>
        <?php endif; ?>
        <?php
        // Guardar/editar/eliminar conductor solo si no es conductor
    if (!$rol_conductor && !$rol_tecnico) {
            // Guardar/editar conductor
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cargo'], $_POST['horas_trabajadas'], $_POST['tareas_completadas'], $_POST['efeciencia'], $_POST['regis_vehic_id'])) {
                $fields = [
                    'cargo','horas_trabajadas','tareas_completadas','efeciencia','descripcion','regis_vehic_id'
                ];
                $values = [];
                foreach ($fields as $f) {
                    $values[] = $_POST[$f] ?? '';
                }
                if (!empty($_POST['id'])) {
                    $sql = "UPDATE cond SET cargo=?, horas_trabajadas=?, tareas_completadas=?, efeciencia=?, descripcion=?, regis_vehic_id=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $values_update = $values;
                    $values_update[] = $_POST['id'];
                    $stmt->bind_param('siidsii', ...$values_update);
                    $stmt->execute();
                    $conductor_id = $_POST['id'];
                } else {
                    $sql = "INSERT INTO cond (cargo, horas_trabajadas, tareas_completadas, efeciencia, descripcion, regis_vehic_id) VALUES (?,?,?,?,?,?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('siidsi', ...$values);
                    $stmt->execute();
                    $conductor_id = $conn->insert_id;
                }
                // Asignar automáticamente el conductor al vehículo seleccionado
                $vehiculo_id = intval($_POST['regis_vehic_id']);
                if ($vehiculo_id > 0) {
                    $sqlV = "UPDATE regis_vehic SET cond_id = ?, estado = 'Asignado' WHERE id = ?";
                    $stmtV = $conn->prepare($sqlV);
                    $stmtV->bind_param('ii', $conductor_id, $vehiculo_id);
                    $stmtV->execute();
                }
                echo '<script>window.location="cond.php";</script>';
                exit;
            }
            // Eliminar conductor
            if (isset($_GET['delete'])) {
                $conductor_id = (int)$_GET['delete'];
                
                try {
                    // Iniciar transacción para asegurar consistencia
                    $conn->begin_transaction();
                    
                    // Verificar si el conductor existe
                    $check_conductor_sql = "SELECT cargo FROM cond WHERE id = ?";
                    $check_conductor_stmt = $conn->prepare($check_conductor_sql);
                    $check_conductor_stmt->bind_param('i', $conductor_id);
                    $check_conductor_stmt->execute();
                    $conductor_result = $check_conductor_stmt->get_result();
                    
                    if ($conductor_result->num_rows === 0) {
                        throw new Exception("El conductor no existe.");
                    }
                    
                    $conductor_data = $conductor_result->fetch_assoc();
                    $nombre_conductor = "Conductor ID $conductor_id (" . ($conductor_data['cargo'] ?? 'Sin cargo') . ")";
                    
                    // Verificar si el conductor está asignado a algún vehículo
                    $check_vehiculos_sql = "SELECT COUNT(*) as total, GROUP_CONCAT(placa) as placas FROM regis_vehic WHERE cond_id = ?";
                    $check_vehiculos_stmt = $conn->prepare($check_vehiculos_sql);
                    $check_vehiculos_stmt->bind_param('i', $conductor_id);
                    $check_vehiculos_stmt->execute();
                    $vehiculos_result = $check_vehiculos_stmt->get_result();
                    $vehiculos_data = $vehiculos_result->fetch_assoc();
                    
                    $mensaje = "Conductor '$nombre_conductor' eliminado correctamente.";
                    
                    // Verificar si el conductor tiene alertas asociadas
                    $check_alertas_sql = "SELECT COUNT(*) as total FROM alert WHERE cond_id = ?";
                    $check_alertas_stmt = $conn->prepare($check_alertas_sql);
                    $check_alertas_stmt->bind_param('i', $conductor_id);
                    $check_alertas_stmt->execute();
                    $alertas_result = $check_alertas_stmt->get_result();
                    $alertas_data = $alertas_result->fetch_assoc();
                    
                    if ($alertas_data['total'] > 0) {
                        // Si el conductor tiene alertas, primero desvincular
                        $update_alertas_sql = "UPDATE alert SET cond_id = NULL WHERE cond_id = ?";
                        $update_alertas_stmt = $conn->prepare($update_alertas_sql);
                        $update_alertas_stmt->bind_param('i', $conductor_id);
                        
                        if (!$update_alertas_stmt->execute()) {
                            throw new Exception("Error al desvincular alertas del conductor.");
                        }
                        
                        $mensaje .= " Se desvincularon {$alertas_data['total']} alerta(s).";
                    }
                    
                    // Verificar si el conductor tiene órdenes de trabajo asociadas
                    $check_ordenes_sql = "SELECT COUNT(*) as total FROM ord_trabj WHERE cond_id = ?";
                    $check_ordenes_stmt = $conn->prepare($check_ordenes_sql);
                    $check_ordenes_stmt->bind_param('i', $conductor_id);
                    $check_ordenes_stmt->execute();
                    $ordenes_result = $check_ordenes_stmt->get_result();
                    $ordenes_data = $ordenes_result->fetch_assoc();
                    
                    if ($ordenes_data['total'] > 0) {
                        // Si el conductor tiene órdenes de trabajo, primero desvincular
                        $update_ordenes_sql = "UPDATE ord_trabj SET cond_id = NULL WHERE cond_id = ?";
                        $update_ordenes_stmt = $conn->prepare($update_ordenes_sql);
                        $update_ordenes_stmt->bind_param('i', $conductor_id);
                        
                        if (!$update_ordenes_stmt->execute()) {
                            throw new Exception("Error al desvincular órdenes de trabajo del conductor.");
                        }
                        
                        $mensaje .= " Se desvincularon {$ordenes_data['total']} orden(es) de trabajo.";
                    }
                    
                    if ($vehiculos_data['total'] > 0) {
                        // Si el conductor está asignado a vehículos, primero desvincular
                        $update_vehiculos_sql = "UPDATE regis_vehic SET cond_id = NULL, estado = 'Sin conductor' WHERE cond_id = ?";
                        $update_vehiculos_stmt = $conn->prepare($update_vehiculos_sql);
                        $update_vehiculos_stmt->bind_param('i', $conductor_id);
                        
                        if (!$update_vehiculos_stmt->execute()) {
                            throw new Exception("Error al desvincular vehículos del conductor.");
                        }
                        
                        $mensaje .= " Los vehículos ({$vehiculos_data['placas']}) han sido desvinculados.";
                    }
                    
                    // Ahora eliminar el conductor
                    $delete_sql = "DELETE FROM cond WHERE id = ?";
                    $delete_stmt = $conn->prepare($delete_sql);
                    $delete_stmt->bind_param('i', $conductor_id);
                    
                    if (!$delete_stmt->execute()) {
                        throw new Exception("Error al eliminar el conductor de la base de datos.");
                    }
                    
                    // Confirmar transacción
                    $conn->commit();
                    
                    echo '<script>alert("' . addslashes($mensaje) . '"); window.location="cond.php";</script>';
                    
                } catch (Exception $e) {
                    // Revertir transacción en caso de error
                    $conn->rollback();
                    echo '<script>alert("Error: ' . addslashes($e->getMessage()) . '"); window.location="cond.php";</script>';
                }
                exit;
            }
        }
        
        // AJAX para verificar dependencias del conductor
        if (isset($_GET['ajax']) && $_GET['ajax'] === 'check_dependencies' && isset($_GET['cond_id'])) {
            header('Content-Type: application/json');
            $conductor_id = (int)$_GET['cond_id'];
            
            try {
                // Verificar vehículos
                $vehiculos_sql = "SELECT COUNT(*) as total, GROUP_CONCAT(placa) as placas FROM regis_vehic WHERE cond_id = ?";
                $vehiculos_stmt = $conn->prepare($vehiculos_sql);
                $vehiculos_stmt->bind_param('i', $conductor_id);
                $vehiculos_stmt->execute();
                $vehiculos_result = $vehiculos_stmt->get_result();
                $vehiculos_data = $vehiculos_result->fetch_assoc();
                
                // Verificar alertas
                $alertas_sql = "SELECT COUNT(*) as total FROM alert WHERE cond_id = ?";
                $alertas_stmt = $conn->prepare($alertas_sql);
                $alertas_stmt->bind_param('i', $conductor_id);
                $alertas_stmt->execute();
                $alertas_result = $alertas_stmt->get_result();
                $alertas_data = $alertas_result->fetch_assoc();
                
                // Verificar órdenes de trabajo
                $ordenes_sql = "SELECT COUNT(*) as total FROM ord_trabj WHERE cond_id = ?";
                $ordenes_stmt = $conn->prepare($ordenes_sql);
                $ordenes_stmt->bind_param('i', $conductor_id);
                $ordenes_stmt->execute();
                $ordenes_result = $ordenes_stmt->get_result();
                $ordenes_data = $ordenes_result->fetch_assoc();
                
                echo json_encode([
                    'vehiculos' => [
                        'total' => $vehiculos_data['total'],
                        'placas' => $vehiculos_data['placas']
                    ],
                    'alertas' => [
                        'total' => $alertas_data['total']
                    ],
                    'ordenes' => [
                        'total' => $ordenes_data['total']
                    ]
                ]);
                
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            exit;
        }
        ?>
        <div class="mt-5 text-end">
            <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver al Dashboard</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función mejorada para confirmar eliminación de conductor con verificación AJAX
        function confirmarEliminacionAvanzada(conductorId, nombreConductor) {
            // Mostrar mensaje de carga
            const loadingMsg = "Verificando dependencias del conductor...";
            
            // Hacer petición AJAX para verificar dependencias
            fetch(`cond.php?ajax=check_dependencies&cond_id=${conductorId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(`Error al verificar dependencias: ${data.error}`);
                        return;
                    }
                    
                    let mensaje = `¿Está seguro de eliminar al conductor "${nombreConductor}"?`;
                    let advertencias = [];
                    
                    // Verificar vehículos
                    if (data.vehiculos.total > 0) {
                        advertencias.push(`• ${data.vehiculos.total} vehículo(s): ${data.vehiculos.placas}`);
                    }
                    
                    // Verificar alertas
                    if (data.alertas.total > 0) {
                        advertencias.push(`• ${data.alertas.total} alerta(s) activa(s)`);
                    }
                    
                    // Verificar órdenes de trabajo
                    if (data.ordenes.total > 0) {
                        advertencias.push(`• ${data.ordenes.total} orden(es) de trabajo`);
                    }
                    
                    if (advertencias.length > 0) {
                        mensaje += `\n\n⚠️ ADVERTENCIA: Este conductor tiene los siguientes elementos asociados que serán desvinculados:\n\n`;
                        mensaje += advertencias.join('\n');
                        mensaje += `\n\nTodos estos elementos quedarán disponibles para ser reasignados a otros conductores.`;
                    } else {
                        mensaje += `\n\n✅ Este conductor no tiene elementos asociados, se puede eliminar sin afectar otros registros.`;
                    }
                    
                    mensaje += `\n\n¿Desea continuar con la eliminación?`;
                    
                    if (confirm(mensaje)) {
                        window.location.href = `cond.php?delete=${conductorId}`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Fallback a confirmación simple
                    if (confirm(`¿Está seguro de eliminar al conductor "${nombreConductor}"?\n\nNota: Si tiene elementos asociados, serán desvinculados automáticamente.`)) {
                        window.location.href = `cond.php?delete=${conductorId}`;
                    }
                });
        }
        
        // Función de respaldo para casos simples
        function confirmarEliminacion(conductorId, nombreConductor, vehiculoInfo) {
            let mensaje = `¿Está seguro de eliminar al conductor "${nombreConductor}"?`;
            
            if (vehiculoInfo && vehiculoInfo.trim() !== '' && vehiculoInfo !== '(Sin vehículo)') {
                mensaje += `\n\nAdvertencia: Este conductor tiene asignado el vehículo: ${vehiculoInfo}`;
                mensaje += `\nEl vehículo será desvinculado automáticamente y quedará disponible para otros conductores.`;
            } else {
                mensaje += `\n\nEste conductor no tiene vehículos asignados.`;
            }
            
            mensaje += `\n\nNota: Si este conductor tiene alertas o reportes asociados, también serán desvinculados automáticamente.`;
            mensaje += `\n\n¿Desea continuar con la eliminación?`;
            
            return confirm(mensaje);
        }
    </script>
</body>
</html>
