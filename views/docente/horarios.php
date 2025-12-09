<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0 text-dark"><i class="fas fa-clock"></i> Configuración de Disponibilidad</h1>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-ban"></i> <?php echo $error; ?>
                </div>
            <?php elseif(!empty($mensaje)): ?>
                <div class="alert alert-success alert-dismissible">
                    <button class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-check"></i> <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Nuevo Bloque de Atención</h3>
                        </div>
                        <form method="POST" action="<?php echo BASE_URL; ?>docente/horarios">
                            <input type="hidden" name="action" value="agregar">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Día de la Semana</label>
                                    <select name="dia" class="form-control" required>
                                        <option value="1">Lunes</option>
                                        <option value="2">Martes</option>
                                        <option value="3">Miércoles</option>
                                        <option value="4">Jueves</option>
                                        <option value="5">Viernes</option>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Desde</label>
                                            <input type="time" name="inicio" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Hasta</label>
                                            <input type="time" name="fin" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Ubicación Predeterminada</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        </div>
                                        <input type="text" name="lugar" class="form-control" placeholder="Ej: Oficina 302 o Zoom Link" required>
                                    </div>
                                    <small class="text-muted">Podrás cambiar esto en cada cita si es necesario.</small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-block">Agregar Horario</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Mis Horarios Registrados</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Día</th>
                                        <th>Intervalo</th>
                                        <th>Ubicación</th>
                                        <th class="text-center" style="width: 50px">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $diasSemana = [1=>'Lunes', 2=>'Martes', 3=>'Miércoles', 4=>'Jueves', 5=>'Viernes'];
                                    
                                    if(empty($mis_horarios)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                                                No tienes horarios configurados.<br>
                                                Los estudiantes no podrán encontrarte.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($mis_horarios as $h): ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-info text-md">
                                                    <?php echo $diasSemana[$h['dia_semana']]; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <b><?php echo substr($h['hora_inicio'], 0, 5); ?></b> 
                                                <small class="text-muted">a</small> 
                                                <b><?php echo substr($h['hora_fin'], 0, 5); ?></b>
                                            </td>
                                            <td><?php echo htmlspecialchars($h['ubicacion_default']); ?></td>
                                            <td class="text-center">
                                                <form method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este bloque horario?');">
                                                    <input type="hidden" name="action" value="eliminar">
                                                    <input type="hidden" name="id_horario" value="<?php echo $h['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
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
</div>