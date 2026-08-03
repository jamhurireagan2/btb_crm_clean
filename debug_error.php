<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Debugging Dashboard Error</h1>";

// Test 1: Check if files exist
echo "<h2>Step 1: Checking Files</h2>";

$files_to_check = [
    'config/database.php',
    'config/payment.php',
    'includes/payment_functions.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<p style='color:green;'>✅ $file exists</p>";
    } else {
        echo "<p style='color:red;'>❌ $file NOT found</p>";
    }
}

// Test 2: Test Database
echo "<h2>Step 2: Testing Database</h2>";
try {
    require_once 'config/database.php';
    echo "<p style='color:green;'>✅ Database connected</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test 3: Test Payment Config
echo "<h2>Step 3: Testing Payment Config</h2>";
try {
    if (file_exists('config/payment.php')) {
        require_once 'config/payment.php';
        echo "<p style='color:green;'>✅ Payment config loaded</p>";
        echo "<p>CURRENCY_SYMBOL: " . CURRENCY_SYMBOL . "</p>";
    } else {
        echo "<p style='color:red;'>❌ config/payment.php missing</p>";
        // Define fallback
        define('CURRENCY_SYMBOL', 'KSh');
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Payment config error: " . $e->getMessage() . "</p>";
    define('CURRENCY_SYMBOL', 'KSh');
}

// Test 4: Test Payment Functions
echo "<h2>Step 4: Testing Payment Functions</h2>";
try {
    if (file_exists('includes/payment_functions.php')) {
        require_once 'includes/payment_functions.php';
        echo "<p style='color:green;'>✅ Payment functions loaded</p>";
        $stats = getPaymentStats();
        echo "<p>Stats: " . print_r($stats, true) . "</p>";
    } else {
        echo "<p style='color:red;'>❌ includes/payment_functions.php missing</p>";
        // Define fallback function
        function getPaymentStats() {
            return ['total' => 0, 'total_amount' => 0, 'pending' => 0];
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Payment functions error: " . $e->getMessage() . "</p>";
    function getPaymentStats() {
        return ['total' => 0, 'total_amount' => 0, 'pending' => 0];
    }
}

echo "<h2 style='color:green;'>✅ Debug Complete!</h2>";
echo "<p>Now visit: <a href='dashboard.php'>dashboard.php</a></p>";
?>