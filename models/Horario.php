<?php
// models/Horario.php
require_once 'config/database.php';

class Horario {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    // Listar horarios de un docente ordenados por día y hora
    public function obtenerPorDocente($docente_id) {
        $sql = "SELECT * FROM horarios_docentes WHERE docente_id = ? ORDER BY dia_semana ASC, hora_inicio ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$docente_id]);
        return $stmt->fetchAll();
    }

    // Agregar nuevo bloque con validaciones estrictas
    public function agregar($docente_id, $dia, $inicio, $fin, $lugar) {
        // 1. Validación de tiempo lógico
        if ($inicio >= $fin) {
            return "Error: La hora de inicio debe ser antes que la hora de fin.";
        }

        // 2. Validación Anti-Colisión (Solapamiento)
        // Busca si existe algún horario para ese docente en ese día que se cruce con el nuevo
        $sql = "SELECT COUNT(*) FROM horarios_docentes 
                WHERE docente_id = ? AND dia_semana = ? 
                AND (
                    (hora_inicio < ? AND hora_fin > ?) OR  -- El nuevo empieza antes de que termine el existente y termina después de que empiece
                    (hora_inicio >= ? AND hora_inicio < ?) -- O empieza justo en medio
                )";
        
        $stmt = $this->pdo->prepare($sql);
        // Parámetros: id, dia, fin_nuevo, inicio_nuevo, inicio_nuevo, fin_nuevo
        $stmt->execute([$docente_id, $dia, $fin, $inicio, $inicio, $fin]);
        
        if ($stmt->fetchColumn() > 0) {
            return "Error: Ya tienes un horario configurado que choca con este intervalo.";
        }

        // 3. Insertar si todo está limpio
        try {
            $sqlInsert = "INSERT INTO horarios_docentes (docente_id, dia_semana, hora_inicio, hora_fin, ubicacion_default) VALUES (?, ?, ?, ?, ?)";
            $this->pdo->prepare($sqlInsert)->execute([$docente_id, $dia, $inicio, $fin, $lugar]);
            return true;
        } catch (PDOException $e) {
            return "Error en BD: " . $e->getMessage();
        }
    }

    // Eliminar bloque
    public function eliminar($id, $docente_id) {
        // Validamos docente_id para asegurar que nadie borre horarios de otro
        $stmt = $this->pdo->prepare("DELETE FROM horarios_docentes WHERE id = ? AND docente_id = ?");
        return $stmt->execute([$id, $docente_id]);
    }
}
?>