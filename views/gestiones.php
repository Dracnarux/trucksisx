<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Repuestos | TruckSISX</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        h2, h3, h4, h5, h6 {
            color: #1E3A8A;
            font-weight: 600;
        }
        .container-fluid {
            max-width: 1280px;
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
        .main-header h1 {
            color: #fff;
            margin-bottom: 0.5rem;
        }
        .main-header .lead {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .card {
            background: #FFFFFF;
            border: 1px solid rgba(209, 213, 219, 0.3);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .card-header {
            background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%);
            border-bottom: 1px solid #D1D5DB;
            color: #1E3A8A;
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
        .module-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        .module-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border-color: #1E3A8A;
        }
        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #1E3A8A;
            border-radius: 12px 12px 0 0;
        }
        .card-body {
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        .module-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #1E3A8A;
            background: rgba(30, 58, 138, 0.08);
            border: 1px solid rgba(30, 58, 138, 0.15);
        }
        .module-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1E3A8A;
        }
        .module-description {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }
        .module-btn {
            background: #1E3A8A;
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }
        .module-btn:hover {
            background: #3B82F6;
            color: white;
        }
        @media (max-width: 768px) {
            .main-header {
                padding: 1.5rem;
                text-align: center;
            }
            .card-body {
                padding: 1rem;
            }
            .btn {
                font-size: 16px;
                min-height: 44px;
                width: 100%;
            }
            .btn + .btn {
                margin-top: 0.5rem;
            }
        }
        @media (max-width: 576px) {
            .container-fluid {
                padding: 0.5rem;
            }
            h2 {
                font-size: 1.5rem;
            }
            .main-header {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="main-header animate-fade-in mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1 class="h3 mb-1"><i class="fas fa-cogs text-accent"></i> Gestión de Repuestos</h1>
                <p class="mb-0 opacity-75">Centro de control para la administración completa del inventario de repuestos y proveedores</p>
            </div>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <a href="dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </div>
    <div class="card animate-slide-up shadow-corporate mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-gear-wide-connected text-accent"></i> Módulos de Gestión</h5>
        </div>
        <div class="card-body">
            <div class="row g-4 mb-2">
                <!-- Categorías de Repuestos -->
                <div class="col-lg-4 col-md-6">
                    <div class="module-card">
                        <div class="card-body">
                            <div class="module-icon">
                                <i class="bi bi-collection"></i>
                            </div>
                            <h3 class="module-title">Categorías de Repuestos</h3>
                            <p class="module-description">
                                Organiza y clasifica los repuestos por categorías principales. 
                                Gestiona la estructura jerárquica de tu inventario.
                            </p>
                            <a href="cat_repu.php" class="module-btn">
                                <i class="bi bi-arrow-right-circle"></i>
                                Gestionar Categorías
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Subcategorías de Repuestos -->
                <div class="col-lg-4 col-md-6">
                    <div class="module-card">
                        <div class="card-body">
                            <div class="module-icon">
                                <i class="bi bi-diagram-3"></i>
                            </div>
                            <h3 class="module-title">Subcategorías</h3>
                            <p class="module-description">
                                Define subcategorías específicas para una clasificación detallada. 
                                Mejora la organización y búsqueda de repuestos.
                            </p>
                            <a href="subcat_repu.php" class="module-btn">
                                <i class="bi bi-arrow-right-circle"></i>
                                Gestionar Subcategorías
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Repuestos -->
                <div class="col-lg-4 col-md-6">
                    <div class="module-card">
                        <div class="card-body">
                            <div class="module-icon">
                                <i class="bi bi-gear-wide-connected"></i>
                            </div>
                            <h3 class="module-title">Inventario de Repuestos</h3>
                            <p class="module-description">
                                Administra el catálogo completo de repuestos. Registro, edición, 
                                consulta y control de stock de todos los componentes.
                            </p>
                            <a href="repue.php" class="module-btn">
                                <i class="bi bi-arrow-right-circle"></i>
                                Gestionar Inventario
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Proveedores -->
                <div class="col-lg-4 col-md-6">
                    <div class="module-card">
                        <div class="card-body">
                            <div class="module-icon">
                                <i class="bi bi-building"></i>
                            </div>
                            <h3 class="module-title">Red de Proveedores</h3>
                            <p class="module-description">
                                Gestiona tu red de proveedores. Información de contacto, 
                                condiciones comerciales, tiempos de entrega y cobertura.
                            </p>
                            <a href="proveedor.php" class="module-btn">
                                <i class="bi bi-arrow-right-circle"></i>
                                Gestionar Proveedores
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animaciones suaves al cargar
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.module-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(30px)';
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    
                    requestAnimationFrame(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    });
                }, index * 100);
            });
        });

        // Efecto hover mejorado
        document.querySelectorAll('.module-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Atajos de teclado para módulos de repuestos
        document.addEventListener('keydown', function(e) {
            if (e.altKey) {
                switch(e.key) {
                    case '1':
                        e.preventDefault();
                        window.location.href = 'cat_repu.php';
                        break;
                    case '2':
                        e.preventDefault();
                        window.location.href = 'subcat_repu.php';
                        break;
                    case '3':
                        e.preventDefault();
                        window.location.href = 'repue.php';
                        break;
                    case '4':
                        e.preventDefault();
                        window.location.href = 'proveedor.php';
                        break;
                }
            }
        });

        // Mostrar tooltips para atajos
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>
