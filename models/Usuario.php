<?php
// models/Usuario.php
require_once 'config/database.php';

class Usuario {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    // --- AUTENTICACIÓN (Ya existía) ---
    public function buscarPorCorreo($correo) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE correo = ? AND deleted_at IS NULL");
        $stmt->execute([$correo]);
        return $stmt->fetch();
    }

    public function registrarUltimoLogin($id) {
        $this->pdo->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?")->execute([$id]);
    }

    // --- ADMINISTRACIÓN (Nuevo) ---
    
    // Listar todos los usuarios activos
    public function obtenerTodos() {
        $sql = "SELECT u.*, c.nombre as carrera 
                FROM usuarios u 
                LEFT JOIN carreras c ON u.carrera_id = c.id 
                WHERE u.deleted_at IS NULL 
                ORDER BY u.rol ASC, u.nombre ASC";
        return $this->pdo->query($sql)->fetchAll();
    }

    // Crear Usuario
    public function crear($datos) {
        // Validar correo duplicado
        if ($this->buscarPorCorreo($datos['correo'])) {
            return "El correo ya está registrado.";
        }

        $sql = "INSERT INTO usuarios (nombre, correo, password_hash, rol, cedula, telefono, carrera_id, semestre_actual, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVO')";
        
        $hash = password_hash($datos['password'], PASSWORD_DEFAULT);
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $datos['nombre'], 
                $datos['correo'], 
                $hash, 
                $datos['rol'], 
                $datos['cedula'], 
                $datos['telefono'],
                $datos['carrera_id'] ?: null, // Si es nulo, guardar NULL
                $datos['semestre'] ?: null
            ]);
            return true;
        } catch (PDOException $e) {
            return "Error BD: " . $e->getMessage();
        }
    }

    // Eliminar Usuario (Soft Delete)
    public function eliminar($id) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET deleted_at = NOW(), estado = 'INACTIVO' WHERE id = ?");
        return $stmt->execute([$id]);
    }
    public function obtenerPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>