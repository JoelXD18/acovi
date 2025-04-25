<?php
// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función para verificar si el usuario está autenticado
function checkSession() {
    // Si no existe la sesión usuario o ha expirado, redirigir al login
    if (!isset($_SESSION['usuario']) ||
        !isset($_SESSION['tiempo_inicio']) ||
        (time() > $_SESSION['tiempo_inicio'] + $_SESSION['tiempo_expiracion'])) {

        // Limpiar la sesión si existe
        if (isset($_SESSION['usuario'])) {
            $_SESSION = array();
            session_destroy();
        }

        // Redirigir al login - Usamos una ruta absoluta desde la raíz del servidor web
        header("Location: /Acovi/app/view/login.php");
        exit();
    }

    // Actualizar el tiempo de inicio para "renovar" la sesión
    $_SESSION['tiempo_inicio'] = time();
}
?>
