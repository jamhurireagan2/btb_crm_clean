<?php
session_start();
require_once '../config/database.php';
require_once '../includes/payment_functions.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$payments = getAllPayments(100);
$stats = getPaymentStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            border-left: 4px solid #dc2626;
        }
        .stat-card .number {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
        }
        .stat-card .label {
            color: #64748b;
            font-size: 14px;
        }
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background: #f8fafc;
        }
        th {
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
        }
        td {
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        .status-completed { color: #22c55e; font-weight: 600; }
        .status-pending { color: #f59e0b; font-weight: 600; }
        .status-failed { color: #dc2626; font-weight: 600; }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            text-decoration: none;
        }
        .back-link:hover { color: #dc2626; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-credit-card" style="color:#dc2626;"></i> Payment Management</h1>
            <a href="../dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?= $stats['total'] ?></div>
                <div class="label">Total Payments</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= CURRENCY_SYMBOL ?> <?= number_format($stats['total_amount'], 2) ?></div>
                <div class="label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['pending'] ?></div>
                <div class="label">Pending Payments</div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Client</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payments as $payment): ?>
                    <tr>
                        <td><?= htmlspecialchars($payment['transaction_id']) ?></td>
                        <td><?= htmlspecialchars($payment['full_name']) ?></td>
                        <td><?= CURRENCY_SYMBOL ?> <?= number_format($payment['amount'], 2) ?></td>
                        <td><?= strtoupper($payment['payment_method']) ?></td>
                        <td><?= date('d M Y', strtotime($payment['created_at'])) ?></td>
                        <td>
                            <span class="status-<?= $payment['status'] ?>">
                                <?= ucfirst($payment['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>