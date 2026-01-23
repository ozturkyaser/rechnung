<?php
namespace App\Controllers;

use Libs\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Product;

/**
 * Offer Controller
 * Verwaltet Angebote (nutzt Invoice-Model mit type='offer')
 */
class OfferController extends Controller {
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
     * Liste aller Angebote
     */
    public function index() {
        $this->requireAuth();

        $offers = $this->invoiceModel->getAllWithCustomer(1, 'offer');

        $this->view('offers.index', [
            'title' => 'Angebote',
            'subtitle' => 'Angebotsverwaltung',
            'activeMenu' => 'offers',
            'offers' => $offers
        ]);
    }

    /**
     * Zeige einzelnes Angebot
     */
    public function show($id) {
        $this->requireAuth();

        $offer = $this->invoiceModel->find($id);

        if (!$offer || $offer['invoice_type'] !== 'offer') {
            setFlash('error', 'Angebot nicht gefunden.');
            $this->redirect(url('offers'));
        }

        $customer = $this->customerModel->find($offer['customer_id']);
        $items = $this->invoiceItemModel->getItemsForInvoice($id);

        $this->view('offers.show', [
            'title' => 'Angebot',
            'subtitle' => $offer['invoice_number'],
            'activeMenu' => 'offers',
            'offer' => $offer,
            'customer' => $customer,
            'items' => $items
        ]);
    }

    /**
     * Formular für neues Angebot
     */
    public function create() {
        $this->requireRole('sales');

        $customerId = $this->get('customer');
        $selectedCustomer = null;

        if ($customerId) {
            $selectedCustomer = $this->customerModel->find($customerId);
        }

        $customers = $this->customerModel->getActiveCustomers();
        $products = $this->productModel->getActiveProducts();
        $taxRates = $this->getTaxRates();

        $this->view('offers.create', [
            'title' => 'Neues Angebot',
            'subtitle' => 'Angebot erstellen',
            'activeMenu' => 'offers',
            'customers' => $customers,
            'products' => $products,
            'taxRates' => $taxRates,
            'selectedCustomer' => $selectedCustomer
        ]);
    }

    /**
     * Speichere neues Angebot
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

        $items = $this->post('items', []);

        if (empty($items)) {
            setFlash('error', 'Bitte fügen Sie mindestens eine Position hinzu.');
            $this->redirectBack();
        }

        try {
            $db = \Libs\Database::getInstance();
            $db->beginTransaction();

            $invoiceDate = $this->post('invoice_date') ?: date('Y-m-d');
            $validDays = (int)$this->post('valid_days', 14);
            $validUntil = date('Y-m-d', strtotime($invoiceDate . " +{$validDays} days"));

            // Erstelle Angebot
            $offerData = [
                'client_id' => 1,
                'customer_id' => $customerId,
                'invoice_number' => $this->invoiceModel->generateInvoiceNumber(1, 'offer'),
                'invoice_type' => 'offer',
                'invoice_status' => 'draft',
                'invoice_date' => $invoiceDate,
                'offer_valid_until' => $validUntil,
                'delivery_date' => $this->post('delivery_date'),
                'subject' => $this->post('subject'),
                'intro_text' => $this->post('intro_text'),
                'outro_text' => $this->post('outro_text'),
                'payment_terms_days' => $this->post('payment_terms_days') ?: $customer['payment_terms_days'] ?: DEFAULT_PAYMENT_TERMS,
                'skonto_days' => $this->post('skonto_days') ?: $customer['skonto_days'],
                'skonto_percent' => $this->post('skonto_percent') ?: $customer['skonto_percent'],
                'notes' => $this->post('notes'),
                'created_by' => $_SESSION['user_id'] ?? null
            ];

            // Berechne Summen
            $totals = $this->calculateTotals($items);
            $offerData['subtotal_net'] = $totals['subtotal_net'];
            $offerData['total_net'] = $totals['total_net'];
            $offerData['total_tax'] = $totals['total_tax'];
            $offerData['total_gross'] = $totals['total_gross'];

            $offerId = $this->invoiceModel->create($offerData);

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
                    'invoice_id' => $offerId,
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
                    'is_optional' => $item['is_optional'] ?? 0
                ];

                $this->invoiceItemModel->create($itemData);
            }

            $db->commit();

            logMessage("Offer created: ID {$offerId}, Number {$offerData['invoice_number']}");
            setFlash('success', "Angebot {$offerData['invoice_number']} erfolgreich erstellt.");
            $this->redirect(url('offers/' . $offerId));

        } catch (\Exception $e) {
            $db->rollback();
            logMessage("Error creating offer: " . $e->getMessage(), 'error');
            setFlash('error', 'Fehler beim Erstellen des Angebots.');
            $this->redirectBack();
        }
    }

    /**
     * Angebot in Rechnung umwandeln
     */
    public function convertToInvoice($id) {
        $this->requireRole('sales');
        $this->validateCsrf();

        $offer = $this->invoiceModel->find($id);

        if (!$offer || $offer['invoice_type'] !== 'offer') {
            setFlash('error', 'Angebot nicht gefunden.');
            $this->redirect(url('offers'));
        }

        if ($offer['invoice_status'] !== 'accepted' && $offer['invoice_status'] !== 'sent') {
            setFlash('warning', 'Nur angenommene oder versendete Angebote können in Rechnungen umgewandelt werden.');
            $this->redirectBack();
        }

        try {
            $db = \Libs\Database::getInstance();
            $db->beginTransaction();

            // Hole Items vom Angebot
            $items = $this->invoiceItemModel->getItemsForInvoice($id);

            // Erstelle neue Rechnung basierend auf Angebot
            $invoiceData = [
                'client_id' => $offer['client_id'],
                'customer_id' => $offer['customer_id'],
                'parent_invoice_id' => $id,
                'invoice_number' => $this->invoiceModel->generateInvoiceNumber(1, 'invoice'),
                'invoice_type' => 'invoice',
                'invoice_status' => 'draft',
                'invoice_date' => date('Y-m-d'),
                'due_date' => calculateDueDate(date('Y-m-d'), $offer['payment_terms_days']),
                'delivery_date' => $offer['delivery_date'],
                'subject' => $offer['subject'],
                'intro_text' => $offer['intro_text'],
                'outro_text' => $offer['outro_text'],
                'subtotal_net' => $offer['subtotal_net'],
                'total_net' => $offer['total_net'],
                'total_tax' => $offer['total_tax'],
                'total_gross' => $offer['total_gross'],
                'payment_terms_days' => $offer['payment_terms_days'],
                'skonto_days' => $offer['skonto_days'],
                'skonto_percent' => $offer['skonto_percent'],
                'notes' => 'Erstellt aus Angebot ' . $offer['invoice_number'],
                'created_by' => $_SESSION['user_id'] ?? null
            ];

            $invoiceId = $this->invoiceModel->create($invoiceData);

            // Kopiere Items
            foreach ($items as $item) {
                $itemData = [
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'],
                    'position' => $item['position'],
                    'item_type' => $item['item_type'],
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'price_net' => $item['price_net'],
                    'discount_percent' => $item['discount_percent'],
                    'tax_rate' => $item['tax_rate'],
                    'tax_rate_id' => $item['tax_rate_id'],
                    'subtotal_net' => $item['subtotal_net'],
                    'tax_amount' => $item['tax_amount'],
                    'total_gross' => $item['total_gross'],
                    'is_optional' => 0
                ];

                $this->invoiceItemModel->create($itemData);

                // Update Lagerbestand
                if (!empty($item['product_id']) && $item['item_type'] === 'product' && !$item['is_optional']) {
                    $this->productModel->updateStock($item['product_id'], $item['quantity'], 'subtract');
                }
            }

            $db->commit();

            logMessage("Offer {$offer['invoice_number']} converted to Invoice {$invoiceData['invoice_number']}");
            setFlash('success', "Angebot erfolgreich in Rechnung {$invoiceData['invoice_number']} umgewandelt.");
            $this->redirect(url('invoices/' . $invoiceId));

        } catch (\Exception $e) {
            $db->rollback();
            logMessage("Error converting offer to invoice: " . $e->getMessage(), 'error');
            setFlash('error', 'Fehler beim Umwandeln des Angebots.');
            $this->redirectBack();
        }
    }

    /**
     * Angebot akzeptieren
     */
    public function accept($id) {
        $this->requireRole('sales');
        $this->validateCsrf();

        $offer = $this->invoiceModel->find($id);

        if (!$offer || $offer['invoice_type'] !== 'offer') {
            setFlash('error', 'Angebot nicht gefunden.');
            $this->redirect(url('offers'));
        }

        $this->invoiceModel->update($id, ['invoice_status' => 'accepted']);

        logMessage("Offer accepted: ID {$id}, Number {$offer['invoice_number']}");
        setFlash('success', 'Angebot als angenommen markiert.');
        $this->redirectBack();
    }

    /**
     * Angebot ablehnen
     */
    public function reject($id) {
        $this->requireRole('sales');
        $this->validateCsrf();

        $offer = $this->invoiceModel->find($id);

        if (!$offer || $offer['invoice_type'] !== 'offer') {
            setFlash('error', 'Angebot nicht gefunden.');
            $this->redirect(url('offers'));
        }

        $this->invoiceModel->update($id, ['invoice_status' => 'rejected']);

        logMessage("Offer rejected: ID {$id}, Number {$offer['invoice_number']}");
        setFlash('success', 'Angebot als abgelehnt markiert.');
        $this->redirectBack();
    }

    /**
     * PDF generieren
     */
    public function generatePdf($id) {
        $this->requireAuth();

        $offer = $this->invoiceModel->find($id);

        if (!$offer || $offer['invoice_type'] !== 'offer') {
            setFlash('error', 'Angebot nicht gefunden.');
            $this->redirect(url('offers'));
        }

        try {
            $customer = $this->customerModel->find($offer['customer_id']);
            $items = $this->invoiceItemModel->getItemsForInvoice($id);

            $db = \Libs\Database::getInstance();
            $companyData = $db->fetchOne("SELECT * FROM clients WHERE id = ? LIMIT 1", [1]);

            $pdfGenerator = new \Libs\PdfGenerator($companyData);
            $pdf = $pdfGenerator->generateOffer($offer, $customer, $items);

            $pdfPath = STORAGE_PATH . '/invoices/';
            if (!is_dir($pdfPath)) {
                mkdir($pdfPath, 0775, true);
            }

            $filename = 'Angebot_' . $offer['invoice_number'] . '.pdf';
            $fullPath = $pdfPath . $filename;
            $pdfGenerator->save($fullPath);

            $this->invoiceModel->update($id, ['pdf_path' => 'invoices/' . $filename]);

            $pdf->Output($filename, 'D');
            exit;

        } catch (\Exception $e) {
            logMessage("PDF generation error: " . $e->getMessage(), 'error');
            setFlash('error', 'Fehler beim Generieren des PDFs: ' . $e->getMessage());
            $this->redirectBack();
        }
    }

    /**
     * Email versenden
     */
    public function sendEmail($id) {
        $this->requireAuth();

        $offer = $this->invoiceModel->find($id);

        if (!$offer || $offer['invoice_type'] !== 'offer') {
            setFlash('error', 'Angebot nicht gefunden.');
            $this->redirect(url('offers'));
        }

        try {
            $customer = $this->customerModel->find($offer['customer_id']);

            if (empty($customer['email'])) {
                setFlash('error', 'Kunde hat keine Email-Adresse hinterlegt.');
                $this->redirectBack();
            }

            // Generiere PDF falls nicht vorhanden
            $pdfPath = null;
            if (empty($offer['pdf_path']) || !file_exists(STORAGE_PATH . '/' . $offer['pdf_path'])) {
                $items = $this->invoiceItemModel->getItemsForInvoice($id);
                $db = \Libs\Database::getInstance();
                $companyData = $db->fetchOne("SELECT * FROM clients WHERE id = ? LIMIT 1", [1]);

                $pdfGenerator = new \Libs\PdfGenerator($companyData);
                $pdfGenerator->generateOffer($offer, $customer, $items);

                $pdfStoragePath = STORAGE_PATH . '/invoices/';
                if (!is_dir($pdfStoragePath)) {
                    mkdir($pdfStoragePath, 0775, true);
                }

                $filename = 'Angebot_' . $offer['invoice_number'] . '.pdf';
                $fullPath = $pdfStoragePath . $filename;
                $pdfGenerator->save($fullPath);

                $this->invoiceModel->update($id, ['pdf_path' => 'invoices/' . $filename]);
                $pdfPath = $fullPath;
            } else {
                $pdfPath = STORAGE_PATH . '/' . $offer['pdf_path'];
            }

            // Sende Email
            $emailService = new \Libs\EmailService();
            $result = $emailService->sendOffer($offer, $customer, $pdfPath);

            if ($result['success']) {
                setFlash('success', 'Angebot erfolgreich per Email versendet an ' . $customer['email']);
            } else {
                setFlash('error', $result['message']);
            }

            $this->redirectBack();

        } catch (\Exception $e) {
            logMessage("Email send error: " . $e->getMessage(), 'error');
            setFlash('error', 'Fehler beim Versenden der Email: ' . $e->getMessage());
            $this->redirectBack();
        }
    }

    /**
     * Berechne Gesamtsummen
     */
    private function calculateTotals($items) {
        $subtotalNet = 0;
        $totalNet = 0;
        $totalTax = 0;

        foreach ($items as $item) {
            // Skip optionale Positionen bei Summenberechnung
            if (!empty($item['is_optional'])) {
                continue;
            }

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
}
