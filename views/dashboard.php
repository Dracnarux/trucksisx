<?php
session_start();

// Validar sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}

$usuario = $_SESSION['usuario'];

// Obtener estadísticas
try {
    require_once '../config/db.php';
    $database = new Database();
    $db = $database->getConnection();
    
    // Stats básicas
    $stmt = $db->query("SELECT COUNT(*) as total FROM regis_vehic");
    $vehiculos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM alert WHERE estado = 'activa'");
    $alertas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM ord_trabj WHERE estado IN ('pendiente', 'en_proceso')");
    $ordenes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM cond");
    $conductores = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Datos para gráfico de tendencia (últimos 12 meses de órdenes)
    $stmt = $db->query("
        SELECT 
            DATE_FORMAT(fecha_ingreso, '%Y-%m') as mes,
            COUNT(*) as total
        FROM ord_trabj 
        WHERE fecha_ingreso >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY mes
        ORDER BY mes ASC
    ");
    $tendencia_ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Top 5 tipos de alertas (por tipo_alerta y posición)
    $stmt = $db->query("
        SELECT 
            CONCAT(tipo_alerta, 
                CASE 
                    WHEN posicion_llanta IS NOT NULL 
                    THEN CONCAT(' - ', REPLACE(posicion_llanta, '_', ' '))
                    ELSE ''
                END
            ) as tipo_falla,
            COUNT(*) as total 
        FROM alert 
        WHERE estado = 'activa'
        GROUP BY tipo_alerta, posicion_llanta
        ORDER BY total DESC 
        LIMIT 5
    ");
    $top_alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular variaciones (simuladas ya que las tablas no tienen campos de fecha consistentes)
    // En producción, estas deberían calcularse con fechas reales
    $var_vehiculos = $vehiculos > 0 ? 5.2 : 0;
    $var_alertas = $alertas > 0 ? -12.4 : 0;
    $var_ordenes = $ordenes > 0 ? 8.1 : 0;
    $var_conductores = $conductores > 0 ? 2.3 : 0;
    
    // Distribución de estados de órdenes (para donut chart)
    $stmt = $db->query("
        SELECT 
            estado,
            COUNT(*) as total
        FROM ord_trabj
        GROUP BY estado
        ORDER BY total DESC
    ");
    $estados_ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular eficiencia operativa (órdenes completadas vs total)
    $stmt = $db->query("
        SELECT 
            (SELECT COUNT(*) FROM ord_trabj WHERE estado = 'completada') as completadas,
            COUNT(*) as total
        FROM ord_trabj
    ");
    $eficiencia = $stmt->fetch(PDO::FETCH_ASSOC);
    $porcentaje_eficiencia = $eficiencia['total'] > 0 ? round(($eficiencia['completadas'] / $eficiencia['total']) * 100) : 0;
    
    // Si no hay datos, usar datos de ejemplo
    if ($vehiculos == 0 && $alertas == 0 && $ordenes == 0 && $conductores == 0) {
        $vehiculos = 10;
        $alertas = 8;
        $ordenes = 9;
        $conductores = 10;
        $var_vehiculos = 5.2;
        $var_alertas = -12.4;
        $var_ordenes = 8.1;
        $var_conductores = 2.3;
        
        // Datos de ejemplo para gráficos
        if (empty($tendencia_ordenes)) {
            $meses = ['2024-01', '2024-02', '2024-03', '2024-04', '2024-05', '2024-06', 
                      '2024-07', '2024-08', '2024-09', '2024-10', '2024-11', '2024-12'];
            $valores = [12, 19, 15, 25, 22, 30, 28, 35, 32, 38, 42, 45];
            foreach ($meses as $i => $mes) {
                $tendencia_ordenes[] = ['mes' => $mes, 'total' => $valores[$i]];
            }
        }
        
        if (empty($top_alertas)) {
            $top_alertas = [
                ['tipo_falla' => 'Llanta - Presión Baja', 'total' => 15],
                ['tipo_falla' => 'Motor - Temperatura Alta', 'total' => 12],
                ['tipo_falla' => 'Frenos - Desgaste', 'total' => 10],
                ['tipo_falla' => 'Llanta - Desgaste', 'total' => 8],
                ['tipo_falla' => 'General - Revisión', 'total' => 5]
            ];
        }
        
        if (empty($estados_ordenes)) {
            $estados_ordenes = [
                ['estado' => 'completada', 'total' => 45],
                ['estado' => 'en_proceso', 'total' => 25],
                ['estado' => 'pendiente', 'total' => 20],
                ['estado' => 'cancelada', 'total' => 10]
            ];
            $porcentaje_eficiencia = 45;
        }
    }
    
} catch (Exception $e) {
    // En caso de error, usar datos de ejemplo
    $vehiculos = 10;
    $alertas = 8;
    $ordenes = 9;
    $conductores = 10;
    $var_vehiculos = 5.2;
    $var_alertas = -12.4;
    $var_ordenes = 8.1;
    $var_conductores = 2.3;
    
    $meses = ['2024-01', '2024-02', '2024-03', '2024-04', '2024-05', '2024-06', 
              '2024-07', '2024-08', '2024-09', '2024-10', '2024-11', '2024-12'];
    $valores = [12, 19, 15, 25, 22, 30, 28, 35, 32, 38, 42, 45];
    $tendencia_ordenes = [];
    foreach ($meses as $i => $mes) {
        $tendencia_ordenes[] = ['mes' => $mes, 'total' => $valores[$i]];
    }
    
    $top_alertas = [
        ['tipo_falla' => 'Llanta - Presión Baja', 'total' => 15],
        ['tipo_falla' => 'Motor - Temperatura Alta', 'total' => 12],
        ['tipo_falla' => 'Frenos - Desgaste', 'total' => 10],
        ['tipo_falla' => 'Llanta - Desgaste', 'total' => 8],
        ['tipo_falla' => 'General - Revisión', 'total' => 5]
    ];
    
    $estados_ordenes = [
        ['estado' => 'completada', 'total' => 45],
        ['estado' => 'en_proceso', 'total' => 25],
        ['estado' => 'pendiente', 'total' => 20],
        ['estado' => 'cancelada', 'total' => 10]
    ];
    
    $porcentaje_eficiencia = 45;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Analítico | TruckSisX</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --bg-primary: #0F172A;
            --bg-secondary: #111827;
            --text-primary: #F1F5F9;
            --text-secondary: #94A3B8;
            --accent: #F97316;
            --accent-hover: #EA580C;
            --warning: #F59E0B;
            --danger: #EF4444;
            --border: #1E293B;
            --shadow: rgba(0, 0, 0, 0.3);
            --shadow-hover: rgba(0, 0, 0, 0.5);
            --sidebar-width: 280px;
            --sidebar-collapsed: 72px;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0A0F1E;
            color: var(--text-primary);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }
        
        .container {
            padding: 32px;
        }
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
        
        .header-left h1 {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .header-right {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        
        .date-display {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .filter-chip {
            padding: 8px 16px;
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 24px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 300ms;
        }
        
        .filter-chip:hover {
            border-color: var(--accent);
            color: var(--accent);
        }
        
        /* KPI Cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .kpi-card {
            background: var(--accent);
            border-radius: 16px;
            padding: 32px 28px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
            transition: all 300ms;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .kpi-card:hover {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6);
            transform: translateY(-4px);
        }
        
        .kpi-card:nth-child(2) {
            background: var(--danger);
        }
        
        .kpi-card:nth-child(3) {
            background: var(--warning);
        }
        
        .kpi-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .kpi-icon {
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            backdrop-filter: blur(10px);
        }
        
        .kpi-change {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
        }
        
        .kpi-change.positive {
            color: var(--accent);
            background: rgba(16, 185, 129, 0.1);
        }
        
        .kpi-change.negative {
            color: #EF4444;
            background: rgba(239, 68, 68, 0.1);
        }
        
        .kpi-value {
            font-size: 56px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 8px;
            color: white;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .kpi-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        
        /* Modules Grid */
        .modules-section {
            margin-bottom: 32px;
        }
        
        .modules-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modules-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        
        .module-card {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            cursor: pointer;
            transition: all 300ms;
            text-decoration: none;
            color: var(--text-primary);
        }

        .module-card:hover {
            border-color: rgba(249,115,22,0.14);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
            transform: translateY(-6px);
            background: rgba(255,255,255,0.02);
        }

        .module-icon svg {
            width: 36px;
            height: 36px;
            stroke: white;
            fill: none;
            stroke-width: 1.8;
            opacity: 0.95;
        }

        .module-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-primary);
            margin-top: 12px;
        }

        .module-desc {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 6px;
            line-height: 1.4;
            max-width: 220px;
        }

        .module-btn {
            margin-top: 18px;
            padding: 10px 18px;
            background: linear-gradient(90deg, var(--accent) 0%, var(--accent-hover) 100%);
            color: white;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 8px 20px rgba(249,115,22,0.20);
            border: none;
        }

        .module-btn.secondary {
            background: rgba(255,255,255,0.03);
            color: var(--text-primary);
            border: 1px solid rgba(255,255,255,0.04);
            box-shadow: none;
            font-weight: 600;
        }
        
        .module-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 14px;
            box-shadow: 0 4px 16px rgba(249, 115, 22, 0.3);
        }
        
        .module-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .module-desc {
            font-size: 11px;
            color: var(--text-secondary);
            line-height: 1.4;
        }
        
        /* Main Layout */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
            margin-bottom: 24px;
        }
        
        .chart-card {
            background: var(--bg-primary);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
            border: 1px solid var(--border);
        }
        
        .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 20px;
        }
        
        .chart-container {
            position: relative;
            height: 280px;
        }
        
        /* Top Categories Panel */
        .categories-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .category-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .category-name {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .category-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-secondary);
        }
        
        .category-bar {
            height: 8px;
            background: var(--border);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .category-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent) 0%, var(--warning) 100%);
            border-radius: 4px;
            transition: width 600ms ease;
            box-shadow: 0 0 12px rgba(249, 115, 22, 0.4);
        }
        
        /* Bottom Grid */
        .bottom-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .gauge-container {
            position: relative;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .gauge-value {
            position: absolute;
            font-size: 32px;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .gauge-label {
            position: absolute;
            bottom: 35px;
            font-size: 12px;
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 2000;
            overflow: hidden;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.5);
        }
        
        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }
        
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 80px;
            background: rgba(249, 115, 22, 0.03);
        }
        
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            white-space: nowrap;
            overflow: hidden;
        }
        
        .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
            box-shadow: 0 4px 16px rgba(249, 115, 22, 0.4);
        }
        
        .brand-text {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            opacity: 1;
            transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1);
            letter-spacing: -0.5px;
        }
        
        .sidebar.collapsed .brand-text {
            opacity: 0;
            width: 0;
            transform: translateX(-20px);
        }
        
        .sidebar-toggle {
            width: 36px;
            height: 36px;
            border: 1px solid var(--border);
            background: var(--bg-primary);
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1);
            flex-shrink: 0;
            font-size: 16px;
            color: var(--text-secondary);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .sidebar-toggle:hover {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
            transform: scale(1.05);
            box-shadow: 0 4px 16px rgba(249, 115, 22, 0.4);
        }
        
        .sidebar-toggle:active {
            transform: scale(0.95);
        }
        
        .sidebar.collapsed .sidebar-toggle {
            transform: rotate(180deg);
        }
        
        .sidebar.collapsed .sidebar-toggle:hover {
            transform: rotate(180deg) scale(1.05);
        }
        
        .sidebar-nav {
            flex: 1;
            padding: 16px 8px;
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }
        
        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: var(--text-secondary);
        }
        
        .nav-section {
            margin-bottom: 28px;
        }
        
        .nav-section-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 16px 12px;
            white-space: nowrap;
            transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0.7;
        }
        
        .sidebar.collapsed .nav-section-title {
            opacity: 0;
            height: 0;
            padding: 0;
            margin-bottom: 8px;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            margin: 0 12px 6px 12px;
            border-radius: 12px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        
        .nav-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 0;
            width: 3px;
            height: 100%;
            background: var(--accent);
            transform: scaleY(0);
            transition: transform 300ms cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 0 2px 2px 0;
        }
        
        .nav-item:hover {
            background: rgba(249, 115, 22, 0.08);
            color: var(--text-primary);
            transform: translateX(4px);
        }
        
        .nav-item.active {
            background: rgba(249, 115, 22, 0.15);
            color: var(--accent);
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(249, 115, 22, 0.2);
        }
        
        .nav-item.active::before {
            transform: scaleY(1);
        }
        
        .nav-item-icon {
            width: 20px;
            height: 20px;
            min-width: 20px;
            min-height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
            transition: transform 300ms;
            overflow: visible;
        }
        
        .nav-item-icon svg {
            width: 100%;
            height: 100%;
            display: block;
        }
        
        .nav-item:hover .nav-item-icon {
            transform: scale(1.1);
        }
        
        .nav-item-text {
            white-space: nowrap;
            transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .sidebar.collapsed .nav-item-text {
            opacity: 0;
            width: 0;
            transform: translateX(-10px);
        }
        
        .sidebar.collapsed .nav-item {
            justify-content: center;
            padding: 12px 8px;
            margin: 0 12px 4px 12px;
        }
        
        .sidebar.collapsed .nav-item:hover {
            transform: translateX(0) scale(1.05);
        }
        
        .nav-badge {
            margin-left: auto;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 12px;
            transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(249, 115, 22, 0.4);
        }
        
        .sidebar.collapsed .nav-badge {
            opacity: 0;
            width: 0;
            padding: 0;
            transform: scale(0);
        }
        
        /* Tooltip para sidebar colapsado */
        .nav-item-tooltip {
            position: absolute;
            left: 100%;
            margin-left: 12px;
            padding: 8px 12px;
            background: var(--text-primary);
            color: white;
            font-size: 13px;
            font-weight: 500;
            border-radius: 6px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 300ms;
            z-index: 3000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .sidebar.collapsed .nav-item:hover .nav-item-tooltip {
            opacity: 1;
        }
        
        .nav-item-tooltip::before {
            content: '';
            position: absolute;
            right: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 6px solid transparent;
            border-right-color: var(--text-primary);
        }
        
        /* Main Content Area */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            transition: margin-left 300ms ease;
        }
        
        .main-wrapper.sidebar-collapsed {
            margin-left: var(--sidebar-collapsed);
        }
        
        /* Top Bar */
        .top-bar {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            padding: 20px 32px;
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.3);
        }
        
        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .breadcrumb-item {
            color: var(--text-secondary);
        }
        
        .breadcrumb-item.active {
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .search-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(30, 41, 59, 0.5);
            padding: 10px 18px;
            border-radius: 12px;
            border: 1px solid var(--border);
            min-width: 320px;
            backdrop-filter: blur(10px);
        }
        
        .search-bar input {
            border: none;
            background: transparent;
            outline: none;
            font-size: 14px;
            width: 100%;
            color: var(--text-primary);
        }
        
        .search-bar input::placeholder {
            color: var(--text-secondary);
        }
        
        .icon-btn {
            width: 40px;
            height: 40px;
            border: 1px solid var(--border);
            background: rgba(30, 41, 59, 0.5);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 300ms;
            position: relative;
            backdrop-filter: blur(10px);
        }
        
        .icon-btn:hover {
            background: rgba(249, 115, 22, 0.15);
            border-color: var(--accent);
            transform: scale(1.05);
        }
        
        .icon-btn .badge-dot {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            background: #EF4444;
            border-radius: 50%;
            border: 2px solid var(--bg-primary);
        }
        
        /* Notifications Panel */
        .notifications-panel {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            width: 380px;
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 2000;
        }
        
        .notifications-panel.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .notifications-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notifications-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .notifications-count {
            background: var(--accent);
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 12px;
        }
        
        .notifications-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .notification-item {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: all 300ms;
            display: flex;
            gap: 12px;
        }
        
        .notification-item:hover {
            background: var(--bg-secondary);
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .notification-icon.warning {
            background: rgba(239, 68, 68, 0.1);
            color: #EF4444;
        }
        
        .notification-icon.success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent);
        }
        
        .notification-icon.info {
            background: rgba(59, 130, 246, 0.1);
            color: #3B82F6;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .notification-text {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.4;
            margin-bottom: 4px;
        }
        
        .notification-time {
            font-size: 11px;
            color: var(--text-secondary);
            opacity: 0.7;
        }
        
        .notifications-footer {
            padding: 12px 20px;
            border-top: 1px solid var(--border);
            text-align: center;
        }
        
        .notifications-footer a {
            font-size: 13px;
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }
        
        .notifications-footer a:hover {
            color: var(--accent-hover);
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 6px 12px 6px 6px;
            border: 1px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            transition: all 300ms;
            position: relative;
        }
        
        .user-menu:hover {
            background: var(--bg-secondary);
        }
        
        .user-menu:hover .user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .user-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 280px;
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 2000;
            overflow: hidden;
        }
        
        .user-dropdown-header {
            padding: 16px;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, transparent 100%);
        }
        
        .user-dropdown-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .user-dropdown-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .user-dropdown-item {
            font-size: 13px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .user-dropdown-item strong {
            color: var(--text-primary);
            font-weight: 500;
        }
        
        .user-dropdown-footer {
            padding: 12px;
        }
        
        .user-dropdown-btn {
            width: 100%;
            padding: 10px 16px;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 300ms;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .user-dropdown-btn:hover {
            background: #FEE2E2;
            border-color: #EF4444;
            color: #EF4444;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(249, 115, 22, 0.3);
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            line-height: 1.2;
        }
        
        .user-role {
            font-size: 11px;
            color: var(--text-secondary);
        }
        
        /* Mobile Overlay */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(4px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 300ms;
            z-index: 1999;
        }
        
        .sidebar-overlay.active {
            opacity: 1;
            pointer-events: all;
        }
        
        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            width: 40px;
            height: 40px;
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
            color: var(--text-primary);
            transition: all 300ms;
        }
        
        .mobile-menu-btn:hover {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width) !important;
            }
            
            .sidebar.collapsed {
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-wrapper {
                margin-left: 0 !important;
            }
            
            .search-bar {
                min-width: 200px;
            }
            
            .mobile-menu-btn {
                display: flex;
            }
        }
        
        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .modules-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 640px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .header-right {
                width: 100%;
                overflow-x: auto;
            }
            
            .kpi-grid {
                grid-template-columns: 1fr;
                gap: 16px;
                margin-bottom: 24px;
            }
            
            .bottom-grid {
                grid-template-columns: 1fr;
            }
            
            .kpi-value {
                font-size: 36px;
            }
            
            .kpi-card {
                padding: 16px;
            }
            
            .header-left h1 {
                font-size: 24px;
            }
            
            .modules-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            
            .module-card {
                padding: 14px;
            }
            
            .module-icon {
                width: 44px;
                height: 44px;
                font-size: 20px;
                margin-bottom: 10px;
            }
            
            .top-bar {
                padding: 12px 16px;
            }
            
            .container {
                padding: 20px 16px;
            }
            
            .search-bar {
                display: none;
            }
            
            .chart-container {
                height: 240px;
            }
            
            .gauge-container {
                height: 160px;
            }
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .kpi-card, .chart-card {
            animation: fadeInUp 600ms ease;
        }
        
        .kpi-card:nth-child(1) { animation-delay: 100ms; }
        .kpi-card:nth-child(2) { animation-delay: 200ms; }
        .kpi-card:nth-child(3) { animation-delay: 300ms; }
        .kpi-card:nth-child(4) { animation-delay: 400ms; }
        
        /* Tooltip */
        .tooltip-custom {
            position: absolute;
            background: var(--text-primary);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            pointer-events: none;
            opacity: 0;
            transition: opacity 300ms;
            z-index: 1000;
        }
        
        /* Welcome Message */
        .welcome-banner {
            position: fixed;
            top: 20px;
            right: 20px;
            max-width: 420px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
            color: white;
            padding: 24px 28px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(249, 115, 22, 0.5);
            z-index: 3000;
            animation: slideInRight 500ms ease;
            display: flex;
            gap: 16px;
            align-items: flex-start;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .welcome-banner.hidden {
            animation: slideOutRight 500ms ease;
            pointer-events: none;
        }
        
        .welcome-icon {
            font-size: 32px;
            flex-shrink: 0;
        }
        
        .welcome-content {
            flex: 1;
        }
        
        .welcome-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        
        .welcome-message {
            font-size: 14px;
            opacity: 0.95;
            line-height: 1.5;
        }
        
        .welcome-time {
            font-size: 12px;
            opacity: 0.8;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .welcome-close {
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 6px;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 300ms;
            flex-shrink: 0;
        }
        
        .welcome-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(450px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(450px);
                opacity: 0;
            }
        }
        
        @media (max-width: 640px) {
            .welcome-banner {
                top: 10px;
                right: 10px;
                left: 10px;
                max-width: none;
            }
            
            @keyframes slideInRight {
                from {
                    transform: translateY(-100px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateY(0);
                    opacity: 1;
                }
                to {
                    transform: translateY(-100px);
                    opacity: 0;
                }
            }
        }
    </style>
</head>
<body>
    <!-- Welcome Banner -->
    <div class="welcome-banner" id="welcomeBanner">
        <div class="welcome-icon">👋</div>
        <div class="welcome-content">
            <div class="welcome-title">¡Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?>!</div>
            <div class="welcome-message">
                <?php
                $hora = date('H');
                if ($hora >= 6 && $hora < 12) {
                    echo "Buenos días. ";
                } elseif ($hora >= 12 && $hora < 19) {
                    echo "Buenas tardes. ";
                } else {
                    echo "Buenas noches. ";
                }
                ?>
                Tu panel está listo para gestionar <?= $vehiculos ?> vehículos.
            </div>
            <div class="welcome-time">
                <span>🕐</span>
                <span><?= date('d M Y, H:i') ?></span>
            </div>
        </div>
        <button class="welcome-close" onclick="closeWelcome()">✕</button>
    </div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <div class="brand-icon">🚚</div>
                <div class="brand-text">TruckSisX</div>
            </div>
            <button class="sidebar-toggle" onclick="toggleSidebar()" title="Colapsar menú">
                <span id="toggleIcon">←</span>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Principal</div>
                <a href="dashboard_minimal.php" class="nav-item active">
                    <span class="nav-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    </span>
                    <span class="nav-item-text">Dashboard</span>
                    <span class="nav-item-tooltip">Dashboard</span>
                </a>
                <a href="truck_alerts.php" class="nav-item">
                    <span class="nav-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    </span>
                    <span class="nav-item-text">Sistema de Alertas</span>
                    <span class="nav-badge"><?= $alertas ?></span>
                    <span class="nav-item-tooltip">Sistema de Alertas (<?= $alertas ?>)</span>
                </a>
                <a href="orden_trabajo.php" class="nav-item">
                    <span class="nav-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                    </span>
                    <span class="nav-item-text">Órdenes de Trabajo</span>
                    <span class="nav-badge"><?= $ordenes ?></span>
                    <span class="nav-item-tooltip">Órdenes de Trabajo (<?= $ordenes ?>)</span>
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Gestiones</div>
                <a href="gestion_vehicular.php" class="nav-item">
                    <span class="nav-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 18h-1.5a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5H18"></path><path d="M6 18H4.5a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5H6"></path><path d="M2 14h20"></path><path d="M22 11V7.414a2 2 0 0 0-.586-1.414l-1.414-1.414A2 2 0 0 0 18.586 4H5.414A2 2 0 0 0 4 4.586L2.586 6A2 2 0 0 0 2 7.414V11"></path><circle cx="6" cy="18" r="2"></circle><circle cx="18" cy="18" r="2"></circle></svg>
                    </span>
                    <span class="nav-item-text">Gestión Vehicular</span>
                    <span class="nav-item-tooltip">Gestión Vehicular</span>
                </a>
                <a href="regis_vehic.php" class="nav-item">
                    <span class="nav-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </span>
                    <span class="nav-item-text">Registro Vehículos</span>
                    <span class="nav-item-tooltip">Registro Vehículos</span>
                </a>
                <a href="cond.php" class="nav-item">
                    <span class="nav-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </span>
                    <span class="nav-item-text">Conductores</span>
                    <span class="nav-item-tooltip">Conductores</span>
                </a>
                <a href="repue.php" class="nav-item">
                    <span class="nav-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                    </span>
                    <span class="nav-item-text">Repuestos</span>
                    <span class="nav-item-tooltip">Repuestos</span>
                </a>
                <a href="proveedor.php" class="nav-item">
                    <span class="nav-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    </span>
                    <span class="nav-item-text">Proveedores</span>
                    <span class="nav-item-tooltip">Proveedores</span>
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Registros</div>
                <a href="salida_vehiculo.php" class="nav-item">
                    <span class="nav-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                    </span>
                    <span class="nav-item-text">Salida Vehículos</span>
                    <span class="nav-item-tooltip">Salida Vehículos</span>
                </a>
                <a href="salida_repuesto.php" class="nav-item">
                    <span class="nav-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    </span>
                    <span class="nav-item-text">Salida Repuestos</span>
                    <span class="nav-item-tooltip">Salida Repuestos</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Configuración</div>
                <a href="cat_vehiculo.php" class="nav-item">
                    <span class="nav-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                    </span>
                    <span class="nav-item-text">Categorías</span>
                    <span class="nav-item-tooltip">Categorías</span>
                </a>
                <a href="crear_usuario.php" class="nav-item">
                    <span class="nav-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                    </span>
                    <span class="nav-item-text">Usuarios</span>
                    <span class="nav-item-tooltip">Usuarios</span>
                </a>
                <a href="../logout.php" class="nav-item">
                    <span class="nav-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    </span>
                    <span class="nav-item-text">Cerrar Sesión</span>
                    <span class="nav-item-tooltip">Cerrar Sesión</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

    <!-- Main Content -->
    <div class="main-wrapper" id="mainWrapper">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="top-bar-left">
                <button class="mobile-menu-btn" onclick="openMobileSidebar()">☰</button>
                <div class="breadcrumb">
                    <span class="breadcrumb-item">TruckSisX</span>
                    <span class="breadcrumb-item">/</span>
                    <span class="breadcrumb-item active">Dashboard</span>
                </div>
            </div>
            <div class="top-bar-right">
                <div class="icon-btn" id="notificationBtn" style="position: relative;">
                    <span>🔔</span>
                    <div class="badge-dot"></div>
                    
                    <!-- Notifications Panel -->
                    <div class="notifications-panel" id="notificationsPanel">
                        <div class="notifications-header">
                            <div class="notifications-title">Notificaciones</div>
                            <div class="notifications-count"><?= $alertas ?></div>
                        </div>
                        <div class="notifications-list">
                            <?php
                            // Obtener últimas 5 alertas
                            try {
                                $stmt = $db->query("
                                    SELECT 
                                        a.*,
                                        v.num_econo
                                    FROM alert a
                                    LEFT JOIN regis_vehic v ON a.id_vehiculo = v.id_vehi
                                    WHERE a.estado = 'activa'
                                    ORDER BY a.fecha_registro DESC
                                    LIMIT 5
                                ");
                                $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (empty($notificaciones)) {
                                    // Notificaciones de ejemplo
                                    $notificaciones = [
                                        [
                                            'tipo_alerta' => 'Presión baja',
                                            'num_econo' => 'V-001',
                                            'posicion_llanta' => 'delantera_izquierda',
                                            'fecha_registro' => date('Y-m-d H:i:s', strtotime('-2 hours'))
                                        ],
                                        [
                                            'tipo_alerta' => 'Temperatura alta',
                                            'num_econo' => 'V-003',
                                            'posicion_llanta' => null,
                                            'fecha_registro' => date('Y-m-d H:i:s', strtotime('-5 hours'))
                                        ],
                                        [
                                            'tipo_alerta' => 'Desgaste',
                                            'num_econo' => 'V-005',
                                            'posicion_llanta' => 'trasera_derecha',
                                            'fecha_registro' => date('Y-m-d H:i:s', strtotime('-1 day'))
                                        ]
                                    ];
                                }
                                
                                foreach ($notificaciones as $notif):
                                    $tiempo = time() - strtotime($notif['fecha_registro']);
                                    if ($tiempo < 3600) {
                                        $tiempo_texto = floor($tiempo / 60) . ' min';
                                    } elseif ($tiempo < 86400) {
                                        $tiempo_texto = floor($tiempo / 3600) . ' h';
                                    } else {
                                        $tiempo_texto = floor($tiempo / 86400) . ' días';
                                    }
                            ?>
                            <div class="notification-item">
                                <div class="notification-icon warning">⚠️</div>
                                <div class="notification-content">
                                    <div class="notification-title"><?= htmlspecialchars($notif['tipo_alerta']) ?></div>
                                    <div class="notification-text">
                                        Vehículo <?= htmlspecialchars($notif['num_econo']) ?>
                                        <?php if ($notif['posicion_llanta']): ?>
                                        - <?= ucfirst(str_replace('_', ' ', $notif['posicion_llanta'])) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notification-time">Hace <?= $tiempo_texto ?></div>
                                </div>
                            </div>
                            <?php 
                                endforeach;
                            } catch (Exception $e) {
                                // Notificación de error
                            ?>
                            <div class="notification-item">
                                <div class="notification-icon info">ℹ️</div>
                                <div class="notification-content">
                                    <div class="notification-title">Sistema</div>
                                    <div class="notification-text">No hay notificaciones disponibles</div>
                                    <div class="notification-time">Ahora</div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="notifications-footer">
                            <a href="truck_alerts.php">Ver todas las alertas →</a>
                        </div>
                    </div>
                </div>
                <?php if ($usuario['rol'] === 'admin'): ?>
                <a href="../system_status.php" class="icon-btn">
                    <span>⚙️</span>
                </a>
                <?php endif; ?>
                <div class="user-menu">
                    <div class="user-avatar">
                        <?= strtoupper(substr($usuario['nombre'], 0, 1)) ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($usuario['nombre']) ?></div>
                        <div class="user-role"><?= htmlspecialchars($usuario['rol']) ?></div>
                    </div>
                    
                    <!-- User Dropdown -->
                    <div class="user-dropdown">
                        <div class="user-dropdown-header">
                            <div class="user-dropdown-title">¡Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?>!</div>
                            <div class="user-dropdown-info">
                                <div class="user-dropdown-item">
                                    <span>👤</span>
                                    <span><strong>Rol:</strong> <?= htmlspecialchars($usuario['rol']) ?></span>
                                </div>
                                <div class="user-dropdown-item">
                                    <span>🕐</span>
                                    <span><strong>Último acceso:</strong> <?= date('d/m/Y H:i:s') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="user-dropdown-footer">
                            <a href="../logout.php" class="user-dropdown-btn">
                                <span>🚪</span>
                                <span>Cerrar Sesión</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- Header -->
            <div class="header">
                <div class="header-left">
                    <h1>Panel de Control</h1>
                </div>
                <div class="header-right">
                    <div class="date-display">
                        <?= date('d M Y') ?>
                    </div>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-header">
                        <div class="kpi-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="1" y="3" width="15" height="13"></rect>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                <circle cx="18.5" cy="18.5" r="2.5"></circle>
                            </svg>
                        </div>
                    </div>
                    <div class="kpi-value"><?= $vehiculos ?></div>
                    <div class="kpi-label">Vehículos</div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-header">
                        <div class="kpi-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                        </div>
                    </div>
                    <div class="kpi-value"><?= $alertas ?></div>
                    <div class="kpi-label">Alertas Activas</div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-header">
                        <div class="kpi-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="9" y1="15" x2="15" y2="15"></line>
                            </svg>
                        </div>
                    </div>
                    <div class="kpi-value"><?= $ordenes ?></div>
                    <div class="kpi-label">Órdenes Pendientes</div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-header">
                        <div class="kpi-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="7" width="20" height="15" rx="2" ry="2"></rect>
                                <polyline points="17 2 12 7 7 2"></polyline>
                            </svg>
                        </div>
                    </div>
                    <div class="kpi-value"><?= $conductores ?></div>
                    <div class="kpi-label">Conductores</div>
                </div>
            </div>

            <!-- Modules Section -->
            <div class="modules-section">
                <div class="modules-header">
                    <h2 class="modules-title">Gestiones Rápidas</h2>
                </div>
                <div class="modules-grid">
                    <a href="truck_alerts.php" class="module-card">
                        <div class="module-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        </div>
                        <div class="module-title">Sistema de Alertas</div>
                        <div class="module-desc">Monitoreo y alertas</div>
                        <span class="module-btn">Acceder</span>
                    </a>
                    
                    <a href="salida_repuesto.php" class="module-card">
                        <div class="module-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                        </div>
                        <div class="module-title">Salida de Repuestos</div>
                        <div class="module-desc">Registra salida de repuestos.</div>
                        <span class="module-btn">Registrar</span>
                    </a>
                    
                    <a href="salida_vehiculo.php" class="module-card">
                        <div class="module-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                        </div>
                        <div class="module-title">Salida de Vehículo</div>
                        <div class="module-desc">Registra salida de vehículos.</div>
                        <span class="module-btn">Registrar</span>
                    </a>
                    
                    <a href="orden_trabajo.php" class="module-card">
                        <div class="module-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                        </div>
                        <div class="module-title">Órdenes de Trabajo</div>
                        <div class="module-desc">Gestiona órdenes del sistema.</div>
                        <span class="module-btn">Ver Órdenes</span>
                    </a>
                    
                    <a href="gestion_vehicular.php" class="module-card">
                        <div class="module-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M18 18h-1.5a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5H18"></path><path d="M6 18H4.5a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5H6"></path><path d="M2 14h20"></path><path d="M22 11V7.414a2 2 0 0 0-.586-1.414l-1.414-1.414A2 2 0 0 0 18.586 4H5.414A2 2 0 0 0 4 4.586L2.586 6A2 2 0 0 0 2 7.414V11"></path><circle cx="6" cy="18" r="2"></circle><circle cx="18" cy="18" r="2"></circle></svg>
                        </div>
                        <div class="module-title">Gestión Vehicular</div>
                        <div class="module-desc">Categorías, vehículos y conductores</div>
                        <span class="module-btn secondary">Acceder</span>
                    </a>
                    
                    <a href="gestiones.php" class="module-card">
                        <div class="module-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                        </div>
                        <div class="module-title">Gestión de Repuestos</div>
                        <div class="module-desc">Categorías, inventario y proveedores</div>
                        <span class="module-btn secondary">Abrir</span>
                    </a>
                    
                    <a href="crear_usuario.php" class="module-card">
                        <div class="module-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                        </div>
                        <div class="module-title">Usuarios</div>
                        <div class="module-desc">Administrar usuarios</div>
                        <span class="module-btn">Gestionar</span>
                    </a>
                    
                    <a href="reportes/index.php" class="module-card">
                        <div class="module-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M11 3v18"></path><path d="M20 3v12"></path><path d="M2 3v6"></path></svg>
                        </div>
                        <div class="module-title">Reportes</div>
                        <div class="module-desc">Generar reportes</div>
                        <span class="module-btn">Ver</span>
                    </a>
                </div>
            </div>

        <!-- Main Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Main Chart -->
            <div class="chart-card">
                <div class="chart-title">Órdenes de Trabajo - Últimos 12 Meses</div>
                <div class="chart-container">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>

            <!-- Top Categories -->
            <div class="chart-card">
                <div class="chart-title">Top 5 Tipos de Alertas</div>
                <div class="categories-list">
                    <?php 
                    $max_value = !empty($top_alertas) ? $top_alertas[0]['total'] : 1;
                    foreach($top_alertas as $alerta): 
                        $percentage = ($alerta['total'] / $max_value) * 100;
                    ?>
                    <div class="category-item">
                        <div class="category-header">
                            <span class="category-name"><?= htmlspecialchars($alerta['tipo_falla']) ?></span>
                            <span class="category-value"><?= $alerta['total'] ?></span>
                        </div>
                        <div class="category-bar">
                            <div class="category-fill" style="width: <?= $percentage ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if(empty($top_alertas)): ?>
                    <div style="text-align: center; color: var(--text-secondary); padding: 40px 0;">
                        Sin alertas activas
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Bottom Grid -->
        <div class="bottom-grid">
            <!-- Donut Chart -->
            <div class="chart-card">
                <div class="chart-title">Distribución de Estados</div>
                <div class="chart-container">
                    <canvas id="donutChart"></canvas>
                </div>
            </div>

            <!-- Gauge Chart -->
            <div class="chart-card">
                <div class="chart-title">Eficiencia Operativa</div>
                <div class="gauge-container">
                    <canvas id="gaugeChart"></canvas>
                    <div class="gauge-value" id="gaugeValue"><?= $porcentaje_eficiencia ?>%</div>
                    <div class="gauge-label">Órdenes Completadas</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuración global de Chart.js
        Chart.defaults.font.family = 'Inter';
        Chart.defaults.color = '#94A3B8';
        
        // Main Chart - Area Line
        const mainCtx = document.getElementById('mainChart');
        const tendenciaData = <?= json_encode($tendencia_ordenes) ?>;
        
        const labels = tendenciaData.length > 0 
            ? tendenciaData.map(d => {
                const [year, month] = d.mes.split('-');
                return new Date(year, month - 1).toLocaleDateString('es', { month: 'short' });
            })
            : ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            
        const values = tendenciaData.length > 0
            ? tendenciaData.map(d => d.total)
            : [12, 19, 15, 25, 22, 30, 28, 35, 32, 38, 42, 45];
        
        new Chart(mainCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Órdenes',
                    data: values,
                    borderColor: '#F97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.15)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#F97316',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1E293B',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' órdenes';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94A3B8', font: { size: 12 } }
                    },
                    y: {
                        grid: { color: 'rgba(30, 41, 59, 0.8)', drawBorder: false },
                        ticks: { color: '#94A3B8', font: { size: 12 } }
                    }
                }
            }
        });
        
        // Donut Chart - Distribución de Estados
        const donutCtx = document.getElementById('donutChart');
        const estadosData = <?= json_encode($estados_ordenes) ?>;
        
        const donutLabels = estadosData.length > 0 
            ? estadosData.map(e => {
                const estados = {
                    'completada': 'Completadas',
                    'en_proceso': 'En Proceso',
                    'pendiente': 'Pendientes',
                    'cancelada': 'Canceladas'
                };
                return estados[e.estado] || e.estado;
            })
            : ['Sin Datos'];
            
        const donutValues = estadosData.length > 0
            ? estadosData.map(e => e.total)
            : [1];
            
        const donutColors = {
            'Completadas': '#10B981',
            'En Proceso': '#3B82F6',
            'Pendientes': '#F59E0B',
            'Canceladas': '#EF4444'
        };
        
        const backgroundColors = donutLabels.map(label => donutColors[label] || '#64748B');
        
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: donutLabels,
                datasets: [{
                    data: donutValues,
                    backgroundColor: backgroundColors,
                    borderWidth: 0,
                    cutout: '75%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: { size: 13, weight: '500' },
                            color: '#94A3B8',
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1E293B',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
        
        // Gauge Chart (Semicircle) - Eficiencia Operativa
        const gaugeCtx = document.getElementById('gaugeChart');
        const eficienciaValue = <?= $porcentaje_eficiencia ?>;
        
        // Determinar color según eficiencia
        let gaugeColor = '#F97316'; // Naranja por defecto
        if (eficienciaValue < 50) {
            gaugeColor = '#EF4444'; // Rojo
        } else if (eficienciaValue < 75) {
            gaugeColor = '#F59E0B'; // Amarillo
        }
        
        // Aplicar color al valor mostrado
        const gaugeValueEl = document.getElementById('gaugeValue');
        gaugeValueEl.style.color = gaugeColor;
        
        new Chart(gaugeCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [eficienciaValue, 100 - eficienciaValue],
                    backgroundColor: [gaugeColor, '#1E293B'],
                    borderWidth: 0,
                    cutout: '80%',
                    circumference: 180,
                    rotation: 270
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
        
        // Animación de barras al cargar
        document.addEventListener('DOMContentLoaded', () => {
            const bars = document.querySelectorAll('.category-fill');
            setTimeout(() => {
                bars.forEach(bar => {
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 100);
                });
            }, 500);
        });
        
        // Toggle Sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainWrapper = document.getElementById('mainWrapper');
            const toggleIcon = document.getElementById('toggleIcon');
            
            sidebar.classList.toggle('collapsed');
            mainWrapper.classList.toggle('sidebar-collapsed');
            
            if (sidebar.classList.contains('collapsed')) {
                toggleIcon.textContent = '→';
                localStorage.setItem('sidebarCollapsed', 'true');
            } else {
                toggleIcon.textContent = '←';
                localStorage.setItem('sidebarCollapsed', 'false');
            }
        }
        
        // Open Mobile Sidebar
        function openMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.add('mobile-open');
            sidebar.classList.remove('collapsed');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        // Close Mobile Sidebar
        function closeMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        // Restaurar estado del sidebar
        window.addEventListener('DOMContentLoaded', () => {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            
            // Solo aplicar estado colapsado en desktop
            if (window.innerWidth > 1200) {
                if (isCollapsed) {
                    document.getElementById('sidebar').classList.add('collapsed');
                    document.getElementById('mainWrapper').classList.add('sidebar-collapsed');
                    document.getElementById('toggleIcon').textContent = '→';
                }
            }
        });
        
        // Cerrar sidebar mobile al hacer clic en un link
        document.addEventListener('DOMContentLoaded', () => {
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.addEventListener('click', () => {
                    if (window.innerWidth <= 1200) {
                        closeMobileSidebar();
                    }
                });
            });
        });
        
        // Manejar resize de ventana
        window.addEventListener('resize', () => {
            if (window.innerWidth > 1200) {
                closeMobileSidebar();
            }
        });
        
        // Welcome Banner Functions
        function closeWelcome() {
            const banner = document.getElementById('welcomeBanner');
            banner.classList.add('hidden');
            localStorage.setItem('welcomeShown_' + new Date().toDateString(), 'true');
            setTimeout(() => {
                banner.style.display = 'none';
            }, 500);
        }
        
        // Auto-close welcome banner después de 8 segundos
        setTimeout(() => {
            const banner = document.getElementById('welcomeBanner');
            if (banner && !banner.classList.contains('hidden')) {
                closeWelcome();
            }
        }, 8000);
        
        // Mostrar banner solo una vez por día
        window.addEventListener('DOMContentLoaded', () => {
            const today = new Date().toDateString();
            const welcomeShown = localStorage.getItem('welcomeShown_' + today);
            
            if (welcomeShown === 'true') {
                const banner = document.getElementById('welcomeBanner');
                if (banner) {
                    banner.style.display = 'none';
                }
            }
        });
        
        // Notifications Panel Toggle
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationsPanel = document.getElementById('notificationsPanel');
        
        notificationBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notificationsPanel.classList.toggle('active');
        });
        
        // Cerrar panel al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!notificationBtn.contains(e.target)) {
                notificationsPanel.classList.remove('active');
            }
        });
        
        // Prevenir que el panel se cierre al hacer clic dentro
        notificationsPanel.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    </script>
    </div>
</body>
</html>
