<?php
// controllers/TutoriasController.php

require_once 'models/Tutoria.php';
require_once 'models/Usuario.php';
require_once 'models/TipoTutoria.php';
require_once 'models/Horario.php';

class TutoriasController {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "auth/index");
            exit;
        }
    }

    public function index() {
        // Redirección inteligente según rol
        if ($_SESSION['usuario_rol'] == 'ESTUDIANTE') {
            header("Location: " . BASE_URL . "tutorias/mis_solicitudes");
        } elseif ($_SESSION['usuario_rol'] == 'DOCENTE') {
            header("Location: " . BASE_URL . "tutorias/agenda");
        } else {
            header("Location: " . BASE_URL . "admintutorias/index");
        }
        exit;
    }

    // --- VISTA: SOLICITAR TUTORÍA ---
    public function solicitar() {
        $userModel = new Usuario();
        $tipoModel = new TipoTutoria();
        $rol = $_SESSION['usuario_rol'];
        
        $todos = $userModel->obtenerTodos();
        $usuarios_destino = [];

        if ($rol === 'DOCENTE') {
            // Docentes buscan Estudiantes
            $usuarios_destino = array_filter($todos, function($u) { return $u['rol'] === 'ESTUDIANTE' && $u['estado'] === 'ACTIVO'; });
        } else {
            // Estudiantes buscan Docentes
            $usuarios_destino = array_filter($todos, function($u) { return $u['rol'] === 'DOCENTE' && $u['estado'] === 'ACTIVO'; });
        }
        
        $tipos = $tipoModel->obtenerActivos();
        
        require_once 'views/layouts/header.php';
        require_once 'views/layouts/sidebar.php';
        require_once 'views/tutorias/solicitar.php';
        require_once 'views/layouts/footer.php';
    }

    // --- ACCIÓN: GUARDAR SOLICITUD ---
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modelo = new Tutoria();
            $rol = $_SESSION['usuario_rol'];
            
            $fecha = $_POST['fecha'];
            $hora_ini = $_POST['hora_inicio'];
            $duracion = $_POST['duracion'] ?? 30;
            $hora_fin = date('H:i', strtotime("$hora_ini + $duracion minutes"));

            // Validaciones
            if ($rol === 'DOCENTE') {
                $estudiantes_ids = $_POST['estudiantes_ids'] ?? [];
                if(empty($estudiantes_ids)) {
                    $_SESSION['error'] = "Debe seleccionar al menos un estudiante.";
                    header("Location: " . BASE_URL . "tutorias/solicitar"); exit;
                }
                // Docente no puede tener cruce consigo mismo
                if ($modelo->verificarCruce($_SESSION['usuario_id'], $fecha, $hora_ini, $hora_fin)) {
                    $_SESSION['error'] = "Ya tienes una cita programada en ese horario.";
                    header("Location: " . BASE_URL . "tutorias/solicitar"); exit;
                }

                $count = 0;
                foreach ($estudiantes_ids as $id_est) {
                    $datos = [
                        'solicitado_por' => $_SESSION['usuario_id'],
                        'tutor_id' => $_SESSION['usuario_id'],
                        'estudiante_id' => $id_est,
                        'tipo_id' => $_POST['tipo_id'],
                        'tema' => $_POST['tema'],
                        'fecha' => $fecha,
                        'hora_inicio' => $hora_ini,
                        'hora_fin' => $hora_fin,
                        'modalidad' => $_POST['modalidad'],
                        'lugar' => $_POST['lugar'],
                        'estado' => 'CONFIRMADA' // Docente se auto-aprueba
                    ];
                    if($modelo->crear($datos)) {
                        $count++;
                        $this->enviarCorreo($id_est, $_SESSION['usuario_id'], $datos, 'cita_creada_por_docente');
                    }
                }
                $_SESSION['mensaje'] = "Se agendaron $count estudiantes correctamente.";
                header("Location: " . BASE_URL . "tutorias/agenda");
                exit;

            } else {
                // ESTUDIANTE SOLICITA
                $tutor_id = $_POST['tutor_id'];
                
                // Regla 24h
                if (strtotime("$fecha $hora_ini") < (time() + 86400)) {
                    $_SESSION['error'] = "Debe reservar con al menos 24 horas de anticipación.";
                    header("Location: " . BASE_URL . "tutorias/solicitar"); exit;
                }
                // Regla Equidad (Máx 2 pendientes)
                if ($modelo->contarActivasEstudiante($_SESSION['usuario_id']) >= 2) {
                    $_SESSION['error'] = "Has alcanzado el límite de reservas activas.";
                    header("Location: " . BASE_URL . "tutorias/solicitar"); exit;
                }
                // Regla Cruce Docente
                if ($modelo->verificarCruce($tutor_id, $fecha, $hora_ini, $hora_fin)) {
                    $_SESSION['error'] = "El docente ya tiene una cita en ese horario.";
                    header("Location: " . BASE_URL . "tutorias/solicitar"); exit;
                }

                $datos = [
                    'solicitado_por' => $_SESSION['usuario_id'],
                    'tutor_id' => $tutor_id,
                    'estudiante_id' => $_SESSION['usuario_id'],
                    'tipo_id' => $_POST['tipo_id'],
                    'tema' => $_POST['tema'],
                    'fecha' => $fecha,
                    'hora_inicio' => $hora_ini,
                    'hora_fin' => $hora_fin,
                    'modalidad' => $_POST['modalidad'],
                    'estado' => 'PENDIENTE'
                ];

                if ($modelo->crear($datos)) {
                    $this->enviarCorreo($tutor_id, $_SESSION['usuario_id'], $datos, 'solicitud_estudiante');
                    $_SESSION['mensaje'] = "Solicitud enviada correctamente. Espera confirmación.";
                    header("Location: " . BASE_URL . "tutorias/mis_solicitudes");
                } else {
                    $_SESSION['error'] = "Error al guardar la solicitud.";
                    header("Location: " . BASE_URL . "tutorias/solicitar");
                }
                exit;
            }
        }
    }

    // --- API JSON PARA CALENDARIO ---
    public function apiGetHorarios() {
        header('Content-Type: application/json');
        $docente = $_GET['docente_id'] ?? null;
        $fecha = $_GET['fecha'] ?? null;
        
        if (!$docente || !$fecha) { echo json_encode([]); exit; }
        
        $db = Database::getConnection();
        $dia_semana = date('N', strtotime($fecha)); // 1=Lunes, 7=Domingo
        
        // 1. Obtener horario configurado por el docente
        $sql = "SELECT * FROM horarios_docentes WHERE docente_id = ? AND dia_semana = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$docente, $dia_semana]);
        $bloques = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 2. Obtener citas ocupadas ese día
        $sqlOc = "SELECT hora_inicio, hora_fin FROM tutorias 
                  WHERE tutor_id = ? AND fecha = ? AND estado NOT IN ('CANCELADA','RECHAZADA')";
        $stmtOc = $db->prepare($sqlOc);
        $stmtOc->execute([$docente, $fecha]);
        $ocupados = $stmtOc->fetchAll(PDO::FETCH_ASSOC);
        
        $disponibles = [];

        foreach ($bloques as $b) {
            $inicio = strtotime($fecha . ' ' . $b['hora_inicio']);
            $fin = strtotime($fecha . ' ' . $b['hora_fin']);
            
            while ($inicio < $fin) {
                $slot_fin = $inicio + 1800; // Bloques de 30 mins (1800 seg)
                if ($slot_fin > $fin) break;
                
                $libre = true;
                foreach ($ocupados as $oc) {
                    $o_ini = strtotime($fecha . ' ' . $oc['hora_inicio']);
                    $o_fin = strtotime($fecha . ' ' . $oc['hora_fin']);
                    
                    // Si el slot se solapa con una cita ocupada
                    if (($inicio < $o_fin) && ($slot_fin > $o_ini)) {
                        $libre = false;
                        break;
                    }
                }
                
                if ($libre) {
                    $disponibles[] = date('H:i', $inicio);
                }
                $inicio = $slot_fin;
            }
        }
        echo json_encode($disponibles);
    }

    public function apiGetDetalle() {
        header('Content-Type: application/json');
        if (!isset($_GET['id'])) { echo json_encode(['error'=>'Falta ID']); exit; }
        
        $modelo = new Tutoria();
        $d = $modelo->obtenerDetalle($_GET['id']);
        
        if(!$d) { echo json_encode(['error'=>'No encontrada']); exit; }
        
        // Seguridad: solo ver si es tuyo
        $rol = $_SESSION['usuario_rol'];
        $uid = $_SESSION['usuario_id'];
        if ($rol != 'ADMIN' && !($rol == 'DOCENTE' && $d['tutor_id'] == $uid) && !($rol == 'ESTUDIANTE' && $d['estudiante_id'] == $uid)) {
            echo json_encode(['error'=>'Acceso denegado']); exit;
        }
        echo json_encode($d);
    }

    // --- VISTA: MIS SOLICITUDES (ESTUDIANTE) ---
    public function mis_solicitudes() {
        if ($_SESSION['usuario_rol'] !== 'ESTUDIANTE') header("Location: " . BASE_URL);
        
        $tutoria = new Tutoria();
        $citas = $tutoria->obtenerPorUsuario($_SESSION['usuario_id'], 'ESTUDIANTE');
        
        require_once 'views/layouts/header.php';
        require_once 'views/layouts/sidebar.php';
        require_once 'views/tutorias/lista_estudiante.php';
        require_once 'views/layouts/footer.php';
    }

    // --- ACCIÓN: CANCELAR (ESTUDIANTE) - ACTUALIZADO CON MOTIVO ---
    public function cancelar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_tutoria'];
            $motivo = trim($_POST['motivo_cancelacion'] ?? '');

            // VALIDACIÓN DE BACKEND
            if (empty($motivo)) {
                $_SESSION['error'] = "Error: Debes escribir una razón para cancelar.";
                header("Location: " . BASE_URL . "tutorias/mis_solicitudes");
                exit;
            }

            $modelo = new Tutoria();
            // Lógica para cancelar
            if($modelo->cancelar($id, $_SESSION['usuario_id'], $motivo)) {
                $_SESSION['mensaje'] = "Tutoría cancelada correctamente.";
            } else {
                $_SESSION['error'] = "No se pudo cancelar.";
            }
            header("Location: " . BASE_URL . "tutorias/mis_solicitudes");
        }
    }

    // --- VISTA: AGENDA (DOCENTE) ---
    public function agenda() {
        if ($_SESSION['usuario_rol'] !== 'DOCENTE') header("Location: " . BASE_URL);
        
        $modelo = new Tutoria();
        $todas = $modelo->obtenerPorUsuario($_SESSION['usuario_id'], 'DOCENTE');
        
        $pendientes = [];
        $agenda = [];
        
        foreach ($todas as $t) {
            if ($t['estado'] == 'PENDIENTE') {
                $pendientes[] = $t;
            } elseif (in_array($t['estado'], ['CONFIRMADA','PROGRAMADA','REALIZADA','NO_ASISTIO'])) {
                $agenda[] = $t;
            }
        }
        
        require_once 'views/layouts/header.php';
        require_once 'views/layouts/sidebar.php';
        require_once 'views/tutorias/agenda_docente.php';
        require_once 'views/layouts/footer.php';
    }

    // --- ACCIÓN: RESPONDER (DOCENTE) - ACTUALIZADO CON MOTIVO ---
    public function responder() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_tutoria'];
            $accion = $_POST['accion'];
            $motivo = trim($_POST['motivo'] ?? '');
            $lugar = $_POST['lugar'] ?? null;

            // VALIDACIÓN DE BACKEND
            if ($accion === 'rechazar' && empty($motivo)) {
                $_SESSION['error'] = "Error: El motivo es obligatorio para rechazar.";
                header("Location: " . BASE_URL . "tutorias/agenda");
                exit;
            }

            $modelo = new Tutoria();
            $res = $modelo->responderSolicitud($id, $_SESSION['usuario_id'], $accion, $motivo, $lugar);
            
            if($res) $_SESSION['mensaje'] = "Solicitud procesada correctamente.";
            header("Location: " . BASE_URL . "tutorias/agenda");
        }
    }

    // --- ACCIÓN: REGISTRAR ASISTENCIA ---
    public function asistencia() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modelo = new Tutoria();
            $modelo->registrarAsistencia(
                $_POST['id_tutoria'], 
                $_SESSION['usuario_id'], 
                $_POST['asistio'], 
                $_POST['observaciones']
            );
            $_SESSION['mensaje'] = "Asistencia registrada.";
            header("Location: " . BASE_URL . "tutorias/agenda");
        }
    }

    // --- NOTIFICACIONES EMAIL ---
    private function enviarCorreo($dest_id, $remit_id, $datos, $tipo) {
        if(file_exists('config/Mailer.php')) {
            require_once 'config/Mailer.php';
            $u = new Usuario();
            $dest = $u->obtenerPorId($dest_id);
            $remit = $u->obtenerPorId($remit_id);
            
            $asunto = ($tipo=='cita_creada_por_docente') ? "Nueva Cita Agendada" : "Nueva Solicitud de Tutoría";
            $msg = "<p>Hola ".$dest['nombre'].",</p>";
            $msg .= "<p>El usuario <b>".$remit['nombre']."</b> ha generado una actividad en el sistema SGTM.</p>";
            $msg .= "<ul><li>Fecha: ".$datos['fecha']."</li><li>Hora: ".$datos['hora_inicio']."</li></ul>";
            
            Mailer::enviar($dest['correo'], $dest['nombre'], $asunto, $msg);
        }
    }
}
?>