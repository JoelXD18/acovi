<?php
/**
 * Clase CompanyModel optimizada para gestión de comercios
 * 
 * Proporciona métodos para operaciones CRUD y consultas eficientes a la base de datos
 */
class CompanyModel
{
    private $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Verifica la conexión a la base de datos
     * 
     * @return bool Estado de la conexión
     */
    public function checkConnection()
    {
        try {
            $result = $this->db->query("SELECT 1");
            return !empty($result);
        } catch (Exception $e) {
            error_log("Error de conexión en CompanyModel: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los comercios con filtros, paginación y ordenamiento
     * 
     * @param array $filters Filtros aplicados
     * @param int $page Número de página
     * @param int $itemsPerPage Elementos por página
     * @param string $orderBy Campo para ordenar
     * @param string $orderDir Dirección de ordenamiento (ASC/DESC)
     * @return array Resultado con datos, total y páginas
     */
    public function getAllCompanies($filters = [], $page = 1, $itemsPerPage = 20, $orderBy = 'nombre', $orderDir = 'ASC')
    {
        try {
            // Validar parámetros
            $page = max(1, intval($page));
            $itemsPerPage = max(1, min(100, intval($itemsPerPage)));
            $offset = ($page - 1) * $itemsPerPage;
            $conditions = [];
            $params = [];

            // Mapeo de campos para ordenación
            $orderMapping = [
                'nombre' => 'c.Nombre',
                'cif' => 'c.CIF',
                'estado' => 'c.Estado',
                'categoria' => 'cat.Principal',
                'empleados' => 'c.numero_empleados',
                'email' => 'c.Email'
            ];

            // Validar ordenación
            $safeOrderBy = isset($orderMapping[$orderBy]) ? $orderMapping[$orderBy] : 'c.Nombre';
            $safeOrderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

            // Consulta optimizada con JOIN explícitos e índices
            $baseSql = "
                SELECT DISTINCT
                    c.ID,
                    c.Nombre as nombre,
                    c.CIF as cif,
                    c.Descripcion as descripcion,
                    c.Estado as estado,
                    c.Email as email,
                    c.numero_empleados as empleados,
                    c.forma_juridica,
                    c.Id_categoria,
                    cat.Principal as categoria,
                    CONCAT(
                        IFNULL(dir.Tipo_via, ''), ' ', 
                        IFNULL(dir.Nombre_calle, ''), 
                        IF(dir.Numero IS NOT NULL AND dir.Numero != '', CONCAT(' ', dir.Numero), '')
                    ) as direccion,
                    CONCAT(
                        IFNULL(h.Dias_Laborables, ''),
                        IF(h.Apertura_Manana IS NOT NULL, 
                           CONCAT(': ', TIME_FORMAT(h.Apertura_Manana, '%H:%i'), '-', 
                                 TIME_FORMAT(h.Cierre_Manana, '%H:%i')), ''),
                        IF(h.Apertura_Tarde IS NOT NULL, 
                           CONCAT(' / ', TIME_FORMAT(h.Apertura_Tarde, '%H:%i'), '-',
                                 TIME_FORMAT(h.Cierre_Tarde, '%H:%i')), '')
                    ) as horario,
                    t.Telefono as telefono_principal
                FROM comercios c
                LEFT JOIN categoria cat ON c.Id_categoria = cat.ID
                LEFT JOIN direccion dir ON c.ID = dir.Id_comercio
                LEFT JOIN horario h ON c.ID = h.Id_comercio
                LEFT JOIN (
                    SELECT Id_Comercio, Telefono
                    FROM contactos
                    WHERE Tipo = 'Principal'
                    LIMIT 1
                ) t ON c.ID = t.Id_Comercio
            ";

            // Aplicar filtros
            if (!empty($filters['search'])) {
                $searchTerm = '%' . trim($filters['search']) . '%';
                $conditions[] = "(c.Nombre LIKE ? OR c.CIF LIKE ? OR c.Email LIKE ?)";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
            }

            if (!empty($filters['estado'])) {
                $conditions[] = "c.Estado = ?";
                $params[] = $filters['estado'];
            }

            if (!empty($filters['categoria']) && intval($filters['categoria']) > 0) {
                $conditions[] = "c.Id_categoria = ?";
                $params[] = intval($filters['categoria']);
            }

            if (!empty($filters['tipo'])) {
                $conditions[] = "c.forma_juridica = ?";
                $params[] = $filters['tipo'];
            }

            // Añadir condiciones WHERE
            if (!empty($conditions)) {
                $baseSql .= " WHERE " . implode(" AND ", $conditions);
            }

            // Consulta para contar registros usando SQL_CALC_FOUND_ROWS
            $totalSql = "SELECT COUNT(1) as total FROM (" . $baseSql . ") AS counted";
            $totalResult = $this->query($totalSql, $params);
            $totalItems = isset($totalResult[0]['total']) ? intval($totalResult[0]['total']) : 0;

            // Añadir ordenamiento y paginación
            $baseSql .= " ORDER BY {$safeOrderBy} {$safeOrderDir} LIMIT ? OFFSET ?";
            $params[] = intval($itemsPerPage);
            $params[] = intval($offset);

            // Ejecutar consulta principal
            $data = $this->query($baseSql, $params);

            return [
                'success' => true,
                'data' => [
                    'data' => $data,
                    'total' => $totalItems,
                    'pages' => ceil($totalItems / $itemsPerPage)
                ]
            ];
        } catch (Exception $e) {
            error_log("Error en getAllCompanies: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error al obtener los comercios: " . $e->getMessage(),
                'data' => [
                    'data' => [],
                    'total' => 0,
                    'pages' => 0
                ]
            ];
        }
    }

    /**
     * Ejecuta una consulta SQL
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parámetros para la consulta
     * @return array|bool Resultado de la consulta
     */
    public function query($sql, $params = [])
    {
        try {
            return $this->db->query($sql, $params);
        } catch (Exception $e) {
            error_log("Error en query: " . $e->getMessage() . " - SQL: " . $sql);
            throw $e;
        }
    }

    /**
     * Ejecuta una consulta SQL sin retorno de datos
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parámetros para la consulta
     * @return bool Resultado de la ejecución
     */
    public function execute($sql, $params = [])
    {
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Error en execute: " . $e->getMessage() . " - SQL: " . $sql);
            throw $e;
        }
    }

    /**
     * Ejecuta una consulta SQL de inserción y retorna el ID
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parámetros para la consulta
     * @return int|bool ID insertado o falso en caso de error
     */
    public function insert($sql, $params = [])
    {
        try {
            return $this->db->insert($sql, $params);
        } catch (Exception $e) {
            error_log("Error en insert: " . $e->getMessage() . " - SQL: " . $sql);
            throw $e;
        }
    }

    /**
     * Inicia una transacción
     * 
     * @return bool Resultado
     */
    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }

    /**
     * Confirma una transacción
     * 
     * @return bool Resultado
     */
    public function commit()
    {
        return $this->db->commit();
    }

    /**
     * Revierte una transacción
     * 
     * @return bool Resultado
     */
    public function rollback()
    {
        return $this->db->rollback();
    }
    /**
 * Obtiene un comercio por su ID con datos relacionados
 * 
 * @param int $id ID del comercio
 * @return array|null Datos del comercio o null si no existe
 */
public function getCompanyById($id)
{
    try {
        $id = intval($id); // Validación de tipo

        // Consulta principal con JOIN optimizados y nombres de campo consistentes
        $sql = "
            SELECT 
                c.ID,
                c.Nombre as nombre,
                c.CIF as cif,
                c.Descripcion as descripcion,
                c.Estado as estado,
                c.Email as email,
                c.numero_empleados as empleados,
                c.forma_juridica,
                c.Id_categoria as categoria_id,
                cat.Principal as categoria,
                (SELECT GROUP_CONCAT(DISTINCT sc.Nombre SEPARATOR ', ') 
                 FROM sub_categoria sc 
                 WHERE sc.id_categoria = c.Id_categoria) as subcategorias,
                dir.ID as direccion_id, 
                CONCAT(
                    IFNULL(dir.Tipo_via, ''), ' ', 
                    IFNULL(dir.Nombre_calle, ''), 
                    IF(dir.Numero IS NOT NULL AND dir.Numero != '', CONCAT(' ', dir.Numero), '')
                ) as direccion,
                dir.Codigo_Postal as codigo_postal,
                dir.provincia, 
                dir.ciudad,
                dir.detalles_adicionales,
                h.ID as horario_id,
                CONCAT(
                    IFNULL(h.Dias_Laborables, ''),
                    IF(h.Apertura_Manana IS NOT NULL, 
                       CONCAT(': ', TIME_FORMAT(h.Apertura_Manana, '%H:%i'), '-', 
                             TIME_FORMAT(h.Cierre_Manana, '%H:%i')), ''),
                    IF(h.Apertura_Tarde IS NOT NULL, 
                       CONCAT(' / ', TIME_FORMAT(h.Apertura_Tarde, '%H:%i'), '-',
                             TIME_FORMAT(h.Cierre_Tarde, '%H:%i')), '')
                ) as horario,
                con.Telefono as telefono_principal,
                pw.URL as web
            FROM comercios c
            LEFT JOIN categoria cat ON c.Id_categoria = cat.ID
            LEFT JOIN direccion dir ON c.ID = dir.Id_comercio
            LEFT JOIN horario h ON c.ID = h.Id_comercio
            LEFT JOIN contactos con ON c.ID = con.Id_Comercio AND con.Tipo = 'Principal'
            LEFT JOIN paginaweb pw ON c.ID = pw.Id_comercio
            WHERE c.ID = ?
            LIMIT 1
        ";

        $result = $this->db->query($sql, [$id]);
        
        // Depuración
        error_log("Consultando comercio ID: $id");
        if (!empty($result)) {
            error_log("Datos recuperados: " . json_encode($result[0]));
            return $result[0];
        }

        error_log("No se encontró el comercio con ID: $id");
        return null;
    } catch (Exception $e) {
        error_log("Error en getCompanyById: " . $e->getMessage());
        return null;
    }
}

    /**
     * Crea un nuevo comercio
     * 
     * @param array $data Datos del comercio
     * @return int|bool ID del comercio creado o false en caso de error
     */
    public function createCompany($data)
    {
        try {
            // Validación básica
            if (empty(trim($data['nombre'])) || empty(trim($data['cif']))) {
                throw new Exception("Faltan campos obligatorios");
            }

            $this->beginTransaction();

            // Insertar en la tabla comercios
            $sql = "
                INSERT INTO comercios (
                    Nombre, Descripcion, CIF, Estado, Email, 
                    Id_categoria, numero_empleados, forma_juridica
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ";

            $params = [
                trim($data['nombre']),
                !empty($data['descripcion']) ? trim($data['descripcion']) : null,
                trim($data['cif']),
                trim($data['estado']),
                !empty($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : null,
                intval($data['categoria']),
                !empty($data['empleados']) ? intval($data['empleados']) : null,
                !empty($data['forma_juridica']) ? trim($data['forma_juridica']) : null
            ];

            $id = $this->db->insert($sql, $params);

            if (!$id) {
                throw new Exception("Error al insertar el comercio");
            }

            // Registrar en auditoría
            $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 1;

            $this->query(
                "INSERT INTO auditoria (
                    Tabla_Afectada, Accion, Fecha_Hora, Valor_Anterior, 
                    Valor_Actualizado, Id_usuario
                ) VALUES (?, ?, NOW(), ?, ?, ?)",
                ['comercios', 'INSERT', null, 'Nuevo comercio: ' . $data['nombre'], $userId]
            );

            // Guardar información adicional
            if (!empty($data['direccion'])) {
                $this->saveAddress($id, $data);
            }

            if (!empty($data['horario'])) {
                $this->saveSchedule($id, $data);
            }

            if (!empty($data['telefono'])) {
                $this->saveContact($id, $data);
            }

            if (!empty($data['web'])) {
                $this->saveWebsite($id, $data['web']);
            }

            $this->commit();
            return $id;

        } catch (Exception $e) {
            $this->rollback();
            error_log("Error al crear comercio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un comercio existente
     * 
     * @param int $id ID del comercio
     * @param array $data Datos del comercio
     * @return bool Resultado de la operación
     */
    public function updateCompany($id, $data)
    {
        try {
            $id = intval($id); // Validación de tipo

            // Validación básica
            if (empty(trim($data['nombre'])) || empty(trim($data['cif']))) {
                throw new Exception("Faltan campos obligatorios");
            }

            $this->beginTransaction();

            // Actualizar tabla comercios
            $sql = "
                UPDATE comercios 
                SET Nombre = ?, 
                    Descripcion = ?, 
                    CIF = ?, 
                    Estado = ?, 
                    Email = ?, 
                    Id_categoria = ?, 
                    numero_empleados = ?, 
                    forma_juridica = ?
                WHERE ID = ?
            ";

            $params = [
                trim($data['nombre']),
                !empty($data['descripcion']) ? trim($data['descripcion']) : null,
                trim($data['cif']),
                trim($data['estado']),
                !empty($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : null,
                intval($data['categoria']),
                !empty($data['empleados']) ? intval($data['empleados']) : null,
                !empty($data['forma_juridica']) ? trim($data['forma_juridica']) : null,
                $id
            ];

            $result = $this->execute($sql, $params);

            if (!$result) {
                throw new Exception("Error al actualizar el comercio");
            }

            // Registrar en auditoría
            $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 1;

            $this->query(
                "INSERT INTO auditoria (
                    Tabla_Afectada, Accion, Fecha_Hora, Valor_Anterior, 
                    Valor_Actualizado, Id_usuario
                ) VALUES (?, ?, NOW(), ?, ?, ?)",
                ['comercios', 'UPDATE', 'Comercio ID: ' . $id, 'Actualizado: ' . $data['nombre'], $userId]
            );

            // Actualizar información adicional
            if (isset($data['direccion'])) {
                $this->saveAddress($id, $data);
            }

            if (isset($data['horario'])) {
                $this->saveSchedule($id, $data);
            }

            if (isset($data['telefono'])) {
                $this->saveContact($id, $data);
            }

            if (isset($data['web'])) {
                $this->saveWebsite($id, $data['web']);
            }

            $this->commit();
            return true;

        } catch (Exception $e) {
            $this->rollback();
            error_log("Error al actualizar comercio: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Guarda o actualiza la dirección de un comercio
     * 
     * @param int $companyId ID del comercio
     * @param array $data Datos de la dirección
     * @return int|bool ID de la dirección o false en caso de error
     */
    public function saveAddress($companyId, $data)
    {
        try {
            $companyId = intval($companyId);

            // Verificar si ya existe una dirección
            $exists = $this->query(
                "SELECT ID FROM direccion WHERE Id_comercio = ?",
                [$companyId]
            );

            if (!empty($exists)) {
                // Actualizar existente
                $sql = "
                    UPDATE direccion 
                    SET Tipo_via = ?, 
                        Nombre_calle = ?,
                        Numero = ?, 
                        Codigo_Postal = ?, 
                        provincia = ?, 
                        ciudad = ?, 
                        detalles_adicionales = ?
                    WHERE Id_comercio = ?
                ";

                $params = [
                    !empty($data['tipo_via']) ? trim($data['tipo_via']) : 'Calle',
                    trim($data['direccion']),
                    !empty($data['numero']) ? trim($data['numero']) : '',
                    !empty($data['codigo_postal']) ? trim($data['codigo_postal']) : '28691',
                    !empty($data['provincia']) ? trim($data['provincia']) : 'Madrid',
                    !empty($data['ciudad']) ? trim($data['ciudad']) : 'Villanueva de la Cañada',
                    !empty($data['detalles']) ? trim($data['detalles']) : null,
                    $companyId
                ];

                return $this->execute($sql, $params);
            } else {
                // Insertar nueva
                $sql = "
                    INSERT INTO direccion (
                        Tipo_via, Nombre_calle, Numero, Codigo_Postal, 
                        Id_comercio, provincia, ciudad, detalles_adicionales
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ";

                $params = [
                    !empty($data['tipo_via']) ? trim($data['tipo_via']) : 'Calle',
                    trim($data['direccion']),
                    !empty($data['numero']) ? trim($data['numero']) : '',
                    !empty($data['codigo_postal']) ? trim($data['codigo_postal']) : '28691',
                    $companyId,
                    !empty($data['provincia']) ? trim($data['provincia']) : 'Madrid',
                    !empty($data['ciudad']) ? trim($data['ciudad']) : 'Villanueva de la Cañada',
                    !empty($data['detalles']) ? trim($data['detalles']) : null
                ];

                return $this->db->insert($sql, $params);
            }
        } catch (Exception $e) {
            error_log("Error al guardar dirección: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Guarda o actualiza el horario de un comercio
     * 
     * @param int $companyId ID del comercio
     * @param array $data Datos del horario
     * @return int|bool ID del horario o false en caso de error
     */
    public function saveSchedule($companyId, $data)
    {
        try {
            $companyId = intval($companyId);

            // Parsear formato de horario
            $schedule = $this->parseSchedule(isset($data['horario']) ? $data['horario'] : '');

            // Verificar si ya existe un horario
            $exists = $this->query(
                "SELECT ID FROM horario WHERE Id_comercio = ?",
                [$companyId]
            );

            if (!empty($exists)) {
                // Actualizar existente
                $sql = "
                    UPDATE horario 
                    SET Dias_Laborables = ?, 
                        Apertura_Manana = ?, 
                        Cierre_Manana = ?, 
                        Apertura_Tarde = ?, 
                        Cierre_Tarde = ?
                    WHERE Id_comercio = ?
                ";

                $params = [
                    $schedule['dias'],
                    $schedule['am'],
                    $schedule['cm'],
                    $schedule['at'],
                    $schedule['ct'],
                    $companyId
                ];

                return $this->execute($sql, $params);
            } else {
                // Insertar nuevo
                $sql = "
                    INSERT INTO horario (
                        Dias_Laborables, Apertura_Manana, Cierre_Manana, 
                        Apertura_Tarde, Cierre_Tarde, Id_comercio
                    ) VALUES (?, ?, ?, ?, ?, ?)
                ";

                $params = [
                    $schedule['dias'],
                    $schedule['am'],
                    $schedule['cm'],
                    $schedule['at'],
                    $schedule['ct'],
                    $companyId
                ];

                return $this->db->insert($sql, $params);
            }
        } catch (Exception $e) {
            error_log("Error al guardar horario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Analiza un string de horario y extrae componentes
     * 
     * @param string $scheduleStr String de horario
     * @return array Componentes del horario
     */
    private function parseSchedule($scheduleStr)
    {
        // Valores predeterminados
        $result = [
            'dias' => 'Lunes a Viernes',
            'am' => '09:00:00',
            'cm' => '14:00:00',
            'at' => '16:00:00',
            'ct' => '20:00:00'
        ];

        if (empty($scheduleStr)) {
            return $result;
        }

        $scheduleStr = trim($scheduleStr);

        // Parsear días
        if (preg_match('/([L-VD\-a-zA-Z\s]+)[:;]\s*/', $scheduleStr, $matches)) {
            $days = trim($matches[1]);

            // Mapeo común de formatos de días
            $dayMappings = [
                'L-V' => 'Lunes a Viernes',
                'L-S' => 'Lunes a Sábado',
                'L-D' => 'Todos los días',
                'LUN-VIE' => 'Lunes a Viernes',
                'LUN-SAB' => 'Lunes a Sábado',
                'LUNES-VIERNES' => 'Lunes a Viernes',
                'LUNES-DOMINGO' => 'Todos los días'
            ];

            if (isset($dayMappings[$days])) {
                $result['dias'] = $dayMappings[$days];
            } else {
                // Si no coincide exactamente, usar lo que se ingresó
                $result['dias'] = $days;
            }
        }

        // Parsear horarios con regex mejorado
        if (preg_match('/(\d{1,2}[:\.]\d{2})\s*[-a]\s*(\d{1,2}[:\.]\d{2})(?:[,\s\/y]+(\d{1,2}[:\.]\d{2})\s*[-a]\s*(\d{1,2}[:\.]\d{2}))?/', $scheduleStr, $matches)) {
            if (isset($matches[1]) && isset($matches[2])) {
                // Normalizar formato (cambiar . por :)
                $am = str_replace('.', ':', $matches[1]);
                $cm = str_replace('.', ':', $matches[2]);

                // Formatear correctamente
                $result['am'] = $this->formatTimeForDB($am);
                $result['cm'] = $this->formatTimeForDB($cm);
            }

            if (isset($matches[3]) && isset($matches[4])) {
                // Normalizar formato para horario de tarde
                $at = str_replace('.', ':', $matches[3]);
                $ct = str_replace('.', ':', $matches[4]);

                // Formatear correctamente
                $result['at'] = $this->formatTimeForDB($at);
                $result['ct'] = $this->formatTimeForDB($ct);
            }
        }

        return $result;
    }

    /**
     * Formatea hora para almacenar en BD
     * 
     * @param string $timeStr String de hora (HH:MM)
     * @return string Hora formateada para BD
     */
    private function formatTimeForDB($timeStr)
    {
        $parts = explode(':', $timeStr);
        if (count($parts) >= 2) {
            $hour = intval($parts[0]);
            $min = intval($parts[1]);
            return sprintf('%02d:%02d:00', $hour, $min);
        }
        return $timeStr;
    }

    /**
     * Guarda o actualiza el contacto de un comercio
     * 
     * @param int $companyId ID del comercio
     * @param array $data Datos del contacto
     * @return int|bool ID del contacto o false en caso de error
     */
    public function saveContact($companyId, $data)
    {
        try {
            $companyId = intval($companyId);

            // Verificar si ya existe un contacto principal
            $exists = $this->query(
                "SELECT ID FROM contactos WHERE Id_Comercio = ? AND Tipo = 'Principal'",
                [$companyId]
            );

            if (!empty($exists)) {
                // Actualizar existente
                $sql = "
                    UPDATE contactos 
                    SET Telefono = ?,
                        Whatsapp = ?
                    WHERE Id_Comercio = ? AND Tipo = 'Principal'
                ";

                $params = [
                    !empty($data['telefono']) ? trim($data['telefono']) : '',
                    !empty($data['whatsapp']) ? trim($data['whatsapp']) : null,
                    $companyId
                ];

                return $this->execute($sql, $params);
            } else {
                // Insertar nuevo
                $sql = "
                    INSERT INTO contactos (
                        Tipo, Telefono, Whatsapp, Id_Comercio
                    ) VALUES (?, ?, ?, ?)
                ";

                $params = [
                    'Principal',
                    !empty($data['telefono']) ? trim($data['telefono']) : '',
                    !empty($data['whatsapp']) ? trim($data['whatsapp']) : null,
                    $companyId
                ];

                return $this->db->insert($sql, $params);
            }
        } catch (Exception $e) {
            error_log("Error al guardar contacto: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Guarda o actualiza la página web de un comercio
     * 
     * @param int $companyId ID del comercio
     * @param string $url URL del sitio web
     * @return int|bool ID de la página web o false en caso de error
     */
    public function saveWebsite($companyId, $url)
    {
        try {
            $companyId = intval($companyId);
            $url = filter_var(trim($url), FILTER_SANITIZE_URL);

            // No guardar si la URL está vacía
            if (empty($url)) {
                return true;
            }

            // Verificar si ya existe una web
            $exists = $this->query(
                "SELECT ID FROM paginaweb WHERE Id_comercio = ?",
                [$companyId]
            );

            if (!empty($exists)) {
                // Actualizar existente
                $sql = "UPDATE paginaweb SET URL = ? WHERE Id_comercio = ?";
                $params = [$url, $companyId];
                return $this->execute($sql, $params);
            } else {
                // Insertar nueva
                $sql = "INSERT INTO paginaweb (URL, Id_comercio) VALUES (?, ?)";
                $params = [$url, $companyId];
                return $this->db->insert($sql, $params);
            }
        } catch (Exception $e) {
            error_log("Error al guardar página web: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene las categorías de comercios
     * 
     * @return array Lista de categorías
     */
    public function getCategories()
    {
        try {
            return $this->query("SELECT * FROM categoria ORDER BY Principal");
        } catch (Exception $e) {
            error_log("Error al obtener categorías: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Elimina un comercio y todos sus datos relacionados
     * 
     * @param int $id ID del comercio
     * @return bool Resultado de la operación
     */
    public function deleteCompany($id)
    {
        try {
            $id = intval($id);
            $this->beginTransaction();

            // Comprobar que exista
            $company = $this->getCompanyById($id);
            if (!$company) {
                throw new Exception("El comercio con ID $id no existe");
            }

            // Guardar información para auditoría
            $companyName = $company['nombre'] ?? '';

            // Borrado en cascada con orden específico
            $tables = [
                'pertenecen' => 'Id_Comercio',
                'direccion' => 'Id_comercio',
                'horario' => 'Id_comercio',
                'contactos' => 'Id_Comercio',
                'fotos' => 'Id_Comercio',
                'paginaweb' => 'Id_comercio'
            ];

            foreach ($tables as $table => $fieldName) {
                $this->execute("DELETE FROM $table WHERE $fieldName = ?", [$id]);
            }

            // Eliminar el comercio
            $this->execute("DELETE FROM comercios WHERE ID = ?", [$id]);

            // Registrar en auditoría
            $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 1;

            $this->query(
                "INSERT INTO auditoria (
                    Tabla_Afectada, Accion, Fecha_Hora, Valor_Anterior, 
                    Valor_Actualizado, Id_usuario
                ) VALUES (?, ?, NOW(), ?, ?, ?)",
                ['comercios', 'DELETE', 'Comercio: ' . $companyName . ' (ID: ' . $id . ')', 'Eliminado', $userId]
            );

            $this->commit();
            return true;

        } catch (Exception $e) {
            $this->rollback();
            error_log("Error al eliminar comercio ID $id: " . $e->getMessage());
            return false;
        }
    }
}