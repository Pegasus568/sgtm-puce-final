<?php
// controllers/HomeController.php

class HomeController {
    public function index() {
        // Carga la vista de bienvenida (Landing Page)
        require_once 'views/home/landing.php';
    }
}
?>