<?php
// models/Estadistica.php
require_once 'config/database.php';

class Estadistica {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    // 1. Conteo total por Estado (Para gráfico de Pastel)
    public function obtenerPorEstado() {
        $sql = "SELECT estado, COUNT(*) as total FROM tutorias GROUP BY estado";
        return $this->pdo->query($sql)->fetchAll();
    }

    // 2. Conteo por Carrera (Para gráfico de Barras)
    public function obtenerPorCarrera() {
        $sql = "SELECT c.nombre as carrera, COUNT(t.id) as total 
                FROM tutorias t
                JOIN usuarios u ON t.estudiante_id = u.id
                JOIN carreras c ON u.carrera_id = c.id
                GROUP BY c.nombre
                ORDER BY total DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    // 3. Top 5 Docentes más solicitados (Tabla)
    public function obtenerTopDocentes() {
        $sql = "SELECT u.nombre, c.nombre as carrera, COUNT(t.id) as total
                FROM tutorias t
                JOIN usuarios u ON t.tutor_id = u.id
                LEFT JOIN carreras c ON u.carrera_id = c.id
                GROUP BY u.id
                ORDER BY total DESC
                LIMIT 5";
        return $this->pdo->query($sql)->fetchAll();
    }

    // 4. Totales Generales (Tarjetas)
    public function obtenerTotales() {
        return [
            'total_citas' => $this->pdo->query("SELECT COUNT(*) FROM tutorias")->fetchColumn(),
            'total_usuarios' => $this->pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn(),
            'tasa_cancelacion' => 0 // Se calcula en el controlador
        ];
    }
}
?>