<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * API para actualizar un comercio existente
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
    error_log("POST recibido: " . print_r($_POST, true));

    // Verificar y sanitizar ID - CORREGIDO: ahora tomamos el ID desde diferentes fuentes
    $id = null;

    // Prioridad 1: Campo "id" (el más probable desde la actualización del JS)
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $id = intval($_POST['id']);
    }
    // Prioridad 2: Campo "edit-company-id" (el usado originalmente)
    else if (isset($_POST['edit-company-id']) && is_numeric($_POST['edit-company-id'])) {
        $id = intval($_POST['edit-company-id']);
    }

    if ($id === null) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de comercio no válido o no proporcionado',
            'code' => 400
        ]);
        exit;
    }

    // Sanitizar y recopilar datos
    $data = [
        'nombre' => $_POST['nombre'] ?? '',
        'cif' => $_POST['cif'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? '',
        'estado' => $_POST['estado'] ?? '',
        'email' => $_POST['email'] ?? '',
        'categoria' => $_POST['categoria'] ?? '',
        'telefono' => $_POST['telefono'] ?? '',
        'web' => $_POST['web'] ?? '',
        'empleados' => $_POST['empleados'] ?? '',
        'forma_juridica' => $_POST['forma_juridica'] ?? '',
        'horario' => $_POST['horario'] ?? '',
        'direccion' => $_POST['direccion'] ?? ''
    ];

    // Inicializar controlador
    $controller = new CompanyController();

    // Añadir log para depuración
    error_log("Enviando datos para actualizar comercio ID: $id");
    error_log("Datos: " . json_encode($data));

    // Actualizar comercio
    $result = $controller->update($id, $data);

    // Añadir log para depuración
    error_log("Resultado de la actualización: " . json_encode($result));

    // Devolver respuesta
    echo json_encode($result);

} catch (Exception $e) {
    // Registrar error
    error_log("Error en update_company.php: " . $e->getMessage());

    // Devolver respuesta de error
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar comercio: ' . $e->getMessage(),
        'code' => 500
    ]);
}