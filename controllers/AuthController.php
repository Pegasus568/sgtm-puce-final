<?php
// controllers/AuthController.php

// Verificar que el modelo existe antes de cargarlo
if (!file_exists(__DIR__ . '/../models/Usuario.php')) {
    die("Error Crítico: No se encuentra models/Usuario.php");
}
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {
    
    public function index() {
        // Verificar sesión
        if (isset($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "tutorias/index");
            exit;
        }

        // Verificar que la vista existe
        $rutaVista = __DIR__ . '/../views/auth/login.php';
        if (!file_exists($rutaVista)) {
            die("Error Crítico: Falta la vista en " . $rutaVista);
        }
        
        require_once $rutaVista;
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = trim($_POST['correo'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($correo) || empty($password)) {
                $this->redirectConError("Campos vacíos.");
                return;
            }

            $userModel = new Usuario();
            $usuario = $userModel->buscarPorCorreo($correo);

            if ($usuario && password_verify($password, $usuario['password_hash'])) {
                if ($usuario['estado'] !== 'ACTIVO') {
                    $this->redirectConError("Cuenta inactiva.");
                    return;
                }

                // Login exitoso
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                $_SESSION['usuario_carrera'] = $usuario['carrera_id'];
                
                $userModel->registrarUltimoLogin($usuario['id']);

                header("Location: " . BASE_URL . "tutorias/index");
                exit;

            } else {
                $this->redirectConError("Credenciales incorrectas.");
            }
        }
    }

public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        
        // CORRECCIÓN: Redirigir explícitamente a auth/index
        header("Location: " . BASE_URL . "auth/index");
        exit;
    }

    private function redirectConError($mensaje) {
        $_SESSION['error_login'] = $mensaje;
        header("Location: " . BASE_URL);
        exit;
    }
}
?>