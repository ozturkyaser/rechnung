<?php
/**
 * Globale Helper-Funktionen
 */

/**
 * URL generieren
 */
function url($path = '') {
    return APP_URL . '/' . ltrim($path, '/');
}

/**
 * Asset URL generieren
 */
function asset($path) {
    return APP_URL . '/' . ltrim($path, '/');
}

/**
 * Redirect
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * JSON Response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Escape HTML
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Prüfe ob User eingeloggt ist
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Hole aktuellen User
 */
function currentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Prüfe Benutzerrolle
 */
function hasRole($role) {
    $user = currentUser();
    if (!$user) return false;

    $roles = ['admin' => 4, 'accounting' => 3, 'sales' => 2, 'readonly' => 1];
    $userRole = $roles[$user['role']] ?? 0;
    $requiredRole = $roles[$role] ?? 0;

    return $userRole >= $requiredRole;
}

/**
 * CSRF Token generieren
 */
function csrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF Token validieren
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Flash Message setzen
 */
function setFlash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Flash Message holen
 */
function getFlash($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

/**
 * Formatiere Betrag
 */
function formatMoney($amount, $currency = 'EUR') {
    return number_format($amount, 2, ',', '.') . ' ' . $currency;
}

/**
 * Formatiere Datum
 */
function formatDate($date, $format = 'd.m.Y') {
    if (!$date) return '';
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    return $date->format($format);
}

/**
 * Generiere eindeutige ID (UUID v4)
 */
function generateUuid() {
    return \Ramsey\Uuid\Uuid::uuid4()->toString();
}

/**
 * Logge Nachricht
 */
function logMessage($message, $level = 'info', $file = 'app.log') {
    $logFile = STORAGE_PATH . '/logs/' . $file;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

/**
 * Validiere Email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitize String
 */
function sanitize($string) {
    return trim(strip_tags($string));
}

/**
 * Prüfe ob Request AJAX ist
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Hole Request Method
 */
function requestMethod() {
    return $_SERVER['REQUEST_METHOD'];
}

/**
 * Hole Request URI
 */
function requestUri() {
    return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
}

/**
 * Berechne MwSt aus Netto
 */
function calculateTax($netAmount, $taxRate) {
    return round($netAmount * ($taxRate / 100), 2);
}

/**
 * Berechne Brutto aus Netto
 */
function netToGross($netAmount, $taxRate) {
    return $netAmount + calculateTax($netAmount, $taxRate);
}

/**
 * Berechne Netto aus Brutto
 */
function grossToNet($grossAmount, $taxRate) {
    return round($grossAmount / (1 + ($taxRate / 100)), 2);
}

/**
 * Berechne Fälligkeitsdatum
 */
function calculateDueDate($invoiceDate, $paymentTermsDays) {
    $date = new DateTime($invoiceDate);
    $date->modify("+{$paymentTermsDays} days");
    return $date->format('Y-m-d');
}

/**
 * Prüfe ob Rechnung überfällig
 */
function isOverdue($dueDate) {
    if (!$dueDate) return false;
    $due = new DateTime($dueDate);
    $now = new DateTime();
    return $now > $due;
}

/**
 * Generiere Rechnungsnummer
 */
function generateInvoiceNumber($prefix, $currentNumber, $digits, $yearSeparator = true) {
    $number = str_pad($currentNumber + 1, $digits, '0', STR_PAD_LEFT);
    if ($yearSeparator) {
        return $prefix . '-' . date('Y') . '-' . $number;
    }
    return $prefix . '-' . $number;
}
