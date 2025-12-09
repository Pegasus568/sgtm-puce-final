<?php
// index.php
// 1. Configuración de Errores (Para que nunca salga pantalla blanca sin aviso)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config/config.php';
require_once 'config/database.php';

// 2. Captura y Limpieza de la URL
$url = isset($_GET['url']) ? $_GET['url'] : '';
$url = rtrim($url, '/'); // Quitar barras al final
$url = trim($url); // Quitar espacios

// 3. Regla de Oro: Si la URL está vacía, forzar el Login
if (empty($url)) {
    $url = 'auth/index';
}

$partes = explode('/', $url);

// 4. Determinar Controlador y Método
// Si $partes[0] es 'auth', el controlador es 'AuthController'
$nombreControlador = ucfirst(strtolower($partes[0])) . 'Controller';
$metodo = isset($partes[1]) && !empty($partes[1]) ? $partes[1] : 'index';
$parametro = $partes[2] ?? null;

// 5. Cargar el Controlador
$archivoControlador = 'controllers/' . $nombreControlador . '.php';

if (file_exists($archivoControlador)) {
    require_once $archivoControlador;
    $controlador = new $nombreControlador();
    
    if (method_exists($controlador, $metodo)) {
        if ($parametro) {
            $controlador->{$metodo}($parametro);
        } else {
            $controlador->{$metodo}();
        }
    } else {
        echo "Error: El método <b>$metodo</b> no existe en el controlador <b>$nombreControlador</b>.";
    }
} else {
    echo "Error 404: No se encuentra el controlador <b>$nombreControlador</b>.<br>Ruta buscada: $archivoControlador";
}
?>