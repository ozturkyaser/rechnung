<div class="mb-4">
    <a href="<?= url('offers') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Zurück zur Übersicht
    </a>
</div>

<form method="POST" action="<?= url('offers') ?>" id="offer-form">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

    <div class="row">
        <!-- Linke Spalte -->
        <div class="col-md-8">
            <!-- Kunde auswählen -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Kunde</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Kunde auswählen <span class="text-danger">*</span></label>
                        <select class="form-select" name="customer_id" id="customer_id" required>
                            <option value="">Bitte wählen...</option>
                            <?php foreach ($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>">
                                <?= e($customer['customer_number']) ?> -
                                <?= $customer['customer_type'] === 'b2b' ? e($customer['company_name']) : e($customer['first_name'] . ' ' . $customer['last_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Positionen -->
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Positionen</h5>
                    <button type="button" class="btn btn-sm btn-success" onclick="addOfferItem()">
                        <i class="bi bi-plus-circle"></i> Position hinzufügen
                    </button>
                </div>
                <div class="card-body">
                    <div id="offer-items">
                        <!-- Items werden hier dynamisch hinzugefügt -->
                    </div>

                    <!-- Produkt schnell hinzufügen -->
                    <div class="mt-3">
                        <label class="form-label">Produkt auswählen</label>
                        <select class="form-select" id="product-select" onchange="addProductToOffer()">
                            <option value="">Produkt auswählen...</option>
                            <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>"
                                    data-name="<?= e($product['name']) ?>"
                                    data-description="<?= e($product['description']) ?>"
                                    data-price="<?= $product['price_net'] ?>"
                                    data-unit="<?= e($product['unit']) ?>"
                                    data-tax="<?= $product['tax_rate_id'] ?>">
                                <?= e($product['product_number']) ?> - <?= e($product['name']) ?> (<?= formatMoney($product['price_net']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Texte -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-text-left"></i> Texte</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="subject" class="form-label">Betreff</label>
                        <input type="text" class="form-control" id="subject" name="subject" placeholder="z.B. Angebot für Dienstleistungen">
                    </div>

                    <div class="mb-3">
                        <label for="intro_text" class="form-label">Einleitungstext</label>
                        <textarea class="form-control" id="intro_text" name="intro_text" rows="3" placeholder="vielen Dank für Ihre Anfrage. Gerne unterbreiten wir Ihnen folgendes Angebot:"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="outro_text" class="form-label">Schlusstext</label>
                        <textarea class="form-control" id="outro_text" name="outro_text" rows="3" placeholder="Wir freuen uns auf Ihre Bestellung und stehen Ihnen für Rückfragen gerne zur Verfügung."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rechte Spalte -->
        <div class="col-md-4">
            <!-- Angebotsdaten -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-calendar"></i> Angebotsdaten</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="invoice_date" class="form-label">Angebotsdatum</label>
                        <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="valid_until" class="form-label">Gültig bis</label>
                        <input type="date" class="form-control" id="valid_until" name="valid_until" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                        <small class="text-muted">Standard: 30 Tage ab Angebotsdatum</small>
                    </div>
                </div>
            </div>

            <!-- Summen -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-calculator"></i> Summen</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td>Zwischensumme (Netto):</td>
                                <td class="text-end" id="display-subtotal-net">0,00 €</td>
                            </tr>
                            <tr>
                                <td>MwSt. gesamt:</td>
                                <td class="text-end" id="display-total-tax">0,00 €</td>
                            </tr>
                            <tr class="fw-bold">
                                <td>Gesamtbetrag (Brutto):</td>
                                <td class="text-end fs-5" id="display-total-gross">0,00 €</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Optionen -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Optionen</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="is_reverse_charge" name="is_reverse_charge" value="1">
                        <label class="form-check-label" for="is_reverse_charge">
                            Reverse-Charge
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="is_small_business" name="is_small_business" value="1">
                        <label class="form-check-label" for="is_small_business">
                            Kleinunternehmer (§19 UStG)
                        </label>
                    </div>
                </div>
            </div>

            <!-- Notizen -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-journal-text"></i> Notizen</h5>
                </div>
                <div class="card-body">
                    <textarea class="form-control" name="notes" rows="3" placeholder="Interne Notizen (nicht auf dem Angebot sichtbar)"></textarea>
                </div>
            </div>

            <!-- Aktionen -->
            <div class="d-grid gap-2">
                <button type="submit" name="action" value="save_draft" class="btn btn-secondary">
                    <i class="bi bi-save"></i> Als Entwurf speichern
                </button>
                <button type="submit" name="action" value="save_and_send" class="btn btn-primary">
                    <i class="bi bi-send"></i> Speichern & Versenden
                </button>
                <a href="<?= url('offers') ?>" class="btn btn-outline-secondary">Abbrechen</a>
            </div>
        </div>
    </div>
</form>

<!-- Item Template -->
<template id="item-template">
    <div class="item-row border rounded p-3 mb-3 bg-light">
        <div class="row">
            <div class="col-12 mb-2">
                <div class="d-flex justify-content-between">
                    <strong>Position <span class="position-number"></span></strong>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeOfferItem(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>

            <div class="col-md-8 mb-2">
                <label class="form-label small">Bezeichnung</label>
                <input type="text" class="form-control form-control-sm" name="items[][name]" required>
            </div>

            <div class="col-md-4 mb-2">
                <label class="form-label small">Typ</label>
                <select class="form-select form-control-sm" name="items[][type]">
                    <option value="product">Produkt</option>
                    <option value="service">Dienstleistung</option>
                    <option value="text">Text</option>
                </select>
            </div>

            <div class="col-12 mb-2">
                <label class="form-label small">Beschreibung</label>
                <textarea class="form-control form-control-sm" name="items[][description]" rows="2"></textarea>
            </div>

            <div class="col-md-3 mb-2">
                <label class="form-label small">Menge</label>
                <input type="number" step="0.01" class="form-control form-control-sm item-quantity" name="items[][quantity]" value="1" onchange="calculateItemTotal(this)">
            </div>

            <div class="col-md-3 mb-2">
                <label class="form-label small">Einheit</label>
                <select class="form-select form-control-sm" name="items[][unit]">
                    <option value="Stück">Stück</option>
                    <option value="Stunde">Stunde</option>
                    <option value="Tag">Tag</option>
                    <option value="Pauschal">Pauschal</option>
                </select>
            </div>

            <div class="col-md-3 mb-2">
                <label class="form-label small">Preis (Netto)</label>
                <input type="text" class="form-control form-control-sm item-price" name="items[][price_net]" value="0,00" onchange="calculateItemTotal(this)">
            </div>

            <div class="col-md-3 mb-2">
                <label class="form-label small">Rabatt %</label>
                <input type="number" step="0.01" class="form-control form-control-sm item-discount" name="items[][discount_percent]" value="0" onchange="calculateItemTotal(this)">
            </div>

            <div class="col-md-6 mb-2">
                <label class="form-label small">MwSt.</label>
                <select class="form-select form-control-sm item-tax" name="items[][tax_rate]" onchange="calculateItemTotal(this)">
                    <?php foreach ($taxRates as $rate): ?>
                    <option value="<?= $rate['rate'] ?>" data-id="<?= $rate['id'] ?>" <?= $rate['is_default'] ? 'selected' : '' ?>>
                        <?= e($rate['name']) ?> (<?= number_format($rate['rate'], 2, ',', '.') ?>%)
                    </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="items[][tax_rate_id]" class="item-tax-id">
            </div>

            <div class="col-md-6 mb-2">
                <label class="form-label small">Summe (Brutto)</label>
                <div class="form-control form-control-sm bg-white item-total">0,00 €</div>
            </div>

            <div class="col-12 mb-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="items[][is_optional]" value="1">
                    <label class="form-check-label small">
                        Optional (nicht im Gesamtpreis enthalten)
                    </label>
                </div>
            </div>

            <input type="hidden" name="items[][product_id]" class="item-product-id">
        </div>
    </div>
</template>

<script>
let itemCounter = 0;

// Füge initiale Position hinzu
document.addEventListener('DOMContentLoaded', function() {
    addOfferItem();
});

// Position hinzufügen
function addOfferItem() {
    const template = document.getElementById('item-template');
    const clone = template.content.cloneNode(true);

    itemCounter++;
    clone.querySelector('.position-number').textContent = itemCounter;

    document.getElementById('offer-items').appendChild(clone);
    updateTotals();
}

// Position entfernen
function removeOfferItem(button) {
    if (document.querySelectorAll('.item-row').length > 1) {
        button.closest('.item-row').remove();
        updateTotals();
        renumberPositions();
    } else {
        alert('Mindestens eine Position muss vorhanden sein.');
    }
}

// Positionen neu nummerieren
function renumberPositions() {
    document.querySelectorAll('.item-row').forEach((row, index) => {
        row.querySelector('.position-number').textContent = index + 1;
    });
}

// Produkt zum Angebot hinzufügen
function addProductToOffer() {
    const select = document.getElementById('product-select');
    const option = select.options[select.selectedIndex];

    if (!option.value) return;

    addOfferItem();

    const lastItem = document.querySelector('.item-row:last-child');
    lastItem.querySelector('[name="items[][name]"]').value = option.dataset.name;
    lastItem.querySelector('[name="items[][description]"]').value = option.dataset.description || '';
    lastItem.querySelector('[name="items[][price_net]"]').value = parseFloat(option.dataset.price).toFixed(2).replace('.', ',');
    lastItem.querySelector('[name="items[][unit]"]').value = option.dataset.unit || 'Stück';
    lastItem.querySelector('.item-product-id').value = option.value;

    if (option.dataset.tax) {
        const taxSelect = lastItem.querySelector('[name="items[][tax_rate]"]');
        const taxOption = taxSelect.querySelector(`option[data-id="${option.dataset.tax}"]`);
        if (taxOption) {
            taxSelect.value = taxOption.value;
        }
    }

    calculateItemTotal(lastItem.querySelector('.item-price'));
    select.value = '';
}

// Berechne Position Summe
function calculateItemTotal(input) {
    const row = input.closest('.item-row');
    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
    const price = parseFloat(row.querySelector('.item-price').value.replace(',', '.')) || 0;
    const discount = parseFloat(row.querySelector('.item-discount').value) || 0;
    const taxRate = parseFloat(row.querySelector('.item-tax').value) || 19;

    const subtotal = quantity * price;
    const discountAmount = subtotal * (discount / 100);
    const netTotal = subtotal - discountAmount;
    const taxAmount = netTotal * (taxRate / 100);
    const grossTotal = netTotal + taxAmount;

    row.querySelector('.item-total').textContent = grossTotal.toFixed(2).replace('.', ',') + ' €';

    // Setze tax_rate_id
    const taxSelect = row.querySelector('.item-tax');
    const selectedOption = taxSelect.options[taxSelect.selectedIndex];
    row.querySelector('.item-tax-id').value = selectedOption.dataset.id || '';

    updateTotals();
}

// Aktualisiere Gesamtsummen
function updateTotals() {
    let subtotalNet = 0;
    let totalTax = 0;

    document.querySelectorAll('.item-row').forEach(row => {
        const isOptional = row.querySelector('[name="items[][is_optional]"]').checked;

        // Optionale Positionen nicht in Gesamtsumme einbeziehen
        if (isOptional) {
            return;
        }

        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value.replace(',', '.')) || 0;
        const discount = parseFloat(row.querySelector('.item-discount').value) || 0;
        const taxRate = parseFloat(row.querySelector('.item-tax').value) || 19;

        const itemSubtotal = quantity * price;
        subtotalNet += itemSubtotal;

        const discountAmount = itemSubtotal * (discount / 100);
        const netAfterDiscount = itemSubtotal - discountAmount;
        const taxAmount = netAfterDiscount * (taxRate / 100);

        totalTax += taxAmount;
    });

    const totalNet = subtotalNet;
    const totalGross = totalNet + totalTax;

    document.getElementById('display-subtotal-net').textContent = subtotalNet.toFixed(2).replace('.', ',') + ' €';
    document.getElementById('display-total-tax').textContent = totalTax.toFixed(2).replace('.', ',') + ' €';
    document.getElementById('display-total-gross').textContent = totalGross.toFixed(2).replace('.', ',') + ' €';
}

// Event Listener für Optional-Checkbox
document.addEventListener('change', function(e) {
    if (e.target && e.target.name === 'items[][is_optional]') {
        updateTotals();
    }
});
</script>
