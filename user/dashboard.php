<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: index.php');
    exit;
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$client = $stmt->fetch();

if (!$client) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Calculate policy status
$today = new DateTime();
$expiry = new DateTime($client['expiry_date']);
$diff = $today->diff($expiry)->days;
$isExpired = $expiry < $today;
$isExpiring = !$isExpired && $diff <= 30;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Client Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
        }

        /* Top Navigation */
        .top-nav {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 24px;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .nav-brand .logo-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        .nav-brand span {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .nav-right .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-right .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .nav-right .user-name {
            font-weight: 600;
            font-size: 14px;
            color: #0f172a;
        }

        .nav-right .user-policy {
            font-size: 12px;
            color: #94a3b8;
        }

        .btn-logout {
            padding: 6px 16px;
            background: #fee2e2;
            color: #dc2626;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .btn-logout:hover {
            background: #dc2626;
            color: white;
        }

        /* Main Content */
        .main-content {
            max-width: 1000px;
            margin: 0 auto;
            padding: 32px 24px;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            border-radius: 16px;
            padding: 32px 40px;
            color: white;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: rgba(220, 38, 38, 0.1);
            border-radius: 50%;
        }

        .welcome-banner h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .welcome-banner p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 15px;
        }

        .welcome-banner .policy-badge {
            display: inline-block;
            padding: 4px 16px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 9999px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 8px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            padding: 20px 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .stat-card .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-bottom: 12px;
        }

        .stat-card .stat-icon.green {
            background: #dcfce7;
            color: #22c55e;
        }

        .stat-card .stat-icon.yellow {
            background: #fef3c7;
            color: #f59e0b;
        }

        .stat-card .stat-icon.red {
            background: #fee2e2;
            color: #dc2626;
        }

        .stat-card .stat-icon.blue {
            background: #dbeafe;
            color: #2563eb;
        }

        .stat-card .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
        }

        .stat-card .stat-label {
            font-size: 13px;
            color: #64748b;
        }

        /* Policy Card */
        .policy-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            margin-bottom: 32px;
        }

        .policy-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .policy-card .card-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        .policy-card .card-header h2 i {
            color: #dc2626;
            margin-right: 10px;
        }

        .status-badge {
            padding: 4px 16px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-badge.active {
            background: #dcfce7;
            color: #22c55e;
        }

        .status-badge.expiring {
            background: #fef3c7;
            color: #f59e0b;
        }

        .status-badge.expired {
            background: #fee2e2;
            color: #dc2626;
        }

        .policy-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 32px;
        }

        .policy-detail {
            display: flex;
            flex-direction: column;
        }

        .policy-detail .label {
            font-size: 12px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .policy-detail .value {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin-top: 2px;
        }

        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .profile-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .profile-card .card-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        .profile-card .card-header h2 i {
            color: #dc2626;
            margin-right: 10px;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 32px;
        }

        .profile-item .label {
            font-size: 12px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .profile-item .value {
            font-size: 16px;
            font-weight: 500;
            color: #0f172a;
            margin-top: 2px;
        }

        .btn-edit {
            padding: 8px 20px;
            background: #dc2626;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .btn-edit:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            .policy-details {
                grid-template-columns: 1fr;
            }
            .profile-grid {
                grid-template-columns: 1fr;
            }
            .welcome-banner {
                padding: 24px 20px;
            }
            .welcome-banner h1 {
                font-size: 22px;
            }
            .nav-right .user-name {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .top-nav {
                padding: 0 16px;
            }
            .main-content {
                padding: 16px;
            }
            .policy-card,
            .profile-card {
                padding: 20px;
            }
            .welcome-banner h1 {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>

    <!-- Top Navigation -->
    <nav class="top-nav">
        <a href="dashboard.php" class="nav-brand">
            <div class="logo-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <span>Client Portal</span>
        </a>
        <div class="nav-right">
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['user_name'] ?? 'C', 0, 1)) ?>
                </div>
                <div>
                    <div class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Client') ?></div>
                    <div class="user-policy"><?= htmlspecialchars($_SESSION['user_policy'] ?? '') ?></div>
                </div>
            </div>
            <a href="logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <h1>Welcome, <?= htmlspecialchars($client['full_name']) ?>! 👋</h1>
            <p>Here's an overview of your policy and account information.</p>
            <span class="policy-badge">
                <i class="fas fa-file-alt"></i> Policy: <?= htmlspecialchars($client['policy_number']) ?>
            </span>
        </div>

        <!-- Renewal Alert -->
<?php 
$days = $diff; // Already calculated in dashboard
if (!$isExpired && $days <= 30): 
?>
<div style="background: <?= $days <= 7 ? '#fee2e2' : '#fef3c7' ?>; border: 1px solid <?= $days <= 7 ? '#fca5a5' : '#fcd34d' ?>; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
    <div style="font-size: 24px;">
        <?= $days <= 7 ? '🔴' : '🟡' ?>
    </div>
    <div>
        <strong style="color: <?= $days <= 7 ? '#dc2626' : '#f59e0b' ?>;">
            <?= $days <= 7 ? '⚠️ URGENT: Your policy expires in ' . $days . ' days!' : '📋 Reminder: Your policy expires in ' . $days . ' days' ?>
        </strong>
        <p style="font-size: 14px; color: #475569; margin-top: 2px;">
            Please contact us to renew your policy.
            <?php if ($days <= 7): ?>
                <strong>Immediate action required!</strong>
            <?php endif; ?>
        </p>
    </div>
</div>
<?php endif; ?>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon <?= $isExpired ? 'red' : ($isExpiring ? 'yellow' : 'green') ?>">
                    <i class="fas <?= $isExpired ? 'fa-times-circle' : ($isExpiring ? 'fa-clock' : 'fa-check-circle') ?>"></i>
                </div>
                <div class="stat-value"><?= $isExpired ? 'Expired' : ($isExpiring ? 'Expiring Soon' : 'Active') ?></div>
                <div class="stat-label">Policy Status</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-value"><?= date('d M Y', strtotime($client['expiry_date'])) ?></div>
                <div class="stat-label">Expiry Date</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon <?= $isExpired ? 'red' : ($isExpiring ? 'yellow' : 'green') ?>">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-value">
                    <?php if($isExpired): ?>
                        Expired
                    <?php else: ?>
                        <?= $diff ?> days
                    <?php endif; ?>
                </div>
                <div class="stat-label">Time Remaining</div>
            </div>
        </div>

        <!-- Policy Details -->
        <div class="policy-card">
            <div class="card-header">
                <h2><i class="fas fa-file-contract"></i> Policy Details</h2>
                <span class="status-badge <?= $isExpired ? 'expired' : ($isExpiring ? 'expiring' : 'active') ?>">
                    <?= $isExpired ? 'Expired' : ($isExpiring ? 'Expiring Soon' : 'Active') ?>
                </span>
            </div>
            <div class="policy-details">
                <div class="policy-detail">
                    <span class="label">Policy Number</span>
                    <span class="value"><?= htmlspecialchars($client['policy_number']) ?></span>
                </div>
                <div class="policy-detail">
                    <span class="label">Policy Type</span>
                    <span class="value"><?= htmlspecialchars($client['policy_type']) ?></span>
                </div>
                <div class="policy-detail">
                    <span class="label">Expiry Date</span>
                    <span class="value"><?= date('d M Y', strtotime($client['expiry_date'])) ?></span>
                </div>
                <div class="policy-detail">
                    <span class="label">Days Remaining</span>
                    <span class="value">
                        <?php if($isExpired): ?>
                            <span style="color:#dc2626;">Expired</span>
                        <?php else: ?>
                            <span style="color:<?= $diff <= 30 ? '#f59e0b' : '#22c55e' ?>;">
                                <?= $diff ?> days
                            </span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Profile Information -->
        <div class="profile-card">
            <div class="card-header">
                <h2><i class="fas fa-user"></i> My Profile</h2>
                <a href="profile.php" class="btn-edit">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
            <div class="profile-grid">
                <div class="profile-item">
                    <span class="label">Full Name</span>
                    <div class="value"><?= htmlspecialchars($client['full_name']) ?></div>
                </div>
                <div class="profile-item">
                    <span class="label">Phone Number</span>
                    <div class="value"><?= htmlspecialchars($client['phone']) ?></div>
                </div>
                <div class="profile-item">
                    <span class="label">Email Address</span>
                    <div class="value"><?= htmlspecialchars($client['email'] ?? 'Not provided') ?></div>
                </div>
                <div class="profile-item">
                    <span class="label">Member Since</span>
                    <div class="value"><?= date('d M Y', strtotime($client['created_at'])) ?></div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>