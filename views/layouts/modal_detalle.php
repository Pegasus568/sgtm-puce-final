<div class="modal fade" id="modalDetalleTutoria">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info"><h5 class="modal-title text-white">Detalles</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div id="loader_detalle" class="text-center"><div class="spinner-border"></div></div>
                <div id="contenido_detalle" style="display:none;">
                    <div class="row">
                        <div class="col-md-6 border-right">
                            <p><strong>Código:</strong> <span id="det_codigo" class="badge badge-dark"></span></p>
                            <p><strong>Tipo:</strong> <span id="det_tipo"></span></p>
                            <p><strong>Tema:</strong> <span id="det_tema"></span></p>
                            <p><strong>Fecha:</strong> <span id="det_fecha"></span> <span id="det_hora"></span></p>
                            <p><strong>Lugar:</strong> <span id="det_lugar"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Docente:</strong> <span id="det_docente"></span></p>
                            <p><strong>Estudiante:</strong> <span id="det_estudiante"></span></p>
                            <p><strong>Estado:</strong> <span id="det_estado"></span></p>
                            <div id="bloque_rechazo" class="alert alert-danger mt-2" style="display:none;"><span id="det_motivo"></span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>
<script>
    function verDetalles(id) {
        $('#modalDetalleTutoria').modal('show'); $('#loader_detalle').show(); $('#contenido_detalle').hide();
        $.getJSON('<?php echo BASE_URL; ?>tutorias/apiGetDetalle', {id:id}, function(data){
            if(data.error){alert(data.error);return;}
            $('#det_codigo').text(data.codigo_reserva); $('#det_tipo').text(data.tipo_nombre);
            $('#det_tema').text(data.tema); $('#det_fecha').text(data.fecha);
            $('#det_hora').text(data.hora_inicio.substring(0,5)); $('#det_lugar').text(data.lugar||'--');
            $('#det_docente').text(data.doc_nombre); $('#det_estudiante').text(data.est_nombre);
            $('#det_estado').text(data.estado);
            if(data.motivo_rechazo){$('#det_motivo').text(data.motivo_rechazo);$('#bloque_rechazo').show();}else{$('#bloque_rechazo').hide();}
            $('#loader_detalle').hide(); $('#contenido_detalle').fadeIn();
        });
    }
</script>