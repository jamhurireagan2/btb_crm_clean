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
            max-width: 420px;
            margin: 0 auto;
        }
        
        /* Header with Logo */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background: white;
            padding: 12px 16px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .header-logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
            font-weight: 800;
            font-family: 'Inter', sans-serif;
        }
        .header-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }
        .header-title span {
            color: #dc2626;
        }
        .header .logout {
            color: #dc2626;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            padding: 6px 12px;
            background: #fee2e2;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .header .logout:active {
            transform: scale(0.95);
        }

        /* Welcome Card */
        .welcome-card {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            padding: 20px;
            border-radius: 16px;
            color: white;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        .welcome-card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 150px;
            height: 150px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
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
        .welcome-card .policy i {
            margin-right: 4px;
        }

        /* Stats Grid */
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
            transition: all 0.3s ease;
        }
        .stat-card:active {
            transform: scale(0.96);
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
        .stat-card .status-active {
            color: #22c55e;
            font-weight: 700;
            font-size: 16px;
        }
        .stat-card .status-expiring {
            color: #f59e0b;
            font-weight: 700;
            font-size: 16px;
        }
        .stat-card .status-expired {
            color: #dc2626;
            font-weight: 700;
            font-size: 16px;
        }

        /* Menu Grid */
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
            cursor: pointer;
            border: 2px solid transparent;
        }
        .menu-item:active {
            transform: scale(0.95);
        }
        .menu-item:hover {
            border-color: #fee2e2;
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
        .menu-item .badge {
            display: inline-block;
            background: #dc2626;
            color: white;
            font-size: 10px;
            padding: 1px 8px;
            border-radius: 9999px;
            margin-left: 4px;
        }

        /* Tab Content */
        .tab-content {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            display: none;
        }
        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Payment Options */
        .payment-option {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-option:active {
            transform: scale(0.97);
        }
        .payment-option:hover {
            border-color: #dc2626;
            background: #fef2f2;
        }
        .payment-option i {
            font-size: 24px;
            width: 40px;
            text-align: center;
        }
        .payment-option .paypal { color: #0070ba; }
        .payment-option .mpesa { color: #22c55e; }
        .payment-option .info {
            flex: 1;
        }
        .payment-option .info .title {
            font-weight: 600;
            font-size: 14px;
        }
        .payment-option .info .desc {
            font-size: 12px;
            color: #64748b;
        }
        .payment-option .arrow {
            color: #94a3b8;
        }

        /* History List */
        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .history-item:last-child {
            border-bottom: none;
        }
        .history-item .left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .history-item .left .icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        .history-item .left .icon.success {
            background: #dcfce7;
            color: #22c55e;
        }
        .history-item .left .icon.pending {
            background: #fef3c7;
            color: #f59e0b;
        }
        .history-item .left .icon.failed {
            background: #fee2e2;
            color: #dc2626;
        }
        .history-item .left .info .name {
            font-size: 14px;
            font-weight: 500;
        }
        .history-item .left .info .date {
            font-size: 12px;
            color: #94a3b8;
        }
        .history-item .amount {
            font-weight: 600;
            font-size: 14px;
        }
        .history-item .amount.positive {
            color: #22c55e;
        }
        .history-item .amount.negative {
            color: #dc2626;
        }
        .empty-state {
            text-align: center;
            padding: 30px 20px;
            color: #94a3b8;
        }
        .empty-state i {
            font-size: 40px;
            margin-bottom: 12px;
            color: #e2e8f0;
        }

        /* Logout Button */
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
            transition: all 0.3s ease;
        }
        .logout-btn:active {
            transform: scale(0.98);
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 16px;
            font-size: 12px;
            color: #94a3b8;
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #0f172a;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            display: none;
            z-index: 999;
            max-width: 90%;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .toast.show {
            display: block;
            animation: slideUp 0.3s ease;
        }
        @keyframes slideUp {
            from { transform: translateX(-50%) translateY(20px); opacity: 0; }
            to { transform: translateX(-50%) translateY(0); opacity: 1; }
        }

        @media (max-width: 480px) {
            .container { padding: 0; }
            .welcome-card { padding: 16px; }
            .header { padding: 10px 12px; }
            .header-logo { width: 34px; height: 34px; font-size: 14px; }
            .header-title { font-size: 16px; }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header with Logo -->
        <div class="header">
            <div class="header-left">
                <div class="header-logo">C</div>
                <div class="header-title">CMS</div>
            </div>
            <a href="pwa_logout.php" class="logout">Logout</a>
        </div>

        <!-- Welcome Card -->
        <div class="welcome-card">
            <h2>👋 Welcome, <?= htmlspecialchars($user_name) ?></h2>
            <p>Manage your policy anytime, anywhere</p>
            <span class="policy"><i class="fas fa-file-alt"></i> <?= htmlspecialchars($policy_number) ?></span>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card" onclick="showToast('Policy status: Active ✅')">
                <i class="fas fa-check-circle"></i>
                <div class="status-active">Active</div>
                <div class="label">Policy Status</div>
            </div>
            <div class="stat-card" onclick="showToast('15 days remaining ⏳')">
                <i class="fas fa-calendar-alt"></i>
                <div class="number">15</div>
                <div class="label">Days Left</div>
            </div>
        </div>

        <!-- Menu -->
        <div class="menu-grid">
            <div class="menu-item" onclick="openTab('payment')">
                <i class="fas fa-credit-card"></i>
                <span>Make Payment</span>
            </div>
            <div class="menu-item" onclick="openTab('history')">
                <i class="fas fa-history"></i>
                <span>History</span>
            </div>
            <div class="menu-item" onclick="openTab('profile')">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </div>
            <div class="menu-item" onclick="showToast('🔔 Notifications coming soon!')">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </div>
        </div>

        <!-- Tab: Payment -->
        <div id="tab-payment" class="tab-content">
            <h3 style="margin-bottom:16px;font-size:16px;">💳 Make Payment</h3>
            
            <div class="payment-option" onclick="showToast('🔴 PayPal payment coming soon!')">
                <i class="fab fa-paypal paypal"></i>
                <div class="info">
                    <div class="title">PayPal</div>
                    <div class="desc">Pay securely with PayPal</div>
                </div>
                <i class="fas fa-chevron-right arrow"></i>
            </div>
            
            <div class="payment-option" onclick="showToast('🟢 M-Pesa payment coming soon!')">
                <i class="fas fa-mobile-alt mpesa"></i>
                <div class="info">
                    <div class="title">M-Pesa</div>
                    <div class="desc">Pay with Safaricom M-Pesa</div>
                </div>
                <i class="fas fa-chevron-right arrow"></i>
            </div>
        </div>

        <!-- Tab: History -->
        <div id="tab-history" class="tab-content">
            <h3 style="margin-bottom:16px;font-size:16px;">📜 Payment History</h3>
            
            <div class="history-item">
                <div class="left">
                    <div class="icon success"><i class="fas fa-check"></i></div>
                    <div class="info">
                        <div class="name">Policy Renewal</div>
                        <div class="date">05 Aug 2026</div>
                    </div>
                </div>
                <div class="amount positive">+KSh 5,000</div>
            </div>
            
            <div class="history-item">
                <div class="left">
                    <div class="icon success"><i class="fas fa-check"></i></div>
                    <div class="info">
                        <div class="name">Policy Renewal</div>
                        <div class="date">01 Aug 2026</div>
                    </div>
                </div>
                <div class="amount positive">+KSh 5,000</div>
            </div>
            
            <div class="history-item">
                <div class="left">
                    <div class="icon pending"><i class="fas fa-clock"></i></div>
                    <div class="info">
                        <div class="name">Policy Renewal</div>
                        <div class="date">28 Jul 2026</div>
                    </div>
                </div>
                <div class="amount negative">-KSh 5,000</div>
            </div>
            
            <div class="history-item" onclick="showToast('View all payments in the full history')" style="cursor:pointer;">
                <div class="left">
                    <div class="icon" style="background:#f1f5f9;color:#64748b;"><i class="fas fa-arrow-right"></i></div>
                    <div class="info">
                        <div class="name" style="color:#64748b;">View All</div>
                        <div class="date">See complete history</div>
                    </div>
                </div>
                <i class="fas fa-chevron-right" style="color:#94a3b8;"></i>
            </div>
        </div>

        <!-- Tab: Profile -->
        <div id="tab-profile" class="tab-content">
            <h3 style="margin-bottom:16px;font-size:16px;">👤 My Profile</h3>
            
            <div style="display:flex;align-items:center;gap:16px;padding-bottom:16px;border-bottom:1px solid #e2e8f0;">
                <div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#dc2626,#b91c1c);display:flex;align-items:center;justify-content:center;color:white;font-size:24px;font-weight:700;">
                    <?= strtoupper(substr($user_name, 0, 1)) ?>
                </div>
                <div>
                    <div style="font-weight:700;font-size:16px;"><?= htmlspecialchars($user_name) ?></div>
                    <div style="font-size:13px;color:#64748b;"><?= htmlspecialchars($policy_number) ?></div>
                </div>
            </div>
            
            <div style="padding-top:16px;">
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;">
                    <span style="color:#64748b;">Policy Type</span>
                    <span style="font-weight:500;">Motor</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;">
                    <span style="color:#64748b;">Expiry Date</span>
                    <span style="font-weight:500;">17 Aug 2026</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;">
                    <span style="color:#64748b;">Phone</span>
                    <span style="font-weight:500;">0712345678</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;">
                    <span style="color:#64748b;">Email</span>
                    <span style="font-weight:500;">john@email.com</span>
                </div>
            </div>
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

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script>
        // ============================================
        // TAB SYSTEM
        // ============================================
        function openTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            const targetTab = document.getElementById('tab-' + tabName);
            if (targetTab) {
                targetTab.classList.add('active');
                showToast('📂 ' + tabName.charAt(0).toUpperCase() + tabName.slice(1) + ' opened');
            }
        }

        // ============================================
        // TOAST NOTIFICATION
        // ============================================
        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.classList.add('show');
            
            clearTimeout(toast._timeout);
            toast._timeout = setTimeout(() => {
                toast.classList.remove('show');
            }, 2500);
        }

        // ============================================
        // SERVICE WORKER REGISTRATION
        // ============================================
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('Service Worker registered');
                })
                .catch(function(error) {
                    console.log('Service Worker registration failed:', error);
                });
        }

        // ============================================
        // PWA INSTALL PROMPT
        // ============================================
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            // Show a custom install button if needed
            console.log('PWA can be installed');
        });

        // ============================================
        // CLOSE TABS ON BACK PRESS
        // ============================================
        document.addEventListener('click', function(e) {
            // Close tabs when clicking outside
            if (!e.target.closest('.menu-item') && !e.target.closest('.tab-content')) {
                // Don't auto-close - let user control
            }
        });

        // ============================================
        // AUTO SHOW FIRST TAB ON LOAD
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            // Show payment tab by default
            openTab('payment');
        });
    </script>

</body>
</html>