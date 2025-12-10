<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1><i class="fas fa-print"></i> Generador de Reportes</h1>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <div class="card card-navy">
                <div class="card-header">
                    <h3 class="card-title">Filtros de Búsqueda</h3>
                </div>
                
                <form action="<?php echo BASE_URL; ?>reportes/generar" method="POST" target="_blank">
                    <div class="card-body">
                        
                        <div class="callout callout-info">
                            <p><i class="fas fa-info-circle"></i> <b>Nota:</b> Deje los campos vacíos para ver todo el historial.</p>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Desde:</label>
                                    <input type="date" name="fecha_ini" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Hasta:</label>
                                    <input type="date" name="fecha_fin" class="form-control">
                                </div>
                            </div>

                            <?php if($_SESSION['usuario_rol'] !== 'DOCENTE'): ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Docente:</label>
                                    <select name="docente_id" class="form-control select2">
                                        <option value="">-- Todos --</option>
                                        <?php foreach($docentes as $d): ?>
                                            <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if($_SESSION['usuario_rol'] !== 'ESTUDIANTE'): ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Estudiante:</label>
                                    <select name="estudiante_id" class="form-control select2">
                                        <option value="">-- Todos --</option>
                                        <?php foreach($estudiantes as $e): ?>
                                            <option value="<?php echo $e['id']; ?>">
                                                <?php echo htmlspecialchars($e['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Categoría / Tipo:</label>
                                    <select name="tipo_id" class="form-control">
                                        <option value="">-- Todas --</option>
                                        <?php foreach($tipos as $t): ?>
                                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Estado:</label>
                                    <select name="estado" class="form-control">
                                        <option value="">-- Todos --</option>
                                        <option value="REALIZADA">Realizadas (Asistió)</option>
                                        <option value="CONFIRMADA">Agendadas (Futuras)</option>
                                        <option value="PENDIENTE">Pendientes de Aprobar</option>
                                        <option value="CANCELADA">Canceladas</option>
                                        <option value="NO_ASISTIO">Faltas</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-danger btn-lg">
                            <i class="fas fa-file-pdf"></i> Generar Reporte PDF
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Inicializar Select2 para búsquedas rápidas
        $('.select2').select2({ theme: 'bootstrap4' });
    });
</script>