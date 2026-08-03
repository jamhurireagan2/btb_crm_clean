<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email'] ?? '');
    $policy_number = trim($_POST['policy_number']);
    $policy_type = $_POST['policy_type'];
    $expiry_date = $_POST['expiry_date'];

    if (empty($full_name) || empty($phone) || empty($policy_number) || empty($expiry_date)) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO clients (full_name, phone, email, policy_number, policy_type, expiry_date) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $phone, $email, $policy_number, $policy_type, $expiry_date]);
            
            header('Location: dashboard.php?msg=Client added successfully!');
            exit;
        } catch(PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Policy number already exists. Please use a unique policy number.';
            } else {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Client - Client Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ============================================
           BASE STYLES
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
            --success-light: #dcfce7;
            --shadow: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1);
            --shadow-red: 0 8px 25px rgba(220, 38, 38, 0.35);
            --radius: 16px;
            --radius-sm: 10px;
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

        body.dark-mode .top-nav,
        body.dark-mode .sidebar,
        body.dark-mode .form-card {
            background: var(--bg-card);
            border-color: var(--border-color);
        }

        body.dark-mode .nav-brand span,
        body.dark-mode .page-title h1,
        body.dark-mode .page-subtitle,
        body.dark-mode .form-group label,
        body.dark-mode .form-card h2 {
            color: var(--text-primary);
        }

        body.dark-mode .form-group input,
        body.dark-mode .form-group select {
            background: var(--gray-50);
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        body.dark-mode .form-group input::placeholder,
        body.dark-mode .form-group select::placeholder {
            color: var(--text-secondary);
        }

        body.dark-mode .content-footer {
            border-color: var(--border-color);
            color: var(--text-secondary);
        }

        body.dark-mode .alert-error {
            background: #7f1d1d;
            color: #fca5a5;
            border-color: #7f1d1d;
        }

        /* ============================================
           TOP NAVIGATION
           ============================================ */
        .top-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 68px;
            background: white;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            z-index: 1000;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 22px;
            color: var(--gray-600);
            cursor: pointer;
            padding: 6px 10px;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .mobile-toggle:hover { background: var(--gray-100); }

        .logo-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        .logo-link:hover { transform: scale(1.03); }

        .logo-link img {
            height: 45px;
            width: auto;
            object-fit: contain;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .nav-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-greeting {
            font-size: 14px;
            font-weight: 500;
            color: var(--gray-600);
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            background: var(--red-gradient);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 15px;
            box-shadow: 0 2px 10px rgba(220, 38, 38, 0.3);
        }

        .theme-toggle-nav {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: var(--gray-600);
            padding: 6px;
            border-radius: 50%;
            transition: var(--transition);
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .theme-toggle-nav:hover { background: var(--gray-100); }

        .logout-btn {
            color: var(--gray-400);
            text-decoration: none;
            padding: 6px 10px;
            border-radius: var(--radius-sm);
            transition: var(--transition);
            font-size: 16px;
        }

        .logout-btn:hover {
            background: var(--red-light);
            color: var(--red-primary);
        }

        /* ============================================
           SIDEBAR
           ============================================ */
        .sidebar {
            position: fixed;
            top: 68px;
            left: 0;
            bottom: 0;
            width: 240px;
            background: white;
            border-right: 1px solid var(--gray-200);
            padding: 20px 14px;
            overflow-y: auto;
            z-index: 999;
            transition: transform 0.3s ease;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            color: var(--gray-600);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
        }

        .sidebar-menu a i {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .sidebar-menu a:hover {
            background: var(--red-light);
            color: var(--red-primary);
        }

        .sidebar-menu a.active {
            background: var(--red-light);
            color: var(--red-primary);
            font-weight: 600;
        }

        /* ============================================
           MAIN CONTENT
           ============================================ */
        .main-content {
            margin-left: 240px;
            margin-top: 68px;
            padding: 32px 40px 24px;
            min-height: calc(100vh - 68px);
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-header-left {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .page-header-left .badge-icon {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 4px 14px;
            background: var(--red-light);
            color: var(--red-primary);
            border-radius: var(--radius-full);
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
            width: fit-content;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--gray-900);
            letter-spacing: -0.5px;
        }

        .page-header h1 i {
            color: var(--red-primary);
            margin-right: 12px;
        }

        .page-subtitle {
            color: var(--gray-500);
            font-size: 15px;
            font-weight: 400;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background: transparent;
            color: var(--gray-600);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-back:hover {
            border-color: var(--red-primary);
            color: var(--red-primary);
            transform: translateX(-3px);
        }

        /* ============================================
           FORM CARD
           ============================================ */
        .form-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
            padding: 40px 44px;
            max-width: 820px;
            margin: 0 auto;
            transition: var(--transition);
        }

        .form-card:hover {
            box-shadow: var(--shadow-lg);
        }

        .form-card-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--gray-100);
        }

        .form-card-header .form-icon {
            width: 48px;
            height: 48px;
            background: var(--red-light);
            color: var(--red-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .form-card-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .form-card-header p {
            font-size: 14px;
            color: var(--gray-500);
            margin-top: 2px;
        }

        /* Alert */
        .alert-error {
            background: var(--red-light);
            border: 1px solid #fca5a5;
            padding: 14px 18px;
            border-radius: var(--radius-sm);
            color: var(--red-dark);
            font-size: 14px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-error i {
            font-size: 18px;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px 28px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-700);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-group label i {
            color: var(--red-primary);
            font-size: 14px;
        }

        .form-group label .required {
            color: var(--red-primary);
            font-weight: 700;
        }

        .form-group input,
        .form-group select {
            padding: 12px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
            background: var(--gray-50);
            color: var(--gray-900);
            width: 100%;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--red-primary);
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
            background: white;
        }

        .form-group input::placeholder {
            color: var(--gray-400);
        }

        .form-group .hint {
            font-size: 12px;
            color: var(--gray-400);
            margin-top: 4px;
        }

        .form-group .hint i {
            margin-right: 4px;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 14px;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 2px solid var(--gray-100);
            flex-wrap: wrap;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 13px 32px;
            background: var(--red-gradient);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(220, 38, 38, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 28px;
            background: transparent;
            color: var(--gray-600);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
        }

        .btn-secondary:hover {
            border-color: var(--gray-400);
            color: var(--gray-800);
        }

        /* ============================================
           FOOTER
           ============================================ */
        .content-footer {
            margin-top: 32px;
            padding-top: 18px;
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: var(--gray-500);
            flex-wrap: wrap;
            gap: 8px;
            max-width: 820px;
            margin-left: auto;
            margin-right: auto;
        }

        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 1024px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .form-card {
                padding: 32px 28px;
            }
        }

        @media (max-width: 768px) {
            .mobile-toggle { display: block; }

            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            .sidebar.show { transform: translateX(0); }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .top-nav {
                padding: 0 16px;
                height: 60px;
            }

            .logo-link img {
                height: 32px;
            }

            .user-greeting { display: none; }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-header h1 {
                font-size: 22px;
            }

            .form-card {
                padding: 24px 18px;
                border-radius: var(--radius-sm);
            }

            .form-card-header .form-icon {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }

            .form-card-header h2 {
                font-size: 17px;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn-primary,
            .form-actions .btn-secondary {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 12px;
            }

            .page-header h1 {
                font-size: 19px;
            }

            .page-subtitle {
                font-size: 13px;
            }

            .form-card {
                padding: 18px 14px;
            }

            .form-group input,
            .form-group select {
                padding: 10px 14px;
                font-size: 13px;
            }

            .btn-primary,
            .btn-secondary {
                padding: 11px 20px;
                font-size: 14px;
            }

            .logo-link img {
                height: 28px;
            }

            .nav-user .user-avatar {
                width: 32px;
                height: 32px;
                font-size: 12px;
            }

            .content-footer {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Loading animation for save button */
        .btn-primary.loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .btn-primary.loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="nav-left">
            <button class="mobile-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <a href="dashboard.php" class="logo-link">
                <img src="assets/images/cms-logo-red-white.png" alt="Client Management System">
            </a>
        </div>
        <div class="nav-right">
            <div class="nav-user">
                <span class="user-greeting">Hi, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
                </div>
                <button class="theme-toggle-nav" onclick="toggleTheme()" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="logout.php" class="logout-btn" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-menu">
            <a href="dashboard.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="add_client.php" class="active">
                <i class="fas fa-user-plus"></i> Add Client
            </a>
            <a href="reports.php">
                <i class="fas fa-file-alt"></i> Reports
            </a>
            <a href="renewals.php">
                <i class="fas fa-calendar-alt"></i> Renewals
            </a>
            <a href="settings.php">
                <i class="fas fa-cog"></i> Settings
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-left">
                <div class="badge-icon">
                    <i class="fas fa-user-plus"></i> New Entry
                </div>
                <h1><i class="fas fa-user-plus"></i> Add New Client</h1>
                <p class="page-subtitle">Fill in the client details below to add them to your system</p>
            </div>
            <a href="dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Form Card -->
        <div class="form-card">
            <div class="form-card-header">
                <div class="form-icon">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div>
                    <h2>Client Information</h2>
                    <p>Enter the client's personal and policy details</p>
                </div>
            </div>

            <?php if($error): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="clientForm" onsubmit="return validateForm()">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>
                            <i class="fas fa-user"></i> Full Name
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="full_name" id="full_name" placeholder="e.g. John Smith" required>
                        <span class="hint"><i class="fas fa-info-circle"></i> Enter the client's full legal name</span>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <i class="fas fa-phone"></i> Phone Number
                            <span class="required">*</span>
                        </label>
                        <input type="tel" name="phone" id="phone" placeholder="e.g. 0712345678" required>
                        <span class="hint"><i class="fas fa-info-circle"></i> Include country code if needed</span>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <i class="fas fa-envelope"></i> Email Address
                        </label>
                        <input type="email" name="email" id="email" placeholder="e.g. client@email.com">
                        <span class="hint"><i class="fas fa-info-circle"></i> Optional but recommended</span>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>
                            <i class="fas fa-file-alt"></i> Policy Number
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="policy_number" id="policy_number" placeholder="e.g. POL-2024-001" required>
                        <span class="hint"><i class="fas fa-info-circle"></i> Must be unique - this will be used to identify the client</span>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <i class="fas fa-tag"></i> Policy Type
                            <span class="required">*</span>
                        </label>
                        <select name="policy_type" id="policy_type" required>
                            <option value="">Select Policy Type</option>
                            <option value="Motor">🚗 Motor Vehicle</option>
                            <option value="Life">❤️ Life Insurance</option>
                            <option value="Health">🏥 Health Insurance</option>
                            <option value="Property">🏠 Property Insurance</option>
                            <option value="Travel">✈️ Travel Insurance</option>
                            <option value="Business">💼 Business Insurance</option>
                        </select>
                        <span class="hint"><i class="fas fa-info-circle"></i> Select the type of insurance policy</span>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <i class="fas fa-calendar-alt"></i> Expiry Date
                            <span class="required">*</span>
                        </label>
                        <input type="date" name="expiry_date" id="expiry_date" required>
                        <span class="hint"><i class="fas fa-info-circle"></i> When does the policy expire?</span>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Save Client
                    </button>
                    <a href="dashboard.php" class="btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <footer class="content-footer">
            <p>&copy; <?= date('Y') ?> <strong>Client Management System</strong>. All rights reserved.</p>
            <p class="footer-version">v3.0 <span class="dot">•</span> Premium</p>
        </footer>
    </main>

    <script>
        // ============================================
        // SIDEBAR TOGGLE
        // ============================================
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Close sidebar on outside click (mobile)
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-toggle');
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });

        // ============================================
        // THEME TOGGLE
        // ============================================
        function toggleTheme() {
            document.body.classList.toggle('dark-mode');
            const icons = document.querySelectorAll('.theme-toggle-nav i');
            icons.forEach(icon => {
                if (document.body.classList.contains('dark-mode')) {
                    icon.className = 'fas fa-sun';
                } else {
                    icon.className = 'fas fa-moon';
                }
            });
        }

        // ============================================
        // FORM VALIDATION
        // ============================================
        function validateForm() {
            const name = document.getElementById('full_name').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const policy = document.getElementById('policy_number').value.trim();
            const expiry = document.getElementById('expiry_date').value;

            if (!name) {
                alert('Please enter the client\'s full name');
                document.getElementById('full_name').focus();
                return false;
            }

            if (!phone) {
                alert('Please enter the client\'s phone number');
                document.getElementById('phone').focus();
                return false;
            }

            if (phone.length < 10) {
                alert('Please enter a valid phone number (at least 10 digits)');
                document.getElementById('phone').focus();
                return false;
            }

            if (!policy) {
                alert('Please enter the policy number');
                document.getElementById('policy_number').focus();
                return false;
            }

            if (!expiry) {
                alert('Please select the expiry date');
                document.getElementById('expiry_date').focus();
                return false;
            }

            // Check if expiry date is in the past
            const today = new Date();
            const expiryDate = new Date(expiry);
            if (expiryDate < today) {
                alert('Warning: The expiry date is in the past. Please check the date.');
                document.getElementById('expiry_date').focus();
                return false;
            }

            // Show loading state
            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner"></i> Saving...';

            return true;
        }

        // ============================================
        // SET MIN DATE TO TODAY
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('expiry_date').setAttribute('min', today);
        });

        // ============================================
        // PHONE NUMBER FORMATTING
        // ============================================
        document.getElementById('phone').addEventListener('input', function(e) {
            // Only allow numbers and + sign
            this.value = this.value.replace(/[^0-9+]/g, '');
        });

        // ============================================
        // POLICY NUMBER AUTO-FORMAT
        // ============================================
        document.getElementById('policy_number').addEventListener('input', function(e) {
            // Convert to uppercase
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>