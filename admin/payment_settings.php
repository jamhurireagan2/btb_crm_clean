<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Set user_type if not set (for admin)
if (!isset($_SESSION['user_type'])) {
    $_SESSION['user_type'] = 'admin';
}

// Check if user is admin (if username is 'admin')
if ($_SESSION['user_type'] !== 'admin') {
    // Try to check from database
    require_once '../config/database.php';
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user && $user['username'] === 'admin') {
        $_SESSION['user_type'] = 'admin';
    } else {
        header('Location: ../login.php');
        exit;
    }
}

require_once '../config/database.php';
require_once '../includes/payment_functions.php';

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $policy_types = ['Motor', 'Life', 'Health', 'Property', 'Travel', 'Business'];
    $all_success = true;
    
    foreach ($policy_types as $type) {
        $price = trim($_POST['price_' . $type]);
        if (is_numeric($price) && $price >= 0) {
            if (!updatePaymentSetting('policy_price_' . $type, $price)) {
                $all_success = false;
            }
        }
    }
    
    if (isset($_POST['currency_symbol'])) {
        updatePaymentSetting('currency_symbol', trim($_POST['currency_symbol']));
    }
    if (isset($_POST['currency'])) {
        updatePaymentSetting('currency', trim($_POST['currency']));
    }
    
    if ($all_success) {
        $message = '✅ Payment settings updated successfully!';
    } else {
        $error = '❌ Some settings failed to update. Please try again.';
    }
}

$policy_prices = getAllPolicyPrices();
$currency_symbol = getCurrencySymbol();
$currency = getCurrency();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Settings - Admin</title>
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
            flex-wrap: wrap;
            gap: 12px;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
        }
        .header h1 i { color: #dc2626; margin-right: 10px; }
        .header a {
            color: #64748b;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .header a:hover { color: #dc2626; }
        .alert-success {
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            padding: 12px 16px;
            border-radius: 10px;
            color: #166534;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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
        .settings-group {
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid #e2e8f0;
        }
        .settings-group:last-child { border-bottom: none; }
        .settings-group h2 {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 16px;
        }
        .settings-group h2 i {
            color: #dc2626;
            margin-right: 8px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 12px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 4px;
        }
        .form-group input {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 4px rgba(220,38,38,0.1);
        }
        .form-group .hint {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 4px;
        }
        .btn-save {
            padding: 12px 32px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(220,38,38,0.4);
        }
        .btn-save i { margin-right: 8px; }
        @media (max-width: 640px) {
            .container { padding: 16px; }
            .form-row { grid-template-columns: 1fr; }
            .header { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-cog"></i> Payment Settings</h1>
            <a href="../dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
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

        <form method="POST">
            <div class="settings-group">
                <h2><i class="fas fa-tag"></i> Policy Prices</h2>
                <p style="color:#64748b;font-size:14px;margin-bottom:16px;">Set the price for each policy type.</p>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Motor</label>
                        <input type="number" name="price_Motor" value="<?= $policy_prices['Motor'] ?>" step="100" min="0">
                    </div>
                    <div class="form-group">
                        <label>Life</label>
                        <input type="number" name="price_Life" value="<?= $policy_prices['Life'] ?>" step="100" min="0">
                    </div>
                    <div class="form-group">
                        <label>Health</label>
                        <input type="number" name="price_Health" value="<?= $policy_prices['Health'] ?>" step="100" min="0">
                    </div>
                    <div class="form-group">
                        <label>Property</label>
                        <input type="number" name="price_Property" value="<?= $policy_prices['Property'] ?>" step="100" min="0">
                    </div>
                    <div class="form-group">
                        <label>Travel</label>
                        <input type="number" name="price_Travel" value="<?= $policy_prices['Travel'] ?>" step="100" min="0">
                    </div>
                    <div class="form-group">
                        <label>Business</label>
                        <input type="number" name="price_Business" value="<?= $policy_prices['Business'] ?>" step="100" min="0">
                    </div>
                </div>
            </div>

            <div class="settings-group">
                <h2><i class="fas fa-dollar-sign"></i> Currency Settings</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Currency Symbol</label>
                        <input type="text" name="currency_symbol" value="<?= htmlspecialchars($currency_symbol) ?>" placeholder="e.g., KSh, $, €">
                        <div class="hint">Example: KSh, $, €</div>
                    </div>
                    <div class="form-group">
                        <label>Currency Code</label>
                        <input type="text" name="currency" value="<?= htmlspecialchars($currency) ?>" placeholder="e.g., KES, USD, EUR">
                        <div class="hint">Example: KES, USD, EUR</div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </form>
    </div>
</body>
</html>