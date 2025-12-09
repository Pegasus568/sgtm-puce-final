<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Historial Global de Tutorías</h1>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <?php if(isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-warning alert-dismissible">
                    <button class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-trash"></i> <?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Listado Completo</h3>
                </div>
                <div class="card-body">
                    <table id="tablaTutorias" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Fecha</th>
                                <th>Estudiante</th>
                                <th>Docente</th>
                                <th>Tipo / Tema</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($tutorias as $t): ?>
                            <tr>
                                <td><small class="text-muted"><?php echo $t['codigo_reserva']; ?></small></td>
                                <td>
                                    <?php echo $t['fecha']; ?> <br>
                                    <small><?php echo substr($t['hora_inicio'], 0, 5); ?> - <?php echo substr($t['hora_fin'], 0, 5); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($t['nombre_estudiante']); ?></td>
                                <td><?php echo htmlspecialchars($t['nombre_docente']); ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo $t['color_etiqueta']; ?>; color: #fff;">
                                        <?php echo $t['nombre_tipo']; ?>
                                    </span>
                                    <br>
                                    <small><?php echo htmlspecialchars($t['tema']); ?></small>
                                </td>
                                <td>
                                    <?php 
                                        $estado = $t['estado'];
                                        $clase = 'secondary';
                                        if($estado == 'CONFIRMADA') $clase = 'primary';
                                        if($estado == 'PENDIENTE') $clase = 'warning';
                                        if($estado == 'REALIZADA') $clase = 'success';
                                        if($estado == 'CANCELADA') $clase = 'danger';
                                    ?>
                                    <span class="badge badge-<?php echo $clase; ?>"><?php echo $estado; ?></span>
                                </td>
                                <td class="text-center">
                                    <form method="POST" onsubmit="return confirm('ATENCIÓN: Esta acción no se puede deshacer.\n¿Borrar esta tutoría de la base de datos?');">
                                        <input type="hidden" name="action" value="eliminar">
                                        <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                        <button class="btn btn-sm btn-danger" title="Eliminar Definitivamente">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
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

<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(function () {
        $("#tablaTutorias").DataTable({
            "responsive": true, 
            "lengthChange": false, 
            "autoWidth": false,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [[ 1, "desc" ]] // Ordenar por fecha descendente
        });
    });
</script>