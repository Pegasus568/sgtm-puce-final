<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1>Mis Solicitudes</h1>
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
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="card card-primary card-outline">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped text-nowrap">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Docente</th>
                                    <th>Tema</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($citas)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            No tienes solicitudes registradas.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($citas as $c): 
                                        // 1. LÓGICA DE TIEMPO (NUEVO)
                                        // Calculamos cuándo es la cita en formato UNIX timestamp
                                        $fechaCita = strtotime($c['fecha'] . ' ' . $c['hora_inicio']);
                                        // Calculamos el límite (Ahora + 24 horas)
                                        $limiteCancelacion = time() + 86400; // 86400 seg = 24 horas

                                        // ¿Es cancelable por tiempo? (La cita debe ser DESPUÉS del límite)
                                        $enTiempo = ($fechaCita > $limiteCancelacion);

                                        // ¿Es cancelable por estado?
                                        $estadoActivo = ($c['estado'] == 'PENDIENTE' || $c['estado'] == 'CONFIRMADA');
                                    ?>
                                    <tr>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($c['fecha'])); ?><br>
                                            <small class="text-muted"><?php echo substr($c['hora_inicio'],0,5); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($c['contraparte'] ?? 'Docente'); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($c['tema'] ?? 'Sin tema'); ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $badge = 'secondary';
                                                if($c['estado']=='CONFIRMADA') $badge='primary';
                                                if($c['estado']=='PENDIENTE') $badge='warning';
                                                if($c['estado']=='RECHAZADA' || $c['estado']=='CANCELADA') $badge='danger';
                                                if($c['estado']=='REALIZADA') $badge='success';
                                            ?>
                                            <span class="badge badge-<?php echo $badge; ?>">
                                                <?php echo $c['estado']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" onclick="verDetalles(<?php echo $c['id']; ?>)" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <?php if($estadoActivo): ?>
                                                <?php if($enTiempo): ?>
                                                    <button type="button" class="btn btn-sm btn-danger btn-cancelar" 
                                                            data-id="<?php echo $c['id']; ?>"
                                                            title="Cancelar Cita">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-sm btn-secondary" 
                                                            disabled 
                                                            title="No se puede cancelar con menos de 24h de anticipación">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                <?php endif; ?>
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

<div class="modal fade" id="modalCancelar">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="<?php echo BASE_URL; ?>tutorias/cancelar">
            <input type="hidden" name="id_tutoria" id="cancel_id">
            
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white">
                    <i class="fas fa-exclamation-triangle"></i> Cancelar Tutoría
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            
            <div class="modal-body">
                <p>¿Estás seguro que deseas cancelar esta tutoría?</p>
                
                <div class="form-group">
                    <label>Motivo de la cancelación <span class="text-danger">*</span></label>
                    <textarea name="motivo_cancelacion" class="form-control" rows="3" required 
                              placeholder="Escribe la razón por la que no podrás asistir..."></textarea>
                </div>
                
                <div class="alert alert-warning text-sm">
                    <i class="fas fa-clock"></i> Debes cancelar con al menos 24h de anticipación.
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-danger">Confirmar Cancelación</button>
            </div>
        </form>
    </div>
</div>

<script>
    window.addEventListener('load', function() {
        if (typeof $ !== 'undefined') {
            $(document).on('click', '.btn-cancelar', function() {
                var idCita = $(this).data('id');
                $('#cancel_id').val(idCita);
                $('#modalCancelar').modal('show');
            });
        }
    });
</script>

<?php require_once 'views/layouts/modal_detalle.php'; ?>