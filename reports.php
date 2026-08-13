<?php
require_once 'config/database.php';
require_once 'config/payment.php';
require_once 'includes/payment_functions.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Get filters
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$report_type = $_GET['report_type'] ?? 'clients';
$search = $_GET['search'] ?? '';

// Build query based on report type
$data = [];
$total_records = 0;

if ($report_type == 'clients') {
    $sql = "SELECT * FROM clients WHERE 1=1";
    $params = [];
    
    if ($start_date && $end_date) {
        $sql .= " AND created_at BETWEEN ? AND ?";
        $params[] = $start_date . ' 00:00:00';
        $params[] = $end_date . ' 23:59:59';
    }
    
    if ($search) {
        $sql .= " AND (full_name LIKE ? OR phone LIKE ? OR policy_number LIKE ? OR email LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $sql .= " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    $total_records = count($data);
    
} elseif ($report_type == 'payments') {
    $sql = "SELECT p.*, c.full_name as client_name, c.policy_number 
            FROM payments p 
            JOIN clients c ON p.client_id = c.id 
            WHERE 1=1";
    $params = [];
    
    if ($start_date && $end_date) {
        $sql .= " AND p.created_at BETWEEN ? AND ?";
        $params[] = $start_date . ' 00:00:00';
        $params[] = $end_date . ' 23:59:59';
    }
    
    if ($search) {
        $sql .= " AND (c.full_name LIKE ? OR c.policy_number LIKE ? OR p.transaction_id LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    $total_records = count($data);
    
} else { // reports
    $sql = "SELECT 
                DATE(created_at) as date,
                COUNT(*) as total_clients,
                SUM(CASE WHEN expiry_date < CURDATE() THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN expiry_date >= CURDATE() THEN 1 ELSE 0 END) as active
            FROM clients 
            WHERE 1=1";
    $params = [];
    
    if ($start_date && $end_date) {
        $sql .= " AND created_at BETWEEN ? AND ?";
        $params[] = $start_date . ' 00:00:00';
        $params[] = $end_date . ' 23:59:59';
    }
    
    $sql .= " GROUP BY DATE(created_at) ORDER BY date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    $total_records = count($data);
}

// Get summary stats
$total_clients = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$total_payments = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'completed'")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'completed'")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Client Management System</title>
    <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Same styles as dashboard.php - keeping it consistent */
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
        body.dark-mode .filter-card {
            background: var(--bg-card);
            border-color: var(--border-color);
        }

        body.dark-mode .nav-brand span,
        body.dark-mode .page-title h1,
        body.dark-mode .stat-info h3,
        body.dark-mode .table-header h2,
        body.dark-mode .client-name,
        body.dark-mode .contact-info,
        body.dark-mode .filter-card label {
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

        body.dark-mode .filter-card input,
        body.dark-mode .filter-card select {
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

        body.dark-mode .content-footer {
            border-color: var(--border-color);
        }

        /* Top Navigation - same as dashboard */
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

        .logo-link {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .logo-link img {
            height: 40px;
            width: auto;
            object-fit: contain;
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

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
        }

        .page-header h1 i {
            color: var(--red-primary);
            margin-right: 12px;
        }

        .page-subtitle {
            color: var(--gray-500);
            font-size: 15px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            padding: 20px 24px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border-left: 4px solid var(--red-primary);
        }

        .stat-card h3 {
            font-size: 28px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .stat-card p {
            color: var(--gray-500);
            font-size: 13px;
        }

        .stat-card .stat-icon {
            display: inline-block;
            margin-right: 8px;
        }

        /* Filter Card */
        .filter-card {
            background: white;
            padding: 20px 24px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
            border: 1px solid var(--gray-200);
        }

        .filter-card h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--gray-900);
        }

        .filter-card h3 i {
            color: var(--red-primary);
            margin-right: 8px;
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-group input,
        .filter-group select {
            padding: 8px 14px;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: var(--gray-50);
            color: var(--gray-900);
            transition: var(--transition);
            min-width: 150px;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: var(--red-primary);
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
        }

        .filter-group .search-input {
            min-width: 250px;
        }

        .btn-filter {
            padding: 8px 24px;
            background: var(--red-gradient);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
            height: 42px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-red);
        }

        .btn-export {
            padding: 8px 24px;
            background: #22c55e;
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
            height: 42px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4);
        }

        .btn-reset {
            padding: 8px 24px;
            background: var(--gray-200);
            color: var(--gray-700);
            border: none;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
            height: 42px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-reset:hover {
            background: var(--gray-300);
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

        .badge-status {
            display: inline-block;
            padding: 3px 12px;
            border-radius: var(--radius-full);
            font-size: 12px;
            font-weight: 600;
        }

        .badge-status.completed { background: var(--success-light); color: var(--success); }
        .badge-status.pending { background: var(--warning-light); color: var(--warning); }
        .badge-status.failed { background: #fee2e2; color: var(--red-primary); }
        .badge-status.active { background: var(--success-light); color: var(--success); }
        .badge-status.expired { background: #fee2e2; color: var(--red-primary); }
        .badge-status.expiring { background: var(--warning-light); color: var(--warning); }

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
            .filter-row { flex-direction: column; align-items: stretch; }
            .filter-group input, .filter-group select { width: 100%; }
            .filter-group .search-input { min-width: auto; }
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
            .logo-link img { height: 32px; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .nav-search input { width: 80px; }
            .table-header { flex-direction: column; align-items: flex-start; }
            .content-footer { flex-direction: column; text-align: center; }
            .logo-link img { height: 28px; }
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
                <img src="assets/images/cms-logo-red-white.png" alt="Client Management System">
            </a>
        </div>
        <div class="nav-right">
            <div class="nav-search">
                <form method="GET">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search clients..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <?php if($search): ?>
                        <a href="reports.php" class="clear-search"><i class="fas fa-times"></i></a>
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
            <a href="dashboard.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="add_client.php">
                <i class="fas fa-user-plus"></i> Add Client
            </a>
            <a href="reports.php" class="active">
                <i class="fas fa-file-alt"></i> Reports
            </a>
            <a href="renewals.php">
                <i class="fas fa-calendar-alt"></i> Renewals
            </a>
            <a href="settings.php">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="payment/history.php">
                <i class="fas fa-credit-card"></i> Payments
            </a>
            <a href="admin/payment_settings.php">
                <i class="fas fa-credit-card"></i> Payment Settings
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1><i class="fas fa-file-alt"></i> Reports</h1>
                <p class="page-subtitle">View and export your data with advanced filtering</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= number_format($total_clients) ?></h3>
                <p><i class="fas fa-users stat-icon"></i> Total Clients</p>
            </div>
            <div class="stat-card">
                <h3><?= number_format($total_payments) ?></h3>
                <p><i class="fas fa-credit-card stat-icon"></i> Total Payments</p>
            </div>
            <div class="stat-card">
                <h3><?= CURRENCY_SYMBOL ?> <?= number_format($total_revenue, 2) ?></h3>
                <p><i class="fas fa-chart-line stat-icon"></i> Total Revenue</p>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="filter-card">
            <h3><i class="fas fa-filter"></i> Filter Reports</h3>
            <form method="GET" class="filter-row">
                <div class="filter-group">
                    <label>Report Type</label>
                    <select name="report_type">
                        <option value="clients" <?= $report_type == 'clients' ? 'selected' : '' ?>>Clients</option>
                        <option value="payments" <?= $report_type == 'payments' ? 'selected' : '' ?>>Payments</option>
                        <option value="reports" <?= $report_type == 'reports' ? 'selected' : '' ?>>Daily Reports</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" value="<?= $start_date ?>">
                </div>
                <div class="filter-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" value="<?= $end_date ?>">
                </div>
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>" class="search-input">
                </div>
                <div class="filter-group" style="flex-direction:row;gap:8px;">
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="reports.php" class="btn-reset">
                        <i class="fas fa-times"></i> Reset
                    </a>
                    <a href="export_excel.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&export_type=<?= $report_type ?>&search=<?= urlencode($search) ?>" class="btn-export" target="_blank">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                </div>
            </form>
        </div>

        <!-- Results Table -->
        <div class="table-container">
            <div class="table-header">
                <div>
                    <h2><i class="fas fa-list"></i> <?= ucfirst($report_type) ?> Results</h2>
                    <p class="table-subtitle">Showing <?= $total_records ?> records</p>
                </div>
                <span class="record-count">
                    <i class="fas fa-database"></i> <?= $total_records ?> records
                </span>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <?php if($report_type == 'clients'): ?>
                                <th>#</th>
                                <th>Full Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Policy #</th>
                                <th>Type</th>
                                <th>Expiry</th>
                                <th>Status</th>
                            <?php elseif($report_type == 'payments'): ?>
                                <th>#</th>
                                <th>Client</th>
                                <th>Policy</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Transaction ID</th>
                                <th>Status</th>
                                <th>Date</th>
                            <?php else: ?>
                                <th>#</th>
                                <th>Date</th>
                                <th>Total Clients</th>
                                <th>Active</th>
                                <th>Expired</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($total_records > 0): ?>
                            <?php foreach($data as $index => $row): ?>
                            <tr>
                                <?php if($report_type == 'clients'): ?>
                                    <td><?= $index + 1 ?></td>
                                    <td><strong><?= htmlspecialchars($row['full_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['phone']) ?></td>
                                    <td><?= htmlspecialchars($row['email'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($row['policy_number']) ?></td>
                                    <td><?= htmlspecialchars($row['policy_type']) ?></td>
                                    <td><?= date('d M Y', strtotime($row['expiry_date'])) ?></td>
                                    <td>
                                        <?php 
                                        $is_expired = strtotime($row['expiry_date']) < time();
                                        $is_expiring = !$is_expired && (strtotime($row['expiry_date']) - time()) / 86400 <= 30;
                                        ?>
                                        <span class="badge-status <?= $is_expired ? 'expired' : ($is_expiring ? 'expiring' : 'active') ?>">
                                            <?= $is_expired ? 'Expired' : ($is_expiring ? 'Expiring Soon' : 'Active') ?>
                                        </span>
                                    </td>
                                <?php elseif($report_type == 'payments'): ?>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($row['client_name']) ?></td>
                                    <td><?= htmlspecialchars($row['policy_number']) ?></td>
                                    <td><?= CURRENCY_SYMBOL ?> <?= number_format($row['amount'], 2) ?></td>
                                    <td><?= strtoupper($row['payment_method']) ?></td>
                                    <td><?= htmlspecialchars($row['transaction_id']) ?></td>
                                    <td><span class="badge-status <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                <?php else: ?>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= date('d M Y', strtotime($row['date'])) ?></td>
                                    <td><?= $row['total_clients'] ?></td>
                                    <td><?= $row['active'] ?></td>
                                    <td><?= $row['expired'] ?></td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <h3>No records found</h3>
                                        <p>Try adjusting your filters or date range</p>
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
            <p class="footer-version">v3.0 <span class="dot">•</span> Reports</p>
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