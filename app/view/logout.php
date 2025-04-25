<?php
require_once __DIR__ . '/../controller/UserLogoutController.php';
$controller = new UserLogoutController();
$controller->cerrarSesion();

/*** EJEMPLO DE USO EN PÁGINA PROTEGIDA (app/view/panel.php) ***/
require_once __DIR__ . '../../includes/authGuard.php';
AuthGuard::protect(); // Redirecciona automáticamente si no hay sesión

// El resto de tu código para la página panel...
?>
<!DOCTYPE html>
<html>
<head>
    <title>Panel de Administración</title>
</head>
<body>
<header>
    <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']['Nombre']); ?></h1>
    <nav>
        <!-- Enlace de cierre de sesión que lleva a logout.php -->
        <a href="/Acovi/app/view/logout.php">Cerrar sesión</a>
    </nav>
</header>

<main>
    <!-- Contenido del panel -->
</main>
</body>
</html>