<?php
namespace App\Models;

use Libs\Model;

/**
 * Product Model
 */
class Product extends Model {
    protected $table = 'products';
    protected $primaryKey = 'id';

    protected $fillable = [
        'client_id',
        'category_id',
        'product_number',
        'sku',
        'ean',
        'name',
        'description',
        'product_type',
        'unit',
        'price_net',
        'price_gross',
        'purchase_price',
        'tax_rate_id',
        'stock_quantity',
        'stock_min',
        'is_active',
        'shopify_product_id',
        'shopify_variant_id'
    ];

    /**
     * Hole aktive Produkte
     */
    public function getActiveProducts($clientId = 1) {
        return $this->where([
            'client_id' => $clientId,
            'is_active' => 1
        ], 'product_number', 'DESC');
    }

    /**
     * Suche Produkte
     */
    public function search($term, $clientId = 1) {
        $sql = "SELECT * FROM {$this->table}
                WHERE client_id = ?
                AND is_active = 1
                AND (
                    product_number LIKE ?
                    OR name LIKE ?
                    OR sku LIKE ?
                    OR ean LIKE ?
                )
                ORDER BY product_number DESC
                LIMIT 20";

        $searchTerm = "%{$term}%";
        return $this->db->fetchAll($sql, [
            $clientId,
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm
        ]);
    }

    /**
     * Generiere nächste Produktnummer
     */
    public function generateProductNumber($clientId = 1) {
        $sql = "SELECT * FROM number_ranges
                WHERE client_id = ?
                AND type = 'product'
                AND is_default = 1
                LIMIT 1";

        $range = $this->db->fetchOne($sql, [$clientId]);

        if (!$range) {
            return 'ART-' . str_pad(1, 5, '0', STR_PAD_LEFT);
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

    /**
     * Update Lagerbestand
     */
    public function updateStock($productId, $quantity, $operation = 'subtract') {
        $sql = "UPDATE {$this->table}
                SET stock_quantity = stock_quantity " . ($operation === 'add' ? '+' : '-') . " ?
                WHERE {$this->primaryKey} = ?";

        return $this->db->execute($sql, [$quantity, $productId]);
    }

    /**
     * Hole Produkte mit niedrigem Lagerbestand
     */
    public function getLowStockProducts($clientId = 1) {
        $sql = "SELECT * FROM {$this->table}
                WHERE client_id = ?
                AND is_active = 1
                AND product_type = 'product'
                AND stock_quantity <= stock_min
                ORDER BY stock_quantity ASC";

        return $this->db->fetchAll($sql, [$clientId]);
    }
}
