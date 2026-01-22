<?php
namespace App\Models;

use Libs\Model;

/**
 * Customer Model
 */
class Customer extends Model {
    protected $table = 'customers';
    protected $primaryKey = 'id';

    protected $fillable = [
        'client_id',
        'customer_number',
        'customer_type',
        'company_name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'website',
        'tax_id',
        'vat_id',
        'tax_region',
        'billing_street',
        'billing_zip',
        'billing_city',
        'billing_country',
        'shipping_street',
        'shipping_zip',
        'shipping_city',
        'shipping_country',
        'payment_terms_days',
        'skonto_days',
        'skonto_percent',
        'credit_limit',
        'notes',
        'is_active',
        'shopify_customer_id'
    ];

    /**
     * Hole aktive Kunden
     */
    public function getActiveCustomers($clientId = 1) {
        return $this->where([
            'client_id' => $clientId,
            'is_active' => 1
        ], 'customer_number', 'DESC');
    }

    /**
     * Suche Kunden
     */
    public function search($term, $clientId = 1) {
        $sql = "SELECT * FROM {$this->table}
                WHERE client_id = ?
                AND is_active = 1
                AND (
                    customer_number LIKE ?
                    OR company_name LIKE ?
                    OR first_name LIKE ?
                    OR last_name LIKE ?
                    OR email LIKE ?
                )
                ORDER BY customer_number DESC
                LIMIT 20";

        $searchTerm = "%{$term}%";
        return $this->db->fetchAll($sql, [
            $clientId,
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm
        ]);
    }

    /**
     * Generiere nächste Kundennummer
     */
    public function generateCustomerNumber($clientId = 1) {
        // Hole Nummernkreis
        $sql = "SELECT * FROM number_ranges
                WHERE client_id = ?
                AND type = 'customer'
                AND is_default = 1
                LIMIT 1";

        $range = $this->db->fetchOne($sql, [$clientId]);

        if (!$range) {
            // Fallback
            return 'KD-' . str_pad(1, 5, '0', STR_PAD_LEFT);
        }

        $currentNumber = $range['current_number'] + 1;

        // Update Nummer
        $updateSql = "UPDATE number_ranges SET current_number = ? WHERE id = ?";
        $this->db->execute($updateSql, [$currentNumber, $range['id']]);

        return generateInvoiceNumber(
            $range['prefix'],
            $currentNumber - 1,
            $range['digits'],
            $range['year_separator']
        );
    }

    /**
     * Hole Kundenstatistiken
     */
    public function getCustomerStats($customerId) {
        $sql = "SELECT
                    COUNT(*) as total_invoices,
                    SUM(CASE WHEN invoice_status = 'paid' THEN total_gross ELSE 0 END) as total_revenue,
                    SUM(CASE WHEN invoice_status IN ('draft', 'sent', 'viewed', 'overdue') THEN total_gross ELSE 0 END) as open_amount
                FROM invoices
                WHERE customer_id = ?
                AND invoice_type = 'invoice'";

        return $this->db->fetchOne($sql, [$customerId]);
    }
}
