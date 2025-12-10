<?php
// controllers/ReportesController.php
require_once 'config/database.php';
require_once 'models/Usuario.php';
require_once 'models/TipoTutoria.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class ReportesController {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header("Location: " . BASE_URL); exit; }
    }

    public function index() {
        $userModel = new Usuario();
        $tipoModel = new TipoTutoria();
        $rol = $_SESSION['usuario_rol'];
        
        $todos = $userModel->obtenerTodos();
        $tipos = $tipoModel->obtenerTodos();
        $docentes = []; $estudiantes = [];

        if ($rol === 'ADMIN') {
            $docentes = array_filter($todos, function($u) { return $u['rol'] === 'DOCENTE'; });
            $estudiantes = array_filter($todos, function($u) { return $u['rol'] === 'ESTUDIANTE'; });
        } elseif ($rol === 'DOCENTE') {
            $estudiantes = array_filter($todos, function($u) { return $u['rol'] === 'ESTUDIANTE'; });
        } elseif ($rol === 'ESTUDIANTE') {
            $docentes = array_filter($todos, function($u) { return $u['rol'] === 'DOCENTE'; });
        }

        require_once 'views/layouts/header.php'; require_once 'views/layouts/sidebar.php';
        require_once 'views/reportes/index.php'; require_once 'views/layouts/footer.php';
    }

    public function generar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: " . BASE_URL . "reportes/index"); exit; }
        $db = Database::getConnection();
        
        // --- AQUÍ ESTABA EL ERROR: FALTABA TRAER LA CÉDULA ---
        $sql = "SELECT t.*, 
                       est.nombre as estudiante_nom, 
                       est.cedula as estudiante_ced,  /* <--- ESTA LÍNEA ES LA CLAVE */
                       doc.nombre as docente_nom, 
                       tt.nombre as tipo 
                FROM tutorias t
                JOIN usuarios est ON t.estudiante_id = est.id
                JOIN usuarios doc ON t.tutor_id = doc.id
                JOIN tipos_tutorias tt ON t.tipo_id = tt.id 
                WHERE 1=1 ";
        
        $params = [];

        if (!empty($_POST['fecha_ini'])) { $sql .= " AND t.fecha >= ?"; $params[] = $_POST['fecha_ini']; }
        if (!empty($_POST['fecha_fin'])) { $sql .= " AND t.fecha <= ?"; $params[] = $_POST['fecha_fin']; }

        if ($_SESSION['usuario_rol'] === 'DOCENTE') { $sql .= " AND t.tutor_id=?"; $params[] = $_SESSION['usuario_id']; }
        elseif (!empty($_POST['docente_id'])) { $sql .= " AND t.tutor_id=?"; $params[] = $_POST['docente_id']; }

        if ($_SESSION['usuario_rol'] === 'ESTUDIANTE') { $sql .= " AND t.estudiante_id=?"; $params[] = $_SESSION['usuario_id']; }
        elseif (!empty($_POST['estudiante_id'])) { $sql .= " AND t.estudiante_id=?"; $params[] = $_POST['estudiante_id']; }

        if (!empty($_POST['tipo_id'])) { $sql .= " AND t.tipo_id=?"; $params[] = $_POST['tipo_id']; }
        if (!empty($_POST['estado'])) { $sql .= " AND t.estado=?"; $params[] = $_POST['estado']; }

        $sql .= " ORDER BY t.fecha ASC";
        
        $stmt = $db->prepare($sql); 
        $stmt->execute($params);
        $resultados = $stmt->fetchAll();

        ob_start();
        include 'views/reportes/plantilla_pdf.php';
        $html = ob_get_clean();

        $options = new Options(); $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options); $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape'); $dompdf->render();
        $dompdf->stream("Reporte.pdf", ["Attachment" => false]);
    }
}
?>