<?php
// reportes.php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Solo Administrador
verificarRol(['ADMIN']);

// Filtros
$fecha_ini = $_GET['f_ini'] ?? date('Y-m-01'); // Inicio de mes por defecto
$fecha_fin = $_GET['f_fin'] ?? date('Y-m-t');  // Fin de mes por defecto
$carrera_id = $_GET['carrera'] ?? '';

// Construcción de Query Dinámico
$where_clause = " WHERE t.fecha BETWEEN '$fecha_ini' AND '$fecha_fin' ";
if ($carrera_id) {
    // Subconsulta para filtrar por carrera del estudiante
    $where_clause .= " AND t.estudiante_id IN (SELECT id FROM usuarios WHERE carrera_id = $carrera_id) ";
}

// 1. Estadísticas Generales (Contadores)
$sql_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN estado = 'REALIZADA' THEN 1 ELSE 0 END) as realizadas,
    SUM(CASE WHEN estado = 'CANCELADA' THEN 1 ELSE 0 END) as canceladas
    FROM tutorias t $where_clause";
$stats = $pdo->query($sql_stats)->fetch();

// 2. Datos para Gráfico (Por Estado)
$sql_chart = "SELECT estado, COUNT(*) as cantidad FROM tutorias t $where_clause GROUP BY estado";
$chart_data = $pdo->query($sql_chart)->fetchAll();
$labels = [];
$values = [];
foreach($chart_data as $cd) {
    $labels[] = $cd['estado'];
    $values[] = $cd['cantidad'];
}

// 3. Listado Detallado
$sql_list = "SELECT t.fecha, t.titulo, t.estado, 
             doc.nombre as docente, est.nombre as estudiante, car.nombre as carrera_est
             FROM tutorias t
             JOIN usuarios doc ON t.tutor_id = doc.id
             JOIN usuarios est ON t.estudiante_id = est.id
             LEFT JOIN carreras car ON est.carrera_id = car.id
             $where_clause
             ORDER BY t.fecha DESC";
$reporte_detallado = $pdo->query($sql_list)->fetchAll();

// Carreras para el select
$carreras = $pdo->query("SELECT * FROM carreras")->fetchAll();

require_once 'includes/header.php';
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1>Panel de Reportes Administrativos</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Desde:</label>
                                <input type="date" name="f_ini" class="form-control" value="<?php echo $fecha_ini; ?>">
                            </div>
                            <div class="col-md-3">
                                <label>Hasta:</label>
                                <input type="date" name="f_fin" class="form-control" value="<?php echo $fecha_fin; ?>">
                            </div>
                            <div class="col-md-3">
                                <label>Carrera:</label>
                                <select name="carrera" class="form-control">
                                    <option value="">-- Todas --</option>
                                    <?php foreach($carreras as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" <?php echo ($carrera_id == $c['id']) ? 'selected' : ''; ?>>
                                            <?php echo $c['nombre']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter"></i> Filtrar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Tutorías</span>
                            <span class="info-box-number"><?php echo $stats['total']; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Realizadas</span>
                            <span class="info-box-number"><?php echo $stats['realizadas']; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-danger">
                        <span class="info-box-icon"><i class="fas fa-times"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Canceladas</span>
                            <span class="info-box-number"><?php echo $stats['canceladas']; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Distribución por Estado</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="estadoChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Detalle de Registros</h3>
                        </div>
                        <div class="card-body">
                            <table id="tabla_reportes" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Docente</th>
                                        <th>Estudiante</th>
                                        <th>Carrera (Est)</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reporte_detallado as $r): ?>
                                    <tr>
                                        <td><?php echo $r['fecha']; ?></td>
                                        <td><?php echo $r['docente']; ?></td>
                                        <td><?php echo $r['estudiante']; ?></td>
                                        <td><?php echo $r['carrera_est']; ?></td>
                                        <td><?php echo $r['estado']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<script src="adminlte/plugins/chart.js/Chart.min.js"></script>

<link rel="stylesheet" href="adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="adminlte/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<script src="adminlte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="adminlte/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="adminlte/plugins/jszip/jszip.min.js"></script>
<script src="adminlte/plugins/pdfmake/pdfmake.min.js"></script>
<script src="adminlte/plugins/pdfmake/vfs_fonts.js"></script>
<script src="adminlte/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="adminlte/plugins/datatables-buttons/js/buttons.print.min.js"></script>

<script>
    // 1. Configuración del Gráfico
    var donutChartCanvas = $('#estadoChart').get(0).getContext('2d')
    var donutData        = {
      labels: <?php echo json_encode($labels); ?>,
      datasets: [
        {
          data: <?php echo json_encode($values); ?>,
          backgroundColor : ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'],
        }
      ]
    }
    var donutOptions     = {
      maintainAspectRatio : false,
      responsive : true,
    }
    new Chart(donutChartCanvas, {
      type: 'doughnut',
      data: donutData,
      options: donutOptions
    })

    // 2. Configuración de la Tabla con PDF
    $(function () {
        $("#tabla_reportes").DataTable({
            "responsive": true, 
            "lengthChange": false, 
            "autoWidth": false,
            "buttons": [
                {
                    extend: 'pdfHtml5',
                    title: 'Reporte de Tutorías - SGTM',
                    className: 'btn btn-danger',
                    text: '<i class="fas fa-file-pdf"></i> Exportar PDF'
                },
                {
                    extend: 'excelHtml5',
                    className: 'btn btn-success',
                    text: '<i class="fas fa-file-excel"></i> Excel'
                }
            ],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            }
        }).buttons().container().appendTo('#tabla_reportes_wrapper .col-md-6:eq(0)');
    });
</script>

<?php require_once 'includes/footer.php'; ?>