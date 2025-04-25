<?php
/**
 * API para obtener detalles de un comercio
 */

// Configurar encabezados
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

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
    // Verificar método de solicitud
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        echo json_encode([
            'success' => false,
            'message' => 'Método no permitido',
            'code' => 405
        ]);
        exit;
    }

    // Verificar y sanitizar ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de comercio no válido',
            'code' => 400
        ]);
        exit;
    }

    $id = intval($_GET['id']);

    // Inicializar controlador
    $controller = new CompanyController();

    // Obtener detalles del comercio
    $result = $controller->getDetail($id);

    // Devolver respuesta
    echo json_encode($result);

} catch (Exception $e) {
    // Registrar error
    error_log("Error en get_company.php: " . $e->getMessage());
    
    // Devolver respuesta de error
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener datos del comercio: ' . $e->getMessage(),
        'code' => 500
    ]);
}
