<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Kunden</h6>
                    <h3 class="mb-0"><?= number_format($stats['total_customers'], 0, ',', '.') ?></h3>
                </div>
                <div class="fs-1 text-primary">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Produkte</h6>
                    <h3 class="mb-0"><?= number_format($stats['total_products'], 0, ',', '.') ?></h3>
                </div>
                <div class="fs-1 text-success">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Offene Rechnungen</h6>
                    <h3 class="mb-0"><?= number_format($stats['open_invoices'], 0, ',', '.') ?></h3>
                </div>
                <div class="fs-1 text-warning">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Überfällige Rechnungen</h6>
                    <h3 class="mb-0 text-danger"><?= number_format($stats['overdue_invoices'], 0, ',', '.') ?></h3>
                </div>
                <div class="fs-1 text-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="stats-card">
            <h6 class="text-muted mb-2"><i class="bi bi-calendar-month"></i> Umsatz aktueller Monat</h6>
            <h2 class="mb-0 text-success"><?= formatMoney($stats['month_revenue']) ?></h2>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="stats-card">
            <h6 class="text-muted mb-2"><i class="bi bi-calendar-range"></i> Umsatz aktuelles Jahr</h6>
            <h2 class="mb-0 text-primary"><?= formatMoney($stats['year_revenue']) ?></h2>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="stats-card">
            <h6 class="text-muted mb-2"><i class="bi bi-cash-stack"></i> Offene Forderungen</h6>
            <h2 class="mb-0 text-warning"><?= formatMoney($stats['open_amount']) ?></h2>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-lightning-fill"></i> Schnellaktionen</h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= url('invoices/create') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Neue Rechnung
                    </a>
                    <a href="<?= url('offers/create') ?>" class="btn btn-info text-white">
                        <i class="bi bi-plus-circle"></i> Neues Angebot
                    </a>
                    <a href="<?= url('customers/create') ?>" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Neuer Kunde
                    </a>
                    <a href="<?= url('products/create') ?>" class="btn btn-secondary">
                        <i class="bi bi-plus-circle"></i> Neues Produkt
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent & Overdue Invoices -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Letzte Rechnungen</h5>
                <a href="<?= url('invoices') ?>" class="btn btn-sm btn-outline-primary">Alle anzeigen</a>
            </div>
            <div class="card-body">
                <?php if (empty($stats['recent_invoices'])): ?>
                    <p class="text-muted mb-0">Keine Rechnungen vorhanden</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Nr.</th>
                                    <th>Kunde</th>
                                    <th>Datum</th>
                                    <th class="text-end">Betrag</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['recent_invoices'] as $invoice): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('invoices/' . $invoice['id']) ?>">
                                            <?= e($invoice['invoice_number']) ?>
                                        </a>
                                    </td>
                                    <td><?= e($invoice['customer_name']) ?></td>
                                    <td><?= formatDate($invoice['invoice_date']) ?></td>
                                    <td class="text-end"><?= formatMoney($invoice['total_gross']) ?></td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'sent' => 'info',
                                            'paid' => 'success',
                                            'overdue' => 'danger'
                                        ];
                                        $color = $statusColors[$invoice['invoice_status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $color ?>">
                                            <?= e($invoice['invoice_status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Überfällige Rechnungen</h5>
                <a href="<?= url('reminders') ?>" class="btn btn-sm btn-outline-danger">Mahnungen</a>
            </div>
            <div class="card-body">
                <?php if (empty($stats['overdue_invoices_list'])): ?>
                    <p class="text-muted mb-0">
                        <i class="bi bi-check-circle"></i> Keine überfälligen Rechnungen
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Nr.</th>
                                    <th>Kunde</th>
                                    <th>Fällig am</th>
                                    <th class="text-end">Betrag</th>
                                    <th>Tage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['overdue_invoices_list'] as $invoice): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('invoices/' . $invoice['id']) ?>">
                                            <?= e($invoice['invoice_number']) ?>
                                        </a>
                                    </td>
                                    <td><?= e($invoice['customer_name']) ?></td>
                                    <td><?= formatDate($invoice['due_date']) ?></td>
                                    <td class="text-end"><?= formatMoney($invoice['total_gross']) ?></td>
                                    <td>
                                        <?php
                                        $dueDate = new DateTime($invoice['due_date']);
                                        $now = new DateTime();
                                        $diff = $now->diff($dueDate);
                                        $days = $diff->days;
                                        ?>
                                        <span class="badge bg-danger"><?= $days ?> Tage</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
