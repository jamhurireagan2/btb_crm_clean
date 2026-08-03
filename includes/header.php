<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BTB Insurance - Client Management</title>
    <link rel="stylesheet" href="assets/style.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <h1><i class="fas fa-shield-alt"></i> BTB Insurance Brokers Ltd</h1>
            </div>
            <nav>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                    <a href="add_client.php"><i class="fas fa-user-plus"></i> Add Client</a>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <span class="user-badge">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                <?php endif; ?>
            </nav>
        </header>
        <main>
            <!-- Display success/error messages -->
            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']) ?>
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>