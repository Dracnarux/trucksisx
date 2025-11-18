<div class="container mt-4">
   <div class="card shadow-sm mb-4">
       <div class="card-body">
           <h2 class="card-title text-primary"><i class="bi bi-truck-front"></i> Reportes de Salidas de Vehículos</h2>
           <form method="GET" action="/trucksisx/reportes.php" class="row g-3 align-items-center mb-3">
                 <input type="hidden" name="reporte" value="salidasVehiculos">
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
                 <div class="col-md-3">
                    <label for="conductor" class="form-label">Conductor</label>
                    <select id="conductor" name="conductor" class="form-select">
                        <option value="">Todos</option>
                        <?php if (!empty($conductores)): ?>
                            <?php foreach ($conductores as $cond): ?>
                                <option value="<?= htmlspecialchars($cond['id']) ?>" <?= (isset($_GET['conductor']) && $_GET['conductor'] == $cond['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($cond['nombre_completo']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                 </div>
                 <div class="col-md-3">
                    <label for="fecha_desde" class="form-label">Fecha desde</label>
                     <input type="date" id="fecha_desde" name="fecha_desde" class="form-control" value="<?= htmlspecialchars($_GET['fecha_desde'] ?? '') ?>">
                 </div>
                 <div class="col-md-3">
                    <label for="fecha_hasta" class="form-label">Fecha hasta</label>
                     <input type="date" id="fecha_hasta" name="fecha_hasta" class="form-control" value="<?= htmlspecialchars($_GET['fecha_hasta'] ?? '') ?>">
                 </div>
                 <div class="col-md-12 d-flex gap-2 align-items-end">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                          <a href="/trucksisx/reportes.php?reporte=salidasVehiculos" class="btn btn-primary">Reporte total</a>
                 </div>
           </form>
           <div class="mb-3">
                <a class="btn btn-danger me-2" id="descargar-pdf" href="#"><i class="bi bi-file-earmark-pdf"></i> Descargar PDF</a>
                <a class="btn btn-success" id="descargar-excel" href="#"><i class="bi bi-file-earmark-excel"></i> Descargar Excel</a>
           </div>
           <div id="tabla-salidas" class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Vehículo</th>
                            <th>Conductor</th>
                            <th>Fecha Salida</th>
                            <th>Fecha Retorno</th>
                            <th>Destino</th>
                            <th>Kilometraje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($salidas)): ?>
                            <?php foreach ($salidas as $sal): ?>
                                <tr>
                                    <td><?= htmlspecialchars($sal['id_salida'] ?? $sal['id'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($sal['id_vehiculo'] ?? $sal['vehiculo_placa'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($sal['id_conductor'] ?? $sal['conductor_nombre'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($sal['fecha_salida'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($sal['fecha_retorno'] ?? 'N/A'); ?></td>
                                    <td><?= htmlspecialchars($sal['destino'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($sal['kilometraje'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay resultados</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
           </div>
           <a href="/trucksisx/reportes.php" class="btn btn-dark"><i class="bi bi-arrow-left"></i> Volver</a>
       </div>
   </div>
</div>

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
        color: #1E3A8A;
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
    }

    .card-title i {
        margin-right: 0.5rem;
        color: #3B82F6;
    }

    .form-label {
        color: #374151;
        font-weight: 500;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        font-size: 0.95rem;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
        background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);
        color: #FFFFFF;
        box-shadow: 0 2px 8px rgba(30, 58, 138, 0.3);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #1E40AF 0%, #2563EB 100%);
        box-shadow: 0 4px 12px rgba(30, 58, 138, 0.4);
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6B7280 0%, #9CA3AF 100%);
        color: #FFFFFF;
        box-shadow: 0 2px 8px rgba(107, 114, 128, 0.3);
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #4B5563 0%, #6B7280 100%);
        box-shadow: 0 4px 12px rgba(107, 114, 128, 0.4);
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
        color: #1E3A8A;
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
        params.set('reporte', 'descargarSalidasVPDF');
        window.location.href = '/trucksisx/reportes.php?' + params.toString();
    };
    document.getElementById('descargar-excel').onclick = function(e) {
        e.preventDefault();
        const params = new URLSearchParams(window.location.search);
        params.set('reporte', 'descargarSalidasVExcel');
        window.location.href = '/trucksisx/reportes.php?' + params.toString();
    };
</script>
