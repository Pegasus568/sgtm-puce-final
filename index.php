<?php
// index.php - Punto de Entrada Principal (Router)

// 1. Cargar configuraciones globales
require_once 'config/config.php';
require_once 'config/database.php';

// 2. Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 3. Lógica de Enrutamiento (Router)
if (isset($_GET['url'])) {
    
    // Si hay una URL (ej: localhost/sgtm_v2/auth/login), la desglosamos
    $url = rtrim($_GET['url'], '/');
    $url = explode('/', $url);

    // El primer elemento es el Controlador (ej: Auth)
    $controllerName = ucfirst($url[0]) . 'Controller';
    
    // El segundo elemento es el Método (ej: login), por defecto es 'index'
    $methodName = isset($url[1]) ? $url[1] : 'index';
    
    // Los siguientes elementos son parámetros
    $params = array_slice($url, 2);

    // Ruta del archivo del controlador
    $controllerPath = 'controllers/' . $controllerName . '.php';

    // Verificar si el archivo existe
    if (file_exists($controllerPath)) {
        require_once $controllerPath;
        
        // Instanciar el controlador
        $controller = new $controllerName();
        
        // Verificar si el método existe dentro de la clase
        if (method_exists($controller, $methodName)) {
            // Llamar al método con sus parámetros
            call_user_func_array([$controller, $methodName], $params);
        } else {
            // Error 404: Método no encontrado
            echo "Error: El método <b>$methodName</b> no existe en <b>$controllerName</b>.";
        }
    } else {
        // Error 404: Controlador no encontrado
        echo "Error: El controlador <b>$controllerName</b> no existe.";
    }

} else {
    // ====================================================================
    // CASO POR DEFECTO (RAÍZ DEL PROYECTO)
    // ====================================================================
    // Aquí es donde cargamos la Landing Page en lugar del Login
    
    require_once 'controllers/HomeController.php';
    $controller = new HomeController();
    $controller->index();
    exit;
}
?>