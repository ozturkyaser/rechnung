<?php
/**
 * Hauptkonfigurationsdatei
 * Lädt .env und definiert globale Konstanten
 */

// Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Environment laden
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Basis-Pfade
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('CONFIG_PATH', ROOT_PATH . '/config');

// App-Konfiguration
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Rechnungsprogramm');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('APP_URL', rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/'));
define('APP_TIMEZONE', $_ENV['APP_TIMEZONE'] ?? 'Europe/Berlin');

// Timezone setzen
date_default_timezone_set(APP_TIMEZONE);

// Fehler-Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', STORAGE_PATH . '/logs/php_errors.log');
}

// Session-Konfiguration
define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 7200));

// Datenbank-Konfiguration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_DATABASE', $_ENV['DB_DATABASE'] ?? 'rechnung_db');
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');
define('DB_CHARSET', 'utf8mb4');

// Mail-Konfiguration
define('MAIL_DRIVER', $_ENV['MAIL_DRIVER'] ?? 'smtp');
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? 'localhost');
define('MAIL_PORT', (int)($_ENV['MAIL_PORT'] ?? 587));
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME'] ?? '');
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD'] ?? '');
define('MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION'] ?? 'tls');
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@localhost');
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? APP_NAME);

// PDF-Konfiguration
define('PDF_FONT', $_ENV['PDF_FONT'] ?? 'helvetica');
define('PDF_FONT_SIZE', (int)($_ENV['PDF_FONT_SIZE'] ?? 10));

// Rechnungs-Konfiguration
define('INVOICE_PREFIX', $_ENV['INVOICE_PREFIX'] ?? 'RE');
define('INVOICE_DIGITS', (int)($_ENV['INVOICE_DIGITS'] ?? 5));
define('INVOICE_YEAR_SEPARATOR', filter_var($_ENV['INVOICE_YEAR_SEPARATOR'] ?? true, FILTER_VALIDATE_BOOLEAN));

// Steuersätze
define('DEFAULT_TAX_RATE', (float)($_ENV['DEFAULT_TAX_RATE'] ?? 19));
define('REDUCED_TAX_RATE', (float)($_ENV['REDUCED_TAX_RATE'] ?? 7));

// Zahlungs-Konfiguration
define('DEFAULT_PAYMENT_TERMS', (int)($_ENV['DEFAULT_PAYMENT_TERMS'] ?? 14));
define('DEFAULT_SKONTO_DAYS', (int)($_ENV['DEFAULT_SKONTO_DAYS'] ?? 7));
define('DEFAULT_SKONTO_PERCENT', (float)($_ENV['DEFAULT_SKONTO_PERCENT'] ?? 2));

// Firmen-Konfiguration (Standard)
define('COMPANY_NAME', $_ENV['COMPANY_NAME'] ?? 'Ihre Firma GmbH');
define('COMPANY_STREET', $_ENV['COMPANY_STREET'] ?? 'Musterstraße 123');
define('COMPANY_ZIP', $_ENV['COMPANY_ZIP'] ?? '12345');
define('COMPANY_CITY', $_ENV['COMPANY_CITY'] ?? 'Berlin');
define('COMPANY_COUNTRY', $_ENV['COMPANY_COUNTRY'] ?? 'Deutschland');
define('COMPANY_PHONE', $_ENV['COMPANY_PHONE'] ?? '+49 30 12345678');
define('COMPANY_EMAIL', $_ENV['COMPANY_EMAIL'] ?? 'info@example.com');
define('COMPANY_WEB', $_ENV['COMPANY_WEB'] ?? 'www.example.com');
define('COMPANY_TAX_ID', $_ENV['COMPANY_TAX_ID'] ?? '');
define('COMPANY_VAT_ID', $_ENV['COMPANY_VAT_ID'] ?? '');
define('COMPANY_REGISTER', $_ENV['COMPANY_REGISTER'] ?? '');
define('COMPANY_BANK_NAME', $_ENV['COMPANY_BANK_NAME'] ?? '');
define('COMPANY_IBAN', $_ENV['COMPANY_IBAN'] ?? '');
define('COMPANY_BIC', $_ENV['COMPANY_BIC'] ?? '');

// Shopify-Konfiguration
define('SHOPIFY_SHOP_DOMAIN', $_ENV['SHOPIFY_SHOP_DOMAIN'] ?? '');
define('SHOPIFY_API_KEY', $_ENV['SHOPIFY_API_KEY'] ?? '');
define('SHOPIFY_API_SECRET', $_ENV['SHOPIFY_API_SECRET'] ?? '');
define('SHOPIFY_ACCESS_TOKEN', $_ENV['SHOPIFY_ACCESS_TOKEN'] ?? '');

// Helper-Funktionen
require_once CONFIG_PATH . '/helpers.php';
