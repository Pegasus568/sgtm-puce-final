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
                        <span class="badge badge-warning ml-2"><?php echo count($pendientes); ?></span>
                    </h3>
                </div>
                <div class="card-body p-0">
                    <?php if(empty($pendientes)): ?>
                        <div class="text-center p-4 text-muted">No tienes solicitudes pendientes por revisar.</div>
                    <?php else: ?>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Fecha Solicitada</th>
                                    <th>Tema / Tipo</th>
                                    <th class="text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($pendientes as $p): ?>
                                <tr>
                                    <td>
                                        <b><?php echo htmlspecialchars($p['contraparte']); ?></b><br>
                                        <small class="text-muted"><?php echo $p['codigo_reserva']; ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($p['fecha'])); ?> <br>
                                        <span class="badge badge-light">
                                            <?php echo substr($p['hora_inicio'], 0, 5); ?> - <?php echo substr($p['hora_fin'], 0, 5); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background:<?php echo $p['color_etiqueta']; ?>; color:#fff"><?php echo $p['tipo']; ?></span><br>
                                        <?php echo htmlspecialchars($p['tema']); ?>
                                    </td>
                                    <td class="text-right">
                                        <button class="btn btn-success btn-sm btn-responder" 
                                                data-id="<?php echo $p['id']; ?>" 
                                                data-accion="confirmar">
                                            <i class="fas fa-check"></i> Aceptar
                                        </button>
                                        <button class="btn btn-danger btn-sm btn-responder" 
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

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-alt mr-1"></i> Próximas Citas</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Horario</th>
                                <th>Estudiante</th>
                                <th>Lugar</th>
                                <th>Estado / Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($agenda as $a): 
                                // Calcular si ya es hora de tomar lista (Fecha actual >= Fecha Cita)
                                $fechaCita = $a['fecha'] . ' ' . $a['hora_inicio'];
                                $esPasado = (strtotime($fechaCita) <= time());
                            ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($a['fecha'])); ?></td>
                                <td><?php echo substr($a['hora_inicio'], 0, 5); ?> - <?php echo substr($a['hora_fin'], 0, 5); ?></td>
                                <td><?php echo htmlspecialchars($a['contraparte']); ?></td>
                                <td>
                                    <?php if($a['modalidad']=='VIRTUAL'): ?>
                                        <a href="<?php echo $a['lugar']; ?>" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-video"></i> Link</a>
                                    <?php else: ?>
                                        <small><i class="fas fa-map-marker-alt text-danger"></i> <?php echo htmlspecialchars($a['lugar']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($esPasado && $a['estado'] != 'REALIZADA' && $a['estado'] != 'NO_ASISTIO'): ?>
                                        <button class="btn btn-sm btn-primary btn-asistencia" 
                                                data-id="<?php echo $a['id']; ?>"
                                                data-alumno="<?php echo htmlspecialchars($a['contraparte']); ?>">
                                            <i class="fas fa-clipboard-check"></i> Finalizar
                                        </button>
                                    <?php elseif($a['estado'] == 'REALIZADA'): ?>
                                        <span class="badge badge-success">Finalizada</span>
                                    <?php elseif($a['estado'] == 'NO_ASISTIO'): ?>
                                        <span class="badge badge-danger">Ausente</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><i class="fas fa-clock"></i> Programada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if(empty($agenda)): ?>
                                <tr><td colspan="5" class="text-center text-muted p-3">Tu agenda está libre por ahora.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalRespuesta">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="<?php echo BASE_URL; ?>tutorias/responder">
            <input type="hidden" name="id_tutoria" id="resp_id">
            <input type="hidden" name="accion" id="resp_accion">
            
            <div class="modal-header">
                <h5 class="modal-title" id="resp_titulo">Gestionar Solicitud</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            
            <div class="modal-body">
                <div id="bloque_aceptar" style="display:none;">
                    <p class="text-success"><i class="fas fa-check-circle"></i> Estás aceptando esta tutoría.</p>
                    <div class="form-group">
                        <label>Asignar Lugar o Enlace de Reunión:</label>
                        <input type="text" name="lugar" class="form-control" placeholder="Ej: Aula 202 o https://zoom.us/..." id="input_lugar">
                    </div>
                </div>

                <div id="bloque_rechazar" style="display:none;">
                    <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Vas a rechazar esta solicitud.</p>
                    <div class="form-group">
                        <label>Motivo (Obligatorio):</label>
                        <textarea name="motivo" class="form-control" rows="3" placeholder="Explica brevemente por qué..." id="input_motivo"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btn_confirmar">Confirmar Acción</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalAsistencia">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="<?php echo BASE_URL; ?>tutorias/asistencia">
            <input type="hidden" name="id_tutoria" id="asis_id">
            
            <div class="modal-header bg-navy">
                <h5 class="modal-title">Cierre de Tutoría</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            
            <div class="modal-body">
                <p>Estudiante: <b id="asis_alumno"></b></p>
                
                <div class="form-group text-center">
                    <label>¿El estudiante asistió?</label><br>
                    
                    <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width:100%">
                        <label class="btn btn-outline-success active">
                            <input type="radio" name="asistio" value="1" checked> 
                            <i class="fas fa-check"></i> SÍ ASISTIÓ
                        </label>
                        <label class="btn btn-outline-danger">
                            <input type="radio" name="asistio" value="0"> 
                            <i class="fas fa-times"></i> NO ASISTIÓ
                        </label>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label>Observaciones / Resultados (Opcional):</label>
                    <textarea name="observaciones" class="form-control" rows="3" placeholder="Se revisó el capítulo 1..."></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-block">Guardar Registro</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        
        // 1. Manejo del Modal RESPONDER (Aceptar/Rechazar)
        $(document).on('click', '.btn-responder', function() {
            var id = $(this).data('id');
            var accion = $(this).data('accion'); // 'confirmar' o 'rechazar'

            $('#resp_id').val(id);
            $('#resp_accion').val(accion);

            if(accion === 'confirmar') {
                $('#resp_titulo').text('Aceptar Solicitud');
                $('.modal-header').removeClass('bg-danger').addClass('bg-success');
                $('#bloque_aceptar').show();
                $('#bloque_rechazar').hide();
                
                $('#input_lugar').prop('required', true);
                $('#input_motivo').prop('required', false);
                
                $('#btn_confirmar').removeClass('btn-danger').addClass('btn-success').text('Aceptar Tutoría');
            } else {
                $('#resp_titulo').text('Rechazar Solicitud');
                $('.modal-header').removeClass('bg-success').addClass('bg-danger');
                $('#bloque_aceptar').hide();
                $('#bloque_rechazar').show();
                
                $('#input_lugar').prop('required', false);
                $('#input_motivo').prop('required', true);
                
                $('#btn_confirmar').removeClass('btn-success').addClass('btn-danger').text('Rechazar Solicitud');
            }

            $('#modalRespuesta').modal('show');
        });

        // 2. Manejo del Modal ASISTENCIA (Finalizar)
        $(document).on('click', '.btn-asistencia', function() {
            var id = $(this).data('id');
            var alumno = $(this).data('alumno');

            $('#asis_id').val(id);
            $('#asis_alumno').text(alumno);
            $('#modalAsistencia').modal('show');
        });
    });
</script>