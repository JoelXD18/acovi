<?php
/**
 * Clase CompanyController optimizada
 * 
 * Controlador para la gestión de comercios
 */
class CompanyController
{
    private $model;

    /**
     * Constructor
     * 
     * @param CompanyModel $model Modelo de comercios (opcional)
     */
    public function __construct(CompanyModel $model = null)
    {
        try {
            // Inicializar modelo
            $this->model = $model ?? new CompanyModel();

            // Verificar que el modelo tenga los métodos necesarios
            if (!method_exists($this->model, 'checkConnection')) {
                error_log("El método checkConnection no está definido en el modelo");
                throw new Exception("Error de configuración del modelo");
            }

            // Verificar conexión
            if (!$this->checkConnection()) {
                throw new Exception("No se pudo establecer conexión con la base de datos");
            }
        } catch (Exception $e) {
            error_log("Error en la inicialización del controlador: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Método principal para listar comercios con filtros y paginación
     * 
     * @param array $filters Filtros de búsqueda
     * @param int $page Número de página
     * @param int $itemsPerPage Elementos por página
     * @param string $orderBy Campo para ordenamiento
     * @param string $orderDir Dirección de ordenamiento
     * @return array Resultados paginados
     */
    public function index($filters = [], $page = 1, $itemsPerPage = 20, $orderBy = 'nombre', $orderDir = 'ASC')
{
    try {
        // Validar parámetros
        $page = max(1, intval($page));
        $itemsPerPage = max(1, min(100, intval($itemsPerPage)));
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        // Sanitizar filtros
        if (is_array($filters)) {
            $filters = array_map(function ($value) {
                return is_string($value) ? trim($value) : $value;
            }, $filters);
        } else {
            $filters = [];
        }
        // Log de filtros para depuración
        error_log("Filtros aplicados: " . json_encode($filters));
        // Obtener resultados
        $result = $this->model->getAllCompanies($filters, $page, $itemsPerPage, $orderBy, $orderDir);
        if ($result['success']) {
            return [
                'success' => true,
                'data' => [
                    'data' => $result['data']['data'],
                    'total' => $result['data']['total'],
                    'pages' => $result['data']['pages'],
                    'currentPage' => $page
                ]
            ];
        } else {
            throw new Exception($result['message'] ?? 'Error desconocido');
        }
    } catch (Exception $e) {
        error_log("Error en CompanyController::index: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al recuperar los comercios: ' . $e->getMessage(),
            'data' => [
                'data' => [],
                'total' => 0,
                'pages' => 0,
                'currentPage' => 1
            ]
        ];
    }
}
 

    /**
     * Obtiene detalle de un comercio
     * 
     * @param int $id ID del comercio
     * @return array Resultado con datos del comercio
     */
    public function getDetail($id)
    {
        try {
            // Validar ID
            $id = intval($id);
            if ($id <= 0) {
                throw new Exception('ID de comercio inválido');
            }

            $company = $this->model->getCompanyById($id);

            // Añadir log para depuración
            error_log("Datos del comercio ID $id obtenidos: " . json_encode($company));

            if (!$company) {
                return [
                    'success' => false,
                    'message' => 'Comercio no encontrado',
                    'code' => 404
                ];
            }

            return [
                'success' => true,
                'data' => $company
            ];
        } catch (Exception $e) {
            error_log("Error en CompanyController::getDetail: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al recuperar los detalles del comercio: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Crea un nuevo comercio
     * 
     * @param array $data Datos del comercio
     * @return array Resultado de la operación
     */
    public function create($data)
    {
        // Validación de datos
        $validationErrors = $this->validateData($data);
        if (!empty($validationErrors)) {
            return [
                'success' => false,
                'errors' => $validationErrors
            ];
        }

        try {
            // Crear comercio
            $companyId = $this->model->createCompany($data);

            if (!$companyId) {
                throw new Exception('No se pudo crear el comercio');
            }

            return [
                'success' => true,
                'id' => $companyId,
                'message' => 'Comercio creado exitosamente'
            ];

        } catch (Exception $e) {
            error_log("Error creating company: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al crear el comercio: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza un comercio existente
     * 
     * @param int $id ID del comercio
     * @param array $data Datos del comercio
     * @return array Resultado de la operación
     */
    public function update($id, $data)
    {
        // Validación de datos
        $validationErrors = $this->validateData($data, $id);
        if (!empty($validationErrors)) {
            return [
                'success' => false,
                'errors' => $validationErrors
            ];
        }

        try {
            // Actualizar comercio
            $success = $this->model->updateCompany($id, $data);

            if (!$success) {
                throw new Exception('No se pudo actualizar el comercio');
            }

            return [
                'success' => true,
                'message' => 'Comercio actualizado exitosamente'
            ];

        } catch (Exception $e) {
            error_log("Error updating company: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al actualizar el comercio: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina un comercio
     * 
     * @param int $id ID del comercio
     * @return array Resultado de la operación
     */
    public function delete($id)
    {
        try {
            // Validar ID
            $id = intval($id);
            if ($id <= 0) {
                throw new Exception('ID de comercio inválido');
            }

            // Verificar que el comercio exista
            $company = $this->model->getCompanyById($id);
            if (!$company) {
                return [
                    'success' => false,
                    'message' => 'El comercio no existe',
                    'code' => 404
                ];
            }

            // Realizar borrado
            $success = $this->model->deleteCompany($id);

            if (!$success) {
                throw new Exception('No se pudo eliminar el comercio');
            }

            return [
                'success' => true,
                'message' => 'Comercio eliminado exitosamente'
            ];

        } catch (Exception $e) {
            error_log("Error deleting company: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al eliminar el comercio: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Obtiene todas las categorías disponibles
     * 
     * @return array Lista de categorías
     */
    public function getCategories()
    {
        try {
            $categories = $this->model->getCategories();

            return [
                'success' => true,
                'data' => $categories
            ];
        } catch (Exception $e) {
            error_log("Error en CompanyController::getCategories: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error al recuperar las categorías: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Valida los datos de un comercio
     * 
     * @param array $data Datos a validar
     * @param int|null $id ID del comercio en caso de actualización
     * @return array Errores de validación
     */
    private function validateData($data, $id = null)
    {
        $errors = [];

        // Validaciones básicas
        if (empty(trim($data['nombre'] ?? ''))) {
            $errors['nombre'] = 'El nombre es obligatorio';
        } elseif (strlen(trim($data['nombre'])) > 100) {
            $errors['nombre'] = 'El nombre no puede exceder los 100 caracteres';
        }

        if (empty(trim($data['cif'] ?? ''))) {
            $errors['cif'] = 'El CIF es obligatorio';
        }
        // Validación de CIF más permisiva
        elseif (!preg_match('/^[A-Z0-9]{5,9}$/', trim($data['cif']))) {
            $errors['cif'] = 'El formato del CIF no es válido';
        } else {
            // Validación de CIF único
            $query = $id === null
                ? "SELECT ID FROM comercios WHERE CIF = ?"
                : "SELECT ID FROM comercios WHERE CIF = ? AND ID != ?";

            $params = $id === null
                ? [trim($data['cif'])]
                : [trim($data['cif']), intval($id)];

            try {
                $exists = $this->model->query($query, $params);

                if ($exists && count($exists) > 0) {
                    $errors['cif'] = 'El CIF ya está registrado';
                }
            } catch (Exception $e) {
                error_log("Error validando CIF: " . $e->getMessage());
                $errors['cif'] = 'No se pudo validar el CIF';
            }
        }

        if (empty(trim($data['estado'] ?? ''))) {
            $errors['estado'] = 'El estado es obligatorio';
        } elseif (!in_array(trim($data['estado']), ['Activo', 'No activo', 'Pendiente'])) {
            $errors['estado'] = 'El estado debe ser Activo, No activo o Pendiente';
        }

        if (empty($data['categoria'] ?? '')) {
            $errors['categoria'] = 'La categoría es obligatoria';
        } elseif (!is_numeric($data['categoria'])) {
            $errors['categoria'] = 'La categoría debe ser un número';
        }

        // Validación de email si se proporciona
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'El formato del email no es válido';
        }

        // Validación de teléfono si se proporciona (menos estricta)
        if (!empty($data['telefono'])) {
            $telefono = preg_replace('/\D/', '', trim($data['telefono'])); // Eliminar no dígitos
            if (strlen($telefono) != 9) {
                $errors['telefono'] = 'El teléfono debe tener 9 dígitos';
            }
        }

        // Validación de página web si se proporciona
        if (!empty($data['web']) && !filter_var($data['web'], FILTER_VALIDATE_URL)) {
            $errors['web'] = 'La URL de la página web no es válida';
        }

        // Validación de número de empleados si se proporciona
        if (!empty($data['empleados']) && (!is_numeric($data['empleados']) || intval($data['empleados']) < 0)) {
            $errors['empleados'] = 'El número de empleados debe ser un número positivo';
        }

        return $errors;
    }

    /**
     * Verifica la conexión a la base de datos
     * 
     * @return bool Estado de la conexión
     */
    private function checkConnection()
    {
        try {
            return $this->model->checkConnection();
        } catch (Exception $e) {
            error_log("Error de conexión: " . $e->getMessage());
            return false;
        }
    }
}