<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../utils/csrf.php';
$csrf_token = generateCSRFToken();


// Incluir el controlador correcto
require_once __DIR__ . '/../controller/userLoginController.php';

// Inicializar variables
$email = '';
$error = '';
$validation_errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Función de limpieza de datos para prevenir XSS
    function limpiarDatos($datos) {
        $datos = trim($datos);
        $datos = stripslashes($datos);
        $datos = htmlspecialchars($datos);
        return $datos;
    }

    // Validación del correo
    if (empty($_POST['email'])) {
        $validation_errors['email'] = "El correo electrónico es obligatorio.";
    } else {
        $email = limpiarDatos($_POST['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validation_errors['email'] = "Formato de correo electrónico no válido.";
        }
    }

    // Validación de la contraseña
    if (empty($_POST['password'])) {
        $validation_errors['password'] = "La contraseña es obligatoria.";
    } else {
        $password = limpiarDatos($_POST['password']);
    }

    // Si no hay errores de validación, intentar iniciar sesión
    if (empty($validation_errors)) {
        $usuarioLogin = new UsuarioLogin();

        // Implementar protección contra ataques de fuerza bruta
        $intentos_permitidos = 3;
        $tiempo_bloqueo = 0 * 60; //
        
        // Comprobar si hay intentos previos
        if (isset($_SESSION['login_intentos'][$email])) {
            // Si ha superado los intentos permitidos y aún no ha pasado el tiempo de bloqueo
            if ($_SESSION['login_intentos'][$email]['contador'] >= $intentos_permitidos && 
                (time() - $_SESSION['login_intentos'][$email]['tiempo']) < $tiempo_bloqueo) {
                
                $tiempo_restante = $tiempo_bloqueo - (time() - $_SESSION['login_intentos'][$email]['tiempo']);
                $minutos = floor($tiempo_restante / 60);
                $segundos = $tiempo_restante % 60;
                
                $error = "Demasiados intentos fallidos. Por favor, inténtelo de nuevo en {$minutos}:{$segundos} minutos.";
            } else {
                // Si ha pasado el tiempo de bloqueo, reiniciar el contador
                if ($_SESSION['login_intentos'][$email]['contador'] >= $intentos_permitidos && 
                    (time() - $_SESSION['login_intentos'][$email]['tiempo']) >= $tiempo_bloqueo) {
                    
                    $_SESSION['login_intentos'][$email]['contador'] = 0;
                }
                
                // Intentar verificar credenciales
                $usuario = $usuarioLogin->verificarCredenciales($email, $password);
                
                if ($usuario) {
                    // Éxito - resetear contador de intentos
                    unset($_SESSION['login_intentos'][$email]);
                    
                    // Redirigir (el controlador ya se encarga de esto)
                } else {
                    // Incrementar contador de intentos fallidos
                    $_SESSION['login_intentos'][$email]['contador'] = isset($_SESSION['login_intentos'][$email]['contador']) ? 
                        $_SESSION['login_intentos'][$email]['contador'] + 1 : 1;
                    $_SESSION['login_intentos'][$email]['tiempo'] = time();
                    
                    $intentos_restantes = $intentos_permitidos - $_SESSION['login_intentos'][$email]['contador'];
                    
                    if ($intentos_restantes > 0) {
                        $error = "Correo o contraseña incorrectos. Intentos restantes: {$intentos_restantes}.";
                    } else {
                        $error = "Demasiados intentos fallidos. Su cuenta ha sido bloqueada temporalmente por 2 minutos.";
                    }
                }
            }
        } else {
            // Primer intento para este email
            $usuario = $usuarioLogin->verificarCredenciales($email, $password);
            
            if ($usuario) {
                // Éxito - no hay necesidad de hacer nada con el contador
                // La redirección la maneja el controlador
            } else {
                // Inicializar contador de intentos para este email
                $_SESSION['login_intentos'][$email] = [
                    'contador' => 1,
                    'tiempo' => time()
                ];
                
                $error = "Correo o contraseña incorrectos. Intentos restantes: " . ($intentos_permitidos - 1) . ".";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Login - Ayuntamiento Villanueva de la Cañada</title>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-[calc(100vh-180px)] flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <img class="mx-auto h-16 w-auto" src="../view/assets/img/logo.png.png" alt="Logo Ayuntamiento">
            <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Iniciar Sesión</h2>
        </div>
      
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-sm">
            <?php if (!empty($error)) : ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form id="loginForm" class="space-y-6" action="login.php" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div>
                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Correo</label>
                    <div class="mt-2">
                        <input type="email" 
                               name="email" 
                               id="email" 
                               autocomplete="email" 
                               required 
                               value="<?php echo htmlspecialchars($email); ?>"
                               class="block w-full rounded-md border <?php echo isset($validation_errors['email']) ? 'border-red-500' : 'border-gray-300'; ?> px-3 py-2 text-gray-900 shadow-sm placeholder:text-gray-400 focus:ring-2 focus:ring-green-600 focus:border-green-600 sm:text-sm">
                        <?php if (isset($validation_errors['email'])) : ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo $validation_errors['email']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Contraseña</label>
                    <div class="mt-2">
                        <input type="password" 
                               name="password" 
                               id="password" 
                               autocomplete="current-password" 
                               required 
                               class="block w-full rounded-md border <?php echo isset($validation_errors['password']) ? 'border-red-500' : 'border-gray-300'; ?> px-3 py-2 text-gray-900 shadow-sm placeholder:text-gray-400 focus:ring-2 focus:ring-green-600 focus:border-green-600 sm:text-sm">
                        <?php if (isset($validation_errors['password'])) : ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo $validation_errors['password']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            id="submitBtn"
                            class="flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 transition-colors">
                        Iniciar Sesión
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Aviso de Cookies -->
    <div class="aviso-cookies fixed bottom-4 left-4 left-4 sm:left-auto sm:left-4 max-w-md bg-white p-6 rounded-lg shadow-lg z-50 hidden" id="aviso-cookies">
        <div class="flex items-start">
            <img class="w-10 h-10 mr-4" src="../view/assets/img/cookie.svg" alt="Galleta">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Cookies</h3>
                <p class="mt-1 text-sm text-gray-600">Utilizamos cookies propias y de terceros para mejorar nuestros servicios.</p>
                <div class="mt-4 flex items-center">
                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors" id="btn-aceptar-cookies">De acuerdo</button>
                    <a href="../view/Aviso_legal_cookies.html " class="ml-4 text-sm text-green-600 hover:text-green-700 transition-colors">Aviso de Cookies</a>
                </div>
            </div>
        </div>
    </div>
    <div class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" id="fondo-aviso-cookies"></div>

    <!-- Script para el aviso de cookies -->
    <script>
    const botonAceptarCookies = document.getElementById('btn-aceptar-cookies');
    const avisoCookies = document.getElementById('aviso-cookies');
    const fondoAvisoCookies = document.getElementById('fondo-aviso-cookies');

    // Inicializar dataLayer si no existe
    window.dataLayer = window.dataLayer || [];

    if(!localStorage.getItem('cookies-aceptadas')) {
        avisoCookies.classList.remove('hidden');
        fondoAvisoCookies.classList.remove('hidden');
    } else {
        dataLayer.push({'event': 'cookies-aceptadas'});
    }

    botonAceptarCookies.addEventListener('click', () => {
        avisoCookies.classList.add('hidden');
        fondoAvisoCookies.classList.add('hidden');

        localStorage.setItem('cookies-aceptadas', true);

        dataLayer.push({'event': 'cookies-aceptadas'});
    });
    </script>

    <!-- Script para validación del lado del cliente -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Validar email
            if (!emailInput.value.trim()) {
                showError(emailInput, 'El correo electrónico es obligatorio');
                isValid = false;
            } else if (!isValidEmail(emailInput.value.trim())) {
                showError(emailInput, 'Por favor, introduce un correo electrónico válido');
                isValid = false;
            } else {
                removeError(emailInput);
            }
            
            // Validar contraseña
            if (!passwordInput.value.trim()) {
                showError(passwordInput, 'La contraseña es obligatoria');
                isValid = false;
            } else {
                removeError(passwordInput);
            }
            
            // Prevenir envío si no es válido
            if (!isValid) {
                event.preventDefault();
            } else {
                // Deshabilitar el botón para prevenir múltiples envíos
                submitBtn.disabled = true;
                submitBtn.innerText = 'Procesando...';
                submitBtn.classList.add('opacity-75');
            }
        });
        
        // Función para validar email con expresión regular
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        // Función para mostrar error
        function showError(input, message) {
            // Eliminar mensaje de error anterior si existe
            removeError(input);
            
            // Añadir clase de error
            input.classList.add('border-red-500');
            
            // Crear mensaje de error
            const errorMessage = document.createElement('p');
            errorMessage.className = 'mt-1 text-sm text-red-600 validation-error';
            errorMessage.textContent = message;
            
            // Insertar después del input
            input.parentNode.appendChild(errorMessage);
        }
        
        // Función para eliminar error
        function removeError(input) {
            input.classList.remove('border-red-500');
            const container = input.parentNode;
            const error = container.querySelector('.validation-error');
            if (error) {
                container.removeChild(error);
            }
        }
        
        // Limpiar errores al escribir
        emailInput.addEventListener('input', function() {
            if (this.value.trim() && isValidEmail(this.value.trim())) {
                removeError(this);
            }
        });
        
        passwordInput.addEventListener('input', function() {
            if (this.value.trim()) {
                removeError(this);
            }
        });
    });
    </script>

    <footer class="bg-white shadow">
        <div class="max-w-screen-xl mx-auto p-4 md:py-8">
            <div class="sm:flex sm:items-center sm:justify-between">
                <a href="#" class="flex items-center mb-4 sm:mb-0 space-x-3">
                    <img src="../view/assets/img/logo.png.png" class="h-8" alt="Logo Ayuntamiento" />
                    <span class="self-center text-xl font-semibold whitespace-nowrap text-gray-900">
                        Ayuntamiento Villanueva de la Cañada
                    </span>
                </a>
                <ul class="flex flex-wrap items-center mb-6 text-sm font-medium text-gray-500 sm:mb-0">
                    <li>
                        <a href="#" class="hover:text-green-600 transition-colors">Política de Privacidad</a>
                    </li>
                </ul>
            </div>
            <hr class="my-6 border-gray-200 sm:mx-auto lg:my-8" />
            <span class="block text-sm text-gray-500 text-center">
                © 2025 <a href="https://www.ayto-villacanada.es/" class="hover:text-green-600 transition-colors">
                    Ayuntamiento Villanueva de la Cañada
                </a>. Todos los derechos reservados
            </span>
        </div>
    </footer>
    <!-- Incluye el archivo JS externo -->
    <script src="../view/assets/js/cookies.js"></script>
</body>
</html>