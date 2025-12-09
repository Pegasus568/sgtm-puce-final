<?php
// controllers/AdminTutoriasController.php
require_once 'models/Tutoria.php';

class AdminTutoriasController {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Seguridad: Bloquear si no es ADMIN
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'ADMIN') {
            header("Location: " . BASE_URL);
            exit;
        }
    }

    public function index() {
        $modelo = new Tutoria();
        
        // 1. Procesar Eliminación
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'eliminar') {
                $modelo->eliminarDefinitivamente($_POST['id']);
                $_SESSION['mensaje'] = "Registro eliminado permanentemente del sistema.";
                header("Location: " . BASE_URL . "admintutorias/index");
                exit;
            }
        }

        // 2. Cargar datos
        $tutorias = $modelo->obtenerTodas();
        
        // 3. Renderizar vista
        require_once 'views/layouts/header.php';
        require_once 'views/layouts/sidebar.php';
        require_once 'views/admin/tutorias.php';
        require_once 'views/layouts/footer.php';
    }
}
?>