<?php
session_start();
require_once '../config/database.php';
require_once '../includes/payment_functions.php';

$payment_id = $_GET['payment_id'] ?? 0;
$payment = getPayment($payment_id);

if (!$payment) {
    header('Location: ../user/dashboard.php');
    exit;
}

// Update payment status to completed
updatePaymentStatus($payment_id, 'completed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .success-container {
            background: white;
            border-radius: 16px;
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }
        .success-icon {
            width: 80px; height: 80px;
            background: #dcfce7;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px; color: #22c55e;
        }
        .success-container h1 {
            font-size: 28px; font-weight: 700;
            color: #0f172a; margin-bottom: 8px;
        }
        .success-container p { color: #64748b; font-size: 16px; margin-bottom: 24px; }
        .payment-details {
            background: #f8fafc; border-radius: 12px; padding: 20px;
            margin-bottom: 24px; text-align: left;
        }
        .payment-details .row {
            display: flex; justify-content: space-between;
            padding: 8px 0; border-bottom: 1px solid #e2e8f0;
        }
        .payment-details .row:last-child { border-bottom: none; }
        .payment-details .label { color: #64748b; }
        .payment-details .value { font-weight: 600; color: #0f172a; }
        .btn-dashboard {
            padding: 12px 32px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white; border: none; border-radius: 10px;
            font-size: 16px; font-weight: 600;
            cursor: pointer; text-decoration: none; display: inline-block;
            transition: all 0.3s ease;
        }
        .btn-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(220,38,38,0.4);
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Payment Successful! 🎉</h1>
        <p>Your payment has been processed successfully.</p>

        <div class="payment-details">
            <div class="row">
                <span class="label">Transaction ID</span>
                <span class="value"><?= htmlspecialchars($payment['transaction_id']) ?></span>
            </div>
            <div class="row">
                <span class="label">Amount</span>
                <span class="value"><?= CURRENCY_SYMBOL ?> <?= number_format($payment['amount'], 2) ?></span>
            </div>
            <div class="row">
                <span class="label">Method</span>
                <span class="value"><?= strtoupper($payment['payment_method']) ?></span>
            </div>
            <div class="row">
                <span class="label">Status</span>
                <span class="value" style="color:#22c55e;">Completed</span>
            </div>
        </div>

        <a href="../user/dashboard.php" class="btn-dashboard">
            <i class="fas fa-home"></i> Back to Dashboard
        </a>
    </div>
</body>
</html>