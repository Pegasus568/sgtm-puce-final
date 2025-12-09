<?php
// config/config.php

// MODO DEPURACIÓN (Muestra errores si los hay)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// [IMPORTANTE] Definimos la URL con el formato "index.php?url="
// Esto obliga al sistema a usar rutas manuales y evita el error 404
define('BASE_URL', 'http://localhost/sgtm_v2/index.php?url=');

// Credenciales de Base de Datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sgtm_puce_v2');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

date_default_timezone_set('America/Guayaquil');
?>