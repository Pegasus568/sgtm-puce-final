<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1>
                <?php echo ($_SESSION['usuario_rol'] == 'DOCENTE') ? '<i class="fas fa-users"></i> Agendar Sesión (Individual/Grupal)' : '<i class="fas fa-plus-circle"></i> Nueva Solicitud'; ?>
            </h1>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-ban"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="card card-primary card-outline">
                
                <form action="<?php echo BASE_URL; ?>tutorias/guardar" method="POST">
                    <div class="card-body">
                        
                        <div class="form-group">
                            <?php if($_SESSION['usuario_rol'] == 'DOCENTE'): ?>
                                <label>Seleccionar Estudiantes (Multiselección)</label>
                                <select name="estudiantes_ids[]" class="form-control select2" multiple="multiple" data-placeholder="Buscar estudiantes..." required style="width: 100%;">
                                    <?php foreach($usuarios_destino as $u): ?>
                                        <option value="<?php echo $u['id']; ?>">
                                            <?php echo htmlspecialchars($u['nombre']); ?> (<?php echo htmlspecialchars($u['carrera'] ?? 'Sin Carrera'); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Se creará una cita individual para cada estudiante seleccionado.</small>
                            
                            <?php else: ?>
                                <label>Seleccionar Docente</label>
                                <select name="tutor_id" id="tutor_id" class="form-control select2" required style="width: 100%;">
                                    <option value="">-- Buscar Docente --</option>
                                    <?php foreach($usuarios_destino as $u): ?>
                                        <option value="<?php echo $u['id']; ?>">
                                            <?php echo htmlspecialchars($u['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="date" name="fecha" id="fecha" class="form-control" 
                                           min="<?php echo date('Y-m-d', ($_SESSION['usuario_rol']=='DOCENTE' ? time() : strtotime('+1 day'))); ?>" 
                                           required <?php echo ($_SESSION['usuario_rol']=='ESTUDIANTE' ? 'disabled' : ''); ?>>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Hora Inicio</label>
                                    
                                    <?php if($_SESSION['usuario_rol'] == 'DOCENTE'): ?>
                                        <input type="time" name="hora_inicio" class="form-control" required>
                                    
                                    <?php else: ?>
                                        <select name="hora_inicio" id="hora_inicio" class="form-control" required disabled>
                                            <option value="">-- --</option>
                                        </select>
                                        <small id="loader_hora" class="text-info" style="display:none">Buscando disponibilidad...</small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Duración</label>
                                    <select name="duracion" class="form-control">
                                        <option value="30">30 Minutos</option>
                                        <option value="60">1 Hora</option>
                                        <option value="90">1.5 Horas</option>
                                        <option value="120">2 Horas</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipo de Actividad</label>
                                    <select name="tipo_id" class="form-control" required>
                                        <?php foreach($tipos as $t): ?>
                                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Modalidad</label>
                                    <select name="modalidad" class="form-control">
                                        <option value="PRESENCIAL">Presencial</option>
                                        <option value="VIRTUAL">Virtual</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Tema / Asunto</label>
                            <input type="text" name="tema" class="form-control" required placeholder="Ej: Revisión Tesis Cap. 1">
                        </div>
                        
                        <?php if($_SESSION['usuario_rol'] == 'DOCENTE'): ?>
                        <div class="form-group">
                            <label>Lugar / Enlace (Ya confirmado)</label>
                            <input type="text" name="lugar" class="form-control" required placeholder="Ej: Aula 101">
                        </div>
                        <?php endif; ?>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block">
                            <?php echo ($_SESSION['usuario_rol'] == 'DOCENTE') ? 'Agendar Citas Confirmadas' : 'Enviar Solicitud'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // 1. Inicializar Select2 (Búsqueda bonita)
        $('.select2').select2({ theme: 'bootstrap4' });

        // 2. Lógica AJAX SOLO para ESTUDIANTES
        // (Si el elemento #tutor_id existe, es que soy estudiante)
        if ($('#tutor_id').length > 0) {
            const selTutor = $('#tutor_id');
            const inpFecha = document.getElementById('fecha');
            const selHora = document.getElementById('hora_inicio');
            const loader = document.getElementById('loader_hora');

            selTutor.on('change', function() {
                if(this.value) {
                    inpFecha.disabled = false;
                    inpFecha.value = '';
                    selHora.innerHTML = '<option value="">-- --</option>';
                    selHora.disabled = true;
                } else {
                    inpFecha.disabled = true;
                }
            });

            inpFecha.addEventListener('change', function() {
                const tutorId = selTutor.val();
                const fecha = this.value;
                const diaSemana = new Date(fecha).getUTCDay();

                if(diaSemana === 0 || diaSemana === 6) {
                    alert("Fines de semana no disponibles.");
                    this.value = ''; return;
                }

                if(tutorId && fecha) {
                    selHora.disabled = true;
                    loader.style.display = 'block';
                    
                    // URL AJAX
                    const urlApi = "<?php echo BASE_URL; ?>tutorias/apiGetHorarios&docente_id=" + tutorId + "&fecha=" + fecha;

                    fetch(urlApi)
                        .then(res => res.json())
                        .then(data => {
                            loader.style.display = 'none';
                            selHora.innerHTML = '';
                            if(data.length > 0) {
                                selHora.disabled = false;
                                data.forEach(h => {
                                    let opt = document.createElement('option');
                                    opt.value = h; opt.innerText = h;
                                    selHora.appendChild(opt);
                                });
                            } else {
                                let opt = document.createElement('option');
                                opt.innerText = "Sin cupos";
                                selHora.appendChild(opt);
                            }
                        });
                }
            });
        }
    });
</script>