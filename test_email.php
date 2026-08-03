<?php
// ============================================
// TEST EMAIL: Test SendGrid Integration
// ============================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'includes/send_email.php';

echo "<h1>📧 SendGrid Email Test</h1>";

// Test 1: Check cURL
echo "<h2>🔍 Test 1: cURL Check</h2>";
if (function_exists('curl_version')) {
    echo "<p style='color:green;'>✅ cURL is enabled!</p>";
    $version = curl_version();
    echo "<p>Version: " . $version['version'] . "</p>";
} else {
    echo "<p style='color:red;'>❌ cURL is NOT enabled!</p>";
}

// Test 2: Check Database Connection
echo "<h2>🔍 Test 2: Database Check</h2>";
try {
    $pdo->query("SELECT 1");
    echo "<p style='color:green;'>✅ Database connected!</p>";
} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test 3: Get a client with email
echo "<h2>🔍 Test 3: Client with Email</h2>";
$stmt = $pdo->prepare("SELECT * FROM clients WHERE email IS NOT NULL AND email != '' AND TRIM(email) != '' LIMIT 1");
$stmt->execute();
$client = $stmt->fetch();

if ($client) {
    echo "<p><strong>Client:</strong> " . htmlspecialchars($client['full_name']) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($client['email']) . "</p>";
    echo "<p><strong>Policy:</strong> " . htmlspecialchars($client['policy_number']) . "</p>";
    echo "<p><strong>Expiry:</strong> " . $client['expiry_date'] . "</p>";
    
    // Calculate days
    $today = new DateTime();
    $expiry = new DateTime($client['expiry_date']);
    $days = $today->diff($expiry)->days;
    echo "<p><strong>Days Remaining:</strong> $days</p>";
    
    if ($days > 0 && $days <= 30) {
        echo "<hr>";
        echo "<h2>📤 Sending Test Email...</h2>";
        
        $result = sendRenewalReminder($client);
        
        if ($result) {
            echo "<p style='color:green; font-weight:bold; font-size:18px;'>✅ Email sent successfully to " . htmlspecialchars($client['email']) . "!</p>";
            echo "<p>Check your inbox (and spam folder).</p>";
        } else {
            echo "<p style='color:red; font-weight:bold;'>❌ Email failed to send.</p>";
            echo "<p>Possible issues:</p>";
            echo "<ul>";
            echo "<li>Check your SendGrid API key in <code>config/email.php</code></li>";
            echo "<li>Make sure you've verified your sender email in SendGrid</li>";
            echo "<li>Check if cURL is enabled</li>";
            echo "<li>Check the error log for more details</li>";
            echo "</ul>";
        }
    } else {
        echo "<p style='color:orange;'>⚠️ Policy not expiring within 30 days (days remaining: $days).</p>";
        echo "<p>Update the expiry date to test:</p>";
        echo "<pre style='background:#f1f5f9;padding:10px;border-radius:5px;'>UPDATE clients SET expiry_date = DATE_ADD(CURDATE(), INTERVAL 15 DAY) WHERE id = {$client['id']};</pre>";
    }
} else {
    echo "<p style='color:red;'>❌ No clients with email addresses found.</p>";
    echo "<p>Update a client's email address in the database first:</p>";
    echo "<pre style='background:#f1f5f9;padding:10px;border-radius:5px;'>UPDATE clients SET email = 'your_email@example.com' WHERE id = 1;</pre>";
}

// Test 4: Check Log File
echo "<h2>🔍 Test 4: Log File Check</h2>";
$log_file = 'logs/cron_log.txt';
if (file_exists($log_file)) {
    echo "<p style='color:green;'>✅ Log file exists!</p>";
    echo "<p>Last 5 lines:</p>";
    echo "<pre style='background:#f1f5f9;padding:10px;border-radius:5px;max-height:200px;overflow:auto;'>";
    $lines = file($log_file);
    $last_lines = array_slice($lines, -5);
    echo htmlspecialchars(implode('', $last_lines));
    echo "</pre>";
} else {
    echo "<p style='color:orange;'>⚠️ Log file not found yet. Run the cron job first.</p>";
}
?>