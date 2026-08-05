<?php
session_start();
require_once '../config/database.php';
require_once '../config/payment.php';
require_once '../includes/payment_functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../user/index.php');
    exit;
}

$client_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$client_id]);
$client = $stmt->fetch();

if (!$client) {
    header('Location: ../user/dashboard.php');
    exit;
}

$policy_number = $client['policy_number'];
$amount = 5000; // Example amount

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_id = createPayment($client_id, $policy_number, $amount, 'paypal');
    
    // Use the correct PayPal sandbox URL
    $paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    
    $paypal_data = [
        'cmd' => '_xclick',
        'business' => PAYPAL_BUSINESS_EMAIL,
        'item_name' => 'Policy Renewal - ' . $policy_number,
        'item_number' => $payment_id,
        'amount' => $amount,
        'currency_code' => 'USD',
        'return' => SITE_URL . '/payment/success.php?payment_id=' . $payment_id,
        'cancel_return' => SITE_URL . '/payment/cancel.php?payment_id=' . $payment_id,
        'notify_url' => SITE_URL . '/payment/ipn.php'
    ];
    
    $paypal_query = http_build_query($paypal_data);
    header('Location: ' . $paypal_url . '?' . $paypal_query);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay with PayPal</title>
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
        .payment-container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }
        .payment-header { text-align: center; margin-bottom: 32px; }
        .payment-header .icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #0070ba, #003087);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px; font-size: 28px; color: white;
        }
        .payment-header h1 { font-size: 24px; font-weight: 700; color: #0f172a; }
        .payment-header p { color: #64748b; font-size: 14px; }
        .payment-details {
            background: #f8fafc; border-radius: 12px; padding: 20px;
            margin-bottom: 24px;
        }
        .payment-details .row {
            display: flex; justify-content: space-between;
            padding: 8px 0; border-bottom: 1px solid #e2e8f0;
        }
        .payment-details .row:last-child { border-bottom: none; }
        .payment-details .label { color: #64748b; font-size: 14px; }
        .payment-details .value { font-weight: 600; color: #0f172a; }
        .payment-details .amount { font-size: 24px; font-weight: 700; color: #dc2626; }
        .btn-pay {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #0070ba, #003087);
            color: white; border: none; border-radius: 10px;
            font-size: 16px; font-weight: 600; cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-pay:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,112,186,0.4); }
        .btn-pay i { margin-right: 8px; }
        .back-link {
            display: inline-flex; align-items: center; gap: 8px;
            color: #64748b; text-decoration: none; font-size: 14px;
            margin-top: 16px; transition: all 0.3s ease;
        }
        .back-link:hover { color: #dc2626; }
        .secure-badge { text-align: center; margin-top: 16px; font-size: 13px; color: #94a3b8; }
        .secure-badge i { color: #22c55e; }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <div class="icon"><i class="fab fa-paypal"></i></div>
            <h1>Pay with PayPal</h1>
            <p>Secure payment for your policy renewal</p>
        </div>

        <div class="payment-details">
            <div class="row">
                <span class="label">Client</span>
                <span class="value"><?= htmlspecialchars($client['full_name']) ?></span>
            </div>
            <div class="row">
                <span class="label">Policy Number</span>
                <span class="value"><?= htmlspecialchars($policy_number) ?></span>
            </div>
            <div class="row">
                <span class="label">Policy Type</span>
                <span class="value"><?= htmlspecialchars($client['policy_type']) ?></span>
            </div>
            <div class="row">
                <span class="label">Amount</span>
                <span class="value amount">$ <?= number_format($amount, 2) ?> USD</span>
            </div>
        </div>

        <form method="POST">
            <button type="submit" class="btn-pay">
                <i class="fab fa-paypal"></i> Pay with PayPal
            </button>
        </form>

        <div class="secure-badge">
            <i class="fas fa-lock"></i> Secure payment powered by PayPal Sandbox
        </div>
        <div style="text-align: center;">
            <a href="../user/dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>