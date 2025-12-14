<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: /trucksisx/index.php');
    exit();
}

require_once __DIR__ . '/controllers/ReporteController.php';

$controller = new ReporteController();
$reporte = $_GET['reporte'] ?? 'index';

// Procesar filtros desde GET
$filtros = $_GET;

switch ($reporte) {
    // Categorías de repuestos
    case 'categorias':
        $controller->categorias($filtros);
        break;
    case 'descargarCategoriasPDF':
        $controller->descargarCategoriasPDF($filtros);
        break;
    case 'descargarCategoriasExcel':
        $controller->descargarCategoriasExcel($filtros);
        break;
    
    // Categorías de vehículos
    case 'categoriasVehiculos':
        $controller->categoriasVehiculos($filtros);
        break;
    case 'descargarCategoriasVehiculosPDF':
        $controller->descargarCategoriasVehiculosPDF($filtros);
        break;
    case 'descargarCategoriasVehiculosExcel':
        $controller->descargarCategoriasVehiculosExcel($filtros);
        break;
    
    // Subcategorías de repuestos
    case 'subcategorias':
        $controller->subcategorias($filtros);
        break;
    case 'descargarSubcategoriasPDF':
        $controller->descargarSubcategoriasPDF($filtros);
        break;
    case 'descargarSubcategoriasExcel':
        $controller->descargarSubcategoriasExcel($filtros);
        break;
    
    // Subcategorías de vehículos
    case 'subcategoriasVehiculos':
        $controller->subcategoriasVehiculos($filtros);
        break;
    case 'descargarSubcategoriasVehiculosPDF':
        $controller->descargarSubcategoriasVehiculosPDF($filtros);
        break;
    case 'descargarSubcategoriasVehiculosExcel':
        $controller->descargarSubcategoriasVehiculosExcel($filtros);
        break;
    
    // Repuestos
    case 'repuestos':
        $controller->productos($filtros);
        break;
    case 'descargarRepuestosPDF':
        $controller->descargarProductosPDF($filtros);
        break;
    case 'descargarRepuestosExcel':
        $controller->descargarProductosExcel($filtros);
        break;
    
    // Proveedores
    case 'proveedores':
        $controller->proveedores($filtros);
        break;
    case 'descargarProveedoresPDF':
        $controller->descargarProveedoresPDF($filtros);
        break;
    case 'descargarProveedoresExcel':
        $controller->descargarProveedoresExcel($filtros);
        break;
    
    // Usuarios
    case 'usuarios':
        $controller->usuarios($filtros);
        break;
    case 'descargarUsuariosPDF':
        $controller->descargarUsuariosPDF($filtros);
        break;
    case 'descargarUsuariosExcel':
        $controller->descargarUsuariosExcel($filtros);
        break;
    
    // Vehículos
    case 'vehiculos':
        $controller->vehiculos($filtros);
        break;
    case 'descargarVehiculosPDF':
        $controller->descargarVehiculosPDF($filtros);
        break;
    case 'descargarVehiculosExcel':
        $controller->descargarVehiculosExcel($filtros);
        break;
    
    // Conductores
    case 'conductores':
        $controller->conductores($filtros);
        break;
    case 'descargarConductoresPDF':
        $controller->descargarConductoresPDF($filtros);
        break;
    case 'descargarConductoresExcel':
        $controller->descargarConductoresExcel($filtros);
        break;
    
    // Alertas
    case 'alertas':
        $controller->alertas($filtros);
        break;
    case 'descargarAlertasPDF':
        $controller->descargarAlertasPDF($filtros);
        break;
    case 'descargarAlertasExcel':
        $controller->descargarAlertasExcel($filtros);
        break;
    
    // Órdenes de trabajo
    case 'ordenesTrabajo':
        $controller->ordenesTrabajo($filtros);
        break;
    case 'descargarOrdenesTPDF':
        $controller->descargarOrdenesTrabajosPDF($filtros);
        break;
    case 'descargarOrdenesTexcel':
        $controller->descargarOrdenesTrabajosExcel($filtros);
        break;
    
    // Salidas de repuestos
    case 'salidasRepuestos':
        $controller->salidasRepuestos($filtros);
        break;
    case 'descargarSalidasRPDF':
        $controller->descargarSalidasRepuestosPDF($filtros);
        break;
    case 'descargarSalidasRExcel':
        $controller->descargarSalidasRepuestosExcel($filtros);
        break;
    
    // Salidas de vehículos
    case 'salidasVehiculos':
        $controller->salidasVehiculos($filtros);
        break;
    case 'descargarSalidasVPDF':
        $controller->descargarSalidasVehiculosPDF($filtros);
        break;
    case 'descargarSalidasVExcel':
        $controller->descargarSalidasVehiculosExcel($filtros);
        break;
    
    // Índice
    case 'index':
    default:
        include __DIR__ . '/views/reportes/index.php';
        break;
}
