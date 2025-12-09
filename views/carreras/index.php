<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Oferta Académica</h1>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <?php if($mensaje): ?><div class="alert alert-success"><?php echo $mensaje; ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

            <div class="row">
                
                <div class="col-md-5">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Carreras</h3>
                            <div class="card-tools">
                                <button class="btn btn-xs btn-success" data-toggle="modal" data-target="#modalCarrera"><i class="fas fa-plus"></i> Nueva</button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover">
                                <tbody>
                                    <?php foreach($carreras as $c): ?>
                                    <tr class="<?php echo ($c['id'] == $id_seleccionada) ? 'bg-light' : ''; ?>">
                                        <td>
                                            <b><?php echo htmlspecialchars($c['nombre']); ?></b><br>
                                            <small class="text-muted">Cod: <?php echo $c['codigo']; ?></small>
                                        </td>
                                        <td class="text-right">
                                            <a href="<?php echo BASE_URL; ?>carreras/index&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Materias
                                            </a>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('¿Borrar carrera?');">
                                                <input type="hidden" name="action" value="eliminar_carrera">
                                                <input type="hidden" name="id_carrera" value="<?php echo $c['id']; ?>">
                                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <?php if($carrera_actual): ?>
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Malla: <b><?php echo $carrera_actual['nombre']; ?></b></h3>
                            <div class="card-tools">
                                <button class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modalMateria"><i class="fas fa-plus"></i> Agregar Materia</button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm">
                                <thead><tr><th>Semestre</th><th>Materia</th><th style="width:40px"></th></tr></thead>
                                <tbody>
                                    <?php foreach($materias as $m): ?>
                                    <tr>
                                        <td><span class="badge badge-secondary"><?php echo $m['semestre']; ?></span></td>
                                        <td><?php echo htmlspecialchars($m['nombre']); ?></td>
                                        <td>
                                            <form method="POST" onsubmit="return confirm('¿Borrar materia?');">
                                                <input type="hidden" name="action" value="eliminar_materia">
                                                <input type="hidden" name="id_materia" value="<?php echo $m['id']; ?>">
                                                <input type="hidden" name="carrera_ref" value="<?php echo $carrera_actual['id']; ?>">
                                                <button class="btn btn-xs btn-danger"><i class="fas fa-times"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($materias)): ?>
                                        <tr><td colspan="3" class="text-center text-muted">No hay materias registradas.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="callout callout-info">
                        <h5><i class="fas fa-info"></i> Seleccione una carrera</h5>
                        <p>Haga clic en el botón "Materias" de la lista izquierda para gestionar su malla curricular.</p>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCarrera">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <input type="hidden" name="action" value="nueva_carrera">
            <div class="modal-header bg-primary text-white"><h5 class="modal-title">Nueva Carrera</h5><button class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="form-group"><label>Nombre</label><input type="text" name="nombre" class="form-control" required></div>
                <div class="form-group"><label>Código (Siglas)</label><input type="text" name="codigo" class="form-control" required></div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary">Guardar</button></div>
        </form>
    </div>
</div>

<?php if($carrera_actual): ?>
<div class="modal fade" id="modalMateria">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <input type="hidden" name="action" value="nueva_materia">
            <input type="hidden" name="carrera_id" value="<?php echo $carrera_actual['id']; ?>">
            <div class="modal-header bg-info text-white"><h5 class="modal-title">Nueva Materia</h5><button class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="form-group"><label>Nombre de Materia</label><input type="text" name="nombre" class="form-control" required></div>
                <div class="form-group"><label>Semestre</label>
                    <select name="semestre" class="form-control">
                        <option>1ro</option><option>2do</option><option>3ro</option><option>4to</option>
                        <option>5to</option><option>6to</option><option>7mo</option><option>8vo</option><option>9no</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-info">Agregar</button></div>
        </form>
    </div>
</div>
<?php endif; ?>