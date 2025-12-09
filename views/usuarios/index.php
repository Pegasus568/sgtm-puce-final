<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>Gestión de Usuarios</h1></div>
                <div class="col-sm-6 text-right">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#modalCrear">
                        <i class="fas fa-user-plus"></i> Nuevo Usuario
                    </button>
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

            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Rol</th>
                                <th>Correo</th>
                                <th>Carrera</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($usuarios as $u): ?>
                            <tr>
                                <td>
                                    <i class="fas fa-user-circle text-muted"></i> 
                                    <?php echo htmlspecialchars($u['nombre']); ?>
                                </td>
                                <td>
                                    <?php 
                                        $color = 'secondary';
                                        if($u['rol']=='ADMIN') $color='danger';
                                        if($u['rol']=='DOCENTE') $color='info';
                                        if($u['rol']=='ESTUDIANTE') $color='success';
                                    ?>
                                    <span class="badge badge-<?php echo $color; ?>"><?php echo $u['rol']; ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($u['correo']); ?></td>
                                <td><?php echo htmlspecialchars($u['carrera'] ?? '-'); ?></td>
                                <td><span class="badge badge-<?php echo ($u['estado']=='ACTIVO')?'success':'warning'; ?>"><?php echo $u['estado']; ?></span></td>
                                <td>
                                    <?php if($u['id'] != $_SESSION['usuario_id']): ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar usuario?');">
                                        <input type="hidden" name="action" value="eliminar">
                                        <input type="hidden" name="id_usuario" value="<?php echo $u['id']; ?>">
                                        <button class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </form>
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

<div class="modal fade" id="modalCrear">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="<?php echo BASE_URL; ?>usuarios/index">
            <input type="hidden" name="action" value="crear">
            <div class="modal-header bg-primary">
                <h4 class="modal-title">Registrar Nuevo Usuario</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nombre Completo</label>
                    <input type="text" name="nombre" class="form-control" required placeholder="Ej: Juan Pérez">
                </div>
                <div class="row">
                    <div class="col-6">
                        <label>Cédula</label>
                        <input type="text" name="cedula" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control">
                    </div>
                </div>
                <div class="form-group mt-2">
                    <label>Correo Institucional</label>
                    <input type="email" name="correo" class="form-control" required placeholder="@pucesa.edu.ec">
                </div>
                <div class="form-group">
                    <label>Contraseña Temporal</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Rol</label>
                    <select name="rol" id="selectRol" class="form-control" required>
                        <option value="ESTUDIANTE">Estudiante</option>
                        <option value="DOCENTE">Docente</option>
                        <option value="ADMIN">Administrador</option>
                    </select>
                </div>

                <div id="camposAcademicos">
                    <div class="form-group">
                        <label>Carrera</label>
                        <select name="carrera_id" class="form-control">
                            <option value="">-- Seleccione --</option>
                            <?php foreach($carreras as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo $c['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" id="groupSemestre">
                        <label>Semestre Actual</label>
                        <select name="semestre" class="form-control">
                            <option value="">-- Seleccione --</option>
                            <option>1ro</option><option>2do</option><option>3ro</option>
                            <option>4to</option><option>5to</option><option>6to</option>
                            <option>7mo</option><option>8vo</option><option>9no</option>
                        </select>
                    </div>
                </div>

            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Usuario</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Esperar a que cargue el DOM
    document.addEventListener("DOMContentLoaded", function() {
        const selectRol = document.getElementById('selectRol');
        const boxAcademicos = document.getElementById('camposAcademicos');
        const groupSemestre = document.getElementById('groupSemestre');

        function toggleCampos() {
            if (selectRol.value === 'ADMIN') {
                boxAcademicos.style.display = 'none';
            } else {
                boxAcademicos.style.display = 'block';
                // Si es Docente, ocultamos Semestre (no aplica)
                if (selectRol.value === 'DOCENTE') {
                    groupSemestre.style.display = 'none';
                } else {
                    groupSemestre.style.display = 'block';
                }
            }
        }

        selectRol.addEventListener('change', toggleCampos);
        toggleCampos(); // Ejecutar al inicio
    });
</script>