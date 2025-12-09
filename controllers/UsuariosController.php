<?php
// controllers/UsuariosController.php
require_once 'models/Usuario.php';
require_once 'models/Carrera.php'; // Importamos el modelo de carreras

class UsuariosController {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'ADMIN') {
            header("Location: " . BASE_URL . "tutorias/index");
            exit;
        }
    }

    public function index() {
        $userModel = new Usuario();
        $carreraModel = new Carrera();

        // 1. Procesar Eliminación
        if (isset($_POST['action']) && $_POST['action'] === 'eliminar') {
            $userModel->eliminar($_POST['id_usuario']);
            $_SESSION['mensaje'] = "Usuario eliminado correctamente.";
            header("Location: " . BASE_URL . "usuarios/index");
            exit;
        }

        // 2. Procesar Creación
        if (isset($_POST['action']) && $_POST['action'] === 'crear') {
            $datos = [
                'nombre'   => trim($_POST['nombre']),
                'correo'   => trim($_POST['correo']),
                'password' => $_POST['password'],
                'rol'      => $_POST['rol'],
                'cedula'   => $_POST['cedula'],
                'telefono' => $_POST['telefono'],
                'carrera_id' => !empty($_POST['carrera_id']) ? $_POST['carrera_id'] : null,
                'semestre'   => !empty($_POST['semestre']) ? $_POST['semestre'] : null
            ];

            $res = $userModel->crear($datos);

            if ($res === true) {
                $_SESSION['mensaje'] = "Usuario creado exitosamente.";
            } else {
                $_SESSION['error'] = "Error: " . $res;
            }
            header("Location: " . BASE_URL . "usuarios/index");
            exit;
        }

        // 3. Cargar Datos para la Vista
        $usuarios = $userModel->obtenerTodos();
        $carreras = $carreraModel->obtenerTodas(); // Para el select del modal
        
        require_once 'views/layouts/header.php';
        require_once 'views/layouts/sidebar.php';
        require_once 'views/usuarios/index.php';
        require_once 'views/layouts/footer.php';
    }
}
?>