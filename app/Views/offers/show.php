<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <a href="<?= url('offers') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Zurück zur Übersicht
            </a>
        </div>
        <div class="btn-group">
            <?php if ($offer['invoice_status'] === 'draft'): ?>
            <a href="<?= url('offers/' . $offer['id'] . '/edit') ?>" class="btn btn-secondary">
                <i class="bi bi-pencil"></i> Bearbeiten
            </a>
            <?php endif; ?>
            <a href="<?= url('offers/' . $offer['id'] . '/pdf') ?>" class="btn btn-danger" target="_blank">
                <i class="bi bi-file-pdf"></i> PDF
            </a>
            <button type="button" class="btn btn-primary" onclick="sendOfferEmail()">
                <i class="bi bi-envelope"></i> Email senden
            </button>
        </div>
    </div>
</div>

<div class="row">
    <!-- Linke Spalte: Angebot -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0"><?= e($offer['invoice_number']) ?></h4>
                    <small class="text-muted">Erstellt am <?= formatDate($offer['created_at']) ?></small>
                </div>
                <div>
                    <?php
                    $statusColors = [
                        'draft' => 'secondary',
                        'sent' => 'info',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'expired' => 'warning',
                        'converted' => 'primary'
                    ];
                    $statusLabels = [
                        'draft' => 'Entwurf',
                        'sent' => 'Versendet',
                        'accepted' => 'Angenommen',
                        'rejected' => 'Abgelehnt',
                        'expired' => 'Abgelaufen',
                        'converted' => 'Umgewandelt'
                    ];
                    $color = $statusColors[$offer['invoice_status']] ?? 'secondary';
                    $label = $statusLabels[$offer['invoice_status']] ?? strtoupper($offer['invoice_status']);
                    ?>
                    <span class="badge bg-<?= $color ?> fs-6">
                        <?= e($label) ?>
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
                                <td class="text-end"><strong>Angebotsdatum:</strong></td>
                                <td class="text-end"><?= formatDate($offer['invoice_date']) ?></td>
                            </tr>
                            <?php if ($offer['valid_until']): ?>
                            <tr>
                                <td class="text-end"><strong>Gültig bis:</strong></td>
                                <td class="text-end">
                                    <?= formatDate($offer['valid_until']) ?>
                                    <?php if (strtotime($offer['valid_until']) < time() && !in_array($offer['invoice_status'], ['accepted', 'converted', 'rejected'])): ?>
                                        <span class="badge bg-warning ms-2">Abgelaufen</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- Betreff -->
                <?php if ($offer['subject']): ?>
                <div class="mb-3">
                    <strong><?= e($offer['subject']) ?></strong>
                </div>
                <?php endif; ?>

                <!-- Einleitungstext -->
                <?php if ($offer['intro_text']): ?>
                <div class="mb-4">
                    <?= nl2br(e($offer['intro_text'])) ?>
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
                            <tr <?= !empty($item['is_optional']) ? 'class="table-secondary"' : '' ?>>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <strong><?= e($item['name']) ?></strong>
                                    <?php if ($item['description']): ?>
                                        <br><small class="text-muted"><?= nl2br(e($item['description'])) ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($item['is_optional'])): ?>
                                        <br><span class="badge bg-secondary">Optional</span>
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
                                <td class="text-end"><?= formatMoney($offer['total_net']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end"><strong>MwSt. gesamt:</strong></td>
                                <td class="text-end"><?= formatMoney($offer['total_tax']) ?></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="5" class="text-end"><strong class="fs-5">Gesamtbetrag (Brutto):</strong></td>
                                <td class="text-end"><strong class="fs-5"><?= formatMoney($offer['total_gross']) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Schlusstext -->
                <?php if ($offer['outro_text']): ?>
                <div class="mb-3">
                    <?= nl2br(e($offer['outro_text'])) ?>
                </div>
                <?php endif; ?>

                <!-- Gültigkeit -->
                <?php if ($offer['valid_until']): ?>
                <div class="alert alert-info">
                    <strong>Gültigkeit:</strong>
                    Dieses Angebot ist gültig bis zum <?= formatDate($offer['valid_until']) ?>.
                </div>
                <?php endif; ?>

                <!-- Notizen -->
                <?php if ($offer['notes']): ?>
                <div class="alert alert-warning">
                    <strong>Interne Notizen:</strong><br>
                    <?= nl2br(e($offer['notes'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Rechte Spalte: Status & Aktionen -->
    <div class="col-md-4">
        <!-- Angebotsstatus -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Status</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-3">
                    <tr>
                        <td>Status:</td>
                        <td class="text-end">
                            <span class="badge bg-<?= $color ?>">
                                <?= e($label) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>Angebotsbetrag:</td>
                        <td class="text-end"><strong><?= formatMoney($offer['total_gross']) ?></strong></td>
                    </tr>
                    <?php if ($offer['valid_until']): ?>
                    <tr>
                        <td>Gültig bis:</td>
                        <td class="text-end"><?= formatDate($offer['valid_until']) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>

                <?php if (in_array($offer['invoice_status'], ['sent', 'draft'])): ?>
                <div class="d-grid gap-2">
                    <form method="POST" action="<?= url('offers/' . $offer['id'] . '/accept') ?>" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="bi bi-check-circle"></i> Angebot annehmen
                        </button>
                    </form>

                    <form method="POST" action="<?= url('offers/' . $offer['id'] . '/reject') ?>" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('Angebot wirklich ablehnen?')">
                            <i class="bi bi-x-circle"></i> Angebot ablehnen
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($offer['invoice_status'] === 'accepted'): ?>
                <form method="POST" action="<?= url('offers/' . $offer['id'] . '/convert') ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <button type="submit" class="btn btn-primary btn-sm w-100" onclick="return confirm('Angebot in Rechnung umwandeln?')">
                        <i class="bi bi-file-earmark-arrow-down"></i> In Rechnung umwandeln
                    </button>
                </form>
                <?php endif; ?>

                <?php if ($offer['invoice_status'] === 'converted' && !empty($offer['converted_invoice_id'])): ?>
                <div class="alert alert-success mt-3">
                    <strong>Umgewandelt</strong><br>
                    <a href="<?= url('invoices/' . $offer['converted_invoice_id']) ?>" class="alert-link">
                        Zur Rechnung <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Aktionen -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Aktionen</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= url('offers/' . $offer['id'] . '/pdf') ?>" class="btn btn-outline-danger btn-sm" target="_blank">
                        <i class="bi bi-file-pdf"></i> PDF herunterladen
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="sendOfferEmail()">
                        <i class="bi bi-envelope"></i> Per Email senden
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="duplicateOffer()">
                        <i class="bi bi-files"></i> Duplizieren
                    </button>
                    <?php if ($offer['invoice_status'] !== 'converted'): ?>
                    <a href="<?= url('offers/create?copy=' . $offer['id']) ?>" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-arrow-repeat"></i> Neues Angebot basierend auf diesem
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Email Send Form -->
<form id="send-email-form" method="POST" action="<?= url('offers/' . $offer['id'] . '/send') ?>" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
</form>

<script>
function sendOfferEmail() {
    if (confirm('Angebot per Email an den Kunden senden?\n\nEmpfänger: <?= e($customer['email'] ?? '') ?>')) {
        document.getElementById('send-email-form').submit();
    }
}

function duplicateOffer() {
    if (confirm('Angebot duplizieren?')) {
        window.location.href = '<?= url('offers/create?copy=' . $offer['id']) ?>';
    }
}
</script>
