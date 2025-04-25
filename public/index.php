<?php
// Si el usuario ya está logueado, lo redirigimos al menú.
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: menu.php");  // Redirigir al menú si ya está logueado
    exit;
}

// Redirigir a login.php si no está logueado
header("Location: login.php");
exit;
?>
