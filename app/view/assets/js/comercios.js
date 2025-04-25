document.addEventListener("DOMContentLoaded", function () {
  console.log("Iniciando script comercios.js");

  // Variables globales
  let currentDeleteId = null;
  let currentEditId = null;

  // Función para mostrar notificaciones
  const showNotification = (message, type = "success") => {
    const notificationEl = document.getElementById(
      type === "success" ? "success-notification" : "error-notification"
    );
    const messageEl = document.getElementById(
      type === "success" ? "notification-message" : "error-message"
    );

    if (notificationEl && messageEl) {
      messageEl.textContent = message;
      notificationEl.classList.remove("hidden");

      setTimeout(() => {
        notificationEl.classList.add("hidden");
      }, 3000);
    } else {
      console.log(`${type}: ${message}`);
    }
  };

  // Gestor de modales
  const modalManager = {
    open: function (modalId) {
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.remove("hidden");
        document.body.classList.add("overflow-hidden");
      } else {
        console.error(`Modal con ID ${modalId} no encontrado`);
      }
    },

    close: function (modalId) {
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.add("hidden");
        document.body.classList.remove("overflow-hidden");
      } else {
        console.error(`Modal con ID ${modalId} no encontrado`);
      }
    },

    closeAll: function () {
      const modals = document.querySelectorAll('[id$="-modal"]');
      modals.forEach((modal) => {
        modal.classList.add("hidden");
      });
      document.body.classList.remove("overflow-hidden");
    },
  };

  // Configurar cierre de notificaciones
  const setupNotificationClosing = () => {
    const closeNotificationBtn = document.getElementById("close-notification");
    if (closeNotificationBtn) {
      closeNotificationBtn.addEventListener("click", () => {
        const notification = document.getElementById("success-notification");
        if (notification) {
          notification.classList.add("hidden");
        }
      });
    }

    const closeErrorBtn = document.getElementById("close-error-notification");
    if (closeErrorBtn) {
      closeErrorBtn.addEventListener("click", () => {
        const errorNotification = document.getElementById("error-notification");
        if (errorNotification) {
          errorNotification.classList.add("hidden");
        }
      });
    }
  };

  // Configurar menú móvil - CORREGIDO
  const setupMobileMenu = () => {
    const mobileMenuBtn = document.getElementById("mobile-menu-button");
    const mobileMenu = document.getElementById("mobile-menu");

    if (mobileMenuBtn && mobileMenu) {
      console.log("Configurando evento de menú móvil");

      // Usar función directa en lugar de arrow function
      mobileMenuBtn.addEventListener("click", function () {
        console.log("Botón de menú móvil clickeado");
        mobileMenu.classList.toggle("hidden");
      });
    } else {
      console.error("No se encontraron los elementos del menú móvil:", {
        mobileMenuBtn: !!mobileMenuBtn,
        mobileMenu: !!mobileMenu,
      });
    }
  };

  // Configurar contadores de caracteres para textareas
  const setupCharCounters = () => {
    // Para el modal de registro
    const registerDescripcion = document.getElementById("register-descripcion");
    const registerDescripcionCount = document.getElementById(
      "register-descripcion-count"
    );

    if (registerDescripcion && registerDescripcionCount) {
      registerDescripcion.addEventListener("input", function () {
        const count = this.value.length;
        registerDescripcionCount.textContent = count;

        // Cambiar color si se acerca al límite
        if (count > 450) {
          registerDescripcionCount.classList.add("text-yellow-500");
        } else {
          registerDescripcionCount.classList.remove("text-yellow-500");
        }

        if (count >= 500) {
          registerDescripcionCount.classList.add("text-red-500");
        } else {
          registerDescripcionCount.classList.remove("text-red-500");
        }
      });
    }

    // Para el modal de edición
    const editDescripcion = document.getElementById("edit-descripcion");
    const editDescripcionCount = document.getElementById(
      "edit-descripcion-count"
    );

    if (editDescripcion && editDescripcionCount) {
      editDescripcion.addEventListener("input", function () {
        const count = this.value.length;
        editDescripcionCount.textContent = count;

        // Cambiar color si se acerca al límite
        if (count > 450) {
          editDescripcionCount.classList.add("text-yellow-500");
        } else {
          editDescripcionCount.classList.remove("text-yellow-500");
        }

        if (count >= 500) {
          editDescripcionCount.classList.add("text-red-500");
        } else {
          editDescripcionCount.classList.remove("text-red-500");
        }
      });
    }
  };

  // Configurar cierre de modales
  const setupModalClosing = () => {
    // Botones para cerrar modales
    document
      .querySelectorAll(
        ".modal-close, #cancel-delete-btn, #close-edit-modal-btn, #close-register-modal-btn"
      )
      .forEach((btn) => {
        if (btn) {
          btn.addEventListener("click", () => modalManager.closeAll());
        }
      });

    // Cerrar modal al hacer clic en el fondo
    document.querySelectorAll(".modal-backdrop").forEach((backdrop) => {
      if (backdrop) {
        backdrop.addEventListener("click", function (e) {
          if (e.target === this) {
            modalManager.closeAll();
          }
        });
      }
    });

    // Cerrar modal con tecla Escape
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") {
        modalManager.closeAll();
      }
    });
  };

  // Validar formulario
  const validateForm = (formId) => {
    const form = document.getElementById(formId);
    if (!form) return false;

    let isValid = true;
    const requiredFields = form.querySelectorAll("[required]");

    // Limpiar errores previos
    form.querySelectorAll(".text-red-600").forEach((errorEl) => {
      errorEl.classList.add("hidden");
      errorEl.textContent = "";
    });

    // Validar campos requeridos
    requiredFields.forEach((field) => {
      const errorEl = document.getElementById(`${field.id}-error`);
      if (!field.value.trim()) {
        isValid = false;
        if (errorEl) {
          errorEl.textContent = "Este campo es obligatorio";
          errorEl.classList.remove("hidden");
        }
        field.classList.add("border-red-500");
      } else {
        field.classList.remove("border-red-500");
      }
    });

    // Validar formato de email si existe
    const emailField = form.querySelector('[type="email"]');
    if (emailField && emailField.value.trim()) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      const errorEl = document.getElementById(`${emailField.id}-error`);

      if (!emailRegex.test(emailField.value.trim())) {
        isValid = false;
        if (errorEl) {
          errorEl.textContent = "Formato de email inválido";
          errorEl.classList.remove("hidden");
        }
        emailField.classList.add("border-red-500");
      }
    }

    // Validar formato de teléfono si existe
    const telField = form.querySelector('[type="tel"]');
    if (telField && telField.value.trim()) {
      const telRegex = /^[0-9]{9}$/;
      const errorEl = document.getElementById(`${telField.id}-error`);

      if (!telRegex.test(telField.value.trim())) {
        isValid = false;
        if (errorEl) {
          errorEl.textContent = "El teléfono debe tener 9 dígitos";
          errorEl.classList.remove("hidden");
        }
        telField.classList.add("border-red-500");
      }
    }

    return isValid;
  };

  // Función para cargar detalles de un comercio
  const loadCompanyDetails = (id) => {
    const detailContent = document.getElementById("detail-content");
    if (!detailContent) {
      showNotification("Error: Elemento de detalle no encontrado", "error");
      return;
    }

    // Mostrar loading
    detailContent.innerHTML = `
    <div class="animate-pulse">
      <div class="h-6 bg-gray-200 rounded w-1/4 mb-4"></div>
      <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
      <div class="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
      <div class="h-6 bg-gray-200 rounded w-1/4 mb-4"></div>
      <div class="h-4 bg-gray-200 rounded w-full mb-2"></div>
      <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
    </div>
  `;

    // Añadir log para depuración
    console.log(`Cargando detalles para el comercio ID: ${id}`);

    fetch(`/acovi/api/api-get-company.php?id=${id}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        // Añadir log para depuración
        console.log("Datos recibidos:", data);

        if (data.success) {
          // Guardar ID en el botón de editar
          const editBtn = document.getElementById("edit-from-detail-btn");
          if (editBtn) {
            editBtn.setAttribute("data-id", id);
          }

          updateDetailContent(data.data);
        } else {
          throw new Error(data.message || "Error al cargar los detalles");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showNotification(`Error de conexión: ${error.message}`, "error");

        // Actualizar contenido para mostrar el error
        if (detailContent) {
          detailContent.innerHTML = `
          <div class="p-4 text-center">
            <i class="fa-solid fa-circle-exclamation text-red-500 text-4xl mb-4"></i>
            <p class="text-red-500">Error al cargar los detalles: ${error.message}</p>
          </div>
        `;
        }
      });
  };

  // Función para actualizar contenido de detalles
  const updateDetailContent = (company) => {
    const detailContent = document.getElementById("detail-content");
    if (!detailContent) return;

    // Añadir log para depuración
    console.log("Actualizando contenido con datos:", company);

    // Generar HTML de detalles completo
    const detailHTML = `
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <h4 class="text-sm font-bold text-gray-700 mb-2 pb-1 border-b">Información General</h4>
        <div class="mt-2 space-y-3">
          <p class="text-sm"><span class="font-medium text-gray-700">Nombre:</span> ${
            company.nombre || "No disponible"
          }</p>
          <p class="text-sm"><span class="font-medium text-gray-700">CIF:</span> ${
            company.cif || "No disponible"
          }</p>
          <p class="text-sm"><span class="font-medium text-gray-700">Estado:</span> 
            <span class="px-2 py-0.5 rounded-full text-xs ${
              company.estado === "Activo"
                ? "bg-green-100 text-green-800"
                : company.estado === "Pendiente"
                ? "bg-yellow-100 text-yellow-800"
                : "bg-red-100 text-red-800"
            }">
              ${company.estado || "Desconocido"}
            </span>
          </p>
          <p class="text-sm"><span class="font-medium text-gray-700">Forma Jurídica:</span> ${
            company.forma_juridica || "No especificada"
          }</p>
          <p class="text-sm"><span class="font-medium text-gray-700">Empleados:</span> ${
            company.empleados || "No disponible"
          }</p>
        </div>
        
        <h4 class="text-sm font-bold text-gray-700 mt-5 mb-2 pb-1 border-b">Ubicación y Horario</h4>
        <div class="mt-2 space-y-3">
          <p class="text-sm"><span class="font-medium text-gray-700">Dirección:</span> ${
            company.direccion || "No disponible"
          }</p>
          <p class="text-sm"><span class="font-medium text-gray-700">Horario:</span> ${
            company.horario || "No disponible"
          }</p>
        </div>
      </div>
      
      <div>
        <h4 class="text-sm font-bold text-gray-700 mb-2 pb-1 border-b">Contacto</h4>
        <div class="mt-2 space-y-3">
          <p class="text-sm"><span class="font-medium text-gray-700">Email:</span> ${
            company.email || "No disponible"
          }</p>
          <p class="text-sm"><span class="font-medium text-gray-700">Teléfono:</span> ${
            company.telefono_principal || "No disponible"
          }</p>
          <p class="text-sm"><span class="font-medium text-gray-700">Web:</span> ${
            company.web
              ? `<a href="${company.web}" target="_blank" class="text-blue-600 hover:underline">${company.web}</a>`
              : "No disponible"
          }</p>
        </div>
        
        <h4 class="text-sm font-bold text-gray-700 mt-5 mb-2 pb-1 border-b">Categoría</h4>
        <div class="mt-2">
          <p class="text-sm"><span class="font-medium text-gray-700">Categoría Principal:</span> ${
            company.categoria || "No categorizado"
          }</p>
        </div>
        
        <h4 class="text-sm font-bold text-gray-700 mt-5 mb-2 pb-1 border-b">Descripción</h4>
        <div class="mt-2 bg-gray-50 p-3 rounded-md">
          <p class="text-sm text-gray-700 whitespace-pre-line">${
            company.descripcion || "Sin descripción"
          }</p>
        </div>
      </div>
    </div>
  `;

    detailContent.innerHTML = detailHTML;
  };

  // Función para cargar datos de comercio en el formulario de edición
  const loadCompanyDataForEdit = (id) => {
    // Guardar el ID para usar luego
    currentEditId = id;

    // Establecer el ID en el campo oculto
    const editCompanyIdField = document.getElementById("edit-company-id");
    if (editCompanyIdField) {
      editCompanyIdField.value = id;
    }

    // Mostrar el modal mientras se carga
    modalManager.open("edit-modal");

    // Añadir log para depuración
    console.log(`Cargando datos para editar comercio ID: ${id}`);

    fetch(`/acovi/api/api-get-company.php?id=${id}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        // Añadir log para depuración
        console.log("Datos para edición recibidos:", data);

        if (data.success) {
          fillEditForm(data.data);
        } else {
          throw new Error(data.message || "Error al cargar los datos");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showNotification(`Error al cargar datos: ${error.message}`, "error");
        modalManager.close("edit-modal");
      });
  };

  // Función para llenar el formulario de edición con los datos
  const fillEditForm = (company) => {
    // Obtener referencias a todos los campos
    const nombreField = document.getElementById("edit-nombre");
    const cifField = document.getElementById("edit-cif");
    const descripcionField = document.getElementById("edit-descripcion");
    const estadoField = document.getElementById("edit-estado");
    const emailField = document.getElementById("edit-email");
    const telefonoField = document.getElementById("edit-telefono");
    const webField = document.getElementById("edit-web");
    const empleadosField = document.getElementById("edit-empleados");
    const formaJuridicaField = document.getElementById("edit-forma-juridica");
    const categoriaField = document.getElementById("edit-categoria");
    const horarioField = document.getElementById("edit-horario");
    const direccionField = document.getElementById("edit-direccion");
    const descripcionCount = document.getElementById("edit-descripcion-count");

    // Añadir log para depuración
    console.log("Llenando formulario con datos:", company);

    // Rellenar cada campo con los datos
    if (nombreField) nombreField.value = company.nombre || "";
    if (cifField) cifField.value = company.cif || "";
    if (descripcionField) {
      descripcionField.value = company.descripcion || "";
      // Actualizar contador de caracteres
      if (descripcionCount) {
        const count = (company.descripcion || "").length;
        descripcionCount.textContent = count;
      }
    }
    if (estadoField) estadoField.value = company.estado || "Activo";
    if (emailField) emailField.value = company.email || "";
    if (telefonoField) telefonoField.value = company.telefono_principal || "";
    if (webField) webField.value = company.web || "";
    if (empleadosField) empleadosField.value = company.empleados || "";
    if (formaJuridicaField)
      formaJuridicaField.value = company.forma_juridica || "";
    if (categoriaField) categoriaField.value = company.categoria_id || "";
    if (horarioField) horarioField.value = company.horario || "";
    if (direccionField) direccionField.value = company.direccion || "";
  };

  // Función para enviar formulario de edición
  const submitEditForm = () => {
    if (!validateForm("edit-form")) {
      showNotification(
        "Por favor, complete todos los campos obligatorios correctamente",
        "error"
      );
      return;
    }

    const editForm = document.getElementById("edit-form");
    if (!editForm) return;

    const formData = new FormData(editForm);

    // ⬇️ AÑADIR ESTO:
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
if (csrfToken) {
  formData.append("csrf_token", csrfToken);
}

    // Asegurar que se envía el ID correcto
    formData.append("id", currentEditId);

    // Añadir log para depuración
    console.log("Enviando formulario de edición para ID:", currentEditId);
    console.log("Valores del formulario:");
    for (let [key, value] of formData.entries()) {
      console.log(`${key}: ${value}`);
    }

    // Mostrar feedback de "Guardando..."
    const saveBtn = document.getElementById("save-edit-btn");
    const originalBtnText = saveBtn.innerHTML;
    saveBtn.innerHTML =
      '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i>Guardando...';
    saveBtn.disabled = true;

    fetch("/acovi/api/api-update-company.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        // Verificar la respuesta HTTP
        if (!response.ok) {
          throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        // Añadir log para depuración
        console.log("Respuesta de actualización:", data);

        if (data.success) {
          showNotification("Comercio actualizado con éxito");
          modalManager.closeAll();
          setTimeout(() => {
            // Recargar página para ver cambios
            window.location.reload();
          }, 1500);
        } else {
          // Si hay errores específicos, mostrarlos
          if (data.errors) {
            let errorMsg = "Errores de validación:";
            for (const [field, message] of Object.entries(data.errors)) {
              errorMsg += `\n- ${message}`;
            }
            throw new Error(errorMsg);
          } else {
            throw new Error(data.message || "Error al actualizar el comercio");
          }
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showNotification(error.message, "error");
        // Restaurar botón
        saveBtn.innerHTML = originalBtnText;
        saveBtn.disabled = false;
      });
  };

  // Función para enviar formulario de registro
  const submitRegisterForm = () => {
    if (!validateForm("register-form")) {
      showNotification(
        "Por favor, complete todos los campos obligatorios correctamente",
        "error"
      );
      return;
    }

    const registerForm = document.getElementById("register-form");
    if (!registerForm) return;

    const formData = new FormData(registerForm);

    // ⬇️ AÑADIR ESTO:
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
if (csrfToken) {
  formData.append("csrf_token", csrfToken);
}

    // Añadir log para depuración
    console.log("Enviando formulario de registro");
    console.log("Valores del formulario:");
    for (let [key, value] of formData.entries()) {
      console.log(`${key}: ${value}`);
    }

    // Mostrar feedback de "Guardando..."
    const saveBtn = document.getElementById("save-register-btn");
    const originalBtnText = saveBtn.innerHTML;
    saveBtn.innerHTML =
      '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i>Guardando...';
    saveBtn.disabled = true;

    fetch("/acovi/api/api-create-company.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        // Verificar la respuesta HTTP
        if (!response.ok) {
          throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        // Añadir log para depuración
        console.log("Respuesta de creación:", data);

        if (data.success) {
          showNotification("Comercio registrado con éxito");
          modalManager.closeAll();
          setTimeout(() => {
            // Recargar página para ver el nuevo comercio
            window.location.reload();
          }, 1500);
        } else {
          // Si hay errores específicos, mostrarlos
          if (data.errors) {
            let errorMsg = "Errores de validación:";
            for (const [field, message] of Object.entries(data.errors)) {
              errorMsg += `\n- ${message}`;
            }
            throw new Error(errorMsg);
          } else {
            throw new Error(data.message || "Error al registrar el comercio");
          }
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showNotification(error.message, "error");
        // Restaurar botón
        saveBtn.innerHTML = originalBtnText;
        saveBtn.disabled = false;
      });
  };

  // Función para confirmar y eliminar comercio
  const setupDeleteCompany = () => {
    // Configurar la apertura del modal de confirmación
    document.querySelectorAll(".delete-btn").forEach((btn) => {
      btn.addEventListener("click", function () {
        const id = this.dataset.id;
        const name = this.dataset.name || "este comercio";

        currentDeleteId = id;

        // Añadir log para depuración
        console.log(
          `Configurando eliminación para comercio ID: ${id}, Nombre: ${name}`
        );

        // Actualizar el texto del modal para incluir el nombre
        const modalTitle = document.getElementById("modal-title");
        if (modalTitle) {
          modalTitle.textContent = `Eliminar ${name}`;
        }

        // Mostrar modal de confirmación
        modalManager.open("delete-confirmation-modal");
      });
    });

    // Configurar botón de confirmación
    const confirmDeleteBtn = document.getElementById("confirm-delete-btn");
    if (confirmDeleteBtn) {
      confirmDeleteBtn.addEventListener("click", function () {
        if (!currentDeleteId) {
          showNotification("ID de comercio no válido", "error");
          return;
        }

        // Añadir log para depuración
        console.log(`Eliminando comercio ID: ${currentDeleteId}`);

        // Cambiar apariencia del botón
        this.innerHTML =
          '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i>Eliminando...';
        this.disabled = true;

        /*  */
        // Crear FormData con el ID y token CSRF
        const formData = new FormData();
        formData.append("id", currentDeleteId);

        // Obtener token CSRF de meta tag
        const csrfToken = document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute("content");

        fetch("/acovi/api/api-delete-company.php", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            // Añadir log para depuración
            console.log("Respuesta de eliminación:", data);

            if (data.success) {
              showNotification("Comercio eliminado con éxito");
              modalManager.closeAll();
              setTimeout(() => {
                // Recargar página para ver cambios
                window.location.reload();
              }, 1500);
            } else {
              throw new Error(data.message || "Error al eliminar el comercio");
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            showNotification(error.message, "error");
            // Restaurar botón
            this.innerHTML = '<i class="fa-solid fa-trash mr-2"></i>Eliminar';
            this.disabled = false;
          });
      });
    }
  };

  // Función para exportar a Excel
  const setupExcelExport = () => {
    const exportBtn = document.getElementById("export-excel-btn");
    if (exportBtn) {
      exportBtn.addEventListener("click", function () {
        // Cambiar apariencia del botón
        const originalBtnText = this.innerHTML;
        this.innerHTML =
          '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i>Exportando...';
        this.disabled = true;

        // Crear una tabla temporal con los datos visibles
        const table = document.getElementById("companies-table");
        if (!table) {
          showNotification("No se encontró la tabla de comercios", "error");
          this.innerHTML = originalBtnText;
          this.disabled = false;
          return;
        }

        try {
          // Crear una nueva hoja de cálculo
          const wb = XLSX.utils.book_new();

          // Obtener los datos de la tabla
          const tableData = [];

          // Obtener encabezados
          const headerRow = [];
          table.querySelectorAll("thead th").forEach((th) => {
            // Omitir columna de acciones
            if (th.textContent.trim() !== "Acciones") {
              headerRow.push(th.textContent.trim());
            }
          });
          tableData.push(headerRow);

          // Obtener filas de datos
          table.querySelectorAll("tbody tr").forEach((tr) => {
            const dataRow = [];
            tr.querySelectorAll("td").forEach((td, index) => {
              // Omitir columna de acciones
              if (index < tr.querySelectorAll("td").length - 1) {
                // Limpiar el contenido de HTML y obtener solo el texto
                let content = td.textContent.trim();
                dataRow.push(content);
              }
            });
            if (dataRow.length > 0) {
              tableData.push(dataRow);
            }
          });

          // Crear hoja con los datos
          const ws = XLSX.utils.aoa_to_sheet(tableData);

          // Añadir la hoja al libro
          XLSX.utils.book_append_sheet(wb, ws, "Comercios");

          // Generar archivo y descargar
          const fecha = new Date().toISOString().slice(0, 10);
          XLSX.writeFile(wb, `listado_comercios_${fecha}.xlsx`);

          showNotification("Datos exportados con éxito");
        } catch (error) {
          console.error("Error al exportar:", error);
          showNotification("Error al exportar los datos", "error");
        }

        // Restaurar botón
        this.innerHTML = originalBtnText;
        this.disabled = false;
      });
    }
  };

  // Función para imprimir la tabla
  const setupTablePrint = () => {
    const printBtn = document.getElementById("print-table-btn");
    if (printBtn) {
      printBtn.addEventListener("click", function () {
        // Abrir una ventana de impresión
        window.print();
      });
    }
  };

  // Configurar cambio de elementos por página
  const setupItemsPerPage = () => {
    const itemsPerPageSelect = document.getElementById("items-per-page");
    if (itemsPerPageSelect) {
      itemsPerPageSelect.addEventListener("change", function () {
        // Enviar formulario automáticamente al cambiar
        document.getElementById("filter-form").submit();
      });
    }
  };

  // Configurar filtros
  const setupFilters = () => {
    // Botón para limpiar filtros
    const clearFiltersBtn = document.getElementById("clear-filters-btn");
    if (clearFiltersBtn) {
      clearFiltersBtn.addEventListener("click", function () {
        window.location.href = "panel.php";
      });
    }

    // Cambio de ordenación
    const orderBySelect = document.getElementById("order-by");
    const orderDirSelect = document.getElementById("order-dir");

    if (orderBySelect && orderDirSelect) {
      // Aplicar ordenación automáticamente al cambiar
      orderBySelect.addEventListener("change", function () {
        document.getElementById("filter-form").submit();
      });

      orderDirSelect.addEventListener("change", function () {
        document.getElementById("filter-form").submit();
      });
    }

    // Filtros automáticos
    const autoFilterFields = document.querySelectorAll(
      "#filter-estado, #filter-categoria"
    );
    autoFilterFields.forEach((field) => {
      field.addEventListener("change", function () {
        document.getElementById("filter-form").submit();
      });
    });
  };

  // Configurar botón de "Añadir primer comercio"
  const setupAddFirstCompany = () => {
    const addFirstBtn = document.getElementById("add-first-company");
    if (addFirstBtn) {
      addFirstBtn.addEventListener("click", function () {
        modalManager.open("register-modal");
      });
    }
  };

  // Configurar filtros móviles
  const setupMobileFilters = () => {
    const toggleFiltersBtn = document.getElementById("toggle-filters");
    const mobileFilters = document.getElementById("mobile-filters");

    if (toggleFiltersBtn && mobileFilters) {
      console.log("Configurando filtros móviles");

      toggleFiltersBtn.addEventListener("click", function () {
        console.log("Botón de filtros móviles clickeado");
        mobileFilters.classList.toggle("hidden");

        // Cambiar el icono
        const icon = this.querySelector("i");
        if (icon) {
          icon.classList.toggle("fa-chevron-down");
          icon.classList.toggle("fa-chevron-up");
        }
      });

      // Sincronizar cambios entre filtros móviles y de escritorio
      const mobileFiltersSelects = document.querySelectorAll(
        "#mobile-filters select"
      );
      mobileFiltersSelects.forEach((select) => {
        const desktopSelect = document.getElementById(
          select.id.replace("-mobile", "")
        );
        if (desktopSelect) {
          select.addEventListener("change", function () {
            desktopSelect.value = this.value;
            document.getElementById("filter-form").submit();
          });
        }
      });
    } else {
      console.error("No se encontraron los elementos de filtros móviles:", {
        toggleFiltersBtn: !!toggleFiltersBtn,
        mobileFilters: !!mobileFilters,
      });
    }
  };

  // Configurar eventos de botones
  const setupButtonEvents = () => {
    // Ver detalle - delegación de eventos
    document.addEventListener("click", function (e) {
      // Para botones de ver detalles
      if (e.target.closest(".view-btn")) {
        const btn = e.target.closest(".view-btn");
        const id = btn.dataset.id;
        if (id) {
          loadCompanyDetails(id);
          modalManager.open("detail-modal");
        } else {
          showNotification("ID de comercio no válido", "error");
        }
      }

      // Para botones de editar
      if (
        e.target.closest(".edit-btn") ||
        e.target.closest("#edit-from-detail-btn")
      ) {
        const btn =
          e.target.closest(".edit-btn") ||
          e.target.closest("#edit-from-detail-btn");
        const id = btn.dataset.id;
        if (id) {
          loadCompanyDataForEdit(id);
        } else {
          showNotification("ID de comercio no válido", "error");
        }
      }
    });

    // Botón de registro
    const registerCompanyBtn = document.getElementById("register-company-btn");
    if (registerCompanyBtn) {
      registerCompanyBtn.addEventListener("click", function () {
        modalManager.open("register-modal");
      });
    }

    // Botón para guardar edición
    const saveEditBtn = document.getElementById("save-edit-btn");
    if (saveEditBtn) {
      saveEditBtn.addEventListener("click", submitEditForm);
    }

    // Botón para guardar registro
    const saveRegisterBtn = document.getElementById("save-register-btn");
    if (saveRegisterBtn) {
      saveRegisterBtn.addEventListener("click", submitRegisterForm);
    }
  };

  // Función principal de inicialización
  const init = () => {
    console.log("Inicializando componentes de la aplicación");

    setupNotificationClosing();
    setupMobileMenu();
    setupMobileFilters(); // Añadido configuración de filtros móviles
    setupModalClosing();
    setupButtonEvents();
    setupCharCounters();
    setupDeleteCompany();
    setupExcelExport();
    setupTablePrint();
    setupItemsPerPage();
    setupFilters();
    setupAddFirstCompany();

    // Para los formularios, prevenir envío por defecto
    const editForm = document.getElementById("edit-form");
    if (editForm) {
      editForm.addEventListener("submit", function (e) {
        e.preventDefault();
        submitEditForm();
      });
    }

    const registerForm = document.getElementById("register-form");
    if (registerForm) {
      registerForm.addEventListener("submit", function (e) {
        e.preventDefault();
        submitRegisterForm();
      });
    }

    // Comprobar si hay mensajes de éxito o error en URL y mostrar notificación
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has("success")) {
      showNotification(decodeURIComponent(urlParams.get("success")));
    }
    if (urlParams.has("error")) {
      showNotification(decodeURIComponent(urlParams.get("error")), "error");
    }

    console.log("Inicialización completada");
  };

  // Iniciar la aplicación
  init();
});
// Fin del script
