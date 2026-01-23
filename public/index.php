<?php
/**
 * Entry Point
 * Alle Requests gehen durch diese Datei
 */

// Lade Konfiguration
require_once __DIR__ . '/../config/config.php';

// Session starten
session_start();

// Lade Routen
$router = require_once __DIR__ . '/../config/routes.php';

// Dispatch Request
try {
    $router->dispatch();
} catch (Exception $e) {
    if (APP_DEBUG) {
        echo '<h1>Error</h1>';
        echo '<p>' . $e->getMessage() . '</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    } else {
        logMessage('Application Error: ' . $e->getMessage(), 'error');
        http_response_code(500);
        echo '<h1>Internal Server Error</h1>';
    }
}
