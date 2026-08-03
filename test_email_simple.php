<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Simple Email Test</h1>";

// Check config
require_once 'config/email.php';
echo "<p>✅ Config loaded</p>";
echo "<p>From: " . SENDGRID_FROM_EMAIL . "</p>";

// Check includes
require_once 'includes/send_email.php';
echo "<p>✅ send_email.php loaded</p>";

// Get a client
require_once 'config/database.php';
$stmt = $pdo->prepare("SELECT * FROM clients WHERE email IS NOT NULL AND email != '' LIMIT 1");
$stmt->execute();
$client = $stmt->fetch();

if ($client) {
    echo "<p>Client: " . $client['full_name'] . " (" . $client['email'] . ")</p>";
    
    // Try sending
    $result = sendRenewalReminder($client);
    
    if ($result) {
        echo "<p style='color:green;font-weight:bold;'>✅ Email sent!</p>";
    } else {
        echo "<p style='color:red;font-weight:bold;'>❌ Email failed!</p>";
    }
} else {
    echo "<p style='color:red;'>No clients with email found</p>";
}
?>