<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>Catálogo de Tutorías</h1></div>
                <div class="col-sm-6 text-right">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#modalNuevo">
                        <i class="fas fa-plus"></i> Nuevo Tipo
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

            <div class="card card-outline card-purple">
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Color</th>
                                <th>Nombre de la Actividad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($tipos as $t): ?>
                            <tr>
                                <td>
                                    <span class="badge" style="background-color: <?php echo $t['color_etiqueta']; ?>; color: #fff; padding: 8px;">
                                        <?php echo $t['color_etiqueta']; ?>
                                    </span>
                                </td>
                                <td><b><?php echo htmlspecialchars($t['nombre']); ?></b></td>
                                <td>
                                    <?php if($t['activo']): ?>
                                        <span class="badge badge-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info btn-editar" 
                                            data-id="<?php echo $t['id']; ?>" 
                                            data-nombre="<?php echo htmlspecialchars($t['nombre']); ?>" 
                                            data-color="<?php echo $t['color_etiqueta']; ?>"
                                            data-toggle="modal" data-target="#modalEditar">
                                        <i class="fas fa-pen"></i>
                                    </button>

                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="cambiar_estado">
                                        <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                        <input type="hidden" name="estado_actual" value="<?php echo $t['activo']; ?>">
                                        <?php if($t['activo']): ?>
                                            <button class="btn btn-sm btn-outline-danger" title="Desactivar"><i class="fas fa-eye-slash"></i></button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-success" title="Activar"><i class="fas fa-eye"></i></button>
                                        <?php endif; ?>
                                    </form>
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

<div class="modal fade" id="modalNuevo">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <input type="hidden" name="action" value="crear">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Registrar Tipo de Actividad</h5>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" class="form-control" required placeholder="Ej: Refuerzo Académico">
                </div>
                <div class="form-group">
                    <label>Color Identificador</label>
                    <input type="color" name="color" class="form-control" value="#007bff" style="height: 45px;">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEditar">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <input type="hidden" name="action" value="editar">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-header bg-info">
                <h5 class="modal-title">Editar Actividad</h5>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Color</label>
                    <input type="color" name="color" id="edit_color" class="form-control" style="height: 45px;">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-info">Actualizar</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Script para cargar datos en el modal de edición
    $(document).on("click", ".btn-editar", function () {
        var id = $(this).data('id');
        var nombre = $(this).data('nombre');
        var color = $(this).data('color');
        
        $('#edit_id').val(id);
        $('#edit_nombre').val(nombre);
        $('#edit_color').val(color);
    });
</script>