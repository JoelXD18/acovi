<?php
/**
 * Clase de conexión a la base de datos
 */
class Database
{
    private static $instance = null;
    private $host = 'localhost';
    private $dbname = 'glitc_ayuntamiento_villanueva';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function __construct()
    {
        try {
            $this->conn = new PDO(
                "mysql:host=$this->host;dbname=$this->dbname;charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Error de conexión a la BD: " . $e->getMessage());
            die("Error de conexión: No fue posible conectar a la base de datos en este momento.");
        }
    }

    // Método para implementación singleton (opcional)
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Método para obtener la conexión PDO directamente
    public function getConnection()
    {
        return $this->conn;
    }

    // Método estático para conectar (usado por el código antiguo)
    public static function conectar()
    {
        $instance = self::getInstance();
        return $instance->getConnection();
    }

    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error SQL: " . $e->getMessage() . " | Query: " . $sql);
            return false;
        }
    }

    public function insert($sql, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error SQL en INSERT: " . $e->getMessage() . " | Query: " . $sql);
            return false;
        }
    }

    public function execute($sql, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error SQL en EXECUTE: " . $e->getMessage() . " | Query: " . $sql);
            return false;
        }
    }

    public function beginTransaction()
    {
        return $this->conn->beginTransaction();
    }

    public function commit()
    {
        return $this->conn->commit();
    }

    public function rollback()
    {
        return $this->conn->rollBack();
    }
}
?>