document.addEventListener("DOMContentLoaded", function () {
  // Funcionalidad para el menú móvil
  document
    .getElementById("mobile-menu-button")
    .addEventListener("click", function () {
      const mobileMenu = document.getElementById("mobile-menu");
      mobileMenu.classList.toggle("hidden");
    });

  // Contador de caracteres para textareas
  const descripcionTextarea = document.getElementById("descripcion");
  const descripcionCount = document.getElementById("descripcion-count");
  const editDescripcionTextarea = document.getElementById("edit-descripcion");
  const editDescripcionCount = document.getElementById(
    "edit-descripcion-count"
  );

  if (descripcionTextarea && descripcionCount) {
    descripcionTextarea.addEventListener("input", function () {
      const count = this.value.length;
      descripcionCount.textContent = count;

      if (count >= 490) {
        descripcionCount.classList.add("text-red-500");
      } else {
        descripcionCount.classList.remove("text-red-500");
      }
    });
  }

  if (editDescripcionTextarea && editDescripcionCount) {
    editDescripcionTextarea.addEventListener("input", function () {
      const count = this.value.length;
      editDescripcionCount.textContent = count;

      if (count >= 490) {
        editDescripcionCount.classList.add("text-red-500");
      } else {
        editDescripcionCount.classList.remove("text-red-500");
      }
    });
  }

  // Convertir CIF a mayúsculas
  const cifInput = document.getElementById("cif");
  const editCifInput = document.getElementById("edit-cif");

  if (cifInput) {
    cifInput.addEventListener("input", function () {
      this.value = this.value.toUpperCase();
    });
  }

  if (editCifInput) {
    editCifInput.addEventListener("input", function () {
      this.value = this.value.toUpperCase();
    });
  }

  // Auto-formato para código postal
  const codigoPostalInput = document.getElementById("codigo_postal");
  const editCodigoPostalInput = document.getElementById("edit-codigo_postal");

  [codigoPostalInput, editCodigoPostalInput].forEach((input) => {
    if (input) {
      input.addEventListener("input", function () {
        // Permitir solo dígitos
        this.value = this.value.replace(/[^0-9]/g, "");

        // Limitar a 5 dígitos
        if (this.value.length > 5) {
          this.value = this.value.slice(0, 5);
        }
      });
    }
  });

  // Auto-formato para número de calle
  const numeroInput = document.getElementById("numero");
  const editNumeroInput = document.getElementById("edit-numero");

  [numeroInput, editNumeroInput].forEach((input) => {
    if (input) {
      input.addEventListener("input", function () {
        // Solo permitir dígitos y una letra al final
        this.value = this.value.replace(/[^0-9A-Za-z]/g, "");

        // Asegurar que solo haya una letra y esté al final
        const digits = this.value.replace(/[A-Za-z]/g, "");
        const letters = this.value.replace(/[0-9]/g, "");

        if (letters.length > 1) {
          // Si hay más de una letra, mantener solo la primera
          this.value = digits + letters.charAt(0);
        }
      });
    }
  });

  // Auto-formato para teléfonos
  const telefonoInputs = [
    document.getElementById("telefono"),
    document.getElementById("telefono_secundario"),
    document.getElementById("edit-telefono"),
    document.getElementById("edit-telefono_secundario"),
  ];

  telefonoInputs.forEach((input) => {
    if (input) {
      input.addEventListener("input", function () {
        // Solo permitir dígitos
        this.value = this.value.replace(/[^0-9]/g, "");

        // Limitar longitud
        if (this.value.length > 15) {
          this.value = this.value.slice(0, 15);
        }
      });
    }
  });

  // Gestión del horario tarde (mostrar/ocultar según el checkbox)
  function setupHorarioTardeToggle(checkboxId, contenedorId) {
    const checkbox = document.getElementById(checkboxId);
    const contenedor = document.getElementById(contenedorId);

    if (checkbox && contenedor) {
      // Inicialización
      contenedor.style.display = checkbox.checked ? "grid" : "none";

      // Event listener
      checkbox.addEventListener("change", function () {
        contenedor.style.display = this.checked ? "grid" : "none";
      });
    }
  }

  // Configurar toggles para ambos formularios
  setupHorarioTardeToggle("tiene_horario_tarde", "horario_tarde_contenedor");
  setupHorarioTardeToggle(
    "edit-tiene_horario_tarde",
    "edit-horario_tarde_contenedor"
  );

  // Validación de formulario para registro de empresa
  const companyForm = document.getElementById("company-form");
  const saveCompanyBtn = document.getElementById("save-company-btn");

  if (companyForm && saveCompanyBtn) {
    saveCompanyBtn.addEventListener("click", function (e) {
      e.preventDefault();
      validarGuardarDatos("company-form", false);
    });
  }

  // Botón para abrir formulario desde la página vacía
  const addFirstCompanyBtn = document.getElementById("add-first-company-btn");
  if (addFirstCompanyBtn) {
    addFirstCompanyBtn.addEventListener("click", function () {
      document.getElementById("register-modal").classList.remove("hidden");
    });
  }

  // Funcionalidad para abrir y cerrar el modal de registro
  document
    .getElementById("register-company-btn")
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
      const companyId = this.getAttribute("data-company-id");
      const companyName = this.getAttribute("data-company-name");
      document.getElementById(
        "edit-modal-title"
      ).textContent = `Editar ${companyName}`;
      document.getElementById("edit-company-id").value = companyId;

      // Aquí cargarías datos de la empresa desde el servidor
      // Para efectos de demostración, usamos una función que limpia el formulario
      clearEditFormErrors();
      document.getElementById("edit-modal").classList.remove("hidden");
    });
  });

  function clearEditFormErrors() {
    // Limpiar mensajes de error en el formulario de edición
    const editForm = document.getElementById("edit-form");
    if (editForm) {
      const errorElements = editForm.querySelectorAll(".text-red-500");
      errorElements.forEach((element) => {
        if (element.id && element.id.endsWith("-error")) {
          element.classList.add("hidden");
          element.textContent = "";
        }
      });

      // Restablecer estilos de los campos
      const formFields = editForm.querySelectorAll("input, select, textarea");
      formFields.forEach((field) => {
        field.classList.remove("border-red-500");
      });
    }
  }

  document
    .getElementById("close-edit-modal-btn")
    .addEventListener("click", function () {
      document.getElementById("edit-modal").classList.add("hidden");
    });

  // Funcionalidad para guardar cambios de edición
  document
    .getElementById("save-edit-btn")
    .addEventListener("click", function () {
      validarGuardarDatos("edit-form", true);
    });

  // Funcionalidad para abrir y cerrar el modal de confirmación de eliminación
  const deleteButtons = document.querySelectorAll(".delete-btn");
  let companyToDelete = null;

  deleteButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const companyId = this.getAttribute("data-company-id");
      const companyName = this.getAttribute("data-company-name");
      document.getElementById(
        "modal-title"
      ).textContent = `Eliminar ${companyName}`;
      companyToDelete = companyId;
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
      // Aquí se enviaría la solicitud para eliminar la empresa
      document
        .getElementById("delete-confirmation-modal")
        .classList.add("hidden");
      showNotification("Empresa eliminada correctamente");
      updateFilterCounter();
    });

  // Función para formatear los horarios para guardar en la base de datos
  function formatearHorario(formId, prefijo = "") {
    // Prefijo puede ser 'edit-' para el formulario de edición
    const form = document.getElementById(formId);
    if (!form) return null;

    // Obtener los días seleccionados
    const diasCheckboxes = form.querySelectorAll(
      `input[name="${prefijo}horario_dias"]:checked`
    );
    const dias = Array.from(diasCheckboxes).map((cb) => cb.value);

    // Obtener horarios
    const mananaApertura = form.querySelector(
      `#${prefijo}horario_manana_apertura`
    ).value;
    const mananaCierre = form.querySelector(
      `#${prefijo}horario_manana_cierre`
    ).value;

    const tieneHorarioTarde = form.querySelector(
      `#${prefijo}tiene_horario_tarde`
    ).checked;

    let tardeApertura = "";
    let tardeCierre = "";

    if (tieneHorarioTarde) {
      tardeApertura = form.querySelector(
        `#${prefijo}horario_tarde_apertura`
      ).value;
      tardeCierre = form.querySelector(`#${prefijo}horario_tarde_cierre`).value;
    }

    const comentarios = form.querySelector(
      `#${prefijo}horario_comentarios`
    ).value;

    // Crear objeto de horario
    const horario = {
      dias: dias,
      manana: {
        apertura: mananaApertura,
        cierre: mananaCierre,
      },
      tarde: tieneHorarioTarde
        ? {
            apertura: tardeApertura,
            cierre: tardeCierre,
          }
        : null,
      comentarios: comentarios,
    };

    return horario;
  }

  // Función para rellenar el formulario de edición con datos existentes
  function rellenarHorarioEdicion(horario) {
    if (!horario) return;

    // Marcar días
    if (horario.dias && Array.isArray(horario.dias)) {
      horario.dias.forEach((dia) => {
        const checkbox = document.getElementById(`edit-horario_${dia}`);
        if (checkbox) checkbox.checked = true;
      });
    }

    // Rellenar horario mañana
    if (horario.manana) {
      document.getElementById("edit-horario_manana_apertura").value =
        horario.manana.apertura || "";
      document.getElementById("edit-horario_manana_cierre").value =
        horario.manana.cierre || "";
    }

    // Rellenar horario tarde si existe
    const tieneHorarioTarde = !!horario.tarde;
    document.getElementById("edit-tiene_horario_tarde").checked =
      tieneHorarioTarde;

    // Mostrar/ocultar sección
    document.getElementById("edit-horario_tarde_contenedor").style.display =
      tieneHorarioTarde ? "grid" : "none";

    if (tieneHorarioTarde && horario.tarde) {
      document.getElementById("edit-horario_tarde_apertura").value =
        horario.tarde.apertura || "";
      document.getElementById("edit-horario_tarde_cierre").value =
        horario.tarde.cierre || "";
    }

    // Rellenar comentarios
    document.getElementById("edit-horario_comentarios").value =
      horario.comentarios || "";
  }

  // Obtener la dirección completa formateada a partir de los campos estructurados
  function obtenerDireccionFormateada(formId, prefijo = "") {
    // Prefijo puede ser 'edit-' para el formulario de edición
    const form = document.getElementById(formId);
    if (!form) return "";

    const tipoVia = form.querySelector(`#${prefijo}tipo_via`).value;
    const nombreVia = form.querySelector(`#${prefijo}nombre_via`).value;
    const numero = form.querySelector(`#${prefijo}numero`).value;
    const piso = form.querySelector(`#${prefijo}piso`).value;
    const puerta = form.querySelector(`#${prefijo}puerta`).value;
    const codigoPostal = form.querySelector(`#${prefijo}codigo_postal`).value;
    const localidad = form.querySelector(`#${prefijo}localidad`).value;
    const provincia = form.querySelector(`#${prefijo}provincia`).value;
    const infoAdicional = form.querySelector(
      `#${prefijo}info_adicional_direccion`
    ).value;

    // Crear dirección formateada
    let direccion = `${tipoVia} ${nombreVia}, ${numero}`;
    if (piso) direccion += `, ${piso}`;
    if (puerta) direccion += `, ${puerta}`;

    direccion += `. ${codigoPostal || ""} ${localidad || ""}`;
    if (provincia) direccion += ` (${provincia})`;
    if (infoAdicional) direccion += `. ${infoAdicional}`;

    return direccion;
  }

  // Función para validar y guardar datos (registro y edición)
  function validarGuardarDatos(formId, esEdicion = false) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const prefijo = esEdicion ? "edit-" : "";

    // Validar campos obligatorios mínimos
    const camposObligatorios = [
      `${prefijo}nombre`,
      `${prefijo}cif`,
      `${prefijo}tipo`,
      `${prefijo}estado`,
      `${prefijo}tipo_via`,
      `${prefijo}nombre_via`,
      `${prefijo}numero`,
      `${prefijo}telefono`,
    ];

    let isValid = true;

    // Limpiar errores previos
    const errorElements = form.querySelectorAll('[id$="-error"]');
    errorElements.forEach((element) => {
      element.classList.add("hidden");
      element.textContent = "";
    });

    // Validar campos requeridos
    camposObligatorios.forEach((fieldId) => {
      const field = document.getElementById(fieldId);
      if (!field) return;

      const errorId = `${fieldId}-error`;
      const errorElement = document.getElementById(errorId);

      if (!field.value.trim()) {
        isValid = false;
        field.classList.add("border-red-500");

        if (errorElement) {
          errorElement.textContent = "Este campo es obligatorio";
          errorElement.classList.remove("hidden");
        }
      } else if (field.validity && !field.validity.valid) {
        isValid = false;
        field.classList.add("border-red-500");

        if (errorElement) {
          errorElement.textContent =
            field.validationMessage || field.title || "Formato inválido";
          errorElement.classList.remove("hidden");
        }
      } else {
        field.classList.remove("border-red-500");
      }
    });

    // Validaciones adicionales específicas

    // Validar CIF
    const cifInput = document.getElementById(`${prefijo}cif`);
    const cifError = document.getElementById(`${prefijo}cif-error`);
    if (cifInput && cifInput.value.trim() && !validateCIF(cifInput.value)) {
      isValid = false;
      cifInput.classList.add("border-red-500");

      if (cifError) {
        cifError.textContent = "El formato del CIF no es válido";
        cifError.classList.remove("hidden");
      }
    }

    // Validar teléfono
    const telefonoInput = document.getElementById(`${prefijo}telefono`);
    const telefonoError = document.getElementById(`${prefijo}telefono-error`);
    if (
      telefonoInput &&
      telefonoInput.value.trim() &&
      !validatePhone(telefonoInput.value)
    ) {
      isValid = false;
      telefonoInput.classList.add("border-red-500");

      if (telefonoError) {
        telefonoError.textContent =
          "El teléfono debe contener solo números (entre 9 y 15 dígitos)";
        telefonoError.classList.remove("hidden");
      }
    }

    // Validar email si tiene valor
    const emailInput = document.getElementById(`${prefijo}email`);
    if (emailInput && emailInput.value.trim()) {
      const emailError = document.getElementById(`${prefijo}email-error`);
      if (!validateEmail(emailInput.value)) {
        isValid = false;
        emailInput.classList.add("border-red-500");

        if (emailError) {
          emailError.textContent = "El formato del email no es válido";
          emailError.classList.remove("hidden");
        }
      }
    }

    // Validar URL web si se ha rellenado
    const webInput = document.getElementById(`${prefijo}web`);
    if (webInput && webInput.value.trim()) {
      const webError = document.getElementById(`${prefijo}web-error`);
      if (!validateURL(webInput.value)) {
        isValid = false;
        webInput.classList.add("border-red-500");

        if (webError) {
          webError.textContent = "La URL debe comenzar con http:// o https://";
          webError.classList.remove("hidden");
        }
      }
    }

    // Validar código postal si se ha rellenado
    const codigoPostalInput = document.getElementById(
      `${prefijo}codigo_postal`
    );
    if (codigoPostalInput && codigoPostalInput.value.trim()) {
      const codigoPostalError = document.getElementById(
        `${prefijo}codigo_postal-error`
      );
      if (!/^[0-9]{5}$/.test(codigoPostalInput.value)) {
        isValid = false;
        codigoPostalInput.classList.add("border-red-500");

        if (codigoPostalError) {
          codigoPostalError.textContent =
            "El código postal debe tener 5 dígitos";
          codigoPostalError.classList.remove("hidden");
        }
      }
    }

    // Si todo es válido, preparar datos para enviar
    if (isValid) {
      // Obtener horario formateado
      const horario = formatearHorario(formId, prefijo);

      // Obtener dirección formateada
      const direccionCompleta = obtenerDireccionFormateada(formId, prefijo);

      // Ejemplo de objeto de datos a enviar
      const datos = {
        nombre: document.getElementById(`${prefijo}nombre`).value,
        cif: document.getElementById(`${prefijo}cif`).value,
        tipo: document.getElementById(`${prefijo}tipo`).value,
        estado: document.getElementById(`${prefijo}estado`).value,
        descripcion:
          document.getElementById(`${prefijo}descripcion`).value || "",

        // Campos de dirección
        direccion: direccionCompleta,
        // También incluir campos individuales para búsquedas
        direccion_tipo_via: document.getElementById(`${prefijo}tipo_via`).value,
        direccion_nombre_via: document.getElementById(`${prefijo}nombre_via`)
          .value,
        direccion_numero: document.getElementById(`${prefijo}numero`).value,
        direccion_piso: document.getElementById(`${prefijo}piso`).value || "",
        direccion_puerta:
          document.getElementById(`${prefijo}puerta`).value || "",
        direccion_codigo_postal:
          document.getElementById(`${prefijo}codigo_postal`).value || "",
        direccion_localidad:
          document.getElementById(`${prefijo}localidad`).value ||
          "Villanueva de la Cañada",
        direccion_provincia:
          document.getElementById(`${prefijo}provincia`).value || "madrid",
        direccion_info_adicional:
          document.getElementById(`${prefijo}info_adicional_direccion`).value ||
          "",

        // Contacto
        telefono: document.getElementById(`${prefijo}telefono`).value,
        telefono_secundario:
          document.getElementById(`${prefijo}telefono_secundario`)?.value || "",
        email: document.getElementById(`${prefijo}email`)?.value || "",
        web: document.getElementById(`${prefijo}web`)?.value || "",

        // Categoría
        categoria: document.getElementById(`${prefijo}categoria`)?.value || "",
        subcategoria:
          document.getElementById(`${prefijo}subcategoria`)?.value || "",

        // Horario (formato JSON)
        horario: JSON.stringify(horario),
      };

      // Si es edición, añadir el ID
      if (esEdicion) {
        datos.id = document.getElementById("edit-company-id").value;
      }

      console.log("Datos a enviar:", datos);

      // Aquí iría la función para enviar los datos al servidor
      // Por ejemplo, usando fetch API

      /*
        fetch('/api/empresas', {
          method: esEdicion ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(datos)
        })
        .then(response => response.json())
        .then(data => {
          console.log('Respuesta:', data);
          if (data.success) {
            // Mostrar notificación de éxito
            showNotification(esEdicion ? "Empresa actualizada correctamente" : "Empresa registrada correctamente");
            
            // Cerrar modal
            document.getElementById(esEdicion ? "edit-modal" : "register-modal").classList.add("hidden");
            
            // Resetear formulario si es nuevo registro
            if (!esEdicion) {
              form.reset();
            }
            
            // Recargar datos de la tabla
            // cargarDatosTabla();
          } else {
            // Mostrar error
            showErrorNotification(data.message || "Error al guardar los datos");
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showErrorNotification("Error de conexión al guardar los datos");
        });
        */

      // Para este ejemplo, simularemos éxito
      showNotification(
        esEdicion
          ? "Empresa actualizada correctamente"
          : "Empresa registrada correctamente"
      );
      document
        .getElementById(esEdicion ? "edit-modal" : "register-modal")
        .classList.add("hidden");

      // Resetear formulario si es nuevo registro
      if (!esEdicion) {
        form.reset();
      }

      return true;
    } else {
      showErrorNotification("Por favor, corrige los errores en el formulario");
      return false;
    }
  }

  // Funcionalidad para filtrar la tabla mediante búsqueda por nombre
  document.getElementById("search").addEventListener("keyup", function () {
    filterTable();
  });

  // Funcionalidad para los filtros
  document
    .getElementById("filter-estado")
    .addEventListener("change", function () {
      filterTable();
    });

  document
    .getElementById("filter-categoria")
    .addEventListener("change", function () {
      filterTable();
    });

  document
    .getElementById("filter-tipo")
    .addEventListener("change", function () {
      filterTable();
    });

  // Botón para limpiar filtros
  document
    .getElementById("clear-filters-btn")
    .addEventListener("click", function () {
      document.getElementById("search").value = "";
      document.getElementById("filter-estado").value = "todos";
      document.getElementById("filter-categoria").value = "todas";
      document.getElementById("filter-tipo").value = "todos";
      filterTable();
    });

  // Función para filtrar la tabla según todos los criterios
  function filterTable() {
    const searchTerm = document.getElementById("search").value.toLowerCase();
    const estadoFilter = document.getElementById("filter-estado").value;
    const categoriaFilter = document.getElementById("filter-categoria").value;
    const tipoFilter = document.getElementById("filter-tipo").value;

    const tableRows = document.querySelectorAll("#companies-table tbody tr");
    let visibleCount = 0;
    const totalCount = tableRows.length;

    // Si no hay filas en la tabla, mostrar mensaje de 'no hay empresas'
    if (totalCount === 0) {
      document.getElementById("no-results").classList.remove("hidden");
      document.getElementById("filter-count").classList.add("hidden");
      return;
    }

    tableRows.forEach((row) => {
      const companyName =
        row.getAttribute("data-company-name")?.toLowerCase() || "";
      const companyEstado = row.getAttribute("data-company-estado") || "";
      const companyCategoria = row.getAttribute("data-company-categoria") || "";
      const companyTipo = row.getAttribute("data-company-tipo") || "";

      // Verificar si cumple con todos los filtros
      const matchesSearch = companyName.includes(searchTerm);
      const matchesEstado =
        estadoFilter === "todos" || companyEstado === estadoFilter;
      const matchesCategoria =
        categoriaFilter === "todas" || companyCategoria === categoriaFilter;
      const matchesTipo = tipoFilter === "todos" || companyTipo === tipoFilter;

      if (matchesSearch && matchesEstado && matchesCategoria && matchesTipo) {
        row.classList.remove("hidden");
        visibleCount++;
      } else {
        row.classList.add("hidden");
      }
    });

    // Mostrar mensaje cuando no hay resultados
    const noResultsElement = document.getElementById("no-results");
    if (visibleCount === 0 && totalCount > 0) {
      noResultsElement.classList.remove("hidden");
      // Cambiar el texto para indicar que es por filtros
      const noResultsTitle = noResultsElement.querySelector("h3");
      const noResultsDesc = noResultsElement.querySelector("p");

      if (noResultsTitle)
        noResultsTitle.textContent = "No se encontraron resultados";
      if (noResultsDesc)
        noResultsDesc.textContent =
          "No hay empresas que coincidan con los criterios de búsqueda.";

      // Mostrar el botón de limpiar filtros
      const clearSearchBtn = document.getElementById("clear-search-btn");
      if (clearSearchBtn) clearSearchBtn.classList.remove("hidden");
    } else {
      noResultsElement.classList.add("hidden");
    }

    // Actualizar contador de resultados
    document.getElementById("filtered-count").textContent = visibleCount;
    document.getElementById("total-count").textContent = totalCount;
    document
      .getElementById("filter-count")
      .classList.toggle(
        "hidden",
        visibleCount === totalCount || totalCount === 0
      );
  }

  // Inicializar contador de filtros
  function updateFilterCounter() {
    const tableRows = document.querySelectorAll("#companies-table tbody tr");
    const visibleRows = document.querySelectorAll(
      "#companies-table tbody tr:not(.hidden)"
    );

    // Si no hay filas, mostramos el mensaje de "no hay empresas"
    if (tableRows.length === 0) {
      document.getElementById("no-results").classList.remove("hidden");
      document.getElementById("filter-count").classList.add("hidden");
      return;
    }

    document.getElementById("filtered-count").textContent = visibleRows.length;
    document.getElementById("total-count").textContent = tableRows.length;
    document
      .getElementById("filter-count")
      .classList.toggle("hidden", visibleRows.length === tableRows.length);
  }

  // Botón para limpiar búsqueda cuando no hay resultados
  const clearSearchBtn = document.getElementById("clear-search-btn");
  if (clearSearchBtn) {
    clearSearchBtn.addEventListener("click", function () {
      document.getElementById("search").value = "";
      document.getElementById("filter-estado").value = "todos";
      document.getElementById("filter-categoria").value = "todas";
      document.getElementById("filter-tipo").value = "todos";
      filterTable();
    });
  }

  // Funcionalidad para exportar a Excel
  document
    .getElementById("export-excel-btn")
    .addEventListener("click", function () {
      // Verificar si hay datos para exportar
      const tableRows = document.querySelectorAll("#companies-table tbody tr");
      if (tableRows.length === 0) {
        showErrorNotification("No hay datos para exportar");
        return;
      }

      // Crear una nueva hoja de cálculo
      const workbook = XLSX.utils.book_new();

      // Obtener datos de la tabla (solo filas visibles)
      const table = document.getElementById("companies-table");
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

        // Solo exportamos si hay celdas
        if (cells.length > 0) {
          // Nombre (extraemos el texto, no todo el HTML)
          const nameCell = cells[0].querySelector(".text-sm");
          rowData.push(
            nameCell ? nameCell.textContent.trim() : cells[0].textContent.trim()
          );

          // Resto de columnas (excepto la última que es acciones)
          for (let i = 1; i < cells.length - 1; i++) {
            // Si es un enlace, obtenemos el href
            const link = cells[i].querySelector("a");
            if (link) {
              rowData.push(link.href);
            } else {
              rowData.push(cells[i].textContent.trim());
            }
          }

          tableData.push(rowData);
        }
      });

      if (tableData.length <= 1) {
        showErrorNotification("No hay datos visibles para exportar");
        return;
      }

      try {
        // Crear la hoja y añadirla al libro
        const worksheet = XLSX.utils.aoa_to_sheet(tableData);
        XLSX.utils.book_append_sheet(workbook, worksheet, "Empresas");

        // Generar el archivo y descargarlo
        const excelBuffer = XLSX.write(workbook, {
          bookType: "xlsx",
          type: "array",
        });
        const blob = new Blob([excelBuffer], {
          type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        });
        saveAs(blob, "listado_empresas.xlsx");

        showNotification("Datos exportados correctamente");
      } catch (error) {
        console.error("Error al exportar datos:", error);
        showErrorNotification("Error al exportar datos");
      }
    });

  // Funciones de validación
  function validateCIF(cif) {
    // Formato básico: una letra seguida de 8 dígitos o 8 dígitos seguidos de una letra
    const cifRegex = /^[A-Z0-9]{9}$/;
    return cifRegex.test(cif);
  }

  function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  function validateURL(url) {
    try {
      new URL(url);
      return true;
    } catch (e) {
      return false;
    }
  }

  function validatePhone(phone) {
    const phoneRegex = /^[0-9]{9,15}$/;
    return phoneRegex.test(phone);
  }

  // Función para mostrar notificaciones
  function showNotification(message) {
    const notification = document.getElementById("success-notification");
    document.getElementById("notification-message").textContent = message;
    notification.classList.remove("hidden");

    setTimeout(function () {
      notification.classList.add("hidden");
    }, 3000);
  }

  function showErrorNotification(message) {
    const notification = document.getElementById("error-notification");
    document.getElementById("error-message").textContent = message;
    notification.classList.remove("hidden");

    setTimeout(function () {
      notification.classList.add("hidden");
    }, 3000);
  }

  document
    .getElementById("close-notification")
    .addEventListener("click", function () {
      document.getElementById("success-notification").classList.add("hidden");
    });

  document
    .getElementById("close-error-notification")
    .addEventListener("click", function () {
      document.getElementById("error-notification").classList.add("hidden");
    });

  // Inicializar la tabla al cargar la página
  updateFilterCounter();
});
