<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Gestión de Tutorías</h1>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">

            <?php if(isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <button class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-check"></i> <?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-ban"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-inbox mr-1"></i> Solicitudes Pendientes
                    </h3>
                </div>
                <div class="card-body p-0">
                    <?php if(empty($pendientes)): ?>
                        <div class="text-center p-4 text-muted">No hay solicitudes pendientes.</div>
                    <?php else: ?>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Fecha</th>
                                    <th>Detalles</th>
                                    <th class="text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($pendientes as $p): ?>
                                <tr>
                                    <td>
                                        <b><?php echo htmlspecialchars($p['contraparte']); ?></b><br>
                                        <small class="text-muted">Cod: <?php echo $p['codigo_reserva']; ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($p['fecha'])); ?><br>
                                        <small><?php echo substr($p['hora_inicio'], 0, 5); ?> - <?php echo substr($p['hora_fin'], 0, 5); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge" style="background:<?php echo $p['color_etiqueta']; ?>; color:#fff">
                                            <?php echo htmlspecialchars($p['tipo']); ?>
                                        </span>
                                        <br>
                                        <small><?php echo htmlspecialchars($p['tema']); ?></small>
                                    </td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-success btn-sm btn-responder" 
                                                data-id="<?php echo $p['id']; ?>" 
                                                data-accion="confirmar">
                                            <i class="fas fa-check"></i> Aceptar
                                        </button>
                                        
                                        <button type="button" class="btn btn-danger btn-sm btn-responder" 
                                                data-id="<?php echo $p['id']; ?>" 
                                                data-accion="rechazar">
                                            <i class="fas fa-times"></i> Rechazar
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card card-primary card-outline mt-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Agenda Confirmada</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Estudiante</th>
                                <th>Lugar</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($agenda as $a): 
                                $esPasado = (strtotime($a['fecha'] . ' ' . $a['hora_inicio']) <= time());
                            ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($a['fecha'])); ?> <?php echo substr($a['hora_inicio'], 0, 5); ?></td>
                                <td><?php echo htmlspecialchars($a['contraparte']); ?></td>
                                <td><?php echo htmlspecialchars($a['lugar']); ?></td>
                                <td>
                                    <?php if($esPasado && !in_array($a['estado'], ['REALIZADA','NO_ASISTIO'])): ?>
                                        <button class="btn btn-sm btn-primary btn-asistencia" 
                                                data-id="<?php echo $a['id']; ?>"
                                                data-alumno="<?php echo htmlspecialchars($a['contraparte']); ?>">
                                            <i class="fas fa-clipboard-check"></i> Finalizar
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><?php echo $a['estado']; ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalRespuesta" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form class="modal-content" method="POST" action="<?php echo BASE_URL; ?>tutorias/responder">
            <input type="hidden" name="id_tutoria" id="resp_id">
            <input type="hidden" name="accion" id="resp_accion">
            
            <div class="modal-header">
                <h5 class="modal-title" id="resp_titulo">Procesar Solicitud</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            
            <div class="modal-body">
                <div id="msg_confirmar" class="alert alert-success" style="display:none">
                    <i class="fas fa-check-circle"></i> Vas a <b>ACEPTAR</b> esta tutoría.
                </div>
                <div id="msg_rechazar" class="alert alert-danger" style="display:none">
                    <i class="fas fa-times-circle"></i> Vas a <b>RECHAZAR</b> esta tutoría.
                </div>

                <div class="form-group" id="group_lugar">
                    <label>Lugar / Enlace de Reunión</label>
                    <input type="text" name="lugar" id="input_lugar" class="form-control" placeholder="Ej: Aula 101 o Zoom">
                    <small class="text-muted">Si lo dejas vacío, se usará tu lugar predeterminado.</small>
                </div>

                <div class="form-group" id="group_motivo" style="display:none">
                    <label>Motivo del Rechazo</label>
                    <textarea name="motivo" id="input_motivo" class="form-control" rows="2" placeholder="Indica la razón..."></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btn_submit">Confirmar Acción</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalAsistencia">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="<?php echo BASE_URL; ?>tutorias/asistencia">
            <input type="hidden" name="id_tutoria" id="asis_id">
            <div class="modal-header bg-navy"><h5 class="modal-title text-white">Finalizar Tutoría</h5><button class="close text-white" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <p>Estudiante: <b id="asis_alumno"></b></p>
                <div class="text-center mb-3">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-outline-success active"><input type="radio" name="asistio" value="1" checked> SÍ ASISTIÓ</label>
                        <label class="btn btn-outline-danger"><input type="radio" name="asistio" value="0"> NO ASISTIÓ</label>
                    </div>
                </div>
                <textarea name="observaciones" class="form-control" placeholder="Observaciones..."></textarea>
            </div>
            <div class="modal-footer"><button class="btn btn-primary btn-block">Guardar</button></div>
        </form>
    </div>
</div>

<script>
    // Esperamos a que todo cargue
    window.addEventListener('load', function() {
        
        // Usamos jQuery con delegación para asegurar que funcione siempre
        $(document).on('click', '.btn-responder', function(e) {
            e.preventDefault(); // Prevenir cualquier comportamiento raro
            
            // 1. Obtener datos del botón
            var id = $(this).data('id');
            var accion = $(this).data('accion');
            
            console.log("Click detectado: ID=" + id + ", Accion=" + accion); // DEBUG

            // 2. Llenar inputs ocultos
            $('#resp_id').val(id);
            $('#resp_accion').val(accion);

            // 3. Configurar interfaz según acción
            if(accion === 'confirmar') {
                $('#resp_titulo').text('Aceptar Solicitud');
                $('#msg_confirmar').show();
                $('#msg_rechazar').hide();
                
                $('#group_lugar').show();
                $('#group_motivo').hide();
                
                // Quitamos el required por JS para evitar bloqueos del navegador, validaremos en PHP si hace falta
                $('#input_lugar').attr('placeholder', 'Ej: Aula 101');
                $('#btn_submit').removeClass('btn-danger').addClass('btn-success').text('Confirmar Aceptación');
            } else {
                $('#resp_titulo').text('Rechazar Solicitud');
                $('#msg_confirmar').hide();
                $('#msg_rechazar').show();
                
                $('#group_lugar').hide();
                $('#group_motivo').show();
                
                $('#btn_submit').removeClass('btn-success').addClass('btn-danger').text('Confirmar Rechazo');
            }

            // 4. Abrir Modal
            $('#modalRespuesta').modal('show');
        });

        // Lógica para el modal de asistencia (sin cambios)
        $(document).on('click', '.btn-asistencia', function() {
            var id = $(this).data('id');
            var alumno = $(this).data('alumno');
            $('#asis_id').val(id);
            $('#asis_alumno').text(alumno);
            $('#modalAsistencia').modal('show');
        });
    });
</script>