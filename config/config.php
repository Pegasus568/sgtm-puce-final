<?php
// config/config.php

// 1. CONFIGURACIÓN DE BASE DE DATOS
// Ajusta el puerto si usas 3306 o 3307 según tu XAMPP
define('DB_HOST', 'localhost:3306'); 
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sgtm_puce_v2');
define('DB_CHARSET', 'utf8mb4');

// 2. RUTAS DEL SISTEMA (Frontend y Backend)

// BASE_URL: Para lógica (Controladores, Enlaces, Redirecciones)
// Nota: Incluye index.php?url= porque no usas .htaccess
define('BASE_URL', 'http://localhost/sgtm_v2/index.php?url=');

// ASSET_URL: Para recursos visuales (Imágenes, CSS, JS)
// Nota: Apunta directo a la carpeta raíz
define('ASSET_URL', 'http://localhost/sgtm_v2/');

?>