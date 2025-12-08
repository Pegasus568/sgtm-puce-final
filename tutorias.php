<?php
// tutorias.php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Verificamos permisos
verificarRol(['DOCENTE', 'ESTUDIANTE', 'ADMIN']);

$rol_actual = $_SESSION['usuario_rol'];
$user_id    = $_SESSION['usuario_id'];
$mensaje    = "";
$error      = "";

// ==========================================================================
// 1. CONTROLADOR: PROCESAR ACCIONES (POST)
// ==========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- A. CREAR SOLICITUD / ASIGNACIÓN ---
    if (isset($_POST['action']) && $_POST['action'] === 'crear_tutoria') {
        $tipo        = $_POST['tipo']; 
        $titulo      = $_POST['titulo'];
        $fecha       = $_POST['fecha'];
        $hora_ini    = $_POST['hora_inicio'];
        $modalidad   = $_POST['modalidad'];
        $lugar       = $_POST['lugar'] ?? 'Por definir';

        // Lógica de hora fin
        if ($rol_actual === 'ESTUDIANTE' && isset($_POST['duracion'])) {
            $duracion = (int)$_POST['duracion'];
            $hora_fin = date('H:i', strtotime("+$duracion minutes", strtotime($hora_ini)));
        } else {
            $hora_fin = $_POST['hora_fin']; // Docente la pone manual
        }

        try {
            $pdo->beginTransaction();

            if ($rol_actual === 'DOCENTE') {
                // --- LÓGICA DOCENTE (SIN CAMBIOS) ---
                $estudiantes_ids = $_POST['estudiantes']; 
                $tutor_id        = $user_id;
                $estado_inicial  = 'CONFIRMADA'; 

                foreach ($estudiantes_ids as $est_id) {
                    $sql = "INSERT INTO tutorias (tipo, solicitado_por, tutor_id, estudiante_id, titulo, fecha, hora_inicio, hora_fin, modalidad, lugar, estado) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$tipo, $user_id, $tutor_id, $est_id, $titulo, $fecha, $hora_ini, $hora_fin, $modalidad, $lugar, $estado_inicial]);
                }
                $mensaje = "Tutoría asignada a " . count($estudiantes_ids) . " estudiante(s).";

            } else {
                // --- LÓGICA ESTUDIANTE (MEJORADA) ---
                $tutor_id       = $_POST['tutor_id'];
                $est_id         = $user_id;
                $estado_inicial = 'PENDIENTE';

                // 1. Validación 24 horas (Anticipación)
                $fecha_hora_tutoria = strtotime("$fecha $hora_ini");
                if ($fecha_hora_tutoria < (time() + 86400)) {
                    throw new Exception("Las solicitudes deben realizarse con al menos 24 horas de anticipación.");
                }

                // 2. Límite de Carga (Máx 2 activas)
                $stmt_load = $pdo->prepare("SELECT COUNT(*) FROM tutorias WHERE estudiante_id = ? AND estado IN ('PENDIENTE', 'CONFIRMADA', 'PROGRAMADA')");
                $stmt_load->execute([$est_id]);
                if ($stmt_load->fetchColumn() >= 2) {
                    throw new Exception("Límite excedido: No puedes tener más de 2 tutorías activas.");
                }

                // 3. Validación de Duración (Seguridad Backend)
                $minutos = (strtotime($hora_fin) - strtotime($hora_ini)) / 60;
                if ($minutos < 30 || $minutos > 60) {
                    throw new Exception("La duración debe ser de 30 o 60 minutos.");
                }

                // 4. Validación de Disponibilidad Real (Contra la tabla horarios_docentes)
                $dia_semana = date('N', strtotime($fecha)); // 1 (Lunes) a 7 (Domingo)
                // Buscamos si hay un bloque que cubra TODA la duración solicitada
                $sql_check = "SELECT COUNT(*) FROM horarios_docentes 
                              WHERE docente_id = ? 
                              AND dia_semana = ? 
                              AND hora_inicio <= ? 
                              AND hora_fin >= ?";
                $stmt_val = $pdo->prepare($sql_check);
                $stmt_val->execute([$tutor_id, $dia_semana, $hora_ini, $hora_fin]);

                if ($stmt_val->fetchColumn() == 0) {
                    throw new Exception("El horario seleccionado no coincide con la disponibilidad del docente.");
                }

                // Insertar
                $sql = "INSERT INTO tutorias (tipo, solicitado_por, tutor_id, estudiante_id, titulo, fecha, hora_inicio, hora_fin, modalidad, lugar, estado) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$tipo, $user_id, $tutor_id, $est_id, $titulo, $fecha, $hora_ini, $hora_fin, $modalidad, $lugar, $estado_inicial]);
                $mensaje = "Solicitud enviada al docente correctamente.";
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }

    // --- B. GESTIONAR (DOCENTE) ---
    if (isset($_POST['action']) && $_POST['action'] === 'gestionar') {
        $id_tutoria = $_POST['id_tutoria'];
        $nuevo_estado = $_POST['nuevo_estado'];
        $motivo = $_POST['motivo_rechazo'] ?? null;
        $lugar_final = $_POST['lugar_asignado'] ?? null;

        $stmt = $pdo->prepare("UPDATE tutorias SET estado = ?, motivo_rechazo = ?, lugar = COALESCE(?, lugar) WHERE id = ? AND tutor_id = ?");
        if ($stmt->execute([$nuevo_estado, $motivo, $lugar_final, $id_tutoria, $user_id])) {
            $mensaje = "Solicitud actualizada.";
        }
    }

    // --- C. ASISTENCIA (DOCENTE) ---
    if (isset($_POST['action']) && $_POST['action'] === 'asistencia') {
        $id_tutoria = $_POST['id_tutoria'];
        $asistio = $_POST['asistio']; 
        
        $stmt = $pdo->prepare("UPDATE tutorias SET asistio = ?, estado = 'REALIZADA' WHERE id = ? AND tutor_id = ?");
        if ($stmt->execute([$asistio, $id_tutoria, $user_id])) {
            $mensaje = "Asistencia registrada.";
        }
    }

    // --- D. ELIMINAR / CANCELAR ---
    if (isset($_POST['action']) && $_POST['action'] === 'eliminar_tutoria') {
        $id_tutoria = $_POST['id_tutoria'];
        
        // Verificar propiedad
        if ($rol_actual == 'DOCENTE') {
            $stmt_check = $pdo->prepare("SELECT fecha, hora_inicio FROM tutorias WHERE id = ? AND tutor_id = ?");
        } else {
            $stmt_check = $pdo->prepare("SELECT fecha, hora_inicio FROM tutorias WHERE id = ? AND estudiante_id = ?");
        }
        $stmt_check->execute([$id_tutoria, $user_id]);
        $tut = $stmt_check->fetch();

        if ($tut) {
            $ts_inicio = strtotime($tut['fecha'] . ' ' . $tut['hora_inicio']);
            $ahora = time();
            
            if (($ts_inicio - $ahora) < 86400) {
                $error = "No puedes cancelar: Faltan menos de 24 horas para el inicio.";
            } else {
                $stmt_del = $pdo->prepare("UPDATE tutorias SET estado = 'CANCELADA' WHERE id = ?");
                if ($stmt_del->execute([$id_tutoria])) {
                    $mensaje = "Tutoría cancelada correctamente.";
                }
            }
        } else {
            $error = "Tutoría no encontrada.";
        }
    }

    // --- E. EDITAR ---
    if (isset($_POST['action']) && $_POST['action'] === 'editar_tutoria') {
        $id_tutoria = $_POST['id_tutoria'];
        $titulo_ed  = $_POST['titulo'];
        $mod_ed     = $_POST['modalidad'];
        $lugar_ed   = $_POST['lugar'] ?? null;
        $obs_ed     = $_POST['observaciones'] ?? null;

        if ($rol_actual == 'DOCENTE') {
            $stmt_check = $pdo->prepare("SELECT fecha, hora_fin FROM tutorias WHERE id = ? AND tutor_id = ?");
        } else {
            $stmt_check = $pdo->prepare("SELECT fecha, hora_fin FROM tutorias WHERE id = ? AND estudiante_id = ?");
        }
        $stmt_check->execute([$id_tutoria, $user_id]);
        $tut = $stmt_check->fetch();

        if ($tut) {
            $ts_fin = strtotime($tut['fecha'] . ' ' . $tut['hora_fin']);
            $ahora = time();

            if ($ahora > $ts_fin && ($ahora - $ts_fin) > (48 * 3600)) {
                $error = "No puedes editar: Han pasado más de 48 horas desde la finalización.";
            } else {
                $stmt_upd = $pdo->prepare("UPDATE tutorias SET titulo=?, modalidad=?, lugar=?, observaciones=? WHERE id=?");
                if ($stmt_upd->execute([$titulo_ed, $mod_ed, $lugar_ed, $obs_ed, $id_tutoria])) {
                    $mensaje = "Datos actualizados.";
                }
            }
        }
    }
}

// ==========================================================================
// 2. VISTAS
// ==========================================================================

if ($rol_actual === 'DOCENTE') {
    $estudiantes = $pdo->query("SELECT id, nombre, semestre FROM usuarios WHERE rol = 'ESTUDIANTE' AND estado = 'ACTIVO' ORDER BY nombre")->fetchAll();
    $pendientes = $pdo->prepare("SELECT t.*, u.nombre as est_nombre FROM tutorias t JOIN usuarios u ON t.estudiante_id = u.id WHERE t.tutor_id = ? AND t.estado = 'PENDIENTE' ORDER BY fecha ASC");
    $pendientes->execute([$user_id]);
    $lista_pendientes = $pendientes->fetchAll();
    $agenda = $pdo->prepare("SELECT t.*, u.nombre as est_nombre FROM tutorias t JOIN usuarios u ON t.estudiante_id = u.id WHERE t.tutor_id = ? AND t.estado NOT IN ('PENDIENTE') ORDER BY fecha DESC");
    $agenda->execute([$user_id]);
    $lista_agenda = $agenda->fetchAll();
} else {
    $docentes = $pdo->query("SELECT id, nombre FROM usuarios WHERE rol = 'DOCENTE' AND estado = 'ACTIVO' ORDER BY nombre")->fetchAll();
    $mis_tutorias = $pdo->prepare("SELECT t.*, u.nombre as doc_nombre FROM tutorias t JOIN usuarios u ON t.tutor_id = u.id WHERE t.estudiante_id = ? ORDER BY fecha DESC");
    $mis_tutorias->execute([$user_id]);
    $lista_mias = $mis_tutorias->fetchAll();
}

require_once 'includes/header.php';
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1>Gestión de Tutorías <small>(<?php echo ucfirst(strtolower($rol_actual)); ?>)</small></h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            
            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="icon fas fa-ban"></i> <?php echo $error; ?></div>
            <?php elseif($mensaje): ?>
                <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="icon fas fa-check"></i> <?php echo $mensaje; ?></div>
            <?php endif; ?>

            <div class="row">
                
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header"><h3 class="card-title">Nueva Actividad</h3></div>
                        <form action="tutorias.php" method="POST">
                            <input type="hidden" name="action" value="crear_tutoria">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Tema / Título</label>
                                    <input type="text" name="titulo" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Tipo</label>
                                    <select name="tipo" class="form-control">
                                        <option value="TUTORIA">Tutoría</option>
                                        <option value="MENTORIA">Mentoría</option>
                                    </select>
                                </div>
                                
                                <?php if ($rol_actual === 'DOCENTE'): ?>
                                    <div class="form-group">
                                        <label>Estudiante(s)</label>
                                        <select name="estudiantes[]" class="form-control select2" multiple="multiple" style="width: 100%;" required>
                                            <?php foreach($estudiantes as $e): ?>
                                                <option value="<?php echo $e['id']; ?>"><?php echo $e['nombre']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-6"><label>Fecha</label><input type="date" name="fecha" class="form-control" min="<?php echo date('Y-m-d'); ?>" required></div>
                                        <div class="col-6"><label>Modalidad</label><select name="modalidad" class="form-control"><option>PRESENCIAL</option><option>VIRTUAL</option></select></div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-6"><label>Inicio</label><input type="time" name="hora_inicio" class="form-control" required></div>
                                        <div class="col-6"><label>Fin</label><input type="time" name="hora_fin" class="form-control" required></div>
                                    </div>
                                    <div class="form-group mt-2"><label>Lugar</label><input type="text" name="lugar" class="form-control"></div>
                                
                                <?php else: ?>
                                    <div class="form-group">
                                        <label>Docente</label>
                                        <select name="tutor_id" id="tutor_id" class="form-control" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach($docentes as $d): ?>
                                                <option value="<?php echo $d['id']; ?>"><?php echo $d['nombre']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Fecha Deseada</label>
                                        <input type="date" name="fecha" id="fecha_solicitud" class="form-control" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required disabled>
                                        <small class="text-muted" id="msg_dispo">Seleccione docente primero</small>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Hora Inicio (Disp.)</label>
                                                <select name="hora_inicio" id="hora_inicio_est" class="form-control" required disabled>
                                                    <option value="">-- --</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Duración</label>
                                                <select name="duracion" id="duracion_est" class="form-control">
                                                    <option value="30">30 Minutos</option>
                                                    <option value="60">60 Minutos</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Hora Fin (Automática)</label>
                                        <input type="text" id="hora_fin_show" class="form-control" readonly>
                                        <input type="hidden" name="hora_fin" id="hora_fin_hidden">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Modalidad</label>
                                        <select name="modalidad" class="form-control">
                                            <option value="PRESENCIAL">Presencial</option>
                                            <option value="VIRTUAL">Virtual</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-block">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-8">
                    <?php if ($rol_actual === 'DOCENTE'): ?>
                        <div class="card card-warning card-outline collapsed-card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-inbox"></i> Pendientes (<?php echo count($lista_pendientes); ?>)</h3>
                                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div>
                            </div>
                            <div class="card-body p-0" style="display:none;">
                                <table class="table table-sm">
                                    <tbody>
                                        <?php foreach($lista_pendientes as $sol): ?>
                                        <tr>
                                            <td><?php echo $sol['est_nombre']; ?></td>
                                            <td><?php echo $sol['titulo']; ?> <br> <small><?php echo $sol['fecha']; ?></small></td>
                                            <td>
                                                <button class="btn btn-success btn-xs" data-toggle="modal" data-target="#modalAp<?php echo $sol['id']; ?>">OK</button>
                                                <button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#modalRe<?php echo $sol['id']; ?>">X</button>
                                            </td>
                                        </tr>
                                        <div class="modal fade" id="modalAp<?php echo $sol['id']; ?>"><div class="modal-dialog"><form class="modal-content" method="POST"><input type="hidden" name="action" value="gestionar"><input type="hidden" name="id_tutoria" value="<?php echo $sol['id']; ?>"><input type="hidden" name="nuevo_estado" value="CONFIRMADA"><div class="modal-body"><input type="text" name="lugar_asignado" placeholder="Lugar" class="form-control" required></div><div class="modal-footer"><button class="btn btn-success">Confirmar</button></div></form></div></div>
                                        <div class="modal fade" id="modalRe<?php echo $sol['id']; ?>"><div class="modal-dialog"><form class="modal-content" method="POST"><input type="hidden" name="action" value="gestionar"><input type="hidden" name="id_tutoria" value="<?php echo $sol['id']; ?>"><input type="hidden" name="nuevo_estado" value="RECHAZADA"><div class="modal-body"><textarea name="motivo_rechazo" placeholder="Motivo" class="form-control" required></textarea></div><div class="modal-footer"><button class="btn btn-danger">Rechazar</button></div></form></div></div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card card-info card-outline">
                            <div class="card-header"><h3 class="card-title">Agenda</h3></div>
                            <div class="card-body p-0">
                                <table class="table table-striped table-sm">
                                    <thead><tr><th>Fecha</th><th>Estudiante</th><th>Estado</th><th>Acciones</th></tr></thead>
                                    <tbody>
                                        <?php foreach($lista_agenda as $item): ?>
                                        <tr>
                                            <td><?php echo $item['fecha']; ?> <small><?php echo substr($item['hora_inicio'],0,5); ?></small></td>
                                            <td><?php echo $item['est_nombre']; ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo ($item['estado']=='CANCELADA')?'danger':'success'; ?>"><?php echo $item['estado']; ?></span>
                                                <?php if($item['asistio']!==null) echo ($item['asistio'])?'<i class="fas fa-check text-success"></i>':'<i class="fas fa-times text-danger"></i>'; ?>
                                            </td>
                                            <td>
                                                <?php if($item['estado'] == 'CONFIRMADA' && $item['asistio'] === null): ?>
                                                    <form method="POST" class="d-inline"><input type="hidden" name="action" value="asistencia"><input type="hidden" name="id_tutoria" value="<?php echo $item['id']; ?>"><button name="asistio" value="1" class="btn btn-xs btn-outline-success"><i class="fas fa-check"></i></button></form>
                                                    <form method="POST" class="d-inline"><input type="hidden" name="action" value="asistencia"><input type="hidden" name="id_tutoria" value="<?php echo $item['id']; ?>"><button name="asistio" value="0" class="btn btn-xs btn-outline-danger"><i class="fas fa-times"></i></button></form>
                                                <?php endif; ?>
                                                
                                                <?php if((time() - strtotime($item['fecha'].' '.$item['hora_fin'])) < (48*3600)): ?>
                                                    <button class="btn btn-info btn-xs" data-toggle="modal" data-target="#modalEd<?php echo $item['id']; ?>"><i class="fas fa-pen"></i></button>
                                                <?php endif; ?>

                                                <?php if((strtotime($item['fecha'].' '.$item['hora_inicio']) - time()) > 86400 && $item['estado'] != 'CANCELADA'): ?>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar?');">
                                                        <input type="hidden" name="action" value="eliminar_tutoria">
                                                        <input type="hidden" name="id_tutoria" value="<?php echo $item['id']; ?>">
                                                        <button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <div class="modal fade" id="modalEd<?php echo $item['id']; ?>"><div class="modal-dialog"><form class="modal-content" method="POST"><input type="hidden" name="action" value="editar_tutoria"><input type="hidden" name="id_tutoria" value="<?php echo $item['id']; ?>"><div class="modal-body"><label>Título:</label><input type="text" name="titulo" class="form-control" value="<?php echo $item['titulo']; ?>" required><label>Modalidad:</label><select name="modalidad" class="form-control"><option value="PRESENCIAL">Presencial</option><option value="VIRTUAL">Virtual</option></select><label>Obs:</label><textarea name="observaciones" class="form-control"><?php echo $item['observaciones']; ?></textarea></div><div class="modal-footer"><button class="btn btn-primary">Guardar</button></div></form></div></div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="card card-primary card-outline">
                            <div class="card-header"><h3 class="card-title">Mis Solicitudes</h3></div>
                            <div class="card-body p-0">
                                <table class="table table-striped">
                                    <thead><tr><th>Docente</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr></thead>
                                    <tbody>
                                        <?php foreach($lista_mias as $mi): ?>
                                        <tr>
                                            <td><?php echo $mi['doc_nombre']; ?> <br> <small><?php echo $mi['titulo']; ?></small></td>
                                            <td><?php echo $mi['fecha']; ?> <small><?php echo substr($mi['hora_inicio'],0,5); ?></small></td>
                                            <td><span class="badge badge-secondary"><?php echo $mi['estado']; ?></span></td>
                                            <td>
                                                <?php 
                                                    // Estudiante puede editar/eliminar si faltan más de 24h
                                                    $ts_ini = strtotime($mi['fecha'].' '.$mi['hora_inicio']);
                                                    if(($ts_ini - time()) > 86400 && $mi['estado'] != 'CANCELADA'): 
                                                ?>
                                                    <button class="btn btn-info btn-xs" data-toggle="modal" data-target="#modalEdEst<?php echo $mi['id']; ?>"><i class="fas fa-pen"></i></button>
                                                    
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar solicitud?');">
                                                        <input type="hidden" name="action" value="eliminar_tutoria">
                                                        <input type="hidden" name="id_tutoria" value="<?php echo $mi['id']; ?>">
                                                        <button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <div class="modal fade" id="modalEdEst<?php echo $mi['id']; ?>"><div class="modal-dialog"><form class="modal-content" method="POST"><input type="hidden" name="action" value="editar_tutoria"><input type="hidden" name="id_tutoria" value="<?php echo $mi['id']; ?>"><div class="modal-body"><label>Título:</label><input type="text" name="titulo" class="form-control" value="<?php echo $mi['titulo']; ?>" required><label>Modalidad:</label><select name="modalidad" class="form-control"><option value="PRESENCIAL">Presencial</option><option value="VIRTUAL">Virtual</option></select></div><div class="modal-footer"><button class="btn btn-primary">Actualizar</button></div></form></div></div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </section>
</div>

<?php if ($rol_actual === 'ESTUDIANTE'): ?>
<script>
    const tutorSelect = document.getElementById('tutor_id');
    const fechaInput  = document.getElementById('fecha_solicitud');
    const horaSelect  = document.getElementById('hora_inicio_est');
    const duracionSel = document.getElementById('duracion_est');
    const msgDispo    = document.getElementById('msg_dispo');
    const horaFinShow = document.getElementById('hora_fin_show');

    // 1. Al elegir tutor, activar fecha
    tutorSelect.addEventListener('change', function() {
        if(this.value) {
            fechaInput.disabled = false;
            msgDispo.textContent = "Seleccione una fecha para ver horarios";
        } else {
            fechaInput.disabled = true;
            horaSelect.disabled = true;
            fechaInput.value = '';
        }
    });

    // 2. Al cambiar fecha, traer horarios (AJAX)
    fechaInput.addEventListener('change', function() {
        const docenteId = tutorSelect.value;
        const fecha = this.value;
        
        if(docenteId && fecha) {
            msgDispo.textContent = "Buscando horarios...";
            horaSelect.innerHTML = '<option>Cargando...</option>';
            horaSelect.disabled = true;

            // Llamada al API creado anteriormente
            fetch(`api_horarios.php?docente_id=${docenteId}&fecha=${fecha}`)
                .then(response => response.json())
                .then(data => {
                    horaSelect.innerHTML = ''; // Limpiar
                    
                    if(data.disponibles && data.disponibles.length > 0) {
                        horaSelect.disabled = false;
                        msgDispo.textContent = "Horarios cargados.";
                        
                        // Llenar select con bloques de 30 mins
                        data.disponibles.forEach(rango => {
                            let start = new Date(`2000-01-01T${rango.hora_inicio}`);
                            let end   = new Date(`2000-01-01T${rango.hora_fin}`);
                            
                            while(start < end) {
                                let timeStr = start.toTimeString().substr(0,5); // HH:MM
                                
                                // Opcional: Filtrar si ya está ocupado en data.ocupados
                                // Por ahora mostramos todo el rango base del docente
                                let option = document.createElement('option');
                                option.value = timeStr;
                                option.text  = timeStr;
                                horaSelect.appendChild(option);
                                
                                start.setMinutes(start.getMinutes() + 30);
                            }
                        });
                        calcularFin(); // Calcular inicial
                    } else {
                        horaSelect.disabled = true;
                        horaSelect.innerHTML = '<option>No hay disponibilidad</option>';
                        msgDispo.textContent = "El docente no tiene horario este día.";
                    }
                })
                .catch(err => {
                    console.error(err);
                    msgDispo.textContent = "Error al cargar horarios.";
                });
        }
    });

    // 3. Calcular Hora Fin automáticamente
    function calcularFin() {
        const horaIni = horaSelect.value; 
        const dur = parseInt(duracionSel.value); 

        if(horaIni && !isNaN(dur)) {
            let fechaBase = new Date(`2000-01-01T${horaIni}:00`);
            fechaBase.setMinutes(fechaBase.getMinutes() + dur);
            
            let finStr = fechaBase.toTimeString().substr(0,5);
            horaFinShow.value = finStr;
            document.getElementById('hora_fin_hidden').value = finStr;
        }
    }

    horaSelect.addEventListener('change', calcularFin);
    duracionSel.addEventListener('change', calcularFin);
</script>
<?php endif; ?>

<?php if ($rol_actual === 'DOCENTE'): ?>
<link rel="stylesheet" href="adminlte/plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
<script src="adminlte/plugins/select2/js/select2.full.min.js"></script>
<script>$(function(){$('.select2').select2({theme:'bootstrap4'})});</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>