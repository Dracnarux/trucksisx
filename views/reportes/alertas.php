<div class="container mt-4">
   <div class="card shadow-sm mb-4">
       <div class="card-body">
           <h2 class="card-title text-primary"><i class="bi bi-exclamation-triangle"></i> Reportes de Alertas</h2>
           <form method="GET" action="/trucksisx/reportes.php" class="row g-3 align-items-center mb-3">
                 <input type="hidden" name="reporte" value="alertas">
                 <div class="col-md-3">
                    <label for="tipo" class="form-label">Tipo de alerta</label>
                    <select id="tipo" name="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="llanta" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'llanta') ? 'selected' : ''; ?>>Llanta</option>
                        <option value="motor" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'motor') ? 'selected' : ''; ?>>Motor</option>
                        <option value="frenos" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'frenos') ? 'selected' : ''; ?>>Frenos</option>
                        <option value="general" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'general') ? 'selected' : ''; ?>>General</option>
                    </select>
                 </div>
                 <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select id="estado" name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="activa" <?= (isset($_GET['estado']) && $_GET['estado'] == 'activa') ? 'selected' : ''; ?>>Activa</option>
                        <option value="en_proceso" <?= (isset($_GET['estado']) && $_GET['estado'] == 'en_proceso') ? 'selected' : ''; ?>>En Proceso</option>
                        <option value="resuelta" <?= (isset($_GET['estado']) && $_GET['estado'] == 'resuelta') ? 'selected' : ''; ?>>Resuelta</option>
                        <option value="cancelada" <?= (isset($_GET['estado']) && $_GET['estado'] == 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                    </select>
                 </div>
                 <div class="col-md-3">
                    <label for="prioridad" class="form-label">Prioridad</label>
                    <select id="prioridad" name="prioridad" class="form-select">
                        <option value="">Todas</option>
                        <option value="baja" <?= (isset($_GET['prioridad']) && $_GET['prioridad'] == 'baja') ? 'selected' : ''; ?>>Baja</option>
                        <option value="media" <?= (isset($_GET['prioridad']) && $_GET['prioridad'] == 'media') ? 'selected' : ''; ?>>Media</option>
                        <option value="alta" <?= (isset($_GET['prioridad']) && $_GET['prioridad'] == 'alta') ? 'selected' : ''; ?>>Alta</option>
                        <option value="critica" <?= (isset($_GET['prioridad']) && $_GET['prioridad'] == 'critica') ? 'selected' : ''; ?>>Crítica</option>
                    </select>
                 </div>
                 <div class="col-md-3">
                    <label for="vehiculo" class="form-label">Vehículo</label>
                    <select id="vehiculo" name="vehiculo" class="form-select">
                        <option value="">Todos</option>
                        <?php if (!empty($vehiculos)): ?>
                            <?php foreach ($vehiculos as $veh): ?>
                                <option value="<?= htmlspecialchars($veh['id']) ?>" <?= (isset($_GET['vehiculo']) && $_GET['vehiculo'] == $veh['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($veh['placa']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                 </div>
                 <div class="col-md-12 d-flex gap-2 align-items-end">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                          <a href="/trucksisx/reportes.php?reporte=alertas" class="btn btn-primary">Reporte total</a>
                 </div>
           </form>
           <div class="mb-3">
                <a class="btn btn-danger me-2" id="descargar-pdf" href="#"><i class="bi bi-file-earmark-pdf"></i> Descargar PDF</a>
                <a class="btn btn-success" id="descargar-excel" href="#"><i class="bi bi-file-earmark-excel"></i> Descargar Excel</a>
           </div>
           <div id="tabla-alertas" class="table-responsive">
                <div class="mb-2 text-muted">Registros: <?= isset($alertas) ? count($alertas) : 0 ?></div>
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>Fecha / Hora</th>
                            <th>Tipo</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Descripción</th>
                            <th>Vehículo</th>
                            <th>Orden</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($alertas)): ?>
                            <?php foreach ($alertas as $idx => $a): ?>
                                <tr>
                                    <td><?= htmlspecialchars($idx + 1); ?></td>
                                    <td><?= htmlspecialchars($a['id'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($a['fecha_hora'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($a['tipo_alerta'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($a['prioridad'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($a['estado'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($a['descripcion'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($a['vehiculo_placa'] ?? 'Sin asignar'); ?></td>
                                    <td><?= htmlspecialchars($a['orden'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No hay resultados</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <a href="/trucksisx/reportes.php" class="btn btn-dark mt-3"><i class="bi bi-arrow-left"></i> Módulos de reportes</a>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    body {
        background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%);
        color: #374151;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 16px;
        line-height: 1.6;
        min-height: 100vh;
    }

    .container {
        max-width: 1280px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .card {
        background: #FFFFFF;
        border: 1px solid rgba(209, 213, 219, 0.3);
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .card-body {
        padding: 2rem;
    }

    .card-title {
        color: #475569;
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
    }

    .card-title i {
        margin-right: 0.5rem;
        color: #475569;
    }

    .form-label {
        color: #374151;
        font-weight: 500;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .form-control {
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        font-size: 0.95rem;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #475569;
        box-shadow: 0 0 0 3px rgba(71, 85, 105, 0.1);
        outline: none;
    }

    .btn {
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: 0.95rem;
        font-weight: 500;
        min-height: 44px;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #475569 0%, #334155 100%);
        color: #FFFFFF;
        box-shadow: 0 2px 8px rgba(71, 85, 105, 0.3);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
        box-shadow: 0 4px 12px rgba(71, 85, 105, 0.4);
        transform: translateY(-1px);
    }

    .btn-danger {
        background: linear-gradient(135deg, #DC2626 0%, #EF4444 100%);
        color: #FFFFFF;
        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #B91C1C 0%, #DC2626 100%);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
        transform: translateY(-1px);
    }

    .btn-success {
        background: linear-gradient(135deg, #059669 0%, #10B981 100%);
        color: #FFFFFF;
        box-shadow: 0 2px 8px rgba(5, 150, 105, 0.3);
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #047857 0%, #059669 100%);
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.4);
        transform: translateY(-1px);
    }

    .btn-dark {
        background: linear-gradient(135deg, #374151 0%, #6B7280 100%);
        color: #FFFFFF;
        box-shadow: 0 2px 8px rgba(55, 65, 81, 0.3);
    }

    .btn-dark:hover {
        background: linear-gradient(135deg, #1F2937 0%, #374151 100%);
        box-shadow: 0 4px 12px rgba(55, 65, 81, 0.4);
        transform: translateY(-1px);
    }

    .table-responsive {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-top: 1.5rem;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead {
        background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%);
    }

    .table thead th {
        border-bottom: 2px solid #D1D5DB;
        color: #475569;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table tbody td {
        border-bottom: 1px solid #E5E7EB;
        color: #374151;
        font-size: 0.95rem;
        padding: 1rem;
        vertical-align: middle;
    }

    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: #F9FAFB;
    }

    .mb-3 {
        margin-bottom: 1.5rem !important;
    }

    .me-2 {
        margin-right: 0.75rem !important;
    }
</style>

<script>
    document.getElementById('descargar-pdf').onclick = function(e) {
        e.preventDefault();
        const params = new URLSearchParams(window.location.search);
        params.set('reporte', 'descargarAlertasPDF');
        window.location.href = '/trucksisx/reportes.php?' + params.toString();
    };
    document.getElementById('descargar-excel').onclick = function(e) {
        e.preventDefault();
        const params = new URLSearchParams(window.location.search);
        params.set('reporte', 'descargarAlertasExcel');
        window.location.href = '/trucksisx/reportes.php?' + params.toString();
    };
</script>
