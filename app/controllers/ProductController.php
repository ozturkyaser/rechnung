<?php
namespace App\Controllers;

use Libs\Controller;
use App\Models\Product;

/**
 * Product Controller
 */
class ProductController extends Controller {
    private $productModel;

    public function __construct() {
        $this->productModel = new Product();
    }

    /**
     * Liste aller Produkte
     */
    public function index() {
        $this->requireAuth();

        $page = $this->get('page', 1);
        $search = $this->get('search', '');

        if ($search) {
            $products = $this->productModel->search($search);
            $pagination = null;
        } else {
            $pagination = $this->productModel->paginate($page, 20, ['client_id' => 1], 'id', 'DESC');
            $products = $pagination['data'];
        }

        $this->view('products.index', [
            'title' => 'Produkte',
            'subtitle' => 'Produktverwaltung',
            'activeMenu' => 'products',
            'products' => $products,
            'pagination' => $pagination,
            'search' => $search
        ]);
    }

    /**
     * Zeige einzelnes Produkt
     */
    public function show($id) {
        $this->requireAuth();

        $product = $this->productModel->find($id);

        if (!$product) {
            setFlash('error', 'Produkt nicht gefunden.');
            $this->redirect(url('products'));
        }

        $this->view('products.show', [
            'title' => 'Produkt Details',
            'subtitle' => $product['product_number'],
            'activeMenu' => 'products',
            'product' => $product
        ]);
    }

    /**
     * Formular für neues Produkt
     */
    public function create() {
        $this->requireRole('sales');

        // Hole Steuersätze
        $taxRates = $this->getTaxRates();

        $this->view('products.create', [
            'title' => 'Neues Produkt',
            'subtitle' => 'Produkt anlegen',
            'activeMenu' => 'products',
            'taxRates' => $taxRates
        ]);
    }

    /**
     * Speichere neues Produkt
     */
    public function store() {
        $this->requireRole('sales');
        $this->validateCsrf();

        $priceNet = (float)str_replace(',', '.', $this->post('price_net', '0'));
        $purchasePrice = (float)str_replace(',', '.', $this->post('purchase_price', '0'));
        $stockQuantity = (float)str_replace(',', '.', $this->post('stock_quantity', '0'));
        $stockMin = (float)str_replace(',', '.', $this->post('stock_min', '0'));

        $data = [
            'client_id' => 1,
            'product_number' => $this->productModel->generateProductNumber(),
            'sku' => $this->post('sku'),
            'ean' => $this->post('ean'),
            'name' => $this->post('name'),
            'description' => $this->post('description'),
            'product_type' => $this->post('product_type', 'product'),
            'unit' => $this->post('unit', 'Stück'),
            'price_net' => $priceNet,
            'purchase_price' => $purchasePrice,
            'tax_rate_id' => $this->post('tax_rate_id') ?: null,
            'stock_quantity' => $stockQuantity,
            'stock_min' => $stockMin,
            'is_active' => 1
        ];

        // Validierung
        $errors = $this->validate($data, [
            'name' => 'required'
        ]);

        if (!empty($errors)) {
            setFlash('error', 'Bitte füllen Sie alle Pflichtfelder aus.');
            $this->redirectBack();
        }

        try {
            $productId = $this->productModel->create($data);

            logMessage("Product created: ID {$productId}");
            setFlash('success', 'Produkt erfolgreich angelegt.');
            $this->redirect(url('products/' . $productId));
        } catch (\Exception $e) {
            logMessage("Error creating product: " . $e->getMessage(), 'error');
            setFlash('error', 'Fehler beim Anlegen des Produkts.');
            $this->redirectBack();
        }
    }

    /**
     * Formular zum Bearbeiten
     */
    public function edit($id) {
        $this->requireRole('sales');

        $product = $this->productModel->find($id);

        if (!$product) {
            setFlash('error', 'Produkt nicht gefunden.');
            $this->redirect(url('products'));
        }

        $taxRates = $this->getTaxRates();

        $this->view('products.edit', [
            'title' => 'Produkt bearbeiten',
            'subtitle' => $product['product_number'],
            'activeMenu' => 'products',
            'product' => $product,
            'taxRates' => $taxRates
        ]);
    }

    /**
     * Aktualisiere Produkt
     */
    public function update($id) {
        $this->requireRole('sales');
        $this->validateCsrf();

        $product = $this->productModel->find($id);

        if (!$product) {
            setFlash('error', 'Produkt nicht gefunden.');
            $this->redirect(url('products'));
        }

        $priceNet = (float)str_replace(',', '.', $this->post('price_net', '0'));
        $purchasePrice = (float)str_replace(',', '.', $this->post('purchase_price', '0'));
        $stockQuantity = (float)str_replace(',', '.', $this->post('stock_quantity', '0'));
        $stockMin = (float)str_replace(',', '.', $this->post('stock_min', '0'));

        $data = [
            'sku' => $this->post('sku'),
            'ean' => $this->post('ean'),
            'name' => $this->post('name'),
            'description' => $this->post('description'),
            'product_type' => $this->post('product_type'),
            'unit' => $this->post('unit'),
            'price_net' => $priceNet,
            'purchase_price' => $purchasePrice,
            'tax_rate_id' => $this->post('tax_rate_id') ?: null,
            'stock_quantity' => $stockQuantity,
            'stock_min' => $stockMin,
            'is_active' => $this->post('is_active', 1)
        ];

        try {
            $this->productModel->update($id, $data);

            logMessage("Product updated: ID {$id}");
            setFlash('success', 'Produkt erfolgreich aktualisiert.');
            $this->redirect(url('products/' . $id));
        } catch (\Exception $e) {
            logMessage("Error updating product: " . $e->getMessage(), 'error');
            setFlash('error', 'Fehler beim Aktualisieren des Produkts.');
            $this->redirectBack();
        }
    }

    /**
     * Lösche Produkt (Soft Delete)
     */
    public function delete($id) {
        $this->requireRole('admin');
        $this->validateCsrf();

        try {
            $this->productModel->update($id, ['is_active' => 0]);

            logMessage("Product deactivated: ID {$id}");
            setFlash('success', 'Produkt erfolgreich deaktiviert.');
        } catch (\Exception $e) {
            logMessage("Error deleting product: " . $e->getMessage(), 'error');
            setFlash('error', 'Fehler beim Löschen des Produkts.');
        }

        $this->redirect(url('products'));
    }

    /**
     * Hole Steuersätze
     */
    private function getTaxRates() {
        $db = \Libs\Database::getInstance();
        return $db->fetchAll("SELECT * FROM tax_rates WHERE client_id = 1 AND is_active = 1 ORDER BY rate DESC");
    }
}
