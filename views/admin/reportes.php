<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>Reportes Estadísticos</h1></div>
                <div class="col-sm-6 text-right">
                    <button onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print"></i> Imprimir / Guardar PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Tutorías</span>
                            <span class="info-box-number"><?php echo $generales['total_citas']; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Usuarios Activos</span>
                            <span class="info-box-number"><?php echo $generales['total_usuarios']; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Tasa Cancelación</span>
                            <span class="info-box-number"><?php echo $generales['tasa_cancelacion']; ?>%</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Estado de las Solicitudes</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="pieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Demanda por Carrera</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark">
                            <h3 class="card-title">Top Docentes Más Solicitados</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Docente</th>
                                        <th>Área/Carrera</th>
                                        <th class="text-center">Total Citas</th>
                                        <th style="width: 40%">Barra de Carga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($topDocentes as $doc): 
                                        $porcentaje = ($generales['total_citas'] > 0) ? ($doc['total'] / $generales['total_citas']) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($doc['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($doc['carrera'] ?? 'General'); ?></td>
                                        <td class="text-center"><span class="badge bg-primary"><?php echo $doc['total']; ?></span></td>
                                        <td>
                                            <div class="progress progress-xs">
                                                <div class="progress-bar bg-primary" style="width: <?php echo $porcentaje; ?>%"></div>
                                            </div>
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
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. Datos desde PHP
    var labelsEstado = <?php echo json_encode($labelsEstado); ?>;
    var dataEstado = <?php echo json_encode($dataEstado); ?>;
    var labelsCarrera = <?php echo json_encode($labelsCarrera); ?>;
    var dataCarrera = <?php echo json_encode($dataCarrera); ?>;

    // 2. Gráfico de Pastel (Estados)
    var pieCtx = document.getElementById('pieChart').getContext('2d');
    var pieChart = new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: labelsEstado,
            datasets: [{
                data: dataEstado,
                backgroundColor : ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'],
            }]
        }
    });

    // 3. Gráfico de Barras (Carreras)
    var barCtx = document.getElementById('barChart').getContext('2d');
    var barChart = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: labelsCarrera,
            datasets: [{
                label: 'Solicitudes',
                data: dataCarrera,
                backgroundColor: '#3c8dbc',
                borderColor: '#3c8dbc',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>