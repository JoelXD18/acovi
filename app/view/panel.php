<?php
//hola
// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir archivos de configuración y clases
require_once '../../includes/authGuard.php';
require_once '../../config/database.php';
require_once '../model/companyModel.php';
require_once '../controller/companyController.php';
//Token?
require_once '../../app/utils/csrf.php';
$csrf_token = generateCSRFToken();
// Generar token si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

checkSession();
// Verificar si ya existe una preferencia de tema
if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = 'light'; // Por defecto modo claro
}

// Cambiar el tema si se solicita
if (isset($_GET['theme']) && in_array($_GET['theme'], ['light', 'dark'])) {
    $_SESSION['theme'] = $_GET['theme'];
    // Redireccionar para eliminar el parámetro de la URL
    $redirectUrl = strtok($_SERVER['REQUEST_URI'], '?') . '?' . http_build_query(array_diff_key($_GET, ['theme' => '']));
    header('Location: ' . $redirectUrl);
    exit;
}

$currentTheme = $_SESSION['theme'];


// Inicializar controlador
$companyController = new CompanyController();

// Paginación
$itemsPerPage = isset($_GET['items_per_page']) ? intval($_GET['items_per_page']) : 20;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Filtros
$filters = [
    'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
    'estado' => isset($_GET['estado']) ? trim($_GET['estado']) : '',
    'categoria' => isset($_GET['categoria']) ? intval($_GET['categoria']) : 0,
    'tipo' => isset($_GET['tipo']) ? trim($_GET['tipo']) : ''
];

// Ordenamiento
$orderBy = isset($_GET['order_by']) ? trim($_GET['order_by']) : 'nombre';
$orderDir = isset($_GET['order_dir']) ? strtoupper(trim($_GET['order_dir'])) : 'ASC';

// Obtener datos
$result = $companyController->index($filters, $page, $itemsPerPage, $orderBy, $orderDir);

// Inicializar variables con valores predeterminados
$comercios = [];
$totalItems = 0;
$totalPages = 0;

// Verificar si la solicitud fue exitosa
if ($result['success']) {
    // Asegurar que los datos existen y tienen la estructura esperada
    if (
        isset($result['data']) &&
        isset($result['data']['data']) &&
        is_array($result['data']['data'])
    ) {

        $comercios = $result['data']['data'];
        $totalItems = $result['data']['total'] ?? 0;
        $totalPages = $result['data']['pages'] ?? 0;

        // Procesar datos de los comercios
        foreach ($comercios as &$comercio) {
            // Asegurar que todos los campos existan
            $comercio['nombre'] = $comercio['Nombre'] ?? $comercio['nombre'] ?? 'Sin nombre';
            $comercio['cif'] = $comercio['CIF'] ?? $comercio['cif'] ?? 'N/A';
            $comercio['descripcion'] = $comercio['Descripcion'] ?? $comercio['descripcion'] ?? 'Sin descripción';
            $comercio['estado'] = $comercio['Estado'] ?? $comercio['estado'] ?? 'Desconocido';
            $comercio['email'] = $comercio['Email'] ?? $comercio['email'] ?? 'N/A';
            $comercio['categoria'] = $comercio['categoria'] ?? 'N/A';
            $comercio['telefono_principal'] = $comercio['telefono_principal'] ?? 'N/A';
            $comercio['direccion'] = $comercio['direccion'] ?? 'N/A';
            $comercio['horario'] = $comercio['horario'] ?? 'N/A';
        }
    } else {
        // Log de estructura inesperada
        error_log("Estructura de datos inesperada: " . print_r($result, true));
    }
} else {
    // Manejar el error
    $errorMessage = $result['message'] ?? 'Error desconocido al recuperar comercios';
    error_log("Error en la recuperación de comercios: " . $errorMessage);
}

// Obtener el total sin filtros de manera segura
try {
    $totalCountResult = $companyController->index([], 1, 1);
    $totalCount = $totalCountResult['success'] && isset($totalCountResult['data']['total'])
        ? $totalCountResult['data']['total']
        : 0;
} catch (Exception $e) {
    $totalCount = 0;
    error_log("Error al obtener el total de comercios: " . $e->getMessage());
}

// Obtener categorías para filtros
$categoriesResult = $companyController->getCategories();
$categories = $categoriesResult['success'] ? $categoriesResult['data'] : [];
?>
<!DOCTYPE html>
<html lang="es" class="<?php echo $currentTheme === 'dark' ? 'dark' : ''; ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">


    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <title>Ayto. Villanueva de la Cañada - Panel de Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#10B981', // Verde
                        'primary-dark': '#059669',
                        'secondary': '#4B5563', // Gris
                        'tertiary': '#4F46E5', // Índigo
                    },
                    boxShadow: {
                        'card': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                        'card-hover': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
                    },
                    transitionProperty: {
                        'height': 'height',
                        'spacing': 'margin, padding',
                    },
                    animation: {
                        'modal-in': 'modalIn 0.3s ease-out forwards',
                        'fade-in': 'fadeIn 0.3s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        modalIn: {
                            '0%': { opacity: 0, transform: 'scale(0.95)' },
                            '100%': { opacity: 1, transform: 'scale(1)' }
                        },
                        fadeIn: {
                            '0%': { opacity: 0 },
                            '100%': { opacity: 1 }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(10px)', opacity: 0 },
                            '100%': { transform: 'translateY(0)', opacity: 1 }
                        }
                    }
                }
            },
            darkMode: 'class' // Habilitamos modo oscuro
        }
    </script>
    <!-- SheetJS para Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- FileSaver.js para descargar archivos -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <!-- Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Estilos mejorados para la tabla y barras de desplazamiento */
        /* Contenedor principal para la tabla */
        .table-container {
            max-height: 65vh;
            overflow: hidden;
            width: 100%;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin: 0 auto;
        }

        /* Contenedor interno con scroll vertical */
        .table-scroll {
            max-height: 65vh;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #10B981 #F9FAFB;
            border-radius: 0.5rem;
        }

        /* Estilos para barras de desplazamiento personalizadas */
        .table-scroll::-webkit-scrollbar {
            width: 12px;
            height: 12px;
        }

        .table-scroll::-webkit-scrollbar-track {
            background: #F9FAFB;
            border-radius: 8px;
        }

        .table-scroll::-webkit-scrollbar-thumb {
            background-color: #D1D5DB;
            border-radius: 8px;
            border: 3px solid #F9FAFB;
        }

        .table-scroll::-webkit-scrollbar-thumb:hover {
            background-color: #10B981;
        }

        /* Contenedor para scroll horizontal */
        .horizontal-scroll {
            overflow-x: auto;
            width: 100%;
            scrollbar-width: thin;
            scrollbar-color: #10B981 #F9FAFB;
        }

        .horizontal-scroll::-webkit-scrollbar {
            width: 12px;
            height: 12px;
        }

        .horizontal-scroll::-webkit-scrollbar-track {
            background: #F9FAFB;
            border-radius: 8px;
        }

        .horizontal-scroll::-webkit-scrollbar-thumb {
            background-color: #D1D5DB;
            border-radius: 8px;
            border: 3px solid #F9FAFB;
        }

        .horizontal-scroll::-webkit-scrollbar-thumb:hover {
            background-color: #10B981;
        }

        /* Estilos para la tabla */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: auto;
        }

        /* Estilos para las cabeceras de la tabla */
        .data-table th {
            position: sticky;
            top: 0;
            background-color: #F0FDF4;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem 1.25rem;
            text-align: left;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #065F46;
            white-space: nowrap;
            min-width: 120px;
            transition: background-color 0.2s ease;
        }

        .data-table th:hover {
            background-color: #E0F2FE;
        }

        /* Estilos para las celdas de la tabla */
        .data-table td {
            padding: 0.875rem 1.25rem;
            font-size: 0.925rem;
            vertical-align: middle;
            border-bottom: 1px solid #F3F4F6;
        }

        /* Estilos para las filas de la tabla al pasar el mouse */
        .data-table tbody tr {
            transition: all 0.2s ease;
        }

        .data-table tbody tr:hover {
            background-color: #F0FDF4;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* Estilos para truncar texto largo con tooltip */
        .truncate-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            position: relative;
        }

        /* Botones de acción mejorados */
        .action-btn {
            padding: 8px;
            border-radius: 50%;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .view-btn {
            background-color: #EFF6FF;
            color: #3B82F6;
        }

        .view-btn:hover {
            background-color: #3B82F6;
            color: white;
            transform: scale(1.1);
        }

        .edit-btn {
            background-color: #EEF2FF;
            color: #6366F1;
        }

        .edit-btn:hover {
            background-color: #6366F1;
            color: white;
            transform: scale(1.1);
        }

        .delete-btn {
            background-color: #FEF2F2;
            color: #EF4444;
        }

        .delete-btn:hover {
            background-color: #EF4444;
            color: white;
            transform: scale(1.1);
        }

        /* Animaciones para elementos de la interfaz */
        .fade-in {
            animation: fadeInAnimation 0.5s ease-in-out;
        }

        @keyframes fadeInAnimation {
            0% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }

        /* Modales mejorados */
        .modal-container {
            @apply fixed inset-0 overflow-y-auto z-40;
            backdrop-filter: blur(2px);
        }

        .modal-content {
            @apply inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full;
            animation: modalIn 0.3s ease-out forwards;
        }

        @keyframes modalIn {
            0% {
                opacity: 0;
                transform: scale(0.95);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Asegurar que el texto se vea bien en modo oscuro */
        .dark input,
        .dark select,
        .dark textarea {
            color-scheme: dark;
        }

        .dark .modal-form label,
        .dark .modal-form p,
        .dark .modal-form input,
        .dark .modal-form select,
        .dark .modal-form textarea,
        .dark .modal-content h3 {
            color: #F9FAFB !important;
        }

        /* Estilos para modo oscuro */
        .dark .table-scroll::-webkit-scrollbar-track {
            background: #1F2937;
        }

        .dark .table-scroll::-webkit-scrollbar-thumb {
            background-color: #4B5563;
            border: 3px solid #1F2937;
        }

        .dark .data-table th {
            background-color: #1F2937;
            color: #E5E7EB;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
        }

        .dark .data-table th:hover {
            background-color: #2D3748;
        }

        .dark .data-table td {
            border-bottom: 1px solid #374151;
        }

        .dark .data-table tbody tr:hover {
            background-color: #2D3748;
        }

        .dark .view-btn {
            background-color: rgba(59, 130, 246, 0.2);
        }

        .dark .edit-btn {
            background-color: rgba(99, 102, 241, 0.2);
        }

        .dark .delete-btn {
            background-color: rgba(239, 68, 68, 0.2);
        }

        /* Input focus para modo oscuro */
        .dark input:focus,
        .dark select:focus,
        .dark textarea:focus {
            border-color: #10B981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
        }

        /* Dropdown menu */
        .dropdown-menu {
            display: none;
            z-index: 20;
            transform-origin: top right;
            animation: dropdownAnimation 0.2s ease-out;
        }

        @keyframes dropdownAnimation {
            0% {
                opacity: 0;
                transform: scale(0.95);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .dropdown:hover .dropdown-menu {
            display: block;
        }

        /* Animación para tooltip mejorado */
        [data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 0;
            top: calc(100% + 5px);
            z-index: 50;
            min-width: 150px;
            max-width: 300px;
            background-color: #333;
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            opacity: 0;
            pointer-events: none;
            animation: tooltip 0.3s ease-out forwards;
        }

        @keyframes tooltip {
            0% {
                opacity: 0;
                transform: translateY(5px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mejora de impresión */
        @media print {

            .table-container,
            .table-scroll,
            .horizontal-scroll {
                max-height: none;
                overflow: visible;
            }

            .data-table {
                width: 100%;
                table-layout: auto;
            }

            .data-table th {
                position: static;
                box-shadow: none;
                border-bottom: 1px solid #E5E7EB;
            }

            .truncate-cell {
                max-width: none;
                white-space: normal;
                overflow: visible;
                text-overflow: clip;
            }

            /* Ocultar elementos no necesarios para impresión */
            nav,
            button,
            .no-print {
                display: none !important;
            }

            /* Expandir contenido para usar todo el ancho de la página */
            main,
            .max-w-full {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }

        /* Tooltip personalizado */
        .tooltip {
            position: relative;
            display: inline-block;
        }

        .tooltip .tooltip-text {
            visibility: hidden;
            width: 180px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -90px;
            opacity: 0;
            transition: opacity 0.3s, transform 0.3s;
            transform: translateY(10px);
        }

        .tooltip .tooltip-text::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #333 transparent transparent transparent;
        }

        .tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
            transform: translateY(0);
        }

        /* Switch de tema mejorado */
        .theme-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .theme-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #10B981;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #10B981;
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        .slider .sun {
            position: absolute;
            left: 4px;
            top: 4px;
            font-size: 12px;
            color: #f39c12;
        }

        .slider .moon {
            position: absolute;
            right: 4px;
            top: 4px;
            font-size: 12px;
            color: #f1c40f;
        }

        /* Filtros compactos */
        .compact-filters .form-group {
            margin-bottom: 0.5rem;
        }

        .compact-filters label {
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }

        .compact-filters select,
        .compact-filters input {
            padding-top: 0.375rem;
            padding-bottom: 0.375rem;
        }

        /* Badge estilo pill mejorado */
        .badge-pill {
            border-radius: 9999px;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s;
        }

        .badge-pill-primary {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10B981;
        }

        .badge-pill-secondary {
            background-color: rgba(99, 102, 241, 0.1);
            color: #6366F1;
        }

        .dark .badge-pill-primary {
            background-color: rgba(16, 185, 129, 0.2);
            color: #34D399;
        }

        .dark .badge-pill-secondary {
            background-color: rgba(99, 102, 241, 0.2);
            color: #A5B4FC;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-200 min-h-screen flex flex-col">
    <!-- Notificación de acción exitosa -->
    <div id="success-notification" aria-live="assertive"
        class="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 sm:items-start z-50 hidden">
        <div class="w-full flex flex-col items-center space-y-4 sm:items-end">
            <div
                class="max-w-sm w-full bg-white dark:bg-gray-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100" id="notification-message">
                                Acción completada con éxito
                            </p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button id="close-notification"
                                class="bg-white dark:bg-gray-800 rounded-md inline-flex text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <span class="sr-only">Cerrar</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notificación de error -->
    <div id="error-notification" aria-live="assertive"
        class="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 sm:items-start z-50 hidden">
        <div class="w-full flex flex-col items-center space-y-4 sm:items-end">
            <div
                class="max-w-sm w-full bg-white dark:bg-gray-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100" id="error-message">
                                Ha ocurrido un error
                            </p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button id="close-error-notification"
                                class="bg-white dark:bg-gray-800 rounded-md inline-flex text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <span class="sr-only">Cerrar</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal de confirmación para eliminar -->
    <div id="delete-confirmation-modal" class="fixed z-30 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-backdrop dark:bg-gray-800 dark:bg-opacity-75"
                aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full animate-modal-in">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 modal-form">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                Eliminar comercio
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    ¿Está seguro de que desea eliminar este comercio? Esta acción
                                    no se puede deshacer.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="confirm-delete-btn"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        <i class="fa-solid fa-trash mr-2"></i>Eliminar
                    </button>
                    <button type="button" id="cancel-delete-btn"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para ver detalles -->
    <div id="detail-modal" class="fixed z-30 inset-0 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-backdrop dark:bg-gray-800 dark:bg-opacity-75"
                aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full animate-modal-in">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 modal-form">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-xl leading-6 font-semibold text-gray-900 dark:text-gray-100"
                                id="modal-title">
                                Detalles del Comercio
                            </h3>
                            <div class="mt-6" id="detail-content">
                                <!-- El contenido se cargará dinámicamente -->
                                <div class="animate-pulse">
                                    <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/4 mb-4"></div>
                                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
                                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-4"></div>
                                    <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/4 mb-4"></div>
                                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-full mb-2"></div>
                                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-4"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="edit-from-detail-btn"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        <i class="fa-solid fa-pen mr-2"></i>Editar
                    </button>
                    <button type="button"
                        class="modal-close mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para editar empresa -->
    <div id="edit-modal" class="fixed inset-0 overflow-y-auto hidden z-40 modal-backdrop"
        aria-labelledby="edit-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-backdrop dark:bg-gray-800 dark:bg-opacity-75"
                aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full animate-modal-in">
                <div
                    class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[85vh] overflow-y-auto modal-form">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-xl leading-6 font-semibold text-gray-900 dark:text-gray-100"
                                id="edit-modal-title">
                                Editar Comercio
                            </h3>
                            <div class="mt-6">
                                <form id="edit-form" class="space-y-6">
                                    <input type="hidden" name="edit-company-id" id="edit-company-id" />
                                    <!--<input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>" />-->

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="edit-nombre"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre
                                                <span class="text-red-500">*</span></label>
                                            <input type="text" name="nombre" id="edit-nombre" required
                                                class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" />
                                            <p id="edit-nombre-error"
                                                class="mt-1 text-sm text-red-600 dark:text-red-400 hidden"></p>
                                        </div>
                                        <div>
                                            <label for="edit-cif"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">CIF
                                                <span class="text-red-500">*</span></label>
                                            <input type="text" name="cif" id="edit-cif" required
                                                class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" />
                                            <p id="edit-cif-error"
                                                class="mt-1 text-sm text-red-600 dark:text-red-400 hidden"></p>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="edit-descripcion"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                                        <textarea name="descripcion" id="edit-descripcion" rows="3" maxlength="500"
                                            class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"></textarea>
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 flex justify-end">
                                            <span id="edit-descripcion-count">0</span>/500 caracteres
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="edit-estado"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado
                                                <span class="text-red-500">*</span></label>
                                            <select name="estado" id="edit-estado" required
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm dark:text-white">
                                                <option value="Activo">Activo</option>
                                                <option value="No activo">No activo</option>
                                                <option value="Pendiente">Pendiente</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="edit-email"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                            <input type="email" name="email" id="edit-email"
                                                class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="edit-telefono"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono</label>
                                            <input type="tel" name="telefono" id="edit-telefono" pattern="[0-9]{9}"
                                                class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" />
                                        </div>
                                        <div>
                                            <label for="edit-web"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Página
                                                Web</label>
                                            <input type="url" name="web" id="edit-web"
                                                class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="edit-empleados"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número
                                                de
                                                empleados</label>
                                            <input type="number" name="empleados" id="edit-empleados" min="0"
                                                class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" />
                                        </div>
                                        <div>
                                            <label for="edit-forma-juridica"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Forma
                                                Jurídica</label>
                                            <select name="forma_juridica" id="edit-forma-juridica"
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm dark:text-white">
                                                <option value="">Seleccionar...</option>
                                                <option value="Autónomo">Autónomo</option>
                                                <option value="S.L.">Sociedad Limitada (S.L.)</option>
                                                <option value="S.A.">Sociedad Anónima (S.A.)</option>
                                                <option value="Cooperativa">Cooperativa</option>
                                                <option value="Otra">Otra</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="edit-categoria"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría
                                                <span class="text-red-500">*</span></label>
                                            <select name="categoria" id="edit-categoria" required
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm dark:text-white">
                                                <option value="">Seleccionar...</option>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?php echo $cat['ID']; ?>">
                                                        <?php echo htmlspecialchars($cat['Principal']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <p id="edit-categoria-error"
                                                class="mt-1 text-sm text-red-600 dark:text-red-400 hidden"></p>
                                        </div>
                                        <div>
                                            <label for="edit-horario"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Horario</label>
                                            <input type="text" name="horario" id="edit-horario"
                                                class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" />
                                        </div>
                                    </div>

                                    <div>
                                        <label for="edit-direccion"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                                        <input type="text" name="direccion" id="edit-direccion"
                                            class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" />
                                    </div>

                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        <span class="text-red-500">*</span> Campos obligatorios
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="save-edit-btn"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        <i class="fa-solid fa-save mr-2"></i>Guardar cambios
                    </button>
                    <button type="button"
                        class="modal-close mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors"
                        data-modal="edit-modal" id="close-edit-modal-btn">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para registrar empresa -->
    <div id="register-modal" class="fixed inset-0 overflow-y-auto hidden z-40" aria-labelledby="register-modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-backdrop dark:bg-gray-800 dark:bg-opacity-75"
                aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full animate-modal-in">
                <div
                    class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[85vh] overflow-y-auto modal-form">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-xl leading-6 font-semibold text-gray-900 dark:text-gray-100"
                                id="register-modal-title">
                                Registrar Nuevo Comercio
                            </h3>
                            <div class="mt-6">
                                <form id="register-form" class="space-y-6">
                                <!--<input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>" />-->

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="register-nombre"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre
                                                <span class="text-red-500">*</span></label>
                                            <input type="text" name="nombre" id="register-nombre" required
                                                class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" />
                                            <p id="register-nombre-error"
                                                class="mt-1 text-sm text-red-600 dark:text-red-400 hidden"></p>
                                        </div>
                                        <div>
                                            <label for="register-cif"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">CIF
                                                <span class="text-red-500">*</span></label>
                                            <input type="text" name="cif" id="register-cif" required
                                                class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" />
                                            <p id="register-cif-error"
                                                class="mt-1 text-sm text-red-600 dark:text-red-400 hidden"></p>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="register-descripcion"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                                        <textarea name="descripcion" id="register-descripcion" rows="3" maxlength="500"
                                            class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"></textarea>
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 flex justify-end">
                                            <span id="register-descripcion-count">0</span>/500 caracteres
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="register-estado"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado
                                                <span class="text-red-500">*</span></label>
                                            <select name="estado" id="register-estado" required
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm dark:text-white">
                                                <option value="Activo">Activo</option>
                                                <option value="No activo">No activo</option>
                                                <option value="Pendiente">Pendiente</option>
                                            </select>
                                            <p id="register-estado-error"
                                                class="mt-1 text-sm text-red-600 dark:text-red-400 hidden"></p>
                                        </div>
                                        <div>
                                            <label for="register-email"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                            <input type="email" name="email" id="register-email"
                                                class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" />
                                            <p id="register-email-error"
                                                class="mt-1 text-sm text-red-600 dark:text-red-400 hidden"></p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="register-telefono"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono</label>
                                            <input type="tel" name="telefono" id="register-telefono" pattern="[0-9]{9}"
                                                class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" />
                                            <p id="register-telefono-error"
                                                class="mt-1 text-sm text-red-600 dark:text-red-400 hidden">
                                            </p>
                                        </div>
                                        <div>
                                            <label for="register-web"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Página
                                                Web</label>
                                            <input type="url" name="web" id="register-web"
                                                class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" />
                                            <p id="register-web-error"
                                                class="mt-1 text-sm text-red-600 dark:text-red-400 hidden"></p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="register-empleados"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número
                                                de
                                                empleados</label>
                                            <input type="number" name="empleados" id="register-empleados" min="0"
                                                class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" />
                                            <p id="register-empleados-error"
                                                class="mt-1 text-sm text-red-600 dark:text-red-400 hidden">
                                            </p>
                                        </div>
                                        <div>
                                            <label for="register-forma-juridica"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Forma
                                                Jurídica</label>
                                            <select name="forma_juridica" id="register-forma-juridica"
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm dark:text-white">
                                                <option value="">Seleccionar...</option>
                                                <option value="Autónomo">Autónomo</option>
                                                <option value="S.L.">Sociedad Limitada (S.L.)</option>
                                                <option value="S.A.">Sociedad Anónima (S.A.)</option>
                                                <option value="Cooperativa">Cooperativa</option>
                                                <option value="Otra">Otra</option>
                                            </select>
                                            <p id="register-forma-juridica-error"
                                                class="mt-1 text-sm text-red-600 dark:text-red-400 hidden"></p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="register-categoria"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría
                                                <span class="text-red-500">*</span></label>
                                            <select name="categoria" id="register-categoria" required
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm dark:text-white">
                                                <option value="">Seleccionar...</option>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?php echo $cat['ID']; ?>">
                                                        <?php echo htmlspecialchars($cat['Principal']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <p id="register-categoria-error"
                                                class="mt-1 text-sm text-red-600 dark:text-red-400 hidden">
                                            </p>
                                        </div>
                                        <div>
                                            <label for="register-horario"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Horario</label>
                                            <input type="text" name="horario" id="register-horario"
                                                placeholder="Ej: Lun-Vie 9:00-14:00 y 17:00-20:00"
                                                class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" />
                                            <p id="register-horario-error"
                                                class="mt-1 text-sm text-red-600 dark:text-red-400 hidden"></p>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="register-direccion"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                                        <input type="text" name="direccion" id="register-direccion"
                                            placeholder="Ej: Calle Principal 23, Villanueva de la Cañada"
                                            class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" />
                                        <p id="register-direccion-error"
                                            class="mt-1 text-sm text-red-600 dark:text-red-400 hidden"></p>
                                    </div>

                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        <span class="text-red-500">*</span> Campos obligatorios
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="save-register-btn"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        <i class="fa-solid fa-save mr-2"></i>Guardar comercio
                    </button>
                    <button type="button" id="close-register-modal-btn"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Página del dashboard -->
    <div id="dashboard-page" class="min-h-screen flex flex-col">
        <!-- Barra de navegación superior -->
        <nav class="bg-white dark:bg-gray-800 shadow transition-colors duration-200">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 flex items-center">
                            <img src="assets/img/logo.png.png" alt="Logo Ayuntamiento" class="block h-10 w-auto" />
                            <span
                                class="text-lg sm:text-xl font-bold text-gray-800 dark:text-gray-100 ml-2 hidden md:block">Ayuntamiento
                                Villanueva de la Cañada</span>
                        </div>
                        <div class="hidden md:ml-6 md:flex md:space-x-8">
                            <a href="panel.php"
                                class="border-green-500 text-gray-900 dark:text-white border-b-2 inline-flex items-center px-1 pt-1 text-sm font-medium"
                                aria-current="page">
                                <i class="fa-solid fa-database mr-1"></i> Base de datos
                            </a>
                            <a href="users.php"
                                class="border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                <i class="fa-solid fa-users mr-1"></i> Usuarios
                            </a>
                        </div>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:items-center space-x-4">
                        <!-- Selector de tema -->
                        <div class="flex items-center">
                            <span class="text-sm text-gray-700 dark:text-gray-300 mr-2">Tema:</span>
                            <label class="theme-switch">
                                <input type="checkbox" id="theme-toggle" <?php echo $currentTheme === 'dark' ? 'checked' : ''; ?>>
                                <span class="slider round">
                                    <i class="fa-solid fa-sun sun"></i>
                                    <i class="fa-solid fa-moon moon"></i>
                                </span>
                            </label>
                        </div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Hola,
                            <?php echo htmlspecialchars($_SESSION['username'] ?? 'usuario'); ?></span>
                        <!-- Botón de cerrar sesión simplificado -->
                        <a href="logout.php"
                            class="px-3 py-1 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-md text-sm hover:bg-red-200 dark:hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800 transition-colors">
                            <i class="fa-solid fa-right-from-bracket mr-1"></i>Cerrar sesión
                        </a>
                    </div>
                    <div class="-mr-2 flex items-center md:hidden">
                        <!-- Mobile menu button -->
                        <button type="button" id="mobile-menu-button"
                            class="bg-white dark:bg-gray-800 inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-green-500 transition-colors"
                            aria-controls="mobile-menu" aria-expanded="false">
                            <span class="sr-only">Abrir menú principal</span>
                            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu, show/hide based on menu state. -->
            <div class="md:hidden hidden" id="mobile-menu">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="panel.php"
                        class="bg-green-50 dark:bg-green-900 border-green-500 text-green-700 dark:text-green-300 block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
                        aria-current="page">
                        <i class="fa-solid fa-database mr-1"></i> Base de datos
                    </a>
                    <a href="users.php"
                        class="border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        <i class="fa-solid fa-users mr-1"></i> Usuarios
                    </a>
                </div>
                <div class="pt-4 pb-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between px-4">
                        <div class="text-base font-medium text-gray-800 dark:text-gray-200">
                            Hola, <?php echo htmlspecialchars($_SESSION['username'] ?? 'usuario'); ?>
                        </div>
                        <div class="flex items-center space-x-3">
                            <!-- Selector de tema móvil -->
                            <label class="theme-switch">
                                <input type="checkbox" id="mobile-theme-toggle" <?php echo $currentTheme === 'dark' ? 'checked' : ''; ?>>
                                <span class="slider round">
                                    <i class="fa-solid fa-sun sun"></i>
                                    <i class="fa-solid fa-moon moon"></i>
                                </span>
                            </label>
                            <a href="logout.php"
                                class="px-3 py-1 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-md text-sm hover:bg-red-200 dark:hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800 transition-colors">
                                <i class="fa-solid fa-right-from-bracket mr-1"></i>Cerrar sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <!-- Contenido principal -->
        <main class="flex-1 bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
            <div class="py-6">
                <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4 md:mb-0 flex items-center">
                            <i class="fa-solid fa-store mr-2 text-green-600 dark:text-green-400"></i>
                            Listado de Comercios
                            <span
                                class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                Total: <?php echo $totalItems; ?>
                            </span>
                        </h1>

                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                            <!-- Buscador simplificado -->
                            <form method="GET" action="" class="flex space-x-2">
                                <div class="relative w-full sm:w-64">
                                    <input type="text" name="search" id="search"
                                        value="<?php echo htmlspecialchars($filters['search']); ?>"
                                        class="block w-full pr-10 pl-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm transition-colors"
                                        placeholder="Buscar por nombre, CIF o email..." />
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <button type="submit"
                                            class="text-gray-400 dark:text-gray-300 hover:text-gray-500 dark:hover:text-gray-200">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="flex space-x-2">
                                <!-- Botón para exportar a Excel -->
                                <button id="export-excel-btn"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 transition-colors">
                                    <i class="fa-solid fa-file-excel mr-2"></i>
                                    Exportar
                                </button>

                                <!-- Botón para imprimir -->
                                <button id="print-table-btn"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 transition-colors">
                                    <i class="fa-solid fa-print mr-2"></i>
                                    Imprimir
                                </button>

                                <!-- Botón para añadir comercio -->
                                <button id="register-company-btn"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-700 hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 transition-colors">
                                    <i class="fa-solid fa-plus mr-2"></i>
                                    Añadir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Sección de filtros (versión compacta) -->
                <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 mb-4">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-3 transition-colors duration-200">
                        <div class="flex flex-col mb-3">
                            <div class="flex items-center justify-between mb-2">
                                <h2 class="text-base font-medium text-gray-900 dark:text-white flex items-center">
                                    <i class="fa-solid fa-filter mr-2 text-gray-600 dark:text-gray-400"></i>Filtros
                                </h2>
                                <!-- Toggle para expandir/colapsar filtros en móvil -->
                                <button id="toggle-filters"
                                    class="md:hidden text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                            </div>
                            
                            <form id="filter-form" method="GET" action="panel.php">
    <label for="nombre"> Categoria: </label>
    <select name="categoria" id="filter-categoria" class="py-0.5 px-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 text-xs text-gray-900 dark:text-white transition-colors">
        <option value="">Todas</option>
        <option value="1" <?= isset($_GET['categoria']) && $_GET['categoria'] == '1' ? 'selected' : '' ?>>Educacion</option>
        <option value="2" <?= isset($_GET['categoria']) && $_GET['categoria'] == '2' ? 'selected' : '' ?>>Comercios</option>
        <option value="3" <?= isset($_GET['categoria']) && $_GET['categoria'] == '3' ? 'selected' : '' ?>>Servicios</option>
        <option value="4" <?= isset($_GET['categoria']) && $_GET['categoria'] == '4' ? 'selected' : '' ?>>Hogar</option>
        <option value="5" <?= isset($_GET['categoria']) && $_GET['categoria'] == '5' ? 'selected' : '' ?>>Salud y Bienestar</option>
        <option value="6" <?= isset($_GET['categoria']) && $_GET['categoria'] == '6' ? 'selected' : '' ?>>Restauración</option>
    </select>

    <label for="nombre"> Estado: </label>
    <select name="estado" id="filter-estado" class="py-0.5 px-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 text-xs text-gray-900 dark:text-white transition-colors">
        <option value="">Todos</option>
        <option value="activo" <?= isset($_GET['estado']) && $_GET['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
        <option value="No activo" <?= isset($_GET['estado']) && $_GET['estado'] == 'No activo' ? 'selected' : '' ?>>No activo</option>
        <option value="pendiente" <?= isset($_GET['estado']) && $_GET['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
    </select>

    <label for="nombre"> Nº por pagina: </label>
    <select name="items_per_page" id="items-per-page" class="py-0.5 px-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 text-xs text-gray-900 dark:text-white transition-colors">
        <option value="10" <?= isset($_GET['items_per_page']) && $_GET['items_per_page'] == '10' ? 'selected' : '' ?>>10</option>
        <option value="20" <?= isset($_GET['items_per_page']) && $_GET['items_per_page'] == '20' ? 'selected' : '' ?>>20</option>
        <option value="50" <?= isset($_GET['items_per_page']) && $_GET['items_per_page'] == '50' ? 'selected' : '' ?>>50</option>
        <option value="100" <?= isset($_GET['items_per_page']) && $_GET['items_per_page'] == '100' ? 'selected' : '' ?>>100</option>
    </select>

    <label for="nombre">Ordenar por: </label>
    <select name="order_by" id="order-by" class="py-0.5 px-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 text-xs text-gray-900 dark:text-white transition-colors">
        <option value="nombre" <?= isset($_GET['order_by']) && $_GET['order_by'] == 'nombre' ? 'selected' : '' ?>>Nombre</option>
        <option value="cif" <?= isset($_GET['order_by']) && $_GET['order_by'] == 'cif' ? 'selected' : '' ?>>CIF</option>
        <option value="estado" <?= isset($_GET['order_by']) && $_GET['order_by'] == 'estado' ? 'selected' : '' ?>>Estado</option>
        <option value="categoria" <?= isset($_GET['order_by']) && $_GET['order_by'] == 'categoria' ? 'selected' : '' ?>>Categoria</option>
        <option value="empleados" <?= isset($_GET['order_by']) && $_GET['order_by'] == 'empleados' ? 'selected' : '' ?>>Empleados</option>
    </select>

    <label for="nombre">Orden: </label>
    <select name="order_dir" id="order-dir" class="py-0.5 px-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 text-xs text-gray-900 dark:text-white transition-colors">
        <option value="ASC" <?= isset($_GET['order_dir']) && $_GET['order_dir'] == 'ASC' ? 'selected' : '' ?>>Ascendente</option>
        <option value="DESC" <?= isset($_GET['order_dir']) && $_GET['order_dir'] == 'DESC' ? 'selected' : '' ?>>Descendente</option>
    </select>
</form>


                        </div>

                        <div id="filter-count"
                            class="mt-2 text-xs text-gray-600 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700 pt-2">
                            <div class="flex items-center">
                                <i class="fa-solid fa-info-circle mr-2 text-blue-500 dark:text-blue-400"></i>
                                Mostrando
                                <span id="filtered-count" class="font-medium mx-1"><?php echo $totalItems; ?></span> de
                                <span class="font-medium mx-1"><?php echo $totalCount; ?></span>
                                comercios
                                <?php if (!empty($filters['search']) || !empty($filters['estado']) || $filters['categoria'] > 0): ?>
                                    <span class="ml-1 badge-pill badge-pill-primary">
                                        <i class="fa-solid fa-filter-circle-check text-xs mr-1"></i>Filtros aplicados
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Tabla de comercios con manejo optimizado de scroll -->
                <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col">
                        <div class="-my-2 sm:-mx-6 lg:-mx-8">
                            <div class="py-2 align-middle sm:px-6 lg:px-8">
                                <div
                                    class="shadow border-b border-gray-200 dark:border-gray-700 sm:rounded-lg bg-white dark:bg-gray-800 table-container transition-colors duration-200">
                                    <!-- Contenedor para scroll horizontal -->
                                    <div class="horizontal-scroll">
                                        <!-- Contenedor para scroll vertical -->
                                        <div class="table-scroll">
                                            <table id="companies-table"
                                                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 data-table"
                                                aria-label="Listado de comercios registrados">
                                                <thead class="bg-green-50 dark:bg-green-900">
                                                    <tr>
                                                        <th scope="col" class="text-left">
                                                            Nombre
                                                        </th>
                                                        <th scope="col" class="text-left">
                                                            CIF
                                                        </th>
                                                        <th scope="col" class="text-left">
                                                            Descripción
                                                        </th>
                                                        <th scope="col" class="text-left">
                                                            Dirección
                                                        </th>
                                                        <th scope="col" class="text-left">
                                                            Horario
                                                        </th>
                                                        <th scope="col" class="text-left">
                                                            Categoría
                                                        </th>
                                                        <th scope="col" class="text-left">
                                                            Email
                                                        </th>
                                                        <th scope="col" class="text-left">
                                                            Teléfono
                                                        </th>
                                                        <th scope="col" class="text-left">
                                                            Estado
                                                        </th>
                                                        <th scope="col" class="text-center">
                                                            Acciones
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody
                                                    class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                    <?php if (count($comercios) > 0): ?>
                                                        <?php foreach ($comercios as $row): ?>
                                                            <tr
                                                                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                                <td
                                                                    class="whitespace-nowrap font-medium text-gray-900 dark:text-white">
                                                                    <div class="flex items-center">
                                                                        <div
                                                                            class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center mr-3">
                                                                            <span
                                                                                class="text-green-700 dark:text-green-300 font-bold text-lg"><?php echo isset($row['nombre']) ? substr($row['nombre'], 0, 1) : '?'; ?></span>
                                                                        </div>
                                                                        <div
                                                                            class="text-sm font-medium text-gray-900 dark:text-white">
                                                                            <?php echo isset($row['nombre']) ? htmlspecialchars($row['nombre']) : 'Sin nombre'; ?>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td
                                                                    class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                                    <?php echo isset($row['cif']) ? htmlspecialchars($row['cif']) : 'N/A'; ?>
                                                                </td>
                                                                <td class="text-sm text-gray-500 dark:text-gray-400">
                                                                    <div class="truncate-cell"
                                                                        title="<?php echo isset($row['descripcion']) ? htmlspecialchars($row['descripcion']) : ''; ?>">
                                                                        <?php echo isset($row['descripcion']) ? htmlspecialchars(substr($row['descripcion'], 0, 100)) : 'Sin descripción'; ?>
                                                                        <?php echo isset($row['descripcion']) && strlen($row['descripcion']) > 100 ? '...' : ''; ?>
                                                                    </div>
                                                                </td>
                                                                <td
                                                                    class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                                    <?php echo isset($row['direccion']) ? htmlspecialchars($row['direccion']) : 'N/A'; ?>
                                                                </td>
                                                                <td
                                                                    class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                                    <?php echo isset($row['horario']) ? htmlspecialchars($row['horario']) : 'N/A'; ?>
                                                                </td>
                                                                <td
                                                                    class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                                    <?php echo isset($row['categoria']) ? htmlspecialchars($row['categoria']) : 'N/A'; ?>
                                                                </td>
                                                                <td
                                                                    class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                                    <?php echo isset($row['email']) ? htmlspecialchars($row['email']) : 'N/A'; ?>
                                                                </td>
                                                                <td
                                                                    class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                                    <?php echo isset($row['telefono_principal']) && !empty($row['telefono_principal']) ? htmlspecialchars($row['telefono_principal']) : 'N/A'; ?>
                                                                </td>
                                                                <td class="whitespace-nowrap text-sm">
                                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                        <?php
                        if (isset($row['estado'])) {
                            if ($row['estado'] === 'Activo') {
                                echo 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200';
                            } elseif ($row['estado'] === 'Pendiente') {
                                echo 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200';
                            } else {
                                echo 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200';
                            }
                        } else {
                            echo 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200';
                        }
                        ?>">
                                                                        <?php echo isset($row['estado']) ? htmlspecialchars($row['estado']) : 'Desconocido'; ?>
                                                                    </span>
                                                                </td>
                                                                <td class="whitespace-nowrap text-sm font-medium text-center">
                                                                    <div class="flex space-x-2 justify-center">
                                                                        <button class="view-btn action-btn"
                                                                            data-id="<?php echo isset($row['ID']) ? $row['ID'] : 0; ?>"
                                                                            title="Ver detalles">
                                                                            <i class="fa-solid fa-eye"></i>
                                                                        </button>
                                                                        <button class="edit-btn action-btn"
                                                                            data-id="<?php echo isset($row['ID']) ? $row['ID'] : 0; ?>"
                                                                            title="Editar">
                                                                            <i class="fa-solid fa-pen"></i>
                                                                        </button>
                                                                        <button class="delete-btn action-btn"
                                                                            data-id="<?php echo isset($row['ID']) ? $row['ID'] : 0; ?>"
                                                                            data-name="<?php echo isset($row['nombre']) ? htmlspecialchars($row['nombre']) : 'Sin nombre'; ?>"
                                                                            title="Eliminar">
                                                                            <i class="fa-solid fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="10" class="text-center py-8">
                                                                <div class="flex flex-col items-center justify-center">
                                                                    <i
                                                                        class="fa-solid fa-store text-gray-300 dark:text-gray-600 text-5xl mb-4"></i>
                                                                    <p class="text-gray-500 dark:text-gray-400 text-lg">No
                                                                        hay comercios
                                                                        registrados</p>
                                                                    <?php if (!empty($filters['search']) || !empty($filters['estado']) || $filters['categoria'] > 0): ?>
                                                                        <button onclick="window.location.href='panel.php'"
                                                                            class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 transition-colors">
                                                                            <i
                                                                                class="fa-solid fa-filter-circle-xmark mr-2"></i>Limpiar
                                                                            filtros
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <button id="add-first-company"
                                                                            class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 transition-colors">
                                                                            <i class="fa-solid fa-plus mr-2"></i>Añadir primer
                                                                            comercio
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paginación -->
                <?php if ($totalPages > 1): ?>
                    <div class="mt-4 flex justify-center">
                        <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Paginación">
                            <!-- Botón anterior -->
                            <?php if ($page > 1): ?>
                                <a href="<?php echo '?page=' . ($page - 1) . '&' . http_build_query(array_merge($filters, ['items_per_page' => $itemsPerPage, 'order_by' => $orderBy, 'order_dir' => $orderDir])); ?>"
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <span class="sr-only">Anterior</span>
                                    <i class="fa-solid fa-chevron-left"></i>
                                </a>
                            <?php else: ?>
                                <span
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-sm font-medium text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                    <span class="sr-only">Anterior</span>
                                    <i class="fa-solid fa-chevron-left"></i>
                                </span>
                            <?php endif; ?>

                            <!-- Números de página -->
                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $startPage + 4);
                            if ($endPage - $startPage < 4) {
                                $startPage = max(1, $endPage - 4);
                            }

                            for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                <?php if ($i == $page): ?>
                                    <span
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-green-50 dark:bg-green-900 text-sm font-medium text-green-600 dark:text-green-200 transition-colors">
                                        <?php echo $i; ?>
                                    </span>
                                <?php else: ?>
                                    <a href="<?php echo '?page=' . $i . '&' . http_build_query(array_merge($filters, ['items_per_page' => $itemsPerPage, 'order_by' => $orderBy, 'order_dir' => $orderDir])); ?>"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <!-- Botón siguiente -->
                            <?php if ($page < $totalPages): ?>
                                <a href="<?php echo '?page=' . ($page + 1) . '&' . http_build_query(array_merge($filters, ['items_per_page' => $itemsPerPage, 'order_by' => $orderBy, 'order_dir' => $orderDir])); ?>"
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <span class="sr-only">Siguiente</span>
                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <span
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-sm font-medium text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                    <span class="sr-only">Siguiente</span>
                                    <i class="fa-solid fa-chevron-right"></i>
                                </span>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <footer class="bg-white dark:bg-gray-800 shadow mt-auto transition-colors duration-200">
        <div class="max-w-screen-xl mx-auto px-6 py-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-center sm:text-left">
                <!-- Logo y Nombre -->
                <a href="#"
                    class="flex flex-col sm:flex-row items-center mb-4 sm:mb-0 space-y-2 sm:space-y-0 sm:space-x-3">
                    <img src="assets/img/logo.png.png" class="h-10 sm:h-8 mx-auto sm:mx-0" alt="Logo Ayuntamiento" />
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">Ayuntamiento Villanueva de la
                        Cañada</span>
                </a>

                <!-- Enlaces -->
                <ul
                    class="flex flex-col sm:flex-row flex-wrap items-center text-sm font-medium text-gray-500 dark:text-gray-400 space-y-2 sm:space-y-0 sm:space-x-6">
                    <li>
                        <a href="#" class="hover:text-green-600 dark:hover:text-green-400 transition-colors">Política de
                            Privacidad</a>
                    </li>
                    <li>
                        <a href="https://www.ayto-villacanada.es/aviso-legal" class="hover:text-green-600 dark:hover:text-green-400 transition-colors">Aviso
                            Legal</a>
                    </li>
                    <li>
                        <a href="contacto.html"class="hover:text-green-600 dark:hover:text-green-400 transition-colors">Contacto</a>
                    </li>
                </ul>
            </div>

            <hr class="my-6 border-gray-200 dark:border-gray-700 sm:mx-auto lg:my-8" />

            <span class="block text-sm text-gray-500 dark:text-gray-400 text-center">
                © <?php echo date('Y'); ?>
                <a href="https://www.ayto-villacanada.es/" class="hover:text-green-600 dark:hover:text-green-400 transition-colors">
                    Ayuntamiento Villanueva de la Cañada
                </a>. Todos los derechos reservados.
            </span>
        </div>
    </footer>

    <!-- Script JS externo -->
    <script src="assets/js/comercios.js"></script>
    <!-- Script para el modo oscuro -->
    <script>
        // Función para alternar el modo oscuro
        function toggleDarkMode() {
            const isDarkMode = document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', isDarkMode ? 'dark' : 'light');

            // Enviar al servidor para persistencia entre páginas
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('theme', isDarkMode ? 'dark' : 'light');
            fetch(currentUrl.toString(), { method: 'HEAD' });
        }

        // Inicialización de tema según localStorage o preferencia del sistema
        document.addEventListener('DOMContentLoaded', () => {
            // Cargar preferencia guardada
            const savedTheme = localStorage.getItem('darkMode');

            // Detectar preferencia del sistema si no hay nada guardado
            if (savedTheme === null) {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('darkMode', 'dark');
                }
            } else if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            }

            // Sincronizar el estado de los switchs con el tema actual
            const isDark = document.documentElement.classList.contains('dark');
            document.getElementById('theme-toggle').checked = isDark;
            if (document.getElementById('mobile-theme-toggle')) {
                document.getElementById('mobile-theme-toggle').checked = isDark;
            }

            // Agregar event listeners a los toggles
            document.getElementById('theme-toggle').addEventListener('change', toggleDarkMode);
            if (document.getElementById('mobile-theme-toggle')) {
                document.getElementById('mobile-theme-toggle').addEventListener('change', toggleDarkMode);
            }

           

            // Toggle de filtros en móvil
            const toggleFiltersBtn = document.getElementById('toggle-filters');
            const mobileFilters = document.getElementById('mobile-filters');

            if (toggleFiltersBtn && mobileFilters) {
                toggleFiltersBtn.addEventListener('click', () => {
                    mobileFilters.classList.toggle('hidden');
                    const icon = toggleFiltersBtn.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-chevron-down');
                        icon.classList.toggle('fa-chevron-up');
                    }
                });
            }

            // Contadores de caracteres en textareas
            const textareas = document.querySelectorAll('textarea[maxlength]');
            textareas.forEach(textarea => {
                const counter = document.getElementById(`${textarea.id}-count`);
                if (counter) {
                    // Inicializar contador
                    counter.textContent = textarea.value.length;

                    // Actualizar contador al escribir
                    textarea.addEventListener('input', () => {
                        counter.textContent = textarea.value.length;
                    });
                }
            });

            // Cerrar modales
            const closeButtons = document.querySelectorAll('.modal-close');
            closeButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const modalId = button.dataset.modal || button.closest('[id$="-modal"]').id;
                    document.getElementById(modalId).classList.add('hidden');
                });
            });
        });
    </script>
</body>

</html>