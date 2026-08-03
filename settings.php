<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->execute([$username, $_SESSION['user_id']]);
        $_SESSION['username'] = $username;
        $message = 'Profile updated successfully!';
    } catch(PDOException $e) {
        $error = 'Error updating profile: ' . $e->getMessage();
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current user
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!password_verify($current_password, $user['password'])) {
        $error = 'Current password is incorrect!';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match!';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters!';
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $_SESSION['user_id']]);
        $message = 'Password changed successfully!';
    }
}

// Export data
if (isset($_GET['export']) && $_GET['export'] == 'clients') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="clients_export_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Full Name', 'Phone', 'Email', 'Policy Number', 'Policy Type', 'Expiry Date', 'Created At']);
    
    $clients = $pdo->query("SELECT * FROM clients ORDER BY id")->fetchAll();
    foreach ($clients as $client) {
        fputcsv($output, $client);
    }
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - BTB Insurance</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Same base styles as reports.php */
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
        body.dark-mode .settings-card {
            background: var(--bg-card);
            border-color: var(--border-color);
        }

        body.dark-mode .nav-brand span,
        body.dark-mode .page-title h1,
        body.dark-mode .settings-card h3,
        body.dark-mode .settings-card label {
            color: var(--text-primary);
        }

        body.dark-mode .page-title p,
        body.dark-mode .settings-card p,
        body.dark-mode .content-footer,
        body.dark-mode .settings-card .hint {
            color: var(--text-secondary);
        }

        body.dark-mode .settings-card input,
        body.dark-mode .settings-card select {
            background: var(--gray-50);
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        body.dark-mode .content-footer {
            border-color: var(--border-color);
        }

        body.dark-mode .alert-success {
            background: #065f46;
            color: #6ee7b7;
            border-color: #065f46;
        }

        body.dark-mode .alert-error {
            background: #7f1d1d;
            color: #fca5a5;
            border-color: #7f1d1d;
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

        /* Settings Grid */
        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .settings-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 28px;
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .settings-card:hover {
            box-shadow: var(--shadow-lg);
        }

        .settings-card h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .settings-card h3 i {
            color: var(--red-primary);
        }

        .settings-card .subtitle {
            color: var(--gray-500);
            font-size: 14px;
            margin-bottom: 20px;
        }

        .settings-card .form-group {
            margin-bottom: 16px;
        }

        .settings-card label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 4px;
        }

        .settings-card input,
        .settings-card select {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 14px;
            transition: var(--transition);
            background: var(--gray-50);
            color: var(--gray-900);
        }

        .settings-card input:focus,
        .settings-card select:focus {
            outline: none;
            border-color: var(--red-primary);
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
        }

        .settings-card .hint {
            font-size: 12px;
            color: var(--gray-400);
            margin-top: 4px;
        }

        .btn-primary {
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

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-red);
        }

        .btn-danger {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background: #dc2626;
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.35);
        }

        .btn-success {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background: #22c55e;
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-success:hover {
            background: #16a34a;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.35);
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            margin-bottom: 16px;
            border: 1px solid #bbf7d0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            margin-bottom: 16px;
            border: 1px solid #fecaca;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .settings-actions {
            display: flex;
            gap: 12px;
            margin-top: 8px;
            flex-wrap: wrap;
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
            .settings-grid { grid-template-columns: 1fr; }
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
            .page-header { flex-direction: column; align-items: flex-start; }
            .settings-card { padding: 20px; }
            .settings-actions { flex-direction: column; }
            .settings-actions .btn-primary,
            .settings-actions .btn-danger,
            .settings-actions .btn-success { width: 100%; justify-content: center; }
        }

        @media (max-width: 480px) {
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
            <a href="reports.php">
                <i class="fas fa-file-alt"></i> Reports
            </a>
            <a href="renewals.php">
                <i class="fas fa-calendar-alt"></i> Renewals
            </a>
            <a href="settings.php" class="active">
                <i class="fas fa-cog"></i> Settings
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-cog"></i> Settings</h1>
                <p class="page-subtitle">Manage your account settings and preferences</p>
            </div>
            <a href="dashboard.php" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <?php if($message): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="settings-grid">
            <!-- Profile Settings -->
            <div class="settings-card">
                <h3><i class="fas fa-user"></i> Profile Settings</h3>
                <p class="subtitle">Update your account information</p>

                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" required>
                        <div class="hint">This is your login username</div>
                    </div>
                    <button type="submit" name="update_profile" class="btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="settings-card">
                <h3><i class="fas fa-lock"></i> Change Password</h3>
                <p class="subtitle">Update your account password</p>

                <form method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" placeholder="Enter current password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" placeholder="Enter new password" required>
                        <div class="hint">Minimum 6 characters</div>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn-danger">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>

            <!-- Data Management -->
            <div class="settings-card">
                <h3><i class="fas fa-database"></i> Data Management</h3>
                <p class="subtitle">Export or manage your data</p>

                <div class="settings-actions">
                    <a href="?export=clients" class="btn-success">
                        <i class="fas fa-file-export"></i> Export CSV
                    </a>
                    <a href="#" class="btn-secondary" onclick="alert('Backup feature coming soon!')">
                        <i class="fas fa-download"></i> Backup Database
                    </a>
                </div>
                <div style="margin-top: 12px; font-size: 13px; color: var(--gray-500);">
                    <i class="fas fa-info-circle"></i> Export client data as CSV file
                </div>
            </div>

            <!-- System Info -->
            <div class="settings-card">
                <h3><i class="fas fa-info-circle"></i> System Information</h3>
                <p class="subtitle">About your BTB Insurance system</p>

                <div class="form-group">
                    <label>Version</label>
                    <div style="padding: 8px 0; font-weight: 600;">v3.0</div>
                </div>
                <div class="form-group">
                    <label>Database</label>
                    <div style="padding: 8px 0;">MySQL 5.7+</div>
                </div>
                <div class="form-group">
                    <label>Total Clients</label>
                    <div style="padding: 8px 0; font-weight: 600;">
                        <?= $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn() ?> records
                    </div>
                </div>
                <div class="form-group">
                    <label>Last Login</label>
                    <div style="padding: 8px 0; color: var(--gray-500);">
                        <?= date('d M Y, H:i:s') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="content-footer">
            <p>&copy; <?= date('Y') ?> <strong>Client Management System</strong>. All rights reserved.</p>
            <p class="footer-version">v3.0 <span class="dot">•</span> Settings</p>
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