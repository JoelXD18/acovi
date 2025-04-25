<?php
/**
 * API para exportar comercios a Excel
 */

// Importar dependencias
require_once '../config/database.php';
require_once '../app/model/companyModel.php';
require_once '../app/controller/companyController.php';
require_once '../app/utils/csrf.php';

// Inicializar sesión
session_start();

// Verificar autenticación
//if (!isset($_SESSION['user_id'])) {
//    echo json_encode([
  //      'success' => false,
    //    'message' => 'No autorizado',
      //  'code' => 401
    //]);
    //exit;
//}

try {
    // Obtener filtros desde URL
    $filters = [
        'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
        'estado' => isset($_GET['estado']) ? trim($_GET['estado']) : '',
        'categoria' => isset($_GET['categoria']) ? intval($_GET['categoria']) : 0,
        'tipo' => isset($_GET['tipo']) ? trim($_GET['tipo']) : ''
    ];

    // Ordenamiento
    $orderBy = isset($_GET['order_by']) ? trim($_GET['order_by']) : 'nombre';
    $orderDir = isset($_GET['order_dir']) ? strtoupper(trim($_GET['order_dir'])) : 'ASC';

    // Inicializar controlador
    $controller = new CompanyController();

    // Obtener todos los comercios (sin paginación)
    $result = $controller->index($filters, 1, 9999, $orderBy, $orderDir);

    if (!$result['success']) {
        throw new Exception($result['message'] ?? 'Error al obtener los datos');
    }

    $comercios = $result['data']['data'];

    // Verificar si hay datos
    if (empty($comercios)) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<p>No hay datos para exportar</p>';
        echo '<a href="javascript:history.back()">Volver</a>';
        exit;
    }

    // Preparar datos para Excel
    $filename = 'comercios_' . date('Y-m-d_H-i-s') . '.xlsx';
    
    // Configurar encabezados
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    // Requerir biblioteca PhpSpreadsheet si está disponible, sino usar una implementación simple
    if (file_exists('../../vendor/autoload.php')) {
        require_once '../../vendor/autoload.php';
        exportWithPhpSpreadsheet($comercios, $filename);
    } else {
        exportSimple($comercios, $filename);
    }

} catch (Exception $e) {
    // Registrar error
    error_log("Error en export_excel.php: " . $e->getMessage());
    
    // Devolver mensaje de error
    header('Content-Type: text/html; charset=utf-8');
    echo '<p>Error al exportar datos: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<a href="javascript:history.back()">Volver</a>';
}

/**
 * Exporta datos a Excel usando PhpSpreadsheet
 */
function exportWithPhpSpreadsheet($data, $filename) {
    try {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Establecer encabezados
        $headers = [
            'A' => 'ID',
            'B' => 'Nombre',
            'C' => 'CIF',
            'D' => 'Descripción',
            'E' => 'Estado',
            'F' => 'Email',
            'G' => 'Categoría',
            'H' => 'Dirección',
            'I' => 'Teléfono',
            'J' => 'Horario',
            'K' => 'Empleados'
        ];
        
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
        }
        
        // Llenar datos
        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item['ID'] ?? '');
            $sheet->setCellValue('B' . $row, $item['nombre'] ?? '');
            $sheet->setCellValue('C' . $row, $item['cif'] ?? '');
            $sheet->setCellValue('D' . $row, $item['descripcion'] ?? '');
            $sheet->setCellValue('E' . $row, $item['estado'] ?? '');
            $sheet->setCellValue('F' . $row, $item['email'] ?? '');
            $sheet->setCellValue('G' . $row, $item['categoria'] ?? '');
            $sheet->setCellValue('H' . $row, $item['direccion'] ?? '');
            $sheet->setCellValue('I' . $row, $item['telefono_principal'] ?? '');
            $sheet->setCellValue('J' . $row, $item['horario'] ?? '');
            $sheet->setCellValue('K' . $row, $item['empleados'] ?? '');
            $row++;
        }
        
        // Auto ajustar anchos de columnas
        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Guardar archivo
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
        
    } catch (Exception $e) {
        error_log("Error en exportWithPhpSpreadsheet: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Exporta datos a CSV (alternativa simple cuando PhpSpreadsheet no está disponible)
 */
function exportSimple($data, $filename) {
    $filename = str_replace('.xlsx', '.csv', $filename);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // UTF-8 BOM para Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Escribir encabezados
    fputcsv($output, [
        'ID', 'Nombre', 'CIF', 'Descripción', 'Estado', 'Email', 
        'Categoría', 'Dirección', 'Teléfono', 'Horario', 'Empleados'
    ]);
    
    // Escribir datos
    foreach ($data as $row) {
        fputcsv($output, [
            $row['ID'] ?? '',
            $row['nombre'] ?? '',
            $row['cif'] ?? '',
            $row['descripcion'] ?? '',
            $row['estado'] ?? '',
            $row['email'] ?? '',
            $row['categoria'] ?? '',
            $row['direccion'] ?? '',
            $row['telefono_principal'] ?? '',
            $row['horario'] ?? '',
            $row['empleados'] ?? ''
        ]);
    }
    
    fclose($output);
    exit;
}
