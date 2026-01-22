<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Kunden</h4>
        <p class="text-muted mb-0">Gesamt: <?= $pagination ? number_format($pagination['total'], 0, ',', '.') : count($customers) ?> Kunden</p>
    </div>
    <div>
        <a href="<?= url('customers/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Neuer Kunde
        </a>
    </div>
</div>

<!-- Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('customers') ?>" class="row g-3">
            <div class="col-md-10">
                <input type="text" class="form-control" name="search" placeholder="Suche nach Kundennummer, Name, Email..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Suchen
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($customers)): ?>
            <div class="text-center py-5">
                <i class="bi bi-people fs-1 text-muted"></i>
                <p class="mt-3 text-muted">Keine Kunden gefunden</p>
                <a href="<?= url('customers/create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Ersten Kunden anlegen
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Kundennummer</th>
                            <th>Typ</th>
                            <th>Name/Firma</th>
                            <th>Email</th>
                            <th>Telefon</th>
                            <th>Ort</th>
                            <th>Status</th>
                            <th class="text-end">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td>
                                <strong><?= e($customer['customer_number']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-<?= $customer['customer_type'] === 'b2b' ? 'primary' : 'info' ?>">
                                    <?= $customer['customer_type'] === 'b2b' ? 'B2B' : 'B2C' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($customer['customer_type'] === 'b2b'): ?>
                                    <?= e($customer['company_name']) ?>
                                <?php else: ?>
                                    <?= e($customer['first_name'] . ' ' . $customer['last_name']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($customer['email']): ?>
                                    <a href="mailto:<?= e($customer['email']) ?>">
                                        <?= e($customer['email']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($customer['phone'] ?: '-') ?></td>
                            <td><?= e($customer['billing_city'] ?: '-') ?></td>
                            <td>
                                <?php if ($customer['is_active']): ?>
                                    <span class="badge bg-success">Aktiv</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inaktiv</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('customers/' . $customer['id']) ?>" class="btn btn-outline-primary" title="Anzeigen">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= url('customers/' . $customer['id'] . '/edit') ?>" class="btn btn-outline-secondary" title="Bearbeiten">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= url('invoices/create?customer=' . $customer['id']) ?>" class="btn btn-outline-success" title="Neue Rechnung">
                                        <i class="bi bi-file-earmark-plus"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pagination && $pagination['last_page'] > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                    <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url('customers?page=' . $i) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
