<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';

// AJAX: Obtener datos de vehículo para editar
if (!$rol_conductor && isset($_GET['ajax']) && $_GET['ajax'] === 'get' && isset($_GET['id'])) {
    require_once '../config/db.php';
    $conn = conectarDB();
    
    $sql = "SELECT rv.*, s.cat_vehic_id FROM regis_vehic rv LEFT JOIN subcat_vehic s ON rv.subcat_vehic_id = s.id WHERE rv.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit();
}

// AJAX: Guardar o editar vehículo
if (!$rol_conductor && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    require_once '../config/db.php';
    $conn = conectarDB();
    
    $fields = ['num_cha','placa','distru_ejes','marca_vehiculo','modelo','color','cilindraje','cap_carga','linea_marca','tecnomecanica','soat','tipo_unidad','tipo_combustible','RUNT','cert_homologacion','cert_matricula','tarje_propiedad','subcat_vehic_id'];
    $values = [];
    foreach ($fields as $f) {
        $values[] = $_POST[$f] ?? '';
    }
    
    if (!empty($_POST['id'])) {
        $sql = "UPDATE regis_vehic SET num_cha=?, placa=?, distru_ejes=?, marca_vehiculo=?, modelo=?, color=?, cilindraje=?, cap_carga=?, linea_marca=?, tecnomecanica=?, soat=?, tipo_unidad=?, tipo_combustible=?, RUNT=?, cert_homologacion=?, cert_matricula=?, tarje_propiedad=?, subcat_vehic_id=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $values_update = $values;
        $values_update[] = $_POST['id'];
        $stmt->bind_param('sssssssssssssssssi', ...$values_update);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Vehículo actualizado correctamente']);
    } else {
        $sql = "INSERT INTO regis_vehic (num_cha, placa, distru_ejes, marca_vehiculo, modelo, color, cilindraje, cap_carga, linea_marca, tecnomecanica, soat, tipo_unidad, tipo_combustible, RUNT, cert_homologacion, cert_matricula, tarje_propiedad, subcat_vehic_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssssssssssssi', ...$values);
        $stmt->execute();
        $nuevo_vehiculo_id = $conn->insert_id;
        $placa = $_POST['placa'];
        echo json_encode(['success' => true, 'message' => 'Vehículo registrado correctamente', 'vehiculo_id' => $nuevo_vehiculo_id, 'placa' => $placa]);
    }
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
            color: var(--text-secondary);
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
            background: var(--accent);
            color: #FFFFFF !important;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: #E65100;
            color: #FFFFFF !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
        }

        .btn-success {
            background: var(--success);
            color: #FFFFFF !important;
        }

        .btn-success:hover {
            background: #059669;
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }

        .btn-warning {
            background: var(--accent-amber);
            color: #FFFFFF !important;
        }

        .btn-warning:hover {
            background: #D97706;
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: var(--danger);
            color: #FFFFFF !important;
        }

        .btn-danger:hover {
            background: #DC2626;
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6B7280;
            color: #FFFFFF !important;
        }

        .btn-secondary:hover {
            background: #4B5563;
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }
        .form-control, .form-select {
            background: #FFFFFF;
            border: 2px solid #D1D5DB;
            border-radius: 8px;
            color: #000;
            font-size: 16px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
            outline: none;
        }

        .form-label {
            color: #000;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-section {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid var(--border);
            border-radius: var(--card-radius);
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-section:hover {
            box-shadow: 0 8px 32px rgba(249, 115, 22, 0.15);
            transform: translateY(-2px);
            border-color: var(--accent);
        }

        .form-section h6 {
            border-bottom: 2px solid var(--text-secondary);
            color: #000;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
        }
        .table-responsive {
            border-radius: var(--card-radius);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
            font-size: 14px;
        }

        .table thead th {
            background: var(--accent);
            border: none;
            color: #FFFFFF;
            font-weight: 700;
            padding: 0.75rem 0.5rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table tbody td {
            border-bottom: 1px solid var(--border);
            color: #000;
            padding: 0.75rem 0.5rem;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background: rgba(249, 115, 22, 0.05);
        }
        .table td.text-center .btn {
            font-size: 12px;
            padding: 0.3rem 0.6rem;
            margin: 0 2px;
        }
        .table td.text-center .btn-info {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: #fff !important;
        }
        .table td.text-center .btn-warning {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            color: #FFFFFF !important;
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
            background: var(--success) !important;
        }

        .badge.bg-warning {
            background: var(--accent-amber) !important;
            color: #FFFFFF !important;
        }

        .badge.bg-danger {
            background: var(--danger) !important;
        }

        .badge.bg-info {
            background: var(--text-secondary) !important;
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
            background: var(--card-bg);
            border-radius: var(--card-radius);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.9) translateY(-20px);
            transition: all 0.3s ease;
            border: 1px solid var(--border);
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
        }

        .modal-header-custom h5 {
            margin: 0;
            color: var(--text-primary);
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
        }

        .modal-footer-custom {
            padding: 1rem 1.5rem 1.5rem;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
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
            background: linear-gradient(135deg, #475569 0%, #334155 100%);
        }
        .text-corporate {
            color: #334155 !important;
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
            background: var(--card-bg);
            border-radius: var(--card-radius);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.9) translateY(-20px);
            transition: all 0.3s ease;
            border: 1px solid var(--border);
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
        }

        .modal-header-custom h5 {
            margin: 0;
            color: var(--text-primary);
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
        }

        .modal-footer-custom {
            padding: 1rem 1.5rem 1.5rem;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .modal-active {
            overflow: hidden;
        }

        /* Toast Notification */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--success);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: var(--card-radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 1070;
            transform: translateX(400px);
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .toast-notification.show {
            transform: translateX(0);
        }

        .toast-notification i {
            font-size: 1.5rem;
        }

        /* Form Validation */
        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: var(--danger);
        }
        .invalid-feedback {
            color: var(--danger);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Autocomplete Dropdown */
        .autocomplete-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #FFFFFF;
            border: 2px solid var(--accent);
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1050;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .autocomplete-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            color: #000;
            transition: background 0.2s;
        }
        .autocomplete-item:hover,
        .autocomplete-item.active {
            background: rgba(249, 115, 22, 0.1);
        }
        .autocomplete-item:last-child {
            border-radius: 0 0 8px 8px;
        }
        .autocomplete-no-results {
            padding: 0.75rem 1rem;
            color: #6B7280;
            font-style: italic;
        }

        /* Estilos para la modal de ver vehículo */
        #modalVerVehiculo .modal-content {
            background-color: #ffffff;
            color: #000000;
        }
        #modalVerVehiculo .modal-body {
            color: #000000;
        }
        #modalVerVehiculo .modal-body span {
            color: #000000;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="p-3">
            <h5 class="text-center mb-4">
                <i class="bi bi-truck text-accent"></i> TruckSISX
            </h5>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="gestion_vehicular.php">
                        <i class="bi bi-car-front"></i> Gestión Vehicular
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="regis_vehic.php">
                        <i class="bi bi-journal-plus"></i> Registro Vehículos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cat_vehiculo.php">
                        <i class="bi bi-tags"></i> Categorías
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="subcat_vehiculo.php">
                        <i class="bi bi-diagram-3"></i> Subcategorías
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="salida_vehiculo.php">
                        <i class="bi bi-arrow-right-circle"></i> Salidas Vehículos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="repue.php">
                        <i class="bi bi-gear"></i> Repuestos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="salida_repuesto.php">
                        <i class="bi bi-tools"></i> Salidas Repuestos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orden_trabajo.php">
                        <i class="bi bi-clipboard-check"></i> Órdenes Trabajo
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="proveedor.php">
                        <i class="bi bi-building"></i> Proveedores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reportes/index.php">
                        <i class="bi bi-graph-up"></i> Reportes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="truck_alerts.php">
                        <i class="bi bi-exclamation-triangle"></i> Alertas
                    </a>
                </li>
                <?php if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="crear_usuario.php">
                        <i class="bi bi-person-plus"></i> Crear Usuario
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="../logout.php">
                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Mobile menu button -->
        <button class="btn btn-outline-secondary d-md-none position-fixed" id="sidebarToggle" style="top: 1rem; left: 1rem; z-index: 1051;">
            <i class="bi bi-list"></i>
        </button>

        <div class="container-fluid">
            <div class="main-header">
                <h1><i class="bi bi-journal-plus"></i> Registro de Vehículos</h1>
                <p class="lead">Gestión completa del registro y administración de vehículos</p>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0"><i class="bi bi-journal-plus"></i> Registro de Vehículo</h2>
                <?php if (!$rol_conductor): ?>
                <button type="button" class="btn btn-success" onclick="openVehicleModal()"><i class="bi bi-plus-circle"></i> Agregar Vehículo</button>
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
        $subcatQuery = $conn->query("SELECT s.id, s.nombre, s.cat_vehic_id, c.nombre AS categoria FROM subcat_vehic s JOIN cat_vehic c ON s.cat_vehic_id = c.id ORDER BY s.nombre ASC");
        $subcategorias = [];
        while ($subcat = $subcatQuery->fetch_assoc()) {
            $subcategorias[$subcat['id']] = [
                'nombre' => $subcat['nombre'],
                'categoria' => $subcat['categoria'],
                'categoria_id' => $subcat['cat_vehic_id']
            ];
        }
        
        // Obtener conductores disponibles
        $condQuery = $conn->query("SELECT id, cargo FROM cond ORDER BY cargo ASC");
        $conductores = [];
        while ($cond = $condQuery->fetch_assoc()) {
            $conductores[] = $cond;
        }
        // Filtros
        $filtro_placa = isset($_GET['filtro_placa']) ? $_GET['filtro_placa'] : '';
        $filtro_marca = isset($_GET['filtro_marca']) ? $_GET['filtro_marca'] : '';
        $filtro_cat = isset($_GET['filtro_cat']) ? $_GET['filtro_cat'] : '';
        $filtro_subcat = isset($_GET['filtro_subcat']) ? $_GET['filtro_subcat'] : '';
        $sql = "SELECT v.*, s.nombre AS subcat_nombre, cat.nombre AS cat_nombre, conductor.cargo AS conductor_nombre 
                FROM regis_vehic v 
                JOIN subcat_vehic s ON v.subcat_vehic_id = s.id 
                JOIN cat_vehic cat ON s.cat_vehic_id = cat.id 
                LEFT JOIN cond conductor ON v.cond_id = conductor.id 
                WHERE v.placa LIKE ? AND v.marca_vehiculo LIKE ?";
        $params = ["%$filtro_placa%", "%$filtro_marca%"];
        $types = 'ss';
        if ($filtro_cat) {
            $sql .= " AND cat.id = ?";
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
                <div style="overflow-x:auto;">
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
                            <tr data-vehiculo-id="<?= $row['id'] ?>">
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
                                <td id="asignacion-<?= $row['id'] ?>">
                                    <?php
                                    if (!empty($row['cond_id']) && !empty($row['conductor_nombre'])) {
                                        echo '<span class="badge bg-success">' . htmlspecialchars($row['conductor_nombre']) . '</span>';
                                    } else {
                                        echo '<span class="badge bg-secondary">Sin asignar</span>';
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!$rol_conductor): ?>
                                    <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap">
                                        <button type="button" class="btn btn-info btn-sm" onclick="verVehiculo(<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>)"><i class="bi bi-eye"></i> Ver</button>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="editVehicle(<?= $row['id'] ?>)"><i class="bi bi-pencil-square"></i> Editar</button>
                                        <a href="regis_vehic.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar vehículo?')"><i class="bi bi-trash"></i> Eliminar</a>
                                    </div>
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
        </div>
        <?php
        if (!$rol_conductor) {
            // Asignar conductor a vehículo
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignar_conductor'], $_POST['vehiculo_id'], $_POST['conductor_id'])) {
                $vehiculo_id = intval($_POST['vehiculo_id']);
                $conductor_id = intval($_POST['conductor_id']);
                
                try {
                    // Verificar que el conductor existe
                    $check_cond = $conn->prepare("SELECT cargo FROM cond WHERE id = ?");
                    $check_cond->bind_param('i', $conductor_id);
                    $check_cond->execute();
                    $cond_result = $check_cond->get_result();
                    
                    if ($cond_result->num_rows === 0) {
                        echo '<script>alert("Error: El conductor seleccionado no existe."); window.location="regis_vehic.php";</script>';
                        exit;
                    }
                    
                    $conductor_data = $cond_result->fetch_assoc();
                    
                    // Verificar que el vehículo existe
                    $check_vehic = $conn->prepare("SELECT placa FROM regis_vehic WHERE id = ?");
                    $check_vehic->bind_param('i', $vehiculo_id);
                    $check_vehic->execute();
                    $vehic_result = $check_vehic->get_result();
                    
                    if ($vehic_result->num_rows === 0) {
                        echo '<script>alert("Error: El vehículo seleccionado no existe."); window.location="regis_vehic.php";</script>';
                        exit;
                    }
                    
                    $vehiculo_data = $vehic_result->fetch_assoc();
                    
                    // Actualizar cond_id y estado en el vehículo
                    $sql = "UPDATE regis_vehic SET cond_id = ?, estado = 'Asignado' WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ii', $conductor_id, $vehiculo_id);
                    
                    if ($stmt->execute()) {
                        // También actualizar regis_vehic_id en el conductor para mantener sincronización
                        $sql_cond = "UPDATE cond SET regis_vehic_id = ? WHERE id = ?";
                        $stmt_cond = $conn->prepare($sql_cond);
                        $stmt_cond->bind_param('ii', $vehiculo_id, $conductor_id);
                        $stmt_cond->execute();
                        
                        // Verificar que se actualizó correctamente
                        if ($stmt->affected_rows > 0) {
                            echo '<script>alert("Conductor ' . addslashes($conductor_data['cargo']) . ' asignado correctamente al vehículo ' . addslashes($vehiculo_data['placa']) . '"); window.location.href="regis_vehic.php?t=' . time() . '";</script>';
                        } else {
                            echo '<script>alert("No se realizó ningún cambio. El conductor puede ya estar asignado."); window.location.href="regis_vehic.php";</script>';
                        }
                    } else {
                        echo '<script>alert("Error al asignar conductor: ' . addslashes($conn->error) . '"); window.location.href="regis_vehic.php";</script>';
                    }
                } catch (Exception $e) {
                    echo '<script>alert("Error: ' . addslashes($e->getMessage()) . '"); window.location="regis_vehic.php";</script>';
                }
                exit;
            }
            // Eliminar vehículo
            if (isset($_GET['delete'])) {
                try {
                    $vehiculo_id = $_GET['delete'];
                    
                    // PASO 1: Verificar alertas pendientes (NO resueltas)
                    $check_alerts_sql = "SELECT COUNT(*) as total FROM alert 
                                        WHERE regis_vehic_id = ? 
                                        AND estado IN ('activa', 'en_proceso', 'cancelada')";
                    $check_alerts_stmt = $conn->prepare($check_alerts_sql);
                    $check_alerts_stmt->bind_param('i', $vehiculo_id);
                    $check_alerts_stmt->execute();
                    $alerts_pendientes = $check_alerts_stmt->get_result()->fetch_assoc()['total'];
                    
                    // Verificar conductor asignado
                    $check_conductor_sql = "SELECT cond_id FROM regis_vehic WHERE id = ?";
                    $check_conductor_stmt = $conn->prepare($check_conductor_sql);
                    $check_conductor_stmt->bind_param('i', $vehiculo_id);
                    $check_conductor_stmt->execute();
                    $conductor_id = $check_conductor_stmt->get_result()->fetch_assoc()['cond_id'];
                    
                    // Si hay alertas pendientes o conductor asignado, bloquear eliminación
                    $dependencias = [];
                    if ($alerts_pendientes > 0) {
                        $dependencias[] = "$alerts_pendientes alerta(s) pendiente(s)";
                    }
                    if ($conductor_id) {
                        $dependencias[] = "1 conductor asignado";
                    }
                    
                    if (count($dependencias) > 0) {
                        $mensaje_dependencias = implode(" y ", $dependencias);
                        echo "<script>
                            alert('No se puede eliminar el vehículo porque tiene: $mensaje_dependencias\\n\\nPrimero debe:\\n- Resolver todas las alertas desde el módulo de alertas (esto desvinculará automáticamente las órdenes de trabajo)\\n- Desasignar el conductor desde este mismo módulo\\n\\nDespués podrá eliminar el vehículo de forma segura.');
                            window.location.href = 'regis_vehic.php';
                        </script>";
                        exit;
                    }
                    
                    // PASO 2: Desvincular alertas resueltas (preservar historial)
                    $unlink_sql = "UPDATE alert SET regis_vehic_id = NULL 
                                   WHERE regis_vehic_id = ? AND estado = 'resuelta'";
                    $unlink_stmt = $conn->prepare($unlink_sql);
                    $unlink_stmt->bind_param('i', $vehiculo_id);
                    $unlink_stmt->execute();
                    
                    // PASO 3: Eliminar el vehículo
                    $sql = "DELETE FROM regis_vehic WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('i', $vehiculo_id);
                    $stmt->execute();
                    
                    if ($stmt->affected_rows > 0) {
                        error_log("regis_vehic.php: Vehículo $vehiculo_id eliminado exitosamente");
                        echo '<script>alert("Vehículo eliminado correctamente. Las alertas resueltas se mantienen en el historial."); window.location="regis_vehic.php";</script>';
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
            <a href="gestion_vehicular.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver a Gestión</a>
        </div>

        <!-- Modal Agregar/Editar Vehículo -->
        <div class="modal-overlay" id="vehicleModal">
            <div class="modal-dialog-custom">
                <div class="modal-header-custom">
                    <h5><i class="bi bi-truck"></i> <span id="modalTitle">Agregar Vehículo</span></h5>
                    <button type="button" class="modal-close-btn" onclick="closeVehicleModal()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <form id="vehicleForm">
                    <input type="hidden" id="vehicle_id" name="id">
                    <input type="hidden" name="ajax" value="1">
                    <div class="modal-body-custom">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Placa <span class="text-danger">*</span></label>
                                <input type="text" name="placa" id="placa" class="form-control" required>
                                <div class="invalid-feedback">La placa es requerida</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Marca <span class="text-danger">*</span></label>
                                <input type="text" name="marca_vehiculo" id="marca_vehiculo" class="form-control" required>
                                <div class="invalid-feedback">La marca es requerida</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Modelo <span class="text-danger">*</span></label>
                                <input type="text" name="modelo" id="modelo" class="form-control" required>
                                <div class="invalid-feedback">El modelo es requerido</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Color</label>
                                <input type="text" name="color" id="color" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cilindraje</label>
                                <input type="text" name="cilindraje" id="cilindraje" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Capacidad de Carga</label>
                                <input type="text" name="cap_carga" id="cap_carga" class="form-control">
                            </div>
                            <div class="col-md-6" style="position: relative;">
                                <label class="form-label">Categoría de Vehículo <span class="text-danger">*</span></label>
                                <input type="text" id="cat_vehic_search" class="form-control" placeholder="Escribe para buscar..." autocomplete="off" required>
                                <input type="hidden" id="cat_vehic_id_modal" name="cat_vehic_id" required>
                                <select id="cat_vehic_dropdown" class="form-select" size="5" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1050; margin-top: 0.25rem; max-height: 200px;">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($categorias as $id => $nombre): ?>
                                        <option value="<?= $id ?>"><?= htmlspecialchars($nombre) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Debe seleccionar una categoría</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subcategoría de Vehículo <span class="text-danger">*</span></label>
                                <select id="subcat_vehic_id_modal" name="subcat_vehic_id" class="form-select" required disabled>
                                    <option value="">Primero selecciona una categoría</option>
                                </select>
                                <div class="invalid-feedback">Debe seleccionar una subcategoría</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Número de Chasis</label>
                                <input type="text" name="num_cha" id="num_cha" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Distribución de Ejes</label>
                                <input type="text" name="distru_ejes" id="distru_ejes" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Línea Marca</label>
                                <input type="text" name="linea_marca" id="linea_marca" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tecnomecánica</label>
                                <input type="text" name="tecnomecanica" id="tecnomecanica" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">SOAT</label>
                                <input type="text" name="soat" id="soat" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo de Unidad</label>
                                <input type="text" name="tipo_unidad" id="tipo_unidad" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo de Combustible</label>
                                <input type="text" name="tipo_combustible" id="tipo_combustible" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">RUNT</label>
                                <input type="text" name="RUNT" id="RUNT" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cert. Homologación</label>
                                <input type="text" name="cert_homologacion" id="cert_homologacion" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cert. Matrícula</label>
                                <input type="text" name="cert_matricula" id="cert_matricula" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tarjeta de Propiedad</label>
                                <input type="text" name="tarje_propiedad" id="tarje_propiedad" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer-custom">
                        <button type="button" class="btn btn-secondary" onclick="closeVehicleModal()">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="saveVehicleBtn" disabled>
                            <i class="bi bi-check-lg"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Ver Vehículo -->
<!-- Modal Asignar Conductor -->
<div class="modal fade" id="modalAsignarConductor" tabindex="-1" aria-labelledby="modalAsignarConductorLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalAsignarConductorLabel"><i class="bi bi-person-plus"></i> Asignar Conductor</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form method="post" action="regis_vehic.php" id="formAsignarConductor">
        <div class="modal-body">
          <input type="hidden" name="asignar_conductor" value="1">
          <input type="hidden" name="vehiculo_id" id="asignar_vehiculo_id">
          <div class="mb-3">
            <label class="form-label fw-bold">Vehículo:</label>
            <p class="text-muted" id="asignar_vehiculo_placa"></p>
          </div>
          <div class="mb-3">
            <label for="asignar_conductor_id" class="form-label fw-bold">Conductor: <span class="text-danger">*</span></label>
            <select name="conductor_id" id="asignar_conductor_id" class="form-select" required>
              <option value="">Seleccione un conductor...</option>
              <?php foreach ($conductores as $cond): ?>
                <option value="<?= $cond['id'] ?>"><?= htmlspecialchars($cond['cargo']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cancelar</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Asignar Conductor</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalVerVehiculo" tabindex="-1" aria-labelledby="modalVerVehiculoLabel" style="display: none;">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="modalVerVehiculoLabel"><i class="bi bi-eye"></i> Detalles del Vehículo</h5>
        <button type="button" class="btn-close btn-close-white" onclick="cerrarModalVerVehiculo()" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 mb-2"><strong>ID:</strong> <span id="ver_id"></span></div>
          <div class="col-md-6 mb-2"><strong>Placa:</strong> <span id="ver_placa"></span></div>
          <div class="col-md-6 mb-2"><strong>Marca:</strong> <span id="ver_marca"></span></div>
          <div class="col-md-6 mb-2"><strong>Modelo:</strong> <span id="ver_modelo"></span></div>
          <div class="col-md-6 mb-2"><strong>Color:</strong> <span id="ver_color"></span></div>
          <div class="col-md-6 mb-2"><strong>Cilindraje:</strong> <span id="ver_cilindraje"></span></div>
          <div class="col-md-6 mb-2"><strong>Cap. Carga:</strong> <span id="ver_cap_carga"></span></div>
          <div class="col-md-6 mb-2"><strong>Subcategoría:</strong> <span id="ver_subcat"></span></div>
          <div class="col-md-6 mb-2"><strong>Categoría:</strong> <span id="ver_cat"></span></div>
          <div class="col-md-6 mb-2"><strong>Estado:</strong> <span id="ver_estado"></span></div>
          <div class="col-md-6 mb-2"><strong>Conductor Asignado:</strong> <span id="ver_conductor"></span></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="cerrarModalVerVehiculo()"><i class="bi bi-x-circle"></i> Cerrar</button>
      </div>
    </div>
  </div>
</div>
                <script>
function abrirAsignarConductor(vehiculoId, placa) {
    document.getElementById('asignar_vehiculo_id').value = vehiculoId;
    document.getElementById('asignar_vehiculo_placa').textContent = placa;
    const modal = document.getElementById('modalAsignarConductor');
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modal);
        modalInstance.show();
    }
}

// AJAX handler para asignación de conductor
document.addEventListener('DOMContentLoaded', function() {
    const formAsignar = document.getElementById('formAsignarConductor');
    if (formAsignar) {
        formAsignar.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const vehiculoId = formData.get('vehiculo_id');
            const conductorId = formData.get('conductor_id');
            const conductorSelect = document.getElementById('asignar_conductor_id');
            const conductorNombre = conductorSelect.options[conductorSelect.selectedIndex].text;
            
            fetch('regis_vehic.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.text();
            })
            .then(html => {
                // Cerrar modal
                const modal = document.getElementById('modalAsignarConductor');
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) modalInstance.hide();
                }
                
                // Esperar a que el modal se cierre completamente antes de recargar
                setTimeout(() => {
                    alert('Conductor asignado correctamente al vehículo');
                    // Forzar recarga sin caché
                    window.location.href = 'regis_vehic.php?t=' + new Date().getTime();
                }, 400);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al asignar conductor: ' + error.message);
            });
        });
    }
});

function verVehiculo(row) {
    document.getElementById('ver_id').textContent = row.id || '';
    document.getElementById('ver_placa').textContent = row.placa || '';
    document.getElementById('ver_marca').textContent = row.marca_vehiculo || '';
    document.getElementById('ver_modelo').textContent = row.modelo || '';
    document.getElementById('ver_color').textContent = row.color || '';
    document.getElementById('ver_cilindraje').textContent = row.cilindraje || '';
    document.getElementById('ver_cap_carga').textContent = row.cap_carga || '';
    document.getElementById('ver_subcat').textContent = row.subcat_nombre || '';
    document.getElementById('ver_cat').textContent = row.cat_nombre || '';
    document.getElementById('ver_estado').textContent = row.estado || '';
    document.getElementById('ver_conductor').textContent = row.cond_id ? row.conductor_nombre || 'Asignado' : 'No asignado';
    
    const modal = document.getElementById('modalVerVehiculo');
    
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

function cerrarModalVerVehiculo() {
    const modal = document.getElementById('modalVerVehiculo');
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

// --- Categorías y Subcategorías (GLOBAL) ---
var categoriasList = [
    <?php foreach ($categorias as $id => $nombre): ?>
        {id: <?= $id ?>, nombre: "<?= addslashes($nombre) ?>"},
    <?php endforeach; ?>
];

var subcatsByCat = {};
<?php
// Generar array JS: subcatsByCat[cat_id] = [{id, nombre}]
$subcatsByCat = [];
foreach ($subcategorias as $id => $subcat) {
        $cat_id = $subcat['categoria_id'];
        if ($cat_id) {
                if (!isset($subcatsByCat[$cat_id])) $subcatsByCat[$cat_id] = [];
                $subcatsByCat[$cat_id][] = ['id' => $id, 'nombre' => $subcat['nombre']];
        }
}
?>
subcatsByCat = <?php echo json_encode($subcatsByCat); ?>;

document.addEventListener('DOMContentLoaded', function() {
    var catSelect = document.getElementById('cat_vehic_id');
    var subcatSelect = document.getElementById('subcat_vehic_id');
    var subcatMsg = document.getElementById('subcat-msg');

    function updateSubcats(selectedCat, selectedSubcat) {
        if (!subcatSelect) return; // Si el elemento no existe, salir
        
        subcatSelect.innerHTML = '';
        if (!selectedCat || !subcatsByCat[selectedCat]) {
            subcatSelect.disabled = true;
            var opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'Primero selecciona una categoría';
            subcatSelect.appendChild(opt);
            if (subcatMsg) subcatMsg.style.display = 'none';
            return;
        }
        subcatSelect.disabled = false;
        var opt = document.createElement('option');
        opt.value = '';
        opt.textContent = 'Seleccione...';
        subcatSelect.appendChild(opt);
        var validSubcat = false;
        subcatsByCat[selectedCat].forEach(function(subcat) {
            var option = document.createElement('option');
            option.value = subcat.id;
            option.textContent = subcat.nombre;
            if (selectedSubcat && selectedSubcat == subcat.id) {
                option.selected = true;
                validSubcat = true;
            }
            subcatSelect.appendChild(option);
        });
        // Si hay subcat seleccionada pero no es válida para la categoría
        if (selectedSubcat && !validSubcat && subcatMsg) {
            subcatMsg.textContent = 'La subcategoría anterior no es válida para la nueva categoría. Por favor, selecciona una nueva.';
            subcatMsg.style.display = 'block';
            subcatSelect.value = '';
        } else if (subcatMsg) {
            subcatMsg.style.display = 'none';
        }
    }

    // Inicializar en edición solo si existen los elementos
    if (catSelect && subcatSelect) {
        var initialCat = catSelect.value || '';
        var initialSubcat = subcatSelect.getAttribute('data-initial') || '';
        updateSubcats(initialCat, initialSubcat);

        catSelect.addEventListener('change', function() {
            updateSubcats(this.value, '');
        });
    }

    // --- Modal Vehículo AJAX ---
    const vehicleModal = document.getElementById('vehicleModal');
    const vehicleForm = document.getElementById('vehicleForm');
    const saveVehicleBtn = document.getElementById('saveVehicleBtn');
    const catSelectModal = document.getElementById('cat_vehic_id_modal');
    const subcatSelectModal = document.getElementById('subcat_vehic_id_modal');

    console.log('Elementos del modal encontrados:', {
        vehicleModal: !!vehicleModal,
        vehicleForm: !!vehicleForm,
        saveVehicleBtn: !!saveVehicleBtn,
        subcatSelectModal: !!subcatSelectModal
    });

    // Select filtrable para categoría
    const catSearchInput = document.getElementById('cat_vehic_search');
    const catDropdownSelect = document.getElementById('cat_vehic_dropdown');
    const catHiddenInput = document.getElementById('cat_vehic_id_modal');
    
    console.log('Elementos filtro categoría:', {
        catSearchInput: !!catSearchInput,
        catDropdownSelect: !!catDropdownSelect,
        catHiddenInput: !!catHiddenInput,
        categoriasList: categoriasList
    });
    
    let allCategoryOptions = Array.from(catDropdownSelect.options).filter(opt => opt.value !== '');
    console.log('Opciones originales:', allCategoryOptions.length);

    // Función para mostrar todas o categorías filtradas
    function mostrarCategoriasVehiculo(searchTerm = '') {
        const filtered = allCategoryOptions.filter(opt => {
            if (!searchTerm) return true; // Mostrar todas si no hay búsqueda
            return opt.text.toLowerCase().includes(searchTerm.toLowerCase());
        });
        
        console.log('Opciones filtradas:', filtered.length);

        // Limpiar y llenar dropdown
        catDropdownSelect.innerHTML = '';
        
        if (filtered.length === 0) {
            const noResult = document.createElement('option');
            noResult.disabled = true;
            noResult.text = 'No se encontraron categorías';
            catDropdownSelect.add(noResult);
        } else {
            filtered.forEach(opt => {
                const newOpt = document.createElement('option');
                newOpt.value = opt.value;
                newOpt.text = opt.text;
                catDropdownSelect.add(newOpt);
            });
        }

        catDropdownSelect.style.display = 'block';
        console.log('Dropdown mostrado');
    }

    // Mostrar dropdown al escribir
    catSearchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        console.log('Búsqueda:', searchTerm);
        
        if (searchTerm === '') {
            catHiddenInput.value = '';
            updateSubcatsModal('', '');
            validateVehicleForm();
        }
        
        mostrarCategoriasVehiculo(searchTerm);
    });

    // Seleccionar del dropdown
    catDropdownSelect.addEventListener('change', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            catSearchInput.value = selectedOption.text;
            catHiddenInput.value = this.value;
            catDropdownSelect.style.display = 'none';
            
            console.log('Categoría seleccionada:', this.value, selectedOption.text);
            updateSubcatsModal(this.value, '');
            validateVehicleForm();
        }
    });

    // Click en el dropdown
    catDropdownSelect.addEventListener('click', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            catSearchInput.value = selectedOption.text;
            catHiddenInput.value = this.value;
            catDropdownSelect.style.display = 'none';
            
            console.log('Categoría seleccionada:', this.value, selectedOption.text);
            updateSubcatsModal(this.value, '');
            validateVehicleForm();
        }
    });

    // Mostrar dropdown al hacer focus (con todas las categorías)
    catSearchInput.addEventListener('focus', function() {
        mostrarCategoriasVehiculo(this.value);
    });

    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!catSearchInput.contains(e.target) && !catDropdownSelect.contains(e.target)) {
            catDropdownSelect.style.display = 'none';
        }
    });

    subcatSelectModal.addEventListener('change', validateVehicleForm);

    // Validación en tiempo real
    const requiredFields = vehicleForm.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('input', validateVehicleForm);
        field.addEventListener('change', validateVehicleForm);
        field.addEventListener('blur', function() {
            if (this.value.trim() === '' && this.hasAttribute('required')) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    });

    function validateVehicleForm() {
        let isValid = true;
        requiredFields.forEach(field => {
            if (field.value.trim() === '' || (field.tagName === 'SELECT' && field.value === '')) {
                isValid = false;
            }
        });
        saveVehicleBtn.disabled = !isValid;
    }

    // Submit AJAX
    vehicleForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('regis_vehic.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message);
                closeVehicleModal();
                setTimeout(() => window.location.reload(), 1500);
            }
        })
        .catch(err => console.error('Error:', err));
    });
});

// Funciones globales para el modal
function updateSubcatsModal(selectedCat, selectedSubcat) {
    console.log('updateSubcatsModal llamada con:', selectedCat, selectedSubcat);
    console.log('subcatsByCat disponible:', subcatsByCat);
    
    const subcatSelectModal = document.getElementById('subcat_vehic_id_modal');
    if (!subcatSelectModal) {
        console.error('No se encontró el select de subcategoría modal');
        return;
    }
    
    subcatSelectModal.innerHTML = '';
    
    if (!selectedCat || !subcatsByCat[selectedCat]) {
        console.log('Sin categoría o sin subcategorías para esta categoría');
        subcatSelectModal.disabled = true;
        var opt = document.createElement('option');
        opt.value = '';
        opt.textContent = 'Primero selecciona una categoría';
        subcatSelectModal.appendChild(opt);
        return;
    }
    
    console.log('Subcategorías encontradas:', subcatsByCat[selectedCat]);
    subcatSelectModal.disabled = false;
    
    var opt = document.createElement('option');
    opt.value = '';
    opt.textContent = 'Seleccione...';
    subcatSelectModal.appendChild(opt);
    
    subcatsByCat[selectedCat].forEach(function(subcat) {
        var option = document.createElement('option');
        option.value = subcat.id;
        option.textContent = subcat.nombre;
        if (selectedSubcat && selectedSubcat == subcat.id) {
            option.selected = true;
        }
        subcatSelectModal.appendChild(option);
    });
    
    console.log('Subcategorías cargadas. Total opciones:', subcatSelectModal.options.length);
}

function openVehicleModal() {
    document.getElementById('modalTitle').textContent = 'Agregar Vehículo';
    document.getElementById('vehicleForm').reset();
    document.getElementById('vehicle_id').value = '';
    
    // Resetear validaciones
    const inputs = document.querySelectorAll('#vehicleForm .form-control, #vehicleForm .form-select');
    inputs.forEach(input => input.classList.remove('is-invalid'));
    
    // Resetear categoría y subcategoría
    document.getElementById('cat_vehic_search').value = '';
    document.getElementById('cat_vehic_id_modal').value = '';
    document.getElementById('cat_vehic_dropdown').style.display = 'none';
    updateSubcatsModal('', '');
    
    document.getElementById('saveVehicleBtn').disabled = true;
    document.getElementById('vehicleModal').classList.add('active');
}

function closeVehicleModal() {
    document.getElementById('vehicleModal').classList.remove('active');
    document.getElementById('cat_vehic_dropdown').style.display = 'none';
}

function editVehicle(id) {
    fetch(`regis_vehic.php?ajax=get&id=${id}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Editar Vehículo';
            document.getElementById('vehicle_id').value = data.id;
            document.getElementById('placa').value = data.placa || '';
            document.getElementById('marca_vehiculo').value = data.marca_vehiculo || '';
            document.getElementById('modelo').value = data.modelo || '';
            document.getElementById('color').value = data.color || '';
            document.getElementById('cilindraje').value = data.cilindraje || '';
            document.getElementById('cap_carga').value = data.cap_carga || '';
            document.getElementById('num_cha').value = data.num_cha || '';
            document.getElementById('distru_ejes').value = data.distru_ejes || '';
            document.getElementById('linea_marca').value = data.linea_marca || '';
            document.getElementById('tecnomecanica').value = data.tecnomecanica || '';
            document.getElementById('soat').value = data.soat || '';
            document.getElementById('tipo_unidad').value = data.tipo_unidad || '';
            document.getElementById('tipo_combustible').value = data.tipo_combustible || '';
            document.getElementById('RUNT').value = data.RUNT || '';
            document.getElementById('cert_homologacion').value = data.cert_homologacion || '';
            document.getElementById('cert_matricula').value = data.cert_matricula || '';
            document.getElementById('tarje_propiedad').value = data.tarje_propiedad || '';
            
            // Cargar categoría y subcategoría
            const categoria = categoriasList.find(cat => cat.id == data.cat_vehic_id);
            if (categoria) {
                document.getElementById('cat_vehic_search').value = categoria.nombre;
                document.getElementById('cat_vehic_id_modal').value = data.cat_vehic_id;
            }
            updateSubcatsModal(data.cat_vehic_id, data.subcat_vehic_id);
            
            // Validar después de cargar datos
            setTimeout(function() {
                validateVehicleForm();
            }, 100);
            
            document.getElementById('vehicleModal').classList.add('active');
        });
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = `<i class="bi bi-check-circle-fill"></i><span>${message}</span>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent = document.querySelector('.main-content');

    // Toggle sidebar on mobile
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('show-mobile');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth < 1024) {
            if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('show-mobile');
            }
        }
    });

    // Adjust main content margin based on sidebar
    function adjustMainContent() {
        if (window.innerWidth >= 1024) {
            mainContent.style.marginLeft = '240px';
        } else {
            mainContent.style.marginLeft = '0';
        }
    }

    adjustMainContent();
    window.addEventListener('resize', adjustMainContent);
});
</script>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
