<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Zahlungen</h4>
        <p class="text-muted mb-0">Alle gebuchten Zahlungseingänge</p>
    </div>
</div>

<!-- Statistiken -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stats-card">
            <h6 class="text-muted mb-2"><i class="bi bi-calendar-month"></i> Zahlungen diesen Monat</h6>
            <h2 class="mb-0 text-success" id="month-total">-</h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <h6 class="text-muted mb-2"><i class="bi bi-calendar-range"></i> Zahlungen dieses Jahr</h6>
            <h2 class="mb-0 text-primary" id="year-total">-</h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <h6 class="text-muted mb-2"><i class="bi bi-receipt"></i> Anzahl Zahlungen</h6>
            <h2 class="mb-0 text-info"><?= count($payments) ?></h2>
        </div>
    </div>
</div>

<!-- Zahlungen Tabelle -->
<div class="card">
    <div class="card-body">
        <?php if (empty($payments)): ?>
            <div class="text-center py-5">
                <i class="bi bi-credit-card fs-1 text-muted"></i>
                <p class="mt-3 text-muted">Keine Zahlungen gefunden</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Rechnung</th>
                            <th>Kunde</th>
                            <th>Zahlungsart</th>
                            <th>Referenz</th>
                            <th class="text-end">Betrag</th>
                            <th class="text-end">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= formatDate($payment['payment_date']) ?></td>
                            <td>
                                <a href="<?= url('invoices/' . $payment['invoice_id']) ?>">
                                    <?= e($payment['invoice_number']) ?>
                                </a>
                            </td>
                            <td><?= e($payment['customer_name']) ?></td>
                            <td>
                                <?php
                                $methodLabels = [
                                    'bank_transfer' => 'Überweisung',
                                    'cash' => 'Bar',
                                    'paypal' => 'PayPal',
                                    'stripe' => 'Stripe',
                                    'credit_card' => 'Kreditkarte',
                                    'direct_debit' => 'Lastschrift',
                                    'other' => 'Sonstiges'
                                ];
                                $methodIcons = [
                                    'bank_transfer' => 'bi-bank',
                                    'cash' => 'bi-cash',
                                    'paypal' => 'bi-paypal',
                                    'stripe' => 'bi-credit-card-2-front',
                                    'credit_card' => 'bi-credit-card',
                                    'direct_debit' => 'bi-arrow-repeat',
                                    'other' => 'bi-question-circle'
                                ];
                                $icon = $methodIcons[$payment['payment_method']] ?? 'bi-question-circle';
                                $label = $methodLabels[$payment['payment_method']] ?? $payment['payment_method'];
                                ?>
                                <i class="<?= $icon ?>"></i> <?= $label ?>
                            </td>
                            <td>
                                <?php if (!empty($payment['reference'])): ?>
                                    <small class="text-muted"><?= e($payment['reference']) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <strong class="text-success"><?= formatMoney($payment['amount']) ?></strong>
                            </td>
                            <td class="text-end">
                                <?php if (hasRole('admin')): ?>
                                <form method="POST" action="<?= url('payments/' . $payment['id'] . '/delete') ?>" style="display: inline;" onsubmit="return confirm('Zahlung wirklich löschen?');">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Löschen">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Lade Statistiken
document.addEventListener('DOMContentLoaded', function() {
    fetch('<?= url('payments/stats') ?>')
        .then(response => response.json())
        .then(data => {
            document.getElementById('month-total').textContent = App.formatMoney(data.month_total);
            document.getElementById('year-total').textContent = App.formatMoney(data.year_total);
        })
        .catch(error => {
            console.error('Error loading stats:', error);
        });
});
</script>
