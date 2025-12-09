<?php
// controllers/DocenteController.php
require_once 'models/Horario.php';

class DocenteController {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Seguridad: Solo DOCENTE (o Admin para pruebas)
        if (!isset($_SESSION['usuario_rol']) || 
           ($_SESSION['usuario_rol'] !== 'DOCENTE' && $_SESSION['usuario_rol'] !== 'ADMIN')) {
            header("Location: " . BASE_URL . "tutorias/index");
            exit;
        }
    }

    // Acción para configurar horarios
    public function horarios() {
        $horarioModel = new Horario();
        $mensaje = "";
        $error = "";

        // Procesar POST (Agregar o Eliminar)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            
            if ($_POST['action'] === 'agregar') {
                $res = $horarioModel->agregar(
                    $_SESSION['usuario_id'], 
                    $_POST['dia'], 
                    $_POST['inicio'], 
                    $_POST['fin'], 
                    trim($_POST['lugar'])
                );

                if ($res === true) {
                    $mensaje = "Horario agregado correctamente.";
                } else {
                    $error = $res;
                }
            } 
            elseif ($_POST['action'] === 'eliminar') {
                $horarioModel->eliminar($_POST['id_horario'], $_SESSION['usuario_id']);
                $mensaje = "Bloque de horario eliminado.";
            }
        }

        // Obtener datos frescos para la vista
        $mis_horarios = $horarioModel->obtenerPorDocente($_SESSION['usuario_id']);
        
        // Cargar Vistas
        require_once 'views/layouts/header.php';
        require_once 'views/layouts/sidebar.php';
        require_once 'views/docente/horarios.php';
        require_once 'views/layouts/footer.php';
    }
}
?>