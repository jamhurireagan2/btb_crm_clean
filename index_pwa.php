<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Client Management System</title>
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#dc2626">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- Fonts & Icons -->
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
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
        }
        .container {
            max-width: 400px;
            width: 100%;
            background: white;
            border-radius: 20px;
            padding: 32px 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .logo {
            text-align: center;
            margin-bottom: 32px;
        }
        .logo .icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 32px;
            color: white;
        }
        .logo h1 {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
        }
        .logo p {
            color: #64748b;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 16px;
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
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
        }
        .form-group input:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 4px rgba(220,38,38,0.1);
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
            box-shadow: 0 4px 20px rgba(220,38,38,0.3);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(220,38,38,0.4);
        }
        .btn-login:active {
            transform: scale(0.98);
        }
        .error {
            color: #dc2626;
            font-size: 14px;
            margin-bottom: 12px;
            text-align: center;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            color: #94a3b8;
            font-size: 13px;
        }
        .install-banner {
            background: #f1f5f9;
            padding: 10px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
            display: none;
        }
        .install-banner i {
            color: #dc2626;
            margin-right: 6px;
        }
        .badge {
            display: inline-block;
            padding: 2px 12px;
            background: #fee2e2;
            color: #dc2626;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 6px;
        }
        .demo {
            background: #f1f5f9;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            color: #64748b;
            display: inline-block;
        }
        .demo strong {
            color: #dc2626;
        }
        @media (max-width: 480px) {
            .container {
                padding: 24px 16px;
            }
            .logo h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

    <div class="container" id="app">
        <!-- Install Banner -->
        <div class="install-banner" id="installBanner">
            <i class="fas fa-download"></i> Install this app for offline access
        </div>

        <div class="logo">
            <div class="icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1>Client Management</h1>
            <p>Sign in to your account</p>
            <span class="badge"><i class="fas fa-mobile-alt"></i> PWA Ready</span>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <form method="POST" action="pwa_login.php" id="loginForm">
            <div class="form-group">
                <label><i class="fas fa-file-alt"></i> Policy Number</label>
                <input type="text" name="policy_number" placeholder="e.g., POL-001" required id="policyInput">
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" placeholder="Enter your password" required id="passwordInput">
            </div>
            <button type="submit" class="btn-login" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="footer">
            <p class="demo">Demo: <strong>POL-001</strong> / <strong>client123</strong></p>
            <p style="margin-top:8px;font-size:12px;color:#94a3b8;">
                <i class="fas fa-wifi"></i> Works offline
            </p>
        </div>
    </div>

    <script>
        // ============================================
        // PWA INSTALL BANNER
        // ============================================
        let deferredPrompt;

        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            document.getElementById('installBanner').style.display = 'block';
            
            document.getElementById('installBanner').addEventListener('click', function() {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then(function(choiceResult) {
                        if (choiceResult.outcome === 'accepted') {
                            console.log('User accepted the install prompt');
                        } else {
                            console.log('User dismissed the install prompt');
                        }
                        deferredPrompt = null;
                    });
                }
            });
        });

        // ============================================
        // FORM SUBMISSION WITH LOADING STATE
        // ============================================
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
            btn.disabled = true;
        });

        // ============================================
        // TOGGLE PASSWORD VISIBILITY
        // ============================================
        document.getElementById('passwordInput').addEventListener('click', function() {
            // Toggle password visibility
        });

        // ============================================
        // REMEMBER LAST POLICY NUMBER (Local Storage)
        // ============================================
        const savedPolicy = localStorage.getItem('cms_policy');
        if (savedPolicy) {
            document.getElementById('policyInput').value = savedPolicy;
        }

        document.getElementById('policyInput').addEventListener('change', function() {
            localStorage.setItem('cms_policy', this.value);
        });

        // ============================================
        // CHECK FOR UPDATES
        // ============================================
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('Service Worker registered');
                })
                .catch(function(error) {
                    console.log('Service Worker registration failed:', error);
                });
        }
    </script>

</body>
</html>