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
            header("Location: " . BASE_URL . "auth/index"); exit;
        }
    }

    public function index() {
        if ($_SESSION['usuario_rol'] == 'ESTUDIANTE') header("Location: " . BASE_URL . "tutorias/mis_solicitudes");
        elseif ($_SESSION['usuario_rol'] == 'DOCENTE') header("Location: " . BASE_URL . "tutorias/agenda");
        else header("Location: " . BASE_URL . "admintutorias/index");
        exit;
    }

    // --- SOLICITUDES UNIFICADAS (Individual + Grupal) ---
    public function solicitar() {
        $userModel = new Usuario();
        $tipoModel = new TipoTutoria();
        $rol = $_SESSION['usuario_rol'];
        
        $usuarios_destino = [];
        // Si soy Docente, veo Estudiantes. Si soy Estudiante, veo Docentes.
        $todos = $userModel->obtenerTodos();
        
        if ($rol === 'DOCENTE') {
            $usuarios_destino = array_filter($todos, function($u) { return $u['rol'] === 'ESTUDIANTE' && $u['estado'] === 'ACTIVO'; });
        } else {
            $usuarios_destino = array_filter($todos, function($u) { return $u['rol'] === 'DOCENTE' && $u['estado'] === 'ACTIVO'; });
        }
        
        $tipos = $tipoModel->obtenerActivos();
        require_once 'views/layouts/header.php'; require_once 'views/layouts/sidebar.php';
        require_once 'views/tutorias/solicitar.php'; require_once 'views/layouts/footer.php';
    }

    // --- GUARDAR (Lógica mixta) ---
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modelo = new Tutoria();
            $rol = $_SESSION['usuario_rol'];
            
            $fecha = $_POST['fecha'];
            $hora_ini = $_POST['hora_inicio'];
            $duracion = $_POST['duracion'] ?? 30;
            $hora_fin = date('H:i', strtotime("$hora_ini + $duracion minutes"));

            // CASO DOCENTE (MULTIPLES CITAS CONFIRMADAS)
            if ($rol === 'DOCENTE') {
                $estudiantes_ids = $_POST['estudiantes_ids'] ?? [];
                if(empty($estudiantes_ids)) {
                    $_SESSION['error'] = "Seleccione estudiantes."; header("Location: " . BASE_URL . "tutorias/solicitar"); exit;
                }
                // Validar mi propia agenda
                if ($modelo->verificarCruce($_SESSION['usuario_id'], $fecha, $hora_ini, $hora_fin)) {
                    $_SESSION['error'] = "Ya tienes cita en ese horario."; header("Location: " . BASE_URL . "tutorias/solicitar"); exit;
                }

                $count = 0;
                foreach ($estudiantes_ids as $id_est) {
                    $datos = [
                        'solicitado_por' => $_SESSION['usuario_id'], 'tutor_id' => $_SESSION['usuario_id'],
                        'estudiante_id' => $id_est, 'tipo_id' => $_POST['tipo_id'], 'tema' => $_POST['tema'],
                        'fecha' => $fecha, 'hora_inicio' => $hora_ini, 'hora_fin' => $hora_fin,
                        'modalidad' => $_POST['modalidad'], 'lugar' => $_POST['lugar'], 'estado' => 'CONFIRMADA'
                    ];
                    if($modelo->crear($datos)) {
                        $count++;
                        $this->enviarCorreo($id_est, $_SESSION['usuario_id'], $datos, 'cita_creada_por_docente');
                    }
                }
                $_SESSION['mensaje'] = "Agendados $count estudiantes."; header("Location: " . BASE_URL . "tutorias/agenda"); exit;

            } 
            // CASO ESTUDIANTE (UNA CITA PENDIENTE)
            else {
                $tutor_id = $_POST['tutor_id'];
                if (strtotime("$fecha $hora_ini") < (time() + 86400)) { $_SESSION['error'] = "Requiere 24h anticipación."; header("Location: " . BASE_URL . "tutorias/solicitar"); exit; }
                if ($modelo->contarActivasEstudiante($_SESSION['usuario_id']) >= 2) { $_SESSION['error'] = "Límite de reservas alcanzado."; header("Location: " . BASE_URL . "tutorias/solicitar"); exit; }
                if ($modelo->verificarCruce($tutor_id, $fecha, $hora_ini, $hora_fin)) { $_SESSION['error'] = "Horario ocupado."; header("Location: " . BASE_URL . "tutorias/solicitar"); exit; }

                $datos = [
                    'solicitado_por' => $_SESSION['usuario_id'], 'tutor_id' => $tutor_id,
                    'estudiante_id' => $_SESSION['usuario_id'], 'tipo_id' => $_POST['tipo_id'],
                    'tema' => $_POST['tema'], 'fecha' => $fecha, 'hora_inicio' => $hora_ini, 'hora_fin' => $hora_fin,
                    'modalidad' => $_POST['modalidad'], 'estado' => 'PENDIENTE'
                ];
                if ($modelo->crear($datos)) {
                    $this->enviarCorreo($tutor_id, $_SESSION['usuario_id'], $datos, 'solicitud_estudiante');
                    $_SESSION['mensaje'] = "Solicitud enviada."; header("Location: " . BASE_URL . "tutorias/mis_solicitudes");
                } else {
                    $_SESSION['error'] = "Error al guardar."; header("Location: " . BASE_URL . "tutorias/solicitar");
                }
                exit;
            }
        }
    }

    // --- API AJAX: HORARIOS ---
    public function apiGetHorarios() {
        header('Content-Type: application/json');
        $docente = $_GET['docente_id'] ?? null; $fecha = $_GET['fecha'] ?? null;
        if (!$docente || !$fecha) { echo json_encode([]); exit; }
        
        $db = Database::getConnection();
        $dia = date('N', strtotime($fecha));
        $bloques = $db->query("SELECT * FROM horarios_docentes WHERE docente_id=$docente AND dia_semana=$dia")->fetchAll();
        $ocupados = $db->query("SELECT hora_inicio, hora_fin FROM tutorias WHERE tutor_id=$docente AND fecha='$fecha' AND estado NOT IN ('CANCELADA','RECHAZADA')")->fetchAll();
        
        $disponibles = [];
        foreach ($bloques as $b) {
            $ini = strtotime($fecha.' '.$b['hora_inicio']); $fin = strtotime($fecha.' '.$b['hora_fin']);
            while ($ini < $fin) {
                $slot_fin = $ini + 1800; // 30 mins
                if ($slot_fin > $fin) break;
                $libre = true;
                foreach ($ocupados as $oc) {
                    $o_ini = strtotime($fecha.' '.$oc['hora_inicio']); $o_fin = strtotime($fecha.' '.$oc['hora_fin']);
                    if (($ini < $o_fin) && ($slot_fin > $o_ini)) { $libre = false; break; }
                }
                if ($libre) $disponibles[] = date('H:i', $ini);
                $ini = $slot_fin;
            }
        }
        echo json_encode($disponibles);
    }

    // --- API AJAX: DETALLES ---
    public function apiGetDetalle() {
        header('Content-Type: application/json');
        if (!isset($_GET['id'])) { echo json_encode(['error'=>'Falta ID']); exit; }
        $modelo = new Tutoria();
        $d = $modelo->obtenerDetalle($_GET['id']);
        if(!$d) { echo json_encode(['error'=>'No encontrada']); exit; }
        
        // Seguridad Básica
        $rol = $_SESSION['usuario_rol']; $uid = $_SESSION['usuario_id'];
        if ($rol!='ADMIN' && !($rol=='DOCENTE' && $d['tutor_id']==$uid) && !($rol=='ESTUDIANTE' && $d['estudiante_id']==$uid)) {
            echo json_encode(['error'=>'Acceso denegado']); exit;
        }
        echo json_encode($d);
    }

    // --- ESTUDIANTE: LISTAR Y CANCELAR ---
    public function mis_solicitudes() {
        if ($_SESSION['usuario_rol'] !== 'ESTUDIANTE') header("Location: ".BASE_URL);
        $tutoria = new Tutoria();
        $citas = $tutoria->obtenerPorUsuario($_SESSION['usuario_id'], 'ESTUDIANTE');
        require_once 'views/layouts/header.php'; require_once 'views/layouts/sidebar.php';
        require_once 'views/tutorias/lista_estudiante.php'; require_once 'views/layouts/footer.php';
    }

    public function cancelar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getConnection();
            $db->prepare("UPDATE tutorias SET estado='CANCELADA' WHERE id=? AND solicitado_por=?")->execute([$_POST['id_tutoria'], $_SESSION['usuario_id']]);
            header("Location: " . BASE_URL . "tutorias/mis_solicitudes");
        }
    }

    // --- DOCENTE: AGENDA, RESPONDER Y ASISTENCIA ---
    public function agenda() {
        if ($_SESSION['usuario_rol'] !== 'DOCENTE') header("Location: ".BASE_URL);
        $modelo = new Tutoria();
        $todas = $modelo->obtenerPorUsuario($_SESSION['usuario_id'], 'DOCENTE');
        $pendientes = []; $agenda = [];
        foreach ($todas as $t) {
            if ($t['estado'] == 'PENDIENTE') $pendientes[] = $t;
            elseif (in_array($t['estado'], ['CONFIRMADA','PROGRAMADA','REALIZADA','NO_ASISTIO'])) $agenda[] = $t;
        }
        require_once 'views/layouts/header.php'; require_once 'views/layouts/sidebar.php';
        require_once 'views/tutorias/agenda_docente.php'; require_once 'views/layouts/footer.php';
    }

    public function responder() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modelo = new Tutoria();
            $res = $modelo->responderSolicitud($_POST['id_tutoria'], $_SESSION['usuario_id'], $_POST['accion'], $_POST['motivo']??null, $_POST['lugar']??null);
            if($res) {
                 // Notificar (Aquí podrías llamar a enviarCorreo si quisieras notificar la respuesta)
                 $_SESSION['mensaje'] = "Procesado.";
            }
            header("Location: " . BASE_URL . "tutorias/agenda");
        }
    }

    public function asistencia() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modelo = new Tutoria();
            $modelo->registrarAsistencia($_POST['id_tutoria'], $_SESSION['usuario_id'], $_POST['asistio'], $_POST['observaciones']);
            header("Location: " . BASE_URL . "tutorias/agenda");
        }
    }

    // --- HELPER CORREOS ---
    private function enviarCorreo($dest_id, $remit_id, $datos, $tipo) {
        if(file_exists('config/Mailer.php')) {
            require_once 'config/Mailer.php';
            $u = new Usuario();
            $dest = $u->obtenerPorId($dest_id); $remit = $u->obtenerPorId($remit_id);
            $asunto = ($tipo=='cita_creada_por_docente') ? "Nueva Cita Agendada" : "Nueva Solicitud";
            $msg = "<p>Gestión de Tutorías: <b>".$remit['nombre']."</b> ha generado una actividad.</p>";
            Mailer::enviar($dest['correo'], $dest['nombre'], $asunto, $msg);
        }
    }
}
?>