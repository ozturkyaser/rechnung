<?php
namespace App\Models;

use Libs\Model;

/**
 * Invoice Item Model
 */
class InvoiceItem extends Model {
    protected $table = 'invoice_items';
    protected $primaryKey = 'id';

    protected $fillable = [
        'invoice_id',
        'product_id',
        'position',
        'item_type',
        'name',
        'description',
        'quantity',
        'unit',
        'price_net',
        'discount_percent',
        'tax_rate',
        'tax_rate_id',
        'subtotal_net',
        'tax_amount',
        'total_gross',
        'is_optional'
    ];

    /**
     * Hole Items für Rechnung
     */
    public function getItemsForInvoice($invoiceId) {
        return $this->where(['invoice_id' => $invoiceId], 'position', 'ASC');
    }

    /**
     * Lösche alle Items einer Rechnung
     */
    public function deleteForInvoice($invoiceId) {
        $sql = "DELETE FROM {$this->table} WHERE invoice_id = ?";
        return $this->db->execute($sql, [$invoiceId]);
    }
}
