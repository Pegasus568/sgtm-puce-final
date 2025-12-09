<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1>Nueva Solicitud de Tutoría</h1>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button class="close" data-dismiss="alert">&times;</button>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="card card-primary">
                <div class="card-header"><h3 class="card-title">Formulario de Reserva</h3></div>
                
                <form action="<?php echo BASE_URL; ?>tutorias/guardar" method="POST">
                    <div class="card-body">
                        
                        <div class="form-group">
                            <label>Docente</label>
                            <select name="tutor_id" id="tutor_id" class="form-control select2" required style="width: 100%;">
                                <option value="">Seleccione un docente...</option>
                                <?php foreach($docentes as $d): ?>
                                    <?php if($d['rol'] == 'DOCENTE'): ?>
                                    <option value="<?php echo $d['id']; ?>">
                                        <?php echo htmlspecialchars($d['nombre']); ?> 
                                        (<?php echo htmlspecialchars($d['carrera'] ?? 'General'); ?>)
                                    </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Fecha Deseada</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required disabled>
                            <small class="text-muted" id="msg_fecha">Seleccione un docente primero.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hora de Inicio (Disponibles)</label>
                                    <select name="hora_inicio" id="hora_inicio" class="form-control" required disabled>
                                        <option value="">-- --</option>
                                    </select>
                                    <small id="loader_hora" class="text-info" style="display:none">Buscando disponibilidad...</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Duración</label>
                                    <select name="duracion" class="form-control">
                                        <option value="30">30 Minutos</option>
                                        <option value="60">1 Hora</option>
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
                            <label>Tema a Tratar</label>
                            <input type="text" name="tema" class="form-control" placeholder="Ej: Dudas sobre POO" required>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block">Confirmar Solicitud</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const selTutor = document.getElementById('tutor_id');
        const inpFecha = document.getElementById('fecha');
        const selHora = document.getElementById('hora_inicio');
        const loader = document.getElementById('loader_hora');
        const msgFecha = document.getElementById('msg_fecha');

        // 1. Activar fecha cuando hay tutor
        $(selTutor).on('change', function() { // Select2 usa jQuery events
            if(this.value) {
                inpFecha.disabled = false;
                msgFecha.innerText = "Solo días laborables (Lun-Vie)";
                inpFecha.value = ''; 
                selHora.innerHTML = '<option value="">-- --</option>';
            } else {
                inpFecha.disabled = true;
            }
        });

        // 2. Buscar horas cuando cambia fecha
        inpFecha.addEventListener('change', function() {
            const tutorId = $(selTutor).val();
            const fecha = this.value;

            // Validar fin de semana
            const diaSemana = new Date(fecha).getUTCDay();
            if(diaSemana === 0 || diaSemana === 6) {
                alert("Los fines de semana no hay atención.");
                this.value = '';
                return;
            }

            if(tutorId && fecha) {
                selHora.disabled = true;
                loader.style.display = 'block';

                // Llamada a la API creada en el controlador
                // Usamos la constante BASE_URL que definimos en config (index.php?url=)
                const urlApi = "<?php echo BASE_URL; ?>tutorias/apiGetHorarios&docente_id=" + tutorId + "&fecha=" + fecha;

                fetch(urlApi)
                    .then(response => response.json())
                    .then(data => {
                        loader.style.display = 'none';
                        selHora.innerHTML = ''; // Limpiar
                        
                        if(data.length > 0) {
                            selHora.disabled = false;
                            data.forEach(hora => {
                                let opt = document.createElement('option');
                                opt.value = hora;
                                opt.innerText = hora;
                                selHora.appendChild(opt);
                            });
                        } else {
                            let opt = document.createElement('option');
                            opt.innerText = "Sin cupos disponibles";
                            selHora.appendChild(opt);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        loader.style.display = 'none';
                        alert("Error al cargar horarios.");
                    });
            }
        });
    });
</script>