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
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Fecha</th>
                                    <th>Detalles</th>
                                    <th class="text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($pendientes)): ?>
                                    <tr><td colspan="4" class="text-center text-muted">No tienes solicitudes pendientes.</td></tr>
                                <?php else: ?>
                                    <?php foreach($pendientes as $p): ?>
                                    <tr>
                                        <td>
                                            <b><?php echo htmlspecialchars($p['contraparte'] ?? 'Estudiante'); ?></b><br>
                                            <small class="text-muted">Cod: <?php echo $p['codigo_reserva'] ?? '--'; ?></small>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($p['fecha'])); ?><br>
                                            <small><?php echo substr($p['hora_inicio'], 0, 5); ?> - <?php echo substr($p['hora_fin'], 0, 5); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge" style="background:<?php echo $p['color_etiqueta'] ?? '#ccc'; ?>; color:#fff">
                                                <?php echo htmlspecialchars($p['tipo'] ?? 'General'); ?>
                                            </span>
                                            <br>
                                            <small><?php echo htmlspecialchars($p['tema'] ?? ''); ?></small>
                                        </td>
                                        <td class="text-right">
                                            <button type="button" class="btn btn-xs btn-outline-info" onclick="verDetalles(<?php echo $p['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-success btn-sm btn-responder" 
                                                    data-id="<?php echo $p['id']; ?>" 
                                                    data-accion="confirmar"
                                                    title="Aceptar Solicitud">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm btn-responder" 
                                                    data-id="<?php echo $p['id']; ?>" 
                                                    data-accion="rechazar"
                                                    title="Rechazar Solicitud">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card card-primary card-outline mt-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Agenda Confirmada</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped text-nowrap">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Estudiante</th>
                                    <th>Lugar</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($agenda)): ?>
                                    <tr><td colspan="4" class="text-center text-muted">No hay citas programadas.</td></tr>
                                <?php else: ?>
                                    <?php foreach($agenda as $a): 
                                        $esPasado = (strtotime($a['fecha'] . ' ' . $a['hora_inicio']) <= time());
                                    ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($a['fecha'])); ?> <?php echo substr($a['hora_inicio'], 0, 5); ?></td>
                                        <td><?php echo htmlspecialchars($a['contraparte'] ?? 'Estudiante'); ?></td>
                                        <td><?php echo htmlspecialchars($a['lugar'] ?? 'Por definir'); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-xs btn-outline-info mr-1" onclick="verDetalles(<?php echo $a['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <?php if($esPasado && !in_array($a['estado'], ['REALIZADA','NO_ASISTIO'])): ?>
                                                <button class="btn btn-sm btn-primary btn-asistencia" 
                                                        data-id="<?php echo $a['id']; ?>"
                                                        data-alumno="<?php echo htmlspecialchars($a['contraparte'] ?? ''); ?>">
                                                    <i class="fas fa-clipboard-check"></i> Finalizar
                                                </button>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><?php echo $a['estado']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalRespuesta">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="<?php echo BASE_URL; ?>tutorias/responder" id="formResponder">
            <input type="hidden" name="id_tutoria" id="resp_id">
            <input type="hidden" name="accion" id="resp_accion">
            
            <div class="modal-header">
                <h5 class="modal-title" id="resp_titulo">Gestionar Solicitud</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            
            <div class="modal-body">
                <div id="bloque_confirmar" style="display:none">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Aceptarás esta tutoría.
                    </div>
                    <div class="form-group">
                        <label>Lugar / Enlace:</label>
                        <input type="text" name="lugar" class="form-control" placeholder="Ej: Aula 101, Zoom...">
                    </div>
                </div>

                <div id="bloque_rechazar" style="display:none">
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i> Rechazarás esta tutoría.
                    </div>
                    <div class="form-group">
                        <label>Motivo obligatorio <span class="text-danger">*</span>:</label>
                        <textarea name="motivo" id="input_motivo" class="form-control" rows="3" placeholder="Indica el motivo..."></textarea>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Confirmar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalAsistencia">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="<?php echo BASE_URL; ?>tutorias/asistencia">
            <input type="hidden" name="id_tutoria" id="asis_id">
            
            <div class="modal-header bg-navy">
                <h5 class="modal-title text-white"><i class="fas fa-check-double"></i> Finalizar Tutoría</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            
            <div class="modal-body">
                <p>Estudiante: <b id="asis_alumno"></b></p>
                
                <div class="text-center mb-3">
                    <label>¿Asistió el estudiante?</label><br>
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-outline-success active">
                            <input type="radio" name="asistio" value="1" checked> SÍ, ASISTIÓ
                        </label>
                        <label class="btn btn-outline-danger">
                            <input type="radio" name="asistio" value="0"> NO, FALTÓ
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Conclusiones / Bitácora:</label>
                    <textarea name="observaciones" class="form-control" rows="3" placeholder="Resumen de lo tratado en la sesión..."></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-block">Guardar y Finalizar</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Esperamos a que TODA la página (incluido footer con jQuery) cargue
    window.addEventListener('load', function() {
        
        if (typeof $ !== 'undefined') {
            
            // 1. LÓGICA BOTÓN RESPONDER (ACEPTAR/RECHAZAR)
            $(document).on('click', '.btn-responder', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var accion = $(this).data('accion');
                
                $('#resp_id').val(id);
                $('#resp_accion').val(accion);

                if(accion === 'confirmar') {
                    $('#resp_titulo').text('Aceptar Solicitud');
                    $('#bloque_confirmar').show();
                    $('#bloque_rechazar').hide();
                    $('#input_motivo').removeAttr('required'); 
                } else {
                    $('#resp_titulo').text('Rechazar Solicitud');
                    $('#bloque_confirmar').hide();
                    $('#bloque_rechazar').show();
                    $('#input_motivo').attr('required', true); 
                }
                $('#modalRespuesta').modal('show');
            });

            // 2. VALIDACIÓN FORMULARIO RESPUESTA
            $('#formResponder').on('submit', function(e) {
                var accion = $('#resp_accion').val();
                var motivo = $('#input_motivo').val().trim();
                
                if (accion === 'rechazar' && motivo === '') {
                    e.preventDefault();
                    alert("⚠️ Error: El motivo es obligatorio para rechazar.");
                    $('#input_motivo').focus();
                }
            });

            // 3. LÓGICA BOTÓN FINALIZAR (ASISTENCIA) - [AQUÍ ESTABA EL FALLO]
            $(document).on('click', '.btn-asistencia', function() {
                var id = $(this).data('id');
                var alumno = $(this).data('alumno');
                
                // Pasamos los datos al modal
                $('#asis_id').val(id);
                $('#asis_alumno').text(alumno);
                
                // Abrimos el modal
                $('#modalAsistencia').modal('show');
            });
            
        } else {
            console.error("Error: jQuery no detectado.");
        }
    });
</script>

<?php require_once 'views/layouts/modal_detalle.php'; ?>