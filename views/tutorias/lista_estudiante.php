<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Mis Solicitudes</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?php echo BASE_URL; ?>tutorias/solicitar" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Nueva Solicitud
                    </a>
                </div>
            </div>
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

            <div class="card card-outline card-primary">
                <div class="card-body p-0">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Docente</th>
                                <th>Fecha y Hora</th>
                                <th>Tipo / Tema</th>
                                <th>Modalidad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($citas as $cita): ?>
                            <tr>
                                <td>
                                    <span class="text-muted text-sm"><?php echo $cita['codigo_reserva']; ?></span>
                                </td>
                                <td>
                                    <b><?php echo htmlspecialchars($cita['contraparte']); ?></b>
                                </td>
                                <td>
                                    <?php 
                                        $fechaObj = new DateTime($cita['fecha'] . ' ' . $cita['hora_inicio']);
                                        echo $fechaObj->format('d/m/Y'); 
                                    ?>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo substr($cita['hora_inicio'], 0, 5); ?> - 
                                        <?php echo substr($cita['hora_fin'], 0, 5); ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo $cita['color_etiqueta']; ?>; color: #fff;">
                                        <?php echo htmlspecialchars($cita['tipo']); ?>
                                    </span>
                                    <br>
                                    <small><?php echo htmlspecialchars($cita['tema']); ?></small>
                                </td>
                                <td>
                                    <?php if($cita['modalidad'] === 'VIRTUAL'): ?>
                                        <span class="badge badge-info"><i class="fas fa-video"></i> Virtual</span>
                                    <?php else: ?>
                                        <span class="badge badge-light"><i class="fas fa-building"></i> Presencial</span>
                                    <?php endif; ?>
                                    
                                    <?php if($cita['estado'] == 'CONFIRMADA' && !empty($cita['lugar'])): ?>
                                        <div class="text-success text-xs mt-1">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($cita['lugar']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                        $est = $cita['estado'];
                                        $badge = 'secondary';
                                        if($est == 'PENDIENTE') $badge = 'warning';
                                        if($est == 'CONFIRMADA') $badge = 'primary';
                                        if($est == 'RECHAZADA' || $est == 'CANCELADA') $badge = 'danger';
                                        if($est == 'REALIZADA') $badge = 'success';
                                    ?>
                                    <span class="badge badge-<?php echo $badge; ?>"><?php echo $est; ?></span>
                                    
                                    <?php if($est == 'RECHAZADA' && !empty($cita['motivo_rechazo'])): ?>
                                        <br><small class="text-danger" data-toggle="tooltip" title="<?php echo htmlspecialchars($cita['motivo_rechazo']); ?>">
                                            Ver motivo <i class="fas fa-info-circle"></i>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                        // Regla de Negocio: Solo cancelar si faltan > 24 horas y no ha pasado
                                        $inicioTimestamp = strtotime($cita['fecha'] . ' ' . $cita['hora_inicio']);
                                        $ahora = time();
                                        $horasRestantes = ($inicioTimestamp - $ahora) / 3600;

                                        if(in_array($est, ['PENDIENTE', 'CONFIRMADA']) && $horasRestantes > 24): 
                                    ?>
                                        <form method="POST" action="<?php echo BASE_URL; ?>tutorias/cancelar" onsubmit="return confirm('¿Estás seguro de cancelar esta solicitud?');">
                                            <input type="hidden" name="id_tutoria" value="<?php echo $cita['id']; ?>">
                                            <button class="btn btn-sm btn-outline-danger" title="Cancelar Solicitud">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    <?php elseif($est == 'CANCELADA'): ?>
                                        <span class="text-muted text-xs">Cancelada</span>
                                    <?php elseif($horasRestantes <= 24 && $est != 'REALIZADA' && $est != 'RECHAZADA'): ?>
                                        <span class="text-muted text-xs" title="Menos de 24h"><i class="fas fa-lock"></i> Bloqueado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if(empty($citas)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-folder-open fa-3x text-gray-300"></i>
                                    <p class="mt-2 text-muted">No tienes solicitudes de tutoría registradas.</p>
                                    <a href="<?php echo BASE_URL; ?>tutorias/solicitar" class="btn btn-primary btn-sm">Crear la primera</a>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>