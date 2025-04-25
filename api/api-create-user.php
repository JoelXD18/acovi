<?php
/**
 * API para crear un nuevo usuario
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once '../config/database.php';
require_once '../app/model/userModel.php';
require_once '../app/controller/userController.php';
require_once '../app/utils/csrf.php';

session_start();

// Verificar autenticación opcionalmente
// if (!isset($_SESSION['user_id'])) {
//     echo json_encode([
//         'success' => false,
//         'message' => 'No autorizado',
//         'code' => 401
//     ]);
//     exit;
// }

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

   // Sanitizar y recopilar datos del usuario
   $data = [
      'nombre' => $_POST['nombre'] ?? '',
      'apellido' => $_POST['apellido'] ?? '',
      'email' => $_POST['email'] ?? '',
      'password' => $_POST['password'] ?? '',  // Asegúrate de encriptarla en el controlador
      'rol' => $_POST['rol'] ?? 'usuario',     // rol por defecto
      'estado' => $_POST['estado'] ?? 'activo' // estado por defecto
   ];

   // Inicializar controlador de usuarios
   $controller = new UserController();

   // Crear usuario
   $result = $controller->createUser($data);

   echo json_encode($result);

} catch (Exception $e) {
   error_log("Error en create_user.php: " . $e->getMessage());
   echo json_encode([
      'success' => false,
      'message' => 'Error al crear usuario: ' . $e->getMessage(),
      'code' => 500
   ]);
}
