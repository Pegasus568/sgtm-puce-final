<?php
// models/TipoTutoria.php
require_once 'config/database.php';

class TipoTutoria {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    // Obtener todos para la lista de administración
    public function obtenerTodos() {
        return $this->pdo->query("SELECT * FROM tipos_tutorias ORDER BY nombre ASC")->fetchAll();
    }

    // Obtener solo activos (Para el select del estudiante)
    public function obtenerActivos() {
        return $this->pdo->query("SELECT * FROM tipos_tutorias WHERE activo = 1 ORDER BY nombre ASC")->fetchAll();
    }

    public function crear($nombre, $color) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO tipos_tutorias (nombre, color_etiqueta, activo) VALUES (?, ?, 1)");
            return $stmt->execute([trim($nombre), $color]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Cambiar estado (Activar/Desactivar en lugar de borrar para no romper historial)
    public function cambiarEstado($id, $estado) {
        $stmt = $this->pdo->prepare("UPDATE tipos_tutorias SET activo = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }
    
    // Editar (Nombre y Color)
    public function actualizar($id, $nombre, $color) {
        $stmt = $this->pdo->prepare("UPDATE tipos_tutorias SET nombre = ?, color_etiqueta = ? WHERE id = ?");
        return $stmt->execute([trim($nombre), $color, $id]);
    }
}
?>