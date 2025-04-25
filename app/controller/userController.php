<?php
require_once BASE_PATH . '/app/model/userModel.php';
 
class UserController
{
   public function obtenerUsuarios()
   {
      return UserModel::obtenerUsuarios();
   }
 
   public function guardarUsuario($datos)
   {
      $datos = array_map('trim', $datos);
 
      if (
         empty($datos['nombre']) || empty($datos['apellidos']) ||
         empty($datos['email']) || empty($datos['username']) ||
         empty($datos['password']) || empty($datos['rol'])
      ) {
         $_SESSION['mensaje'] = "Todos los campos son obligatorios";
         $_SESSION['tipo_mensaje'] = "error";
         return;
      }
 
      if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
         $_SESSION['mensaje'] = "El formato del email no es válido";
         $_SESSION['tipo_mensaje'] = "error";
         return;
      }
 
      if (UserModel::usuarioExiste($datos['username'], $datos['email'])) {
         $_SESSION['mensaje'] = "Ya existe un usuario con ese nombre de usuario o email";
         $_SESSION['tipo_mensaje'] = "error";
         return;
      }
 
      $datos['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
 
      if (UserModel::guardarUsuario($datos)) {
         $_SESSION['mensaje'] = "Usuario guardado correctamente";
         $_SESSION['tipo_mensaje'] = "success";
      } else {
         $_SESSION['mensaje'] = "Error al guardar el usuario";
         $_SESSION['tipo_mensaje'] = "error";
      }
 
      header("Location: " . $_SERVER['PHP_SELF']);
      exit;
   }
 
   public function actualizarUsuario($id, $datos)
   {
      $datos = array_map('trim', $datos);
 
      if (
         empty($datos['nombre']) || empty($datos['apellidos']) ||
         empty($datos['email']) || empty($datos['username']) ||
         empty($datos['rol'])
      ) {
         $_SESSION['mensaje'] = "Los campos obligatorios están incompletos";
         $_SESSION['tipo_mensaje'] = "error";
         return false;
      }
 
      if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
         $_SESSION['mensaje'] = "El formato del email no es válido";
         $_SESSION['tipo_mensaje'] = "error";
         return false;
      }
 
      if (UserModel::usuarioDuplicadoAlActualizar($id, $datos['username'], $datos['email'])) {
         $_SESSION['mensaje'] = "Ya existe otro usuario con ese nombre de usuario o email";
         $_SESSION['tipo_mensaje'] = "error";
         return false;
      }
 
      if (!empty($datos['password'])) {
         $datos['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
      } else {
         unset($datos['password']);
      }
 
      if (UserModel::actualizarUsuario($id, $datos)) {
         $_SESSION['mensaje'] = "Usuario actualizado correctamente";
         $_SESSION['tipo_mensaje'] = "success";
         return true;
      } else {
         $_SESSION['mensaje'] = "Error al actualizar el usuario";
         $_SESSION['tipo_mensaje'] = "error";
         return false;
      }
   }
 
   public function obtenerUsuarioPorId($id)
   {
      return UserModel::obtenerUsuarioPorId($id);
   }
}