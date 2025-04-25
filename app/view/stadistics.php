<?php
// Incluir el verificador de sesión
require_once __DIR__ . "/../../includes/authGuard.php";
checkSession(); // Verificar si el usuario está autenticado

// Resto del contenido de tu panel
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ayto. Villanueva de la Cañada - Estadísticas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind.config.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  </head>
  <body class="bg-gray-50 min-h-screen">
    <!-- Barra de navegación superior actualizada -->
    <nav class="bg-white shadow">
      <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <div class="flex-shrink-0 flex items-center">
              <img src="assets/img/logo.png.png" alt="Logo Ayuntamiento" class="block h-10 w-auto" />
              <span class="text-lg sm:text-xl font-bold text-gray-800 ml-2">Ayuntamiento Villanueva de la Cañada</span>
            </div>
            <div class="hidden md:ml-6 md:flex md:space-x-8">
              <a href="panel.php"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                Base de datos
              </a>
              <a href="users.html"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                Usuarios
              </a>
            </div>
          </div>
          <div class="hidden md:ml-6 md:flex md:items-center space-x-4">
            <?php if(isset($_SESSION['usuario'])): ?>
            <span class="text-sm text-gray-700">Hola, <?= htmlspecialchars($_SESSION['usuario']['Nombre']) ?></span>
            <a href="../view/logout.php" class="ml-2 px-3 py-1 bg-red-100 text-red-700 rounded-md text-sm hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
              <i class="fa-solid fa-right-from-bracket mr-1"></i>Cerrar sesión
            </a>
            <?php endif; ?>
          </div>
          <div class="-mr-2 flex items-center md:hidden">
            <!-- Mobile menu button -->
            <button type="button" id="mobile-menu-button"
              class="bg-white inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-green-500"
              aria-controls="mobile-menu" aria-expanded="false">
              <span class="sr-only">Abrir menú principal</span>
              <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile menu, show/hide based on menu state. -->
      <div class="md:hidden hidden" id="mobile-menu">
        <div class="pt-2 pb-3 space-y-1">
          <a href="panel.php"
            class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
            Base de datos
          </a>
          <a href="users.html"
            class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
            Usuarios
          </a>
          <a href="stadistics.html"
            class="bg-green-50 border-green-500 text-green-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
            aria-current="page">
            Estadísticas
          </a>
        </div>
        <div class="pt-4 pb-3 border-t border-gray-200">
        <div class="hidden md:ml-6 md:flex md:items-center space-x-4">
            <?php if(isset($_SESSION['usuario'])): ?>
            <span class="text-sm text-gray-700">Hola, <?= htmlspecialchars($_SESSION['usuario']['Nombre']) ?></span>
            <a href="../../includes/authGuard.php" class="ml-2 px-3 py-1 bg-red-100 text-red-700 rounded-md text-sm hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
              <i class="fa-solid fa-right-from-bracket mr-1"></i>Cerrar sesión
            </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </nav>

    <!-- Contenido principal con estadísticas -->
    <main class="py-10 bg-gray-50">
      <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Estadísticas de Empresas</h1>
        
        <!-- Filtros para las estadísticas -->
        <div class="bg-white shadow rounded-lg p-4 mb-8">
          <h2 class="text-lg font-medium text-gray-900 mb-3">Filtros</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label for="filtro-ano" class="block text-sm font-medium text-gray-700 mb-1">Año</label>
              <select id="filtro-ano" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                <option value="todos">Todos los años</option>
                <option value="2025" selected>2025</option>
                <option value="2024">2024</option>
                <option value="2023">2023</option>
                <option value="2022">2022</option>
              </select>
            </div>
            <div>
              <label for="filtro-categoria" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
              <select id="filtro-categoria" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                <option value="todas" selected>Todas las categorías</option>
                <option value="alimentacion">Alimentación</option>
                <option value="moda">Moda</option>
                <option value="tecnologia">Tecnología</option>
                <option value="hogar">Hogar</option>
                <option value="salud">Salud y Belleza</option>
                <option value="ocio">Ocio</option>
                <option value="servicios">Servicios</option>
                <option value="construccion">Construcción</option>
                <option value="consultoria">Consultoría</option>
              </select>
            </div>
            <div>
              <label for="filtro-estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
              <select id="filtro-estado" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                <option value="todos" selected>Todos los estados</option>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
                <option value="pendiente">Pendiente</option>
              </select>
            </div>
          </div>
          <div class="mt-4 text-right">
            <button id="aplicar-filtros" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
              <i class="fa-solid fa-filter mr-2"></i>Aplicar filtros
            </button>
            <button id="exportar-pdf" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              <i class="fa-solid fa-file-pdf mr-2"></i>Exportar a PDF
            </button>
          </div>
        </div>
        
        <!-- Fila de tarjetas resumen -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-4 mb-8">
          <!-- Tarjeta Total de Empresas -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                  <i class="fa-solid fa-building text-green-600 text-xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dt class="text-sm font-medium text-gray-500 truncate">Total de Empresas</dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">19</div>
                  </dd>
                </div>
              </div>
            </div>
          </div>

          <!-- Tarjeta Empresas Activas -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                  <i class="fa-solid fa-check-circle text-blue-600 text-xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dt class="text-sm font-medium text-gray-500 truncate">Empresas Activas</dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">15</div>
                    <div class="ml-2 text-sm text-green-600">78.9%</div>
                  </dd>
                </div>
              </div>
            </div>
          </div>

          <!-- Tarjeta Empresas Pendientes -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                  <i class="fa-solid fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dt class="text-sm font-medium text-gray-500 truncate">Empresas Pendientes</dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">3</div>
                    <div class="ml-2 text-sm text-yellow-600">15.8%</div>
                  </dd>
                </div>
              </div>
            </div>
          </div>

          <!-- Tarjeta Empresas Inactivas -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                  <i class="fa-solid fa-ban text-red-600 text-xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dt class="text-sm font-medium text-gray-500 truncate">Empresas Inactivas</dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">1</div>
                    <div class="ml-2 text-sm text-red-600">5.3%</div>
                  </dd>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sección de Gráficos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <!-- Gráfico de Distribución por Categorías -->
          <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Distribución por Categorías</h2>
            <div class="h-64">
              <canvas id="categoriasChart"></canvas>
            </div>
          </div>

          <!-- Gráfico de Evolución Temporal -->
          <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Evolución de Empresas Registradas</h2>
            <div class="h-64">
              <canvas id="evolucionChart"></canvas>
            </div>
          </div>

          <!-- Gráfico de Distribución por Estado -->
          <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Distribución por Estado</h2>
            <div class="h-64">
              <canvas id="estadoChart"></canvas>
            </div>
          </div>

          <!-- Gráfico de Distribución por Tipo -->
          <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Distribución por Tipo</h2>
            <div class="h-64">
              <canvas id="tipoChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Tabla de Indicadores -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Indicadores Principales</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-green-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">% Activas</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tendencia vs 2023</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Alimentación</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">3</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">100%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 text-right">+50% ↑</td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Servicios</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">5</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">80%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 text-right">+25% ↑</td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Salud</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">3</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">66.7%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600 text-right">0% ↔</td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Tecnología</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">1</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">100%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 text-right">+100% ↑</td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Otras categorías</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">7</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">71.4%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 text-right">+40% ↑</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Distribución Geográfica -->
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Distribución por Zona Geográfica</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <span class="text-sm font-medium text-gray-700">Villanueva de la Cañada</span>
                  <span class="text-sm text-gray-500">73.7%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                  <div class="bg-green-600 h-2.5 rounded-full" style="width: 73.7%"></div>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm font-medium text-gray-700">Madrid</span>
                  <span class="text-sm text-gray-500">15.8%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                  <div class="bg-blue-600 h-2.5 rounded-full" style="width: 15.8%"></div>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm font-medium text-gray-700">Barcelona</span>
                  <span class="text-sm text-gray-500">5.3%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                  <div class="bg-purple-600 h-2.5 rounded-full" style="width: 5.3%"></div>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm font-medium text-gray-700">Valencia</span>
                  <span class="text-sm text-gray-500">5.3%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                  <div class="bg-yellow-600 h-2.5 rounded-full" style="width: 5.3%"></div>
                </div>
              </div>
            </div>
            <div class="h-64">
              <canvas id="geografiaChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </main>
    <script>
      // Funcionalidad para el menú móvil
      document.getElementById('mobile-menu-button').addEventListener('click', function() {
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenu.classList.toggle('hidden');
      });

      // Configuración global de Chart.js para añadir etiquetas
      document.addEventListener('DOMContentLoaded', () => {
        // Registrar el plugin de etiquetas
        Chart.register(ChartDataLabels);
        
        // Gráfico de Distribución por Categorías
        const categoriasChart = new Chart(document.getElementById('categoriasChart'), {
          type: 'pie',
          data: {
            labels: ['Servicios', 'Alimentación', 'Salud', 'Moda', 'Hogar', 'Tecnología', 'Ocio', 'Construcción', 'Consultoría'],
            datasets: [{
              data: [5, 3, 3, 1, 2, 1, 2, 1, 1],
              backgroundColor: [
                '#10B981', // Verde - Servicios
                '#F59E0B', // Ámbar - Alimentación
                '#8B5CF6', // Violeta - Salud
                '#EC4899', // Rosa - Moda
                '#6B7280', // Gris - Hogar
                '#3B82F6', // Azul - Tecnología
                '#F97316', // Naranja - Ocio
                '#EF4444', // Rojo - Construcción
                '#6366F1'  // Índigo - Consultoría
              ]
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              datalabels: {
                color: 'white',
                font: {
                  weight: 'bold',
                  size: 11
                },
                formatter: (value, context) => {
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = Math.round(value / total * 100);
                  return percentage > 5 ? `${percentage}%` : '';
                }
              },
              legend: {
                position: 'right',
                labels: {
                  boxWidth: 12,
                  font: {
                    size: 11
                  }
                }
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    const value = context.raw;
                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = Math.round(value / total * 100);
                    return `${context.label}: ${value} (${percentage}%)`;
                  }
                }
              }
            }
          }
        });

        // Gráfico de Evolución Temporal
        const evolucionChart = new Chart(document.getElementById('evolucionChart'), {
          type: 'line',
          data: {
            labels: ['2022', '2023', '2024', '2025'],
            datasets: [{
              label: 'Total Empresas',
              data: [7, 12, 15, 19],
              borderColor: '#10B981',
              backgroundColor: 'rgba(16, 185, 129, 0.1)',
              borderWidth: 2,
              fill: true,
              tension: 0.3
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              datalabels: {
                align: 'top',
                anchor: 'end',
                color: '#10B981',
                font: {
                  weight: 'bold'
                }
              },
              legend: {
                display: false
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  precision: 0
                }
              }
            }
          }
        });

        // Gráfico de Distribución por Estado
        const estadoChart = new Chart(document.getElementById('estadoChart'), {
          type: 'doughnut',
          data: {
            labels: ['Activo', 'Pendiente', 'Inactivo'],
            datasets: [{
              data: [15, 3, 1],
              backgroundColor: [
                '#10B981', // Verde - Activo
                '#F59E0B', // Ámbar - Pendiente
                '#EF4444'  // Rojo - Inactivo
              ]
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
              datalabels: {
                color: 'white',
                font: {
                  weight: 'bold'
                },
                formatter: (value, context) => {
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  return `${Math.round(value / total * 100)}%`;
                }
              },
              legend: {
                position: 'bottom'
              }
            }
          }
        });

        // Gráfico de Distribución por Tipo
        const tipoChart = new Chart(document.getElementById('tipoChart'), {
          type: 'bar',
          data: {
            labels: ['Servicio', 'Tienda', 'Restaurante', 'Otro'],
            datasets: [{
              label: 'Número de empresas',
              data: [10, 6, 2, 1],
              backgroundColor: [
                '#3B82F6', // Azul - Servicio
                '#8B5CF6', // Violeta - Tienda
                '#F59E0B', // Ámbar - Restaurante
                '#6B7280'  // Gris - Otro
              ],
              borderWidth: 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              datalabels: {
                color: 'white',
                font: {
                  weight: 'bold'
                },
                anchor: 'center',
                align: 'center'
              },
              legend: {
                display: false
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  precision: 0
                }
              }
            }
          }
        });

        // Gráfico de Distribución Geográfica
        const geografiaChart = new Chart(document.getElementById('geografiaChart'), {
          type: 'pie',
          data: {
            labels: ['Villanueva de la Cañada', 'Madrid', 'Barcelona', 'Valencia'],
            datasets: [{
              data: [14, 3, 1, 1],
              backgroundColor: [
                '#10B981', // Verde - Villanueva de la Cañada
                '#3B82F6', // Azul - Madrid
                '#8B5CF6', // Violeta - Barcelona
                '#F59E0B'  // Ámbar - Valencia
              ]
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              datalabels: {
                color: 'white',
                font: {
                  weight: 'bold',
                  size: 11
                },
                formatter: (value, context) => {
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = Math.round(value / total * 100);
                  return percentage > 5 ? `${percentage}%` : '';
                }
              },
              legend: {
                position: 'bottom',
                labels: {
                  boxWidth: 12,
                  font: {
                    size: 11
                  }
                }
              }
            }
          }
        });

        // Funcionalidad para actualizar los gráficos según los filtros
        document.getElementById('aplicar-filtros').addEventListener('click', function() {
          // En una aplicación real, aquí harías una llamada a la API para obtener datos filtrados
          // Para este ejemplo, simularemos algunos cambios
          
          const anoSeleccionado = document.getElementById('filtro-ano').value;
          const categoriaSeleccionada = document.getElementById('filtro-categoria').value;
          const estadoSeleccionado = document.getElementById('filtro-estado').value;
          
          // Mostrar una notificación
          alert(`Filtros aplicados: Año=${anoSeleccionado}, Categoría=${categoriaSeleccionada}, Estado=${estadoSeleccionado}`);
          
          // Aquí actualizarías los datos de los gráficos según los filtros
          // Por ejemplo:
          if (anoSeleccionado === '2024') {
            // Actualizar el gráfico de evolución (simular que tenemos menos empresas en 2024)
            evolucionChart.data.datasets[0].data = [7, 12, 15, 15]; // Reducir el último valor
            evolucionChart.update();
            
            // Actualizar las tarjetas resumen
            document.querySelectorAll('.text-2xl.font-semibold.text-gray-900')[0].textContent = '15';
            document.querySelectorAll('.text-2xl.font-semibold.text-gray-900')[1].textContent = '12';
            document.querySelectorAll('.text-sm.text-green-600')[0].textContent = '80%';
          } else if (anoSeleccionado === '2023') {
            evolucionChart.data.datasets[0].data = [7, 12, 12, 12]; // Mantener valores hasta 2023
            evolucionChart.update();
            
            document.querySelectorAll('.text-2xl.font-semibold.text-gray-900')[0].textContent = '12';
            document.querySelectorAll('.text-2xl.font-semibold.text-gray-900')[1].textContent = '9';
            document.querySelectorAll('.text-sm.text-green-600')[0].textContent = '75%';
          } else if (anoSeleccionado === '2022') {
            evolucionChart.data.datasets[0].data = [7, 7, 7, 7]; // Mantener valores hasta 2022
            evolucionChart.update();
            
            document.querySelectorAll('.text-2xl.font-semibold.text-gray-900')[0].textContent = '7';
            document.querySelectorAll('.text-2xl.font-semibold.text-gray-900')[1].textContent = '5';
            document.querySelectorAll('.text-sm.text-green-600')[0].textContent = '71.4%';
          } else {
            // Restaurar datos originales
            evolucionChart.data.datasets[0].data = [7, 12, 15, 19];
            evolucionChart.update();
            
            document.querySelectorAll('.text-2xl.font-semibold.text-gray-900')[0].textContent = '19';
            document.querySelectorAll('.text-2xl.font-semibold.text-gray-900')[1].textContent = '15';
            document.querySelectorAll('.text-sm.text-green-600')[0].textContent = '78.9%';
          }
          
          // Podríamos aplicar lógica similar para filtrar por categoría y estado
        });
        
        // Funcionalidad para exportar a PDF
        document.getElementById('exportar-pdf').addEventListener('click', function() {
          alert('En una aplicación real, esta función exportaría todos los gráficos y tablas a un archivo PDF.');
          // Aquí irían las llamadas a una biblioteca como jsPDF o similar
        });
      });
    </script>
     <footer class="bg-white shadow mt-auto">
      <div class="max-w-screen-xl mx-auto px-6 py-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-center sm:text-left">
          
          <!-- Logo y Nombre -->
          <a href="#" class="flex flex-col sm:flex-row items-center mb-4 sm:mb-0 space-y-2 sm:space-y-0 sm:space-x-3">
            <img src="../view/assets/img/logo.png.png" class="h-10 sm:h-8 mx-auto sm:mx-0" alt="Logo Ayuntamiento" />
            <span class="text-lg font-semibold text-gray-900">Ayuntamiento Villanueva de la Cañada</span>
          </a>
          
          <!-- Enlaces -->
          <ul class="flex flex-col sm:flex-row flex-wrap items-center text-sm font-medium text-gray-500 space-y-2 sm:space-y-0 sm:space-x-6">
            <li><a href="#" class="hover:text-green-600 transition-colors">Política de Privacidad</a></li>
            <li><a href="#" class="hover:text-green-600 transition-colors">Aviso Legal</a></li>
            <li><a href="#" class="hover:text-green-600 transition-colors">Contacto</a></li>
          </ul>
    
        </div>
    
        <hr class="my-6 border-gray-200 sm:mx-auto lg:my-8" />
    
        <span class="block text-sm text-gray-500 text-center">
          © 2025 
          <a href="https://www.ayto-villacanada.es/" class="hover:text-green-600 transition-colors">
            Ayuntamiento Villanueva de la Cañada
          </a>. Todos los derechos reservados.
        </span>
      </div>
    </footer>
  </body>
</html>