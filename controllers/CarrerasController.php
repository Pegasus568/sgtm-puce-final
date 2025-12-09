<?php
// controllers/CarrerasController.php
require_once 'models/Carrera.php';

class CarrerasController {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Seguridad: Solo ADMIN
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'ADMIN') {
            header("Location: " . BASE_URL);
            exit;
        }
    }

    public function index() {
        $modelo = new Carrera();
        $mensaje = "";
        $error = "";

        // Capturar ID de carrera seleccionada de la URL
        $id_seleccionada = $_GET['id'] ?? null;
        $materias = [];
        $carrera_actual = null;

        // --- PROCESAR ACCIONES POST ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['action'] ?? '';

            // 1. Nueva Carrera
            if ($accion === 'nueva_carrera') {
                if ($modelo->crear($_POST['nombre'], $_POST['codigo'])) {
                    $mensaje = "Carrera creada.";
                } else {
                    $error = "Error al crear carrera (posible código duplicado).";
                }
            }
            // 2. Eliminar Carrera
            elseif ($accion === 'eliminar_carrera') {
                $modelo->eliminar($_POST['id_carrera']);
                $mensaje = "Carrera eliminada.";
                $id_seleccionada = null; // Resetear selección
            }
            // 3. Nueva Materia
            elseif ($accion === 'nueva_materia') {
                if ($modelo->crearMateria($_POST['carrera_id'], $_POST['nombre'], $_POST['semestre'])) {
                    $mensaje = "Materia agregada.";
                    $id_seleccionada = $_POST['carrera_id']; // Mantener selección
                }
            }
            // 4. Eliminar Materia
            elseif ($accion === 'eliminar_materia') {
                $modelo->eliminarMateria($_POST['id_materia']);
                $mensaje = "Materia eliminada.";
                $id_seleccionada = $_POST['carrera_ref']; // Mantener selección
            }
        }

        // --- CARGAR DATOS PARA VISTA ---
        $carreras = $modelo->obtenerTodas();
        
        if ($id_seleccionada) {
            $carrera_actual = $modelo->obtenerPorId($id_seleccionada);
            if ($carrera_actual) {
                $materias = $modelo->obtenerMaterias($id_seleccionada);
            }
        }

        require_once 'views/layouts/header.php';
        require_once 'views/layouts/sidebar.php';
        require_once 'views/carreras/index.php';
        require_once 'views/layouts/footer.php';
    }
}
?>