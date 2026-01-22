<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Rechnungen</h4>
        <p class="text-muted mb-0">Gesamt: <?= count($invoices) ?> Rechnungen</p>
    </div>
    <div>
        <a href="<?= url('invoices/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Neue Rechnung
        </a>
    </div>
</div>

<!-- Filter Tabs -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link active" href="<?= url('invoices') ?>">Alle</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="<?= url('invoices?status=draft') ?>">Entwürfe</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="<?= url('invoices?status=sent') ?>">Versendet</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="<?= url('invoices?status=paid') ?>">Bezahlt</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="<?= url('invoices?status=overdue') ?>">Überfällig</a>
    </li>
</ul>

<!-- Invoices Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($invoices)): ?>
            <div class="text-center py-5">
                <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                <p class="mt-3 text-muted">Keine Rechnungen gefunden</p>
                <a href="<?= url('invoices/create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Erste Rechnung erstellen
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Rechnungsnummer</th>
                            <th>Kunde</th>
                            <th>Datum</th>
                            <th>Fällig am</th>
                            <th class="text-end">Betrag (Brutto)</th>
                            <th class="text-end">Bezahlt</th>
                            <th class="text-end">Offen</th>
                            <th>Status</th>
                            <th class="text-end">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td>
                                <a href="<?= url('invoices/' . $invoice['id']) ?>" class="fw-bold">
                                    <?= e($invoice['invoice_number']) ?>
                                </a>
                            </td>
                            <td><?= e($invoice['customer_name']) ?></td>
                            <td><?= formatDate($invoice['invoice_date']) ?></td>
                            <td>
                                <?php if ($invoice['due_date']): ?>
                                    <?= formatDate($invoice['due_date']) ?>
                                    <?php if (isOverdue($invoice['due_date']) && $invoice['invoice_status'] !== 'paid'): ?>
                                        <i class="bi bi-exclamation-triangle text-danger ms-1" title="Überfällig"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <strong><?= formatMoney($invoice['total_gross']) ?></strong>
                            </td>
                            <td class="text-end">
                                <?php if ($invoice['paid_amount'] > 0): ?>
                                    <span class="text-success"><?= formatMoney($invoice['paid_amount']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php
                                $openAmount = $invoice['total_gross'] - $invoice['paid_amount'];
                                if ($openAmount > 0):
                                ?>
                                    <span class="text-warning"><?= formatMoney($openAmount) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'sent' => 'info',
                                    'viewed' => 'primary',
                                    'partial' => 'warning',
                                    'paid' => 'success',
                                    'overdue' => 'danger',
                                    'cancelled' => 'dark'
                                ];
                                $statusLabels = [
                                    'draft' => 'Entwurf',
                                    'sent' => 'Versendet',
                                    'viewed' => 'Angesehen',
                                    'partial' => 'Teilzahlung',
                                    'paid' => 'Bezahlt',
                                    'overdue' => 'Überfällig',
                                    'cancelled' => 'Storniert'
                                ];
                                $color = $statusColors[$invoice['invoice_status']] ?? 'secondary';
                                $label = $statusLabels[$invoice['invoice_status']] ?? $invoice['invoice_status'];
                                ?>
                                <span class="badge bg-<?= $color ?>">
                                    <?= e($label) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('invoices/' . $invoice['id']) ?>" class="btn btn-outline-primary" title="Anzeigen">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($invoice['invoice_status'] === 'draft'): ?>
                                    <a href="<?= url('invoices/' . $invoice['id'] . '/edit') ?>" class="btn btn-outline-secondary" title="Bearbeiten">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?= url('invoices/' . $invoice['id'] . '/pdf') ?>" class="btn btn-outline-danger" title="PDF" target="_blank">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
