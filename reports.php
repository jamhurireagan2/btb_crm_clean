<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get statistics for reports
$totalClients = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$totalPolicies = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$expired = $pdo->query("SELECT COUNT(*) FROM clients WHERE expiry_date < CURDATE()")->fetchColumn();
$expiringSoon = $pdo->query("SELECT COUNT(*) FROM clients WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND expiry_date >= CURDATE()")->fetchColumn();
$activePolicies = $totalClients - $expired;

// Get policy type distribution
$policyTypes = $pdo->query("SELECT policy_type, COUNT(*) as count FROM clients GROUP BY policy_type")->fetchAll();

// Get monthly data for chart
$monthlyData = $pdo->query("SELECT DATE_FORMAT(created_at, '%M') as month, COUNT(*) as count FROM clients WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY month ORDER BY created_at ASC")->fetchAll();

// Get recent activity
$recentActivity = $pdo->query("SELECT full_name, created_at, 'added' as action FROM clients ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentExpiry = $pdo->query("SELECT full_name, expiry_date FROM clients WHERE expiry_date >= CURDATE() ORDER BY expiry_date ASC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - BTB Insurance</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Same base styles as dashboard.php - Copy from dashboard inline CSS */
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

        /* Dark Mode */
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
        body.dark-mode .report-card,
        body.dark-mode .chart-container {
            background: var(--bg-card);
            border-color: var(--border-color);
        }

        body.dark-mode .nav-brand span,
        body.dark-mode .page-title h1,
        body.dark-mode .stat-info h3,
        body.dark-mode .table-header h2,
        body.dark-mode .client-name,
        body.dark-mode .report-card h3,
        body.dark-mode .chart-container h3 {
            color: var(--text-primary);
        }

        body.dark-mode .page-title p,
        body.dark-mode .stat-info p,
        body.dark-mode .table-subtitle,
        body.dark-mode .record-count,
        body.dark-mode .client-email,
        body.dark-mode .content-footer,
        body.dark-mode .report-card p {
            color: var(--text-secondary);
        }

        body.dark-mode .content-footer {
            border-color: var(--border-color);
        }

        /* Top Navigation - Same as dashboard */
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
            flex-wrap: wrap;
            gap: 16px;
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
            margin-top: 4px;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: transparent;
            color: var(--gray-700);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-secondary:hover {
            border-color: var(--red-primary);
            color: var(--red-primary);
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
        .stat-icon.blue { background: #dbeafe; color: #2563eb; }

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

        /* Report Cards */
        .report-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-bottom: 24px;
        }

        .report-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 24px;
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .report-card:hover {
            box-shadow: var(--shadow-lg);
        }

        .report-card h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .report-card h3 i {
            color: var(--red-primary);
        }

        .report-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .report-item:last-child {
            border-bottom: none;
        }

        .report-item .label {
            color: var(--gray-600);
            font-size: 14px;
        }

        .report-item .value {
            font-weight: 600;
            font-size: 14px;
            color: var(--gray-900);
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--gray-200);
            border-radius: var(--radius-full);
            margin-top: 8px;
            overflow: hidden;
        }

        .progress-bar .fill {
            height: 100%;
            background: var(--red-gradient);
            border-radius: var(--radius-full);
            transition: width 0.6s ease;
        }

        /* Chart Container */
        .chart-container {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 24px;
            border: 1px solid var(--gray-200);
            margin-bottom: 24px;
        }

        .chart-container h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-container h3 i {
            color: var(--red-primary);
        }

        .chart-bars {
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            height: 200px;
            padding-top: 20px;
        }

        .chart-bar {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            flex: 1;
        }

        .chart-bar .bar {
            width: 40px;
            background: var(--red-gradient);
            border-radius: 4px 4px 0 0;
            transition: height 0.6s ease;
            min-height: 10px;
        }

        .chart-bar .bar-label {
            font-size: 11px;
            color: var(--gray-500);
            text-align: center;
        }

        .chart-bar .bar-value {
            font-size: 12px;
            font-weight: 600;
            color: var(--gray-700);
        }

        /* Activity List */
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            background: var(--gray-50);
            border-radius: var(--radius-sm);
        }

        .activity-item .activity-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .activity-item .activity-icon.added {
            background: var(--success-light);
            color: var(--success);
        }

        .activity-item .activity-icon.expiring {
            background: var(--warning-light);
            color: var(--warning);
        }

        .activity-item .activity-text {
            flex: 1;
        }

        .activity-item .activity-text .name {
            font-weight: 600;
            font-size: 14px;
            color: var(--gray-900);
        }

        .activity-item .activity-text .action {
            font-size: 13px;
            color: var(--gray-500);
        }

        .activity-item .activity-time {
            font-size: 12px;
            color: var(--gray-400);
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
            .report-grid { grid-template-columns: 1fr; }
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
            .user-greeting { display: none; }
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .chart-bars { height: 150px; }
            .chart-bar .bar { width: 30px; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .content-footer { flex-direction: column; text-align: center; }
            .report-card { padding: 16px; }
        }

        .btn-export {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--red-gradient);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-red);
        }

        .action-bar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
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
            <div class="nav-brand">
                <div class="brand-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <span>Client Management System</span>
            </div>
        </div>
        <div class="nav-right">
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
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-file-alt"></i> Reports</h1>
                <p class="page-subtitle">View analytics and insights about your clients</p>
            </div>
            <div class="action-bar">
                <button class="btn-export" onclick="window.print()">
                    <i class="fas fa-download"></i> Export PDF
                </button>
                <a href="dashboard.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

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
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($activePolicies) ?></h3>
                    <p>Active Policies</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($expiringSoon) ?></h3>
                    <p>Expiring Soon</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $totalClients > 0 ? round(($activePolicies / $totalClients) * 100) : 0 ?>%</h3>
                    <p>Retention Rate</p>
                </div>
            </div>
        </div>

        <!-- Report Grid -->
        <div class="report-grid">
            <!-- Policy Type Distribution -->
            <div class="report-card">
                <h3><i class="fas fa-chart-pie"></i> Policy Type Distribution</h3>
                <?php foreach($policyTypes as $type): 
                    $percentage = $totalClients > 0 ? round(($type['count'] / $totalClients) * 100) : 0;
                ?>
                    <div class="report-item">
                        <span class="label"><?= htmlspecialchars($type['policy_type']) ?></span>
                        <span class="value"><?= $type['count'] ?> (<?= $percentage ?>%)</span>
                    </div>
                    <div class="progress-bar">
                        <div class="fill" style="width: <?= $percentage ?>%"></div>
                    </div>
                <?php endforeach; ?>
                <?php if(empty($policyTypes)): ?>
                    <p style="color: var(--gray-500); text-align: center; padding: 20px 0;">No data available</p>
                <?php endif; ?>
            </div>

            <!-- Client Growth -->
            <div class="report-card">
                <h3><i class="fas fa-chart-line"></i> Client Growth</h3>
                <?php if(!empty($monthlyData)): ?>
                    <?php foreach($monthlyData as $data): ?>
                        <div class="report-item">
                            <span class="label"><?= htmlspecialchars($data['month']) ?></span>
                            <span class="value">+<?= $data['count'] ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--gray-500); text-align: center; padding: 20px 0;">No data available</p>
                <?php endif; ?>
            </div>

            <!-- Recent Activity -->
            <div class="report-card">
                <h3><i class="fas fa-clock"></i> Recent Activity</h3>
                <div class="activity-list">
                    <?php foreach($recentActivity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon added">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-text">
                                <span class="name"><?= htmlspecialchars($activity['full_name']) ?></span>
                                <span class="action">was added to the system</span>
                            </div>
                            <span class="activity-time"><?= date('d M', strtotime($activity['created_at'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($recentActivity)): ?>
                        <p style="color: var(--gray-500); text-align: center; padding: 10px 0;">No recent activity</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Upcoming Renewals -->
            <div class="report-card">
                <h3><i class="fas fa-calendar-check"></i> Upcoming Renewals</h3>
                <div class="activity-list">
                    <?php foreach($recentExpiry as $expiry): ?>
                        <div class="activity-item">
                            <div class="activity-icon expiring">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="activity-text">
                                <span class="name"><?= htmlspecialchars($expiry['full_name']) ?></span>
                                <span class="action">renews on <?= date('d M Y', strtotime($expiry['expiry_date'])) ?></span>
                            </div>
                            <span class="activity-time">
                                <?php 
                                    $days = (new DateTime())->diff(new DateTime($expiry['expiry_date']))->days;
                                    echo $days . 'd';
                                ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($recentExpiry)): ?>
                        <p style="color: var(--gray-500); text-align: center; padding: 10px 0;">No upcoming renewals</p>
                    <?php endif; ?>
                </div>
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