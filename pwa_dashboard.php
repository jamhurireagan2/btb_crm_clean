<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: index_pwa.php');
    exit;
}

$user_name = $_SESSION['username'] ?? 'Client';
$policy_number = $_SESSION['policy_number'] ?? 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard - Client Management System</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#dc2626">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            padding: 16px;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
        }
        .header .logout {
            color: #dc2626;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        .welcome-card {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            padding: 20px;
            border-radius: 16px;
            color: white;
            margin-bottom: 20px;
        }
        .welcome-card h2 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .welcome-card p {
            opacity: 0.8;
            font-size: 14px;
        }
        .welcome-card .policy {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            margin-top: 8px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 16px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .stat-card i {
            font-size: 24px;
            color: #dc2626;
            margin-bottom: 4px;
        }
        .stat-card .number {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
        }
        .stat-card .label {
            font-size: 12px;
            color: #64748b;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        .menu-item {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            text-decoration: none;
            color: #0f172a;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
        }
        .menu-item:active {
            transform: scale(0.95);
        }
        .menu-item i {
            font-size: 28px;
            color: #dc2626;
            margin-bottom: 8px;
        }
        .menu-item span {
            display: block;
            font-size: 13px;
            font-weight: 500;
        }
        .logout-btn {
            width: 100%;
            padding: 14px;
            background: #fee2e2;
            color: #dc2626;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
        }
        .logout-btn:active {
            transform: scale(0.98);
        }
        .footer {
            text-align: center;
            margin-top: 16px;
            font-size: 12px;
            color: #94a3b8;
        }
        @media (max-width: 480px) {
            .container { padding: 0; }
            .welcome-card { padding: 16px; }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-shield-alt" style="color:#dc2626;"></i> CMS</h1>
            <a href="pwa_logout.php" class="logout">Logout</a>
        </div>

        <!-- Welcome -->
        <div class="welcome-card">
            <h2>👋 Welcome, <?= htmlspecialchars($user_name) ?></h2>
            <p>Manage your policy anytime, anywhere</p>
            <span class="policy"><i class="fas fa-file-alt"></i> <?= htmlspecialchars($policy_number) ?></span>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>
                <div class="number">Active</div>
                <div class="label">Policy Status</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-alt"></i>
                <div class="number">15</div>
                <div class="label">Days Left</div>
            </div>
        </div>

        <!-- Menu -->
        <div class="menu-grid">
            <a href="pwa_payment.php" class="menu-item">
                <i class="fas fa-credit-card"></i>
                <span>Make Payment</span>
            </a>
            <a href="pwa_history.php" class="menu-item">
                <i class="fas fa-history"></i>
                <span>History</span>
            </a>
            <a href="pwa_profile.php" class="menu-item">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            <a href="#" class="menu-item" onclick="alert('Coming soon!')">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>
        </div>

        <!-- Logout -->
        <form action="pwa_logout.php" method="POST">
            <button type="submit" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Sign Out
            </button>
        </form>

        <div class="footer">
            <p>&copy; <?= date('Y') ?> Client Management System</p>
        </div>
    </div>

    <script>
        // Service Worker Registration
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('Service Worker registered');
                })
                .catch(function(error) {
                    console.log('Service Worker registration failed:', error);
                });
        }

        // Install Prompt
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            // Show a custom install button if needed
        });
    </script>

</body>
</html>