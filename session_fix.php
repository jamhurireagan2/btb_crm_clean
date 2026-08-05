<?php
session_start();

// Fix admin session
if (isset($_SESSION['user_id']) && $_SESSION['username'] === 'admin') {
    $_SESSION['user_type'] = 'admin';
    echo "✅ Session fixed! You are now recognized as admin.<br>";
    echo "<a href='dashboard.php'>Go to Dashboard</a>";
} else {
    echo "❌ Please login as admin first.<br>";
    echo "<a href='login.php'>Login</a>";
}
?>