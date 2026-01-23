<div class="mb-4">
    <a href="<?= url('products') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Zurück zur Übersicht
    </a>
</div>

<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-box-seam"></i> Neues Produkt</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= url('products') ?>">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <!-- Produkttyp -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Produkttyp <span class="text-danger">*</span></label>
                    <select class="form-select" name="product_type" id="product_type" required>
                        <option value="product">Produkt (Physisch)</option>
                        <option value="service">Dienstleistung</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Einheit</label>
                    <select class="form-select" name="unit">
                        <option value="Stück">Stück</option>
                        <option value="Stunde">Stunde</option>
                        <option value="Tag">Tag</option>
                        <option value="Meter">Meter</option>
                        <option value="Kilogramm">Kilogramm</option>
                        <option value="Liter">Liter</option>
                        <option value="Paket">Paket</option>
                        <option value="Set">Set</option>
                    </select>
                </div>
            </div>

            <!-- Produktname -->
            <div class="mb-3">
                <label for="name" class="form-label">Produktname <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <!-- Beschreibung -->
            <div class="mb-3">
                <label for="description" class="form-label">Beschreibung</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>

            <!-- SKU & EAN -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="sku" class="form-label">SKU (Stock Keeping Unit)</label>
                    <input type="text" class="form-control" id="sku" name="sku">
                </div>
                <div class="col-md-6">
                    <label for="ean" class="form-label">EAN / GTIN</label>
                    <input type="text" class="form-control" id="ean" name="ean">
                </div>
            </div>

            <hr class="my-4">

            <!-- Preise -->
            <h6 class="mb-3"><i class="bi bi-currency-euro"></i> Preise</h6>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="price_net" class="form-label">Verkaufspreis (Netto) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control format-currency" id="price_net" name="price_net" value="0,00" required>
                        <span class="input-group-text">€</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="purchase_price" class="form-label">Einkaufspreis (Netto)</label>
                    <div class="input-group">
                        <input type="text" class="form-control format-currency" id="purchase_price" name="purchase_price" value="0,00">
                        <span class="input-group-text">€</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="tax_rate_id" class="form-label">Steuersatz</label>
                    <select class="form-select" name="tax_rate_id" id="tax_rate_id">
                        <option value="">Bitte wählen</option>
                        <?php foreach ($taxRates as $rate): ?>
                        <option value="<?= $rate['id'] ?>" <?= $rate['is_default'] ? 'selected' : '' ?>>
                            <?= e($rate['name']) ?> (<?= number_format($rate['rate'], 2, ',', '.') ?>%)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Marge:</strong> <span id="margin-display">- %</span>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- Lagerbestand (nur bei Produkten) -->
            <div id="stock-section">
                <h6 class="mb-3"><i class="bi bi-boxes"></i> Lagerbestand</h6>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="stock_quantity" class="form-label">Aktueller Bestand</label>
                        <input type="text" class="form-control format-currency" id="stock_quantity" name="stock_quantity" value="0,00">
                    </div>
                    <div class="col-md-6">
                        <label for="stock_min" class="form-label">Mindestbestand (Warnung)</label>
                        <input type="text" class="form-control format-currency" id="stock_min" name="stock_min" value="0,00">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="<?= url('products') ?>" class="btn btn-secondary">Abbrechen</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Produkt speichern
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle Lagerbestand basierend auf Produkttyp
document.getElementById('product_type').addEventListener('change', function() {
    const stockSection = document.getElementById('stock-section');
    if (this.value === 'product') {
        stockSection.style.display = 'block';
    } else {
        stockSection.style.display = 'none';
    }
});

// Marge berechnen
function calculateMargin() {
    const priceNet = parseFloat(document.getElementById('price_net').value.replace(',', '.')) || 0;
    const purchasePrice = parseFloat(document.getElementById('purchase_price').value.replace(',', '.')) || 0;

    if (purchasePrice > 0 && priceNet > 0) {
        const margin = ((priceNet - purchasePrice) / purchasePrice * 100);
        document.getElementById('margin-display').textContent = margin.toFixed(2).replace('.', ',') + ' %';

        if (margin < 0) {
            document.getElementById('margin-display').className = 'text-danger fw-bold';
        } else if (margin < 20) {
            document.getElementById('margin-display').className = 'text-warning fw-bold';
        } else {
            document.getElementById('margin-display').className = 'text-success fw-bold';
        }
    } else {
        document.getElementById('margin-display').textContent = '- %';
    }
}

document.getElementById('price_net').addEventListener('blur', calculateMargin);
document.getElementById('purchase_price').addEventListener('blur', calculateMargin);
</script>
