<?php
// models/Tutoria.php
require_once 'config/database.php';

class Tutoria {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    // --- ADMIN ---
    public function obtenerTodas() {
        $sql = "SELECT t.*, 
                       est.nombre AS nombre_estudiante, 
                       doc.nombre AS nombre_docente,
                       tipo.nombre AS nombre_tipo,
                       tipo.color_etiqueta,
                       mat.nombre AS nombre_materia
                FROM tutorias t
                INNER JOIN usuarios est ON t.estudiante_id = est.id
                INNER JOIN usuarios doc ON t.tutor_id = doc.id
                INNER JOIN tipos_tutorias tipo ON t.tipo_id = tipo.id
                LEFT JOIN materias mat ON t.materia_id = mat.id
                ORDER BY t.fecha DESC, t.hora_inicio DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function eliminarDefinitivamente($id) {
        $stmt = $this->pdo->prepare("DELETE FROM tutorias WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- GENERAL (Estudiante/Docente) ---
    public function obtenerPorUsuario($id, $rol) {
        $campo = ($rol === 'DOCENTE') ? 'tutor_id' : 'estudiante_id';
        $joinContraparte = ($rol === 'DOCENTE') ? 'estudiante_id' : 'tutor_id';

        $sql = "SELECT t.*, 
                       u.nombre as contraparte, 
                       tt.nombre as tipo, 
                       tt.color_etiqueta
                FROM tutorias t
                JOIN usuarios u ON t.$joinContraparte = u.id
                JOIN tipos_tutorias tt ON t.tipo_id = tt.id
                WHERE t.$campo = ? 
                ORDER BY t.fecha DESC, t.hora_inicio DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    // --- DETALLE COMPLETO (Para Modal) ---
    public function obtenerDetalle($id) {
        $sql = "SELECT t.*, 
                       est.nombre AS est_nombre, est.correo AS est_correo, est.cedula AS est_cedula,
                       est.telefono AS est_telefono,
                       doc.nombre AS doc_nombre, doc.correo AS doc_correo,
                       tt.nombre AS tipo_nombre,
                       c.nombre AS carrera_nombre
                FROM tutorias t
                LEFT JOIN usuarios est ON t.estudiante_id = est.id
                LEFT JOIN usuarios doc ON t.tutor_id = doc.id
                LEFT JOIN tipos_tutorias tt ON t.tipo_id = tt.id
                LEFT JOIN carreras c ON est.carrera_id = c.id
                WHERE t.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- VALIDACIONES ---
    public function verificarCruce($tutor_id, $fecha, $inicio, $fin) {
        $sql = "SELECT COUNT(*) FROM tutorias 
                WHERE tutor_id = ? AND fecha = ? 
                AND estado IN ('PENDIENTE', 'CONFIRMADA', 'PROGRAMADA')
                AND (
                    (hora_inicio < ? AND hora_fin > ?) OR 
                    (hora_inicio >= ? AND hora_inicio < ?)
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tutor_id, $fecha, $fin, $inicio, $inicio, $fin]);
        return $stmt->fetchColumn() > 0;
    }

    public function contarActivasEstudiante($estudiante_id) {
        $sql = "SELECT COUNT(*) FROM tutorias WHERE estudiante_id = ? AND estado IN ('PENDIENTE', 'CONFIRMADA')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$estudiante_id]);
        return $stmt->fetchColumn();
    }

    // --- CREAR ---
    public function crear($datos) {
        $codigo = 'TR-' . date('Y') . '-' . substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4);
        $sql = "INSERT INTO tutorias 
                (codigo_reserva, solicitado_por, tutor_id, estudiante_id, tipo_id, materia_id, tema, fecha, hora_inicio, hora_fin, modalidad, lugar, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $codigo,
                $datos['solicitado_por'],
                $datos['tutor_id'],
                $datos['estudiante_id'],
                $datos['tipo_id'],
                $datos['materia_id'] ?? null,
                $datos['tema'],
                $datos['fecha'],
                $datos['hora_inicio'],
                $datos['hora_fin'],
                $datos['modalidad'],
                $datos['lugar'] ?? null,
                $datos['estado']
            ]);
            return true;
        } catch (PDOException $e) {
            return "Error BD: " . $e->getMessage();
        }
    }

    // --- GESTIÓN ---
    public function responderSolicitud($id, $docente, $accion, $texto, $lugar) {
        $estado = ($accion === 'confirmar') ? 'CONFIRMADA' : 'RECHAZADA';
        $motivo = ($accion === 'rechazar') ? $texto : null;
        $sql = "UPDATE tutorias SET estado=?, motivo_rechazo=?, lugar=COALESCE(?, lugar) WHERE id=? AND tutor_id=?";
        return $this->pdo->prepare($sql)->execute([$estado, $motivo, $lugar, $id, $docente]);
    }

    public function registrarAsistencia($id, $docente, $asistio, $obs) {
        $estado = ($asistio == 1) ? 'REALIZADA' : 'NO_ASISTIO';
        // Asumiendo que 'observaciones' se guarda en la tabla tutorias. Si no tienes esa columna, borra esa parte.
        $sql = "UPDATE tutorias SET estado=?, asistio=?, observaciones=? WHERE id=? AND tutor_id=?";
        return $this->pdo->prepare($sql)->execute([$estado, $asistio, $obs, $id, $docente]);
    }

    // [MODIFICADO] Ahora recibe el motivo
    public function cancelar($id, $solicitante_id, $motivo) {
        $sql = "UPDATE tutorias SET estado='CANCELADA', motivo_rechazo=? WHERE id=? AND solicitado_por=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$motivo, $id, $solicitante_id]);
    }
}
?>