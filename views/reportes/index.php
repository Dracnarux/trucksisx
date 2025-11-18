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
        body {
            background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%);
            color: #374151;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            min-height: 100vh;
            padding: 2rem 0;
        }

        .container {
            max-width: 1400px;
        }

        h2 {
            color: #1E3A8A;
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 3rem;
        }

        .card {
            background: #FFFFFF;
            border: 1px solid rgba(209, 213, 219, 0.3);
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(30, 58, 138, 0.15);
        }

        .card-body {
            padding: 2rem;
        }

        .card i {
            color: #3B82F6;
            transition: all 0.3s ease;
        }

        .card:hover i {
            transform: scale(1.1);
            color: #2563EB;
        }

        .card-title {
            color: #1E3A8A;
            font-weight: 600;
            font-size: 1.25rem;
            margin-top: 1rem;
            margin-bottom: 0.75rem;
        }

        .card-text {
            color: #6B7280;
            font-size: 0.95rem;
            line-height: 1.5;
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
            justify-content: center;
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

        .row {
            row-gap: 1.5rem;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4 text-center"><i class="bi bi-bar-chart"></i> Módulo de Reportes</h2>
    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-folder2-open display-4"></i>
                    <h5 class="card-title">Categorias</h5>
                    <p class="card-text">Consulta y descarga el reporte de todas las categorias registradas.</p>
                    <a href="/trucksisx/reportes.php?reporte=categorias" class="btn btn-primary w-100">Ver Reportes</a>
                </div>
            </div>
        </div>
    
    <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-diagram-3 display-4"></i>
                    <h5 class="card-title">SubCategorias</h5>
                    <p class="card-text">Consulta y descarga el reporte de todas las subcategorias registradas.</p>
                    <a href="/trucksisx/reportes.php?reporte=subcategorias" class="btn btn-primary w-100">Ver Reportes</a>
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
