<?php
/**
 * API para eliminar un comercio
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
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'success' => false,
            'message' => 'Método no permitido',
            'code' => 405
        ]);
        exit;
    }

    // Verificar token CSRF desde POST, no desde headers
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validateCSRFToken($csrf_token)) {
        echo json_encode([
            'success' => false,
            'message' => 'Token de seguridad inválido',
            'code' => 403
        ]);
        exit;
    }

    // Verificar y sanitizar ID desde POST, no desde GET
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de comercio no válido',
            'code' => 400
        ]);
        exit;
    }

    $id = intval($_POST['id']);

    // Inicializar controlador
    $controller = new CompanyController();

    // Eliminar comercio
    $result = $controller->delete($id);

    // Devolver respuesta
    echo json_encode($result);

} catch (Exception $e) {
    // Registrar error
    error_log("Error en delete_company.php: " . $e->getMessage());

    // Devolver respuesta de error
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar comercio: ' . $e->getMessage(),
        'code' => 500
    ]);
}