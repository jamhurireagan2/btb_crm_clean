<?php
session_start();
require_once '../config/database.php';
require_once '../includes/payment_functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../user/index.php');
    exit;
}

$client_id = $_SESSION['user_id'];
$payments = getClientPayments($client_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
        }
        .header a {
            color: #64748b;
            text-decoration: none;
        }
        .header a:hover {
            color: #dc2626;
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
        .status-completed {
            color: #22c55e;
            font-weight: 600;
        }
        .status-pending {
            color: #f59e0b;
            font-weight: 600;
        }
        .status-failed {
            color: #dc2626;
            font-weight: 600;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }
        .empty-state i {
            font-size: 48px;
            color: #e2e8f0;
            margin-bottom: 12px;
        }
        @media (max-width: 640px) {
            .container { padding: 16px; }
            table { font-size: 13px; }
            .header { flex-direction: column; gap: 12px; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-history" style="color:#dc2626;"></i> Payment History</h1>
            <a href="../user/dashboard.php"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <?php if(count($payments) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
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
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-credit-card"></i>
            <h3>No payments yet</h3>
            <p>You haven't made any payments.</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>