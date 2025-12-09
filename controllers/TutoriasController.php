<?php
// controllers/TutoriasController.php

// Carga de Modelos Necesarios
require_once 'models/Tutoria.php';
require_once 'models/Usuario.php';
require_once 'models/TipoTutoria.php';
require_once 'models/Horario.php';

class TutoriasController {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Seguridad: Si no hay usuario logueado, mandar al login
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "auth/index");
            exit;
        }
    }

    // Acción principal: Redirección inteligente según el Rol
    public function index() {
        if ($_SESSION['usuario_rol'] == 'ESTUDIANTE') {
            header("Location: " . BASE_URL . "tutorias/mis_solicitudes");
        } elseif ($_SESSION['usuario_rol'] == 'DOCENTE') {
            header("Location: " . BASE_URL . "tutorias/agenda");
        } else {
            // Si es Admin, mostramos el dashboard general de admin
            header("Location: " . BASE_URL . "admintutorias/index");
        }
        exit;
    }

    // ====================================================================
    // MÓDULO ESTUDIANTE: SOLICITUDES Y RESERVAS
    // ====================================================================

    // 1. Mostrar el formulario de solicitud
    public function solicitar() {
        // Verificar rol
        if ($_SESSION['usuario_rol'] !== 'ESTUDIANTE') {
            header("Location: " . BASE_URL . "tutorias/index"); exit;
        }

        $userModel = new Usuario();
        $tipoModel = new TipoTutoria();
        
        // Obtener docentes para el select
        $todosUsuarios = $userModel->obtenerTodos();
        $docentes = array_filter($todosUsuarios, function($u) {
            return $u['rol'] === 'DOCENTE';
        });

        // Obtener tipos de tutoría activos
        $tipos = $tipoModel->obtenerActivos();

        require_once 'views/layouts/header.php';
        require_once 'views/layouts/sidebar.php';
        require_once 'views/tutorias/solicitar.php';
        require_once 'views/layouts/footer.php';
    }

    // 2. API AJAX: Calcular horarios disponibles (El cerebro del sistema)
    public function apiGetHorarios() {
        // Esta función devuelve JSON, no HTML
        header('Content-Type: application/json');
        
        $docente_id = $_GET['docente_id'] ?? null;
        $fecha = $_GET['fecha'] ?? null;

        if (!$docente_id || !$fecha) {
            echo json_encode([]); 
            exit;
        }

        $dia_semana = date('N', strtotime($fecha)); // 1 (Lunes) a 7 (Domingo)
        
        // Modelos necesarios (usamos conexión directa para optimizar)
        $db = Database::getConnection();

        // A. Obtener bloques definidos por el docente para ese día de la semana
        $stmt = $db->prepare("SELECT * FROM horarios_docentes WHERE docente_id = ? AND dia_semana = ?");
        $stmt->execute([$docente_id, $dia_semana]);
        $bloques = $stmt->fetchAll();

        // B. Obtener citas YA ocupadas para ese día (para restarlas)
        $stmt2 = $db->prepare("SELECT hora_inicio, hora_fin FROM tutorias WHERE tutor_id = ? AND fecha = ? AND estado NOT IN ('CANCELADA', 'RECHAZADA')");
        $stmt2->execute([$docente_id, $fecha]);
        $ocupados = $stmt2->fetchAll();

        // C. Calcular los slots de 30 minutos libres
        $disponibles = [];
        
        foreach ($bloques as $bloque) {
            $inicio = strtotime($fecha . ' ' . $bloque['hora_inicio']);
            $fin = strtotime($fecha . ' ' . $bloque['hora_fin']);

            // Iterar en intervalos de 30 mins dentro del bloque
            while ($inicio < $fin) {
                $slot_fin = $inicio + (30 * 60); // +30 minutos
                
                // Si el slot se pasa del fin del bloque, parar
                if ($slot_fin > $fin) break;

                // Verificar colisión con alguna cita ocupada
                $libre = true;
                foreach ($ocupados as $oc) {
                    $oc_ini = strtotime($fecha . ' ' . $oc['hora_inicio']);
                    $oc_fin = strtotime($fecha . ' ' . $oc['hora_fin']);

                    // Lógica de intersección de tiempos
                    if (($inicio < $oc_fin) && ($slot_fin > $oc_ini)) {
                        $libre = false;
                        break;
                    }
                }

                // Si no chocó con nada, agregamos a la lista
                if ($libre) {
                    $disponibles[] = date('H:i', $inicio);
                }
                
                // Avanzar al siguiente slot
                $inicio = $slot_fin;
            }
        }

        echo json_encode($disponibles);
    }

    // 3. Procesar el formulario de reserva (POST)
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modelo = new Tutoria();
            
            $es_estudiante = ($_SESSION['usuario_rol'] === 'ESTUDIANTE');
            $estado_inicial = $es_estudiante ? 'PENDIENTE' : 'CONFIRMADA';
            $solicitante = $_SESSION['usuario_id'];
            
            // Recoger datos
            $tutor = $_POST['tutor_id'];
            $estudiante = $es_estudiante ? $solicitante : $_POST['estudiante_id'];
            
            $fecha = $_POST['fecha'];
            $hora_ini = $_POST['hora_inicio'];
            $duracion = $_POST['duracion'] ?? 30;
            
            // Calcular Hora Fin automáticamente
            $hora_fin = date('H:i', strtotime("$hora_ini + $duracion minutes"));

            // --- VALIDACIONES ---
            if ($es_estudiante) {
                // A. Regla de 24 horas
                if (strtotime("$fecha $hora_ini") < (time() + 86400)) {
                    $_SESSION['error'] = "Debes reservar con al menos 24 horas de anticipación.";
                    header("Location: " . BASE_URL . "tutorias/solicitar"); exit;
                }
                // B. Regla de Carga (Máx 2 activas)
                if ($modelo->contarActivasEstudiante($solicitante) >= 2) {
                    $_SESSION['error'] = "Has alcanzado el límite de 2 reservas activas simultáneas.";
                    header("Location: " . BASE_URL . "tutorias/solicitar"); exit;
                }
            }

            // C. Regla Anti-Colisión (Universal)
            if ($modelo->verificarCruce($tutor, $fecha, $hora_ini, $hora_fin)) {
                $_SESSION['error'] = "El horario seleccionado ya no está disponible.";
                header("Location: " . BASE_URL . "tutorias/solicitar"); exit;
            }

            // Preparar array para el modelo
            $datos = [
                'solicitado_por' => $solicitante,
                'tutor_id' => $tutor,
                'estudiante_id' => $estudiante,
                'tipo_id' => $_POST['tipo_id'],
                'materia_id' => null, 
                'tema' => $_POST['tema'],
                'fecha' => $fecha,
                'hora_inicio' => $hora_ini,
                'hora_fin' => $hora_fin,
                'modalidad' => $_POST['modalidad'],
                'estado' => $estado_inicial
            ];

            // Intentar guardar
            $res = $modelo->crear($datos);

            if ($res === true) {
                
                // [EMAIL] NOTIFICAR AL DOCENTE
                // Verificamos si existe la clase Mailer antes de usarla
                if (file_exists('config/Mailer.php')) {
                    require_once 'config/Mailer.php';
                    $userModel = new Usuario();
                    $docente = $userModel->obtenerPorId($tutor); 
                    $alumno  = $userModel->obtenerPorId($solicitante); 
                    
                    if ($docente && $alumno) {
                        $asunto = "Nueva Solicitud: " . $alumno['nombre'];
                        $mensaje = "<h3>Hola, " . $docente['nombre'] . "</h3>";
                        $mensaje .= "<p>El estudiante <b>" . $alumno['nombre'] . "</b> ha solicitado una tutoría.</p>";
                        $mensaje .= "<ul><li><b>Tema:</b> " . $_POST['tema'] . "</li>";
                        $mensaje .= "<li><b>Fecha:</b> " . $_POST['fecha'] . " a las " . $_POST['hora_inicio'] . "</li></ul>";
                        
                        Mailer::enviar($docente['correo'], $docente['nombre'], $asunto, $mensaje);
                    }
                }
                // [FIN EMAIL]

                $_SESSION['mensaje'] = "Solicitud creada correctamente.";
                header("Location: " . BASE_URL . "tutorias/mis_solicitudes");
            } else {
                $_SESSION['error'] = "Error al guardar: " . $res;
                header("Location: " . BASE_URL . "tutorias/solicitar");
            }
            exit;
        }
    }

    // 4. Ver mis solicitudes (Vista Estudiante)
    public function mis_solicitudes() {
        if ($_SESSION['usuario_rol'] !== 'ESTUDIANTE') {
            header("Location: " . BASE_URL . "tutorias/index"); exit;
        }

        $tutoria = new Tutoria();
        $citas = $tutoria->obtenerPorUsuario($_SESSION['usuario_id'], 'ESTUDIANTE');
        
        require_once 'views/layouts/header.php';
        require_once 'views/layouts/sidebar.php';
        require_once 'views/tutorias/lista_estudiante.php';
        require_once 'views/layouts/footer.php';
    }

    // 5. Cancelar solicitud (Estudiante)
    public function cancelar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_tutoria'];
            
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE tutorias SET estado = 'CANCELADA' WHERE id = ? AND solicitado_por = ?");
            $stmt->execute([$id, $_SESSION['usuario_id']]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['mensaje'] = "Cita cancelada correctamente.";
            } else {
                $_SESSION['error'] = "No se pudo cancelar la cita.";
            }
            
            header("Location: " . BASE_URL . "tutorias/mis_solicitudes"); exit;
        }
    }

    // ====================================================================
    // MÓDULO DOCENTE: AGENDA Y GESTIÓN
    // ====================================================================

    // 1. Ver la Agenda (Bandeja de Entrada)
    public function agenda() {
        if ($_SESSION['usuario_rol'] !== 'DOCENTE') {
            header("Location: " . BASE_URL . "tutorias/index"); exit;
        }

        $modelo = new Tutoria();
        $todas = $modelo->obtenerPorUsuario($_SESSION['usuario_id'], 'DOCENTE');

        $pendientes = [];
        $agenda = [];
        
        foreach ($todas as $t) {
            if ($t['estado'] == 'PENDIENTE') {
                $pendientes[] = $t;
            } elseif (in_array($t['estado'], ['CONFIRMADA', 'PROGRAMADA', 'REALIZADA', 'NO_ASISTIO'])) {
                $agenda[] = $t;
            }
        }

        require_once 'views/layouts/header.php';
        require_once 'views/layouts/sidebar.php';
        require_once 'views/tutorias/agenda_docente.php';
        require_once 'views/layouts/footer.php';
    }

    // 2. Responder (Aceptar/Rechazar) con notificación por Correo
    public function responder() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['usuario_rol'] === 'DOCENTE') {
            
            $id = $_POST['id_tutoria'];
            $accion = $_POST['accion']; // 'confirmar' o 'rechazar'
            
            $lugar = !empty($_POST['lugar']) ? trim($_POST['lugar']) : null;
            $motivo = !empty($_POST['motivo']) ? trim($_POST['motivo']) : null;

            $modelo = new Tutoria();
            $res = $modelo->responderSolicitud($id, $_SESSION['usuario_id'], $accion, $motivo, $lugar);

            if ($res) {
                
                // [EMAIL] NOTIFICAR AL ESTUDIANTE
                if (file_exists('config/Mailer.php')) {
                    require_once 'config/Mailer.php';
                    
                    // Buscar quién era el estudiante
                    $misCitas = $modelo->obtenerPorUsuario($_SESSION['usuario_id'], 'DOCENTE');
                    $estudiante_id = null;
                    foreach($misCitas as $c) { if($c['id'] == $id) { $estudiante_id = $c['estudiante_id']; break; } }

                    if ($estudiante_id) {
                        $userModel = new Usuario();
                        $alumno = $userModel->obtenerPorId($estudiante_id);
                        
                        if ($alumno) {
                            $estado_txt = ($accion == 'confirmar') ? 'ACEPTADA ✅' : 'RECHAZADA ❌';
                            $asunto = "Tu tutoría ha sido $estado_txt";
                            $mensaje = "<h3>Hola, " . $alumno['nombre'] . "</h3>";
                            $mensaje .= "<p>El docente ha gestionado tu solicitud.</p>";
                            $mensaje .= "<p><b>Estado:</b> $estado_txt</p>";
                            if ($accion == 'confirmar') $mensaje .= "<p><b>Lugar:</b> $lugar</p>";
                            else $mensaje .= "<p><b>Motivo:</b> $motivo</p>";
                            
                            Mailer::enviar($alumno['correo'], $alumno['nombre'], $asunto, $mensaje);
                        }
                    }
                }
                // [FIN EMAIL]

                $_SESSION['mensaje'] = "Solicitud procesada correctamente.";
            } else {
                $_SESSION['error'] = "Error al actualizar la solicitud.";
            }
            
            header("Location: " . BASE_URL . "tutorias/agenda"); exit;
        }
    }

    // 3. Registrar Asistencia (Cierre de Ciclo)
    public function asistencia() {
        if ($_SESSION['usuario_rol'] !== 'DOCENTE') {
            header("Location: " . BASE_URL); exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_tutoria'];
            $asistio = $_POST['asistio']; // 1 o 0
            $obs = trim($_POST['observaciones']);

            $modelo = new Tutoria();
            $res = $modelo->registrarAsistencia($id, $_SESSION['usuario_id'], $asistio, $obs);

            if ($res) {
                $estado_txt = ($asistio == 1) ? "realizada" : "ausente";
                $_SESSION['mensaje'] = "Tutoría marcada como $estado_txt.";
            } else {
                $_SESSION['error'] = "Error al guardar la asistencia.";
            }

            header("Location: " . BASE_URL . "tutorias/agenda"); exit;
        }
    }
}
?>