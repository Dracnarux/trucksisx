<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Vehículo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
                /* Fix para visibilidad de opciones en Select2 dropdown */
                .select2-container--default .select2-dropdown {
                    background-color: #fff;
                    color: #222;
                    border: 2px solid var(--border);
                    border-radius: 0 0 8px 8px;
                }
                .select2-container--default .select2-results__option {
                    color: #222;
                    font-size: 16px;
                    padding: 8px 16px;
                }
                .select2-container--default .select2-results__option--highlighted[aria-selected] {
                    background-color: var(--accent);
                    color: #fff;
                }
                .select2-container--default .select2-results__option[aria-selected=true] {
                    background-color: var(--accent-amber);
                    color: #fff;
                }
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

        .select2-container--default .select2-selection--single {
            height: 44px !important;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 16px;
            color: var(--text-primary);
            background: var(--bg-secondary);
            box-sizing: border-box;
            display: flex;
            align-items: center;
            padding: 0 1rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: normal !important;
            padding-left: 0 !important;
            display: flex;
            align-items: center;
            height: 100%;
            color: var(--text-primary);
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px !important;
            top: 0 !important;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
            outline: none;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            min-height: 100vh;
        }
        h1, h2, h3, h4, h5, h6 {
            color: var(--text-primary);
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
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--card-radius);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-top: 2rem;
            margin-bottom: 2rem;
            padding: 2rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .main-header {
            background: linear-gradient(135deg, rgba(15,23,42,0.9) 0%, rgba(17,24,39,0.85) 100%);
            border-radius: var(--card-radius);
            box-shadow: 0 10px 25px rgba(2,6,23,0.2);
            color: var(--text-primary);
            margin-bottom: 2rem;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        .main-header h2 {
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
            box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.3);
            outline: none;
        }
        .btn-primary {
            background: var(--accent);
            color: white !important;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: #E65100;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
            color: #fff !important;
            transform: translateY(-1px);
        }
        .btn-outline-primary {
            background: #FFFFFF;
            border: 2px solid var(--accent);
            color: var(--accent) !important;
        }
        .btn-outline-primary:hover {
            background: var(--accent);
            color: #FFFFFF !important;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: transparent;
            border: 1px solid var(--text-secondary);
            color: var(--text-secondary);
        }
        .btn-secondary:hover {
            background: var(--text-secondary);
            color: var(--bg-primary);
        }
        .btn-success {
            background: var(--success);
            color: #FFFFFF !important;
        }
        .btn-warning {
            background: var(--accent-amber);
            color: #FFFFFF !important;
        }
        .btn-danger {
            background: var(--danger);
            color: #FFFFFF !important;
        }
        .btn-info {
            background: var(--text-secondary);
            color: #FFFFFF !important;
        }
        .btn-info:hover {
            background: var(--accent);
            color: #FFFFFF !important;
        }
        .form-control, .form-select {
            background: #FFFFFF;
            border: 2px solid var(--border);
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
        }
        .form-section h6 {
            border-bottom: 2px solid var(--accent);
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
            font-size: 13px;
        }
        .table thead th {
            background: var(--accent);
            border: none;
            color: #FFFFFF;
            font-weight: 600;
            padding: 0.75rem;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
            min-width: 120px;
        }
        .table tbody td {
            border-bottom: 1px solid var(--border);
            color: #000;
            padding: 0.75rem;
            vertical-align: middle;
            white-space: nowrap;
            min-width: 120px;
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
        .modal-content {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }
        .modal-header {
            background: var(--accent);
            border-radius: var(--card-radius) var(--card-radius) 0 0;
            color: #FFFFFF;
                border: none;
                border-radius: var(--card-radius);
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
                background: rgba(255,255,255,0.97);
            }
            .modal-header {
                background: var(--accent);
                border-radius: var(--card-radius) var(--card-radius) 0 0;
                color: #FFFFFF;
            }
            .modal-body {
                padding: 2rem;
                color: #000;
            }
            .modal-footer {
                border-top: 1px solid var(--border);
                padding: 1.5rem 2rem;
                background: rgba(255,255,255,0.97);
            color: var(--success);
        }
        .alert-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #D97706;
        }
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
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
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0"><i class="bi bi-journal-plus"></i> Registro de Vehículo</h2>
            <?php if (!$rol_conductor): ?>
            <button type="button" class="btn btn-success" onclick="abrirModalVehiculo()"><i class="bi bi-plus-circle"></i> Agregar Vehículo</button>
            <?php endif; ?>
        <?php if (!$rol_conductor): ?>
        <!-- Modal para agregar/editar vehículo -->
        <div class="modal fade" id="modalFormVehiculo" tabindex="-1" aria-labelledby="modalFormVehiculoLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content animate-slide-up">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalFormVehiculoLabel"><i class="bi bi-pencil-square"></i> <span id="tituloFormVehiculo">Agregar Vehículo</span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form method="post" action="regis_vehic.php" id="formVehiculo">
                        <input type="hidden" name="id" id="vehiculo_id">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Placa</label>
                                    <input type="text" name="placa" id="vehiculo_placa" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Marca</label>
                                    <input type="text" name="marca_vehiculo" id="vehiculo_marca" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Modelo</label>
                                    <input type="text" name="modelo" id="vehiculo_modelo" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Color</label>
                                    <input type="text" name="color" id="vehiculo_color" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cilindraje</label>
                                    <input type="text" name="cilindraje" id="vehiculo_cilindraje" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Capacidad de Carga</label>
                                    <input type="text" name="cap_carga" id="vehiculo_cap_carga" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Categoría de Vehículo</label>
                                    <select id="vehiculo_cat" class="form-select" onchange="filtrarSubcategorias()" style="width:100%">
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($categorias as $id => $nombre): ?>
                                            <option value="<?= $id ?>"><?= htmlspecialchars($nombre) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Subcategoría de Vehículo</label>
                                    <select name="subcat_vehic_id" id="vehiculo_subcat" class="form-select" required disabled onchange="actualizarCategoriaVehiculo()">
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($subcategorias as $id => $subcat): ?>
                                            <option value="<?= $id ?>" data-categoria="<?= htmlspecialchars($subcat['categoria']) ?>" data-cat="<?= array_search($subcat['categoria'], $categorias) !== false ? array_search($subcat['categoria'], $categorias) : '' ?>"><?= htmlspecialchars($subcat['nombre']) ?> (<?= htmlspecialchars($subcat['categoria']) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Número de Chasis</label>
                                    <input type="text" name="num_cha" id="vehiculo_num_cha" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Distribución de Ejes</label>
                                    <input type="text" name="distru_ejes" id="vehiculo_distru_ejes" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Línea Marca</label>
                                    <input type="text" name="linea_marca" id="vehiculo_linea_marca" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tecnomecánica</label>
                                    <input type="text" name="tecnomecanica" id="vehiculo_tecnomecanica" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">SOAT</label>
                                    <input type="text" name="soat" id="vehiculo_soat" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tipo de Unidad</label>
                                    <input type="text" name="tipo_unidad" id="vehiculo_tipo_unidad" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tipo de Combustible</label>
                                    <input type="text" name="tipo_combustible" id="vehiculo_tipo_combustible" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">RUNT</label>
                                    <input type="text" name="RUNT" id="vehiculo_RUNT" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cert. Homologación</label>
                                    <input type="text" name="cert_homologacion" id="vehiculo_cert_homologacion" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cert. Matrícula</label>
                                    <input type="text" name="cert_matricula" id="vehiculo_cert_matricula" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tarjeta de Propiedad</label>
                                    <input type="text" name="tarje_propiedad" id="vehiculo_tarje_propiedad" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Guardar</button>
                            <button type="button" class="btn btn-secondary mx-2" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
        function abrirModalVehiculo() {
            limpiarModalVehiculo();
            document.getElementById('tituloFormVehiculo').textContent = 'Agregar Vehículo';
            const modal = document.getElementById('modalFormVehiculo');
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(modal);
                modalInstance.show();
            }
        }
        function limpiarModalVehiculo() {
            document.getElementById('vehiculo_id').value = '';
            document.getElementById('vehiculo_placa').value = '';
            document.getElementById('vehiculo_marca').value = '';
            document.getElementById('vehiculo_modelo').value = '';
            document.getElementById('vehiculo_color').value = '';
            document.getElementById('vehiculo_cilindraje').value = '';
            document.getElementById('vehiculo_cap_carga').value = '';
            document.getElementById('vehiculo_cat').value = '';
            document.getElementById('vehiculo_subcat').value = '';
            document.getElementById('vehiculo_subcat').disabled = true;
            document.getElementById('vehiculo_num_cha').value = '';
            document.getElementById('vehiculo_distru_ejes').value = '';
            document.getElementById('vehiculo_linea_marca').value = '';
            document.getElementById('vehiculo_tecnomecanica').value = '';
            document.getElementById('vehiculo_soat').value = '';
            document.getElementById('vehiculo_tipo_unidad').value = '';
            document.getElementById('vehiculo_tipo_combustible').value = '';
            document.getElementById('vehiculo_RUNT').value = '';
            document.getElementById('vehiculo_cert_homologacion').value = '';
            document.getElementById('vehiculo_cert_matricula').value = '';
            document.getElementById('vehiculo_tarje_propiedad').value = '';
        }
        function editarVehiculo(row) {
            limpiarModalVehiculo();
            document.getElementById('tituloFormVehiculo').textContent = 'Editar Vehículo';
            document.getElementById('vehiculo_id').value = row.id || '';
            document.getElementById('vehiculo_placa').value = row.placa || '';
            document.getElementById('vehiculo_marca').value = row.marca_vehiculo || '';
            document.getElementById('vehiculo_modelo').value = row.modelo || '';
            document.getElementById('vehiculo_color').value = row.color || '';
            document.getElementById('vehiculo_cilindraje').value = row.cilindraje || '';
            document.getElementById('vehiculo_cap_carga').value = row.cap_carga || '';
            // Buscar la categoría correspondiente a la subcategoría
            var subcatId = row.subcat_vehic_id || '';
            var subcatSelect = document.getElementById('vehiculo_subcat');
            var catSelect = document.getElementById('vehiculo_cat');
            var catId = '';
            for (var i = 0; i < subcatSelect.options.length; i++) {
                if (subcatSelect.options[i].value == subcatId) {
                    catId = subcatSelect.options[i].getAttribute('data-cat');
                    break;
                }
            }
            catSelect.value = catId;
            filtrarSubcategorias();
            subcatSelect.value = subcatId;
            subcatSelect.disabled = false;
            document.getElementById('vehiculo_num_cha').value = row.num_cha || '';
            document.getElementById('vehiculo_distru_ejes').value = row.distru_ejes || '';
            document.getElementById('vehiculo_linea_marca').value = row.linea_marca || '';
            document.getElementById('vehiculo_tecnomecanica').value = row.tecnomecanica || '';
            document.getElementById('vehiculo_soat').value = row.soat || '';
            document.getElementById('vehiculo_tipo_unidad').value = row.tipo_unidad || '';
            document.getElementById('vehiculo_tipo_combustible').value = row.tipo_combustible || '';
            document.getElementById('vehiculo_RUNT').value = row.RUNT || '';
            document.getElementById('vehiculo_cert_homologacion').value = row.cert_homologacion || '';
            document.getElementById('vehiculo_cert_matricula').value = row.cert_matricula || '';
            document.getElementById('vehiculo_tarje_propiedad').value = row.tarje_propiedad || '';
            const modal = document.getElementById('modalFormVehiculo');
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(modal);
                modalInstance.show();
            }
        }
        function filtrarSubcategorias() {
            var catSelect = document.getElementById('vehiculo_cat');
            var subcatSelect = document.getElementById('vehiculo_subcat');
            var selectedCat = catSelect.value;
            for (var i = 0; i < subcatSelect.options.length; i++) {
                var opt = subcatSelect.options[i];
                if (!opt.value) { opt.style.display = ''; continue; }
                if (opt.getAttribute('data-cat') == selectedCat) {
                    opt.style.display = '';
                } else {
                    opt.style.display = 'none';
                }
            }
            subcatSelect.value = '';
            subcatSelect.disabled = !selectedCat;
        }

        </script>
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
        
        // Si es conductor, obtener su vehículo asignado
        $vehiculo_asignado_id = null;
        if ($rol_conductor) {
            $user_id = $_SESSION['usuario']['id'];
            $stmt_cond = $conn->prepare("SELECT regis_vehic_id FROM cond WHERE user_id = ? LIMIT 1");
            $stmt_cond->bind_param('i', $user_id);
            $stmt_cond->execute();
            $result_cond = $stmt_cond->get_result();
            if ($result_cond->num_rows > 0) {
                $row_cond = $result_cond->fetch_assoc();
                $vehiculo_asignado_id = $row_cond['regis_vehic_id'];
            }
        }
        
        $sql = "SELECT v.*, s.nombre AS subcat_nombre, cat.nombre AS cat_nombre, conductor.cargo AS conductor_nombre 
                FROM regis_vehic v 
                JOIN subcat_vehic s ON v.subcat_vehic_id = s.id 
                JOIN cat_vehic cat ON s.cat_vehic_id = cat.id 
                LEFT JOIN cond conductor ON v.cond_id = conductor.id 
                WHERE v.placa LIKE ? AND v.marca_vehiculo LIKE ?";
        $params = ["%$filtro_placa%", "%$filtro_marca%"];
        $types = 'ss';
        
        // Si es conductor, filtrar solo su vehículo
        if ($rol_conductor) {
            if ($vehiculo_asignado_id) {
                $sql .= " AND v.id = ?";
                $params[] = $vehiculo_asignado_id;
                $types .= 'i';
            } else {
                // Sin vehículo asignado, mostrar tabla vacía
                $sql .= " AND 1 = 0";
            }
        }
        
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
        
        <?php if ($rol_conductor): ?>
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle"></i> <strong>Vista de Conductor:</strong> Aquí puedes ver únicamente tu vehículo asignado.
        </div>
        <?php endif; ?>
        
        <?php if (!$rol_conductor): ?>
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
        <?php endif; ?>
        
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
                                    <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap">
                                        <button type="button" class="btn btn-info btn-sm" onclick="verVehiculo(<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>)"><i class="bi bi-eye"></i> Ver</button>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="editarVehiculo(<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>)"><i class="bi bi-pencil-square"></i> Editar</button>
                                        <?php 
                                        // Verificar si tiene conductor asignado (bidireccional)
                                        $tiene_conductor = false;
                                        if ($row['cond_id'] && $row['estado'] === 'Asignado') {
                                            $tiene_conductor = true;
                                        } else {
                                            // Verificar si hay conductores que referencian este vehículo
                                            $check_cond_sql = "SELECT COUNT(*) as total FROM cond WHERE regis_vehic_id = ?";
                                            $check_cond_stmt = $conn->prepare($check_cond_sql);
                                            $check_cond_stmt->bind_param('i', $row['id']);
                                            $check_cond_stmt->execute();
                                            $cond_count = $check_cond_stmt->get_result()->fetch_assoc()['total'];
                                            if ($cond_count > 0) {
                                                $tiene_conductor = true;
                                            }
                                        }
                                        if ($tiene_conductor): ?>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="desasignarConductor(<?= $row['id'] ?>, '<?= htmlspecialchars($row['placa']) ?>')"><i class="bi bi-person-x"></i> Desasignar</button>
                                        <?php endif; ?>
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
            // Desasignar conductor del vehículo
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['desasignar_conductor'])) {
                try {
                    $vehiculo_id = intval($_POST['vehiculo_id']);
                    
                    // Obtener el conductor asignado antes de desasignar
                    $sql_get_conductor = "SELECT cond_id FROM regis_vehic WHERE id = ?";
                    $stmt_get_conductor = $conn->prepare($sql_get_conductor);
                    $stmt_get_conductor->bind_param('i', $vehiculo_id);
                    $stmt_get_conductor->execute();
                    $result = $stmt_get_conductor->get_result();
                    $conductor_data = $result->fetch_assoc();
                    $conductor_id = $conductor_data ? $conductor_data['cond_id'] : null;
                    
                    // Desasignar conductor del vehículo
                    $sql_update_vehiculo = "UPDATE regis_vehic SET cond_id = NULL, estado = 'Sin conductor' WHERE id = ?";
                    $stmt_update_vehiculo = $conn->prepare($sql_update_vehiculo);
                    $stmt_update_vehiculo->bind_param('i', $vehiculo_id);
                    $stmt_update_vehiculo->execute();
                    
                    // Si había un conductor asignado, también actualizar la tabla de conductores
                    if ($conductor_id) {
                        $sql_update_conductor = "UPDATE cond SET regis_vehic_id = NULL WHERE id = ?";
                        $stmt_update_conductor = $conn->prepare($sql_update_conductor);
                        $stmt_update_conductor->bind_param('i', $conductor_id);
                        $stmt_update_conductor->execute();
                    }
                    
                    echo '<script>
                        alert("Conductor desasignado exitosamente. Ahora el vehículo y el conductor pueden ser eliminados por separado.");
                        window.location="regis_vehic.php";
                    </script>';
                    exit;
                    
                } catch (Exception $e) {
                    error_log("regis_vehic.php: Error al desasignar conductor: " . $e->getMessage());
                    echo '<script>
                        alert("Error al desasignar conductor: ' . addslashes($e->getMessage()) . '");
                        window.location="regis_vehic.php";
                    </script>';
                    exit;
                }
            }
            // Eliminar vehículo
            if (isset($_GET['delete'])) {
                try {
                    $vehiculo_id = $_GET['delete'];
                    
                    // PASO 1: Verificar dependencias que impiden la eliminación
                    
                    // Verificar alertas
                    $check_alerts_sql = "SELECT COUNT(*) as total FROM alert WHERE regis_vehic_id = ?";
                    $check_alerts_stmt = $conn->prepare($check_alerts_sql);
                    $check_alerts_stmt->bind_param('i', $vehiculo_id);
                    $check_alerts_stmt->execute();
                    $alerts_count = $check_alerts_stmt->get_result()->fetch_assoc()['total'];
                    
                    // Verificar conductor asignado en regis_vehic
                    $check_conductor_sql = "SELECT cond_id, estado FROM regis_vehic WHERE id = ?";
                    $check_conductor_stmt = $conn->prepare($check_conductor_sql);
                    $check_conductor_stmt->bind_param('i', $vehiculo_id);
                    $check_conductor_stmt->execute();
                    $vehiculo_data = $check_conductor_stmt->get_result()->fetch_assoc();
                    $conductor_asignado = $vehiculo_data ? $vehiculo_data['cond_id'] : null;
                    $estado_vehiculo = $vehiculo_data ? $vehiculo_data['estado'] : null;
                    
                    // Verificar conductores que tengan este vehículo referenciado
                    $check_conductores_sql = "SELECT COUNT(*) as total FROM cond WHERE regis_vehic_id = ?";
                    $check_conductores_stmt = $conn->prepare($check_conductores_sql);
                    $check_conductores_stmt->bind_param('i', $vehiculo_id);
                    $check_conductores_stmt->execute();
                    $conductores_referenciando = $check_conductores_stmt->get_result()->fetch_assoc()['total'];
                    
                    // Crear lista de dependencias que impiden la eliminación
                    $dependencias = [];
                    if ($alerts_count > 0) {
                        $dependencias[] = "$alerts_count alerta(s) activa(s)";
                    }
                    if ($conductor_asignado || $conductores_referenciando > 0 || $estado_vehiculo === 'Asignado') {
                        $dependencias[] = "1 conductor asignado";
                    }
                    
                    // Si hay dependencias, NO permitir eliminación
                    if (count($dependencias) > 0) {
                        $mensaje_dependencias = implode(" y ", $dependencias);
                        echo "<script>
                            alert('No se puede eliminar el vehículo porque tiene: $mensaje_dependencias\\n\\nPrimero debe:\\n- Resolver o cerrar las alertas desde el módulo de alertas\\n- Desasignar el conductor usando el botón \"Desasignar\" en este módulo\\n\\nDespués podrá eliminar el vehículo de forma segura.');
                            window.location.href = 'regis_vehic.php';
                        </script>";
                        exit;
                    }
                    
                    // PASO 2: Eliminar el vehículo (ya verificamos que no tiene dependencias)
                    $sql = "DELETE FROM regis_vehic WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('i', $vehiculo_id);
                    $stmt->execute();
                    
                    if ($stmt->affected_rows > 0) {
                        error_log("regis_vehic.php: Vehículo $vehiculo_id eliminado exitosamente");
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
            <a href="gestion_vehicular.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver al panel de gestiones</a>
        </div>
    </div>
    <!-- Modal Ver Vehículo -->
<div class="modal fade" id="modalVerVehiculo" tabindex="-1" aria-labelledby="modalVerVehiculoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVerVehiculoLabel"><i class="bi bi-eye"></i> Detalles del Vehículo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cerrar</button>
      </div>
    </div>
  </div>
</div>
<script>
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
  // Accesibilidad: quitar aria-hidden si existe
  const modal = document.getElementById('modalVerVehiculo');
  if (modal.hasAttribute('aria-hidden')) {
    modal.removeAttribute('aria-hidden');
  }
  if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modal);
    modalInstance.show();
  }
}

function desasignarConductor(vehiculoId, placa) {
  if (confirm(`¿Está seguro de desasignar el conductor del vehículo con placa "${placa}"?\n\nEsto permitirá:\n- Eliminar el conductor sin restricciones\n- Eliminar el vehículo sin restricciones\n\nEl vehículo quedará marcado como "Sin conductor".`)) {
    // Crear formulario dinámico para enviar la solicitud POST
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';
    
    const inputDesasignar = document.createElement('input');
    inputDesasignar.type = 'hidden';
    inputDesasignar.name = 'desasignar_conductor';
    inputDesasignar.value = '1';
    
    const inputVehiculoId = document.createElement('input');
    inputVehiculoId.type = 'hidden';
    inputVehiculoId.name = 'vehiculo_id';
    inputVehiculoId.value = vehiculoId;
    
    form.appendChild(inputDesasignar);
    form.appendChild(inputVehiculoId);
    document.body.appendChild(form);
    
    form.submit();
  }
}
</script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
        $(document).ready(function() {
            if ($('#vehiculo_cat').length) {
                $('#vehiculo_cat').select2({
                    dropdownParent: $('#modalFormVehiculo'),
                    width: 'resolve',
                    placeholder: 'Seleccione o busque una categoría',
                    language: 'es'
                });
            }
        });
        </script>
</body>
</html>
