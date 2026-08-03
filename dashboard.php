<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get statistics
$totalClients = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$expiringSoon = $pdo->query("SELECT COUNT(*) FROM clients WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND expiry_date >= CURDATE()")->fetchColumn();
$expired = $pdo->query("SELECT COUNT(*) FROM clients WHERE expiry_date < CURDATE()")->fetchColumn();
$activePolicies = $totalClients - $expired;

// Handle search
$search_term = $_GET['search'] ?? '';
$clients = [];

if ($search_term) {
    $sql = "SELECT * FROM clients 
            WHERE full_name LIKE ? 
            OR phone LIKE ? 
            OR policy_number LIKE ? 
            OR email LIKE ?
            ORDER BY created_at DESC";
    $search_param = "%$search_term%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$search_param, $search_param, $search_param, $search_param]);
    $clients = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT * FROM clients ORDER BY created_at DESC LIMIT 50");
    $clients = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BTB Insurance</title>
    <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ============================================
           DASHBOARD CSS
           ============================================ */
        :root {
            --red-primary: #dc2626;
            --red-dark: #b91c1c;
            --red-light: #fee2e2;
            --red-gradient: linear-gradient(135deg, #dc2626, #b91c1c);
            --white: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --success: #22c55e;
            --success-light: #dcfce7;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --shadow: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1);
            --shadow-red: 0 8px 25px rgba(220, 38, 38, 0.35);
            --radius: 12px;
            --radius-sm: 8px;
            --radius-full: 9999px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.6;
        }

        body.dark-mode {
            --gray-50: #0f172a;
            --gray-100: #1e293b;
            --gray-200: #334155;
            --gray-300: #475569;
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --bg-nav: #1e293b;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --border-color: #334155;
        }

        body.dark-mode .top-nav,
        body.dark-mode .sidebar,
        body.dark-mode .stat-card,
        body.dark-mode .table-container,
        body.dark-mode .quick-action {
            background: var(--bg-card);
            border-color: var(--border-color);
        }

        body.dark-mode .nav-brand span,
        body.dark-mode .page-title h1,
        body.dark-mode .stat-info h3,
        body.dark-mode .table-header h2,
        body.dark-mode .client-name,
        body.dark-mode .contact-info {
            color: var(--text-primary);
        }

        body.dark-mode .page-title p,
        body.dark-mode .stat-info p,
        body.dark-mode .table-subtitle,
        body.dark-mode .record-count,
        body.dark-mode .client-email,
        body.dark-mode .content-footer {
            color: var(--text-secondary);
        }

        body.dark-mode .nav-search input,
        body.dark-mode .nav-search form {
            background: var(--gray-50);
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        body.dark-mode tbody tr:hover {
            background: var(--gray-50);
        }

        body.dark-mode thead {
            background: var(--gray-50);
        }

        body.dark-mode .quick-action {
            color: var(--text-primary);
        }

        body.dark-mode .quick-action:hover {
            background: var(--red-light);
            color: var(--red-primary);
        }

        body.dark-mode .policy-code {
            background: var(--gray-50);
            color: var(--text-primary);
        }

        body.dark-mode .badge {
            background: var(--gray-50);
            color: var(--text-primary);
        }

        body.dark-mode .content-footer {
            border-color: var(--border-color);
        }

        /* Top Navigation */
        .top-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 64px;
            background: white;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 1000;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 22px;
            color: var(--gray-600);
            cursor: pointer;
            padding: 4px 8px;
            border-radius: var(--radius-sm);
        }

        .mobile-toggle:hover { background: var(--gray-100); }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-brand .brand-icon {
            width: 36px;
            height: 36px;
            background: var(--red-gradient);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        .nav-brand span {
            font-size: 18px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .nav-search form {
            display: flex;
            align-items: center;
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-full);
            padding: 0 4px 0 14px;
            transition: var(--transition);
        }

        .nav-search form:focus-within {
            border-color: var(--red-primary);
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
        }

        .nav-search i {
            color: var(--gray-400);
            font-size: 14px;
        }

        .nav-search input {
            border: none;
            background: transparent;
            padding: 7px 10px;
            font-size: 14px;
            width: 180px;
            outline: none;
            color: var(--gray-900);
        }

        .nav-search .clear-search {
            color: var(--gray-400);
            padding: 4px 6px;
            text-decoration: none;
        }

        .search-btn {
            background: var(--red-gradient);
            color: white;
            border: none;
            border-radius: var(--radius-full);
            padding: 5px 12px;
            cursor: pointer;
            transition: var(--transition);
        }

        .search-btn:hover { transform: scale(1.05); }

        .nav-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-greeting {
            font-size: 14px;
            font-weight: 500;
            color: var(--gray-600);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: var(--red-gradient);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .theme-toggle-nav {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: var(--gray-600);
            padding: 6px;
            border-radius: 50%;
            transition: var(--transition);
        }

        .theme-toggle-nav:hover { background: var(--gray-100); }

        .logout-btn {
            color: var(--gray-400);
            text-decoration: none;
            padding: 6px 8px;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .logout-btn:hover {
            background: var(--red-light);
            color: var(--red-primary);
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 64px;
            left: 0;
            bottom: 0;
            width: 220px;
            background: white;
            border-right: 1px solid var(--gray-200);
            padding: 16px 12px;
            overflow-y: auto;
            z-index: 999;
            transition: transform 0.3s ease;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            color: var(--gray-600);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
        }

        .sidebar-menu a:hover {
            background: var(--red-light);
            color: var(--red-primary);
        }

        .sidebar-menu a.active {
            background: var(--red-light);
            color: var(--red-primary);
            font-weight: 600;
        }

        .sidebar-menu a i { width: 18px; text-align: center; }

        /* Main Content */
        .main-content {
            margin-left: 220px;
            margin-top: 64px;
            padding: 24px;
            min-height: calc(100vh - 64px);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            padding: 20px 24px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: var(--transition);
            border-left: 4px solid var(--red-primary);
            position: relative;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-icon.red { background: var(--red-light); color: var(--red-primary); }
        .stat-icon.green { background: var(--success-light); color: var(--success); }
        .stat-icon.yellow { background: var(--warning-light); color: var(--warning); }
        .stat-icon.red-dark { background: #fecaca; color: var(--red-dark); }

        .stat-info h3 {
            font-size: 28px;
            font-weight: 700;
            line-height: 1.2;
            color: var(--gray-900);
        }

        .stat-info p {
            color: var(--gray-500);
            font-size: 13px;
            font-weight: 500;
        }

        .stat-trend {
            position: absolute;
            top: 16px;
            right: 16px;
            font-size: 12px;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: var(--radius-full);
        }

        .stat-trend.up { color: var(--success); background: var(--success-light); }
        .stat-trend.down { color: var(--red-primary); background: var(--red-light); }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        .quick-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 20px;
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            text-decoration: none;
            color: var(--gray-700);
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
        }

        .quick-action:hover {
            border-color: var(--red-primary);
            background: var(--red-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .quick-action i {
            font-size: 18px;
            color: var(--red-primary);
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .table-header {
            padding: 16px 24px;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .table-header h2 {
            font-size: 17px;
            font-weight: 600;
            color: var(--gray-900);
        }

        .table-header h2 i {
            margin-right: 8px;
            color: var(--red-primary);
        }

        .table-subtitle {
            font-size: 13px;
            color: var(--gray-500);
        }

        .record-count {
            font-size: 13px;
            color: var(--gray-500);
            background: var(--gray-50);
            padding: 4px 14px;
            border-radius: var(--radius-full);
        }

        .table-responsive { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--gray-50);
        }

        thead th {
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray-500);
            border-bottom: 2px solid var(--gray-200);
        }

        tbody tr { transition: var(--transition); }
        tbody tr:hover { background: var(--gray-50); }

        tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--gray-100);
            font-size: 14px;
            color: var(--gray-700);
        }

        .client-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .client-avatar {
            width: 34px;
            height: 34px;
            background: var(--red-gradient);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
        }

        .client-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--gray-900);
        }

        .client-email {
            font-size: 12px;
            color: var(--gray-400);
        }

        .contact-info {
            font-size: 13px;
            color: var(--gray-600);
        }

        .contact-info i {
            color: var(--gray-400);
            width: 16px;
        }

        .policy-code {
            font-family: 'Courier New', monospace;
            background: var(--gray-50);
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            color: var(--gray-700);
        }

        .badge {
            display: inline-block;
            padding: 3px 12px;
            background: var(--gray-100);
            color: var(--gray-700);
            border-radius: var(--radius-full);
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 12px;
            border-radius: var(--radius-full);
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.active {
            background: var(--success-light);
            color: var(--success);
        }

        .status-badge.expiring {
            background: var(--warning-light);
            color: var(--warning);
        }

        .status-badge.expired {
            background: var(--red-light);
            color: var(--red-primary);
        }

        .action-buttons {
            display: flex;
            gap: 4px;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 13px;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }

        .btn-action.edit {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        .btn-action.edit:hover {
            background: var(--red-light);
            color: var(--red-primary);
        }

        .btn-action.delete {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        .btn-action.delete:hover {
            background: var(--red-light);
            color: var(--red-primary);
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--gray-500);
        }

        .empty-state i {
            font-size: 48px;
            color: var(--gray-300);
            margin-bottom: 12px;
        }

        .empty-state h3 {
            font-size: 18px;
            color: var(--gray-700);
            margin-bottom: 6px;
        }

        .btn-red {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background: var(--red-gradient);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-red:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-red);
        }

        .content-footer {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: var(--gray-500);
            flex-wrap: wrap;
            gap: 8px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .quick-actions { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .mobile-toggle { display: block; }
            .sidebar {
                transform: translateX(-100%);
                width: 260px;
            }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 16px; }
            .top-nav { padding: 0 16px; }
            .nav-search input { width: 120px; }
            .user-greeting { display: none; }
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
            .quick-actions { grid-template-columns: 1fr; }
            .stat-card { padding: 16px 20px; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .nav-search input { width: 80px; }
            .table-header { flex-direction: column; align-items: flex-start; }
            .content-footer { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="nav-left">
            <button class="mobile-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <a href="dashboard.php" class="logo-link">
    <img src="assets/images/cms-logo-red-white.png" alt="Client Management System" height="40">
</a>
        </div>
        <div class="nav-right">
            <div class="nav-search">
                <form method="GET">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search clients..." 
                           value="<?= htmlspecialchars($search_term) ?>">
                    <?php if($search_term): ?>
                        <a href="dashboard.php" class="clear-search"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                    <button type="submit" class="search-btn"><i class="fas fa-arrow-right"></i></button>
                </form>
            </div>
            <div class="nav-user">
                <span class="user-greeting">Hi, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
                </div>
                <button class="theme-toggle-nav" onclick="toggleTheme()" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="logout.php" class="logout-btn" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-menu">
            <a href="dashboard.php" class="active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="add_client.php">
                <i class="fas fa-user-plus"></i> Add Client
            </a>
            <a href="reports.php">
                <i class="fas fa-file-alt"></i> Reports
            </a>
            <a href="renewals.php">
                <i class="fas fa-calendar-alt"></i> Renewals
            </a>
            <a href="settings.php">
                <i class="fas fa-cog"></i> Settings
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($totalClients) ?></h3>
                    <p>Total Clients</p>
                </div>
                <div class="stat-trend up">
                    <i class="fas fa-arrow-up"></i> 12%
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($activePolicies) ?></h3>
                    <p>Active Policies</p>
                </div>
                <div class="stat-trend up">
                    <i class="fas fa-arrow-up"></i> 8%
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($expiringSoon) ?></h3>
                    <p>Expiring in 30 Days</p>
                </div>
                <div class="stat-trend down">
                    <i class="fas fa-arrow-down"></i> 5%
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red-dark">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($expired) ?></h3>
                    <p>Expired Policies</p>
                </div>
                <div class="stat-trend down">
                    <i class="fas fa-arrow-down"></i> 3%
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="add_client.php" class="quick-action">
                <i class="fas fa-user-plus"></i> Add New Client
            </a>
            <a href="#" class="quick-action">
                <i class="fas fa-file-export"></i> Export Report
            </a>
            <a href="#" class="quick-action">
                <i class="fas fa-bell"></i> Renewal Alerts
            </a>
        </div>

        <!-- Client Table -->
        <div class="table-container">
            <div class="table-header">
                <div>
                    <h2><i class="fas fa-list"></i> Client Records</h2>
                    <p class="table-subtitle">Manage your insurance clients</p>
                </div>
                <span class="record-count">
                    <i class="fas fa-database"></i> <?= count($clients) ?> records
                </span>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Contact</th>
                            <th>Policy</th>
                            <th>Type</th>
                            <th>Expiry</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($clients) > 0): ?>
                            <?php foreach($clients as $index => $client): 
                                $today = new DateTime();
                                $expiry = new DateTime($client['expiry_date']);
                                $diff = $today->diff($expiry)->days;
                                $isExpired = $expiry < $today;
                                $isExpiring = !$isExpired && $diff <= 30;
                            ?>
                            <tr>
                                <td>
                                    <div class="client-info">
                                        <div class="client-avatar">
                                            <?= strtoupper(substr($client['full_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="client-name"><?= htmlspecialchars($client['full_name']) ?></div>
                                            <div class="client-email"><?= htmlspecialchars($client['email'] ?? 'No email') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="contact-info">
                                        <div><i class="fas fa-phone"></i> <?= htmlspecialchars($client['phone']) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="policy-code"><?= htmlspecialchars($client['policy_number']) ?></span>
                                </td>
                                <td>
                                    <span class="badge"><?= htmlspecialchars($client['policy_type']) ?></span>
                                </td>
                                <td><?= date('d M Y', strtotime($client['expiry_date'])) ?></td>
                                <td>
                                    <?php if($isExpired): ?>
                                        <span class="status-badge expired">
                                            <i class="fas fa-times-circle"></i> Expired
                                        </span>
                                    <?php elseif($isExpiring): ?>
                                        <span class="status-badge expiring">
                                            <i class="fas fa-clock"></i> <?= $diff ?> days
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge active">
                                            <i class="fas fa-check-circle"></i> Active
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_client.php?id=<?= $client['id'] ?>" class="btn-action edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_client.php?id=<?= $client['id'] ?>" class="btn-action delete" 
                                           onclick="return confirm('Are you sure you want to delete this client?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <h3>No clients found</h3>
                                        <p><?= $search_term ? 'No results match your search.' : 'Start by adding your first client!' ?></p>
                                        <?php if(!$search_term): ?>
                                            <a href="add_client.php" class="btn-red">
                                                <i class="fas fa-user-plus"></i> Add Client
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <footer class="content-footer">
            <p>&copy; <?= date('Y') ?> <strong>Client Management System</strong>. All rights reserved.</p>
            <p class="footer-version">v3.0 <span class="dot">•</span> Premium</p>
        </footer>
    </main>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        function toggleTheme() {
            document.body.classList.toggle('dark-mode');
            const icons = document.querySelectorAll('.theme-toggle-nav i');
            icons.forEach(icon => {
                if (document.body.classList.contains('dark-mode')) {
                    icon.className = 'fas fa-sun';
                } else {
                    icon.className = 'fas fa-moon';
                }
            });
        }
    </script>
</body>
</html>