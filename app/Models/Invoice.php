<?php
namespace App\Models;

use Libs\Model;

/**
 * Invoice Model
 */
class Invoice extends Model {
    protected $table = 'invoices';
    protected $primaryKey = 'id';

    protected $fillable = [
        'client_id',
        'customer_id',
        'number_range_id',
        'invoice_number',
        'invoice_type',
        'invoice_status',
        'parent_invoice_id',
        'invoice_date',
        'due_date',
        'delivery_date',
        'offer_valid_until',
        'subject',
        'intro_text',
        'outro_text',
        'subtotal_net',
        'discount_percent',
        'discount_amount',
        'total_net',
        'total_tax',
        'total_gross',
        'paid_amount',
        'payment_terms_days',
        'skonto_days',
        'skonto_percent',
        'is_reverse_charge',
        'is_small_business',
        'is_differential_taxation',
        'leitweg_id',
        'buyer_reference',
        'project_name',
        'pdf_path',
        'xrechnung_path',
        'zugferd_path',
        'shopify_order_id',
        'notes',
        'created_by'
    ];

    /**
     * Hole Rechnungen mit Kundendaten
     */
    public function getAllWithCustomer($clientId = 1, $invoiceType = 'invoice') {
        $sql = "SELECT i.*,
                    CONCAT(COALESCE(c.company_name, ''), ' ', COALESCE(c.first_name, ''), ' ', COALESCE(c.last_name, '')) as customer_name
                FROM {$this->table} i
                LEFT JOIN customers c ON i.customer_id = c.id
                WHERE i.client_id = ?
                AND i.invoice_type = ?
                ORDER BY i.invoice_date DESC, i.id DESC";

        return $this->db->fetchAll($sql, [$clientId, $invoiceType]);
    }

    /**
     * Hole letzte Rechnungen
     */
    public function getRecentInvoices($limit = 10, $clientId = 1) {
        $sql = "SELECT i.*,
                    CONCAT(COALESCE(c.company_name, ''), ' ', COALESCE(c.first_name, ''), ' ', COALESCE(c.last_name, '')) as customer_name
                FROM {$this->table} i
                LEFT JOIN customers c ON i.customer_id = c.id
                WHERE i.client_id = ?
                AND i.invoice_type = 'invoice'
                ORDER BY i.created_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$clientId, $limit]);
    }

    /**
     * Hole überfällige Rechnungen
     */
    public function getOverdueInvoices($limit = null, $clientId = 1) {
        $sql = "SELECT i.*,
                    CONCAT(COALESCE(c.company_name, ''), ' ', COALESCE(c.first_name, ''), ' ', COALESCE(c.last_name, '')) as customer_name
                FROM {$this->table} i
                LEFT JOIN customers c ON i.customer_id = c.id
                WHERE i.client_id = ?
                AND i.invoice_type = 'invoice'
                AND i.invoice_status IN ('sent', 'viewed', 'overdue')
                AND i.due_date < CURDATE()
                ORDER BY i.due_date ASC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->db->fetchAll($sql, [$clientId]);
    }

    /**
     * Berechne Umsatz für Monat
     */
    public function getMonthRevenue($month, $clientId = 1) {
        $sql = "SELECT COALESCE(SUM(total_gross), 0) as revenue
                FROM {$this->table}
                WHERE client_id = ?
                AND invoice_type = 'invoice'
                AND invoice_status = 'paid'
                AND DATE_FORMAT(invoice_date, '%Y-%m') = ?";

        $result = $this->db->fetchOne($sql, [$clientId, $month]);
        return (float)$result['revenue'];
    }

    /**
     * Berechne Umsatz für Jahr
     */
    public function getYearRevenue($year, $clientId = 1) {
        $sql = "SELECT COALESCE(SUM(total_gross), 0) as revenue
                FROM {$this->table}
                WHERE client_id = ?
                AND invoice_type = 'invoice'
                AND invoice_status = 'paid'
                AND YEAR(invoice_date) = ?";

        $result = $this->db->fetchOne($sql, [$clientId, $year]);
        return (float)$result['revenue'];
    }

    /**
     * Berechne offene Forderungen
     */
    public function getOpenAmount($clientId = 1) {
        $sql = "SELECT COALESCE(SUM(total_gross - paid_amount), 0) as open_amount
                FROM {$this->table}
                WHERE client_id = ?
                AND invoice_type = 'invoice'
                AND invoice_status IN ('sent', 'viewed', 'partial', 'overdue')";

        $result = $this->db->fetchOne($sql, [$clientId]);
        return (float)$result['open_amount'];
    }

    /**
     * Generiere Rechnungsnummer
     */
    public function generateInvoiceNumber($clientId = 1, $type = 'invoice') {
        $sql = "SELECT * FROM number_ranges
                WHERE client_id = ?
                AND type = ?
                AND is_default = 1
                LIMIT 1";

        $range = $this->db->fetchOne($sql, [$clientId, $type]);

        if (!$range) {
            return 'RE-' . date('Y') . '-' . str_pad(1, 5, '0', STR_PAD_LEFT);
        }

        $currentNumber = $range['current_number'] + 1;

        $updateSql = "UPDATE number_ranges SET current_number = ? WHERE id = ?";
        $this->db->execute($updateSql, [$currentNumber, $range['id']]);

        return generateInvoiceNumber(
            $range['prefix'],
            $currentNumber - 1,
            $range['digits'],
            $range['year_separator']
        );
    }
}
