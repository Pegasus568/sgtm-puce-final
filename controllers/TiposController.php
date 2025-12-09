<?php
// controllers/TiposController.php
require_once 'models/TipoTutoria.php';

class TiposController {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Solo ADMIN
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'ADMIN') {
            header("Location: " . BASE_URL);
            exit;
        }
    }

    public function index() {
        $modelo = new TipoTutoria();
        
        // --- PROCESAR POST ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['action'] ?? '';

            if ($accion === 'crear') {
                if ($modelo->crear($_POST['nombre'], $_POST['color'])) {
                    $_SESSION['mensaje'] = "Tipo de tutoría creado.";
                } else {
                    $_SESSION['error'] = "Error: El nombre ya existe.";
                }
            }
            elseif ($accion === 'cambiar_estado') {
                $nuevo_estado = $_POST['estado_actual'] == 1 ? 0 : 1;
                $modelo->cambiarEstado($_POST['id'], $nuevo_estado);
            }
            elseif ($accion === 'editar') {
                 $modelo->actualizar($_POST['id'], $_POST['nombre'], $_POST['color']);
                 $_SESSION['mensaje'] = "Actualizado correctamente.";
            }
            
            // Recargar para limpiar POST
            header("Location: " . BASE_URL . "tipos/index");
            exit;
        }

        // Datos para la vista
        $tipos = $modelo->obtenerTodos();
        
        require_once 'views/layouts/header.php';
        require_once 'views/layouts/sidebar.php';
        require_once 'views/tipos/index.php';
        require_once 'views/layouts/footer.php';
    }
}
?>