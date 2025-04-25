<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class UserLogoutController
{
    /**
     * Cierra la sesión y redirige al login
     */
    public function cerrarSesion()
    {
        // Inicia sesión si no está activa
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Opcional: Registrar la acción de cierre de sesión
        if (isset($_SESSION['usuario'])) {
            $this->registrarCierreSesion($_SESSION['usuario']['ID'] ?? null);
        }

        // Destruir sesión
        $_SESSION = array();

        // Destruir cookie de sesión si existe
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        // Obtener la ruta base
        $base_path = '/Acovi';

        // Redirigir al login
        header("Location: $base_path/app/view/login.php");
        exit();
    }

    /**
     * Registra el cierre de sesión (opcional)
     */
    private function registrarCierreSesion($usuarioId)
    {
        // Implementación para registrar el cierre en BD si lo deseas
        // Ejemplo: $model->registrarAccion($usuarioId, 'logout', date('Y-m-d H:i:s'));
    }
}
?>