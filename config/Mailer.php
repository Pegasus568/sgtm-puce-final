<?php
// config/Mailer.php

class Mailer {
    
    // Simula el envío guardando el HTML en un archivo
    public static function enviar($para_email, $para_nombre, $asunto, $cuerpo) {
        
        $fecha = date('Y-m-d H:i:s');
        
        // Plantilla HTML básica
        $html = "
        <div style='border: 1px solid #ccc; padding: 20px; margin: 20px; font-family: Arial;'>
            <div style='background: #007bff; color: white; padding: 10px;'>
                <strong>[SIMULACIÓN DE ENVÍO] - $fecha</strong>
            </div>
            <p><strong>Para:</strong> $para_nombre ($para_email)</p>
            <p><strong>Asunto:</strong> $asunto</p>
            <hr>
            <div style='background: #f9f9f9; padding: 15px;'>
                $cuerpo
            </div>
        </div>";

        // Guardar en el archivo de log (append)
        $archivo = __DIR__ . '/../emails_log.html';
        file_put_contents($archivo, $html, FILE_APPEND);

        return true; // Simulamos que siempre se envía bien
    }
}
?>