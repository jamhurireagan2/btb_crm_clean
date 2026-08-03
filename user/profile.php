<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$client = $stmt->fetch();

if (!$client) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    if (empty($phone)) {
        $error = 'Phone number is required';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE clients SET phone = ?, email = ? WHERE id = ?");
            $stmt->execute([$phone, $email, $_SESSION['user_id']]);
            $message = 'Profile updated successfully!';
            
            // Refresh client data
            $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $client = $stmt->fetch();
        } catch(PDOException $e) {
            $error = 'Error updating profile: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Client Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
        }

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

        .main-content {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #dc2626;
        }

        .form-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .form-card h1 {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .form-card p {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 24px;
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

        .form-group .readonly {
            padding: 12px 16px;
            background: #f1f5f9;
            border-radius: 10px;
            font-size: 15px;
            color: #475569;
        }

        .btn-save {
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

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(220, 38, 38, 0.4);
        }

        @media (max-width: 480px) {
            .form-card {
                padding: 20px;
            }
            .top-nav {
                padding: 0 16px;
            }
            .main-content {
                padding: 20px 16px;
            }
        }
    </style>
</head>
<body>

    <nav class="top-nav">
        <a href="dashboard.php" class="nav-brand">
            <div class="logo-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <span>Client Portal</span>
        </a>
        <div class="nav-right">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['user_name'] ?? 'C', 0, 1)) ?>
            </div>
            <a href="logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <div class="main-content">
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="form-card">
            <h1><i class="fas fa-user-edit" style="color:#dc2626;"></i> Edit Profile</h1>
            <p>Update your personal information</p>

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
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name</label>
                    <div class="readonly"><?= htmlspecialchars($client['full_name']) ?></div>
                    <small style="color:#94a3b8; font-size:12px;">Contact admin to change your name</small>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Phone Number</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($client['phone']) ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($client['email'] ?? '') ?>" placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-file-alt"></i> Policy Number</label>
                    <div class="readonly"><?= htmlspecialchars($client['policy_number']) ?></div>
                </div>

                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>
    </div>

</body>
</html>