<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Angebote</h4>
        <p class="text-muted mb-0">Gesamt: <?= count($offers) ?> Angebote</p>
    </div>
    <div>
        <a href="<?= url('offers/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Neues Angebot
        </a>
    </div>
</div>

<!-- Filter Tabs -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link <?= !isset($_GET['status']) ? 'active' : '' ?>" href="<?= url('offers') ?>">Alle</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= isset($_GET['status']) && $_GET['status'] === 'draft' ? 'active' : '' ?>" href="<?= url('offers?status=draft') ?>">Entwürfe</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= isset($_GET['status']) && $_GET['status'] === 'sent' ? 'active' : '' ?>" href="<?= url('offers?status=sent') ?>">Versendet</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= isset($_GET['status']) && $_GET['status'] === 'accepted' ? 'active' : '' ?>" href="<?= url('offers?status=accepted') ?>">Angenommen</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= isset($_GET['status']) && $_GET['status'] === 'rejected' ? 'active' : '' ?>" href="<?= url('offers?status=rejected') ?>">Abgelehnt</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= isset($_GET['status']) && $_GET['status'] === 'expired' ? 'active' : '' ?>" href="<?= url('offers?status=expired') ?>">Abgelaufen</a>
    </li>
</ul>

<!-- Offers Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($offers)): ?>
            <div class="text-center py-5">
                <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                <p class="mt-3 text-muted">Keine Angebote gefunden</p>
                <a href="<?= url('offers/create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Erstes Angebot erstellen
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Angebotsnummer</th>
                            <th>Kunde</th>
                            <th>Datum</th>
                            <th>Gültig bis</th>
                            <th class="text-end">Betrag (Brutto)</th>
                            <th>Status</th>
                            <th class="text-end">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($offers as $offer): ?>
                        <tr>
                            <td>
                                <a href="<?= url('offers/' . $offer['id']) ?>" class="fw-bold">
                                    <?= e($offer['invoice_number']) ?>
                                </a>
                            </td>
                            <td><?= e($offer['customer_name']) ?></td>
                            <td><?= formatDate($offer['invoice_date']) ?></td>
                            <td>
                                <?php if ($offer['valid_until']): ?>
                                    <?= formatDate($offer['valid_until']) ?>
                                    <?php if (strtotime($offer['valid_until']) < time() && !in_array($offer['invoice_status'], ['accepted', 'converted', 'rejected'])): ?>
                                        <i class="bi bi-exclamation-triangle text-warning ms-1" title="Abgelaufen"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <strong><?= formatMoney($offer['total_gross']) ?></strong>
                            </td>
                            <td>
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
                                $label = $statusLabels[$offer['invoice_status']] ?? $offer['invoice_status'];
                                ?>
                                <span class="badge bg-<?= $color ?>">
                                    <?= e($label) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('offers/' . $offer['id']) ?>" class="btn btn-outline-primary" title="Anzeigen">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($offer['invoice_status'] === 'draft'): ?>
                                    <a href="<?= url('offers/' . $offer['id'] . '/edit') ?>" class="btn btn-outline-secondary" title="Bearbeiten">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?= url('offers/' . $offer['id'] . '/pdf') ?>" class="btn btn-outline-danger" title="PDF" target="_blank">
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
