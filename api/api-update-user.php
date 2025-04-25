<?php
/**
 * API para actualizar un usuario
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

   // Recopilar datos del usuario
   $data = [
      'id' => $_POST['id'] ?? '',
      'nombre' => $_POST['nombre'] ?? '',
      'apellido' => $_POST['apellido'] ?? '',
      'email' => $_POST['email'] ?? '',
      'rol' => $_POST['rol'] ?? '',
      'estado' => $_POST['estado'] ?? ''
   ];

   // Opcional: Actualizar contraseña solo si está presente
   if (!empty($_POST['password'])) {
      $data['password'] = $_POST['password'];
   }

   $controller = new UserController();

   // Método del controlador para actualizar usuario
   $result = $controller->updateUser($data);

   echo json_encode($result);

} catch (Exception $e) {
   error_log("Error en update_user.php: " . $e->getMessage());
   echo json_encode([
      'success' => false,
      'message' => 'Error al actualizar usuario: ' . $e->getMessage(),
      'code' => 500
   ]);
}
