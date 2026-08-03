<?php
session_start();
require_once '../config/database.php';
require_once '../config/payment.php';
require_once '../includes/payment_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../user/index.php');
    exit;
}

$client_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get client data
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$client_id]);
$client = $stmt->fetch();

if (!$client) {
    header('Location: ../user/dashboard.php');
    exit;
}

$policy_number = $client['policy_number'];
$amount = 5000; // Example amount

// Handle M-Pesa payment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_POST['phone'];
    
    // Validate phone number (Kenyan format)
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (substr($phone, 0, 1) == '0') {
        $phone = '254' . substr($phone, 1);
    } elseif (substr($phone, 0, 4) != '254') {
        $phone = '254' . $phone;
    }
    
    if (strlen($phone) != 12) {
        $error = 'Please enter a valid Kenyan phone number (e.g., 0712345678)';
    } else {
        // Create payment record
        $payment_id = createPayment($client_id, $policy_number, $amount, 'mpesa');
        
        // Here you would integrate with Safaricom API
        // For now, we'll simulate the payment
        // In production, you'd call the Safaricom STK Push API
        
        // Simulate success
        updatePaymentStatus($payment_id, 'completed', 'MPESA-' . date('Ymd') . '-' . rand(1000, 9999));
        $success = 'Payment initiated! Please check your phone for the STK Push prompt.';
        
        // Send confirmation email
        // mail($client['email'], 'Payment Confirmation', 'Your payment was successful.');
        
        header('Location: success.php?payment_id=' . $payment_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay with M-Pesa - Client Management System</title>
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
        .payment-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .payment-header .icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 28px;
            color: white;
        }
        .payment-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
        }
        .payment-header p {
            color: #64748b;
            font-size: 14px;
        }
        .payment-details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .payment-details .row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .payment-details .row:last-child {
            border-bottom: none;
        }
        .payment-details .label {
            color: #64748b;
            font-size: 14px;
        }
        .payment-details .value {
            font-weight: 600;
            color: #0f172a;
        }
        .payment-details .amount {
            font-size: 24px;
            font-weight: 700;
            color: #dc2626;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 4px rgba(220,38,38,0.1);
        }
        .form-group .hint {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 4px;
        }
        .btn-pay {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(34,197,94,0.4);
        }
        .btn-pay i {
            margin-right: 8px;
        }
        .alert-error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            padding: 12px 16px;
            border-radius: 10px;
            color: #991b1b;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            margin-top: 16px;
            transition: all 0.3s ease;
        }
        .back-link:hover {
            color: #dc2626;
        }
        .secure-badge {
            text-align: center;
            margin-top: 16px;
            font-size: 13px;
            color: #94a3b8;
        }
        .secure-badge i {
            color: #22c55e;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <div class="icon">
                <i class="fas fa-mobile-alt"></i>
            </div>
            <h1>Pay with M-Pesa</h1>
            <p>Pay securely using Safaricom M-Pesa</p>
        </div>

        <?php if($error): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

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
                <span class="label">Amount</span>
                <span class="value amount"><?= CURRENCY_SYMBOL ?> <?= number_format($amount, 2) ?></span>
            </div>
        </div>

        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-phone"></i> M-Pesa Phone Number</label>
                <input type="tel" name="phone" placeholder="e.g., 0712345678" required>
                <div class="hint">Enter the phone number registered with M-Pesa</div>
            </div>
            <button type="submit" class="btn-pay">
                <i class="fas fa-check-circle"></i> Pay with M-Pesa
            </button>
        </form>

        <div class="secure-badge">
            <i class="fas fa-lock"></i> Secured by Safaricom M-Pesa
        </div>

        <div style="text-align: center;">
            <a href="../user/dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>