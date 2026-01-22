<?php
/**
 * Routen-Definition
 */

use Libs\Router;

$router = new Router();

// ========================================
// Öffentliche Routen
// ========================================

// Login
$router->get('/login', 'AuthController@showLogin', 'login');
$router->post('/login', 'AuthController@login', 'login.post');
$router->get('/logout', 'AuthController@logout', 'logout');

// ========================================
// Geschützte Routen (erfordern Login)
// ========================================

// Dashboard
$router->get('/', 'DashboardController@index', 'dashboard')->middleware('auth');
$router->get('/dashboard', 'DashboardController@index', 'dashboard.index')->middleware('auth');

// ========================================
// Kunden
// ========================================
$router->get('/customers', 'CustomerController@index', 'customers.index')->middleware('auth');
$router->get('/customers/create', 'CustomerController@create', 'customers.create')->middleware('auth');
$router->post('/customers', 'CustomerController@store', 'customers.store')->middleware('auth');
$router->get('/customers/{id}', 'CustomerController@show', 'customers.show')->middleware('auth');
$router->get('/customers/{id}/edit', 'CustomerController@edit', 'customers.edit')->middleware('auth');
$router->post('/customers/{id}', 'CustomerController@update', 'customers.update')->middleware('auth');
$router->post('/customers/{id}/delete', 'CustomerController@delete', 'customers.delete')->middleware('auth');

// ========================================
// Produkte
// ========================================
$router->get('/products', 'ProductController@index', 'products.index')->middleware('auth');
$router->get('/products/create', 'ProductController@create', 'products.create')->middleware('auth');
$router->post('/products', 'ProductController@store', 'products.store')->middleware('auth');
$router->get('/products/{id}', 'ProductController@show', 'products.show')->middleware('auth');
$router->get('/products/{id}/edit', 'ProductController@edit', 'products.edit')->middleware('auth');
$router->post('/products/{id}', 'ProductController@update', 'products.update')->middleware('auth');
$router->post('/products/{id}/delete', 'ProductController@delete', 'products.delete')->middleware('auth');

// ========================================
// Rechnungen
// ========================================
$router->get('/invoices', 'InvoiceController@index', 'invoices.index')->middleware('auth');
$router->get('/invoices/create', 'InvoiceController@create', 'invoices.create')->middleware('auth');
$router->post('/invoices', 'InvoiceController@store', 'invoices.store')->middleware('auth');
$router->get('/invoices/{id}', 'InvoiceController@show', 'invoices.show')->middleware('auth');
$router->get('/invoices/{id}/edit', 'InvoiceController@edit', 'invoices.edit')->middleware('auth');
$router->post('/invoices/{id}', 'InvoiceController@update', 'invoices.update')->middleware('auth');
$router->post('/invoices/{id}/delete', 'InvoiceController@delete', 'invoices.delete')->middleware('auth');
$router->get('/invoices/{id}/pdf', 'InvoiceController@generatePdf', 'invoices.pdf')->middleware('auth');
$router->post('/invoices/{id}/send', 'InvoiceController@sendEmail', 'invoices.send')->middleware('auth');

// ========================================
// Angebote
// ========================================
$router->get('/offers', 'OfferController@index', 'offers.index')->middleware('auth');
$router->get('/offers/create', 'OfferController@create', 'offers.create')->middleware('auth');
$router->post('/offers', 'OfferController@store', 'offers.store')->middleware('auth');
$router->get('/offers/{id}', 'OfferController@show', 'offers.show')->middleware('auth');
$router->get('/offers/{id}/edit', 'OfferController@edit', 'offers.edit')->middleware('auth');
$router->post('/offers/{id}', 'OfferController@update', 'offers.update')->middleware('auth');
$router->post('/offers/{id}/convert', 'OfferController@convertToInvoice', 'offers.convert')->middleware('auth');

// ========================================
// Gutschriften
// ========================================
$router->get('/credits', 'CreditController@index', 'credits.index')->middleware('auth');
$router->get('/credits/create', 'CreditController@create', 'credits.create')->middleware('auth');
$router->post('/credits', 'CreditController@store', 'credits.store')->middleware('auth');

// ========================================
// Zahlungen
// ========================================
$router->get('/payments', 'PaymentController@index', 'payments.index')->middleware('auth');
$router->post('/payments', 'PaymentController@store', 'payments.store')->middleware('auth');

// ========================================
// Mahnungen
// ========================================
$router->get('/reminders', 'ReminderController@index', 'reminders.index')->middleware('auth');
$router->post('/reminders', 'ReminderController@create', 'reminders.create')->middleware('auth');

// ========================================
// Reports
// ========================================
$router->get('/reports', 'ReportController@index', 'reports.index')->middleware('auth');
$router->get('/reports/sales', 'ReportController@sales', 'reports.sales')->middleware('auth');
$router->get('/reports/tax', 'ReportController@tax', 'reports.tax')->middleware('auth');

// ========================================
// Einstellungen
// ========================================
$router->get('/settings', 'SettingsController@index', 'settings.index')->middleware('auth');
$router->post('/settings', 'SettingsController@update', 'settings.update')->middleware('auth');

// ========================================
// API Routen (für AJAX)
// ========================================
$router->get('/api/customers/search', 'Api\\CustomerController@search', 'api.customers.search')->middleware('auth');
$router->get('/api/products/search', 'Api\\ProductController@search', 'api.products.search')->middleware('auth');

// ========================================
// Middleware Registrierung
// ========================================
$router->registerMiddleware('auth', function() {
    if (!isLoggedIn()) {
        if (isAjax()) {
            jsonResponse(['error' => 'Unauthorized'], 401);
        } else {
            setFlash('error', 'Bitte melden Sie sich an.');
            redirect(url('login'));
        }
    }
});

return $router;
