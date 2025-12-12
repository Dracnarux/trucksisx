<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
$rol_tecnico = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'tecnico';

// AJAX: Obtener datos de conductor para editar
if (!$rol_conductor && isset($_GET['ajax']) && $_GET['ajax'] === 'get' && isset($_GET['id'])) {
    require_once '../config/db.php';
    $conn = conectarDB();
    
    $sql = "SELECT * FROM cond WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit();
}

// AJAX: Guardar o editar conductor
if (!$rol_conductor && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    require_once '../config/db.php';
    $conn = conectarDB();
    
    $fields = ['cargo','horas_trabajadas','tareas_completadas','efeciencia','descripcion','regis_vehic_id'];
    $values = [];
    foreach ($fields as $f) {
        $values[] = $_POST[$f] ?? '';
    }
    
    if (!empty($_POST['id'])) {
        $sql = "UPDATE cond SET cargo=?, horas_trabajadas=?, tareas_completadas=?, efeciencia=?, descripcion=?, regis_vehic_id=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $values_update = $values;
        $values_update[] = $_POST['id'];
        $stmt->bind_param('sssssii', ...$values_update);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Conductor actualizado correctamente']);
    } else {
        $sql = "INSERT INTO cond (cargo, horas_trabajadas, tareas_completadas, efeciencia, descripcion, regis_vehic_id) VALUES (?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssi', ...$values);
        $stmt->execute();
        $nuevo_conductor_id = $conn->insert_id;
        $conductor_cargo = $_POST['cargo'];
        $vehiculo_asignado = !empty($_POST['regis_vehic_id']) && $_POST['regis_vehic_id'] != '';
        
        // Si se asignó un vehículo, actualizar regis_vehic.cond_id y estado
        if ($vehiculo_asignado) {
            $vehiculo_id = intval($_POST['regis_vehic_id']);
            $sql_vehic = "UPDATE regis_vehic SET cond_id = ?, estado = 'Asignado' WHERE id = ?";
            $stmt_vehic = $conn->prepare($sql_vehic);
            $stmt_vehic->bind_param('ii', $nuevo_conductor_id, $vehiculo_id);
            $stmt_vehic->execute();
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Conductor registrado correctamente', 
            'conductor_id' => $nuevo_conductor_id, 
            'conductor_cargo' => $conductor_cargo,
            'tiene_vehiculo' => $vehiculo_asignado
        ]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Conductores</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0F172A;
            --bg-secondary: #1E293B;
            --card-bg: #1E293B;
            --text-primary: #F1F5F9;
            --text-secondary: #94A3B8;
            --border: #334155;
            --accent: #F97316;
            --accent-amber: #F59E0B;
            --danger: #EF4444;
            --success: #10B981;
            --card-radius: 12px;
        }

        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            min-height: 100vh;
        }
        .container-fluid {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Sidebar visual igual al dashboard */
        .sidebar {
            background: var(--card-bg);
            color: var(--text-primary);
            border-right: 1px solid var(--border);
            box-shadow: 4px 0 20px rgba(2,6,23,0.3);
            height: 100vh;
            min-width: 220px;
            max-width: 340px;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 1050;
            border-radius: 0 1rem 1rem 0;
            transform: translateX(-100%);
            transition: transform 0.25s;
        }

        .sidebar.show-mobile {
            transform: translateX(0) !important;
        }

        .sidebar .nav-link {
            color: var(--text-primary) !important;
            font-weight: 500;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            background: rgba(249,115,22,0.1);
            color: var(--accent) !important;
        }

        .sidebar .nav-link.active, .sidebar .nav-link.bg-primary, .sidebar .nav-link.text-white {
            background: var(--accent);
            color: #FFFFFF !important;
            font-weight: 600;
        }

        .sidebar .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
        }

        .sidebar .btn-outline-secondary {
            color: var(--text-primary);
            border-color: var(--text-secondary);
        }

        .sidebar .btn-outline-secondary:hover {
            background: var(--text-secondary);
            color: var(--bg-primary);
        }

        .sidebar .nav-link.text-danger {
            color: var(--danger) !important;
        }

        .sidebar .nav-link.text-danger:hover {
            background: rgba(239,68,68,0.1);
            color: var(--danger) !important;
        }

        .sidebar h5 {
            color: var(--text-primary);
            font-weight: 700;
        }

        @media (max-width: 1024px) {
            .sidebar {
                position: fixed !important;
                top: 0; left: 0; bottom: 0;
                width: 85vw;
                max-width: 340px;
                height: 100vh;
                z-index: 1050;
                border-radius: 0 1rem 1rem 0;
                box-shadow: 0 8px 32px rgba(0,0,0,0.4);
                transform: translateX(-100%);
                transition: transform 0.25s;
            }
            .sidebar.show-mobile {
                transform: translateX(0);
            }
        }
        .main-header {
            background: linear-gradient(135deg, rgba(15,23,42,0.9) 0%, rgba(17,24,39,0.85) 100%);
            border-radius: var(--card-radius);
            box-shadow: 0 18px 50px rgba(2,6,23,0.6);
            color: var(--text-primary);
            margin-bottom: 2rem;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .main-header h1 {
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .main-header .lead {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--card-radius);
            box-shadow: 0 8px 32px rgba(2,6,23,0.3);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 12px 48px rgba(2,6,23,0.4);
            transform: translateY(-2px);
        }

        .card-header {
            background: linear-gradient(135deg, rgba(30,41,59,0.9) 0%, rgba(51,65,85,0.8) 100%);
            border-bottom: 1px solid var(--border);
            color: var(--text-primary);
            font-weight: 600;
            padding: 1.25rem;
        }

        .card-body {
            padding: 1.5rem;
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
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.3);
            outline: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent) 0%, #EA580C 100%);
            box-shadow: 0 4px 16px rgba(249,115,22,0.3);
            color: #FFFFFF !important;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #EA580C 0%, #DC2626 100%);
            box-shadow: 0 6px 24px rgba(249,115,22,0.4);
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }

        .btn-outline-primary {
            background: transparent;
            border: 2px solid var(--accent);
            color: var(--accent) !important;
        }

        .btn-outline-primary:hover {
            background: var(--accent);
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: #FFFFFF !important;
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--accent-amber) 0%, #D97706 100%);
            color: #FFFFFF !important;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #DC2626 100%);
            color: #FFFFFF !important;
        }

        .btn-info {
            background: linear-gradient(135deg, #6B7280 0%, #4B5563 100%);
            color: #FFFFFF !important;
        }

        .btn:hover {
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .form-control, .form-select {
            background: #FFFFFF;
            border: 2px solid #D1D5DB;
            border-radius: 8px;
            color: #000000;
            font-size: 1rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
            outline: none;
        }

        .form-control::placeholder, .form-select::placeholder {
            color: #6B7280;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            color: #000000;
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .table {
            background: var(--card-bg);
            border-collapse: collapse;
            border-radius: var(--card-radius);
            box-shadow: 0 8px 32px rgba(2,6,23,0.3);
            margin-bottom: 2rem;
            overflow: hidden;
            width: 100%;
        }

        .table th {
            background: linear-gradient(135deg, rgba(30,41,59,0.9) 0%, rgba(51,65,85,0.8) 100%);
            border-bottom: 2px solid var(--border);
            color: var(--text-primary);
            font-weight: 600;
            padding: 1rem;
            text-align: left;
        }

        .table td {
            border-bottom: 1px solid var(--border);
            color: var(--text-secondary);
            padding: 1rem;
        }

        .table tbody tr:hover {
            background: rgba(249,115,22,0.05);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .badge {
            border-radius: 20px;
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            text-transform: uppercase;
        }

        .badge-success {
            background: var(--success);
            color: #FFFFFF;
        }

        .badge-warning {
            background: var(--accent-amber);
            color: #FFFFFF;
        }

        .badge-danger {
            background: var(--danger);
            color: #FFFFFF;
        }

        .badge-info {
            background: #6B7280;
            color: #FFFFFF;
        }

        .badge.bg-secondary {
            background: #6B7280 !important;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1060;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(4px);
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-dialog-custom {
            background: #FFFFFF;
            border-radius: var(--card-radius);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.9) translateY(-20px);
            transition: all 0.3s ease;
            border: 1px solid var(--border);
            color: #000000;
        }

        .modal-overlay.active .modal-dialog-custom {
            transform: scale(1) translateY(0);
        }

        .modal-header-custom {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #F9FAFB;
        }

        .modal-header-custom h5 {
            margin: 0;
            color: #000000;
            font-weight: 600;
            font-size: 1.25rem;
        }

        .modal-close-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
        }

        .modal-close-btn:hover {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .modal-body-custom {
            padding: 1.5rem;
            color: #000000;
        }

        .modal-body-custom .text-muted {
            color: #6B7280 !important;
        }

        .modal-footer-custom {
            padding: 1rem 1.5rem 1.5rem;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            background: #F9FAFB;
        }

        .modal-active {
            overflow: hidden;
        }

        .alert {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .alert-info {
            background: rgba(249, 115, 22, 0.1);
            color: var(--accent);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            .main-header {
                padding: 1.5rem;
                text-align: center;
            }
            .card-body {
                padding: 1rem;
            }
            .modal-dialog-custom {
                width: 95%;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <?php
        require_once '../config/db.php';
        $conn = conectarDB();
        // Obtener vehículos para filtro y formulario
        $vehicQuery = $conn->query("SELECT id, placa, marca_vehiculo FROM regis_vehic ORDER BY placa ASC");
        $vehiculos = [];
        while ($vehic = $vehicQuery->fetch_assoc()) {
            $vehiculos[$vehic['id']] = $vehic['placa'] . ' (' . $vehic['marca_vehiculo'] . ')';
        }
        
        // Obtener vehículos sin conductor asignado para el modal de asignación
        $vehicDisponiblesQuery = $conn->query("SELECT id, placa, marca_vehiculo FROM regis_vehic WHERE cond_id IS NULL ORDER BY placa ASC");
        $vehiculosDisponibles = [];
        while ($vd = $vehicDisponiblesQuery->fetch_assoc()) {
            $vehiculosDisponibles[] = $vd;
        }
        // Filtros
        $filtro_conductor = isset($_GET['filtro_conductor']) ? $_GET['filtro_conductor'] : '';
        $filtro_vehic = isset($_GET['filtro_vehic']) ? $_GET['filtro_vehic'] : '';
        
        // Si es conductor, solo mostrar su propia información
        if ($rol_conductor) {
            $nombre_usuario = $_SESSION['usuario']['nombre'];
            $sql = "SELECT c.*, v.placa, v.marca_vehiculo FROM cond c JOIN regis_vehic v ON c.regis_vehic_id = v.id WHERE c.cargo = ?";
            $params = [$nombre_usuario];
            $types = 's';
        } else {
            $sql = "SELECT c.*, v.placa, v.marca_vehiculo FROM cond c JOIN regis_vehic v ON c.regis_vehic_id = v.id WHERE c.cargo LIKE ?";
            $params = ["%$filtro_cargo%"];
            $types = 's';
            if ($filtro_vehic) {
                $sql .= " AND v.id = ?";
                $params[] = $filtro_vehic;
                $types .= 'i';
            }
        }
        $sql .= " ORDER BY c.id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $conductores = $stmt->get_result();
        ?>
        
        <div class="main-header">
            <h1><i class="bi bi-person-badge"></i> <?= $rol_conductor ? 'Mi Perfil de Conductor' : 'Conductores' ?></h1>
            <p class="lead">Gestión de conductores y asignación de vehículos</p>
        </div>
        
        <?php if ($rol_conductor): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> <strong>Vista de Conductor:</strong> Aquí puedes consultar y editar tu información personal.
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul"></i> Lista de Conductores</span>
                <?php if (!$rol_conductor && !$rol_tecnico): ?>
                <button type="button" class="btn btn-success btn-sm" onclick="openModal()"><i class="bi bi-plus-circle"></i> Agregar Conductor</button>
                <?php endif; ?>
            </div>
            
        <?php if (!$rol_conductor): ?>
            <div class="card-body">
                <form class="row" method="get" style="gap: 1rem;">
            <div class="col-md-4">
                <input type="text" name="filtro_conductor" class="form-control" placeholder="Buscar por conductor" value="<?= htmlspecialchars($filtro_conductor) ?>">
            </div>
            <div class="col-md-4">
                <select name="filtro_vehic" class="form-select">
                    <option value="">Todas las marcas</option>
                    <?php foreach ($vehiculos as $id => $desc): ?>
                        <option value="<?= $id ?>" <?= $filtro_vehic == $id ? 'selected' : '' ?>><?= htmlspecialchars($desc) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="bi bi-funnel"></i> Filtrar</button>
            </div>
            <div class="col-md-2">
                <a href="cond.php" class="btn btn-outline-primary" style="width: 100%;"><i class="bi bi-arrow-clockwise"></i> Limpiar</a>
            </div>
                </form>
            </div>
        <?php endif; ?>
        
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
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
                                    <?php if ($rol_conductor): ?>
                                    <a href="cond.php?form=1&id=<?= $row['id'] ?>" class="btn btn-primary btn-sm mx-1"><i class="bi bi-pencil-square"></i> Editar Mi Perfil</a>
                                    <?php elseif (!$rol_tecnico): ?>
                                    <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap">
                                        <button type="button" class="btn btn-info btn-sm" onclick="verConductor(<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>)"><i class="bi bi-eye"></i> Ver</button>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="editConductor(<?= $row['id'] ?>)"><i class="bi bi-pencil-square"></i> Editar</button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmarEliminacionAvanzada(<?= $row['id'] ?>, 'Conductor ID <?= $row['id'] ?> (<?= addslashes($row['cargo'] ?? 'Sin cargo') ?>)')"><i class="bi bi-trash"></i> Eliminar</button>
                                    </div>
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
        
        <!-- Modal para Agregar/Editar Conductor -->
        <div class="modal-overlay" id="conductorModal">
            <div class="modal-dialog-custom">
                <div class="modal-header-custom">
                    <h5><i class="bi bi-person-badge"></i> <span id="modalTitle">Agregar Conductor</span></h5>
                    <button type="button" class="modal-close-btn" onclick="closeModal()">&times;</button>
                </div>
                <form id="conductorForm">
                    <input type="hidden" id="conductor_id" name="id">
                    <input type="hidden" name="ajax" value="1">
                    <div class="modal-body-custom">
                        <div class="row" style="gap: 1rem;">
                            <div class="col-md-6" style="position: relative;">
                                <label for="conductor_search" class="form-label">Nombre del Conductor *</label>
                                <input type="text" id="conductor_search" class="form-control" placeholder="Buscar conductor..." autocomplete="off">
                                <input type="hidden" id="cargo" name="cargo" required>
                                <select id="conductor_dropdown" class="form-select" size="5" style="display:none; position:absolute; z-index:1000; width:100%; max-height:200px; overflow-y:auto;">
                                    <?php
                                    require_once '../models/User.php';
                                    $userModel = new User();
                                    $conductoresUsuarios = $userModel->getConductores();
                                    foreach ($conductoresUsuarios as $conductor) {
                                        $nombreCompleto = htmlspecialchars($conductor['nombre'] . ' ' . $conductor['apellido']);
                                        echo '<option value="' . htmlspecialchars($conductor['nombre']) . '" data-fullname="' . $nombreCompleto . '">' . $nombreCompleto . '</option>';
                                    }
                                    ?>
                                </select>
                                <small class="text-muted">Debe coincidir con el usuario registrado</small>
                            </div>
                            <div class="col-md-6" style="position: relative;">
                                <label for="vehiculo_search" class="form-label">Vehículo Asignado (Opcional)</label>
                                <input type="text" id="vehiculo_search" class="form-control" placeholder="Buscar vehículo..." autocomplete="off">
                                <input type="hidden" id="regis_vehic_id" name="regis_vehic_id">
                                <select id="vehiculo_dropdown" class="form-select" size="5" style="display:none; position:absolute; z-index:1000; width:100%; max-height:200px; overflow-y:auto;">
                                    <option value="">Sin asignar</option>
                                    <?php foreach ($vehiculosDisponibles as $vd): ?>
                                        <option value="<?= $vd['id'] ?>" data-desc="<?= htmlspecialchars($vd['placa'] . ' - ' . $vd['marca_vehiculo']) ?>"><?= htmlspecialchars($vd['placa'] . ' - ' . $vd['marca_vehiculo']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Solo vehículos sin conductor asignado</small>
                            </div>
                            <div class="col-md-4">
                                <label for="horas_trabajadas" class="form-label">Horas Trabajadas *</label>
                                <input type="number" id="horas_trabajadas" name="horas_trabajadas" class="form-control" required min="0">
                            </div>
                            <div class="col-md-4">
                                <label for="tareas_completadas" class="form-label">Tareas Completadas *</label>
                                <input type="number" id="tareas_completadas" name="tareas_completadas" class="form-control" required min="0">
                            </div>
                            <div class="col-md-4">
                                <label for="efeciencia" class="form-label">Eficiencia (%) *</label>
                                <input type="number" id="efeciencia" name="efeciencia" class="form-control" required min="0" max="100" step="0.01">
                            </div>
                            <div class="col-12">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea id="descripcion" name="descripcion" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer-custom">
                        <button type="button" class="btn btn-outline-primary" onclick="closeModal()">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="saveBtn">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php
        // Eliminar conductor solo si no es conductor
        if (!$rol_conductor && !$rol_tecnico) {
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
                    
                    // PASO 1: Verificar alertas pendientes (NO resueltas)
                    $check_alertas_pendientes_sql = "SELECT COUNT(*) as total FROM alert 
                                                     WHERE cond_id = ? 
                                                     AND estado IN ('activa', 'en_proceso', 'cancelada')";
                    $check_alertas_pendientes_stmt = $conn->prepare($check_alertas_pendientes_sql);
                    $check_alertas_pendientes_stmt->bind_param('i', $conductor_id);
                    $check_alertas_pendientes_stmt->execute();
                    $alertas_pendientes_result = $check_alertas_pendientes_stmt->get_result();
                    $alertas_pendientes_data = $alertas_pendientes_result->fetch_assoc();
                    
                    // PASO 2: Verificar si el conductor está asignado a algún vehículo
                    $check_vehiculos_sql = "SELECT COUNT(*) as total, GROUP_CONCAT(placa) as placas FROM regis_vehic WHERE cond_id = ?";
                    $check_vehiculos_stmt = $conn->prepare($check_vehiculos_sql);
                    $check_vehiculos_stmt->bind_param('i', $conductor_id);
                    $check_vehiculos_stmt->execute();
                    $vehiculos_result = $check_vehiculos_stmt->get_result();
                    $vehiculos_data = $vehiculos_result->fetch_assoc();
                    
                    // PASO 3: Si hay alertas pendientes o vehículos asignados, BLOQUEAR la eliminación
                    $dependencias = [];
                    if ($alertas_pendientes_data['total'] > 0) {
                        $dependencias[] = $alertas_pendientes_data['total'] . ' alerta(s) pendiente(s)';
                    }
                    if ($vehiculos_data['total'] > 0) {
                        $dependencias[] = $vehiculos_data['total'] . ' vehículo(s) asignado(s) (' . $vehiculos_data['placas'] . ')';
                    }
                    
                    // PASO 4: Verificar órdenes de trabajo vinculadas (cond_id no es NULL)
                    // Las órdenes desvinculadas (cond_id = NULL) no bloquean la eliminación
                    $check_ordenes_sql = "SELECT COUNT(*) as total FROM ord_trabj WHERE cond_id = ? AND cond_id IS NOT NULL";
                    $check_ordenes_stmt = $conn->prepare($check_ordenes_sql);
                    $check_ordenes_stmt->bind_param('i', $conductor_id);
                    $check_ordenes_stmt->execute();
                    $ordenes_result = $check_ordenes_stmt->get_result();
                    $ordenes_data = $ordenes_result->fetch_assoc();
                    
                    if ($ordenes_data['total'] > 0) {
                        $dependencias[] = $ordenes_data['total'] . ' orden(es) de trabajo vinculada(s)';
                    }
                    
                    // Si hay cualquier dependencia, BLOQUEAR la eliminación
                    if (count($dependencias) > 0) {
                        $conn->rollback();
                        $mensaje_dependencias = implode(' y ', $dependencias);
                        echo '<script>
                            alert("No se puede eliminar el conductor porque tiene: ' . $mensaje_dependencias . '\\n\\nPrimero debe:\\n- Resolver todas las alertas desde el módulo de alertas (esto desvinculará automáticamente las órdenes de trabajo)\\n- Desasignar el conductor de los vehículos\\n\\nDespués podrá eliminar el conductor de forma segura.");
                            window.location="cond.php";
                        </script>';
                        exit;
                    }
                    
                    // PASO 5: Desvincular alertas resueltas antes de eliminar
                    $unlink_alertas_sql = "UPDATE alert SET cond_id = NULL WHERE cond_id = ? AND estado = 'resuelta'";
                    $unlink_alertas_stmt = $conn->prepare($unlink_alertas_sql);
                    $unlink_alertas_stmt->bind_param('i', $conductor_id);
                    $unlink_alertas_stmt->execute();
                    
                    // PASO 6: Eliminar el conductor
                    $delete_sql = "DELETE FROM cond WHERE id = ?";
                    $delete_stmt = $conn->prepare($delete_sql);
                    $delete_stmt->bind_param('i', $conductor_id);
                    
                    if (!$delete_stmt->execute()) {
                        throw new Exception("Error al eliminar el conductor de la base de datos.");
                    }
                    
                    // Confirmar transacción
                    $conn->commit();
                    
                    echo '<script>alert("Conductor eliminado correctamente."); window.location="cond.php";</script>';
                    
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
            <a href="gestion_vehicular.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Volver a Gestión</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Modal functions
        function openModal() {
            document.getElementById('conductorModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Agregar Conductor';
            document.getElementById('conductorForm').reset();
            document.getElementById('conductor_id').value = '';
            document.getElementById('conductor_search').value = '';
            document.getElementById('conductor_dropdown').style.display = 'none';
            document.getElementById('vehiculo_search').value = '';
            document.getElementById('vehiculo_dropdown').style.display = 'none';
        }
        
        function closeModal() {
            document.getElementById('conductorModal').classList.remove('active');
        }
        
        function editConductor(id) {
            fetch(`cond.php?ajax=get&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('conductor_id').value = data.id;
                    document.getElementById('cargo').value = data.cargo;
                    
                    // Buscar el nombre completo del conductor en el dropdown
                    const conductorDropdown = document.getElementById('conductor_dropdown');
                    const allOptions = Array.from(conductorDropdown.options);
                    const matchingOption = allOptions.find(opt => opt.value === data.cargo);
                    if (matchingOption) {
                        document.getElementById('conductor_search').value = matchingOption.getAttribute('data-fullname');
                    }
                    
                    document.getElementById('horas_trabajadas').value = data.horas_trabajadas;
                    document.getElementById('tareas_completadas').value = data.tareas_completadas;
                    document.getElementById('efeciencia').value = data.efeciencia;
                    document.getElementById('descripcion').value = data.descripcion || '';
                    document.getElementById('regis_vehic_id').value = data.regis_vehic_id;
                    
                    // Buscar la descripción del vehículo en el dropdown
                    const vehiculoDropdown = document.getElementById('vehiculo_dropdown');
                    const allVehiculoOptions = Array.from(vehiculoDropdown.options);
                    const matchingVehiculo = allVehiculoOptions.find(opt => opt.value == data.regis_vehic_id);
                    if (matchingVehiculo) {
                        document.getElementById('vehiculo_search').value = matchingVehiculo.getAttribute('data-desc');
                    }
                    
                    document.getElementById('modalTitle').textContent = 'Editar Conductor';
                    document.getElementById('conductorModal').classList.add('active');
                })
                .catch(error => console.error('Error:', error));
        }
        
        // Form submit
        document.getElementById('conductorForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('cond.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                    closeModal();
                    // Recargar página con parámetro anti-caché
                    setTimeout(() => {
                        window.location.href = 'cond.php?t=' + new Date().getTime();
                    }, 1500);
                } else {
                    alert('Error: ' + (data.error || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar');
            });
        });
        
        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            toast.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        // Close modal on overlay click
        document.getElementById('conductorModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        
        // Filtro de búsqueda para conductores
        const conductorSearchInput = document.getElementById('conductor_search');
        const conductorDropdown = document.getElementById('conductor_dropdown');
        const conductorHiddenInput = document.getElementById('cargo');
        const allConductorOptions = Array.from(conductorDropdown.options);
        
        conductorSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            conductorDropdown.innerHTML = '';
            
            const filteredOptions = allConductorOptions.filter(option => 
                option.getAttribute('data-fullname').toLowerCase().includes(searchTerm)
            );
            
            filteredOptions.forEach(option => {
                conductorDropdown.appendChild(option.cloneNode(true));
            });
            
            if (searchTerm && filteredOptions.length > 0) {
                conductorDropdown.style.display = 'block';
            } else {
                conductorDropdown.style.display = 'none';
            }
        });
        
        conductorSearchInput.addEventListener('focus', function() {
            if (conductorDropdown.options.length > 0) {
                conductorDropdown.style.display = 'block';
            }
        });
        
        conductorDropdown.addEventListener('click', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption) {
                conductorHiddenInput.value = selectedOption.value;
                conductorSearchInput.value = selectedOption.getAttribute('data-fullname');
                this.style.display = 'none';
            }
        });
        
        document.addEventListener('click', function(e) {
            if (e.target !== conductorSearchInput && e.target !== conductorDropdown) {
                conductorDropdown.style.display = 'none';
            }
        });
        
        // Filtro de búsqueda para vehículos
        const vehiculoSearchInput = document.getElementById('vehiculo_search');
        const vehiculoDropdown = document.getElementById('vehiculo_dropdown');
        const vehiculoHiddenInput = document.getElementById('regis_vehic_id');
        const allVehiculoOptions = Array.from(vehiculoDropdown.options);
        
        vehiculoSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            vehiculoDropdown.innerHTML = '';
            
            const filteredOptions = allVehiculoOptions.filter(option => 
                option.getAttribute('data-desc').toLowerCase().includes(searchTerm)
            );
            
            filteredOptions.forEach(option => {
                vehiculoDropdown.appendChild(option.cloneNode(true));
            });
            
            if (searchTerm && filteredOptions.length > 0) {
                vehiculoDropdown.style.display = 'block';
            } else {
                vehiculoDropdown.style.display = 'none';
            }
        });
        
        vehiculoSearchInput.addEventListener('focus', function() {
            if (vehiculoDropdown.options.length > 0) {
                vehiculoDropdown.style.display = 'block';
            }
        });
        
        vehiculoDropdown.addEventListener('click', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption) {
                vehiculoHiddenInput.value = selectedOption.value;
                vehiculoSearchInput.value = selectedOption.getAttribute('data-desc');
                this.style.display = 'none';
            }
        });
        
        document.addEventListener('click', function(e) {
            if (e.target !== vehiculoSearchInput && e.target !== vehiculoDropdown) {
                vehiculoDropdown.style.display = 'none';
            }
        });
    </script>
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
        

        function verConductor(row) {
  document.getElementById('verc_id').textContent = row.id || '';
  document.getElementById('verc_cargo').textContent = row.cargo || '';
  document.getElementById('verc_horas').textContent = row.horas_trabajadas || '';
  document.getElementById('verc_tareas').textContent = row.tareas_completadas || '';
  document.getElementById('verc_eficiencia').textContent = row.efeciencia || '';
  document.getElementById('verc_descripcion').textContent = row.descripcion || '';
  document.getElementById('verc_vehiculo').textContent = row.placa ? (row.placa + ' (' + (row.marca_vehiculo || '') + ')') : '';
  
  const modal = document.getElementById('modalVerConductor');
  
  // Esperar un frame para asegurar que el DOM está listo
  requestAnimationFrame(() => {
    modal.style.display = 'block';
    modal.classList.add('show');
    modal.setAttribute('aria-modal', 'true');
    modal.removeAttribute('aria-hidden');
    document.body.classList.add('modal-open');
    
    // Crear backdrop
    if (!document.querySelector('.modal-backdrop')) {
      const backdrop = document.createElement('div');
      backdrop.className = 'modal-backdrop fade show';
      document.body.appendChild(backdrop);
    }
  });
}

function cerrarModalVer() {
  const modal = document.getElementById('modalVerConductor');
  modal.style.display = 'none';
  modal.classList.remove('show');
  modal.removeAttribute('aria-modal');
  document.body.classList.remove('modal-open');
  
  // Remover backdrop
  const backdrop = document.querySelector('.modal-backdrop');
  if (backdrop) {
    backdrop.remove();
  }
}
    </script>

    <!-- Modal Ver Conductor -->
<div class="modal fade" id="modalVerConductor" tabindex="-1" aria-labelledby="modalVerConductorLabel" style="display: none;">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #F97316; color: white;">
        <h5 class="modal-title" id="modalVerConductorLabel"><i class="bi bi-eye"></i> Detalles del Conductor</h5>
        <button type="button" class="btn-close btn-close-white" onclick="cerrarModalVer()" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" style="color: black;">
        <div class="row">
          <div class="col-md-6 mb-2"><strong>ID:</strong> <span id="verc_id"></span></div>
          <div class="col-md-6 mb-2"><strong>Cargo:</strong> <span id="verc_cargo"></span></div>
          <div class="col-md-6 mb-2"><strong>Horas Trabajadas:</strong> <span id="verc_horas"></span></div>
          <div class="col-md-6 mb-2"><strong>Tareas Completadas:</strong> <span id="verc_tareas"></span></div>
          <div class="col-md-6 mb-2"><strong>Eficiencia:</strong> <span id="verc_eficiencia"></span></div>
          <div class="col-md-6 mb-2"><strong>Descripción:</strong> <span id="verc_descripcion"></span></div>
          <div class="col-md-6 mb-2"><strong>Vehículo:</strong> <span id="verc_vehiculo"></span></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-primary" onclick="cerrarModalVer()"><i class="bi bi-x-circle"></i> Cerrar</button>
      </div>
    </div>
  </div>
</div>
</body>
</html>
