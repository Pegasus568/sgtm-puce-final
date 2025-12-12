<?php
// config/database.php

// [IMPORTANTE] Cargamos las variables del archivo config.php
require_once __DIR__ . '/config.php';

class Database {
    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                // Verificación de seguridad: ¿Existen las constantes?
                if (!defined('DB_HOST')) {
                    throw new Exception("Las constantes de conexión (DB_HOST) no están definidas. Revise config/config.php");
                }

                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                
            } catch (PDOException $e) {
                // Error de conexión a MySQL
                die("<div style='background:#f8d7da; color:#721c24; padding:20px; font-family:sans-serif;'>
                        <h3>Error Crítico de Base de Datos</h3>
                        <p>No se pudo conectar al servidor MySQL.</p>
                        <ul>
                            <li>Verifique que XAMPP (MySQL) esté en VERDE.</li>
                            <li>Verifique el puerto en config.php (3306 o 3307).</li>
                        </ul>
                        <small>Detalle técnico: " . $e->getMessage() . "</small>
                     </div>");
            } catch (Exception $e) {
                // Error de configuración (Faltan constantes)
                die("<div style='background:#fff3cd; color:#856404; padding:20px; font-family:sans-serif;'>
                        <h3>Error de Configuración</h3>
                        <p>" . $e->getMessage() . "</p>
                     </div>");
            }
        }
        return self::$pdo;
    }
}
?>