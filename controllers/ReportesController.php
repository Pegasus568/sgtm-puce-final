<?php
// controllers/ReportesController.php
require_once 'models/Estadistica.php';

class ReportesController {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Seguridad: Solo ADMIN
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'ADMIN') {
            header("Location: " . BASE_URL);
            exit;
        }
    }

    public function index() {
        $stats = new Estadistica();
        
        // Obtener datos crudos
        $porEstado = $stats->obtenerPorEstado();
        $porCarrera = $stats->obtenerPorCarrera();
        $topDocentes = $stats->obtenerTopDocentes();
        $generales = $stats->obtenerTotales();

        // Calcular Tasa de Cancelación %
        $canceladas = 0;
        foreach($porEstado as $e) {
            if($e['estado'] === 'CANCELADA') $canceladas = $e['total'];
        }
        $generales['tasa_cancelacion'] = ($generales['total_citas'] > 0) 
            ? round(($canceladas / $generales['total_citas']) * 100, 1) 
            : 0;

        // Preparar datos para Chart.js (Arrays simples)
        $labelsEstado = []; $dataEstado = [];
        foreach($porEstado as $item) {
            $labelsEstado[] = $item['estado'];
            $dataEstado[] = $item['total'];
        }

        $labelsCarrera = []; $dataCarrera = [];
        foreach($porCarrera as $item) {
            $labelsCarrera[] = $item['carrera'];
            $dataCarrera[] = $item['total'];
        }

        require_once 'views/layouts/header.php';
        require_once 'views/layouts/sidebar.php';
        require_once 'views/admin/reportes.php';
        require_once 'views/layouts/footer.php';
    }
}
?>