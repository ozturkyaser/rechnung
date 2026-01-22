<?php
namespace App\Controllers;

use Libs\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Customer;

/**
 * Payment Controller
 */
class PaymentController extends Controller {
    private $paymentModel;
    private $invoiceModel;
    private $customerModel;

    public function __construct() {
        $this->paymentModel = new Payment();
        $this->invoiceModel = new Invoice();
        $this->customerModel = new Customer();
    }

    /**
     * Liste aller Zahlungen
     */
    public function index() {
        $this->requireAuth();

        $page = $this->get('page', 1);
        $payments = $this->paymentModel->getRecentPayments(100);

        $this->view('payments.index', [
            'title' => 'Zahlungen',
            'subtitle' => 'Zahlungsübersicht',
            'activeMenu' => 'payments',
            'payments' => $payments
        ]);
    }

    /**
     * Speichere neue Zahlung
     */
    public function store() {
        $this->requireRole('accounting');
        $this->validateCsrf();

        $invoiceId = $this->post('invoice_id');
        $amount = (float)str_replace(',', '.', $this->post('amount', '0'));
        $paymentDate = $this->post('payment_date') ?: date('Y-m-d');
        $paymentMethod = $this->post('payment_method', 'bank_transfer');
        $reference = $this->post('reference');
        $notes = $this->post('notes');

        // Validierung
        if (!$invoiceId || $amount <= 0) {
            setFlash('error', 'Bitte geben Sie eine gültige Rechnung und einen Betrag an.');
            $this->redirectBack();
        }

        $invoice = $this->invoiceModel->find($invoiceId);

        if (!$invoice) {
            setFlash('error', 'Rechnung nicht gefunden.');
            $this->redirectBack();
        }

        // Prüfe ob Betrag nicht zu hoch ist
        $openAmount = $invoice['total_gross'] - $invoice['paid_amount'];

        if ($amount > $openAmount + 0.01) { // +0.01 für Rundungsdifferenzen
            setFlash('error', 'Der Zahlungsbetrag (' . formatMoney($amount) . ') übersteigt den offenen Betrag (' . formatMoney($openAmount) . ').');
            $this->redirectBack();
        }

        try {
            $db = \Libs\Database::getInstance();
            $db->beginTransaction();

            // Zahlung speichern
            $paymentData = [
                'invoice_id' => $invoiceId,
                'payment_date' => $paymentDate,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'reference' => $reference,
                'notes' => $notes,
                'created_by' => $_SESSION['user_id'] ?? null
            ];

            $paymentId = $this->paymentModel->create($paymentData);

            // Update paid_amount in invoice
            $newPaidAmount = $invoice['paid_amount'] + $amount;
            $this->invoiceModel->update($invoiceId, [
                'paid_amount' => $newPaidAmount
            ]);

            // Update Invoice Status
            $this->updateInvoiceStatus($invoiceId, $invoice['total_gross'], $newPaidAmount);

            $db->commit();

            logMessage("Payment created: ID {$paymentId}, Invoice {$invoice['invoice_number']}, Amount {$amount}");
            setFlash('success', 'Zahlung erfolgreich gebucht: ' . formatMoney($amount));

            // Redirect zur Rechnung
            $this->redirect(url('invoices/' . $invoiceId));

        } catch (\Exception $e) {
            $db->rollback();
            logMessage("Error creating payment: " . $e->getMessage(), 'error');
            setFlash('error', 'Fehler beim Buchen der Zahlung.');
            $this->redirectBack();
        }
    }

    /**
     * Zahlung löschen
     */
    public function delete($id) {
        $this->requireRole('admin');
        $this->validateCsrf();

        $payment = $this->paymentModel->find($id);

        if (!$payment) {
            setFlash('error', 'Zahlung nicht gefunden.');
            $this->redirectBack();
        }

        try {
            $db = \Libs\Database::getInstance();
            $db->beginTransaction();

            $invoice = $this->invoiceModel->find($payment['invoice_id']);

            // Lösche Zahlung
            $this->paymentModel->delete($id);

            // Update paid_amount
            $newPaidAmount = $invoice['paid_amount'] - $payment['amount'];
            $this->invoiceModel->update($payment['invoice_id'], [
                'paid_amount' => $newPaidAmount
            ]);

            // Update Status
            $this->updateInvoiceStatus($payment['invoice_id'], $invoice['total_gross'], $newPaidAmount);

            $db->commit();

            logMessage("Payment deleted: ID {$id}, Amount {$payment['amount']}");
            setFlash('success', 'Zahlung erfolgreich gelöscht.');

        } catch (\Exception $e) {
            $db->rollback();
            logMessage("Error deleting payment: " . $e->getMessage(), 'error');
            setFlash('error', 'Fehler beim Löschen der Zahlung.');
        }

        $this->redirectBack();
    }

    /**
     * Update Invoice Status basierend auf Zahlungen
     */
    private function updateInvoiceStatus($invoiceId, $totalGross, $paidAmount) {
        $newStatus = 'sent'; // Default

        if ($paidAmount >= $totalGross - 0.01) { // -0.01 für Rundungsdifferenzen
            $newStatus = 'paid';
        } elseif ($paidAmount > 0) {
            $newStatus = 'partial';
        } else {
            // Prüfe ob überfällig
            $invoice = $this->invoiceModel->find($invoiceId);
            if ($invoice && !empty($invoice['due_date']) && isOverdue($invoice['due_date'])) {
                $newStatus = 'overdue';
            }
        }

        $this->invoiceModel->update($invoiceId, ['invoice_status' => $newStatus]);

        logMessage("Invoice status updated: ID {$invoiceId}, Status: {$newStatus}");
    }

    /**
     * Zahlungs-Statistiken
     */
    public function stats() {
        $this->requireAuth();

        $db = \Libs\Database::getInstance();

        // Zahlungen diesen Monat
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        $monthPayments = $db->fetchOne("
            SELECT COALESCE(SUM(amount), 0) as total
            FROM payments
            WHERE payment_date >= ? AND payment_date <= ?
        ", [$monthStart, $monthEnd]);

        // Zahlungen dieses Jahr
        $yearStart = date('Y-01-01');
        $yearEnd = date('Y-12-31');

        $yearPayments = $db->fetchOne("
            SELECT COALESCE(SUM(amount), 0) as total
            FROM payments
            WHERE payment_date >= ? AND payment_date <= ?
        ", [$yearStart, $yearEnd]);

        // Nach Zahlungsart
        $paymentMethods = $db->fetchAll("
            SELECT payment_method, COUNT(*) as count, SUM(amount) as total
            FROM payments
            WHERE payment_date >= ?
            GROUP BY payment_method
            ORDER BY total DESC
        ", [$yearStart]);

        $this->json([
            'month_total' => (float)$monthPayments['total'],
            'year_total' => (float)$yearPayments['total'],
            'payment_methods' => $paymentMethods
        ]);
    }
}
