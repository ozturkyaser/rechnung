<?php
namespace App\Models;

use Libs\Model;

/**
 * Payment Model
 */
class Payment extends Model {
    protected $table = 'payments';
    protected $primaryKey = 'id';

    protected $fillable = [
        'invoice_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference',
        'notes',
        'created_by'
    ];

    /**
     * Hole Zahlungen für Rechnung
     */
    public function getPaymentsForInvoice($invoiceId) {
        return $this->where(['invoice_id' => $invoiceId], 'payment_date', 'DESC');
    }

    /**
     * Berechne Gesamtzahlung für Rechnung
     */
    public function getTotalPaidForInvoice($invoiceId) {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total_paid
                FROM {$this->table}
                WHERE invoice_id = ?";

        $result = $this->db->fetchOne($sql, [$invoiceId]);
        return (float)$result['total_paid'];
    }

    /**
     * Hole letzte Zahlungen
     */
    public function getRecentPayments($limit = 20) {
        $sql = "SELECT p.*, i.invoice_number,
                    CONCAT(COALESCE(c.company_name, ''), ' ', COALESCE(c.first_name, ''), ' ', COALESCE(c.last_name, '')) as customer_name
                FROM {$this->table} p
                LEFT JOIN invoices i ON p.invoice_id = i.id
                LEFT JOIN customers c ON i.customer_id = c.id
                ORDER BY p.payment_date DESC, p.id DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }
}
