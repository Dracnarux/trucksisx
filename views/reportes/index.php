<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Reportes - TrucksISX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{
            --bg-primary: #0F172A;
            --card-bg: #111827;
            --card-radius: 12px;
            --text-primary: #F1F5F9;
            --text-secondary: #94A3B8;
            --border: #1E293B;
            --accent: #F97316;
            --accent-amber: #F59E0B;
            --danger: #EF4444;
            --success: #10B981;
        }

        body {
            background: linear-gradient(135deg, var(--bg-primary) 0%, rgba(17,24,39,0.95) 100%);
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            min-height: 100vh;
            padding: 2rem 0;
        }

        .container { max-width: 1400px; }

        h2 { color: var(--accent); font-weight: 700; font-size: 2.25rem; margin-bottom: 2rem; }

        .card { background: #FFFFFF; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06); transition: all 0.3s ease; height: 100%; }

        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }

        .card-body { padding: 1.5rem; }

        .card i { color: var(--accent); transition: all 0.3s ease; }

        .card:hover i { transform: scale(1.05); color: var(--accent-amber); }

        .card-title { color: #111827; font-weight: 600; font-size: 1.1rem; margin-top: 1rem; margin-bottom: 0.5rem; }

        .card-text { color: #374151; font-size: 0.95rem; line-height: 1.4; }

        .btn { border-radius: 8px; border: none; cursor: pointer; font-size: 0.95rem; font-weight: 600; min-height: 44px; padding: 0.6rem 1rem; transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; }

        .btn-primary { background: var(--accent); color: #FFFFFF; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }

        .btn-primary:hover { background: #E65100; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(0,0,0,0.12); }

        .reports-list{ max-width:1200px; margin:2rem auto; }
        .reports-list .card{ border-radius:var(--card-radius); border:1px solid var(--border); background:#fff; }
        .row { row-gap: 1.5rem; }
    </style>
    <div class="container reports-list">
        <h2 class="text-center">Módulo de Reportes</h2>
        <div class="row g-4 justify-content-center">
        <!-- CATEGORÍAS -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-folder2-open display-4"></i>
                    <h5 class="card-title">Categorías de Repuestos</h5>
                    <p class="card-text">Consulta y descarga el reporte de todas las categorías de repuestos registradas.</p>
                    <a href="/trucksisx/reportes.php?reporte=categorias" class="btn btn-primary w-100">Ver Reportes</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-truck-front display-4"></i>
                    <h5 class="card-title">Categorías de Vehículos</h5>
                    <p class="card-text">Consulta y descarga el reporte de todas las categorías de vehículos registradas.</p>
                    <a href="/trucksisx/reportes.php?reporte=categoriasVehiculos" class="btn btn-primary w-100">Ver Reportes</a>
                </div>
            </div>
        </div>
    
    <!-- SUBCATEGORÍAS -->
    <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-diagram-3 display-4"></i>
                    <h5 class="card-title">Subcategorías de Repuestos</h5>
                    <p class="card-text">Consulta y descarga el reporte de todas las subcategorías de repuestos registradas.</p>
                    <a href="/trucksisx/reportes.php?reporte=subcategorias" class="btn btn-primary w-100">Ver Reportes</a>
                </div>
            </div>
    </div>
    
    <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-diagram-2 display-4"></i>
                    <h5 class="card-title">Subcategorías de Vehículos</h5>
                    <p class="card-text">Consulta y descarga el reporte de todas las subcategorías de vehículos registradas.</p>
                    <a href="/trucksisx/reportes.php?reporte=subcategoriasVehiculos" class="btn btn-primary w-100">Ver Reportes</a>
                </div>
            </div>
    </div>

    <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam display-4"></i>
                    <h5 class="card-title">Repuestos</h5>
                    <p class="card-text">Consulta y descarga el reporte de todos los repuestos registrados.</p>
                    <a href="/trucksisx/reportes.php?reporte=repuestos" class="btn btn-primary w-100">Ver Reportes</a>
                </div>
            </div>
    </div>

    <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-building display-4"></i>
                    <h5 class="card-title">Proveedores</h5>
                    <p class="card-text">Consulta y descarga el reporte de todos los proveedores registrados.</p>
                    <a href="/trucksisx/reportes.php?reporte=proveedores" class="btn btn-primary w-100">Ver Reportes</a>
                </div>
            </div>
    </div>
    
    <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people display-4"></i>
                    <h5 class="card-title">Usuarios</h5>
                    <p class="card-text">Consulta y descarga el reporte de todos los usuarios registrados.</p>
                    <a href="/trucksisx/reportes.php?reporte=usuarios" class="btn btn-primary w-100">Ver Reportes</a>
                </div>
            </div>
    </div>

    <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-truck display-4"></i>
                    <h5 class="card-title">Vehículos</h5>
                    <p class="card-text">Consulta y descarga el reporte de todos los vehículos registrados.</p>
                    <a href="/trucksisx/reportes.php?reporte=vehiculos" class="btn btn-primary w-100">Ver Reportes</a>
                </div>
            </div>
    </div>

    <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-person-badge display-4"></i>
                    <h5 class="card-title">Conductores</h5>
                    <p class="card-text">Consulta y descarga el reporte de todos los conductores registrados.</p>
                    <a href="/trucksisx/reportes.php?reporte=conductores" class="btn btn-primary w-100">Ver Reportes</a>
                </div>
            </div>
    </div>

    <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle display-4"></i>
                    <h5 class="card-title">Alertas</h5>
                    <p class="card-text">Consulta y descarga el reporte de todas las alertas del sistema.</p>
                    <a href="/trucksisx/reportes.php?reporte=alertas" class="btn btn-primary w-100">Ver Reportes</a>
                </div>
            </div>
    </div>

    <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-check display-4"></i>
                    <h5 class="card-title">Órdenes de Trabajo</h5>
                    <p class="card-text">Consulta y descarga el reporte de todas las órdenes de trabajo.</p>
                    <a href="/trucksisx/reportes.php?reporte=ordenesTrabajo" class="btn btn-primary w-100">Ver Reportes</a>
                </div>
            </div>
    </div>

    <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-box-arrow-right display-4"></i>
                    <h5 class="card-title">Salidas de Repuestos</h5>
                    <p class="card-text">Consulta y descarga el reporte de salidas de repuestos.</p>
                    <a href="/trucksisx/reportes.php?reporte=salidasRepuestos" class="btn btn-primary w-100">Ver Reportes</a>
                </div>
            </div>
    </div>

    <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-truck-front display-4"></i>
                    <h5 class="card-title">Salidas de Vehículos</h5>
                    <p class="card-text">Consulta y descarga el reporte de salidas de vehículos.</p>
                    <a href="/trucksisx/reportes.php?reporte=salidasVehiculos" class="btn btn-primary w-100">Ver Reportes</a>
                </div>
            </div>
    </div>
</div>

<div class="text-center mt-5">
        <a href="/trucksisx/views/dashboard.php" class="btn btn-dark"><i class="bi bi-arrow-left"></i> Volver al menú principal</a>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
