<?php
// includes/auth.php

// Iniciar sesión solo si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Si no hay usuario en sesión, mandar al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// 2. Función para verificar roles permitidos en una página
// Uso: verificarRol(['ADMIN']); o verificarRol(['DOCENTE', 'ADMIN']);
function verificarRol($rolesPermitidos) {
    // Convertir a array si se pasa un string único
    if (!is_array($rolesPermitidos)) {
        $rolesPermitidos = [$rolesPermitidos];
    }

    // Verificar si el rol del usuario actual está en la lista permitida
    if (!in_array($_SESSION['usuario_rol'], $rolesPermitidos)) {
        // Redirigir a una página de error o al inicio con mensaje
        // (Asegúrate de que index.php maneje este parámetro si quieres mostrar una alerta)
        header("Location: index.php?error=acceso_denegado"); 
        exit;
    }
}
?>