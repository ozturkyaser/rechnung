<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?> - <?= APP_NAME ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">

    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 60px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar .logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar .logo h4 {
            margin: 0;
            font-weight: 700;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
            border-left-color: #3498db;
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 15px 30px;
            margin-bottom: 30px;
        }

        .content-wrapper {
            padding: 0 30px 30px;
        }

        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-primary:hover {
            opacity: 0.9;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        .badge {
            padding: 6px 12px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class="bi bi-receipt-cutoff fs-1"></i>
            <h4 class="mt-2"><?= APP_NAME ?></h4>
        </div>

        <nav class="nav flex-column mt-3">
            <a class="nav-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= url('dashboard') ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <div class="nav-section mt-3 px-3 text-uppercase small opacity-50">
                Verkauf
            </div>

            <a class="nav-link <?= ($activeMenu ?? '') === 'customers' ? 'active' : '' ?>" href="<?= url('customers') ?>">
                <i class="bi bi-people"></i> Kunden
            </a>

            <a class="nav-link <?= ($activeMenu ?? '') === 'invoices' ? 'active' : '' ?>" href="<?= url('invoices') ?>">
                <i class="bi bi-file-earmark-text"></i> Rechnungen
            </a>

            <a class="nav-link <?= ($activeMenu ?? '') === 'offers' ? 'active' : '' ?>" href="<?= url('offers') ?>">
                <i class="bi bi-file-earmark-check"></i> Angebote
            </a>

            <a class="nav-link <?= ($activeMenu ?? '') === 'credits' ? 'active' : '' ?>" href="<?= url('credits') ?>">
                <i class="bi bi-file-earmark-minus"></i> Gutschriften
            </a>

            <div class="nav-section mt-3 px-3 text-uppercase small opacity-50">
                Verwaltung
            </div>

            <a class="nav-link <?= ($activeMenu ?? '') === 'products' ? 'active' : '' ?>" href="<?= url('products') ?>">
                <i class="bi bi-box-seam"></i> Produkte
            </a>

            <a class="nav-link <?= ($activeMenu ?? '') === 'payments' ? 'active' : '' ?>" href="<?= url('payments') ?>">
                <i class="bi bi-credit-card"></i> Zahlungen
            </a>

            <a class="nav-link <?= ($activeMenu ?? '') === 'reminders' ? 'active' : '' ?>" href="<?= url('reminders') ?>">
                <i class="bi bi-bell"></i> Mahnungen
            </a>

            <div class="nav-section mt-3 px-3 text-uppercase small opacity-50">
                Berichte
            </div>

            <a class="nav-link <?= ($activeMenu ?? '') === 'reports' ? 'active' : '' ?>" href="<?= url('reports') ?>">
                <i class="bi bi-graph-up"></i> Statistiken
            </a>

            <div class="nav-section mt-3 px-3 text-uppercase small opacity-50">
                System
            </div>

            <a class="nav-link <?= ($activeMenu ?? '') === 'settings' ? 'active' : '' ?>" href="<?= url('settings') ?>">
                <i class="bi bi-gear"></i> Einstellungen
            </a>

            <a class="nav-link" href="<?= url('logout') ?>">
                <i class="bi bi-box-arrow-right"></i> Abmelden
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><?= $title ?? 'Dashboard' ?></h5>
                <small class="text-muted"><?= $subtitle ?? '' ?></small>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3">
                    <i class="bi bi-person-circle"></i>
                    <?= e(currentUser()['first_name'] ?? currentUser()['username']) ?>
                    <span class="badge bg-secondary ms-2"><?= e(currentUser()['role']) ?></span>
                </span>
            </div>
        </div>

        <!-- Content -->
        <div class="content-wrapper">
            <?php if ($error = getFlash('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success = getFlash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?= e($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($warning = getFlash('warning')): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i><?= e($warning) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (optional, für AJAX) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Custom JS -->
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
