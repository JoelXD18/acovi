<?php
/**
 * API para eliminar un usuario
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once '../config/database.php';
require_once '../app/model/userModel.php';
require_once '../app/controller/userController.php';
require_once '../app/utils/csrf.php';

session_start();

// Verificación opcional de sesión/autenticación

try {
   if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode([
         'success' => false,
         'message' => 'Método no permitido',
         'code' => 405
      ]);
      exit;
   }

   // Verificación del token CSRF
   $csrf_token = $_POST['csrf_token'] ?? '';
   if (!validateCSRFToken($csrf_token)) {
      echo json_encode([
         'success' => false,
         'message' => 'Token de seguridad inválido',
         'code' => 403
      ]);
      exit;
   }

   // Obtener el ID del usuario
   $id = $_POST['id'] ?? '';

   if (empty($id)) {
      echo json_encode([
         'success' => false,
         'message' => 'ID de usuario requerido',
         'code' => 400
      ]);
      exit;
   }

   $controller = new UserController();

   // Método del controlador para eliminar usuario
   $result = $controller->deleteUser($id);

   echo json_encode($result);

} catch (Exception $e) {
   error_log("Error en delete_user.php: " . $e->getMessage());
   echo json_encode([
      'success' => false,
      'message' => 'Error al eliminar usuario: ' . $e->getMessage(),
      'code' => 500
   ]);
}
