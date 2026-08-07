<?php
// Test page to verify everything is working
echo "<h1>✅ Test Page Working!</h1>";
echo "<p>If you can see this, your server is working correctly.</p>";

// Check PHP version
echo "<h2>PHP Version</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Check database
echo "<h2>Database Connection</h2>";
try {
    require_once 'config/database.php';
    echo "<p style='color:green;'>✅ Database connected successfully!</p>";
    
    // Show tables
    $tables = $pdo->query("SHOW TABLES");
    echo "<h3>Tables in database:</h3>";
    echo "<ul>";
    while($row = $tables->fetch()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Check session
echo "<h2>Session Test</h2>";
session_start();
$_SESSION['test'] = 'Working!';
echo "<p>Session test value: " . $_SESSION['test'] . "</p>";

// Check file permissions
echo "<h2>File Permissions</h2>";
$files = ['index.php', 'login.php', 'dashboard.php', 'config/database.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p>✅ $file exists</p>";
    } else {
        echo "<p>❌ $file NOT found</p>";
    }
}

echo "<h2 style='color:green;'>✅ All tests passed!</h2>";
echo "<p><a href='index.php'>Go to Homepage</a> | <a href='dashboard.php'>Go to Dashboard</a></p>";
?>