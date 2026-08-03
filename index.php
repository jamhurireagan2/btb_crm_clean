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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Management System</title>
    <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ============================================
           VIDEO BACKGROUND
           ============================================ */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0f172a;
            color: #0f172a;
            line-height: 1.6;
        }

        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .video-background video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            transform: translate(-50%, -50%);
            object-fit: cover;
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.78);
            z-index: 1;
        }

        .video-fallback {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            z-index: 0;
        }

        body.dark-mode .video-overlay {
            background: rgba(15, 23, 42, 0.88);
        }

        /* ============================================
           LANDING PAGE
           ============================================ */
        .landing-page {
            position: relative;
            z-index: 1;
        }

        /* Navigation */
        .landing-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: rgba(255, 255, 255, 0.95);
            border-bottom: 1px solid #e2e8f0;
            z-index: 1000;
            padding: 0 24px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        body.dark-mode .landing-nav {
            background: rgba(15, 23, 42, 0.95);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
        }

        .logo-link {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .logo-link img {
            height: 40px;
            width: auto;
            object-fit: contain;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-links a {
            text-decoration: none;
            color: #475569;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        body.dark-mode .nav-links a {
            color: rgba(255, 255, 255, 0.7);
        }

        .nav-links a:hover {
            color: #dc2626;
        }

        .btn-login {
            padding: 8px 24px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white !important;
            border-radius: 9999px;
            font-weight: 600;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.35);
            color: white !important;
        }

        .theme-toggle {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #475569;
            padding: 8px;
            border-radius: 50%;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        body.dark-mode .theme-toggle {
            color: rgba(255, 255, 255, 0.7);
        }

        .theme-toggle:hover {
            background: #f1f5f9;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: #0f172a;
            padding: 4px 8px;
            border-radius: 8px;
        }

        body.dark-mode .mobile-menu-btn {
            color: white;
        }

        /* ============================================
           HERO SECTION - FIXED HEIGHT
           ============================================ */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 100px 24px 80px;
            position: relative;
        }

        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            width: 100%;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: rgba(220, 38, 38, 0.2);
            border: 1px solid rgba(220, 38, 38, 0.3);
            color: #fca5a5;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .hero-content h1 {
            font-size: 48px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 20px;
            color: white;
            letter-spacing: -0.5px;
        }

        .hero-content h1 .highlight {
            color: #dc2626;
        }

        .hero-content p {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 32px;
            max-width: 480px;
            line-height: 1.7;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            margin-bottom: 48px;
            flex-wrap: wrap;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(220, 38, 38, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(220, 38, 38, 0.5);
            color: white;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            transform: translateY(-2px);
        }

        .hero-stats {
            display: flex;
            gap: 48px;
        }

        .hero-stats .stat-item h3 {
            font-size: 32px;
            font-weight: 700;
            color: white;
        }

        .hero-stats .stat-item p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 0;
        }

        /* Hero Card */
        .hero-visual {
            display: flex;
            justify-content: center;
        }

        .hero-card {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .card-dots {
            display: flex;
            gap: 6px;
        }

        .card-dots span {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
        }

        .card-dots span:first-child { background: #dc2626; }
        .card-dots span:nth-child(2) { background: #f59e0b; }
        .card-dots span:last-child { background: #22c55e; }

        .card-header span {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
        }

        .card-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .card-stat {
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .card-stat .stat-label {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-stat .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: white;
        }

        .card-stat .stat-value.text-warning { color: #f59e0b; }
        .card-stat .stat-value.text-success { color: #22c55e; }

        /* ============================================
           FEATURES SECTION
           ============================================ */
        .features-section {
            padding: 80px 24px;
            background: #ffffff;
            position: relative;
            z-index: 1;
        }

        body.dark-mode .features-section {
            background: #0f172a;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 48px;
        }

        .section-tag {
            display: inline-block;
            padding: 4px 16px;
            background: #fee2e2;
            color: #dc2626;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        body.dark-mode .section-tag {
            background: rgba(220, 38, 38, 0.15);
            color: #fca5a5;
        }

        .section-header h2 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #0f172a;
        }

        body.dark-mode .section-header h2 { color: white; }

        .section-header h2 .highlight { color: #dc2626; }

        .section-header p {
            font-size: 18px;
            color: #64748b;
            max-width: 600px;
            margin: 0 auto;
        }

        body.dark-mode .section-header p { color: rgba(255, 255, 255, 0.6); }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .feature-card {
            background: #f8fafc;
            padding: 32px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            text-align: center;
        }

        body.dark-mode .feature-card {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.05);
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-color: #dc2626;
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            background: #fee2e2;
            color: #dc2626;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 16px;
        }

        body.dark-mode .feature-icon {
            background: rgba(220, 38, 38, 0.15);
        }

        .feature-card h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #0f172a;
        }

        body.dark-mode .feature-card h3 { color: white; }

        .feature-card p {
            color: #64748b;
            font-size: 14px;
        }

        body.dark-mode .feature-card p { color: rgba(255, 255, 255, 0.6); }

        /* ============================================
           ABOUT SECTION
           ============================================ */
        .about-section {
            padding: 80px 24px;
            background: #f8fafc;
            position: relative;
            z-index: 1;
        }

        body.dark-mode .about-section {
            background: #0f172a;
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .about-content h2 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 16px;
            color: #0f172a;
        }

        body.dark-mode .about-content h2 { color: white; }

        .about-content h2 .highlight { color: #dc2626; }

        .about-content p {
            color: #64748b;
            font-size: 16px;
            margin-bottom: 24px;
        }

        body.dark-mode .about-content p { color: rgba(255, 255, 255, 0.7); }

        .about-list {
            list-style: none;
            margin-bottom: 32px;
        }

        .about-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            color: #475569;
        }

        body.dark-mode .about-list li { color: rgba(255, 255, 255, 0.7); }

        .about-list li i { color: #dc2626; }

        .about-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            text-align: center;
            max-width: 350px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        body.dark-mode .about-card {
            background: #1e293b;
            border-color: #334155;
        }

        .about-card i {
            font-size: 48px;
            color: #dc2626;
            margin-bottom: 12px;
        }

        .about-card h3 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
        }

        body.dark-mode .about-card h3 { color: white; }

        .about-card p {
            color: #64748b;
            margin-bottom: 24px;
        }

        body.dark-mode .about-card p { color: rgba(255, 255, 255, 0.6); }

        .about-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .about-stats div {
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
        }

        body.dark-mode .about-stats div {
            background: rgba(255, 255, 255, 0.05);
        }

        .about-stats span {
            display: block;
            font-size: 24px;
            font-weight: 700;
            color: #dc2626;
        }

        .about-stats p {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 0;
        }

        /* ============================================
           CTA SECTION
           ============================================ */
        .cta-section {
            padding: 80px 24px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            position: relative;
            z-index: 1;
        }

        .cta-content {
            text-align: center;
            color: white;
        }

        .cta-content h2 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .cta-content h2 .highlight { color: #fca5a5; }

        .cta-content p {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 32px;
        }

        .cta-content .btn-primary {
            background: white;
            color: #dc2626;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .cta-content .btn-primary:hover {
            background: white;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            color: #dc2626;
        }

        /* ============================================
           FOOTER
           ============================================ */
        .landing-footer {
            background: #0f172a;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding: 48px 24px 24px;
            position: relative;
            z-index: 1;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto 32px;
        }

        .footer-brand .logo-link {
            margin-bottom: 12px;
            display: inline-block;
        }

        .footer-brand span {
            font-size: 18px;
            font-weight: 700;
            display: block;
            color: white;
        }

        .footer-brand p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
            margin-top: 8px;
        }

        .footer-links h4 {
            font-size: 14px;
            font-weight: 600;
            color: white;
            margin-bottom: 12px;
        }

        .footer-links a {
            display: block;
            color: rgba(255, 255, 255, 0.5);
            text-decoration: none;
            font-size: 14px;
            padding: 4px 0;
            transition: all 0.3s ease;
        }

        .footer-links a:hover { color: #dc2626; }

        .social-icons {
            display: flex;
            gap: 12px;
        }

        .social-icons a {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.5);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-icons a:hover {
            background: #dc2626;
            color: white;
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 0 auto;
            padding-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            text-align: center;
            color: rgba(255, 255, 255, 0.4);
            font-size: 14px;
        }

        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 1024px) {
            .hero-container {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .hero-content p {
                margin: 0 auto 32px;
            }
            .hero-buttons {
                justify-content: center;
            }
            .hero-stats {
                justify-content: center;
            }
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .about-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .about-list {
                text-align: left;
                display: inline-block;
            }
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .hero-content h1 {
                font-size: 42px;
            }
        }

        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
            .nav-links {
                display: none;
                position: absolute;
                top: 70px;
                left: 0;
                right: 0;
                background: white;
                flex-direction: column;
                padding: 20px;
                border-bottom: 1px solid #e2e8f0;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            }
            body.dark-mode .nav-links {
                background: #1e293b;
                border-color: #334155;
            }
            .nav-links.show {
                display: flex;
            }
            .hero-content h1 {
                font-size: 32px;
            }
            .hero-stats {
                gap: 24px;
            }
            .hero-stats .stat-item h3 {
                font-size: 24px;
            }
            .section-header h2 {
                font-size: 28px;
            }
            .features-grid {
                grid-template-columns: 1fr;
            }
            .about-grid {
                grid-template-columns: 1fr;
            }
            .footer-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .social-icons {
                justify-content: center;
            }
            .hero-card {
                max-width: 100%;
            }
            .card-body {
                grid-template-columns: 1fr 1fr;
            }
            .logo-link img {
                height: 32px;
            }
            .hero-section {
                padding: 90px 16px 60px;
            }
        }

        @media (max-width: 480px) {
            .hero-card {
                padding: 16px;
            }
            .card-body {
                grid-template-columns: 1fr;
            }
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            .hero-buttons .btn-primary,
            .hero-buttons .btn-secondary {
                width: 100%;
                justify-content: center;
            }
            .hero-content h1 {
                font-size: 28px;
            }
            .cta-content h2 {
                font-size: 28px;
            }
            .logo-link img {
                height: 28px;
            }
            .about-card {
                padding: 24px;
            }
            .features-section,
            .about-section,
            .cta-section {
                padding: 50px 16px;
            }
        }
    </style>
</head>
<body class="landing-page">

    <!-- VIDEO BACKGROUND -->
    <div class="video-background">
        <video autoplay muted loop playsinline style="width:100%;height:100%;object-fit:cover;">
            <source src="https://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4">
        </video>
        <div class="video-overlay"></div>
    </div>

    <!-- Navigation -->
    <nav class="landing-nav">
        <div class="nav-container">
            <a href="index.php" class="logo-link">
                <img src="assets/images/cms-logo-red-white.png" alt="Client Management System">
            </a>
            <div class="nav-links" id="navLinks">
                <a href="user/" class="btn-client">Client Portal</a>
                <a href="#features">Features</a>
                <a href="#about">About</a>
                <a href="login.php" class="btn-login">Sign In</a>
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fas fa-certificate"></i>
                    Client Management Platform
                </div>
                <h1>Manage Your <span class="highlight">Clients</span> With Confidence</h1>
                <p>Streamline your client management, track policies, and never miss a renewal with our powerful digital platform.</p>
                <div class="hero-buttons">
                    <a href="login.php" class="btn-primary">
                        <i class="fas fa-rocket"></i> Get Started
                    </a>
                    <a href="#features" class="btn-secondary">
                        <i class="fas fa-play-circle"></i> Learn More
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <h3>500+</h3>
                        <p>Active Clients</p>
                    </div>
                    <div class="stat-item">
                        <h3>1,200+</h3>
                        <p>Policies Managed</p>
                    </div>
                    <div class="stat-item">
                        <h3>98%</h3>
                        <p>Satisfaction Rate</p>
                    </div>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-card">
                    <div class="card-header">
                        <div class="card-dots">
                            <span></span><span></span><span></span>
                        </div>
                        <span>Client Overview</span>
                    </div>
                    <div class="card-body">
                        <div class="card-stat">
                            <div class="stat-label">Total Clients</div>
                            <div class="stat-value">2</div>
                        </div>
                        <div class="card-stat">
                            <div class="stat-label">Active Policies</div>
                            <div class="stat-value">1</div>
                        </div>
                        <div class="card-stat">
                            <div class="stat-label">Expiring Soon</div>
                            <div class="stat-value text-warning">0</div>
                        </div>
                        <div class="card-stat">
                            <div class="stat-label">Renewals This Month</div>
                            <div class="stat-value text-success">5</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Features</span>
                <h2>Everything You Need to <span class="highlight">Manage Clients</span></h2>
                <p>Powerful tools designed to simplify client management</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-users"></i></div>
                    <h3>Client Management</h3>
                    <p>Add, edit, and manage all your clients in one centralized dashboard.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-file-contract"></i></div>
                    <h3>Policy Tracking</h3>
                    <p>Track policy types, numbers, and expiry dates effortlessly.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-bell"></i></div>
                    <h3>Renewal Alerts</h3>
                    <p>Get notified about upcoming renewals and never miss a deadline.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-search"></i></div>
                    <h3>Smart Search</h3>
                    <p>Quickly find clients by name, phone, or policy number.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
                    <h3>Reports & Analytics</h3>
                    <p>Visual insights into your client base and policy performance.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3>Secure & Reliable</h3>
                    <p>Enterprise-grade security with encrypted data and secure access.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section" id="about">
        <div class="container">
            <div class="about-grid">
                <div class="about-content">
                    <span class="section-tag">About Us</span>
                    <h2>CMS <span class="highlight">Since 2010</span></h2>
                    <p>We provide comprehensive client management solutions and digital tools to help you manage your client relationships efficiently.</p>
                    <ul class="about-list">
                        <li><i class="fas fa-check-circle"></i> Trusted by 500+ clients</li>
                        <li><i class="fas fa-check-circle"></i> 24/7 Access to your portfolio</li>
                        <li><i class="fas fa-check-circle"></i> Expert support team</li>
                        <li><i class="fas fa-check-circle"></i> Modern, intuitive platform</li>
                    </ul>
                    <a href="login.php" class="btn-primary">Get Started Now</a>
                </div>
                <div class="about-image">
                    <div class="about-card">
                        <i class="fas fa-building"></i>
                        <h3>Client Management</h3>
                        <p>Streamline your future</p>
                        <div class="about-stats">
                            <div>
                                <span>15+</span>
                                <p>Years Experience</p>
                            </div>
                            <div>
                                <span>98%</span>
                                <p>Client Satisfaction</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to <span class="highlight">Streamline</span> Your Client Management?</h2>
                <p>Join hundreds of professionals using Client Management System platform.</p>
                <a href="login.php" class="btn-primary">
                    <i class="fas fa-arrow-right"></i> Get Started Free
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="index.php" class="logo-link">
                        <img src="assets/images/cms-logo-red-white.png" alt="Client Management System" height="40">
                    </a>
                    <span>Client Management System</span>
                    <p>Professional client management platform for businesses and agencies.</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <a href="#features">Features</a>
                    <a href="#about">About</a>
                    <a href="login.php">Sign In</a>
                </div>
                <div class="footer-links">
                    <h4>Support</h4>
                    <a href="#">Help Center</a>
                    <a href="#">Contact Us</a>
                    <a href="#">Privacy Policy</a>
                </div>
                <div class="footer-social">
                    <h4>Connect With Us</h4>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Client Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleTheme() {
            document.body.classList.toggle('dark-mode');
            const icon = document.querySelector('.theme-toggle i');
            if (document.body.classList.contains('dark-mode')) {
                icon.className = 'fas fa-sun';
                document.querySelector('.video-overlay').style.background = 'rgba(15, 23, 42, 0.85)';
            } else {
                icon.className = 'fas fa-moon';
                document.querySelector('.video-overlay').style.background = 'rgba(15, 23, 42, 0.75)';
            }
        }

        function toggleMobileMenu() {
            document.getElementById('navLinks').classList.toggle('show');
        }

        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('navLinks').classList.remove('show');
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>