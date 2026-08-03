<?php
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BTB Insurance - Login</title>
    <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ============================================
           LOGIN PAGE CSS
           ============================================ */
        :root {
            --red-primary: #dc2626;
            --red-dark: #b91c1c;
            --red-light: #fee2e2;
            --red-gradient: linear-gradient(135deg, #dc2626, #b91c1c);
            --white: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --success: #22c55e;
            --warning: #f59e0b;
            --shadow: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1);
            --shadow-red: 0 8px 25px rgba(220, 38, 38, 0.35);
            --radius: 12px;
            --radius-sm: 8px;
            --radius-full: 9999px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.6;
        }

        /* Dark Mode */
        body.dark-mode {
            --gray-50: #0f172a;
            --gray-100: #1e293b;
            --gray-200: #334155;
            --gray-300: #475569;
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --bg-nav: #1e293b;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --border-color: #334155;
        }

        body.dark-mode .login-wrapper {
            background: var(--bg-card);
        }
        body.dark-mode .login-form-section {
            background: var(--bg-card);
        }
        body.dark-mode .form-header h3 {
            color: var(--text-primary);
        }
        body.dark-mode .form-header p {
            color: var(--text-secondary);
        }
        body.dark-mode .login-form .form-group label {
            color: var(--text-secondary);
        }
        body.dark-mode .login-form .form-group input {
            background: var(--gray-50);
            border-color: var(--border-color);
            color: var(--text-primary);
        }
        body.dark-mode .form-footer {
            border-color: var(--border-color);
        }
        body.dark-mode .form-footer p {
            color: var(--text-secondary);
        }

        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gray-50);
            position: relative;
            overflow: hidden;
        }

        .login-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .bg-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.05;
            animation: float 20s infinite ease-in-out;
        }

        .shape-1 {
            width: 500px;
            height: 500px;
            background: var(--red-primary);
            top: -150px;
            right: -100px;
            animation-delay: 0s;
        }
        .shape-2 {
            width: 300px;
            height: 300px;
            background: var(--red-dark);
            bottom: -50px;
            left: -50px;
            animation-delay: -5s;
        }
        .shape-3 {
            width: 200px;
            height: 200px;
            background: var(--red-primary);
            top: 50%;
            left: 50%;
            animation-delay: -10s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(30px, -30px) scale(1.1); }
            50% { transform: translate(-20px, 20px) scale(0.9); }
            75% { transform: translate(20px, 30px) scale(1.05); }
        }

        .login-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1100px;
            width: 100%;
            background: var(--white);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            position: relative;
            z-index: 1;
            margin: 20px;
        }

        /* Left Side - Brand */
        .login-brand-section {
            background: var(--red-gradient);
            padding: 50px 40px;
            display: flex;
            align-items: center;
            color: white;
        }

        .brand-content { width: 100%; }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 40px;
        }

        .logo-icon {
            width: 56px;
            height: 56px;
            background: rgba(255,255,255,0.2);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            backdrop-filter: blur(10px);
        }

        .brand-logo h1 {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .brand-logo p {
            font-size: 14px;
            opacity: 0.8;
            margin-top: -2px;
        }

        .brand-tagline {
            margin-bottom: 40px;
        }

        .brand-tagline h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .brand-tagline p {
            font-size: 16px;
            opacity: 0.85;
        }

        .brand-features {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .feature-icon {
            width: 44px;
            height: 44px;
            background: rgba(255,255,255,0.15);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .feature-item h4 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .feature-item p {
            font-size: 13px;
            opacity: 0.75;
        }

        /* Right Side - Login Form */
        .login-form-section {
            padding: 50px 40px;
            display: flex;
            align-items: center;
            background: var(--white);
        }

        .form-container {
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
        }

        .form-header {
            margin-bottom: 32px;
        }

        .form-header h3 {
            font-size: 28px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 6px;
        }

        .form-header p {
            color: var(--gray-500);
            font-size: 15px;
        }

        .alert-error {
            background: var(--red-light);
            border: 1px solid #fca5a5;
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            color: var(--red-dark);
            font-size: 14px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .login-form .form-group {
            margin-bottom: 20px;
        }

        .login-form .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 6px;
        }

        .login-form .form-group label i {
            margin-right: 8px;
            color: var(--red-primary);
        }

        .login-form .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 15px;
            transition: var(--transition);
            background: var(--gray-50);
            color: var(--gray-900);
        }

        .login-form .form-group input:focus {
            outline: none;
            border-color: var(--red-primary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: var(--red-gradient);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-red);
        }

        .form-footer {
            text-align: center;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-200);
        }

        .form-footer p {
            color: var(--gray-500);
            font-size: 13px;
        }

        .form-footer .copyright {
            margin-top: 8px;
            font-size: 12px;
            color: var(--gray-400);
        }

        .theme-toggle-float {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: white;
            border: 1px solid var(--gray-200);
            color: var(--gray-900);
            font-size: 20px;
            cursor: pointer;
            box-shadow: var(--shadow-lg);
            z-index: 100;
            transition: var(--transition);
        }

        .theme-toggle-float:hover {
            transform: scale(1.1);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                max-width: 500px;
                margin: 20px;
            }
            .login-brand-section { padding: 30px; }
            .login-form-section { padding: 30px; }
        }

        @media (max-width: 480px) {
            .login-wrapper {
                margin: 10px;
                border-radius: var(--radius-sm);
            }
            .login-brand-section { padding: 20px; }
            .login-form-section { padding: 20px; }
            .form-container { max-width: 100%; }
            .brand-tagline h2 { font-size: 24px; }
        }
    </style>
</head>
<body class="login-page">
    <!-- Animated Background -->
    <div class="login-bg">
        <div class="bg-shape shape-1"></div>
        <div class="bg-shape shape-2"></div>
        <div class="bg-shape shape-3"></div>
    </div>

    <div class="login-wrapper">
        <!-- Left Side - Brand -->
        <div class="login-brand-section">
            <div class="brand-content">
                
                <div class="brand-tagline">
                    <h2>Welcome Back</h2>
                    <p>Manage your client portfolio with ease and efficiency.</p>
                </div>

                <div class="brand-features">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-users"></i></div>
                        <div>
                            <h4>Client Management</h4>
                            <p>Track all your clients in one place</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-file-contract"></i></div>
                        <div>
                            <h4>Policy Tracking</h4>
                            <p>Monitor policies and renewals</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-bell"></i></div>
                        <div>
                            <h4>Smart Alerts</h4>
                            <p>Never miss a renewal again</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-form-section">
            <div class="form-container">
                <div class="form-header">
                          <div class="brand-logo">
<img src="assets/images/cms-logo-red-white.png" alt="Client Management System" width="200"></div>
                    <h3>Sign In</h3>
                    <p>Enter your credentials to access your dashboard</p>
                </div>

                <?php if($error): ?>
                    <div class="alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-user"></i>
                            Username
                        </label>
                        <input type="text" name="username" placeholder="Enter your username" required>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <input type="password" name="password" placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                </form>

                <div class="form-footer">
                    <p>Demo: <strong>admin</strong> / <strong>password123</strong></p>
                    <p class="copyright">&copy; <?= date('Y') ?> BTB Insurance Brokers Ltd.</p>
                </div>
            </div>
        </div>
    </div>

    <button class="theme-toggle-float" onclick="toggleTheme()" title="Toggle Theme">
        <i class="fas fa-moon"></i>
    </button>

    <script>
        function toggleTheme() {
            document.body.classList.toggle('dark-mode');
            const icon = document.querySelector('.theme-toggle-float i');
            if (document.body.classList.contains('dark-mode')) {
                icon.className = 'fas fa-sun';
            } else {
                icon.className = 'fas fa-moon';
            }
        }
    </script>
</body>
</html>