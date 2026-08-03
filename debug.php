<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debugging - Step by Step</h1>";

// Step 1: Test Database
echo "<h2>Step 1: Database Connection</h2>";
try {
    require_once 'config/database.php';
    echo "<p style='color:green;'>✅ Database connected</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Database error: " . $e->getMessage() . "</p>";
    exit;
}

// Step 2: Test Payment Config
echo "<h2>Step 2: Payment Config</h2>";
try {
    require_once 'config/payment.php';
    echo "<p style='color:green;'>✅ Payment config loaded</p>";
    echo "<p>Currency Symbol: " . CURRENCY_SYMBOL . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Payment config error: " . $e->getMessage() . "</p>";
    exit;
}

// Step 3: Test Payment Functions
echo "<h2>Step 3: Payment Functions</h2>";
try {
    require_once 'includes/payment_functions.php';
    echo "<p style='color:green;'>✅ Payment functions loaded</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Payment functions error: " . $e->getMessage() . "</p>";
    exit;
}

// Step 4: Test getPaymentStats()
echo "<h2>Step 4: Payment Stats</h2>";
try {
    $stats = getPaymentStats();
    echo "<p style='color:green;'>✅ Payment stats retrieved</p>";
    echo "<pre>";
    print_r($stats);
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Payment stats error: " . $e->getMessage() . "</p>";
}

echo "<h2 style='color:green;'>✅ Debug Complete!</h2>";
?>