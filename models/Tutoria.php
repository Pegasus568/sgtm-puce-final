<?php
// models/Tutoria.php
require_once 'config/database.php';

class Tutoria {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    // ====================================================================
    // SECCIÓN 1: ADMINISTRADOR (Historial y Control)
    // ====================================================================

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

    // ====================================================================
    // SECCIÓN 2: ESTUDIANTE Y VISUALIZACIÓN (Reservas)
    // ====================================================================

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

    // Validación Anti-Colisión
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

    // Límite de carga académica
    public function contarActivasEstudiante($estudiante_id) {
        $sql = "SELECT COUNT(*) FROM tutorias WHERE estudiante_id = ? AND estado IN ('PENDIENTE', 'CONFIRMADA')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$estudiante_id]);
        return $stmt->fetchColumn();
    }

    // Crear solicitud
    public function crear($datos) {
        $codigo = 'TR-' . date('Y') . '-' . substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4);

        $sql = "INSERT INTO tutorias 
                (codigo_reserva, solicitado_por, tutor_id, estudiante_id, tipo_id, materia_id, tema, fecha, hora_inicio, hora_fin, modalidad, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
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
                $datos['estado']
            ]);
            return true;
        } catch (PDOException $e) {
            return "Error BD: " . $e->getMessage();
        }
    }

    // ====================================================================
    // SECCIÓN 3: DOCENTE (Gestión y Asistencia)
    // ====================================================================

    // Aceptar o Rechazar solicitud
    public function responderSolicitud($id_tutoria, $docente_id, $accion, $texto, $lugar = null) {
        $nuevo_estado = ($accion === 'confirmar') ? 'CONFIRMADA' : 'RECHAZADA';
        $motivo = ($accion === 'rechazar') ? $texto : null;
        
        $sql = "UPDATE tutorias SET 
                estado = ?, 
                motivo_rechazo = ?, 
                lugar = COALESCE(?, lugar)
                WHERE id = ? AND tutor_id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$nuevo_estado, $motivo, $lugar, $id_tutoria, $docente_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Registrar Asistencia y Cerrar
    public function registrarAsistencia($id, $docente_id, $asistio, $observaciones) {
        $nuevo_estado = ($asistio == 1) ? 'REALIZADA' : 'NO_ASISTIO';
        
        try {
            $this->pdo->beginTransaction();

            // 1. Actualizar estado de la cita
            $sqlCita = "UPDATE tutorias SET estado = ?, asistio = ? WHERE id = ? AND tutor_id = ?";
            $stmt = $this->pdo->prepare($sqlCita);
            $stmt->execute([$nuevo_estado, $asistio, $id, $docente_id]);

            // 2. Guardar observación en reportes_sesion (si existe texto)
            if (!empty($observaciones)) {
                $sqlRep = "INSERT INTO reportes_sesion (tutoria_id, creado_por, observaciones) VALUES (?, ?, ?)";
                $stmtRep = $this->pdo->prepare($sqlRep);
                $stmtRep->execute([$id, $docente_id, $observaciones]);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}
?>