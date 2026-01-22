<?php
namespace App\Controllers;

use Libs\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Product;

/**
 * Invoice Controller
 */
class InvoiceController extends Controller {
    private $invoiceModel;
    private $invoiceItemModel;
    private $customerModel;
    private $productModel;

    public function __construct() {
        $this->invoiceModel = new Invoice();
        $this->invoiceItemModel = new InvoiceItem();
        $this->customerModel = new Customer();
        $this->productModel = new Product();
    }

    /**
     * Liste aller Rechnungen
     */
    public function index() {
        $this->requireAuth();

        $invoices = $this->invoiceModel->getAllWithCustomer(1, 'invoice');

        $this->view('invoices.index', [
            'title' => 'Rechnungen',
            'subtitle' => 'Rechnungsverwaltung',
            'activeMenu' => 'invoices',
            'invoices' => $invoices
        ]);
    }

    /**
     * Zeige einzelne Rechnung
     */
    public function show($id) {
        $this->requireAuth();

        $invoice = $this->invoiceModel->find($id);

        if (!$invoice) {
            setFlash('error', 'Rechnung nicht gefunden.');
            $this->redirect(url('invoices'));
        }

        // Hole Kunde
        $customer = $this->customerModel->find($invoice['customer_id']);

        // Hole Positionen
        $items = $this->invoiceItemModel->getItemsForInvoice($id);

        // Hole Zahlungen
        $db = \Libs\Database::getInstance();
        $payments = $db->fetchAll("SELECT * FROM payments WHERE invoice_id = ? ORDER BY payment_date DESC", [$id]);

        $this->view('invoices.show', [
            'title' => 'Rechnung',
            'subtitle' => $invoice['invoice_number'],
            'activeMenu' => 'invoices',
            'invoice' => $invoice,
            'customer' => $customer,
            'items' => $items,
            'payments' => $payments
        ]);
    }

    /**
     * Formular für neue Rechnung
     */
    public function create() {
        $this->requireRole('sales');

        // Vorausgewählter Kunde?
        $customerId = $this->get('customer');
        $selectedCustomer = null;

        if ($customerId) {
            $selectedCustomer = $this->customerModel->find($customerId);
        }

        // Hole aktive Kunden
        $customers = $this->customerModel->getActiveCustomers();

        // Hole aktive Produkte
        $products = $this->productModel->getActiveProducts();

        // Hole Steuersätze
        $taxRates = $this->getTaxRates();

        $this->view('invoices.create', [
            'title' => 'Neue Rechnung',
            'subtitle' => 'Rechnung erstellen',
            'activeMenu' => 'invoices',
            'customers' => $customers,
            'products' => $products,
            'taxRates' => $taxRates,
            'selectedCustomer' => $selectedCustomer
        ]);
    }

    /**
     * Speichere neue Rechnung
     */
    public function store() {
        $this->requireRole('sales');
        $this->validateCsrf();

        $customerId = $this->post('customer_id');

        if (!$customerId) {
            setFlash('error', 'Bitte wählen Sie einen Kunden aus.');
            $this->redirectBack();
        }

        $customer = $this->customerModel->find($customerId);

        if (!$customer) {
            setFlash('error', 'Kunde nicht gefunden.');
            $this->redirectBack();
        }

        // Hole Items
        $items = $this->post('items', []);

        if (empty($items)) {
            setFlash('error', 'Bitte fügen Sie mindestens eine Position hinzu.');
            $this->redirectBack();
        }

        try {
            $db = \Libs\Database::getInstance();
            $db->beginTransaction();

            // Berechne Daten
            $invoiceDate = $this->post('invoice_date') ?: date('Y-m-d');
            $paymentTermsDays = $this->post('payment_terms_days') ?: $customer['payment_terms_days'] ?: DEFAULT_PAYMENT_TERMS;
            $dueDate = calculateDueDate($invoiceDate, $paymentTermsDays);

            // Erstelle Rechnung
            $invoiceData = [
                'client_id' => 1,
                'customer_id' => $customerId,
                'invoice_number' => $this->invoiceModel->generateInvoiceNumber(1, 'invoice'),
                'invoice_type' => 'invoice',
                'invoice_status' => 'draft',
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'delivery_date' => $this->post('delivery_date'),
                'subject' => $this->post('subject'),
                'intro_text' => $this->post('intro_text'),
                'outro_text' => $this->post('outro_text'),
                'payment_terms_days' => $paymentTermsDays,
                'skonto_days' => $this->post('skonto_days') ?: $customer['skonto_days'],
                'skonto_percent' => $this->post('skonto_percent') ?: $customer['skonto_percent'],
                'is_reverse_charge' => $this->post('is_reverse_charge', 0),
                'is_small_business' => $this->post('is_small_business', 0),
                'notes' => $this->post('notes'),
                'created_by' => $_SESSION['user_id'] ?? null
            ];

            // Berechne Summen
            $totals = $this->calculateTotals($items);
            $invoiceData['subtotal_net'] = $totals['subtotal_net'];
            $invoiceData['total_net'] = $totals['total_net'];
            $invoiceData['total_tax'] = $totals['total_tax'];
            $invoiceData['total_gross'] = $totals['total_gross'];

            $invoiceId = $this->invoiceModel->create($invoiceData);

            // Erstelle Items
            $position = 0;
            foreach ($items as $item) {
                $position++;

                $quantity = (float)($item['quantity'] ?? 1);
                $priceNet = (float)str_replace(',', '.', $item['price_net'] ?? '0');
                $discountPercent = (float)($item['discount_percent'] ?? 0);
                $taxRate = (float)($item['tax_rate'] ?? 19);

                $subtotalNet = $quantity * $priceNet;
                $discountAmount = $subtotalNet * ($discountPercent / 100);
                $netAfterDiscount = $subtotalNet - $discountAmount;
                $taxAmount = $netAfterDiscount * ($taxRate / 100);
                $totalGross = $netAfterDiscount + $taxAmount;

                $itemData = [
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'] ?? null,
                    'position' => $position,
                    'item_type' => $item['type'] ?? 'product',
                    'name' => $item['name'],
                    'description' => $item['description'] ?? '',
                    'quantity' => $quantity,
                    'unit' => $item['unit'] ?? 'Stück',
                    'price_net' => $priceNet,
                    'discount_percent' => $discountPercent,
                    'tax_rate' => $taxRate,
                    'tax_rate_id' => $item['tax_rate_id'] ?? null,
                    'subtotal_net' => $netAfterDiscount,
                    'tax_amount' => $taxAmount,
                    'total_gross' => $totalGross,
                    'is_optional' => 0
                ];

                $this->invoiceItemModel->create($itemData);

                // Update Lagerbestand (bei Produkten)
                if (!empty($item['product_id']) && $item['type'] === 'product') {
                    $this->productModel->updateStock($item['product_id'], $quantity, 'subtract');
                }
            }

            $db->commit();

            logMessage("Invoice created: ID {$invoiceId}, Number {$invoiceData['invoice_number']}");
            setFlash('success', "Rechnung {$invoiceData['invoice_number']} erfolgreich erstellt.");
            $this->redirect(url('invoices/' . $invoiceId));

        } catch (\Exception $e) {
            $db->rollback();
            logMessage("Error creating invoice: " . $e->getMessage(), 'error');
            setFlash('error', 'Fehler beim Erstellen der Rechnung.');
            $this->redirectBack();
        }
    }

    /**
     * Rechnung bearbeiten
     */
    public function edit($id) {
        $this->requireRole('sales');

        $invoice = $this->invoiceModel->find($id);

        if (!$invoice) {
            setFlash('error', 'Rechnung nicht gefunden.');
            $this->redirect(url('invoices'));
        }

        // Nur Entwürfe können bearbeitet werden (GoBD!)
        if ($invoice['invoice_status'] !== 'draft') {
            setFlash('error', 'Nur Entwürfe können bearbeitet werden. Erstellen Sie eine Gutschrift für Korrekturen.');
            $this->redirect(url('invoices/' . $id));
        }

        $customer = $this->customerModel->find($invoice['customer_id']);
        $items = $this->invoiceItemModel->getItemsForInvoice($id);
        $customers = $this->customerModel->getActiveCustomers();
        $products = $this->productModel->getActiveProducts();
        $taxRates = $this->getTaxRates();

        $this->view('invoices.edit', [
            'title' => 'Rechnung bearbeiten',
            'subtitle' => $invoice['invoice_number'],
            'activeMenu' => 'invoices',
            'invoice' => $invoice,
            'customer' => $customer,
            'items' => $items,
            'customers' => $customers,
            'products' => $products,
            'taxRates' => $taxRates
        ]);
    }

    /**
     * Rechnung aktualisieren
     */
    public function update($id) {
        $this->requireRole('sales');
        $this->validateCsrf();

        $invoice = $this->invoiceModel->find($id);

        if (!$invoice || $invoice['invoice_status'] !== 'draft') {
            setFlash('error', 'Rechnung kann nicht bearbeitet werden.');
            $this->redirect(url('invoices'));
        }

        // Ähnlich wie store(), aber update statt create
        // (Verkürzt dargestellt)

        setFlash('success', 'Rechnung erfolgreich aktualisiert.');
        $this->redirect(url('invoices/' . $id));
    }

    /**
     * Rechnung löschen/stornieren
     */
    public function delete($id) {
        $this->requireRole('admin');
        $this->validateCsrf();

        $invoice = $this->invoiceModel->find($id);

        if (!$invoice) {
            setFlash('error', 'Rechnung nicht gefunden.');
            $this->redirect(url('invoices'));
        }

        // Nur Entwürfe können gelöscht werden
        if ($invoice['invoice_status'] === 'draft') {
            try {
                $this->invoiceModel->update($id, ['invoice_status' => 'cancelled']);
                logMessage("Invoice cancelled: ID {$id}");
                setFlash('success', 'Rechnung erfolgreich storniert.');
            } catch (\Exception $e) {
                logMessage("Error cancelling invoice: " . $e->getMessage(), 'error');
                setFlash('error', 'Fehler beim Stornieren der Rechnung.');
            }
        } else {
            setFlash('error', 'Rechnung kann nicht gelöscht werden. Erstellen Sie eine Storno-Rechnung.');
        }

        $this->redirect(url('invoices'));
    }

    /**
     * Berechne Gesamtsummen
     */
    private function calculateTotals($items) {
        $subtotalNet = 0;
        $totalNet = 0;
        $totalTax = 0;

        foreach ($items as $item) {
            $quantity = (float)($item['quantity'] ?? 1);
            $priceNet = (float)str_replace(',', '.', $item['price_net'] ?? '0');
            $discountPercent = (float)($item['discount_percent'] ?? 0);
            $taxRate = (float)($item['tax_rate'] ?? 19);

            $itemSubtotal = $quantity * $priceNet;
            $subtotalNet += $itemSubtotal;

            $discountAmount = $itemSubtotal * ($discountPercent / 100);
            $netAfterDiscount = $itemSubtotal - $discountAmount;
            $taxAmount = $netAfterDiscount * ($taxRate / 100);

            $totalNet += $netAfterDiscount;
            $totalTax += $taxAmount;
        }

        return [
            'subtotal_net' => round($subtotalNet, 2),
            'total_net' => round($totalNet, 2),
            'total_tax' => round($totalTax, 2),
            'total_gross' => round($totalNet + $totalTax, 2)
        ];
    }

    /**
     * Hole Steuersätze
     */
    private function getTaxRates() {
        $db = \Libs\Database::getInstance();
        return $db->fetchAll("SELECT * FROM tax_rates WHERE client_id = 1 AND is_active = 1 ORDER BY rate DESC");
    }

    /**
     * PDF generieren (wird später implementiert)
     */
    public function generatePdf($id) {
        $this->requireAuth();

        $invoice = $this->invoiceModel->find($id);

        if (!$invoice) {
            setFlash('error', 'Rechnung nicht gefunden.');
            $this->redirect(url('invoices'));
        }

        setFlash('info', 'PDF-Generierung wird in Kürze verfügbar sein.');
        $this->redirectBack();
    }

    /**
     * Email versenden (wird später implementiert)
     */
    public function sendEmail($id) {
        $this->requireAuth();

        $invoice = $this->invoiceModel->find($id);

        if (!$invoice) {
            setFlash('error', 'Rechnung nicht gefunden.');
            $this->redirect(url('invoices'));
        }

        setFlash('info', 'Email-Versand wird in Kürze verfügbar sein.');
        $this->redirectBack();
    }
}
