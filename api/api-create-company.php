<?php
/**
 * API para crear un nuevo comercio
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

    // Verificar token CSRF
    
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrf_token)) {
        echo json_encode([
            'success' => false,
            'message' => 'Token de seguridad inválido',
            'code' => 403
        ]);
        exit; 
    }

    // Depuración
    error_log("POST recibido para creación: " . print_r($_POST, true));

    // Sanitizar y recopilar datos
    $data = [
        'nombre' => $_POST['nombre'] ?? '',
        'cif' => $_POST['cif'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? '',
        'estado' => $_POST['estado'] ?? 'Activo',
        'email' => $_POST['email'] ?? '',
        'categoria' => $_POST['categoria'] ?? '',
        'telefono' => $_POST['telefono'] ?? '',
        'web' => $_POST['web'] ?? '',
        'empleados' => $_POST['empleados'] ?? '',
        'forma_juridica' => $_POST['forma_juridica'] ?? '',
        'horario' => $_POST['horario'] ?? '',
        'direccion' => $_POST['direccion'] ?? ''
    ];

    // Añadir log para depuración
    error_log("Datos para creación: " . json_encode($data));

    // Inicializar controlador
    $controller = new CompanyController();

    // Crear comercio
    $result = $controller->create($data);

    // Añadir log para depuración
    error_log("Resultado de la creación: " . json_encode($result));

    // Devolver respuesta
    echo json_encode($result);

} catch (Exception $e) {
    // Registrar error
    error_log("Error en create_company.php: " . $e->getMessage());

    // Devolver respuesta de error
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear comercio: ' . $e->getMessage(),
        'code' => 500
    ]);
}