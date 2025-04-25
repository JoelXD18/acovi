<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 
define('BASE_PATH', dirname(dirname(__DIR__))); // Ajusta según tu estructura
 
require_once BASE_PATH . '/app/controller/userController.php';
 
session_start();
 
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ayto. Villanueva de la Cañada - Gestión de Usuarios</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind.config.js"></script>
    <!-- SheetJS para Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- FileSaver.js para descargar archivos -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
      integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
  </head>

  <body class="bg-gray-50 min-h-screen">
    <!-- Notificación de acción exitosa -->
    <div
      id="success-notification"
      aria-live="assertive"
      class="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 sm:items-start z-50 hidden"
    >
      <div class="w-full flex flex-col items-center space-y-4 sm:items-end">
        <div
          class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden"
        >
          <div class="p-4">
            <div class="flex items-start">
              <div class="flex-shrink-0">
                <svg
                  class="h-6 w-6 text-green-600"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  aria-hidden="true"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
              <div class="ml-3 w-0 flex-1 pt-0.5">
                <p
                  class="text-sm font-medium text-gray-900"
                  id="notification-message"
                >
                  Acción completada con éxito
                </p>
              </div>
              <div class="ml-4 flex-shrink-0 flex">
                <button
                  id="close-notification"
                  class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                  <span class="sr-only">Cerrar</span>
                  <svg
                    class="h-5 w-5"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    aria-hidden="true"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div
      id="delete-confirmation-modal"
      class="fixed inset-0 overflow-y-auto hidden"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div
        class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
      >
        <div
          class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          aria-hidden="true"
        ></div>
        <span
          class="hidden sm:inline-block sm:align-middle sm:h-screen"
          aria-hidden="true"
          >&#8203;</span
        >
        <div
          class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
        >
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div
                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"
              >
                <svg
                  class="h-6 w-6 text-red-600"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  aria-hidden="true"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                  />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3
                  class="text-lg leading-6 font-medium text-gray-900"
                  id="modal-title"
                >
                  Eliminar usuario
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    ¿Está seguro de que desea eliminar este usuario? Esta acción
                    no se puede deshacer.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              type="button"
              id="confirm-delete-btn"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              <i class="fa-solid fa-trash mr-2"></i>Eliminar
            </button>
            <button
              type="button"
              id="cancel-delete-btn"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para editar usuario -->
    <div
      id="edit-modal"
      class="fixed inset-0 overflow-y-auto hidden z-20"
      aria-labelledby="edit-modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div
        class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
      >
        <div
          class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          aria-hidden="true"
        ></div>
        <span
          class="hidden sm:inline-block sm:align-middle sm:h-screen"
          aria-hidden="true"
          >&#8203;</span
        >
        <div
          class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
        >
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div
                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10"
              >
                <svg
                  class="h-6 w-6 text-green-600"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  aria-hidden="true"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                  />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                <h3
                  class="text-lg leading-6 font-medium text-gray-900"
                  id="edit-modal-title"
                >
                  Editar Usuario
                </h3>
                <div class="mt-4">
                <form method="POST" action="userController.php">
                  <form id="edit-form" class="space-y-4">
                    <input
                      type="hidden"
                      name="edit-user-id"
                      id="edit-user-id"
                    />

                    <!-- Datos personales -->
                    <div class="border-b border-gray-200 pb-4 mb-4">
                      <h3 class="text-sm font-medium text-gray-700 mb-3">
                        Datos personales
                      </h3>
                      <div
                        class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-2"
                      >
                        <div>
                          <label
                            for="edit-nombre"
                            class="block text-sm font-medium text-gray-700 flex"
                          >
                            Nombre <span class="text-red-500 ml-1">*</span>
                          </label>
                          <input
                            type="text"
                            name="edit-nombre"
                            id="edit-nombre"
                            class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                            required
                          />
                        </div>
                        <div>
                          <label
                            for="edit-apellidos"
                            class="block text-sm font-medium text-gray-700 flex"
                          >
                            Apellidos <span class="text-red-500 ml-1">*</span>
                          </label>
                          <input
                            type="text"
                            name="edit-apellidos"
                            id="edit-apellidos"
                            class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                            required
                          />
                        </div>
                      </div>
                    </div>

                    <!-- Datos de acceso -->
                    <div class="border-b border-gray-200 pb-4 mb-4">
                      <h3 class="text-sm font-medium text-gray-700 mb-3">
                        Datos de acceso
                      </h3>
                      <div
                        class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-2"
                      >
                        <div>
                          <label
                            for="edit-email"
                            class="block text-sm font-medium text-gray-700 flex"
                          >
                            Email <span class="text-red-500 ml-1">*</span>
                          </label>
                          <input
                            type="email"
                            name="edit-email"
                            id="edit-email"
                            class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                            required
                          />
                        </div>
                        <div>
                          <label
                            for="edit-username"
                            class="block text-sm font-medium text-gray-700 flex"
                          >
                            Nombre de usuario
                            <span class="text-red-500 ml-1">*</span>
                          </label>
                          <input
                            type="text"
                            name="edit-username"
                            id="edit-username"
                            class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                            required
                          />
                        </div>
                        <div class="sm:col-span-2">
                          <label
                            for="edit-password"
                            class="block text-sm font-medium text-gray-700"
                          >
                            Contraseña
                          </label>
                          <input
                            type="password"
                            name="edit-password"
                            id="edit-password"
                            class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                            placeholder="Dejar en blanco para mantener la actual"
                          />
                          <p class="mt-1 text-xs text-gray-500">
                            Dejar en blanco si no desea cambiar la contraseña
                          </p>
                        </div>
                      </div>
                    </div>

                    <!-- Permisos -->
                    <div>
                      <h3 class="text-sm font-medium text-gray-700 mb-3">
                        Permisos
                      </h3>
                      <div class="mt-1 space-y-2">
                        <div class="flex items-start">
                          <div class="flex items-center h-5">
                            <input
                              id="edit-admin"
                              name="edit-admin"
                              type="checkbox"
                              class="focus:ring-green-500 h-4 w-4 text-green-600 border-gray-300 rounded"
                            />
                          </div>
                          <div class="ml-3 text-sm">
                            <label
                              for="edit-admin"
                              class="font-medium text-gray-700"
                              >Administrador</label
                            >
                            <p class="text-gray-500">
                              Acceso completo a todas las funcionalidades
                            </p>
                          </div>
                        </div>
                        <div class="flex items-start">
                          <div class="flex items-center h-5">
                            <input
                              id="edit-editor"
                              name="edit-editor"
                              type="checkbox"
                              class="focus:ring-green-500 h-4 w-4 text-green-600 border-gray-300 rounded"
                            />
                          </div>
                          <div class="ml-3 text-sm">
                            <label
                              for="edit-editor"
                              class="font-medium text-gray-700"
                              >Editor</label
                            >
                            <p class="text-gray-500">
                              Puede crear y editar contenidos
                            </p>
                          </div>
                        </div>
                        <div class="flex items-start">
                          <div class="flex items-center h-5">
                            <input
                              id="edit-viewer"
                              name="edit-viewer"
                              type="checkbox"
                              class="focus:ring-green-500 h-4 w-4 text-green-600 border-gray-300 rounded"
                            />
                          </div>
                          <div class="ml-3 text-sm">
                            <label
                              for="edit-viewer"
                              class="font-medium text-gray-700"
                              >Visor</label
                            >
                            <p class="text-gray-500">
                              Solo puede ver información, sin editar
                            </p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              type="button"
              id="save-edit-btn"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              <i class="fa-solid fa-save mr-2"></i>Guardar cambios
            </button>
            <button
              type="button"
              id="close-edit-modal-btn"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Página del dashboard -->
    <div id="dashboard-page" class="min-h-screen flex flex-col">
      <!-- Barra de navegación superior -->
      <nav class="bg-white shadow">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <div class="flex items-center">
              <div class="flex-shrink-0 flex items-center">
                <img
                  src="assets/img/logo.png.png"
                  alt="Logo Ayuntamiento"
                  class="block h-10 w-auto"
                />
                <span class="text-lg sm:text-xl font-bold text-gray-800 ml-2"
                  >Ayuntamiento Villanueva de la Cañada</span
                >
              </div>
              <div class="hidden md:ml-6 md:flex md:space-x-8">
                <a
                  href="panel.php"
                  class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                >
                  Base de datos
                </a>
                <a
                  href="users.html"
                  class="border-green-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                  aria-current="page"
                >
                  Usuarios
                </a>
                <a
                  href="stadistics.html"
                  class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                >
                  Estadísticas
                </a>
              </div>
            </div>
            <div class="hidden md:ml-6 md:flex md:items-center space-x-4">
              <?php if(isset($_SESSION['usuario'])): ?>
              <span class="text-sm text-gray-700">Hola,test <?= htmlspecialchars($_SESSION['usuario']['Nombre']) ?></span>
              <a href="../view/login.php" class="ml-2 px-3 py-1 bg-red-100 text-red-700 rounded-md text-sm hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                <i class="fa-solid fa-right-from-bracket mr-1"></i>Cerrar sesión
              </a>
              <?php endif; ?>
            </div>
             
        <!-- Mobile menu, show/hide based on menu state. -->
        <div class="md:hidden hidden" id="mobile-menu">
          <div class="pt-2 pb-3 space-y-1">
            <a
              href="panel.html"
              class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
            >
              Base de datos
            </a>
            <a
              href="users.html"
              class="bg-green-50 border-green-500 text-green-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
              aria-current="page"
            >
              Usuarios
            </a>
            <a
              href="estadisticas.php"
              class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
            >
              Estadísticas
            </a>
          </div>
          <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="flex items-center justify-between px-4">
              <div class="text-base font-medium text-gray-800">
                Hola, usuario
              </div>
              <button
                class="ml-auto px-3 py-1 bg-red-100 text-red-700 rounded-md text-sm hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              >
                <i class="fa-solid fa-right-from-bracket mr-1"></i>Cerrar sesión
              </button>
            </div>
          </div>
        </div>
      </nav>

      <!-- Contenido principal -->
      <main class="flex-1 bg-gray-50">
        <div class="py-6">
          <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div
              class="flex flex-col md:flex-row md:items-center md:justify-between mb-4"
            >
              <h1 class="text-2xl font-semibold text-gray-900 mb-4 md:mb-0">
                Gestión de Usuarios
              </h1>

              <div
                class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3"
              >
                <!-- Buscador simplificado -->
                <div class="relative w-full sm:w-64">
                  <input
                    type="text"
                    name="search"
                    id="search"
                    class="block w-full pr-10 pl-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                    placeholder="Buscar por nombre..."
                  />
                  <div
                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
                  >
                    <svg
                      class="h-5 w-5 text-gray-400"
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </div>
                </div>

                <!-- Botón para exportar a Excel -->
                <button
                  id="export-excel-btn"
                  class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                  <svg
                    class="-ml-1 mr-2 h-5 w-5"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"
                    />
                  </svg>
                  Exportar a Excel
                </button>

                <button
                  id="register-user-btn"
                  class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-700 hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                  <svg
                    class="-ml-1 mr-2 h-5 w-5"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    aria-hidden="true"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                      clip-rule="evenodd"
                    />
                  </svg>
                  Añadir Usuario
                </button>
              </div>
            </div>
          </div>

          <!-- Sección de filtros -->
          <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 mb-6">
            <div class="bg-white shadow rounded-lg p-4">
              <div
                class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0"
              >
                <h2 class="text-lg font-medium text-gray-900">Filtros</h2>
                <div
                  class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full lg:w-auto"
                >
                  <div>
                    <label
                      for="filter-rol"
                      class="block text-sm font-medium text-gray-700 mb-1"
                      >Rol</label
                    >
                    <select
                      id="filter-rol"
                      name="filter-rol"
                      class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                    >
                      <option value="todos">Todos</option>
                      <option value="administrador">Administrador</option>
                      <option value="editor">Editor</option>
                      <option value="visor">Visor</option>
                    </select>
                  </div>
                </div>
                <button
                  id="clear-filters-btn"
                  class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                  <i class="fa-solid fa-filter-circle-xmark mr-2"></i>Limpiar
                  filtros
                </button>
              </div>
              <div id="filter-count" class="mt-2 text-sm text-gray-500 hidden">
                Mostrando
                <span id="filtered-count" class="font-medium">0</span> de
                <span id="total-count" class="font-medium">0</span> usuarios
              </div>
            </div>
          </div>

          <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
           <!-- Tabla de usuarios con scroll vertical -->
        <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg bg-white">
                        <div class="overflow-x-auto">
                            <div class="max-h-[65vh] overflow-y-auto">
                                <table id="users-table" class="min-w-full divide-y divide-gray-200" aria-label="Listado de usuarios registrados">
                                    <thead class="bg-green-50 sticky top-0">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                ID
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Nombre
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Apellidos
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Email
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Usuario
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Permisos
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Último acceso
                                            </th>
                                            <th scope="col" class="relative px-6 py-3">
                                                <span class="sr-only">Acciones</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php
                                        
                                     
                                        try {
                                          $conn = new PDO("mysql:host=localhost;dbname=glitc_ayuntamiento_villanueva", "root", "");
                                          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                      
                                          $stmt = $conn->prepare("SELECT * FROM usuario");
                                          $stmt->execute();
                                      } catch (PDOException $e) {
                                          echo "Error de conexión: " . $e->getMessage();
                                      }
                              
                                        if ($stmt->rowCount() > 0) {
                                            while($row = $stmt->fetch()) {
                                                // Determinar clase de permisos para el badge
                                                $permiso_class = "";
                                                $permiso_text = htmlspecialchars($row["permisos"]);
                                                
                                                if (strtolower($permiso_text) == "administrador") {
                                                    $permiso_class = "bg-purple-100 text-purple-800";
                                                } elseif (strtolower($permiso_text) == "editor") {
                                                    $permiso_class = "bg-blue-100 text-blue-800";
                                                } elseif (strtolower($permiso_text) == "visor") {
                                                    $permiso_class = "bg-green-100 text-green-800";
                                                } else {
                                                    $permiso_class = "bg-gray-100 text-gray-800";
                                                }
                                                
                                                // Formatear fecha si existe
                                                $ultimo_acceso = isset($row["ultimo_acceso"]) ? htmlspecialchars($row["ultimo_acceso"]) : "No registrado";
                                                
                                                $user_id = isset($row["id"]) ? htmlspecialchars($row["id"]) : "";
                                                $user_name = (isset($row["nombre"]) ? htmlspecialchars($row["nombre"]) : "") . " " . (isset($row["apellidos"]) ? htmlspecialchars($row["apellidos"]) : "");
                                                
                                        ?>
                                        <tr data-user-name="<?php echo $user_name; ?>" data-user-id="<?php echo $user_id; ?>" data-user-role="<?php echo strtolower($permiso_text); ?>">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo $user_id; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($row["Nombre"]); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($row["Apellidos"]); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($row["correo"]); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($row["Nombre_Usuario"]); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $permiso_class; ?>">
                                                    <?php echo $permiso_text; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $ultimo_acceso; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button class="edit-btn text-green-600 hover:text-green-900 mr-3" 
                                                    data-user-id="<?php echo $user_id; ?>" 
                                                    data-user-name="<?php echo $user_name; ?>" 
                                                    aria-label="Editar <?php echo $user_name; ?>">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button class="delete-btn text-red-600 hover:text-red-900" 
                                                    data-user-id="<?php echo $user_id; ?>" 
                                                    data-user-name="<?php echo $user_name; ?>" 
                                                    aria-label="Eliminar <?php echo $user_name; ?>">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php
                                            }
                                        } else {
                                            echo '<tr><td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">No se encontraron usuarios</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
            <!-- Mensaje para cuando no hay resultados -->
            <div id="no-results" class="mt-6 text-center hidden">
              <div class="bg-white p-8 rounded-lg shadow">
                <svg
                  class="mx-auto h-12 w-12 text-gray-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  aria-hidden="true"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">
                  No se encontraron resultados
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                  No hay usuarios que coincidan con los criterios de búsqueda.
                </p>
                <div class="mt-6">
                  <button
                    id="clear-search-btn"
                    type="button"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                  >
                    <svg
                      class="-ml-1 mr-2 h-5 w-5"
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                      />
                    </svg>
                    Limpiar filtros
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- Modal para registrar usuario -->
    <div
      id="register-modal"
      class="fixed inset-0 overflow-y-auto hidden"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div
        class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
      >
        <div
          class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          aria-hidden="true"
        ></div>
        <span
          class="hidden sm:inline-block sm:align-middle sm:h-screen"
          aria-hidden="true"
          >&#8203;</span
        >
        <div
          class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
        >
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div
                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10"
              >
                <svg
                  class="h-6 w-6 text-green-600"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  aria-hidden="true"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                  />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                <h3
                  class="text-lg leading-6 font-medium text-gray-900"
                  id="modal-title"
                >
                  Añadir Nuevo Usuario
                </h3>
              <!-- Mostrar mensajes de error o éxito si existen -->
<?php if (isset($_SESSION['mensaje'])): ?>
    <div class="<?= $_SESSION['tipo_mensaje'] == 'error' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?> p-3 mb-4 rounded">
        <?= htmlspecialchars($_SESSION['mensaje']) ?>
        <?php 
        // Limpiar las variables de sesión después de mostrarlas
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
        ?>
    </div>
<?php endif; ?>

<div class="mt-4">
  <form id="user-form" class="space-y-4" method="post" action="">
    <!-- Datos personales -->
    <div class="border-b border-gray-200 pb-4 mb-4">
      <h3 class="text-sm font-medium text-gray-700 mb-3">
        Datos personales
      </h3>
      <div class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-2">
        <div>
          <label for="nombre" class="block text-sm font-medium text-gray-700 flex">
            Nombre <span class="text-red-500 ml-1">*</span>
          </label>
          <input
            type="text"
            name="nombre"
            id="nombre"
            class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            required
          />
        </div>
        <div>
          <label for="apellidos" class="block text-sm font-medium text-gray-700 flex">
            Apellidos <span class="text-red-500 ml-1">*</span>
          </label>
          <input
            type="text"
            name="apellidos"
            id="apellidos"
            class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            required
          />
        </div>
      </div>
    </div>

    <!-- Datos de acceso -->
    <div class="border-b border-gray-200 pb-4 mb-4">
      <h3 class="text-sm font-medium text-gray-700 mb-3">
        Datos de acceso
      </h3>
      <div class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-2">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 flex">
            Email <span class="text-red-500 ml-1">*</span>
          </label>
          <input
            type="email"
            name="email"
            id="email"
            class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            required
          />
        </div>
        <div>
          <label for="username" class="block text-sm font-medium text-gray-700 flex">
            Nombre de usuario
            <span class="text-red-500 ml-1">*</span>
          </label>
          <input
            type="text"
            name="username"
            id="username"
            class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            required
          />
        </div>
        <div class="sm:col-span-2">
          <label for="password" class="block text-sm font-medium text-gray-700 flex">
            Contraseña <span class="text-red-500 ml-1">*</span>
          </label>
          <input
            type="password"
            name="password"
            id="password"
            class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            required
          />
        </div>
      </div>
    </div>

    <!-- Permisos -->
    <div>
      <h3 class="text-sm font-medium text-gray-700 mb-3">
        Permisos <span class="text-red-500 ml-1">*</span>
      </h3>
      <div class="mt-1 space-y-2">
        <div class="flex items-start">
          <div class="flex items-center h-5">
            <input
              id="admin"
              name="rol"
              type="radio"
              value="administrador"
              class="focus:ring-green-500 h-4 w-4 text-green-600 border-gray-300"
              required
            />
          </div>
          <div class="ml-3 text-sm">
            <label for="admin" class="font-medium text-gray-700">Administrador</label>
            <p class="text-gray-500">
              Acceso completo a todas las funcionalidades
            </p>
          </div>
        </div>
        <div class="flex items-start">
          <div class="flex items-center h-5">
            <input
              id="editor"
              name="rol"
              type="radio"
              value="editor"
              class="focus:ring-green-500 h-4 w-4 text-green-600 border-gray-300"
              required
            />
          </div>
          <div class="ml-3 text-sm">
            <label for="editor" class="font-medium text-gray-700">Editor</label>
            <p class="text-gray-500">
              Puede crear y editar contenidos
            </p>
          </div>
        </div>
        <div class="flex items-start">
          <div class="flex items-center h-5">
            <input
              id="viewer"
              name="rol"
              type="radio"
              value="visor"
              class="focus:ring-green-500 h-4 w-4 text-green-600 border-gray-300"
              required
            />
          </div>
          <div class="ml-3 text-sm">
            <label for="viewer" class="font-medium text-gray-700">Visor</label>
            <p class="text-gray-500">
              Solo puede ver información, sin editar
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="text-xs text-gray-500 mt-2">
      <span class="text-red-500">*</span> Campos obligatorios
    </div>

    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
      <button
        type="submit"
        name="save_user"
        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
      >
        <i class="fa-solid fa-save mr-2"></i>Guardar
      </button>
      <button
        type="button"
        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
        onclick="window.location.href='users.php'"
      >
        Cancelar
      </button>
    </div>
  </form>
</div>
    <script>
      // Funcionalidad para el menú móvil
      document
        .getElementById("mobile-menu-button")
        .addEventListener("click", function () {
          const mobileMenu = document.getElementById("mobile-menu");
          mobileMenu.classList.toggle("hidden");
        });

      // Funcionalidad para abrir y cerrar el modal de registro
      document
        .getElementById("register-user-btn")
        .addEventListener("click", function () {
          document.getElementById("register-modal").classList.remove("hidden");
        });

      document
        .getElementById("close-modal-btn")
        .addEventListener("click", function () {
          document.getElementById("register-modal").classList.add("hidden");
        });

      // Funcionalidad para abrir y cerrar el modal de edición
      const editButtons = document.querySelectorAll(".edit-btn");
      editButtons.forEach((button) => {
        button.addEventListener("click", function () {
          const userId = this.getAttribute("data-user-id");
          const userName = this.getAttribute("data-user-name");
          document.getElementById(
            "edit-modal-title"
          ).textContent = `Editar ${userName}`;
          document.getElementById("edit-user-id").value = userId;

          // Aquí se cargarían los datos del usuario desde el servidor
          // Para este ejemplo, usamos datos ficticios
          const userRow = document.querySelector(
            `tr[data-user-id="${userId}"]`
          );
          if (userRow) {
            // Cargar valores del formulario basados en la fila de la tabla
            document.getElementById("edit-nombre").value = userRow
              .querySelector("td:nth-child(2)")
              .textContent.trim();
            document.getElementById("edit-apellidos").value = userRow
              .querySelector("td:nth-child(3)")
              .textContent.trim();
            document.getElementById("edit-email").value = userRow
              .querySelector("td:nth-child(4)")
              .textContent.trim();
            document.getElementById("edit-username").value = userRow
              .querySelector("td:nth-child(5)")
              .textContent.trim();

            // Vaciar la contraseña para seguridad
            document.getElementById("edit-password").value = "";

            // Establecer permisos
            const userRole = userRow.getAttribute("data-user-role");
            document.getElementById("edit-admin").checked =
              userRole === "administrador";
            document.getElementById("edit-editor").checked =
              userRole === "editor";
            document.getElementById("edit-viewer").checked =
              userRole === "visor";
          }

          document.getElementById("edit-modal").classList.remove("hidden");
        });
      });

      document
        .getElementById("close-edit-modal-btn")
        .addEventListener("click", function () {
          document.getElementById("edit-modal").classList.add("hidden");
        });

      // Funcionalidad para guardar cambios de edición
      document
        .getElementById("save-edit-btn")
        .addEventListener("click", function () {
          // Aquí se enviarían los datos al servidor para actualizar el usuario
          // Para este ejemplo, simplemente cerramos el modal y mostramos notificación
          document.getElementById("edit-modal").classList.add("hidden");
          showNotification("Usuario actualizado correctamente");
        });

      // Funcionalidad para abrir y cerrar el modal de confirmación de eliminación
      const deleteButtons = document.querySelectorAll(".delete-btn");
      let userToDelete = null;

      deleteButtons.forEach((button) => {
        button.addEventListener("click", function () {
          const userId = this.getAttribute("data-user-id");
          const userName = this.getAttribute("data-user-name");
          document.getElementById(
            "modal-title"
          ).textContent = `Eliminar ${userName}`;
          userToDelete = userId;
          document
            .getElementById("delete-confirmation-modal")
            .classList.remove("hidden");
        });
      });

      document
        .getElementById("cancel-delete-btn")
        .addEventListener("click", function () {
          document
            .getElementById("delete-confirmation-modal")
            .classList.add("hidden");
        });

      // Funcionalidad para confirmar eliminación
      document
        .getElementById("confirm-delete-btn")
        .addEventListener("click", function () {
          if (userToDelete) {
            // Aquí se enviaría la solicitud al servidor para eliminar el usuario
            // Para este ejemplo, simplemente ocultamos la fila de la tabla
            const userRow = document.querySelector(
              `tr[data-user-id="${userToDelete}"]`
            );
            if (userRow) {
              userRow.style.display = "none";
            }
          }
          document
            .getElementById("delete-confirmation-modal")
            .classList.add("hidden");
          showNotification("Usuario eliminado correctamente");
          updateFilterCounter();
        });

      // Funcionalidad para guardar usuario
      document
        .getElementById("save-user-btn")
        .addEventListener("click", function () {
          // Aquí se enviarían los datos al servidor para guardar el nuevo usuario
          // Para este ejemplo, simplemente cerramos el modal y mostramos notificación
          document.getElementById("register-modal").classList.add("hidden");
          showNotification("Usuario registrado correctamente");
        });

      // Función para mostrar notificaciones
      function showNotification(message) {
        const notification = document.getElementById("success-notification");
        document.getElementById("notification-message").textContent = message;
        notification.classList.remove("hidden");

        setTimeout(function () {
          notification.classList.add("hidden");
        }, 3000);
      }

      document
        .getElementById("close-notification")
        .addEventListener("click", function () {
          document
            .getElementById("success-notification")
            .classList.add("hidden");
        });

      // Funcionalidad para filtrar la tabla mediante búsqueda por nombre
      document.getElementById("search").addEventListener("keyup", function () {
        filterTable();
      });

      // Funcionalidad para los filtros
      document
        .getElementById("filter-rol")
        .addEventListener("change", function () {
          filterTable();
        });

      // Botón para limpiar filtros
      document
        .getElementById("clear-filters-btn")
        .addEventListener("click", function () {
          document.getElementById("search").value = "";
          document.getElementById("filter-rol").value = "todos";
          filterTable();
        });

      // Botón adicional para limpiar búsqueda cuando no hay resultados
      document
        .getElementById("clear-search-btn")
        .addEventListener("click", function () {
          document.getElementById("search").value = "";
          document.getElementById("filter-rol").value = "todos";
          filterTable();
        });

      // Función para filtrar la tabla según todos los criterios
      function filterTable() {
        const searchTerm = document
          .getElementById("search")
          .value.toLowerCase();
        const rolFilter = document.getElementById("filter-rol").value;

        const tableRows = document.querySelectorAll("#users-table tbody tr");
        let visibleCount = 0;
        const totalCount = tableRows.length;

        tableRows.forEach((row) => {
          const userName = row.getAttribute("data-user-name").toLowerCase();
          const userRole = row.getAttribute("data-user-role");

          // Verificar si cumple con todos los filtros
          const matchesSearch = userName.includes(searchTerm);
          const matchesRole = rolFilter === "todos" || userRole === rolFilter;

          if (matchesSearch && matchesRole) {
            row.classList.remove("hidden");
            visibleCount++;
          } else {
            row.classList.add("hidden");
          }
        });

        // Mostrar mensaje cuando no hay resultados
        const noResultsElement = document.getElementById("no-results");
        if (visibleCount === 0) {
          noResultsElement.classList.remove("hidden");
        } else {
          noResultsElement.classList.add("hidden");
        }

        // Actualizar contador de resultados
        document.getElementById("filtered-count").textContent = visibleCount;
        document.getElementById("total-count").textContent = totalCount;
        document
          .getElementById("filter-count")
          .classList.toggle("hidden", visibleCount === totalCount);
      }

      // Inicializar contador de filtros
      function updateFilterCounter() {
        const tableRows = document.querySelectorAll("#users-table tbody tr");
        const visibleRows = document.querySelectorAll(
          "#users-table tbody tr:not(.hidden)"
        );

        document.getElementById("filtered-count").textContent =
          visibleRows.length;
        document.getElementById("total-count").textContent = tableRows.length;
        document
          .getElementById("filter-count")
          .classList.toggle("hidden", visibleRows.length === tableRows.length);
      }

      // Funcionalidad para exportar a Excel
      document
        .getElementById("export-excel-btn")
        .addEventListener("click", function () {
          // Crear una nueva hoja de cálculo
          const workbook = XLSX.utils.book_new();

          // Obtener datos de la tabla (solo filas visibles)
          const table = document.getElementById("users-table");
          const tableData = [];

          // Obtener los encabezados
          const headers = [];
          const headerCells = table.querySelectorAll("thead th");
          headerCells.forEach((cell) => {
            if (cell.textContent.trim() !== "")
              headers.push(cell.textContent.trim());
          });

          // Quitamos la última columna (acciones)
          headers.pop();
          tableData.push(headers);

          // Obtener datos de filas visibles
          const rows = table.querySelectorAll("tbody tr:not(.hidden)");
          rows.forEach((row) => {
            const rowData = [];
            const cells = row.querySelectorAll("td");

            // Obtener todos los datos de las celdas excepto la última (acciones)
            for (let i = 0; i < cells.length - 1; i++) {
              // Para la celda de permisos, obtenemos el texto del span
              if (i === 5) {
                const permiso = cells[i]
                  .querySelector("span")
                  .textContent.trim();
                rowData.push(permiso);
              } else {
                rowData.push(cells[i].textContent.trim());
              }
            }

            tableData.push(rowData);
          });

          // Crear la hoja y añadirla al libro
          const worksheet = XLSX.utils.aoa_to_sheet(tableData);
          XLSX.utils.book_append_sheet(workbook, worksheet, "Usuarios");

          // Generar el archivo y descargarlo
          const excelBuffer = XLSX.write(workbook, {
            bookType: "xlsx",
            type: "array",
          });
          const blob = new Blob([excelBuffer], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
          });
          saveAs(blob, "listado_usuarios.xlsx");

          showNotification("Usuarios exportados correctamente");
        });

      // Inicializar la tabla al cargar la página
      document.addEventListener("DOMContentLoaded", function () {
        updateFilterCounter();
      });

      document.addEventListener('DOMContentLoaded', function() {
            // Botones de edición
            const editButtons = document.querySelectorAll('.edit-btn');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.getAttribute('data-user-id');
                    const userName = this.getAttribute('data-user-name');
                    alert(`Editando usuario: ${userName} (ID: ${userId})`);
                    // Aquí puedes redirigir a la página de edición o mostrar un modal
                });
            });
            
            // Botones de eliminación
            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.getAttribute('data-user-id');
                    const userName = this.getAttribute('data-user-name');
                    if (confirm(`¿Estás seguro de que deseas eliminar a ${userName}?`)) {
                        // Aquí puedes implementar la lógica de eliminación
                        alert(`Usuario ${userName} eliminado correctamente`);
                    }
                });
            });
        });
    </script>
  </body>
</html>
