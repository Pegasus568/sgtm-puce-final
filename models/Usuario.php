<?php
// models/Usuario.php
require_once 'config/database.php';

class Usuario {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    // --- AUTENTICACIÓN ---
    public function buscarPorCorreo($correo) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE correo = ? AND deleted_at IS NULL");
        $stmt->execute([$correo]);
        return $stmt->fetch();
    }

    public function registrarUltimoLogin($id) {
        $this->pdo->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?")->execute([$id]);
    }

    public function obtenerPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // --- ADMINISTRACIÓN ---
    
    public function obtenerTodos() {
        $sql = "SELECT u.*, c.nombre as carrera 
                FROM usuarios u 
                LEFT JOIN carreras c ON u.carrera_id = c.id 
                WHERE u.deleted_at IS NULL 
                ORDER BY u.rol ASC, u.nombre ASC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function crear($datos) {
        // 1. VALIDACIÓN DE CORREO (@pucesa.edu.ec)
        if (!preg_match('/^[a-zA-Z0-9._%+-]+@pucesa\.edu\.ec$/', $datos['correo'])) {
            return "Error: El correo debe ser institucional (@pucesa.edu.ec).";
        }

        // 2. VALIDACIÓN DE CÉDULA (10 dígitos exactos)
        if (!preg_match('/^[0-9]{10}$/', $datos['cedula'])) {
            return "Error: La cédula debe tener exactamente 10 dígitos numéricos.";
        }

        // 3. VALIDACIÓN DE TELÉFONO (10 dígitos exactos)
        if (!empty($datos['telefono']) && !preg_match('/^[0-9]{10}$/', $datos['telefono'])) {
            return "Error: El teléfono debe tener 10 dígitos.";
        }

        // 4. VALIDACIÓN DE DUPLICADOS
        $stmtCheck = $this->pdo->prepare("SELECT id FROM usuarios WHERE correo = ? OR cedula = ?");
        $stmtCheck->execute([$datos['correo'], $datos['cedula']]);
        if ($stmtCheck->fetch()) {
            return "Error: El correo o la cédula ya están registrados en el sistema.";
        }

        // INSERCIÓN
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
                $datos['carrera_id'] ?: null,
                $datos['semestre'] ?: null
            ]);
            return true;
        } catch (PDOException $e) {
            return "Error BD: " . $e->getMessage();
        }
    }

    public function eliminar($id) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET deleted_at = NOW(), estado = 'INACTIVO' WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>