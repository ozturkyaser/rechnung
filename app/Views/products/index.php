<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Produkte & Dienstleistungen</h4>
        <p class="text-muted mb-0">Gesamt: <?= $pagination ? number_format($pagination['total'], 0, ',', '.') : count($products) ?> Produkte</p>
    </div>
    <div>
        <a href="<?= url('products/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Neues Produkt
        </a>
    </div>
</div>

<!-- Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('products') ?>" class="row g-3">
            <div class="col-md-10">
                <input type="text" class="form-control" name="search" placeholder="Suche nach Produktnummer, Name, SKU, EAN..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Suchen
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="bi bi-box-seam fs-1 text-muted"></i>
                <p class="mt-3 text-muted">Keine Produkte gefunden</p>
                <a href="<?= url('products/create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Erstes Produkt anlegen
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Produktnummer</th>
                            <th>Typ</th>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Einheit</th>
                            <th class="text-end">Preis (Netto)</th>
                            <th class="text-center">Lager</th>
                            <th>Status</th>
                            <th class="text-end">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <strong><?= e($product['product_number']) ?></strong>
                            </td>
                            <td>
                                <?php if ($product['product_type'] === 'product'): ?>
                                    <span class="badge bg-primary">Produkt</span>
                                <?php else: ?>
                                    <span class="badge bg-info">Dienstleistung</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>
                                    <strong><?= e($product['name']) ?></strong>
                                    <?php if ($product['description']): ?>
                                        <br><small class="text-muted"><?= e(substr($product['description'], 0, 50)) ?><?= strlen($product['description']) > 50 ? '...' : '' ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= e($product['sku'] ?: '-') ?></td>
                            <td><?= e($product['unit']) ?></td>
                            <td class="text-end">
                                <strong><?= formatMoney($product['price_net']) ?></strong>
                            </td>
                            <td class="text-center">
                                <?php if ($product['product_type'] === 'product'): ?>
                                    <?php
                                    $stock = (float)$product['stock_quantity'];
                                    $min = (float)$product['stock_min'];
                                    $isLow = $stock <= $min && $min > 0;
                                    ?>
                                    <span class="badge bg-<?= $isLow ? 'warning' : 'success' ?>">
                                        <?= number_format($stock, 0, ',', '.') ?>
                                    </span>
                                    <?php if ($isLow): ?>
                                        <i class="bi bi-exclamation-triangle text-warning" title="Niedriger Lagerbestand"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($product['is_active']): ?>
                                    <span class="badge bg-success">Aktiv</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inaktiv</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('products/' . $product['id']) ?>" class="btn btn-outline-primary" title="Anzeigen">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= url('products/' . $product['id'] . '/edit') ?>" class="btn btn-outline-secondary" title="Bearbeiten">
                                        <i class="bi bi-pencil"></i>
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
                        <a class="page-link" href="<?= url('products?page=' . $i) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
