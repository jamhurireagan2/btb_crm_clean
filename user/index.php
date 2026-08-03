<?php
session_start();
require_once '../config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'client') {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $policy_number = trim($_POST['policy_number']);
    $password = $_POST['password'];

    if (empty($policy_number) || empty($password)) {
        $error = 'Please enter your policy number and password';
    } else {
        // Check if client exists with this policy number
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE policy_number = ?");
        $stmt->execute([$policy_number]);
        $client = $stmt->fetch();

        if ($client) {
            // Check password - supports both MD5 and bcrypt
            $password_valid = false;
            
            // Check if password is MD5 hash (32 characters hex)
            if (strlen($client['user_password']) == 32 && ctype_xdigit($client['user_password'])) {
                // MD5 check
                if ($client['user_password'] == md5($password)) {
                    $password_valid = true;
                }
            } else {
                // bcrypt check
                if (password_verify($password, $client['user_password'])) {
                    $password_valid = true;
                }
            }
            
            if ($password_valid) {
                $_SESSION['user_id'] = $client['id'];
                $_SESSION['user_name'] = $client['full_name'];
                $_SESSION['user_policy'] = $client['policy_number'];
                $_SESSION['user_type'] = 'client';
                
                // Update last login
                $stmt = $pdo->prepare("UPDATE clients SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$client['id']]);
                
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid policy number or password!';
            }
        } else {
            $error = 'Invalid policy number or password!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Login - Client Management System</title>
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
            background: linear-gradient(135deg, #0f172a, #1e293b);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            padding: 48px 40px;
            max-width: 440px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-header .icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 28px;
            color: white;
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.3);
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
        }

        .login-header p {
            color: #64748b;
            font-size: 14px;
            margin-top: 4px;
        }

        .login-header .badge {
            display: inline-block;
            padding: 4px 12px;
            background: #fee2e2;
            color: #dc2626;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 8px;
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

        .form-group label i {
            color: #dc2626;
            margin-right: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-group input:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
            background: white;
        }

        .form-group .hint {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 20px rgba(220, 38, 38, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(220, 38, 38, 0.4);
        }

        .login-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .login-footer p {
            color: #94a3b8;
            font-size: 13px;
        }

        .login-footer .demo {
            background: #f1f5f9;
            padding: 8px 16px;
            border-radius: 8px;
            display: inline-block;
            font-size: 12px;
            color: #475569;
        }

        .login-footer .demo strong {
            color: #dc2626;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #64748b;
            text-decoration: none;
            font-size: 13px;
            margin-top: 12px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #dc2626;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 32px 24px;
            }
            .login-header h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-header">
            <div class="icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1>Client Portal</h1>
            <p>Access your policy information</p>
            <span class="badge"><i class="fas fa-lock"></i> Secure Login</span>
        </div>

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-file-alt"></i> Policy Number</label>
                <input type="text" name="policy_number" placeholder="e.g. POL-001" required>
                <div class="hint">Enter your policy number as shown on your documents</div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
                <div class="hint">Default: <strong>client123</strong></div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Access My Account
            </button>
        </form>

        <div class="login-footer">
            <div class="demo">
                <i class="fas fa-info-circle"></i> Demo: <strong>POL-001</strong> / <strong>client123</strong>
            </div>
            <br>
            <a href="../index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>

</body>
</html>