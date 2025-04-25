<?php
class UserModel {
    private static function conectar() {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=glitc_ayuntamiento_villanueva;charset=utf8", "TU_USUARIO", "TU_CONTRASEÑA");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("❌ Error de conexión en UserModel: " . $e->getMessage());
        }
    }
 
    public static function guardarUsuario($datos) {
        $pdo = self::conectar();
        $sql = "INSERT INTO usuario (nombre, apellidos, correo, nombre_usuario, contrasena, permisos) 
                VALUES (:nombre, :apellidos, :correo, :nombre_usuario, :contrasena, :permisos)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'nombre' => $datos['nombre'],
            'apellidos' => $datos['apellidos'],
            'correo' => $datos['email'],
            'nombre_usuario' => $datos['username'],
            'contrasena' => $datos['password'],
            'permisos' => $datos['rol']
        ]);
    }
 
    public static function usuarioExiste($username, $email) {
        $pdo = self::conectar();
        $sql = "SELECT COUNT(*) FROM usuario WHERE nombre_usuario = :username OR correo = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $username, 'email' => $email]);
        return $stmt->fetchColumn() > 0;
    }
 
    public static function usuarioDuplicadoAlActualizar($id, $username, $email) {
        $pdo = self::conectar();
        $sql = "SELECT COUNT(*) FROM usuario 
                WHERE (nombre_usuario = :username OR correo = :email) AND id != :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $username, 'email' => $email, 'id' => $id]);
        return $stmt->fetchColumn() > 0;
    }
 
    public static function actualizarUsuario($id, $datos) {
        $pdo = self::conectar();
 
        $sql = "UPDATE usuario SET 
                nombre = :nombre, 
                apellidos = :apellidos, 
                correo = :correo, 
                nombre_usuario = :nombre_usuario, 
                permisos = :permisos";
 
        if (!empty($datos['password'])) {
            $sql .= ", contrasena = :contrasena";
        }
 
        $sql .= " WHERE id = :id";
 
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':apellidos', $datos['apellidos']);
        $stmt->bindParam(':correo', $datos['email']);
        $stmt->bindParam(':nombre_usuario', $datos['username']);
        $stmt->bindParam(':permisos', $datos['rol']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
 
        if (!empty($datos['password'])) {
            $stmt->bindParam(':contrasena', $datos['password']);
        }
 
        return $stmt->execute();
    }
 
    public static function obtenerUsuarios() {
        $pdo = self::conectar();
        $stmt = $pdo->query("SELECT * FROM usuario");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
 
    public static function obtenerUsuarioPorId($id) {
        $pdo = self::conectar();
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}