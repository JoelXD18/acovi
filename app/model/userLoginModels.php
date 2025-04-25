<?php
require_once __DIR__ . '/../../config/database.php';

class UsuarioLoginModel
{
    private $db;

    public function __construct()
    {
        // Crear una instancia de la clase Database
        $database = new Database();
        // Obtener la conexión PDO
        $this->db = $database->getConnection();
    }

    public function obtenerUsuarioPorEmail($email)
    {
        // El resto del código permanece igual
        error_log("Buscando usuario con correo: " . $email);

        try {
            $query = "SELECT ID, Nombre, Apellidos, Nombre_Usuario, correo, contrasena, permisos, Id_Login 
                     FROM usuario 
                     WHERE correo = :email 
                     LIMIT 1";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                error_log(" No se encontró el usuario en la BD.");
            } else {
                error_log("✅ Usuario encontrado con ID: " . $usuario['ID']);
            }

            return $usuario;
        } catch (PDOException $e) {
            error_log("Error en la consulta de usuario: " . $e->getMessage());
            return false;
        }
    }

    public function insertarRegistroLogin($correo)
    {
        try {
            $fechaActual = date('Y-m-d H:i:s');
            $id_ayuntamiento = 1; // Valor por defecto si es necesario

            $query = "INSERT INTO login (Correo, Fecha_Hora_Acceso, Id_ayuntamiento) 
                      VALUES (:correo, :fecha, :id_ayuntamiento)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":correo", $correo, PDO::PARAM_STR);
            $stmt->bindParam(":fecha", $fechaActual, PDO::PARAM_STR);
            $stmt->bindParam(":id_ayuntamiento", $id_ayuntamiento, PDO::PARAM_INT);

            $resultado = $stmt->execute();

            if ($resultado) {
                $lastId = $this->db->lastInsertId();
                error_log("Registro de login insertado correctamente. ID: " . $lastId);
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log(" Error al insertar el registro de login: " . implode(", ", $errorInfo));
                return "Error al insertar registro de login: " . implode(", ", $errorInfo);
            }
        } catch (PDOException $e) {
            error_log("Excepción al insertar registro de login: " . $e->getMessage());
            return "Excepción al insertar registro de login: " . $e->getMessage();
        }
    }
}
?>