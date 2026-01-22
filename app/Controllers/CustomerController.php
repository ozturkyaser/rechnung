<?php
namespace App\Controllers;

use Libs\Controller;
use App\Models\Customer;

/**
 * Customer Controller
 */
class CustomerController extends Controller {
    private $customerModel;

    public function __construct() {
        $this->customerModel = new Customer();
    }

    /**
     * Liste aller Kunden
     */
    public function index() {
        $this->requireAuth();

        $page = $this->get('page', 1);
        $search = $this->get('search', '');

        if ($search) {
            $customers = $this->customerModel->search($search);
            $pagination = null;
        } else {
            $pagination = $this->customerModel->paginate($page, 20, ['client_id' => 1], 'id', 'DESC');
            $customers = $pagination['data'];
        }

        $this->view('customers.index', [
            'title' => 'Kunden',
            'subtitle' => 'Kundenverwaltung',
            'activeMenu' => 'customers',
            'customers' => $customers,
            'pagination' => $pagination,
            'search' => $search
        ]);
    }

    /**
     * Zeige einzelnen Kunden
     */
    public function show($id) {
        $this->requireAuth();

        $customer = $this->customerModel->find($id);

        if (!$customer) {
            setFlash('error', 'Kunde nicht gefunden.');
            $this->redirect(url('customers'));
        }

        // Hole Kundenstatistiken
        $stats = $this->customerModel->getCustomerStats($id);

        $this->view('customers.show', [
            'title' => 'Kunde Details',
            'subtitle' => $customer['customer_number'],
            'activeMenu' => 'customers',
            'customer' => $customer,
            'stats' => $stats
        ]);
    }

    /**
     * Formular für neuen Kunden
     */
    public function create() {
        $this->requireRole('sales');

        $this->view('customers.create', [
            'title' => 'Neuer Kunde',
            'subtitle' => 'Kunde anlegen',
            'activeMenu' => 'customers'
        ]);
    }

    /**
     * Speichere neuen Kunden
     */
    public function store() {
        $this->requireRole('sales');
        $this->validateCsrf();

        $data = [
            'client_id' => 1,
            'customer_number' => $this->customerModel->generateCustomerNumber(),
            'customer_type' => $this->post('customer_type', 'b2b'),
            'company_name' => $this->post('company_name'),
            'first_name' => $this->post('first_name'),
            'last_name' => $this->post('last_name'),
            'email' => $this->post('email'),
            'phone' => $this->post('phone'),
            'mobile' => $this->post('mobile'),
            'website' => $this->post('website'),
            'tax_id' => $this->post('tax_id'),
            'vat_id' => $this->post('vat_id'),
            'tax_region' => $this->post('tax_region', 'domestic'),
            'billing_street' => $this->post('billing_street'),
            'billing_zip' => $this->post('billing_zip'),
            'billing_city' => $this->post('billing_city'),
            'billing_country' => $this->post('billing_country', 'Deutschland'),
            'shipping_street' => $this->post('shipping_street'),
            'shipping_zip' => $this->post('shipping_zip'),
            'shipping_city' => $this->post('shipping_city'),
            'shipping_country' => $this->post('shipping_country'),
            'payment_terms_days' => $this->post('payment_terms_days', DEFAULT_PAYMENT_TERMS),
            'skonto_days' => $this->post('skonto_days', DEFAULT_SKONTO_DAYS),
            'skonto_percent' => $this->post('skonto_percent', DEFAULT_SKONTO_PERCENT),
            'credit_limit' => $this->post('credit_limit', 0),
            'notes' => $this->post('notes'),
            'is_active' => 1
        ];

        // Validierung
        $errors = $this->validate($data, [
            'email' => 'email'
        ]);

        if (!empty($errors)) {
            setFlash('error', 'Bitte überprüfen Sie Ihre Eingaben.');
            $this->redirectBack();
        }

        try {
            $customerId = $this->customerModel->create($data);

            logMessage("Customer created: ID {$customerId}");
            setFlash('success', 'Kunde erfolgreich angelegt.');
            $this->redirect(url('customers/' . $customerId));
        } catch (\Exception $e) {
            logMessage("Error creating customer: " . $e->getMessage(), 'error');
            setFlash('error', 'Fehler beim Anlegen des Kunden.');
            $this->redirectBack();
        }
    }

    /**
     * Formular zum Bearbeiten
     */
    public function edit($id) {
        $this->requireRole('sales');

        $customer = $this->customerModel->find($id);

        if (!$customer) {
            setFlash('error', 'Kunde nicht gefunden.');
            $this->redirect(url('customers'));
        }

        $this->view('customers.edit', [
            'title' => 'Kunde bearbeiten',
            'subtitle' => $customer['customer_number'],
            'activeMenu' => 'customers',
            'customer' => $customer
        ]);
    }

    /**
     * Aktualisiere Kunden
     */
    public function update($id) {
        $this->requireRole('sales');
        $this->validateCsrf();

        $customer = $this->customerModel->find($id);

        if (!$customer) {
            setFlash('error', 'Kunde nicht gefunden.');
            $this->redirect(url('customers'));
        }

        $data = [
            'customer_type' => $this->post('customer_type'),
            'company_name' => $this->post('company_name'),
            'first_name' => $this->post('first_name'),
            'last_name' => $this->post('last_name'),
            'email' => $this->post('email'),
            'phone' => $this->post('phone'),
            'mobile' => $this->post('mobile'),
            'website' => $this->post('website'),
            'tax_id' => $this->post('tax_id'),
            'vat_id' => $this->post('vat_id'),
            'tax_region' => $this->post('tax_region'),
            'billing_street' => $this->post('billing_street'),
            'billing_zip' => $this->post('billing_zip'),
            'billing_city' => $this->post('billing_city'),
            'billing_country' => $this->post('billing_country'),
            'shipping_street' => $this->post('shipping_street'),
            'shipping_zip' => $this->post('shipping_zip'),
            'shipping_city' => $this->post('shipping_city'),
            'shipping_country' => $this->post('shipping_country'),
            'payment_terms_days' => $this->post('payment_terms_days'),
            'skonto_days' => $this->post('skonto_days'),
            'skonto_percent' => $this->post('skonto_percent'),
            'credit_limit' => $this->post('credit_limit'),
            'notes' => $this->post('notes'),
            'is_active' => $this->post('is_active', 1)
        ];

        try {
            $this->customerModel->update($id, $data);

            logMessage("Customer updated: ID {$id}");
            setFlash('success', 'Kunde erfolgreich aktualisiert.');
            $this->redirect(url('customers/' . $id));
        } catch (\Exception $e) {
            logMessage("Error updating customer: " . $e->getMessage(), 'error');
            setFlash('error', 'Fehler beim Aktualisieren des Kunden.');
            $this->redirectBack();
        }
    }

    /**
     * Lösche Kunden (Soft Delete)
     */
    public function delete($id) {
        $this->requireRole('admin');
        $this->validateCsrf();

        try {
            // Soft Delete - setze is_active auf 0
            $this->customerModel->update($id, ['is_active' => 0]);

            logMessage("Customer deactivated: ID {$id}");
            setFlash('success', 'Kunde erfolgreich deaktiviert.');
        } catch (\Exception $e) {
            logMessage("Error deleting customer: " . $e->getMessage(), 'error');
            setFlash('error', 'Fehler beim Löschen des Kunden.');
        }

        $this->redirect(url('customers'));
    }
}
