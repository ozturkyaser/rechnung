<div class="mb-4">
    <a href="<?= url('customers') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Zurück zur Übersicht
    </a>
</div>

<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-person-plus"></i> Neuer Kunde</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= url('customers') ?>">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <!-- Kundentyp -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Kundentyp <span class="text-danger">*</span></label>
                    <select class="form-select" name="customer_type" id="customer_type" required>
                        <option value="b2b">Geschäftskunde (B2B)</option>
                        <option value="b2c">Privatkunde (B2C)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Steuerregion</label>
                    <select class="form-select" name="tax_region">
                        <option value="domestic">Inland</option>
                        <option value="eu">EU</option>
                        <option value="non_eu">Nicht-EU</option>
                    </select>
                </div>
            </div>

            <!-- Firmenname (B2B) -->
            <div class="mb-3" id="company-field">
                <label for="company_name" class="form-label">Firmenname <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="company_name" name="company_name">
            </div>

            <!-- Personendaten -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="first_name" class="form-label">Vorname</label>
                    <input type="text" class="form-control" id="first_name" name="first_name">
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label">Nachname</label>
                    <input type="text" class="form-control" id="last_name" name="last_name">
                </div>
            </div>

            <!-- Kontaktdaten -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="email" class="form-label">E-Mail</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>
                <div class="col-md-4">
                    <label for="phone" class="form-label">Telefon</label>
                    <input type="text" class="form-control" id="phone" name="phone">
                </div>
                <div class="col-md-4">
                    <label for="mobile" class="form-label">Mobil</label>
                    <input type="text" class="form-control" id="mobile" name="mobile">
                </div>
            </div>

            <div class="mb-3">
                <label for="website" class="form-label">Webseite</label>
                <input type="url" class="form-control" id="website" name="website" placeholder="https://">
            </div>

            <!-- Steuerdaten -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="tax_id" class="form-label">Steuernummer</label>
                    <input type="text" class="form-control" id="tax_id" name="tax_id">
                </div>
                <div class="col-md-6">
                    <label for="vat_id" class="form-label">USt-IdNr.</label>
                    <input type="text" class="form-control" id="vat_id" name="vat_id" placeholder="DE123456789">
                </div>
            </div>

            <hr class="my-4">

            <!-- Rechnungsadresse -->
            <h6 class="mb-3"><i class="bi bi-geo-alt"></i> Rechnungsadresse</h6>

            <div class="mb-3">
                <label for="billing_street" class="form-label">Straße, Hausnummer</label>
                <input type="text" class="form-control" id="billing_street" name="billing_street">
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="billing_zip" class="form-label">PLZ</label>
                    <input type="text" class="form-control" id="billing_zip" name="billing_zip">
                </div>
                <div class="col-md-4">
                    <label for="billing_city" class="form-label">Ort</label>
                    <input type="text" class="form-control" id="billing_city" name="billing_city">
                </div>
                <div class="col-md-4">
                    <label for="billing_country" class="form-label">Land</label>
                    <input type="text" class="form-control" id="billing_country" name="billing_country" value="Deutschland">
                </div>
            </div>

            <hr class="my-4">

            <!-- Lieferadresse -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0"><i class="bi bi-truck"></i> Lieferadresse</h6>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyBillingToShipping()">
                    <i class="bi bi-copy"></i> Von Rechnungsadresse kopieren
                </button>
            </div>

            <div class="mb-3">
                <label for="shipping_street" class="form-label">Straße, Hausnummer</label>
                <input type="text" class="form-control" id="shipping_street" name="shipping_street">
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="shipping_zip" class="form-label">PLZ</label>
                    <input type="text" class="form-control" id="shipping_zip" name="shipping_zip">
                </div>
                <div class="col-md-4">
                    <label for="shipping_city" class="form-label">Ort</label>
                    <input type="text" class="form-control" id="shipping_city" name="shipping_city">
                </div>
                <div class="col-md-4">
                    <label for="shipping_country" class="form-label">Land</label>
                    <input type="text" class="form-control" id="shipping_country" name="shipping_country">
                </div>
            </div>

            <hr class="my-4">

            <!-- Zahlungsbedingungen -->
            <h6 class="mb-3"><i class="bi bi-credit-card"></i> Zahlungsbedingungen</h6>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="payment_terms_days" class="form-label">Zahlungsziel (Tage)</label>
                    <input type="number" class="form-control" id="payment_terms_days" name="payment_terms_days" value="<?= DEFAULT_PAYMENT_TERMS ?>">
                </div>
                <div class="col-md-4">
                    <label for="skonto_days" class="form-label">Skonto-Tage</label>
                    <input type="number" class="form-control" id="skonto_days" name="skonto_days" value="<?= DEFAULT_SKONTO_DAYS ?>">
                </div>
                <div class="col-md-4">
                    <label for="skonto_percent" class="form-label">Skonto (%)</label>
                    <input type="number" step="0.01" class="form-control format-currency" id="skonto_percent" name="skonto_percent" value="<?= DEFAULT_SKONTO_PERCENT ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="credit_limit" class="form-label">Kreditlimit (EUR)</label>
                <input type="number" step="0.01" class="form-control format-currency" id="credit_limit" name="credit_limit" value="0.00">
            </div>

            <!-- Notizen -->
            <div class="mb-3">
                <label for="notes" class="form-label">Notizen</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= url('customers') ?>" class="btn btn-secondary">Abbrechen</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Kunde speichern
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Kopiere Rechnungsadresse zur Lieferadresse
function copyBillingToShipping() {
    document.getElementById('shipping_street').value = document.getElementById('billing_street').value;
    document.getElementById('shipping_zip').value = document.getElementById('billing_zip').value;
    document.getElementById('shipping_city').value = document.getElementById('billing_city').value;
    document.getElementById('shipping_country').value = document.getElementById('billing_country').value;
}

// Toggle Firmenname basierend auf Kundentyp
document.getElementById('customer_type').addEventListener('change', function() {
    const companyField = document.getElementById('company-field');
    const companyInput = document.getElementById('company_name');

    if (this.value === 'b2b') {
        companyField.style.display = 'block';
        companyInput.required = true;
    } else {
        companyField.style.display = 'none';
        companyInput.required = false;
    }
});
</script>
