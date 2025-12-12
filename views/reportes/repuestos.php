<div class="container mt-4">
   <div class="card shadow-sm mb-4">
       <div class="card-body">
           <h2 class="card-title text-primary"><i class="bi bi-box"></i> Reportes de repuestos</h2>
           <form method="GET" action="/trucksisx/reportes.php" class="row g-3 align-items-center mb-3">
                 <input type="hidden" name="reporte" value="repuestos">
                 <div class="col-auto">
                    <label for="categoria" class="form-label">Filtrar por categoría</label>
                 </div>
                 <div class="col-auto">
                    <select id="categoria" name="categoria" class="form-select">
                        <option value="">Todas</option>
                        <?php if (!empty($categorias)): ?>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['id']) ?>" <?= (isset($_GET['categoria']) && $_GET['categoria'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                 </div>
                 <div class="col-auto">
                    <label for="subcategoria" class="form-label">Filtrar por subcategoría</label>
                 </div>
                 <div class="col-auto">
                    <select id="subcategoria" name="subcategoria" class="form-select">
                        <option value="">Todas</option>
                        <?php if (!empty($subcategorias)): ?>
                            <?php foreach ($subcategorias as $sub): ?>
                                <option value="<?= htmlspecialchars($sub['id']) ?>" <?= (isset($_GET['subcategoria']) && $_GET['subcategoria'] == $sub['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($sub['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                 </div>
                 <div class="col-auto">
                    <label for="proveedor" class="form-label">Filtrar por proveedor</label>
                 </div>
                 <div class="col-auto">
                    <select id="proveedor" name="proveedor" class="form-select">
                        <option value="">Todos</option>
                        <?php if (!empty($proveedores)): ?>
                            <?php foreach ($proveedores as $prov): ?>
                                <option value="<?= htmlspecialchars($prov['id']) ?>" <?= (isset($_GET['proveedor']) && $_GET['proveedor'] == $prov['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($prov['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                 </div>
                 <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                 </div>
                <div class="col-auto">
                          <a href="/trucksisx/reportes.php?reporte=repuestos" class="btn btn-primary">Reporte total</a>
                </div>
           </form>
           <div class="mb-3">
                <a class="btn btn-danger me-2" id="descargar-pdf" href="#"><i class="bi bi-file-earmark-pdf"></i> Descargar PDF</a>
                <a class="btn btn-success" id="descargar-excel" href="#"><i class="bi bi-file-earmark-excel"></i> Descargar Excel</a>
           </div>
           <div id="tabla-repuestos" class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Categoria</th>
                            <th>Subcategoria</th>
                            <th>Proveedor</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($repuestos)): ?>
                            <?php foreach ($repuestos as $repuesto): ?>
                                <tr>
                                    <td><?= htmlspecialchars($repuesto['nombre']); ?></td>
                                    <td><?= htmlspecialchars($repuesto['categoria']); ?></td>
                                    <td><?= htmlspecialchars($repuesto['subcategoria']); ?></td>
                                    <td><?= htmlspecialchars($repuesto['proveedor_nombre']); ?></td>
                                    <td><?= htmlspecialchars($repuesto['precio']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No hay resultados</td>
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

    .form-control, .form-select {
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        font-size: 0.95rem;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
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
        box-shadow: 0 2px 8px rgba(30, 58, 138, 0.3);
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
        const categoria = document.getElementById('categoria').value;
        const subcategoria = document.getElementById('subcategoria').value;
        const proveedor = document.getElementById('proveedor').value;

        let url = '/trucksisx/reportes.php?reporte=descargarRepuestosPDF';
        if (categoria) url += '&categoria=' + encodeURIComponent(categoria);
        if (subcategoria) url += '&subcategoria=' + encodeURIComponent(subcategoria);
        if (proveedor) url += '&proveedor=' + encodeURIComponent(proveedor);
        
        window.location.href = url;
    };
    document.getElementById('descargar-excel').onclick = function(e) {
        e.preventDefault();
        const categoria = document.getElementById('categoria').value;
        const subcategoria = document.getElementById('subcategoria').value;
        const proveedor = document.getElementById('proveedor').value;

        let url = '/trucksisx/reportes.php?reporte=descargarRepuestosExcel';
        if (categoria) url += '&categoria=' + encodeURIComponent(categoria);
        if (subcategoria) url += '&subcategoria=' + encodeURIComponent(subcategoria);
        if (proveedor) url += '&proveedor=' + encodeURIComponent(proveedor);

        window.location.href = url;
    };
</script>
