<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <a href="<?= url('invoices') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Zurück zur Übersicht
            </a>
        </div>
        <div class="btn-group">
            <?php if ($invoice['invoice_status'] === 'draft'): ?>
            <a href="<?= url('invoices/' . $invoice['id'] . '/edit') ?>" class="btn btn-secondary">
                <i class="bi bi-pencil"></i> Bearbeiten
            </a>
            <?php endif; ?>
            <a href="<?= url('invoices/' . $invoice['id'] . '/pdf') ?>" class="btn btn-danger" target="_blank">
                <i class="bi bi-file-pdf"></i> PDF
            </a>
            <button type="button" class="btn btn-primary" onclick="sendInvoiceEmail()">
                <i class="bi bi-envelope"></i> Email senden
            </button>
        </div>
    </div>
</div>

<div class="row">
    <!-- Linke Spalte: Rechnung -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0"><?= e($invoice['invoice_number']) ?></h4>
                    <small class="text-muted">Erstellt am <?= formatDate($invoice['created_at']) ?></small>
                </div>
                <div>
                    <?php
                    $statusColors = [
                        'draft' => 'secondary',
                        'sent' => 'info',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        'cancelled' => 'dark'
                    ];
                    $color = $statusColors[$invoice['invoice_status']] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?= $color ?> fs-6">
                        <?= strtoupper($invoice['invoice_status']) ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <!-- Kundendaten -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Kunde</h6>
                        <div>
                            <strong>
                                <?= $customer['customer_type'] === 'b2b' ? e($customer['company_name']) : e($customer['first_name'] . ' ' . $customer['last_name']) ?>
                            </strong>
                        </div>
                        <div><?= e($customer['billing_street']) ?></div>
                        <div><?= e($customer['billing_zip']) ?> <?= e($customer['billing_city']) ?></div>
                        <div><?= e($customer['billing_country']) ?></div>
                        <?php if ($customer['email']): ?>
                        <div class="mt-2">
                            <i class="bi bi-envelope"></i> <a href="mailto:<?= e($customer['email']) ?>"><?= e($customer['email']) ?></a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 text-end">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-end"><strong>Rechnungsdatum:</strong></td>
                                <td class="text-end"><?= formatDate($invoice['invoice_date']) ?></td>
                            </tr>
                            <?php if ($invoice['delivery_date']): ?>
                            <tr>
                                <td class="text-end"><strong>Leistungsdatum:</strong></td>
                                <td class="text-end"><?= formatDate($invoice['delivery_date']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($invoice['due_date']): ?>
                            <tr>
                                <td class="text-end"><strong>Fällig am:</strong></td>
                                <td class="text-end">
                                    <?= formatDate($invoice['due_date']) ?>
                                    <?php if (isOverdue($invoice['due_date']) && $invoice['invoice_status'] !== 'paid'): ?>
                                        <span class="badge bg-danger ms-2">Überfällig</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- Betreff -->
                <?php if ($invoice['subject']): ?>
                <div class="mb-3">
                    <strong><?= e($invoice['subject']) ?></strong>
                </div>
                <?php endif; ?>

                <!-- Einleitungstext -->
                <?php if ($invoice['intro_text']): ?>
                <div class="mb-4">
                    <?= nl2br(e($invoice['intro_text'])) ?>
                </div>
                <?php endif; ?>

                <!-- Positionen -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Pos.</th>
                                <th>Bezeichnung</th>
                                <th class="text-center">Menge</th>
                                <th class="text-end">Einzelpreis</th>
                                <th class="text-center">MwSt.</th>
                                <th class="text-end">Gesamt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <strong><?= e($item['name']) ?></strong>
                                    <?php if ($item['description']): ?>
                                        <br><small class="text-muted"><?= nl2br(e($item['description'])) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?= number_format($item['quantity'], 2, ',', '.') ?> <?= e($item['unit']) ?>
                                </td>
                                <td class="text-end">
                                    <?= formatMoney($item['price_net']) ?>
                                    <?php if ($item['discount_percent'] > 0): ?>
                                        <br><small class="text-muted">- <?= number_format($item['discount_percent'], 2, ',', '.') ?>% Rabatt</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= number_format($item['tax_rate'], 0) ?>%</td>
                                <td class="text-end"><strong><?= formatMoney($item['total_gross']) ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end"><strong>Zwischensumme (Netto):</strong></td>
                                <td class="text-end"><?= formatMoney($invoice['total_net']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end"><strong>MwSt. gesamt:</strong></td>
                                <td class="text-end"><?= formatMoney($invoice['total_tax']) ?></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="5" class="text-end"><strong class="fs-5">Gesamtbetrag (Brutto):</strong></td>
                                <td class="text-end"><strong class="fs-5"><?= formatMoney($invoice['total_gross']) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Schlusstext -->
                <?php if ($invoice['outro_text']): ?>
                <div class="mb-3">
                    <?= nl2br(e($invoice['outro_text'])) ?>
                </div>
                <?php endif; ?>

                <!-- Zahlungsbedingungen -->
                <div class="alert alert-info">
                    <strong>Zahlungsbedingungen:</strong>
                    Zahlbar innerhalb von <?= $invoice['payment_terms_days'] ?> Tagen.
                    <?php if ($invoice['skonto_days'] && $invoice['skonto_percent']): ?>
                        Bei Zahlung innerhalb von <?= $invoice['skonto_days'] ?> Tagen <?= number_format($invoice['skonto_percent'], 2, ',', '.') ?>% Skonto.
                    <?php endif; ?>
                </div>

                <!-- Notizen -->
                <?php if ($invoice['notes']): ?>
                <div class="alert alert-warning">
                    <strong>Interne Notizen:</strong><br>
                    <?= nl2br(e($invoice['notes'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Rechte Spalte: Zahlungen & Aktionen -->
    <div class="col-md-4">
        <!-- Zahlungsstatus -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-credit-card"></i> Zahlungsstatus</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td>Rechnungsbetrag:</td>
                        <td class="text-end"><strong><?= formatMoney($invoice['total_gross']) ?></strong></td>
                    </tr>
                    <tr class="text-success">
                        <td>Bezahlt:</td>
                        <td class="text-end"><strong><?= formatMoney($invoice['paid_amount']) ?></strong></td>
                    </tr>
                    <tr class="<?= ($invoice['total_gross'] - $invoice['paid_amount']) > 0 ? 'text-danger' : 'text-muted' ?>">
                        <td>Offen:</td>
                        <td class="text-end"><strong><?= formatMoney($invoice['total_gross'] - $invoice['paid_amount']) ?></strong></td>
                    </tr>
                </table>

                <?php if ($invoice['invoice_status'] !== 'paid' && ($invoice['total_gross'] - $invoice['paid_amount']) > 0): ?>
                <button type="button" class="btn btn-success btn-sm w-100 mt-3" onclick="addPayment()">
                    <i class="bi bi-plus-circle"></i> Zahlung buchen
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Zahlungen -->
        <?php if (!empty($payments)): ?>
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Zahlungen</h5>
            </div>
            <div class="card-body">
                <?php foreach ($payments as $payment): ?>
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                    <div>
                        <div><?= formatDate($payment['payment_date']) ?></div>
                        <small class="text-muted"><?= e($payment['payment_method']) ?></small>
                    </div>
                    <div class="text-success fw-bold">
                        <?= formatMoney($payment['amount']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Aktionen -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Aktionen</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= url('invoices/' . $invoice['id'] . '/pdf') ?>" class="btn btn-outline-danger btn-sm" target="_blank">
                        <i class="bi bi-file-pdf"></i> PDF herunterladen
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="sendInvoiceEmail()">
                        <i class="bi bi-envelope"></i> Per Email senden
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="duplicateInvoice()">
                        <i class="bi bi-files"></i> Duplizieren
                    </button>
                    <a href="<?= url('credits/create?invoice=' . $invoice['id']) ?>" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-file-minus"></i> Gutschrift erstellen
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function sendInvoiceEmail() {
    if (confirm('Rechnung per Email an den Kunden senden?')) {
        window.location.href = '<?= url('invoices/' . $invoice['id'] . '/send') ?>';
    }
}

function addPayment() {
    // TODO: Modal für Zahlungseingabe
    alert('Zahlungseingabe wird in Kürze verfügbar sein.');
}

function duplicateInvoice() {
    if (confirm('Rechnung duplizieren?')) {
        alert('Duplizierfunktion wird in Kürze verfügbar sein.');
    }
}
</script>
