<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
$rol_conductor = isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'conductor';
$usuario_nombre = $_SESSION['usuario']['nombre'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Alertas - TruckSISX</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/truck-alerts.css" rel="stylesheet">
    
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

        /* Texto principal: aplicar blanco a elementos claves, preservar clases utilitarias como .text-muted */
        body { color: var(--text-primary); }
        .main-header, .card, .truck-diagram-container, .alerts-dashboard, .btn, .badge, label, .form-label { color: var(--text-primary) !important; }
        .nav .nav-link .fas, .main-header .fas { color: var(--text-primary) !important; }

        /* Títulos específicos en negro para legibilidad: nav tabs y h4 principales */
        .nav .nav-link, .nav .nav-link.active { color: #000 !important; }
        .tab-content h4, .tab-content h3, .truck-diagram-container h3 { color: #000 !important; }
        small.text-muted { color: var(--text-secondary) !important; }

        /* MAIN HEADER */
        .main-header{
            background: linear-gradient(135deg, rgba(15,23,42,0.9) 0%, rgba(17,24,39,0.85) 100%);
            border-radius: 14px;
            box-shadow: 0 18px 50px rgba(2,6,23,0.6);
            color:var(--text-primary);
            margin-bottom:2rem;padding:2rem;position:relative;overflow:hidden
        }
        .main-header::before{content:'';position:absolute;right:-80px;top:-80px;width:260px;height:260px;background:radial-gradient(circle at 20% 20%, rgba(59,130,246,0.08), transparent 40%), radial-gradient(circle at 90% 10%, rgba(249,115,22,0.06), transparent 40%);filter:blur(26px);opacity:0.95}
        .main-header h1{color:var(--text-primary);margin-bottom:0.5rem}
        .main-header .lead{color:var(--text-secondary);opacity:1}

        /* CARDS & CONTAINERS */
        .card, .tab-content .card{
            background:var(--card-bg);border:1px solid rgba(255,255,255,0.03);border-radius:12px;box-shadow:0 8px 30px rgba(2,6,23,0.6);margin-bottom:1.5rem;overflow:hidden
        }

        /* Recuadro completo naranja para el contenedor del diagrama de llantas */
        .truck-diagram-container{
            background: linear-gradient(90deg, var(--accent), #814310ff);
            border:1px solid rgba(249,115,22,0.18);
            border-radius:12px;
            box-shadow:0 8px 30px rgba(249,115,22,0.12);
            margin-bottom:1.5rem;
            overflow:hidden;
            padding:2rem 1.5rem;
            color: #000; /* texto negro sobre naranja para legibilidad */
        }

        /* Título sin fondo (el contenedor ya es naranja) */
        .truck-diagram-container h3 {
            display: block;
            width: 100%;
            background: transparent;
            color: #000 !important;
            padding: 0;
            margin: 0 0 1rem 0;
        }

        /* BUTTONS */
        .btn{border-radius:10px;border:0;cursor:pointer;font-size:.95rem;font-weight:600;min-height:44px;padding:.6rem 1rem}
        .btn:focus{outline:none;box-shadow:0 0 0 3px rgba(249,115,22,0.12)}
        .btn-primary{background:linear-gradient(90deg,var(--accent),#FB923C);color:#fff;box-shadow:0 8px 30px rgba(249,115,22,0.08)}
        .btn-primary:hover{filter:brightness(.98);transform:translateY(-2px);box-shadow:0 18px 40px rgba(249,115,22,0.12)}
        .btn-outline-primary{background:transparent;border:1px solid rgba(255,255,255,0.04);color:var(--text-primary)}
        .btn-light{background:transparent;color:var(--text-secondary);border:1px solid rgba(255,255,255,0.02)}

        /* BADGES */
        .badge{border-radius:20px;font-size:.8rem;font-weight:600;padding:.4rem .75rem}
        .badge.bg-success{background:linear-gradient(90deg,var(--success),#059669);color:#fff}
        .badge.bg-warning{background:linear-gradient(90deg,var(--accent-amber),#D97706);color:#fff}
        .badge.bg-danger{background:linear-gradient(90deg,var(--danger),#DC2626);color:#fff}
        .badge.bg-info{background:linear-gradient(90deg,#475569,#334155);color:#fff}

        /* FORMS */
        .form-control,.form-select{background:transparent;border:1px solid var(--border);border-radius:10px;color:var(--text-primary);padding:.65rem .85rem}
        .form-control::placeholder{color:var(--text-primary) !important;opacity:0.85}
        .form-control:focus,.form-select:focus{border-color:var(--accent);box-shadow:0 10px 30px rgba(249,115,22,0.06);outline:none}
        .form-label{color:var(--text-secondary);font-weight:600;margin-bottom:.5rem}

        /* PERFORMANCE */
        #statistics-table *, #statistics-table{animation:none!important;transition:none!important;transform:none!important}
        .progress-bar{animation:none!important;transition:none!important}

        /* ALERT MODAL */
        .alert-modal{display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.6);backdrop-filter:blur(6px)}
        /* Modal: dark card background matching the system palette */
        .alert-modal-content{
            background: var(--card-bg);
            color: var(--text-primary) !important;
            margin:5% auto;padding:0;border-radius:12px;width:90%;max-width:520px;box-shadow:0 18px 60px rgba(2,6,23,0.6);animation:modalSlideIn .28s ease-out;
        }
        /* Header with subtle orange accent */
        .alert-modal-header{background:linear-gradient(135deg, rgba(15,23,42,0.95), rgba(17,24,39,0.95));color:var(--text-primary);padding:18px;border-radius:12px 12px 0 0;display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid var(--accent)}
        .alert-modal-title{margin:0;font-size:1.1rem;font-weight:700;color:var(--text-primary)}
        .close{color:var(--text-secondary);font-size:22px;font-weight:700;cursor:pointer}
        .close:hover{color:var(--accent)}

        /* Formularios dentro del modal: inputs/selects/textareas usan fondo blanco para legibilidad */
        .alert-modal-content input[type="text"],
        .alert-modal-content input[type="search"],
        .alert-modal-content textarea,
        .alert-modal-content select,
        .alert-modal-content .form-control,
        .alert-modal-content .form-select {
            color: #000 !important;
            background: #ffffff !important;
            border-color: #e6e6e6 !important;
            border-radius: 8px;
        }
        .alert-modal-content select option { color: #000 !important; background: #fff !important }

        /* Placeholder styling inside dark modal */
        .alert-modal-content input::placeholder,
        .alert-modal-content textarea::placeholder {
            color: #666666 !important;
            opacity: 0.85 !important;
        }
        @keyframes modalSlideIn{from{opacity:0;transform:translateY(-30px)}to{opacity:1;transform:none}}
        .alert-modal-header{background:#ffffff;color:#000;padding:18px;border-radius:12px 12px 0 0;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #eee}
        .alert-modal-title{margin:0;font-size:1.1rem;font-weight:700}
        .close{color:#666;font-size:22px;font-weight:700;cursor:pointer}
        .close:hover{color:#000}

        /* ALERT FORM */
        #alert-form{padding:20px}
        /* Etiquetas del modal en negro para mejor legibilidad sobre fondo blanco */
        #alert-form label{color:#000;font-weight:600;margin-bottom:6px}
        #alert-form input[type="text"],#alert-form select,#alert-form textarea{width:100%;padding:10px;border:1px solid #dcdcdc;border-radius:10px;background:#fff;color:#000}
        #alert-form textarea{min-height:90px}
        #alert-form .btn-primary{background:linear-gradient(90deg,var(--accent),#FB923C);color:#fff}
        #alert-form .btn-secondary{background:#374151;color:#fff}

        /* TRUCK DIAGRAM */
        .truck-diagram{width:100%;height:400px;max-width:360px;margin:0 auto;display:block}
        .tire{cursor:pointer;stroke:rgba(0,0,0,0.6);stroke-width:2;transition:all .18s ease}
        .tire:hover{fill:rgba(255,255,255,0.03)!important;stroke:var(--accent);stroke-width:3}
        .tire-label{font-family:Arial,Helvetica,sans-serif;font-size:10px;font-weight:700;text-anchor:middle;fill:#000 !important;pointer-events:none}
        .truck-line{stroke:rgba(255,255,255,0.06);stroke-width:3;fill:none}
        .tire.status-normal{fill:var(--success)}
        .tire.status-warning{fill:var(--accent-amber)}
        .tire.status-critical{fill:var(--danger)}

        /* ACTION BUTTONS */
        .btn-group .btn{margin:2px;box-shadow:0 6px 18px rgba(2,6,23,0.45)}
        .alert-actions{display:flex;gap:.5rem}
        .alert-actions .btn{min-width:86px;padding:.35rem .75rem;font-size:.875rem}

        /* EDIT MODAL */
        .edit-modal{display:none;position:fixed;z-index:1000;inset:0;background:rgba(0,0,0,0.6);backdrop-filter:blur(6px)}
        .edit-modal-content{background:#ffffff;margin:3% auto;padding:0;border-radius:12px;width:92%;max-width:700px;max-height:85vh;overflow:auto;box-shadow:0 18px 60px rgba(2,6,23,0.8)}
        .edit-modal-header{background:#ffffff;color:#000000;padding:18px;border-radius:12px 12px 0 0;position:sticky;top:0;z-index:10;border-bottom:1px solid #eee}
        
        /* Forzar texto negro en todo el contenido del modal de edición */
        .edit-modal-content,
        .edit-modal-content *,
        .edit-modal-content label,
        .edit-modal-content input,
        .edit-modal-content select,
        .edit-modal-content textarea,
        .edit-modal-content option,
        .edit-modal-content .form-control,
        .edit-modal-content .form-select,
        .edit-modal-content h1,
        .edit-modal-content h2,
        .edit-modal-content h3,
        .edit-modal-content h4,
        .edit-modal-content h5,
        .edit-modal-content h6,
        .edit-modal-content p,
        .edit-modal-content div,
        .edit-modal-content span {
            color: #000000 !important;
        }
        
        /* Asegurar fondo blanco para inputs y selects del modal de edición */
        .edit-modal-content input,
        .edit-modal-content select,
        .edit-modal-content textarea,
        .edit-modal-content .form-control,
        .edit-modal-content .form-select {
            background: #ffffff !important;
            color: #000000 !important;
            border: 1px solid #ddd !important;
        }
        
        .edit-modal-content select option {
            background: #ffffff !important;
            color: #000000 !important;
        }
        
        /* Placeholder negro para todos los inputs del modal de edición */
        .edit-modal-content input::placeholder,
        .edit-modal-content textarea::placeholder {
            color: #000000 !important;
            opacity: 0.7 !important;
        }
        
        #edit-alert-form .d-flex{position:sticky;bottom:0;background:transparent;padding:16px 0 0 0;margin-top:20px;border-top:1px solid rgba(255,255,255,0.03)}

        /* Responsive */
        @media (max-width:576px){.alert-actions{justify-content:center}.alert-actions .btn{min-width:70px;font-size:.8rem}.truck-diagram{height:280px}}

        /* Estadísticas por Posición: forzar texto blanco dentro del panel */
        #nav-statistics, #nav-statistics * { color: var(--text-primary) !important; }
        #nav-statistics small.text-muted { color: var(--text-secondary) !important; }
        #nav-statistics h4, #nav-statistics h3 { color: var(--text-primary) !important; }

        /* ===== Ajuste: forzar texto negro y fondos blancos en Estadísticas por Posición ===== */
        /* Forzamos texto negro en todo el panel */
        #nav-statistics, #nav-statistics * { color: #000 !important; }
        #nav-statistics small.text-muted { color: #000 !important; }
        #nav-statistics h4, #nav-statistics h3 { color: #030303ff !important; }
        /* Forzar fondos blancos para el panel y sus contenedores internos sin afectar otros componentes */
        #nav-statistics { background: #ffffff !important; }
        #nav-statistics .card,
        #nav-statistics .card-body,
        #nav-statistics #statistics-summary,
        #nav-statistics #chart-container,
        #nav-statistics #statistics-table { background: #ffffff !important; }
        /* Mantener select visuals pero forzar texto y opciones en negro sobre fondo blanco */
        #chart-type-selector, #data-type-selector, #nav-statistics select.form-select { color: #000 !important; background: #fff !important; }
        #nav-statistics select.form-select option { color: #070707ff !important; background: #fff !important; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="container-fluid py-4">
        <div class="main-header mb-4">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
                <div>
                    <h1 class="mb-2"><i class="fas fa-truck text-accent"></i> Sistema de Alertas</h1>
                    <span class="lead">Monitoreo en tiempo real de alertas para camiones doble troque</span>
                    <div class="mt-2">
                        <small class="text-white-50" id="last-update">
                            <i class="fas fa-clock"></i> Última actualización: <span id="last-update-time">--</span>
                        </small>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <a href="dashboard.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                    <button class="btn btn-primary" id="refresh-alerts">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>
        </div>

    <div class="container">
        <!-- Diagrama del Camión -->
        <div class="row">
            <div class="col-12">
                <div class="truck-diagram-container">
                    <h3 class="text-center mb-3">
                        <i class="fas fa-truck"></i> Camión Doble Troque - Vista Superior
                    </h3>
                    <div id="truck-diagram-container"></div>
                </div>
            </div>
        </div>

        <!-- Tabs para diferentes vistas -->
        <div class="row">
            <div class="col-12">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <button class="nav-link active" id="nav-alerts-tab" data-bs-toggle="tab" 
                                data-bs-target="#nav-alerts" type="button" role="tab" 
                                aria-controls="nav-alerts" aria-selected="true">
                            <i class="fas fa-exclamation-triangle"></i> Alertas Recientes
                        </button>
                        <button class="nav-link" id="nav-statistics-tab" data-bs-toggle="tab" 
                                data-bs-target="#nav-statistics" type="button" role="tab" 
                                aria-controls="nav-statistics" aria-selected="false">
                            <i class="fas fa-chart-bar"></i> Estadísticas por Posición
                        </button>
                        <button class="nav-link" id="nav-work-orders-tab" data-bs-toggle="tab" 
                                data-bs-target="#nav-work-orders" type="button" role="tab" 
                                aria-controls="nav-work-orders" aria-selected="false">
                            <i class="fas fa-clipboard-list"></i> Órdenes de Trabajo<?php if ($rol_conductor): ?> <small>(Solo lectura)</small><?php endif; ?>
                        </button>
                    </div>
                </nav>
                
                <div class="tab-content" id="nav-tabContent">
                    <!-- Tab de Alertas -->
                    <div class="tab-pane fade show active" id="nav-alerts" role="tabpanel" 
                         aria-labelledby="nav-alerts-tab">
                        <div class="mt-3">
                            <h4>Alertas de Llantas Recientes</h4>
                            <div id="alerts-list" class="alerts-dashboard">
                                <!-- Contenedor del filtro y lista: el filtro se inserta por JS. -->
                                <!-- Los cards de alertas se renderizan dentro de #alerts-cards para no sobrescribir el filtro -->
                                <div id="alerts-cards"></div>
                            </div>
                            <!-- Modal para nueva alerta -->
                            <div class="alert-modal" id="alert-modal">
                                <div class="alert-modal-content">
                                    <div class="alert-modal-header">
                                        <span class="alert-modal-title">Nueva Alerta</span>
                                        <span class="close" onclick="closeAlertModal()">&times;</span>
                                    </div>
                                    <form id="alert-form" onsubmit="event.preventDefault(); submitAlertForm();">
                                        <div class="mb-3">
                                            <label for="tipo_alerta" class="form-label">Tipo de Alerta</label>
                                            <select class="form-select" id="tipo_alerta" name="tipo_alerta" required>
                                                <option value="llanta">Llantas</option>
                                                <option value="freno">Frenos</option>
                                                <option value="motor">Motor</option>
                                                <option value="electrico">Eléctrico</option>
                                                <option value="otros">Otros</option>
                                            </select>
                                        </div>
                                        <!-- ...otros campos del formulario existentes... -->
                                        <div class="mb-3">
                                            <label for="descripcion" class="form-label">Descripción</label>
                                            <input type="text" class="form-control" id="descripcion" name="descripcion" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="prioridad" class="form-label">Prioridad</label>
                                            <select class="form-select" id="prioridad" name="prioridad" required>
                                                <option value="critica">Crítica</option>
                                                <option value="alta">Alta</option>
                                                <option value="media">Media</option>
                                                <option value="baja">Baja</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="observaciones" class="form-label">Observaciones</label>
                                            <textarea class="form-control" id="observaciones" name="observaciones"></textarea>
                                        </div>
                                        <!-- Aquí puedes agregar más campos según tu lógica -->
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-secondary" onclick="closeAlertModal()">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Guardar Alerta</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab de Estadísticas -->
                    <div class="tab-pane fade" id="nav-statistics" role="tabpanel" 
                         aria-labelledby="nav-statistics-tab">
                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4>Estadísticas por Posición de Llanta</h4>
                                <div class="btn-group" role="group" aria-label="Tipo de vista">
                                    <input type="radio" class="btn-check" name="view-type" id="chart-view" checked>
                                    <label class="btn btn-outline-primary btn-sm" for="chart-view">
                                        <i class="fas fa-chart-bar"></i> Gráfico
                                    </label>
                                    <input type="radio" class="btn-check" name="view-type" id="table-view">
                                    <label class="btn btn-outline-primary btn-sm" for="table-view">
                                        <i class="fas fa-table"></i> Tabla
                                    </label>
                                    <input type="radio" class="btn-check" name="view-type" id="both-view">
                                    <label class="btn btn-outline-primary btn-sm" for="both-view">
                                        <i class="fas fa-th-large"></i> Ambos
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Controles de filtro -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <select id="chart-type-selector" class="form-select form-select-sm">
                                        <option value="bar">Gráfico de Barras</option>
                                        <option value="doughnut">Gráfico Circular</option>
                                        <option value="line">Gráfico de Líneas</option>
                                        <option value="radar">Gráfico Radar</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select id="data-type-selector" class="form-select form-select-sm">
                                        <option value="all">Todas las Métricas</option>
                                        <option value="total">Solo Total</option>
                                        <option value="active">Solo Activas</option>
                                        <option value="critical">Solo Críticas</option>
                                        <option value="percentages">Solo Porcentajes</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Resumen de estadísticas -->
                            <div class="row mb-4" id="statistics-summary">
                                <!-- Se llenará dinámicamente -->
                            </div>
                            
                            <div id="chart-container" class="row">
                                <div class="col-12">
                                    <div class="card shadow-corporate">
                                        <div class="card-body">
                                            <div style="position: relative; height: 400px;">
                                                <canvas id="tire-statistics-chart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="statistics-table" class="mt-3" style="animation:none;">
                                <!-- Tabla de estadísticas se cargará dinámicamente -->
                            </div>
                        </div>
                    </div>

                    <!-- Tab de Órdenes de Trabajo -->
                    <div class="tab-pane fade" id="nav-work-orders" role="tabpanel" 
                         aria-labelledby="nav-work-orders-tab">
                        <div class="mt-3">
                            <h4>Órdenes de Trabajo Generadas por Alertas<?php if ($rol_conductor): ?> <small class="text-muted">(Solo visualización)</small><?php endif; ?></h4>
                            <?php if (!$rol_conductor): ?>
                            <a href="orden_trabajo.php" class="btn btn-primary mb-3">
                                <i class="fas fa-plus"></i> Ir a Gestión de Órdenes de Trabajo
                            </a>
                            <?php else: ?>
                            <a href="orden_trabajo.php" class="btn btn-outline-primary mb-3">
                                <i class="fas fa-eye"></i> Ver Todas las Órdenes de Trabajo
                            </a>
                            <?php endif; ?>
                            <div id="work-orders-list">
                                <!-- Órdenes de trabajo se cargarán dinámicamente -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/truck-alerts.js?v=4"></script>
    
    <script>
        // Verificar que la versión correcta del JS se cargó (sin imagen de evidencia)
        console.log('TruckSISX Alert System v4.0 cargado - CSS del modal agregado');
        
        // Extender funcionalidad para estadísticas y gráficos
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar dashboard inicial
            loadDashboard();
            
            // Configurar eventos de tabs
            document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(event) {
                    const target = event.target.getAttribute('data-bs-target');
                    if (target === '#nav-statistics') {
                        loadStatistics();
                    } else if (target === '#nav-work-orders') {
                        loadWorkOrders();
                    }
                });
            });
            
            // Configurar evento del botón de actualizar
            document.getElementById('refresh-alerts').addEventListener('click', function() {
                // Agregar animación de loading
                const button = this;
                const icon = button.querySelector('i');
                const originalClass = icon.className;
                
                icon.className = 'fas fa-spinner fa-spin';
                button.disabled = true;
                
                // Recargar datos según el tab activo
                const activeTab = document.querySelector('.nav-link.active').getAttribute('data-bs-target');
                
                Promise.all([
                    loadDashboard(),
                    activeTab === '#nav-statistics' ? loadStatistics() : Promise.resolve(),
                    activeTab === '#nav-work-orders' ? loadWorkOrders() : Promise.resolve()
                ]).finally(() => {
                    // Restaurar botón
                    setTimeout(() => {
                        icon.className = originalClass;
                        button.disabled = false;
                    }, 500);
                });
            });
            
            // Configurar actualización automática cada 30 segundos
            setInterval(function() {
                // Mostrar indicador de actualización automática
                const refreshBtn = document.getElementById('refresh-alerts');
                const icon = refreshBtn.querySelector('i');
                const originalClass = icon.className;
                
                // Cambiar temporalmente el icono para indicar actualización automática
                icon.className = 'fas fa-sync-alt fa-spin';
                icon.style.color = '#28a745'; // Verde para indicar auto-actualización
                
                loadDashboard();
                // También actualizar el tab activo si no es el de alertas principales
                const activeTab = document.querySelector('.nav-link.active');
                if (activeTab) {
                    const target = activeTab.getAttribute('data-bs-target');
                    if (target === '#nav-statistics') {
                        loadStatistics();
                    } else if (target === '#nav-work-orders') {
                        loadWorkOrders();
                    }
                }
                
                // Restaurar el icono después de 1 segundo
                setTimeout(() => {
                    icon.className = originalClass;
                    icon.style.color = '';
                }, 1000);
            }, 30000); // 30 segundos
        });

        // Actualizar timestamp de última actualización
        function updateLastUpdateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('es-ES', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            });
            document.getElementById('last-update-time').textContent = timeString;
        }

        // Cargar dashboard general
        async function loadDashboard() {
            try {
                const response = await fetch(`/trucksisx/controllers/AlertController.php?action=getDashboard`);
                const data = await response.json();
                
                if (data.success) {
                    const dashboard = data.dashboard;
                    displayRecentAlerts(dashboard.recent_alerts);
                    updateLastUpdateTime();
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
            }
        }

        // Mostrar alertas recientes en el dashboard
        function displayRecentAlerts(alerts) {
            const container = document.getElementById('alerts-cards');
            if (!container) return;
            if (!alerts || alerts.length === 0) {
                container.innerHTML = '<p>No hay alertas recientes.</p>';
                return;
            }
            
            // Eliminar duplicados basándose en el ID de la alerta
            const uniqueAlerts = alerts.filter((alert, index, self) => 
                index === self.findIndex(a => a.id === alert.id)
            );
            
            const alertsHTML = uniqueAlerts.map(alert => `
                <div class="card mb-3 shadow-corporate">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-1 text-corporate"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>${alert.descripcion}</h5>
                                <span class="badge bg-secondary mb-1 text-uppercase">${alert.tipo_alerta || 'N/A'}</span>
                                <p class="card-text mb-1">${alert.observaciones || ''}</p>
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> ${alert.fecha_hora || ''}
                                    ${alert.placa ? `| <i class=\"fas fa-truck\"></i> ${alert.placa}` : ''}
                                    ${alert.orden_trabajo ? `| <i class=\"fas fa-clipboard-list text-primary\"></i> Orden: ${alert.orden_trabajo}` : ''}
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-${alert.prioridad === 'alta' || alert.prioridad === 'critica' ? 'danger' : alert.prioridad === 'media' ? 'warning' : 'info'} mb-1 text-uppercase">${alert.prioridad}</span>
                                <br>
                                <span class="badge bg-${alert.estado === 'activa' ? 'danger' : alert.estado === 'en_proceso' ? 'warning' : alert.estado === 'resuelta' ? 'success' : 'secondary'} mt-1 text-uppercase">${alert.estado}</span>
                                <?php if (!$rol_conductor): ?>
                                <br>
                                <div class="d-flex flex-wrap gap-1 mt-2 justify-content-end alert-actions">
                                    <button class="btn btn-sm btn-outline-success" onclick="updateAlertStatus(${alert.id}, 'resuelta')" title="Marcar como resuelta">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="updateAlertStatus(${alert.id}, 'en_proceso')" title="Marcar en proceso">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="updateAlertStatus(${alert.id}, 'activa')" title="Marcar como activa">
                                        <i class="fas fa-exclamation"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary" onclick="openEditModal(${alert.id})" title="Editar alerta">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteAlert(${alert.id})" title="Eliminar alerta">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
            container.innerHTML = alertsHTML;
        }

        // Cargar estadísticas por posición
        async function loadStatistics() {
            try {
                const response = await fetch(`/trucksisx/controllers/AlertController.php?action=getTireAlerts`);
                const data = await response.json();
                
                if (data.success && data.statistics) {
                    // Guardar datos para uso posterior
                    window.currentStatistics = data.statistics;
                    
                    createStatisticsChart(data.statistics);
                    createStatisticsTable(data.statistics);
                    updateLastUpdateTime();
                    
                    // Configurar controles si no se ha hecho
                    if (!window.viewControlsSetup) {
                        setupViewControls();
                        window.viewControlsSetup = true;
                    }
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
                document.getElementById('statistics-summary').innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Error al cargar las estadísticas. Por favor, intente nuevamente.
                        </div>
                    </div>
                `;
            }
        }

        // Crear gráfico de estadísticas mejorado
        function createStatisticsChart(statistics) {
            const ctx = document.getElementById('tire-statistics-chart').getContext('2d');
            
            // Destruir gráfico anterior si existe
            if (window.statisticsChart) {
                window.statisticsChart.destroy();
            }
            
            console.log('Estadísticas para gráfico:', statistics.length, statistics);
            
            const labels = statistics.map(stat => {
                const positions = {
                    'direccion_izquierda': 'Dirección Izquierda',
                    'direccion_derecha': 'Dirección Derecha',
                    'traccion1_izquierda': 'Tracción 1 Izquierda',
                    'traccion1_derecha': 'Tracción 1 Derecha',
                    'traccion1_izquierda2': 'Tracción 1 Izquierda 2',
                    'traccion1_derecha2': 'Tracción 1 Derecha 2',
                    'traccion2_izquierda': 'Tracción 2 Izquierda',
                    'traccion2_derecha': 'Tracción 2 Derecha',
                    'traccion2_izquierda2': 'Tracción 2 Izquierda 2',
                    'traccion2_derecha2': 'Tracción 2 Derecha 2'
                };
                return positions[stat.posicion_llanta] || stat.posicion_llanta;
            });
            
            const chartType = document.getElementById('chart-type-selector').value || 'bar';
            const dataType = document.getElementById('data-type-selector').value || 'all';
            
            // Preparar datasets según el tipo de datos seleccionado
            let datasets = [];
            
            if (dataType === 'all' || dataType === 'total') {
                datasets.push({
                    label: 'Total de Alertas',
                    data: statistics.map(stat => stat.total_alertas),
                    backgroundColor: 'rgba(100, 116, 139, 0.8)',
                    borderColor: 'rgba(100, 116, 139, 1)',
                    borderWidth: 2,
                    borderRadius: 4,
                    tension: 0.4
                });
            }
            
            if (dataType === 'all' || dataType === 'active') {
                datasets.push({
                    label: 'Alertas Activas',
                    data: statistics.map(stat => stat.alertas_activas),
                    backgroundColor: 'rgba(251, 191, 36, 0.8)',
                    borderColor: 'rgba(251, 191, 36, 1)',
                    borderWidth: 2,
                    borderRadius: 4,
                    tension: 0.4
                });
            }
            
            if (dataType === 'all' || dataType === 'critical') {
                datasets.push({
                    label: 'Alertas Críticas',
                    data: statistics.map(stat => stat.alertas_criticas),
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 2,
                    borderRadius: 4,
                    tension: 0.4
                });
            }
            
            if (dataType === 'percentages') {
                datasets = [
                    {
                        label: '% Críticas',
                        data: statistics.map(stat => stat.total_alertas > 0 ? 
                            ((stat.alertas_criticas / stat.total_alertas) * 100).toFixed(1) : 0),
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 2
                    },
                    {
                        label: '% Altas',
                        data: statistics.map(stat => stat.total_alertas > 0 ? 
                            (((stat.alertas_altas || 0) / stat.total_alertas) * 100).toFixed(1) : 0),
                        backgroundColor: 'rgba(245, 101, 101, 0.8)',
                        borderColor: 'rgba(245, 101, 101, 1)',
                        borderWidth: 2
                    },
                    {
                        label: '% Medias',
                        data: statistics.map(stat => stat.total_alertas > 0 ? 
                            (((stat.alertas_medias || 0) / stat.total_alertas) * 100).toFixed(1) : 0),
                        backgroundColor: 'rgba(251, 191, 36, 0.8)',
                        borderColor: 'rgba(251, 191, 36, 1)',
                        borderWidth: 2
                    },
                    {
                        label: '% Bajas',
                        data: statistics.map(stat => stat.total_alertas > 0 ? 
                            (((stat.alertas_bajas || 0) / stat.total_alertas) * 100).toFixed(1) : 0),
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 2
                    }
                ];
            }

            const config = {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Estadísticas de Alertas por Posición de Llanta',
                            font: {
                                size: 16,
                                weight: 'bold'
                            },
                            color: '#374151'
                        },
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#64748b',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.parsed.y || context.parsed;
                                    if (dataType === 'percentages') {
                                        label += '%';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: chartType === 'radar' ? {} : {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    if (dataType === 'percentages') {
                                        return value + '%';
                                    }
                                    return value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 10
                                },
                                maxRotation: 45,
                                minRotation: 0
                            }
                        }
                    },
                    animation: false
                }
            };

            window.statisticsChart = new Chart(ctx, config);
            
            // Crear resumen de estadísticas
            createStatisticsSummary(statistics);
        }

        // Crear resumen de estadísticas
        function createStatisticsSummary(statistics) {
            const container = document.getElementById('statistics-summary');
            
            // Calcular totales
            const totalAlerts = statistics.reduce((sum, stat) => sum + (stat.total_alertas || 0), 0);
            const totalActive = statistics.reduce((sum, stat) => sum + (stat.alertas_activas || 0), 0);
            const totalCritical = statistics.reduce((sum, stat) => sum + (stat.alertas_criticas || 0), 0);
            const totalResolved = totalAlerts - totalActive;
            
            // Encontrar posición con más alertas
            const maxAlerts = statistics.reduce((max, stat) => 
                stat.total_alertas > max.total_alertas ? stat : max, statistics[0] || {});
            
            const positions = {
                'direccion_izquierda': 'Dirección Izquierda',
                'direccion_derecha': 'Dirección Derecha',
                'traccion1_izquierda': 'Tracción 1 Izquierda',
                'traccion1_derecha': 'Tracción 1 Derecha',
                'traccion1_izquierda2': 'Tracción 1 Izquierda 2',
                'traccion1_derecha2': 'Tracción 1 Derecha 2',
                'traccion2_izquierda': 'Tracción 2 Izquierda',
                'traccion2_derecha': 'Tracción 2 Derecha',
                'traccion2_izquierda2': 'Tracción 2 Izquierda 2',
                'traccion2_derecha2': 'Tracción 2 Derecha 2'
            };
            
            container.innerHTML = `
                <div class="col-md-3">
                    <div class="card text-center border-primary">
                        <div class="card-body">
                            <h5 class="card-title text-primary">${totalAlerts}</h5>
                            <p class="card-text">Total de Alertas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-warning">
                        <div class="card-body">
                            <h5 class="card-title text-warning">${totalActive}</h5>
                            <p class="card-text">Alertas Activas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-danger">
                        <div class="card-body">
                            <h5 class="card-title text-danger">${totalCritical}</h5>
                            <p class="card-text">Alertas Críticas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <h5 class="card-title text-success">${totalResolved}</h5>
                            <p class="card-text">Alertas Resueltas</p>
                        </div>
                    </div>
                </div>
            `;
            
            if (maxAlerts && maxAlerts.posicion_llanta) {
                container.innerHTML += `
                    <div class="col-12 mt-3">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Posición con más alertas:</strong> 
                            ${positions[maxAlerts.posicion_llanta] || maxAlerts.posicion_llanta} 
                            (${maxAlerts.total_alertas} alertas)
                        </div>
                    </div>
                `;
            }
        }

        // Crear tabla de estadísticas mejorada
        // Crear tabla de estadísticas mejorada
        function createStatisticsTable(statistics) {
            const container = document.getElementById('statistics-table');
            
            console.log('Estadísticas recibidas:', statistics.length, statistics);
            
            if (statistics.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>No hay estadísticas disponibles.</strong>
                        <p class="mb-0">No se encontraron alertas para generar estadísticas.</p>
                    </div>
                `;
                return;
            }

            const positions = {
                'direccion_izquierda': 'Dirección Izquierda',
                'direccion_derecha': 'Dirección Derecha',
                'traccion1_izquierda': 'Tracción 1 - Izquierda',
                'traccion1_derecha': 'Tracción 1 - Derecha',
                'traccion1_izquierda2': 'Tracción 1 - Izquierda 2',
                'traccion1_derecha2': 'Tracción 1 - Derecha 2',
                'traccion2_izquierda': 'Tracción 2 - Izquierda',
                'traccion2_derecha': 'Tracción 2 - Derecha',
                'traccion2_izquierda2': 'Tracción 2 - Izquierda 2',
                'traccion2_derecha2': 'Tracción 2 - Derecha 2'
            };

            // Ordenar estadísticas por total de alertas (descendente)
            const sortedStatistics = [...statistics].sort((a, b) => b.total_alertas - a.total_alertas);

            const tableHTML = `
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-table"></i> Tabla Detallada de Estadísticas
                            </h6>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="statistics-data-table">
                                <thead style="background: #F97316 !important;">
                                    <tr style="background: #F97316 !important;">
                                        <th class="text-center" style="background: #F97316 !important; color: #ffffff !important; border: 1px solid #E65100 !important;">#</th>
                                        <th style="background: #F97316 !important; color: #ffffff !important; border: 1px solid #E65100 !important;">Posición de Llanta</th>
                                        <th class="text-center" style="background: #F97316 !important; color: #ffffff !important; border: 1px solid #E65100 !important;">Total Alertas</th>
                                        <th class="text-center" style="background: #F97316 !important; color: #ffffff !important; border: 1px solid #E65100 !important;">Estado</th>
                                        <th class="text-center" style="background: #F97316 !important; color: #ffffff !important; border: 1px solid #E65100 !important;">Críticas</th>
                                        <th class="text-center" style="background: #F97316 !important; color: #ffffff !important; border: 1px solid #E65100 !important;">% Críticas</th>
                                        <th class="text-center" style="background: #F97316 !important; color: #ffffff !important; border: 1px solid #E65100 !important;">% Altas</th>
                                        <th class="text-center" style="background: #F97316 !important; color: #ffffff !important; border: 1px solid #E65100 !important;">% Medias</th>
                                        <th class="text-center" style="background: #F97316 !important; color: #ffffff !important; border: 1px solid #E65100 !important;">% Bajas</th>
                                        <th class="text-center" style="background: #F97316 !important; color: #ffffff !important; border: 1px solid #E65100 !important;">Tendencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${sortedStatistics.map((stat, index) => {
                                        const criticalPercentage = stat.total_alertas > 0 ? 
                                            ((stat.alertas_criticas / stat.total_alertas) * 100).toFixed(1) : 0;
                                        const highPercentage = stat.total_alertas > 0 ? 
                                            (((stat.alertas_altas || 0) / stat.total_alertas) * 100).toFixed(1) : 0;
                                        const mediumPercentage = stat.total_alertas > 0 ? 
                                            (((stat.alertas_medias || 0) / stat.total_alertas) * 100).toFixed(1) : 0;
                                        const lowPercentage = stat.total_alertas > 0 ? 
                                            (((stat.alertas_bajas || 0) / stat.total_alertas) * 100).toFixed(1) : 0;
                                        
                                        // Determinar el estado de las alertas activas
                                        const alertStatus = stat.alertas_activas > 0 ? 
                                            'Pendiente' : 
                                            (stat.total_alertas > 0 ? 'Completada' : 'Sin alertas');
                                        
                                        const alertStatusBadge = stat.alertas_activas > 0 ? 
                                            'bg-warning text-dark' : 
                                            (stat.total_alertas > 0 ? 'bg-success' : 'bg-secondary');
                                        
                                        // Determinar tendencia (simulada por prioridad)
                                        const criticalRatio = parseFloat(criticalPercentage);
                                        let trendIcon = '';
                                        let trendClass = '';
                                        
                                        if (criticalRatio > 50) {
                                            trendIcon = '<i class="fas fa-arrow-up"></i>';
                                            trendClass = 'text-danger';
                                        } else if (criticalRatio > 25) {
                                            trendIcon = '<i class="fas fa-minus"></i>';
                                            trendClass = 'text-warning';
                                        } else {
                                            trendIcon = '<i class="fas fa-arrow-down"></i>';
                                            trendClass = 'text-success';
                                        }
                                        
                                        // Determinar clase de fila según criticidad
                                        const rowClass = stat.alertas_criticas > 0 ? 'table-danger' : 
                                                        stat.alertas_activas > 0 ? 'table-warning' : '';
                                            
                                        return `
                                            <tr class="${rowClass}">
                                                <td class="text-center fw-bold">${index + 1}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="tire-indicator me-2" style="width: 12px; height: 12px; border-radius: 50%; background-color: ${stat.alertas_criticas > 0 ? '#dc3545' : stat.alertas_activas > 0 ? '#ffc107' : '#28a745'};"></div>
                                                        <strong>${positions[stat.posicion_llanta] || stat.posicion_llanta}</strong>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary fs-6">${stat.total_alertas}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge ${alertStatusBadge}">${alertStatus}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-danger fs-6">${stat.alertas_criticas}</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-danger" role="progressbar" 
                                                             style="width: ${criticalPercentage}%" 
                                                             aria-valuenow="${criticalPercentage}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            ${criticalPercentage}%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-warning" role="progressbar" 
                                                             style="width: ${highPercentage}%" 
                                                             aria-valuenow="${highPercentage}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            ${highPercentage}%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-info" role="progressbar" 
                                                             style="width: ${mediumPercentage}%" 
                                                             aria-valuenow="${mediumPercentage}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            ${mediumPercentage}%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-success" role="progressbar" 
                                                             style="width: ${lowPercentage}%" 
                                                             aria-valuenow="${lowPercentage}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            ${lowPercentage}%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center ${trendClass}">
                                                    ${trendIcon}
                                                </td>
                                            </tr>
                                        `;
                                    }).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            container.innerHTML = tableHTML;
        }

        // Función para exportar tabla a CSV
        function exportTableToCSV() {
            const table = document.getElementById('statistics-data-table');
            if (!table) return;
            
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = [];
                const cols = rows[i].querySelectorAll('td, th');
                
                for (let j = 0; j < cols.length; j++) {
                    let text = cols[j].innerText.replace(/"/g, '""');
                    row.push('"' + text + '"');
                }
                csv.push(row.join(','));
            }
            
            const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
            const downloadLink = document.createElement('a');
            downloadLink.download = `estadisticas_llantas_${new Date().toISOString().split('T')[0]}.csv`;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }

        // Controlar visibilidad de vistas
        function setupViewControls() {
            const chartView = document.getElementById('chart-view');
            const tableView = document.getElementById('table-view');
            const bothView = document.getElementById('both-view');
            const chartContainer = document.getElementById('chart-container');
            const statisticsTable = document.getElementById('statistics-table');
            
            function updateView() {
                if (chartView.checked) {
                    chartContainer.style.display = 'block';
                    statisticsTable.style.display = 'none';
                } else if (tableView.checked) {
                    chartContainer.style.display = 'none';
                    statisticsTable.style.display = 'block';
                } else if (bothView.checked) {
                    chartContainer.style.display = 'block';
                    statisticsTable.style.display = 'block';
                }
            }
            
            chartView.addEventListener('change', updateView);
            tableView.addEventListener('change', updateView);
            bothView.addEventListener('change', updateView);
            
            // Evento para cambio de tipo de gráfico
            document.getElementById('chart-type-selector').addEventListener('change', function() {
                if (window.currentStatistics) {
                    createStatisticsChart(window.currentStatistics);
                }
            });
            
            // Evento para cambio de tipo de datos
            document.getElementById('data-type-selector').addEventListener('change', function() {
                if (window.currentStatistics) {
                    createStatisticsChart(window.currentStatistics);
                }
            });
        }

        // Cargar órdenes de trabajo
        async function loadWorkOrders() {
            try {
                const response = await fetch('/trucksisx/controllers/AlertController.php?action=getAll');
                const data = await response.json();
                
                if (data.success) {
                    const workOrdersWithAlerts = data.data.filter(alert => alert.orden_trabajo);
                    displayWorkOrders(workOrdersWithAlerts);
                    updateLastUpdateTime();
                }
            } catch (error) {
                console.error('Error loading work orders:', error);
            }
        }

        // Mostrar órdenes de trabajo
        function displayWorkOrders(workOrders) {
            const container = document.getElementById('work-orders-list');
            
            if (workOrders.length === 0) {
                container.innerHTML = '<p>No hay órdenes de trabajo generadas por alertas.</p>';
                return;
            }

            const workOrdersHTML = workOrders.map(order => `
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title">${order.orden_trabajo || 'Orden sin título'}</h5>
                                <p class="card-text">${order.descripcion}</p>
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> ${order.fecha_hora}
                                    ${order.tecnico_nombre ? `| <i class="fas fa-user"></i> ${order.tecnico_nombre}` : ''}
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-${order.prioridad === 'alta' || order.prioridad === 'critica' ? 'danger' : 
                                    order.prioridad === 'media' ? 'warning' : 'info'}">
                                    ${order.prioridad}
                                </span>
                                <br>
                                <span class="badge bg-secondary mt-1">${order.estado}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');

            container.innerHTML = workOrdersHTML;
        }

        // Actualizar estado de alerta
        async function updateAlertStatus(alertId, newStatus) {
            try {
                const formData = new FormData();
                formData.append('id', alertId);
                formData.append('estado', newStatus);
                
                const response = await fetch('/trucksisx/controllers/AlertController.php?action=updateStatus', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Mostrar feedback visual temporal
                    const alertElement = document.querySelector(`[onclick*="${alertId}"]`).closest('.card');
                    if (alertElement) {
                        alertElement.style.borderLeft = '4px solid #28a745';
                        setTimeout(() => {
                            alertElement.style.borderLeft = '';
                        }, 2000);
                    }
                    
                    // Actualizar la vista después de cambiar el estado
                    setTimeout(() => {
                        loadDashboard();
                        
                        // También actualizar las estadísticas si están visibles
                        const activeTab = document.querySelector('.nav-link.active').getAttribute('data-bs-target');
                        if (activeTab === '#nav-statistics') {
                            loadStatistics();
                        } else if (activeTab === '#nav-work-orders') {
                            loadWorkOrders();
                        }
                    }, 500);
                    
                    console.log('Estado de alerta actualizado correctamente');
                } else {
                    console.error('Error al actualizar estado:', data.message);
                    alert('Error al actualizar el estado de la alerta');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión al actualizar el estado');
            }
        }

        // Eliminar alerta
        async function deleteAlert(alertId) {
            // Confirmar eliminación con mensaje detallado
            const confirmMessage = `¿Estás seguro de que deseas eliminar esta alerta?

⚠️ Esta acción:
• Eliminará permanentemente la alerta
• Desvinculará las órdenes de trabajo relacionadas
• No se puede deshacer

¿Continuar con la eliminación?`;
            
            if (!confirm(confirmMessage)) {
                return;
            }

            console.log('Iniciando eliminación de alerta ID:', alertId);

            try {
                const formData = new FormData();
                formData.append('id', alertId);
                
                console.log('Enviando petición DELETE a:', '/trucksisx/controllers/AlertController.php?action=delete');
                
                const response = await fetch('/trucksisx/controllers/AlertController.php?action=delete', {
                    method: 'POST',
                    body: formData
                });
                
                console.log('Respuesta del servidor - Status:', response.status, 'OK:', response.ok);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const responseText = await response.text();
                console.log('Respuesta cruda del servidor:', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('Error parsing JSON:', parseError);
                    console.error('Respuesta no es JSON válido:', responseText);
                    throw new Error('Respuesta del servidor no es JSON válido: ' + responseText);
                }
                
                console.log('Datos parseados:', data);
                
                if (data.success) {
                    console.log('Eliminación exitosa:', data.message);
                    
                    // Mostrar feedback visual temporal
                    const alertElement = document.querySelector(`[onclick*="deleteAlert(${alertId})"]`).closest('.card');
                    if (alertElement) {
                        alertElement.style.borderLeft = '4px solid #dc3545';
                        alertElement.style.opacity = '0.7';
                        
                        // Deshabilitar botones para evitar clics múltiples
                        const buttons = alertElement.querySelectorAll('button');
                        buttons.forEach(btn => btn.disabled = true);
                        
                        // Animación de eliminación
                        setTimeout(() => {
                            alertElement.classList.add('alert-deleting');
                            
                            setTimeout(() => {
                                alertElement.remove();
                            }, 300);
                        }, 1000);
                    }
                    
                    // Actualizar la vista después de eliminar
                    setTimeout(() => {
                        loadDashboard();
                        
                        // También actualizar las estadísticas si están visibles
                        const activeTab = document.querySelector('.nav-link.active').getAttribute('data-bs-target');
                        if (activeTab === '#nav-statistics') {
                            loadStatistics();
                        } else if (activeTab === '#nav-work-orders') {
                            loadWorkOrders();
                        }
                    }, 1500);
                    
                    console.log('Alerta eliminada correctamente');
                } else {
                    console.error('Error al eliminar alerta:', data.message);
                    alert('Error al eliminar la alerta: ' + (data.message || 'Error desconocido'));
                }
            } catch (error) {
                console.error('Error completo:', error);
                alert('Error de conexión al eliminar la alerta: ' + error.message);
            }
        }

        // Función para abrir modal de edición
        async function openEditModal(alertId) {
            try {
                // Cargar nombres de alertas para el select
                const select = document.getElementById('edit-posicion');
                select.innerHTML = '<option value="">Cargando alertas...</option>';
                const nombresResp = await fetch('/trucksisx/api/alerta_nombres.php');
                const nombresData = await nombresResp.json();
                if (nombresData.success && Array.isArray(nombresData.data)) {
                    select.innerHTML = '<option value="">Seleccione una alerta...</option>' +
                        nombresData.data.map(nombre => `<option value="${nombre}">${nombre}</option>`).join('');
                } else {
                    select.innerHTML = '<option value="">No hay alertas registradas</option>';
                }

                // Obtener datos de la alerta
                const response = await fetch(`/trucksisx/controllers/AlertController.php?action=getAlert&id=${alertId}`);
                const data = await response.json();
                if (data.success && data.alert) {
                    const alert = data.alert;
                    document.getElementById('edit-alert-id').value = alert.id;
                    document.getElementById('edit-descripcion').value = alert.descripcion || '';
                    if (select) {
                        select.value = alert.posicion_llanta || '';
                    }
                    document.getElementById('edit-prioridad').value = alert.prioridad || 'baja';
                    document.getElementById('edit-estado').value = alert.estado || 'activa';
                    document.getElementById('edit-observaciones').value = alert.observaciones || '';
                    document.getElementById('editAlertModal').style.display = 'block';
                } else {
                    alert('Error al cargar los datos de la alerta');
                    console.error('Error en respuesta:', data);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión al cargar la alerta');
            }
        }

        // Función para cerrar modal de edición
        function closeEditModal() {
            document.getElementById('editAlertModal').style.display = 'none';
        }

        // Función para guardar cambios de la alerta
        async function saveAlertChanges() {
            try {
                const formData = new FormData();
                formData.append('id', document.getElementById('edit-alert-id').value);
                formData.append('descripcion', document.getElementById('edit-descripcion').value);
                formData.append('posicion_llanta', document.getElementById('edit-posicion').value);
                formData.append('prioridad', document.getElementById('edit-prioridad').value);
                formData.append('estado', document.getElementById('edit-estado').value);
                formData.append('observaciones', document.getElementById('edit-observaciones').value);
                
                const response = await fetch('/trucksisx/controllers/AlertController.php?action=update', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    closeEditModal();
                    loadDashboard();
                    loadStatistics();
                    alert('Alerta actualizada correctamente');
                } else {
                    alert('Error al actualizar la alerta: ' + (data.message || 'Error desconocido'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión al actualizar la alerta');
            }
        }

        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('editAlertModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }
    </script>

    <!-- Modal de edición de alerta -->
    <div id="editAlertModal" class="edit-modal">
        <div class="edit-modal-content">
            <div class="edit-modal-header">
                <h3 class="alert-modal-title m-0">
                    <i class="fas fa-edit"></i> Editar Alerta
                </h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="edit-alert-form" onsubmit="event.preventDefault(); saveAlertChanges();">
                <input type="hidden" id="edit-alert-id">
                
                <div class="form-group">
                    <label for="edit-descripcion">Descripción *</label>
                    <input type="text" id="edit-descripcion" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-posicion">Nombre de la Alerta *</label>
                    <select id="edit-posicion" class="form-select" required>
                        <option value="">Cargando alertas...</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit-prioridad">Prioridad *</label>
                    <select id="edit-prioridad" class="form-select" required>
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                        <option value="critica">Crítica</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit-estado">Estado *</label>
                    <select id="edit-estado" class="form-select" required>
                        <option value="activa">Activa</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="resuelta">Resuelta</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit-observaciones">Observaciones</label>
                    <textarea id="edit-observaciones" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>