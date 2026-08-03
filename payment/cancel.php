<?php
session_start();
require_once '../config/database.php';
require_once '../includes/payment_functions.php';

$payment_id = $_GET['payment_id'] ?? 0;

if ($payment_id) {
    updatePaymentStatus($payment_id, 'cancelled');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled</title>
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
        .cancel-container {
            background: white;
            border-radius: 16px;
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }
        .cancel-icon {
            width: 80px; height: 80px;
            background: #fee2e2;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px; color: #dc2626;
        }
        .cancel-container h1 { font-size: 28px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
        .cancel-container p { color: #64748b; font-size: 16px; margin-bottom: 24px; }
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
    <div class="cancel-container">
        <div class="cancel-icon">
            <i class="fas fa-times-circle"></i>
        </div>
        <h1>Payment Cancelled</h1>
        <p>Your payment was cancelled. No charges were made.</p>
        <a href="../user/dashboard.php" class="btn-dashboard">
            <i class="fas fa-home"></i> Back to Dashboard
        </a>
    </div>
</body>
</html>