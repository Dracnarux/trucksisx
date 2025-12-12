<?php
// Controlador de reportes - TruckSISX
// Genera reportes en PDF y Excel para todas las entidades del sistema

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/fpdf/fpdf.php';

class ReporteController
{
    private $db;

    public function __construct() {
        $this->db = conectarDB();
    }
    //vista de subcategorias
    public function subcategorias($filtros = [])
    {
        require_once __DIR__ . '/../models/SubCatRepu.php';
        require_once __DIR__ . '/../models/CatRepu.php';

        $model = new SubCatRepu();
        $categoriasModel = new CatRepu();

        $nombre = $filtros['nombre'] ?? '';
        $categoria = $filtros['categoria'] ?? '';

        // Aplicar filtros a las subcategorías - el modelo espera: nombre, caracteristicas, categoria, tipo
        $result = $model->getAll($nombre, '', $categoria, '');
        $subcategorias = [];
        while ($row = $result->fetch_assoc()) {
            $subcategorias[] = $row;
        }
        
        // Obtener todas las categorías para el filtro
        $resultCat = $categoriasModel->getAll();
        $categorias = [];
        while ($row = $resultCat->fetch_assoc()) {
            $categorias[] = $row;
        }
        
        include __DIR__ . '/../views/reportes/subcategorias.php';
    }

    //vista de repuestos
    public function productos($filtros = [])
    {
        require_once __DIR__ . '/../models/Repue.php';
        require_once __DIR__ . '/../models/CatRepu.php';
        require_once __DIR__ . '/../models/SubCatRepu.php';
        require_once __DIR__ . '/../models/Proveedor.php';

        $categoriaId = $filtros['categoria'] ?? '';
        $subcategoriaId = $filtros['subcategoria'] ?? '';
        $proveedorId = $filtros['proveedor'] ?? '';

        // Obtener repuestos con JOINs para nombres
        $sql = "SELECT r.*, 
                       c.nombre AS categoria, 
                       s.nombre AS subcategoria, 
                       p.nom_proveedor AS proveedor_nombre
                FROM repue r
                LEFT JOIN cat_repu c ON r.cat_repu_id = c.id
                LEFT JOIN subcat_repu s ON r.subcat_repu_id = s.id
                LEFT JOIN proveedor p ON r.proveedor_id = p.id
                WHERE 1=1";
        
        if ($categoriaId) $sql .= " AND r.cat_repu_id = " . intval($categoriaId);
        if ($subcategoriaId) $sql .= " AND r.subcat_repu_id = " . intval($subcategoriaId);
        if ($proveedorId) $sql .= " AND r.proveedor_id = " . intval($proveedorId);
        
        $result = $this->db->query($sql);
        $repuestos = [];
        while ($row = $result->fetch_assoc()) {
            $repuestos[] = $row;
        }
        
        // Obtener opciones para filtros
        $categoriaModel = new CatRepu();
        $subcategoriaModel = new SubCatRepu();
        $proveedorModel = new Proveedor();
        
        $resultCat = $categoriaModel->getAll();
        $categorias = [];
        while ($row = $resultCat->fetch_assoc()) {
            $categorias[] = $row;
        }
        
        $resultSub = $subcategoriaModel->getAll();
        $subcategorias = [];
        while ($row = $resultSub->fetch_assoc()) {
            $subcategorias[] = $row;
        }
        
        $resultProv = $proveedorModel->getAll();
        $proveedores = [];
        while ($row = $resultProv->fetch_assoc()) {
            $proveedores[] = $row;
        }
        
        include __DIR__ . '/../views/reportes/productos.php';
    }

    //vista de proveedores
    public function proveedores($filtros = [])
    {
        require_once __DIR__ . '/../models/Proveedor.php';
        
        $model = new Proveedor();
        
        // Obtener tipos de repuesto únicos para el filtro
        $sqlTipos = "SELECT DISTINCT tip_repuesto FROM proveedor WHERE tip_repuesto IS NOT NULL AND tip_repuesto != '' ORDER BY tip_repuesto";
        $resultTipos = $this->db->query($sqlTipos);
        $tiposRepuesto = [];
        while ($row = $resultTipos->fetch_assoc()) {
            $tiposRepuesto[] = $row;
        }
        
        // Filtrar proveedores
        $filtrosModel = [];
        if (!empty($filtros['nombre'])) $filtrosModel['nom_proveedor'] = $filtros['nombre'];
        if (!empty($filtros['producto'])) $filtrosModel['tip_repuesto'] = $filtros['producto'];
        
        $result = $model->getAll($filtrosModel);
        $proveedores = [];
        while ($row = $result->fetch_assoc()) {
            $proveedores[] = $row;
        }
        
        include __DIR__ . '/../views/reportes/proveedores.php';
    }

    //vista de usuarios 
    public function usuarios($filtros = [])
    {
        require_once __DIR__ . '/../models/User.php';
        $model = new User();
        $usuarios = $model->getAll();
        
        // Aplicar filtros manualmente
        if (!empty($filtros['usuario'])) {
            $usuarios = array_filter($usuarios, function($u) use ($filtros) {
                $nombreCompleto = ($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? '');
                return stripos($nombreCompleto, $filtros['usuario']) !== false;
            });
        }
        if (!empty($filtros['rol'])) {
            $usuarios = array_filter($usuarios, function($u) use ($filtros) {
                return strcasecmp($u['rol'] ?? '', $filtros['rol']) === 0;
            });
        }
        
        //obtener los roles unicos para el filtro
        $usuariosAll = $model->getAll();
        $roles = [];
        foreach ($usuariosAll as $usuario) {
            $rolExiste = false;
            foreach ($roles as $rolExistente) {
                if ($rolExistente['rol'] == $usuario['rol']) {
                    $rolExiste = true;
                    break;
                }
            }
            if (!$rolExiste) {
                $roles[] = ['rol' => $usuario['rol']];
            }
        }
        
        include __DIR__ . '/../views/reportes/usuarios.php';
    }

    //vista de categorias
    public function categorias($filtros = [])
    {
        require_once __DIR__ . '/../models/CatRepu.php';
        $model = new CatRepu();
        $nombre = $filtros['nombre'] ?? '';
        $result = $model->getAll($nombre);
        $categorias = [];
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }
        include __DIR__ . '/../views/reportes/categorias.php';
    }


//descargar pdf de categorias
public function descargarCategoriasPDF($filtros = [])
{
    date_default_timezone_set('america/Bogota');
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../models/CatRepu.php';
    $model = new CatRepu();
    $nombre = $filtros['nombre'] ?? '';
    $result = $model->getAll($nombre);
    $categorias = [];
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
    $pdf = new FPDF();
    $pdf->AddPage();
    //logo de la empresa 
    if (file_exists('public/img/logo.png')) {
        $pdf->Image('public/img/logo.png', 10, 8, 25);
    }
    $pdf->SetFont('Helvetica', 'B', 18);
    $pdf->SetTextColor(40, 80, 180);
    $pdf->Cell(0, 15, utf8_decode('Reporte de Categorías'), 0, 1, 'C');
    $pdf->SetDrawColor(40, 80, 180);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(2);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Helvetica', 'I', 10);
    $pdf->Cell(0, 8, utf8_decode('Generado: ' .date('d/m/Y H:i')), 0, 1, 'R');
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->Cell(0, 8, utf8_decode('Cantidad de registros: ' .count($categorias)), 0, 1, 'L');
    $pdf->Ln(2);
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetFillColor(220, 230, 241);
    $pdf->Cell(70, 10, utf8_decode('Nombre'), 1, 0, 'C', true);
    $pdf->Cell(120, 10, utf8_decode('Características'), 1, 1, 'C', true);
    $pdf->SetFont('Helvetica', '', 10);
    foreach ($categorias as $cat) {
        $nombre = strlen($cat['nombre'] ?? '') > 30 ? substr($cat['nombre'], 0, 27) . '...' : ($cat['nombre'] ?? '');
        $caracteristicas = strlen($cat['caracteristicas'] ?? '') > 50 ? substr($cat['caracteristicas'], 0, 47) . '...' : ($cat['caracteristicas'] ?? '');
        
        $pdf->Cell(70, 8, utf8_decode($nombre), 1, 0, 'L');
        $pdf->Cell(120, 8, utf8_decode($caracteristicas), 1, 1, 'L');
    }
    //pie de pagina
    $pdf->SetY(-25);
    $pdf->SetFont('Helvetica', 'I', 9);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(0, 10, utf8_decode('Sistema de Reportes | Pagina ' . $pdf->PageNo()), 0, 0, 'C');
    header('Content-Type: application/pdf');
    $filename = 'categorias_report_' . date('Ymd_His') . '.pdf';
    header ('Content-Disposition: attachment; filename="' . $filename . '"');
    $pdf->Output('D', $filename);
    exit;

}

//descargar excel de categorias
public function descargarCategoriasExcel($filtros = [])
{
    // Limpiar buffer de salida para evitar problemas con Excel
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    try {
        date_default_timezone_set('America/Bogota');
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../models/CatRepu.php';
        $model = new CatRepu();
        $nombre = $filtros['nombre'] ?? '';
        $result = $model->getAll($nombre);
        $categorias = [];
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Reporte de Categorías');
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('2850B4');
        $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
        $sheet->mergeCells('A2:B2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
        $sheet->setCellValue('A3', 'Cantidad de registros: ' . count($categorias));
        $sheet->mergeCells('A3:B3');
        $sheet->getStyle('A3')->getFont()->setSize(11);
        $sheet->setCellValue('A4', 'Nombre');
        $sheet->setCellValue('B4', 'Caracteristicas');
        $sheet->getStyle('A4:B4')->getFont()->setBold(true);
        $sheet->getStyle('A4:B4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DCE6F1');
        $row = 5;
        foreach ($categorias as $categoria) {
            $sheet->setCellValue('A' . $row, htmlspecialchars($categoria['nombre'] ?? '', ENT_QUOTES, 'UTF-8'));
            $sheet->setCellValue('B' . $row, htmlspecialchars($categoria['caracteristicas'] ?? '', ENT_QUOTES, 'UTF-8'));
            $row++;
        }
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->setAutoFilter('A4:B' . ($row - 1));
        
        // Crear el writer y guardar archivo
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'categorias_report_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
        
    } catch (Exception $e) {
        // En caso de error, mostrar mensaje de error
        header('Content-Type: text/html; charset=utf-8');
        die('Error generando Excel: ' . $e->getMessage());
    }
}

//descargar pdf de subcategorias
public function descargarSubcategoriasPDF($filtros = [])
{
    date_default_timezone_set('America/Bogota');
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../models/SubCatRepu.php';
    require_once __DIR__ . '/../models/CatRepu.php';
    $model = new SubCatRepu();
    $nombre = $filtros['nombre'] ?? '';
    $categoria = $filtros['categoria'] ?? '';
    // Orden: nombre, caracteristicas, categoria, tipo
    $result = $model->getAll($nombre, '', $categoria, '');
    $subcategorias = [];
    while ($row = $result->fetch_assoc()) {
        $subcategorias[] = $row;
    }
    $pdf = new FPDF();
    $pdf->AddPage();
    if (file_exists('public/img/logo.png')) {
        $pdf->Image('public/img/logo.png', 10, 8, 25);
    }
    $pdf->SetFont('Helvetica', 'B', 18);
    $pdf->SetTextColor(40, 80, 180);
    $pdf->Cell(0, 15, utf8_decode('Reporte de Subcategorías'), 0, 1, 'C');
    $pdf->SetDrawColor(40, 80, 180);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(2);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Helvetica', 'I', 10);
    $pdf->Cell(0, 8, utf8_decode('Generado: ' .date('d/m/Y H:i')), 0, 1, 'R');
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->Cell(0, 8, utf8_decode('Cantidad de registros: ' .count($subcategorias)), 0, 1, 'L');
    $pdf->Ln(2);
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetFillColor(220, 241, 220);
    $pdf->Cell(50, 10, utf8_decode('Nombre'), 1, 0, 'C', true);
    $pdf->Cell(70, 10, utf8_decode('Características'), 1, 0, 'C', true);
    $pdf->Cell(70, 10, utf8_decode('Categoría'), 1, 1, 'C', true);
    $pdf->SetFont('Helvetica', '', 10);
    foreach ($subcategorias as $sub) {
        $nombre = strlen($sub['nombre'] ?? '') > 22 ? substr($sub['nombre'], 0, 19) . '...' : ($sub['nombre'] ?? '');
        $caracteristicas = strlen($sub['caracteristicas'] ?? '') > 30 ? substr($sub['caracteristicas'], 0, 27) . '...' : ($sub['caracteristicas'] ?? '');
        $categoria = strlen($sub['categoria_nombre'] ?? '') > 30 ? substr($sub['categoria_nombre'], 0, 27) . '...' : ($sub['categoria_nombre'] ?? '');
        
        $pdf->Cell(50, 8, utf8_decode($nombre), 1, 0, 'L');
        $pdf->Cell(70, 8, utf8_decode($caracteristicas), 1, 0, 'L');
        $pdf->Cell(70, 8, utf8_decode($categoria), 1, 1, 'L');
    }
    //pie de pagina
    $pdf->SetY(-25);
    $pdf->SetFont('Helvetica', 'I', 9);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(0, 10, utf8_decode('Sistema de Reportes | Pagina ' . $pdf->PageNo()), 0, 0, 'C');
    header('Content-Type: application/pdf');
    $filename = 'subcategorias_report_' . date('Ymd_His') . '.pdf';
    header ('Content-Disposition: attachment; filename="' . $filename . '"');
    $pdf->Output('D', $filename);
    exit;
}

//descargar excel de subcategorias
public function descargarSubcategoriasExcel($filtros = [])
{
    // Limpiar buffer de salida para evitar problemas con Excel
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    try {
        date_default_timezone_set('America/Bogota');
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../models/SubCatRepu.php';
        require_once __DIR__ . '/../models/CatRepu.php';
        $model = new SubCatRepu();
        $nombre = $filtros['nombre'] ?? '';
        $categoria = $filtros['categoria'] ?? '';
        // Orden: nombre, caracteristicas, categoria, tipo
        $result = $model->getAll($nombre, '', $categoria, '');
        $subcategorias = [];
        while ($row = $result->fetch_assoc()) {
            $subcategorias[] = $row;
        }
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Reporte de Subcategorías');
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('2850B4');
        $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
        $sheet->mergeCells('A2:C2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
        $sheet->setCellValue('A3', 'Cantidad de registros: ' . count($subcategorias));
        $sheet->mergeCells('A3:C3');
        $sheet->getStyle('A3')->getFont()->setSize(11);
        $sheet->setCellValue('A4', 'Nombre');
        $sheet->setCellValue('B4', 'Caracteristicas');
        $sheet->setCellValue('C4', 'Categoria');
        $sheet->getStyle('A4:C4')->getFont()->setBold(true);
        $sheet->getStyle('A4:C4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DCE6F1');
        $row = 5;
        foreach ($subcategorias as $sub) {
            $sheet->setCellValue('A' . $row, $sub['nombre'] ?? '');
            $sheet->setCellValue('B' . $row, $sub['caracteristicas'] ?? '');
            $sheet->setCellValue('C' . $row, $sub['categoria_nombre'] ?? '');
            $row++;
        }
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->setAutoFilter('A4:C' . ($row - 1));
        
        // Crear el writer y guardar archivo
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'subcategorias_report_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
        
    } catch (Exception $e) {
        // En caso de error, mostrar mensaje de error
        header('Content-Type: text/html; charset=utf-8');
        die('Error generando Excel: ' . $e->getMessage());
    }
}

    // MÉTODOS PARA CATEGORÍAS DE VEHÍCULOS
    //vista de categorias de vehiculos
    public function categoriasVehiculos($filtros = [])
    {
        require_once __DIR__ . '/../models/CatVehiculo.php';
        $model = new CatVehiculo($this->db);
        $nombre = $filtros['nombre'] ?? '';
        $result = $model->getAll($nombre);
        $categorias = [];
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }
        include __DIR__ . '/../views/reportes/categorias_vehiculos.php';
    }

    //descargar pdf de categorias vehiculos
    public function descargarCategoriasVehiculosPDF($filtros = [])
    {
        date_default_timezone_set('america/Bogota');
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../models/CatVehiculo.php';
        $model = new CatVehiculo($this->db);
        $result = $model->getAll();
        $categorias = [];
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }
        $pdf = new FPDF();
        $pdf->AddPage();
        //logo de la empresa 
        if (file_exists('public/img/logo.png')) {
            $pdf->Image('public/img/logo.png', 10, 8, 25);
        }
        $pdf->SetFont('Helvetica', 'B', 18);
        $pdf->SetTextColor(40, 80, 180);
        $pdf->Cell(0, 15, utf8_decode('Reporte de Categorías de Vehículos'), 0, 1, 'C');
        $pdf->SetDrawColor(40, 80, 180);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(2);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Helvetica', 'I', 10);
        $pdf->Cell(0, 8, utf8_decode('Generado: ' .date('d/m/Y H:i')), 0, 1, 'R');
        $pdf->SetFont('Helvetica', '', 11);
        $pdf->Cell(0, 8, utf8_decode('Cantidad de registros: ' .count($categorias)), 0, 1, 'L');
        $pdf->Ln(2);
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetFillColor(220, 230, 241);
        $pdf->Cell(190, 10, utf8_decode('Nombre de Categoría'), 1, 1, 'C', true);
        $pdf->SetFont('Helvetica', '', 10);
        foreach ($categorias as $cat) {
            $nombre = strlen($cat['nombre'] ?? '') > 80 ? substr($cat['nombre'], 0, 77) . '...' : ($cat['nombre'] ?? '');
            $pdf->Cell(190, 8, utf8_decode($nombre), 1, 1, 'L');
        }
        //pie de pagina
        $pdf->SetY(-25);
        $pdf->SetFont('Helvetica', 'I', 9);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->Cell(0, 10, utf8_decode('Sistema de Reportes | Pagina ' . $pdf->PageNo()), 0, 0, 'C');
        header('Content-Type: application/pdf');
        $filename = 'categorias_vehiculos_report_' . date('Ymd_His') . '.pdf';
        header ('Content-Disposition: attachment; filename="' . $filename . '"');
        $pdf->Output('D', $filename);
        exit;
    }

    //descargar excel de categorias vehiculos
    public function descargarCategoriasVehiculosExcel($filtros = [])
    {
        // Limpiar buffer de salida para evitar problemas con Excel
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        try {
            require_once __DIR__ . '/../vendor/autoload.php';
            require_once __DIR__ . '/../models/CatVehiculo.php';
            
            $model = new CatVehiculo($this->db);
            $nombre = $filtros['nombre'] ?? '';
            $result = $model->getAll($nombre);
            $categorias = [];
            while ($row = $result->fetch_assoc()) {
                $categorias[] = $row;
            }
            
            // Crear spreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Configurar encabezados
            $sheet->setTitle('Categorías de Vehículos');
            $sheet->setCellValue('A1', 'REPORTE DE CATEGORÍAS DE VEHÍCULOS');
            $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
            $sheet->setCellValue('A3', 'Total de registros: ' . count($categorias));
            
            // Estilo para el título
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A2:A3')->getFont()->setBold(true);
            
            // Encabezados de tabla
            $sheet->setCellValue('A4', 'Nombre');
            $sheet->getStyle('A4')->getFont()->setBold(true);
            $sheet->getStyle('A4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFCCCCCC');
            
            // Llenar datos
            $row = 5;
            foreach ($categorias as $categoria) {
                $sheet->setCellValue('A' . $row, htmlspecialchars($categoria['nombre'] ?? '', ENT_QUOTES, 'UTF-8'));
                $row++;
            }
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->setAutoFilter('A4:A' . ($row - 1));
            
            // Crear el writer y guardar archivo
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'categorias_vehiculos_report_' . date('Ymd_His') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $writer->save('php://output');
            exit;
            
        } catch (Exception $e) {
            // En caso de error, mostrar mensaje de error
            header('Content-Type: text/html; charset=utf-8');
            die('Error generando Excel: ' . $e->getMessage());
        }
    }

    // MÉTODOS PARA SUBCATEGORÍAS DE VEHÍCULOS
    //vista de subcategorias de vehiculos
    public function subcategoriasVehiculos($filtros = [])
    {
        require_once __DIR__ . '/../models/SubCatVehiculo.php';
        require_once __DIR__ . '/../models/CatVehiculo.php';

        $model = new SubCatVehiculo($this->db);
        $categoriasModel = new CatVehiculo($this->db);

        $categoria = $filtros['categoria'] ?? '';

        // Aplicar filtros a las subcategorías
        $result = $model->getAll($categoria);
        $subcategorias = [];
        while ($row = $result->fetch_assoc()) {
            $subcategorias[] = $row;
        }
        
        // Obtener todas las categorías para el filtro
        $resultCat = $categoriasModel->getAll();
        $categorias = [];
        while ($row = $resultCat->fetch_assoc()) {
            $categorias[] = $row;
        }
        
        include __DIR__ . '/../views/reportes/subcategorias_vehiculos.php';
    }

    //descargar pdf de subcategorias vehiculos
    public function descargarSubcategoriasVehiculosPDF($filtros = [])
    {
        date_default_timezone_set('America/Bogota');
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../models/SubCatVehiculo.php';
        require_once __DIR__ . '/../models/CatVehiculo.php';
        $model = new SubCatVehiculo($this->db);
        $categoria = $filtros['categoria'] ?? '';
        $result = $model->getAll($categoria);
        $subcategorias = [];
        while ($row = $result->fetch_assoc()) {
            $subcategorias[] = $row;
        }
        $pdf = new FPDF();
        $pdf->AddPage();
        if (file_exists('public/img/logo.png')) {
            $pdf->Image('public/img/logo.png', 10, 8, 25);
        }
        $pdf->SetFont('Helvetica', 'B', 18);
        $pdf->SetTextColor(40, 80, 180);
        $pdf->Cell(0, 15, utf8_decode('Reporte de Subcategorías de Vehículos'), 0, 1, 'C');
        $pdf->SetDrawColor(40, 80, 180);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(2);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Helvetica', 'I', 10);
        $pdf->Cell(0, 8, utf8_decode('Generado: ' .date('d/m/Y H:i')), 0, 1, 'R');
        $pdf->SetFont('Helvetica', '', 11);
        $pdf->Cell(0, 8, utf8_decode('Cantidad de registros: ' .count($subcategorias)), 0, 1, 'L');
        $pdf->Ln(2);
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetFillColor(220, 241, 220);
        $pdf->Cell(95, 10, utf8_decode('Nombre'), 1, 0, 'C', true);
        $pdf->Cell(95, 10, utf8_decode('Categoría'), 1, 1, 'C', true);
        $pdf->SetFont('Helvetica', '', 10);
        foreach ($subcategorias as $subcat) {
            $nombre = strlen($subcat['nombre'] ?? '') > 40 ? substr($subcat['nombre'], 0, 37) . '...' : ($subcat['nombre'] ?? '');
            $categoria = strlen($subcat['categoria'] ?? '') > 40 ? substr($subcat['categoria'], 0, 37) . '...' : ($subcat['categoria'] ?? '');
            
            $pdf->Cell(95, 8, utf8_decode($nombre), 1, 0, 'L');
            $pdf->Cell(95, 8, utf8_decode($categoria), 1, 1, 'L');
        }
        //pie de pagina
        $pdf->SetY(-25);
        $pdf->SetFont('Helvetica', 'I', 9);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->Cell(0, 10, utf8_decode('Sistema de Reportes | Pagina ' . $pdf->PageNo()), 0, 0, 'C');
        header('Content-Type: application/pdf');
        $filename = 'subcategorias_vehiculos_report_' . date('Ymd_His') . '.pdf';
        header ('Content-Disposition: attachment; filename="' . $filename . '"');
        $pdf->Output('D', $filename);
        exit;
    }

    //descargar excel de subcategorias vehiculos
    public function descargarSubcategoriasVehiculosExcel($filtros = [])
    {
        // Limpiar buffer de salida para evitar problemas con Excel
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        try {
            require_once __DIR__ . '/../vendor/autoload.php';
            require_once __DIR__ . '/../models/SubCatVehiculo.php';
            require_once __DIR__ . '/../models/CatVehiculo.php';
            
            $model = new SubCatVehiculo($this->db);
            $categoria = $filtros['categoria'] ?? '';
            $result = $model->getAll($categoria);
            $subcategorias = [];
            while ($row = $result->fetch_assoc()) {
                $subcategorias[] = $row;
            }
            
            // Crear spreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Configurar encabezados
            $sheet->setTitle('Subcategorías de Vehículos');
            $sheet->setCellValue('A1', 'REPORTE DE SUBCATEGORÍAS DE VEHÍCULOS');
            $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
            $sheet->setCellValue('A3', 'Total de registros: ' . count($subcategorias));
            
            // Estilo para el título
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A2:A3')->getFont()->setBold(true);
            
            // Encabezados de tabla
            $sheet->setCellValue('A4', 'Nombre');
            $sheet->setCellValue('B4', 'Categoría');
            $sheet->getStyle('A4:B4')->getFont()->setBold(true);
            $sheet->getStyle('A4:B4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFCCCCCC');
            
            // Llenar datos
            $row = 5;
            foreach ($subcategorias as $subcategoria) {
                $sheet->setCellValue('A' . $row, htmlspecialchars($subcategoria['nombre'] ?? '', ENT_QUOTES, 'UTF-8'));
                $sheet->setCellValue('B' . $row, htmlspecialchars($subcategoria['categoria'] ?? '', ENT_QUOTES, 'UTF-8'));
                $row++;
            }
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->setAutoFilter('A4:B' . ($row - 1));
            
            // Crear el writer y guardar archivo
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'subcategorias_vehiculos_report_' . date('Ymd_His') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $writer->save('php://output');
            exit;
            
        } catch (Exception $e) {
            // En caso de error, mostrar mensaje de error
            header('Content-Type: text/html; charset=utf-8');
            die('Error generando Excel: ' . $e->getMessage());
        }
    }


//descargar pdf de repuestos
public function descargarProductosPDF($filtros = [])
{
    // Limpiar todos los buffers de salida para evitar problemas con PDF
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Deshabilitar la salida de errores para evitar que interfieran con el PDF
    error_reporting(0);
    
    try {
        date_default_timezone_set('America/Bogota');
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../models/SubCatRepu.php';
        require_once __DIR__ . '/../models/Proveedor.php';
        require_once __DIR__ . '/../models/Repue.php';
        require_once __DIR__ . '/../models/CatRepu.php';
        
        // Obtener repuestos con JOINs para nombres
        $sql = "SELECT r.*, 
                       c.nombre AS categoria, 
                       s.nombre AS subcategoria, 
                       p.nom_proveedor AS proveedor_nombre
                FROM repue r
                LEFT JOIN cat_repu c ON r.cat_repu_id = c.id
                LEFT JOIN subcat_repu s ON r.subcat_repu_id = s.id
                LEFT JOIN proveedor p ON r.proveedor_id = p.id
                WHERE 1=1";
        
        if (!empty($filtros['categoria'])) $sql .= " AND r.cat_repu_id = " . intval($filtros['categoria']);
        if (!empty($filtros['subcategoria'])) $sql .= " AND r.subcat_repu_id = " . intval($filtros['subcategoria']);
        if (!empty($filtros['proveedor'])) $sql .= " AND r.proveedor_id = " . intval($filtros['proveedor']);
        
        $result = $this->db->query($sql);
        $repuestos = [];
        while ($row = $result->fetch_assoc()) {
            $repuestos[] = $row;
        }
        
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Logo
        if (file_exists('public/img/logo.png')) {
            $pdf->Image('public/img/logo.png', 10, 8, 25);
        }
        
        // Título principal con tema verde
        $pdf->SetFont('Helvetica', 'B', 20);
        $pdf->SetTextColor(34, 139, 34); // Verde bosque
        $pdf->Cell(0, 20, utf8_decode('REPORTE DE REPUESTOS'), 0, 1, 'C');
        
        // Línea decorativa
        $pdf->SetDrawColor(34, 139, 34);
        $pdf->SetLineWidth(1);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(8);
        
        // Información del reporte
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('INFORMACIÓN DEL REPORTE'), 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->Cell(0, 6, utf8_decode('Fecha de generación: ' . date('d/m/Y H:i')), 0, 1, 'L');
        $pdf->Cell(0, 6, utf8_decode('Total de registros: ' . count($repuestos)), 0, 1, 'L');
        $pdf->Ln(3);
        
        // Filtros aplicados
        if (!empty($filtros['categoria']) || !empty($filtros['subcategoria']) || !empty($filtros['proveedor'])) {
            $pdf->SetFont('Helvetica', 'B', 12);
            $pdf->Cell(0, 8, utf8_decode('FILTROS APLICADOS'), 0, 1, 'L');
            $pdf->SetFont('Helvetica', '', 10);
            
            if (!empty($filtros['categoria'])) {
                $categoriaModel = new CatRepu();
                $categoria = $categoriaModel->getById($filtros['categoria']);
                $pdf->Cell(0, 6, utf8_decode('• Categoría: ' . ($categoria ? $categoria['nombre'] : 'N/A')), 0, 1, 'L');
            }
            
            if (!empty($filtros['subcategoria'])) {
                $subcategoriaModel = new SubCatRepu();
                $subcategoria = $subcategoriaModel->getById($filtros['subcategoria']);
                $pdf->Cell(0, 6, utf8_decode('• Subcategoría: ' . ($subcategoria ? $subcategoria['nombre'] : 'N/A')), 0, 1, 'L');
            }
            
            if (!empty($filtros['proveedor'])) {
                $proveedorModel = new Proveedor();
                $proveedor = $proveedorModel->getById($filtros['proveedor']);
                $pdf->Cell(0, 6, utf8_decode('• Proveedor: ' . ($proveedor ? $proveedor['nombre'] : 'N/A')), 0, 1, 'L');
            }
            
            $pdf->Ln(3);
        }
        
        // Tabla de datos
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('LISTADO DE REPUESTOS'), 0, 1, 'L');
        $pdf->Ln(2);
        
        // Encabezados de tabla - Ajustando anchos para que quepan en la página
        $pdf->SetFillColor(220, 255, 220); // Verde claro
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Helvetica', 'B', 9);
        
        // Configurar bordes más delgados
        $pdf->SetLineWidth(0.3);
        $pdf->SetDrawColor(150, 150, 150); // Color gris claro para los bordes
        
        $pdf->Cell(48, 8, utf8_decode('Nombre'), 1, 0, 'C', true);
        $pdf->Cell(48, 8, utf8_decode('Categoría'), 1, 0, 'C', true);
        $pdf->Cell(48, 8, utf8_decode('Subcategoría'), 1, 0, 'C', true);
        $pdf->Cell(46, 8, utf8_decode('Proveedor'), 1, 1, 'C', true);
        
        // Datos de la tabla
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetFillColor(249, 255, 249); // Verde muy claro alternado
        $contador = 0;
        
        foreach ($repuestos as $prod) {
            $fill = ($contador % 2 == 0);
            
            // Truncar texto si es muy largo
            $nombre = strlen($prod['nombre'] ?? '') > 22 ? substr($prod['nombre'], 0, 19) . '...' : ($prod['nombre'] ?? '');
            $categoria = strlen($prod['categoria'] ?? '') > 22 ? substr($prod['categoria'], 0, 19) . '...' : ($prod['categoria'] ?? 'Sin categoría');
            $subcategoria = strlen($prod['subcategoria'] ?? '') > 22 ? substr($prod['subcategoria'], 0, 19) . '...' : ($prod['subcategoria'] ?? 'Sin subcategoría');
            $proveedor = strlen($prod['proveedor_nombre'] ?? '') > 21 ? substr($prod['proveedor_nombre'], 0, 18) . '...' : ($prod['proveedor_nombre'] ?? 'Sin proveedor');
            
            $pdf->Cell(48, 7, utf8_decode($nombre), 1, 0, 'L', $fill);
            $pdf->Cell(48, 7, utf8_decode($categoria), 1, 0, 'L', $fill);
            $pdf->Cell(48, 7, utf8_decode($subcategoria), 1, 0, 'L', $fill);
            $pdf->Cell(46, 7, utf8_decode($proveedor), 1, 1, 'L', $fill);
            
            $contador++;
            
            // Salto de página si es necesario
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
                // Repetir encabezados
                $pdf->SetFillColor(220, 255, 220);
                $pdf->SetFont('Helvetica', 'B', 9);
                
                // Mantener configuración de bordes delgados
                $pdf->SetLineWidth(0.3);
                $pdf->SetDrawColor(150, 150, 150);
                
                $pdf->Cell(45, 8, utf8_decode('Nombre'), 1, 0, 'C', true);
                $pdf->Cell(35, 8, utf8_decode('Categoría'), 1, 0, 'C', true);
                $pdf->Cell(35, 8, utf8_decode('Subcategoría'), 1, 0, 'C', true);
                $pdf->Cell(40, 8, utf8_decode('Proveedor'), 1, 0, 'C', true);
                $pdf->Cell(25, 8, utf8_decode('Precio'), 1, 1, 'C', true);
                $pdf->SetFont('Helvetica', '', 8);
            }
        }
        
        // Pie de página
        $pdf->SetY(-25);
        $pdf->SetFont('Helvetica', 'I', 9);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->Cell(0, 10, utf8_decode('Sistema de Reportes - Repuestos | Página ' . $pdf->PageNo()), 0, 0, 'C');
    
        // Headers para descarga
        header('Content-Type: application/pdf');
        $filename = 'repuestos_reporte_' . date('Ymd_His') . '.pdf';
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $pdf->Output('D', $filename);
        exit;
        
    } catch (Exception $e) {
        // En caso de error, mostrar mensaje de error
        header('Content-Type: text/html; charset=utf-8');
        die('Error generando PDF: ' . $e->getMessage());
    }
}

//descargar excel de repuestos
public function descargarProductosExcel($filtros = [])
{
    // Limpiar buffer de salida para evitar problemas con Excel
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    try {
        date_default_timezone_set('America/Bogota');
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../models/Repue.php';
    
    // Obtener repuestos con JOINs para nombres
    $sql = "SELECT r.*, 
                   c.nombre AS categoria, 
                   s.nombre AS subcategoria, 
                   p.nom_proveedor AS proveedor_nombre
            FROM repue r
            LEFT JOIN cat_repu c ON r.cat_repu_id = c.id
            LEFT JOIN subcat_repu s ON r.subcat_repu_id = s.id
            LEFT JOIN proveedor p ON r.proveedor_id = p.id
            WHERE 1=1";
    
    if (!empty($filtros['categoria'])) $sql .= " AND r.cat_repu_id = " . intval($filtros['categoria']);
    if (!empty($filtros['subcategoria'])) $sql .= " AND r.subcat_repu_id = " . intval($filtros['subcategoria']);
    if (!empty($filtros['proveedor'])) $sql .= " AND r.proveedor_id = " . intval($filtros['proveedor']);
    
    $result = $this->db->query($sql);
    $repuestos = [];
    while ($row = $result->fetch_assoc()) {
        $repuestos[] = $row;
    }
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Reporte de Repuestos');
    $sheet->mergeCells('A1:D1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('2850B4');
    $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
    $sheet->mergeCells('A2:D2');
    $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
    $sheet->setCellValue('A3', 'Cantidad de registros: ' . count($repuestos));
    $sheet->mergeCells('A3:D3');
    $sheet->getStyle('A3')->getFont()->setSize(11);
    $sheet->setCellValue('A4', 'Nombre');
    $sheet->setCellValue('B4', 'Categoria');
    $sheet->setCellValue('C4', 'Subcategoria');
    $sheet->setCellValue('D4', 'Proveedor');
    $sheet->getStyle('A4:D4')->getFont()->setBold(true);
    $sheet->getStyle('A4:D4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()->setRGB('F1ECDC');
    $row = 5;
    foreach ($repuestos as $prod) {
        $sheet->setCellValue('A' . $row, $prod['nombre'] ?? '');
        $sheet->setCellValue('B' . $row, $prod['categoria'] ?? '');
        $sheet->setCellValue('C' . $row, $prod['subcategoria'] ?? '');
        $sheet->setCellValue('D' . $row, $prod['proveedor_nombre'] ?? '');
        $row++;
    }
    $sheet->getColumnDimension('A')->setAutoSize(true);
    $sheet->getColumnDimension('B')->setAutoSize(true);
    $sheet->getColumnDimension('C')->setAutoSize(true);
    $sheet->getColumnDimension('D')->setAutoSize(true);
    $sheet->setAutoFilter('A4:D' . ($row - 1));
    $filename = 'repuesto_report_' . date('Ymd_His') . '.xlsx';
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $writer->save('php://output');
    exit;
    
    } catch (Exception $e) {
        // En caso de error, mostrar mensaje de error
        header('Content-Type: text/html; charset=utf-8');
        die('Error generando Excel: ' . $e->getMessage());
    }
}

//descargar pdf de proveedores
//descargar pdf de proveedores
public function descargarProveedoresPDF($filtros = [])
{
    date_default_timezone_set('America/Bogota');
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../models/Proveedor.php';
    $model = new Proveedor();
    $filtrosModel = [];
    if (!empty($filtros['nombre'])) $filtrosModel['nom_proveedor'] = $filtros['nombre'];
    if (!empty($filtros['producto'])) $filtrosModel['tip_repuesto'] = $filtros['producto'];
    $result = $model->getAll($filtrosModel);
    $proveedores = [];
    while ($row = $result->fetch_assoc()) {
        $proveedores[] = $row;
    }
    
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Logo y encabezado
    if (file_exists('public/img/logo.png')) {
        $pdf->Image('public/img/logo.png', 10, 8, 25);
    }
    
    // Título principal
    $pdf->SetFont('Helvetica', 'B', 18);
    $pdf->SetTextColor(40, 180, 220);
    $pdf->Ln(8);
    $pdf->Cell(0, 12, utf8_decode('Reporte de Proveedores'), 0, 1, 'C');
    
    // Línea separadora
    $pdf->SetDrawColor(40, 180, 220);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
    $pdf->Ln(8);
    
    // Información del reporte
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->Cell(100, 6, utf8_decode('Fecha de generación: ' . date('d/m/Y H:i:s')), 0, 0, 'L');
    $pdf->Cell(90, 6, utf8_decode('Total de registros: ' . count($proveedores)), 0, 1, 'R');
    $pdf->Ln(5);
    
    // Filtros aplicados (si los hay)
    if (!empty($filtros['nombre']) || !empty($filtros['producto'])) {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(0, 6, utf8_decode('Filtros aplicados:'), 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 9);
        if (!empty($filtros['nombre'])) {
            $pdf->Cell(0, 5, utf8_decode('• Nombre: ' . $filtros['nombre']), 0, 1, 'L');
        }
        if (!empty($filtros['producto'])) {
            $pdf->Cell(0, 5, utf8_decode('• Producto: ' . $filtros['producto']), 0, 1, 'L');
        }
        $pdf->Ln(3);
    }
    
    // Encabezados de la tabla
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetFillColor(220, 241, 241);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(60, 10, utf8_decode('Nombre'), 1, 0, 'C', true);
    $pdf->Cell(60, 10, utf8_decode('Contacto'), 1, 0, 'C', true);
    $pdf->Cell(60, 10, utf8_decode('Repuesto'), 1, 1, 'C', true);
    
    // Datos de la tabla
    $pdf->SetFont('Helvetica', '', 11);
    foreach ($proveedores as $prov) {
        $pdf->Cell(60, 10, utf8_decode($prov['nom_proveedor'] ?? ''), 1, 0, 'L');
        $pdf->Cell(60, 10, utf8_decode($prov['tel_contacto'] ?? ''), 1, 0, 'L');
        $pdf->Cell(60, 10, utf8_decode($prov['tip_repuesto'] ?? ''), 1, 1, 'L');
    }
    
    // Pie de página
    $pdf->SetY(-25);
    $pdf->SetFont('Helvetica', 'I', 9);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(0, 10, utf8_decode('Sistema de Reportes | Pagina ' . $pdf->PageNo()), 0, 0, 'C');
    
    // Salida del PDF
    header('Content-Type: application/pdf');
    $filename = 'reporte_proveedores_' . date('Ymd_His') . '.pdf';
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $pdf->Output('D', $filename);
    exit;
}

//descargar excel de proveedores
public function descargarProveedoresExcel($filtros = [])
{
    // Limpiar cualquier salida previa
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    try {
        date_default_timezone_set('America/Bogota');
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../models/Proveedor.php';
        $model = new Proveedor();
        $filtrosModel = [];
        if (!empty($filtros['nombre'])) $filtrosModel['nom_proveedor'] = $filtros['nombre'];
        if (!empty($filtros['producto'])) $filtrosModel['tip_repuesto'] = $filtros['producto'];
        $result = $model->getAll($filtrosModel);
        $proveedores = [];
        while ($row = $result->fetch_assoc()) {
            $proveedores[] = $row;
        }
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Reporte de Proveedores');
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('2850B4');
        $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
        $sheet->mergeCells('A2:C2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
        $sheet->setCellValue('A3', 'Cantidad de registros: ' . count($proveedores));
        $sheet->mergeCells('A3:C3');
        $sheet->getStyle('A3')->getFont()->setSize(11);
        $sheet->setCellValue('A4', 'Nombre');
        $sheet->setCellValue('B4', 'Contacto');
        $sheet->setCellValue('C4', 'Repuesto');
        $sheet->getStyle('A4:C4')->getFont()->setBold(true);
        $sheet->getStyle('A4:C4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('DCE6F1');
        $row = 5;
        foreach ($proveedores as $prov) {
            $sheet->setCellValue('A' . $row, $prov['nom_proveedor'] ?? '');
            $sheet->setCellValue('B' . $row, $prov['tel_contacto'] ?? '');
            $sheet->setCellValue('C' . $row, $prov['tip_repuesto'] ?? '');
            $row++;
        }
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->setAutoFilter('A4:C' . ($row - 1));
        $filename = 'proveedores_report_' . date('Ymd_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
        
    } catch (Exception $e) {
        header('Content-Type: text/html; charset=utf-8');
        die('Error generando Excel: ' . $e->getMessage());
    }
}

//descargar pdf de usuarios
public function descargarUsuariosPDF($filtros = [])
{
    date_default_timezone_set('America/Bogota');
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../models/User.php';
    $model = new User();
    $usuarios = $model->getAll(); // Ya retorna un array con PDO
    
    // Aplicar filtros manualmente
    if (!empty($filtros['usuario'])) {
        $usuarios = array_filter($usuarios, function($u) use ($filtros) {
            $nombreCompleto = ($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? '');
            return stripos($nombreCompleto, $filtros['usuario']) !== false;
        });
    }
    if (!empty($filtros['rol'])) {
        $usuarios = array_filter($usuarios, function($u) use ($filtros) {
            return strcasecmp($u['rol'] ?? '', $filtros['rol']) === 0;
        });
    }
    
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Logo y encabezado
    if (file_exists('public/img/logo.png')) {
        $pdf->Image('public/img/logo.png', 10, 8, 25);
    }
    
    // Título principal
    $pdf->SetFont('Helvetica', 'B', 18);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Ln(8);
    $pdf->Cell(0, 12, utf8_decode('Reporte de Usuarios'), 0, 1, 'C');
    
    // Línea separadora
    $pdf->SetDrawColor(100, 100, 100);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
    $pdf->Ln(8);
    
    // Información del reporte
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->Cell(100, 6, utf8_decode('Fecha de generación: ' . date('d/m/Y H:i:s')), 0, 0, 'L');
    $pdf->Cell(90, 6, utf8_decode('Total de registros: ' . count($usuarios)), 0, 1, 'R');
    $pdf->Ln(5);
    
    // Filtros aplicados (si los hay)
    if (!empty($filtros['usuario']) || !empty($filtros['rol'])) {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(0, 6, utf8_decode('Filtros aplicados:'), 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 9);
        if (!empty($filtros['usuario'])) {
            $pdf->Cell(0, 5, utf8_decode('• Usuario: ' . $filtros['usuario']), 0, 1, 'L');
        }
        if (!empty($filtros['rol'])) {
            $pdf->Cell(0, 5, utf8_decode('• Rol: ' . $filtros['rol']), 0, 1, 'L');
        }
        $pdf->Ln(3);
    }
    
    // Encabezados de la tabla
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(95, 10, utf8_decode('Usuario'), 1, 0, 'C', true);
    $pdf->Cell(95, 10, utf8_decode('Rol'), 1, 1, 'C', true);
    
    // Datos de la tabla
    $pdf->SetFont('Helvetica', '', 11);
    foreach ($usuarios as $user) {
        $nombreCompleto = ($user['nombre'] ?? '') . ' ' . ($user['apellido'] ?? '');
        $pdf->Cell(95, 10, utf8_decode($nombreCompleto), 1, 0, 'L');
        $pdf->Cell(95, 10, utf8_decode($user['rol'] ?? ''), 1, 1, 'L');
    }
    
    // Pie de página
    $pdf->SetY(-25);
    $pdf->SetFont('Helvetica', 'I', 9);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(0, 10, utf8_decode('Sistema de Reportes | Pagina ' . $pdf->PageNo()), 0, 0, 'C');
    
    // Salida del PDF
    header('Content-Type: application/pdf');
    $filename = 'reporte_usuarios_' . date('Ymd_His') . '.pdf';
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $pdf->Output('D', $filename);
    exit;
}

//descargar excel de usuarios   
public function descargarUsuariosExcel($filtros = [])
{
    // Limpiar cualquier salida previa
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    try {
        date_default_timezone_set('America/Bogota');
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../models/User.php';
        $model = new User();
        $usuarios = $model->getAll(); // Ya retorna un array con PDO
        
        // Aplicar filtros manualmente
        if (!empty($filtros['usuario'])) {
            $usuarios = array_filter($usuarios, function($u) use ($filtros) {
                $nombreCompleto = ($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? '');
                return stripos($nombreCompleto, $filtros['usuario']) !== false;
            });
        }
        if (!empty($filtros['rol'])) {
            $usuarios = array_filter($usuarios, function($u) use ($filtros) {
                return strcasecmp($u['rol'] ?? '', $filtros['rol']) === 0;
            });
        }
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Reporte de Usuarios');
    $sheet->mergeCells('A1:B1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('646464');
    $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
    $sheet->mergeCells('A2:B2');
    $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
    $sheet->setCellValue('A3', 'Cantidad de registros: ' . count($usuarios));
    $sheet->mergeCells('A3:B3');
    $sheet->getStyle('A3')->getFont()->setSize(11);
    $sheet->setCellValue('A4', 'Usuario');
    $sheet->setCellValue('B4', 'Rol');
    $sheet->getStyle('A4:B4')->getFont()->setBold(true);
    $sheet->getStyle('A4:B4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()->setRGB('DCE6F1');
    $row = 5;
    foreach ($usuarios as $user) {
        $nombreCompleto = ($user['nombre'] ?? '') . ' ' . ($user['apellido'] ?? '');
        $sheet->setCellValue('A' . $row, $nombreCompleto);
        $sheet->setCellValue('B' . $row, $user['rol'] ?? '');
        $row++;
    }
    $sheet->getColumnDimension('A')->setAutoSize(true);
    $sheet->getColumnDimension('B')->setAutoSize(true);
    $sheet->setAutoFilter('A4:B' . ($row - 1));
    $filename = 'usuarios_report_' . date('Ymd_His') . '.xlsx';
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $writer->save('php://output');
    exit;
    
    } catch (Exception $e) {
        header('Content-Type: text/html; charset=utf-8');
        die('Error generando Excel: ' . $e->getMessage());
    }
}

// ========== REPORTES DE VEHÍCULOS ==========

private function getVehiculosData($filtros = [])
{
    $sql = "SELECT rv.id, rv.placa, rv.marca_vehiculo, rv.modelo, rv.color, 
                   rv.tipo_unidad, rv.estado, sv.nombre AS subcategoria,
                   cv.nombre AS categoria
            FROM regis_vehic rv
            LEFT JOIN subcat_vehic sv ON rv.subcat_vehic_id = sv.id
            LEFT JOIN cat_vehic cv ON sv.cat_vehic_id = cv.id
            WHERE 1=1";
    
    if (!empty($filtros['placa'])) {
        $sql .= " AND rv.placa LIKE '%" . $this->db->real_escape_string($filtros['placa']) . "%'";
    }
    if (!empty($filtros['marca'])) {
        $sql .= " AND rv.marca_vehiculo LIKE '%" . $this->db->real_escape_string($filtros['marca']) . "%'";
    }
    if (!empty($filtros['modelo'])) {
        $sql .= " AND rv.modelo LIKE '%" . $this->db->real_escape_string($filtros['modelo']) . "%'";
    }
    if (!empty($filtros['estado'])) {
        $sql .= " AND rv.estado = '" . $this->db->real_escape_string($filtros['estado']) . "'";
    }
    if (!empty($filtros['categoria'])) {
        $sql .= " AND cv.id = " . intval($filtros['categoria']);
    }
    
    $sql .= " ORDER BY rv.id DESC";
    $result = $this->db->query($sql);
    $vehiculos = [];
    while ($row = $result->fetch_assoc()) {
        $vehiculos[] = $row;
    }
    return $vehiculos;
}

public function vehiculos($filtros = [])
{
    $vehiculos = $this->getVehiculosData($filtros);
    
    // Obtener marcas únicas
    $sqlMarcas = "SELECT DISTINCT marca_vehiculo FROM regis_vehic WHERE marca_vehiculo IS NOT NULL AND marca_vehiculo != '' ORDER BY marca_vehiculo";
    $resultMarcas = $this->db->query($sqlMarcas);
    $marcas = [];
    while ($row = $resultMarcas->fetch_assoc()) {
        $marcas[] = $row;
    }
    
    include __DIR__ . '/../views/reportes/vehiculos.php';
}

public function descargarVehiculosPDF($filtros = [])
{
    date_default_timezone_set('America/Bogota');
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $vehiculos = $this->getVehiculosData($filtros);
    
    $pdf = new FPDF();
    $pdf->AddPage('L'); // Landscape
    
    if (file_exists('assets/images/trucksisx-logo.png')) {
        $pdf->Image('assets/images/trucksisx-logo.png', 10, 8, 25);
    }
    
    $pdf->SetFont('Helvetica', 'B', 18);
    $pdf->SetTextColor(40, 80, 180);
    $pdf->Cell(0, 15, utf8_decode('Reporte de Vehículos'), 0, 1, 'C');
    $pdf->SetDrawColor(40, 80, 180);
    $pdf->Line(10, $pdf->GetY(), 287, $pdf->GetY());
    $pdf->Ln(2);
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Helvetica', 'I', 10);
    $pdf->Cell(0, 8, utf8_decode('Generado: ' . date('d/m/Y H:i')), 0, 1, 'R');
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->Cell(0, 8, utf8_decode('Cantidad de registros: ' . count($vehiculos)), 0, 1, 'L');
    $pdf->Ln(2);
    
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetFillColor(220, 230, 241);
    $pdf->Cell(20, 10, 'ID', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Placa', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Marca', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Modelo', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Color', 1, 0, 'C', true);
    $pdf->Cell(50, 10, utf8_decode('Categoría'), 1, 0, 'C', true);
    $pdf->Cell(37, 10, 'Estado', 1, 1, 'C', true);
    
    $pdf->SetFont('Helvetica', '', 9);
    foreach ($vehiculos as $veh) {
        $pdf->Cell(20, 8, $veh['id'], 1, 0, 'C');
        $pdf->Cell(30, 8, utf8_decode($veh['placa'] ?? ''), 1, 0, 'L');
        $pdf->Cell(50, 8, utf8_decode($veh['marca_vehiculo'] ?? ''), 1, 0, 'L');
        $pdf->Cell(40, 8, utf8_decode($veh['modelo'] ?? ''), 1, 0, 'L');
        $pdf->Cell(30, 8, utf8_decode($veh['color'] ?? ''), 1, 0, 'L');
        $pdf->Cell(50, 8, utf8_decode($veh['categoria'] ?? ''), 1, 0, 'L');
        $pdf->Cell(37, 8, utf8_decode($veh['estado'] ?? ''), 1, 1, 'C');
    }
    
    $pdf->SetY(-25);
    $pdf->SetFont('Helvetica', 'I', 9);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(0, 10, utf8_decode('Sistema de Reportes | Página ' . $pdf->PageNo()), 0, 0, 'C');
    
    header('Content-Type: application/pdf');
    $filename = 'vehiculos_report_' . date('Ymd_His') . '.pdf';
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $pdf->Output('D', $filename);
    exit;
}

public function descargarVehiculosExcel($filtros = [])
{
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    try {
        date_default_timezone_set('America/Bogota');
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $vehiculos = $this->getVehiculosData($filtros);
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Reporte de Vehículos');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('2850B4');
        
        $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
        
        $sheet->setCellValue('A3', 'Cantidad de registros: ' . count($vehiculos));
        $sheet->mergeCells('A3:G3');
        
        $sheet->setCellValue('A4', 'ID');
        $sheet->setCellValue('B4', 'Placa');
        $sheet->setCellValue('C4', 'Marca');
        $sheet->setCellValue('D4', 'Modelo');
        $sheet->setCellValue('E4', 'Color');
        $sheet->setCellValue('F4', 'Categoría');
        $sheet->setCellValue('G4', 'Estado');
        $sheet->getStyle('A4:G4')->getFont()->setBold(true);
        $sheet->getStyle('A4:G4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DCE6F1');
        
        $row = 5;
        foreach ($vehiculos as $veh) {
            $sheet->setCellValue('A' . $row, $veh['id']);
            $sheet->setCellValue('B' . $row, $veh['placa'] ?? '');
            $sheet->setCellValue('C' . $row, $veh['marca_vehiculo'] ?? '');
            $sheet->setCellValue('D' . $row, $veh['modelo'] ?? '');
            $sheet->setCellValue('E' . $row, $veh['color'] ?? '');
            $sheet->setCellValue('F' . $row, $veh['categoria'] ?? '');
            $sheet->setCellValue('G' . $row, $veh['estado'] ?? '');
            $row++;
        }
        
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'vehiculos_report_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
        
    } catch (Exception $e) {
        header('Content-Type: text/html; charset=utf-8');
        die('Error generando Excel: ' . $e->getMessage());
    }
}

// ========== REPORTES DE CONDUCTORES ==========

private function getConductoresData($filtros = [])
{
    $sql = "SELECT c.id, 
                   c.cargo,
                   c.horas_trabajadas,
                   c.tareas_completadas,
                   c.efeciencia,
                   c.descripcion,
                   rv.placa AS vehiculo
            FROM cond c
            LEFT JOIN regis_vehic rv ON c.regis_vehic_id = rv.id
            WHERE 1=1";
    
    if (!empty($filtros['cargo'])) {
        $sql .= " AND c.cargo LIKE '%" . $this->db->real_escape_string($filtros['cargo']) . "%'";
    }
    if (!empty($filtros['vehiculo'])) {
        $sql .= " AND rv.id = " . intval($filtros['vehiculo']);
    }
    
    $sql .= " ORDER BY c.id DESC";
    $result = $this->db->query($sql);
    $conductores = [];
    while ($row = $result->fetch_assoc()) {
        $conductores[] = $row;
    }
    return $conductores;
}

public function conductores($filtros = [])
{
    $conductores = $this->getConductoresData($filtros);
    
    // Obtener vehículos únicos
    $sqlVehiculos = "SELECT DISTINCT rv.id, rv.placa FROM cond c INNER JOIN regis_vehic rv ON c.regis_vehic_id = rv.id WHERE rv.placa IS NOT NULL ORDER BY rv.placa";
    $resultVehiculos = $this->db->query($sqlVehiculos);
    $vehiculos = [];
    while ($row = $resultVehiculos->fetch_assoc()) {
        $vehiculos[] = $row;
    }
    
    include __DIR__ . '/../views/reportes/conductores.php';
}

public function descargarConductoresPDF($filtros = [])
{
    date_default_timezone_set('America/Bogota');
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $conductores = $this->getConductoresData($filtros);
    
    $pdf = new FPDF();
    $pdf->AddPage();
    
    if (file_exists('assets/images/trucksisx-logo.png')) {
        $pdf->Image('assets/images/trucksisx-logo.png', 10, 8, 25);
    }
    
    $pdf->SetFont('Helvetica', 'B', 18);
    $pdf->SetTextColor(40, 140, 80);
    $pdf->Cell(0, 15, utf8_decode('Reporte de Conductores'), 0, 1, 'C');
    $pdf->SetDrawColor(40, 140, 80);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(2);
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Helvetica', 'I', 10);
    $pdf->Cell(0, 8, utf8_decode('Generado: ' . date('d/m/Y H:i')), 0, 1, 'R');
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->Cell(0, 8, utf8_decode('Cantidad de registros: ' . count($conductores)), 0, 1, 'L');
    $pdf->Ln(2);
    
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetFillColor(220, 241, 220);
    $pdf->Cell(12, 10, 'ID', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Cargo', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Horas Trab.', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Tareas Comp.', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'Eficiencia', 1, 0, 'C', true);
    $pdf->Cell(50, 10, utf8_decode('Descripción'), 1, 0, 'C', true);
    $pdf->Cell(23, 10, utf8_decode('Vehículo'), 1, 1, 'C', true);
    
    $pdf->SetFont('Helvetica', '', 8);
    foreach ($conductores as $cond) {
        $pdf->Cell(12, 8, $cond['id'] ?? '', 1, 0, 'C');
        $pdf->Cell(35, 8, utf8_decode($cond['cargo'] ?? ''), 1, 0, 'L');
        $pdf->Cell(25, 8, $cond['horas_trabajadas'] ?? '0', 1, 0, 'C');
        $pdf->Cell(25, 8, $cond['tareas_completadas'] ?? '0', 1, 0, 'C');
        $pdf->Cell(20, 8, $cond['efeciencia'] ?? '0', 1, 0, 'C');
        $descripcion = strlen($cond['descripcion'] ?? '') > 25 ? substr($cond['descripcion'], 0, 22) . '...' : ($cond['descripcion'] ?? '');
        $pdf->Cell(50, 8, utf8_decode($descripcion), 1, 0, 'L');
        $pdf->Cell(23, 8, utf8_decode($cond['vehiculo'] ?? 'N/A'), 1, 1, 'C');
    }
    
    $pdf->SetY(-25);
    $pdf->SetFont('Helvetica', 'I', 9);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(0, 10, utf8_decode('Sistema de Reportes | Página ' . $pdf->PageNo()), 0, 0, 'C');
    
    header('Content-Type: application/pdf');
    $filename = 'conductores_report_' . date('Ymd_His') . '.pdf';
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $pdf->Output('D', $filename);
    exit;
}

public function descargarConductoresExcel($filtros = [])
{
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    try {
        date_default_timezone_set('America/Bogota');
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $conductores = $this->getConductoresData($filtros);
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Reporte de Conductores');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('288C50');
        
        $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
        $sheet->mergeCells('A2:G2');
        
        $sheet->setCellValue('A3', 'Cantidad de registros: ' . count($conductores));
        $sheet->mergeCells('A3:G3');
        
        $headers = ['ID', 'Cargo', 'Horas Trabajadas', 'Tareas Completadas', 'Eficiencia', 'Descripción', 'Vehículo'];
        foreach ($headers as $index => $header) {
            $col = chr(65 + $index);
            $sheet->setCellValue($col . '4', $header);
        }
        $sheet->getStyle('A4:G4')->getFont()->setBold(true);
        $sheet->getStyle('A4:G4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DCF1DC');
        
        $row = 5;
        foreach ($conductores as $cond) {
            $sheet->setCellValue('A' . $row, $cond['id'] ?? '');
            $sheet->setCellValue('B' . $row, $cond['cargo'] ?? '');
            $sheet->setCellValue('C' . $row, $cond['horas_trabajadas'] ?? '0');
            $sheet->setCellValue('D' . $row, $cond['tareas_completadas'] ?? '0');
            $sheet->setCellValue('E' . $row, $cond['efeciencia'] ?? '0');
            $sheet->setCellValue('F' . $row, $cond['descripcion'] ?? '');
            $sheet->setCellValue('G' . $row, $cond['vehiculo'] ?? 'Sin asignar');
            $row++;
        }
        
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'conductores_report_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
        
    } catch (Exception $e) {
        header('Content-Type: text/html; charset=utf-8');
        die('Error generando Excel: ' . $e->getMessage());
    }
}

// ========== REPORTES DE ALERTAS ==========

private function getAlertasData($filtros = [])
{
    $sql = "SELECT a.id, a.fecha_hora, a.prioridad, a.estado, a.descripcion, 
                   a.tipo_alerta, rv.placa AS vehiculo_placa, ot.nombre_trabajo AS orden
            FROM alert a
            LEFT JOIN ord_trabj ot ON a.ord_trabj_id = ot.id
            LEFT JOIN regis_vehic rv ON a.regis_vehic_id = rv.id
            WHERE 1=1";
    
    if (!empty($filtros['tipo'])) {
        $sql .= " AND a.tipo_alerta LIKE '%" . $this->db->real_escape_string($filtros['tipo']) . "%'";
    }
    if (!empty($filtros['estado'])) {
        $sql .= " AND a.estado = '" . $this->db->real_escape_string($filtros['estado']) . "'";
    }
    if (!empty($filtros['vehiculo'])) {
        $sql .= " AND a.regis_vehic_id = " . intval($filtros['vehiculo']);
    }
    if (!empty($filtros['prioridad'])) {
        $sql .= " AND a.prioridad = '" . $this->db->real_escape_string($filtros['prioridad']) . "'";
    }
    
    $sql .= " ORDER BY a.fecha_hora DESC";
    $result = $this->db->query($sql);
    $alertas = [];
    while ($row = $result->fetch_assoc()) {
        $alertas[] = $row;
    }
    return $alertas;
}

public function alertas($filtros = [])
{
    $alertas = $this->getAlertasData($filtros);
    
    // Obtener vehículos únicos con alertas
    $sqlVehiculos = "SELECT DISTINCT rv.id, rv.placa FROM alert a INNER JOIN regis_vehic rv ON a.regis_vehic_id = rv.id WHERE rv.placa IS NOT NULL ORDER BY rv.placa";
    $resultVehiculos = $this->db->query($sqlVehiculos);
    $vehiculos = [];
    while ($row = $resultVehiculos->fetch_assoc()) {
        $vehiculos[] = $row;
    }
    
    include __DIR__ . '/../views/reportes/alertas.php';
}

public function descargarAlertasPDF($filtros = [])
{
    date_default_timezone_set('America/Bogota');
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $alertas = $this->getAlertasData($filtros);
    
    $pdf = new FPDF();
    $pdf->AddPage('L');
    
    if (file_exists('assets/images/trucksisx-logo.png')) {
        $pdf->Image('assets/images/trucksisx-logo.png', 10, 8, 25);
    }
    
    $pdf->SetFont('Helvetica', 'B', 18);
    $pdf->SetTextColor(220, 50, 50);
    $pdf->Cell(0, 15, utf8_decode('Reporte de Alertas'), 0, 1, 'C');
    $pdf->SetDrawColor(220, 50, 50);
    $pdf->Line(10, $pdf->GetY(), 287, $pdf->GetY());
    $pdf->Ln(2);
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Helvetica', 'I', 10);
    $pdf->Cell(0, 8, utf8_decode('Generado: ' . date('d/m/Y H:i')), 0, 1, 'R');
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->Cell(0, 8, utf8_decode('Cantidad de registros: ' . count($alertas)), 0, 1, 'L');
    $pdf->Ln(2);
    
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetFillColor(255, 220, 220);
    $pdf->Cell(15, 10, 'ID', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Fecha/Hora', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Prioridad', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Estado', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Tipo', 1, 0, 'C', true);
    $pdf->Cell(90, 10, utf8_decode('Descripción'), 1, 0, 'C', true);
    $pdf->Cell(52, 10, utf8_decode('Orden Trabajo'), 1, 1, 'C', true);
    
    $pdf->SetFont('Helvetica', '', 8);
    foreach ($alertas as $alerta) {
        $pdf->Cell(15, 8, $alerta['id'], 1, 0, 'C');
        $pdf->Cell(35, 8, date('d/m/Y H:i', strtotime($alerta['fecha_hora'])), 1, 0, 'C');
        $pdf->Cell(25, 8, utf8_decode($alerta['prioridad'] ?? ''), 1, 0, 'C');
        $pdf->Cell(25, 8, utf8_decode($alerta['estado'] ?? ''), 1, 0, 'C');
        $pdf->Cell(35, 8, utf8_decode($alerta['tipo_alerta'] ?? ''), 1, 0, 'L');
        $descripcion = strlen($alerta['descripcion'] ?? '') > 40 ? substr($alerta['descripcion'], 0, 37) . '...' : ($alerta['descripcion'] ?? '');
        $pdf->Cell(90, 8, utf8_decode($descripcion), 1, 0, 'L');
        $pdf->Cell(52, 8, utf8_decode($alerta['orden'] ?? 'N/A'), 1, 1, 'L');
    }
    
    $pdf->SetY(-25);
    $pdf->SetFont('Helvetica', 'I', 9);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(0, 10, utf8_decode('Sistema de Reportes | Página ' . $pdf->PageNo()), 0, 0, 'C');
    
    header('Content-Type: application/pdf');
    $filename = 'alertas_report_' . date('Ymd_His') . '.pdf';
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $pdf->Output('D', $filename);
    exit;
}

public function descargarAlertasExcel($filtros = [])
{
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    try {
        date_default_timezone_set('America/Bogota');
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $alertas = $this->getAlertasData($filtros);
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Reporte de Alertas');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('DC3232');
        
        $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
        $sheet->mergeCells('A2:G2');
        
        $sheet->setCellValue('A3', 'Cantidad de registros: ' . count($alertas));
        $sheet->mergeCells('A3:G3');
        
        $headers = ['ID', 'Fecha/Hora', 'Prioridad', 'Estado', 'Tipo', 'Descripción', 'Orden Trabajo'];
        foreach ($headers as $index => $header) {
            $col = chr(65 + $index);
            $sheet->setCellValue($col . '4', $header);
        }
        $sheet->getStyle('A4:G4')->getFont()->setBold(true);
        $sheet->getStyle('A4:G4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFDCDC');
        
        $row = 5;
        foreach ($alertas as $alerta) {
            $sheet->setCellValue('A' . $row, $alerta['id']);
            $sheet->setCellValue('B' . $row, date('d/m/Y H:i', strtotime($alerta['fecha_hora'])));
            $sheet->setCellValue('C' . $row, $alerta['prioridad'] ?? '');
            $sheet->setCellValue('D' . $row, $alerta['estado'] ?? '');
            $sheet->setCellValue('E' . $row, $alerta['tipo_alerta'] ?? '');
            $sheet->setCellValue('F' . $row, $alerta['descripcion'] ?? '');
            $sheet->setCellValue('G' . $row, $alerta['orden'] ?? 'N/A');
            $row++;
        }
        
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'alertas_report_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
        
    } catch (Exception $e) {
        header('Content-Type: text/html; charset=utf-8');
        die('Error generando Excel: ' . $e->getMessage());
    }
}

// ========== REPORTES DE ÓRDENES DE TRABAJO ==========

private function getOrdenesTrabajoData($filtros = [])
{
    $sql = "SELECT ot.id, ot.nombre_trabajo, ot.descripcion, ot.fecha_creacion, 
                   ot.fecha_estimada, ot.estado, ot.prioridad,
                   rv.placa AS vehiculo_placa,
                   CONCAT(u.nombre, ' ', u.apellido) AS tecnico_nombre
            FROM ord_trabj ot
            LEFT JOIN alert a ON ot.alert_id = a.id
            LEFT JOIN regis_vehic rv ON a.regis_vehic_id = rv.id
            LEFT JOIN users u ON ot.users_id = u.id
            WHERE 1=1";
    
    if (!empty($filtros['estado'])) {
        $sql .= " AND ot.estado = '" . $this->db->real_escape_string($filtros['estado']) . "'";
    }
    if (!empty($filtros['prioridad'])) {
        $sql .= " AND ot.prioridad = '" . $this->db->real_escape_string($filtros['prioridad']) . "'";
    }
    if (!empty($filtros['vehiculo'])) {
        $sql .= " AND rv.id = " . intval($filtros['vehiculo']);
    }
    if (!empty($filtros['tecnico'])) {
        $sql .= " AND u.id = " . intval($filtros['tecnico']);
    }
    if (!empty($filtros['fecha_desde'])) {
        $sql .= " AND ot.fecha_creacion >= '" . $this->db->real_escape_string($filtros['fecha_desde']) . "'";
    }
    
    $sql .= " ORDER BY ot.fecha_creacion DESC";
    $result = $this->db->query($sql);
    $ordenes = [];
    while ($row = $result->fetch_assoc()) {
        $ordenes[] = $row;
    }
    return $ordenes;
}

public function ordenesTrabajo($filtros = [])
{
    $ordenes = $this->getOrdenesTrabajoData($filtros);
    
    // Obtener vehículos únicos con órdenes de trabajo
    $sqlVehiculos = "SELECT DISTINCT rv.id, rv.placa FROM ord_trabj ot INNER JOIN alert a ON ot.alert_id = a.id INNER JOIN regis_vehic rv ON a.regis_vehic_id = rv.id WHERE rv.placa IS NOT NULL ORDER BY rv.placa";
    $resultVehiculos = $this->db->query($sqlVehiculos);
    $vehiculos = [];
    while ($row = $resultVehiculos->fetch_assoc()) {
        $vehiculos[] = $row;
    }
    
    // Obtener técnicos únicos
    $sqlTecnicos = "SELECT DISTINCT u.id, CONCAT(u.nombre, ' ', u.apellido) as nombre_completo FROM ord_trabj ot INNER JOIN users u ON ot.users_id = u.id WHERE u.rol = 'tecnico' ORDER BY u.nombre";
    $resultTecnicos = $this->db->query($sqlTecnicos);
    $tecnicos = [];
    while ($row = $resultTecnicos->fetch_assoc()) {
        $tecnicos[] = $row;
    }
    
    include __DIR__ . '/../views/reportes/ordenes_trabajo.php';
}

public function descargarOrdenesTrabajosPDF($filtros = [])
{
    date_default_timezone_set('America/Bogota');
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $ordenes = $this->getOrdenesTrabajoData($filtros);
    
    $pdf = new FPDF();
    $pdf->AddPage('L');
    
    if (file_exists('assets/images/trucksisx-logo.png')) {
        $pdf->Image('assets/images/trucksisx-logo.png', 10, 8, 25);
    }
    
    $pdf->SetFont('Helvetica', 'B', 18);
    $pdf->SetTextColor(120, 60, 200);
    $pdf->Cell(0, 15, utf8_decode('Reporte de Órdenes de Trabajo'), 0, 1, 'C');
    $pdf->SetDrawColor(120, 60, 200);
    $pdf->Line(10, $pdf->GetY(), 287, $pdf->GetY());
    $pdf->Ln(2);
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Helvetica', 'I', 10);
    $pdf->Cell(0, 8, utf8_decode('Generado: ' . date('d/m/Y H:i')), 0, 1, 'R');
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->Cell(0, 8, utf8_decode('Cantidad de registros: ' . count($ordenes)), 0, 1, 'L');
    $pdf->Ln(2);
    
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetFillColor(230, 220, 241);
    $pdf->Cell(15, 10, 'ID', 1, 0, 'C', true);
    $pdf->Cell(70, 10, 'Nombre Trabajo', 1, 0, 'C', true);
    $pdf->Cell(30, 10, utf8_decode('F. Creación'), 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'F. Estimada', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Prioridad', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Estado', 1, 0, 'C', true);
    $pdf->Cell(82, 10, utf8_decode('Descripción'), 1, 1, 'C', true);
    
    $pdf->SetFont('Helvetica', '', 8);
    foreach ($ordenes as $orden) {
        $pdf->Cell(15, 8, $orden['id'], 1, 0, 'C');
        $nombre = strlen($orden['nombre_trabajo'] ?? '') > 30 ? substr($orden['nombre_trabajo'], 0, 27) . '...' : ($orden['nombre_trabajo'] ?? '');
        $pdf->Cell(70, 8, utf8_decode($nombre), 1, 0, 'L');
        $pdf->Cell(30, 8, $orden['fecha_creacion'] ? date('d/m/Y', strtotime($orden['fecha_creacion'])) : '', 1, 0, 'C');
        $pdf->Cell(30, 8, $orden['fecha_estimada'] ? date('d/m/Y', strtotime($orden['fecha_estimada'])) : '', 1, 0, 'C');
        $pdf->Cell(25, 8, utf8_decode($orden['prioridad'] ?? ''), 1, 0, 'C');
        $pdf->Cell(25, 8, utf8_decode($orden['estado'] ?? ''), 1, 0, 'C');
        $descripcion = strlen($orden['descripcion'] ?? '') > 35 ? substr($orden['descripcion'], 0, 32) . '...' : ($orden['descripcion'] ?? '');
        $pdf->Cell(82, 8, utf8_decode($descripcion), 1, 1, 'L');
    }
    
    $pdf->SetY(-25);
    $pdf->SetFont('Helvetica', 'I', 9);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(0, 10, utf8_decode('Sistema de Reportes | Página ' . $pdf->PageNo()), 0, 0, 'C');
    
    header('Content-Type: application/pdf');
    $filename = 'ordenes_trabajo_report_' . date('Ymd_His') . '.pdf';
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $pdf->Output('D', $filename);
    exit;
}

public function descargarOrdenesTrabajosExcel($filtros = [])
{
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    try {
        date_default_timezone_set('America/Bogota');
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $ordenes = $this->getOrdenesTrabajoData($filtros);
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Reporte de Órdenes de Trabajo');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('783CC8');
        
        $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
        $sheet->mergeCells('A2:G2');
        
        $sheet->setCellValue('A3', 'Cantidad de registros: ' . count($ordenes));
        $sheet->mergeCells('A3:G3');
        
        $headers = ['ID', 'Nombre Trabajo', 'Descripción', 'F. Creación', 'F. Estimada', 'Prioridad', 'Estado'];
        foreach ($headers as $index => $header) {
            $col = chr(65 + $index);
            $sheet->setCellValue($col . '4', $header);
        }
        $sheet->getStyle('A4:G4')->getFont()->setBold(true);
        $sheet->getStyle('A4:G4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E6DCF1');
        
        $row = 5;
        foreach ($ordenes as $orden) {
            $sheet->setCellValue('A' . $row, $orden['id']);
            $sheet->setCellValue('B' . $row, $orden['nombre_trabajo'] ?? '');
            $sheet->setCellValue('C' . $row, $orden['descripcion'] ?? '');
            $sheet->setCellValue('D' . $row, $orden['fecha_creacion'] ? date('d/m/Y', strtotime($orden['fecha_creacion'])) : '');
            $sheet->setCellValue('E' . $row, $orden['fecha_estimada'] ? date('d/m/Y', strtotime($orden['fecha_estimada'])) : '');
            $sheet->setCellValue('F' . $row, $orden['prioridad'] ?? '');
            $sheet->setCellValue('G' . $row, $orden['estado'] ?? '');
            $row++;
        }
        
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'ordenes_trabajo_report_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
        
    } catch (Exception $e) {
        header('Content-Type: text/html; charset=utf-8');
        die('Error generando Excel: ' . $e->getMessage());
    }
}

// ========== REPORTES DE SALIDAS DE REPUESTOS ==========

private function getSalidasRepuestosData($filtros = [])
{
    $sql = "SELECT sr.id, sr.fecha_salida, sr.cantidad,
                   r.nombre AS repuesto_nombre, r.numero_parte,
                   rv.placa AS vehiculo_placa,
                   CONCAT(u.nombre, ' ', u.apellido) AS autorizado_por,
                   ot.nombre_trabajo AS orden
            FROM sali_repue sr
            LEFT JOIN repue r ON sr.repue_id = r.id
            LEFT JOIN ord_trabj ot ON sr.ord_trabj_id = ot.id
            LEFT JOIN alert a ON sr.alerta_id = a.id
            LEFT JOIN regis_vehic rv ON a.regis_vehic_id = rv.id
            LEFT JOIN users u ON ot.users_id = u.id
            WHERE 1=1";
    
    if (!empty($filtros['repuesto'])) {
        $sql .= " AND r.id = " . intval($filtros['repuesto']);
    }
    if (!empty($filtros['vehiculo'])) {
        $sql .= " AND rv.id = " . intval($filtros['vehiculo']);
    }
    if (!empty($filtros['fecha_desde'])) {
        $sql .= " AND sr.fecha_salida >= '" . $this->db->real_escape_string($filtros['fecha_desde']) . "'";
    }
    if (!empty($filtros['fecha_fin'])) {
        $sql .= " AND sr.fecha_salida <= '" . $this->db->real_escape_string($filtros['fecha_fin']) . "'";
    }
    
    $sql .= " ORDER BY sr.fecha_salida DESC";
    $result = $this->db->query($sql);
    $salidas = [];
    while ($row = $result->fetch_assoc()) {
        $salidas[] = $row;
    }
    return $salidas;
}

public function salidasRepuestos($filtros = [])
{
    $salidas = $this->getSalidasRepuestosData($filtros);
    
    // Obtener repuestos únicos con salidas
    $sqlRepuestos = "SELECT DISTINCT r.id, r.nombre FROM sali_repue sr INNER JOIN repue r ON sr.repue_id = r.id WHERE r.nombre IS NOT NULL ORDER BY r.nombre";
    $resultRepuestos = $this->db->query($sqlRepuestos);
    $repuestos = [];
    while ($row = $resultRepuestos->fetch_assoc()) {
        $repuestos[] = $row;
    }
    
    // Obtener vehículos únicos con salidas de repuestos
    $sqlVehiculos = "SELECT DISTINCT rv.id, rv.placa FROM sali_repue sr INNER JOIN alert a ON sr.alerta_id = a.id INNER JOIN regis_vehic rv ON a.regis_vehic_id = rv.id WHERE rv.placa IS NOT NULL ORDER BY rv.placa";
    $resultVehiculos = $this->db->query($sqlVehiculos);
    $vehiculos = [];
    while ($row = $resultVehiculos->fetch_assoc()) {
        $vehiculos[] = $row;
    }
    
    include __DIR__ . '/../views/reportes/salidas_repuestos.php';
}

public function descargarSalidasRepuestosPDF($filtros = [])
{
    date_default_timezone_set('America/Bogota');
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $salidas = $this->getSalidasRepuestosData($filtros);
    
    $pdf = new FPDF();
    $pdf->AddPage();
    
    if (file_exists('assets/images/trucksisx-logo.png')) {
        $pdf->Image('assets/images/trucksisx-logo.png', 10, 8, 25);
    }
    
    $pdf->SetFont('Helvetica', 'B', 18);
    $pdf->SetTextColor(16, 185, 129);
    $pdf->Cell(0, 15, utf8_decode('Reporte de Salidas de Repuestos'), 0, 1, 'C');
    $pdf->SetDrawColor(16, 185, 129);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(2);
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Helvetica', 'I', 10);
    $pdf->Cell(0, 8, utf8_decode('Generado: ' . date('d/m/Y H:i')), 0, 1, 'R');
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->Cell(0, 8, utf8_decode('Cantidad de registros: ' . count($salidas)), 0, 1, 'L');
    $pdf->Ln(2);
    
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetFillColor(209, 250, 229);
    $pdf->Cell(15, 10, 'ID', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Fecha', 1, 0, 'C', true);
    $pdf->Cell(55, 10, 'Repuesto', 1, 0, 'C', true);
    $pdf->Cell(30, 10, utf8_decode('Número Parte'), 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'Cant.', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'Orden Trabajo', 1, 1, 'C', true);
    
    $pdf->SetFont('Helvetica', '', 8);
    foreach ($salidas as $salida) {
        $pdf->Cell(15, 8, $salida['id'] ?? '', 1, 0, 'C');
        $pdf->Cell(25, 8, date('d/m/Y', strtotime($salida['fecha_salida'])), 1, 0, 'C');
        $repuesto = strlen($salida['repuesto_nombre'] ?? '') > 25 ? substr($salida['repuesto_nombre'], 0, 22) . '...' : ($salida['repuesto_nombre'] ?? '');
        $pdf->Cell(55, 8, utf8_decode($repuesto), 1, 0, 'L');
        $pdf->Cell(30, 8, utf8_decode($salida['numero_parte'] ?? 'N/A'), 1, 0, 'C');
        $pdf->Cell(20, 8, $salida['cantidad'] ?? '', 1, 0, 'C');
        $orden = strlen($salida['orden'] ?? '') > 20 ? substr($salida['orden'], 0, 17) . '...' : ($salida['orden'] ?? 'N/A');
        $pdf->Cell(45, 8, utf8_decode($orden), 1, 1, 'L');
    }
    
    $pdf->SetY(-25);
    $pdf->SetFont('Helvetica', 'I', 9);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(0, 10, utf8_decode('Sistema de Reportes | Página ' . $pdf->PageNo()), 0, 0, 'C');
    
    header('Content-Type: application/pdf');
    $filename = 'salidas_repuestos_report_' . date('Ymd_His') . '.pdf';
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $pdf->Output('D', $filename);
    exit;
}

public function descargarSalidasRepuestosExcel($filtros = [])
{
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    try {
        date_default_timezone_set('America/Bogota');
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $salidas = $this->getSalidasRepuestosData($filtros);
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Reporte de Salidas de Repuestos');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('10B981');
        
        $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
        $sheet->mergeCells('A2:F2');
        
        $sheet->setCellValue('A3', 'Cantidad de registros: ' . count($salidas));
        $sheet->mergeCells('A3:F3');
        
        $headers = ['ID', 'Fecha Salida', 'Repuesto', 'Número Parte', 'Cantidad', 'Orden Trabajo'];
        foreach ($headers as $index => $header) {
            $col = chr(65 + $index);
            $sheet->setCellValue($col . '4', $header);
        }
        $sheet->getStyle('A4:F4')->getFont()->setBold(true);
        $sheet->getStyle('A4:F4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D1FAE5');
        
        $row = 5;
        foreach ($salidas as $salida) {
            $sheet->setCellValue('A' . $row, $salida['id'] ?? '');
            $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($salida['fecha_salida'])));
            $sheet->setCellValue('C' . $row, $salida['repuesto_nombre'] ?? '');
            $sheet->setCellValue('D' . $row, $salida['numero_parte'] ?? 'N/A');
            $sheet->setCellValue('E' . $row, $salida['cantidad'] ?? '');
            $sheet->setCellValue('F' . $row, $salida['orden'] ?? 'N/A');
            $row++;
        }
        
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'salidas_repuestos_report_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
        
    } catch (Exception $e) {
        header('Content-Type: text/html; charset=utf-8');
        die('Error generando Excel: ' . $e->getMessage());
    }
}

// ========== REPORTES DE SALIDAS DE VEHÍCULOS ==========

private function getSalidasVehiculosData($filtros = [])
{
    $sql = "SELECT sv.id, 
                   sv.id_flotas,
                   rv.placa AS vehiculo_placa,
                   sv.segui_monitoreo,
                   sv.control_combustible,
                   sv.cump_regulaciones,
                   sv.protocolo_seguridad,
                   sv.gest_conductores,
                   ot.nombre_trabajo AS orden_trabajo,
                   ot.estado AS orden_estado,
                   a.descripcion AS alerta_descripcion
            FROM sali_vehi sv
            LEFT JOIN regis_vehic rv ON sv.id_flotas = rv.id
            LEFT JOIN ord_trabj ot ON sv.ord_trabj_id = ot.id
            LEFT JOIN alert a ON sv.alerta_id = a.id
            WHERE 1=1";
    
        if (!empty($filtros['vehiculo'])) {
            $sql .= " AND sv.id_flotas = " . intval($filtros['vehiculo']);
        }
        if (!empty($filtros['orden'])) {
            $sql .= " AND sv.ord_trabj_id = " . intval($filtros['orden']);
        }
        
    $sql .= " ORDER BY sv.id DESC";
    $result = $this->db->query($sql);
    $salidas = [];
    while ($row = $result->fetch_assoc()) {
        $salidas[] = $row;
    }
    return $salidas;
}

public function salidasVehiculos($filtros = [])
{
    $salidas = $this->getSalidasVehiculosData($filtros);
    
    // Obtener vehículos únicos con salidas
    $sqlVehiculos = "SELECT DISTINCT rv.id, rv.placa FROM sali_vehi sv INNER JOIN regis_vehic rv ON sv.id_flotas = rv.id WHERE rv.placa IS NOT NULL ORDER BY rv.placa";
    $resultVehiculos = $this->db->query($sqlVehiculos);
    $vehiculos = [];
    while ($row = $resultVehiculos->fetch_assoc()) {
        $vehiculos[] = $row;
    }
    
    // Obtener órdenes de trabajo únicas
    $sqlOrdenes = "SELECT DISTINCT ot.id, ot.nombre_trabajo FROM sali_vehi sv INNER JOIN ord_trabj ot ON sv.ord_trabj_id = ot.id WHERE ot.nombre_trabajo IS NOT NULL ORDER BY ot.nombre_trabajo";
    $resultOrdenes = $this->db->query($sqlOrdenes);
    $ordenes = [];
    while ($row = $resultOrdenes->fetch_assoc()) {
        $ordenes[] = $row;
    }
    
    include __DIR__ . '/../views/reportes/salidas_vehiculos.php';
}

public function descargarSalidasVehiculosPDF($filtros = [])
{
    date_default_timezone_set('America/Bogota');
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $salidas = $this->getSalidasVehiculosData($filtros);
    
    $pdf = new FPDF();
    $pdf->AddPage('L');
    
    if (file_exists('assets/images/trucksisx-logo.png')) {
        $pdf->Image('assets/images/trucksisx-logo.png', 10, 8, 25);
    }
    
    $pdf->SetFont('Helvetica', 'B', 18);
    $pdf->SetTextColor(5, 150, 105);
    $pdf->Cell(0, 15, utf8_decode('Reporte de Salidas de Vehículos'), 0, 1, 'C');
    $pdf->SetDrawColor(5, 150, 105);
    $pdf->Line(10, $pdf->GetY(), 287, $pdf->GetY());
    $pdf->Ln(2);
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Helvetica', 'I', 10);
    $pdf->Cell(0, 8, utf8_decode('Generado: ' . date('d/m/Y H:i')), 0, 1, 'R');
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->Cell(0, 8, utf8_decode('Cantidad de registros: ' . count($salidas)), 0, 1, 'L');
    $pdf->Ln(2);
    
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetFillColor(167, 243, 208);
    $pdf->Cell(15, 10, 'ID', 1, 0, 'C', true);
    $pdf->Cell(30, 10, utf8_decode('Vehículo'), 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Monitoreo', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Combustible', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Regulaciones', 1, 0, 'C', true);
    $pdf->Cell(60, 10, 'Orden Trabajo', 1, 0, 'C', true);
    $pdf->Cell(22, 10, 'Estado', 1, 1, 'C', true);
    
    $pdf->SetFont('Helvetica', '', 8);
    foreach ($salidas as $salida) {
        $pdf->Cell(15, 8, $salida['id'], 1, 0, 'C');
        $pdf->Cell(30, 8, utf8_decode($salida['vehiculo_placa'] ?? 'N/A'), 1, 0, 'C');
        $monitoreo = strlen($salida['segui_monitoreo'] ?? '') > 22 ? substr($salida['segui_monitoreo'], 0, 19) . '...' : ($salida['segui_monitoreo'] ?? 'N/A');
        $pdf->Cell(50, 8, utf8_decode($monitoreo), 1, 0, 'L');
        $combustible = strlen($salida['control_combustible'] ?? '') > 22 ? substr($salida['control_combustible'], 0, 19) . '...' : ($salida['control_combustible'] ?? 'N/A');
        $pdf->Cell(50, 8, utf8_decode($combustible), 1, 0, 'L');
        $regulaciones = strlen($salida['cump_regulaciones'] ?? '') > 22 ? substr($salida['cump_regulaciones'], 0, 19) . '...' : ($salida['cump_regulaciones'] ?? 'N/A');
        $pdf->Cell(50, 8, utf8_decode($regulaciones), 1, 0, 'L');
        $orden = strlen($salida['orden_trabajo'] ?? '') > 27 ? substr($salida['orden_trabajo'], 0, 24) . '...' : ($salida['orden_trabajo'] ?? 'N/A');
        $pdf->Cell(60, 8, utf8_decode($orden), 1, 0, 'L');
        $pdf->Cell(22, 8, utf8_decode($salida['orden_estado'] ?? 'N/A'), 1, 1, 'C');
    }
    
    $pdf->SetY(-25);
    $pdf->SetFont('Helvetica', 'I', 9);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(0, 10, utf8_decode('Sistema de Reportes | Página ' . $pdf->PageNo()), 0, 0, 'C');
    
    header('Content-Type: application/pdf');
    $filename = 'salidas_vehiculos_report_' . date('Ymd_His') . '.pdf';
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $pdf->Output('D', $filename);
    exit;
}

public function descargarSalidasVehiculosExcel($filtros = [])
{
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    try {
        date_default_timezone_set('America/Bogota');
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $salidas = $this->getSalidasVehiculosData($filtros);
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Reporte de Salidas de Vehículos');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('059669');
        
        $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
        $sheet->mergeCells('A2:G2');
        
        $sheet->setCellValue('A3', 'Cantidad de registros: ' . count($salidas));
        $sheet->mergeCells('A3:G3');
        
        $headers = ['ID', 'Vehículo', 'Monitoreo', 'Combustible', 'Regulaciones', 'Orden Trabajo', 'Estado'];
        foreach ($headers as $index => $header) {
            $col = chr(65 + $index);
            $sheet->setCellValue($col . '4', $header);
        }
        $sheet->getStyle('A4:G4')->getFont()->setBold(true);
        $sheet->getStyle('A4:G4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('A7F3D0');
        
        $row = 5;
        foreach ($salidas as $salida) {
            $sheet->setCellValue('A' . $row, $salida['id']);
            $sheet->setCellValue('B' . $row, $salida['vehiculo_placa'] ?? 'N/A');
            $sheet->setCellValue('C' . $row, $salida['segui_monitoreo'] ?? 'N/A');
            $sheet->setCellValue('D' . $row, $salida['control_combustible'] ?? 'N/A');
            $sheet->setCellValue('E' . $row, $salida['cump_regulaciones'] ?? 'N/A');
            $sheet->setCellValue('F' . $row, $salida['orden_trabajo'] ?? 'N/A');
            $sheet->setCellValue('G' . $row, $salida['orden_estado'] ?? 'N/A');
            $row++;
        }
        
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'salidas_vehiculos_report_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
        
    } catch (Exception $e) {
        header('Content-Type: text/html; charset=utf-8');
        die('Error generando Excel: ' . $e->getMessage());
    }
}

}


