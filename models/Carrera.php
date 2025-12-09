<?php
// models/Carrera.php
require_once 'config/database.php';

class Carrera {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    // --- CARRERAS ---
    public function obtenerTodas() {
        return $this->pdo->query("SELECT * FROM carreras WHERE estado=1 ORDER BY nombre ASC")->fetchAll();
    }

    public function obtenerPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM carreras WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function crear($nombre, $codigo) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO carreras (nombre, codigo, estado) VALUES (?, ?, 1)");
            return $stmt->execute([trim($nombre), trim($codigo)]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminar($id) {
        // Borrado lógico
        $stmt = $this->pdo->prepare("UPDATE carreras SET estado=0 WHERE id=?");
        return $stmt->execute([$id]);
    }

    // --- MATERIAS ---
    public function obtenerMaterias($carrera_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM materias WHERE carrera_id = ? AND estado=1 ORDER BY semestre, nombre");
        $stmt->execute([$carrera_id]);
        return $stmt->fetchAll();
    }

    public function crearMateria($carrera_id, $nombre, $semestre) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO materias (carrera_id, nombre, semestre, estado) VALUES (?, ?, ?, 1)");
            return $stmt->execute([$carrera_id, trim($nombre), $semestre]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminarMateria($id) {
        $stmt = $this->pdo->prepare("UPDATE materias SET estado=0 WHERE id=?");
        return $stmt->execute([$id]);
    }
}
?>