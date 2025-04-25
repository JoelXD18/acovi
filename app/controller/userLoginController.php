<?php
require_once __DIR__ . '/../model/userLoginModels.php';

class UsuarioLogin {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioLoginModel();
    }

    public function verificarCredenciales($email, $password) {


        if (session_status() == PHP_SESSION_NONE) {
         session_start();
             }
            
             require_once __DIR__ . '/../utils/csrf.php';
            
            // Validar el token CSRF
            $csrf_token = $_POST['csrf_token'];
            if (!validateCSRFToken($csrf_token)) {
             error_log("❌ CSRF token validation failed for user: $email");
                 die('CSRF token validation failed');
                 } else {
                error_log("✅ CSRF token validation successful for user: $email");
                 }
                
            

        // Sanitizar entradas
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        // Verificar que el email sea válido
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $usuario = $this->usuarioModel->obtenerUsuarioPorEmail($email);

        // Verificación de contraseña
        //if ($usuario && password_verify($password, $usuario['contrasena'])) { REMPLAZAR ABAJO ESTO CUANDO YA ESTE IMPLEMENTADO EL USUARIO ENCRIPTADO
        if ($usuario && $password === $usuario['contrasena']) {
            // Llamar al metodo para insertar el registro de login
            $resultadoInsert = $this->usuarioModel->insertarRegistroLogin($usuario['correo']);

            // Verificar si la inserción es exitosa o no
            if ($resultadoInsert !== true) {
                error_log("❌ Error al insertar el registro de login: " . $resultadoInsert);
            } else {
                error_log("✅ Registro de login insertado correctamente.");
            }

            // Continuamos con el registro de inicio de sesión
            $this->registrarInicioSesion($usuario['ID'], true);

            // Establecer la sesión
            if (!isset($_SESSION)) {
                session_start();

                
            }

            session_regenerate_id(true);

            $_SESSION['usuario'] = [
                'ID' => $usuario['ID'],
                'Nombre' => $usuario['Nombre'],
                'Correo' => $usuario['correo']
            ];

            // Para compatibilidad con panel
            $_SESSION['username'] = $usuario['Nombre'];

            $_SESSION['tiempo_inicio'] = time();
            $_SESSION['tiempo_expiracion'] = 600; // 10 minutos

            // Redirigir al panel
            header("Location: /Acovi/app/view/panel.php");
            exit();
        }

        // Si las credenciales son incorrectas, registrar el intento fallido
        if ($usuario) {
            $this->registrarInicioSesion($usuario['ID'], false);
        }

        return false;
    }

    private function registrarInicioSesion($usuarioId, $exitoso) {
        // Opcional: Guardar registro de inicio de sesión (exitoso o fallido)
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        if ($exitoso) {
            error_log("✅ Inicio de sesión exitoso - Usuario ID: $usuarioId, IP: $ip");
        } else {
            error_log("❌ Intento fallido - Usuario ID: $usuarioId, IP: $ip");
        }
    }
}
?>