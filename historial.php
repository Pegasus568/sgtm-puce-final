<?php
// historial.php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Acceso: Estudiantes, Docentes y Admin
verificarRol(['ESTUDIANTE', 'DOCENTE', 'ADMIN']);

$rol = $_SESSION['usuario_rol'];
$user_id = $_SESSION['usuario_id'];

// Consulta base dependiendo del rol
$sql = "SELECT t.*, 
        u_sol.nombre as solicitante, 
        u_tut.nombre as tutor, 
        u_est.nombre as estudiante 
        FROM tutorias t
        JOIN usuarios u_sol ON t.solicitado_por = u_sol.id
        JOIN usuarios u_tut ON t.tutor_id = u_tut.id
        JOIN usuarios u_est ON t.estudiante_id = u_est.id
        WHERE 1=1 ";

// Filtros de seguridad por rol
$params = [];
if ($rol === 'ESTUDIANTE') {
    $sql .= " AND t.estudiante_id = ? ";
    $params[] = $user_id;
} elseif ($rol === 'DOCENTE') {
    $sql .= " AND t.tutor_id = ? ";
    $params[] = $user_id;
}
// Admin ve todo (no se agrega filtro)

// Filtro: Solo mostrar tutorías pasadas o finalizadas
$sql .= " AND (t.estado IN ('REALIZADA', 'CANCELADA', 'RECHAZADA', 'NO_ASISTIO') OR CONCAT(t.fecha, ' ', t.hora_fin) < NOW())";
$sql .= " ORDER BY t.fecha DESC, t.hora_inicio DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$historial = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1>Historial Académico</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Registro de Actividades Pasadas</h3>
                </div>
                <div class="card-body">
                    <table id="tabla_historial" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Rol</th>
                                <th>Contraparte</th>
                                <th>Tema</th>
                                <th>Estado</th>
                                <th>Observación/Motivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $h): 
                                // Definir qué nombre mostrar
                                $contraparte = ($rol === 'ESTUDIANTE') ? $h['tutor'] : $h['estudiante'];
                                $rol_contra = ($rol === 'ESTUDIANTE') ? 'Docente' : 'Estudiante';
                                
                                $color = 'secondary';
                                if($h['estado'] == 'REALIZADA') $color = 'success';
                                if($h['estado'] == 'CANCELADA' || $h['estado'] == 'RECHAZADA') $color = 'danger';
                            ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($h['fecha'])); ?></td>
                                <td><?php echo $h['tipo']; ?></td>
                                <td>
                                    <?php echo $contraparte; ?>
                                    <br><small class="text-muted"><?php echo $rol_contra; ?></small>
                                </td>
                                <td><?php echo $h['titulo']; ?></td>
                                <td><span class="badge badge-<?php echo $color; ?>"><?php echo $h['estado']; ?></span></td>
                                <td>
                                    <?php 
                                        echo $h['motivo_rechazo'] ? '<b>Rechazo:</b> '.$h['motivo_rechazo'] : '';
                                        echo $h['observaciones'] ? '<b>Obs:</b> '.$h['observaciones'] : '';
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<link rel="stylesheet" href="adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="adminlte/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

<script src="adminlte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="adminlte/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="adminlte/plugins/jszip/jszip.min.js"></script>
<script src="adminlte/plugins/pdfmake/pdfmake.min.js"></script>
<script src="adminlte/plugins/pdfmake/vfs_fonts.js"></script>
<script src="adminlte/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="adminlte/plugins/datatables-buttons/js/buttons.print.min.js"></script>

<script>
    $(function () {
        $("#tabla_historial").DataTable({
            "responsive": true, 
            "lengthChange": false, 
            "autoWidth": false,
            "order": [[ 0, "desc" ]],
            // Activar botones PDF y Excel
            "buttons": ["copy", "csv", "excel", "pdf", "print"],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            }
        }).buttons().container().appendTo('#tabla_historial_wrapper .col-md-6:eq(0)');
    });
</script>

<?php require_once 'includes/footer.php'; ?>