<?php
namespace App\Controllers;

use Libs\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Payment;

/**
 * Dashboard Controller
 */
class DashboardController extends Controller {
    private $customerModel;
    private $invoiceModel;
    private $productModel;
    private $paymentModel;

    public function __construct() {
        $this->customerModel = new Customer();
        $this->invoiceModel = new Invoice();
        $this->productModel = new Product();
        $this->paymentModel = new Payment();
    }

    /**
     * Dashboard Index
     */
    public function index() {
        $this->requireAuth();

        // Statistiken holen
        $stats = $this->getDashboardStats();

        $this->view('dashboard.index', [
            'title' => 'Dashboard',
            'subtitle' => 'Übersicht',
            'activeMenu' => 'dashboard',
            'stats' => $stats
        ]);
    }

    /**
     * Dashboard Statistiken
     */
    private function getDashboardStats() {
        $currentYear = date('Y');
        $currentMonth = date('Y-m');

        // Gesamtanzahlen
        $totalCustomers = $this->customerModel->count(['is_active' => 1]);
        $totalProducts = $this->productModel->count(['is_active' => 1]);

        // Rechnungsstatistiken
        $totalInvoices = $this->invoiceModel->count(['invoice_type' => 'invoice']);
        $openInvoices = $this->invoiceModel->count([
            'invoice_type' => 'invoice',
            'invoice_status' => ['draft', 'sent', 'viewed']
        ]);
        $paidInvoices = $this->invoiceModel->count([
            'invoice_type' => 'invoice',
            'invoice_status' => 'paid'
        ]);
        $overdueInvoices = $this->invoiceModel->count([
            'invoice_type' => 'invoice',
            'invoice_status' => 'overdue'
        ]);

        // Umsatz aktueller Monat
        $monthRevenue = $this->invoiceModel->getMonthRevenue($currentMonth);

        // Umsatz aktuelles Jahr
        $yearRevenue = $this->invoiceModel->getYearRevenue($currentYear);

        // Offene Forderungen
        $openAmount = $this->invoiceModel->getOpenAmount();

        // Letzte Rechnungen
        $recentInvoices = $this->invoiceModel->getRecentInvoices(5);

        // Überfällige Rechnungen
        $overdueInvoicesList = $this->invoiceModel->getOverdueInvoices(5);

        return [
            'total_customers' => $totalCustomers,
            'total_products' => $totalProducts,
            'total_invoices' => $totalInvoices,
            'open_invoices' => $openInvoices,
            'paid_invoices' => $paidInvoices,
            'overdue_invoices' => $overdueInvoices,
            'month_revenue' => $monthRevenue,
            'year_revenue' => $yearRevenue,
            'open_amount' => $openAmount,
            'recent_invoices' => $recentInvoices,
            'overdue_invoices_list' => $overdueInvoicesList
        ];
    }
}
