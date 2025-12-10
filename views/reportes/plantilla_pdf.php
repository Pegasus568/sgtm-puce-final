<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { width: 100%; border-bottom: 2px solid #004085; padding-bottom: 10px; margin-bottom: 20px; }
        .info-empresa { text-align: center; }
        h2 { margin: 0; font-size: 18px; color: #004085; text-transform: uppercase; }
        h3 { margin: 2px 0; font-size: 14px; color: #555; }
        .resumen { width: 100%; margin-bottom: 15px; background: #f9f9f9; padding: 10px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #004085; color: white; font-weight: bold; font-size: 11px; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .estado-ok { color: green; font-weight: bold; }
        .estado-fail { color: red; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #777; border-top: 1px solid #ccc; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="info-empresa">
            <h2>Pontificia Universidad Católica del Ecuador</h2>
            <h3>Sede Ambato</h3>
            <p>Reporte Oficial de Tutorías Académicas</p>
        </div>
    </div>

    <div class="resumen">
        <strong>Generado por:</strong> <?php echo $_SESSION['usuario_nombre']; ?> (<?php echo $_SESSION['usuario_rol']; ?>)<br>
        <strong>Fecha de Emisión:</strong> <?php echo date('d/m/Y H:i'); ?><br>
        <strong>Total Registros:</strong> <?php echo count($resultados); ?>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%">Fecha</th>
                <th style="width: 15%">Docente</th>
                <th style="width: 20%">Estudiante</th>
                <th style="width: 15%">Categoría</th>
                <th style="width: 15%">Tema / Observación</th>
                <th style="width: 10%">Estado</th>
                <th style="width: 15%">Firmas</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($resultados as $r): ?>
            <tr>
                <td>
                    <?php echo date('d/m/Y', strtotime($r['fecha'])); ?><br>
                    <?php echo substr($r['hora_inicio'],0,5); ?> - <?php echo substr($r['hora_fin'],0,5); ?>
                </td>
                <td><?php echo htmlspecialchars($r['docente_nom']); ?></td>
                <td>
                    <?php echo htmlspecialchars($r['estudiante_nom']); ?><br>
                    <small>CI: <?php echo htmlspecialchars($r['estudiante_ced'] ?? 'N/A'); ?></small>
                </td>
                <td><?php echo htmlspecialchars($r['tipo']); ?></td>
                <td>
                    <b>Tema:</b> <?php echo htmlspecialchars($r['tema']); ?><br>
                    <?php if(!empty($r['observaciones'])): ?>
                        <br><b>Obs:</b> <i><?php echo htmlspecialchars($r['observaciones']); ?></i>
                    <?php endif; ?>
                </td>
                <td>
                    <?php 
                    if($r['estado'] == 'REALIZADA') echo '<span class="estado-ok">REALIZADA</span>';
                    elseif($r['estado'] == 'NO_ASISTIO') echo '<span class="estado-fail">AUSENTE</span>';
                    else echo $r['estado'];
                    ?>
                </td>
                <td>
                    <br><br>
                    <div style="border-bottom:1px solid #000; width:80%;"></div>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if(empty($resultados)): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding: 20px;">No se encontraron registros con los filtros seleccionados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Sistema de Gestión de Tutorías y Mentorías (SGTM) v2.0 - Reporte Automático
    </div>
</body>
</html>