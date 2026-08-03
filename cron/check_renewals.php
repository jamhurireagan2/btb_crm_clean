<?php
// ============================================
// CRON JOB: Check and Send Renewal Reminders
// Runs daily at 8:00 AM via cron-job.org
// ============================================

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load required files
require_once '../config/database.php';
require_once '../includes/send_email.php';

// Log file for debugging
$log_file = '../logs/cron_log.txt';
$log_dir = dirname($log_file);

// Create logs directory if it doesn't exist
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0777, true);
}

// Start logging
$log_message = "\n" . str_repeat('=', 60) . "\n";
$log_message .= "📧 CRON JOB STARTED: " . date('Y-m-d H:i:s') . "\n";
$log_message .= str_repeat('-', 60) . "\n";

echo "Starting cron job...\n";
$log_message .= "Starting cron job...\n";

try {
    // Get clients expiring within 30 days with valid emails
    $sql = "SELECT * FROM clients 
            WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
            AND expiry_date >= CURDATE() 
            AND email IS NOT NULL 
            AND email != '' 
            AND TRIM(email) != ''";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $clients = $stmt->fetchAll();
    
    $log_message .= "Found " . count($clients) . " clients with expiring policies.\n";
    echo "Found " . count($clients) . " clients with expiring policies.\n";
    
    $sent = 0;
    $failed = 0;
    
    foreach ($clients as $client) {
        $log_message .= "Processing: " . $client['full_name'] . " (" . $client['email'] . ")\n";
        echo "Processing: " . $client['full_name'] . " (" . $client['email'] . ")\n";
        
        // Calculate days remaining
        $today = new DateTime();
        $expiry = new DateTime($client['expiry_date']);
        $days_remaining = $today->diff($expiry)->days;
        
        $log_message .= "  Days remaining: $days_remaining\n";
        
        // Only send if within 30 days and not expired
        if ($days_remaining > 0 && $days_remaining <= 30) {
            $result = sendRenewalReminder($client);
            
            if ($result) {
                $sent++;
                $log_message .= "  ✅ Email sent to " . $client['email'] . "\n";
                echo "  ✅ Email sent\n";
            } else {
                $failed++;
                $log_message .= "  ❌ Failed to send to " . $client['email'] . "\n";
                echo "  ❌ Failed to send\n";
            }
        } else {
            $log_message .= "  ⏭️ Skipped - not within 30 days (expired or too far)\n";
            echo "  ⏭️ Skipped\n";
        }
    }
    
    $log_message .= str_repeat('-', 60) . "\n";
    $log_message .= "📊 SUMMARY: Sent: $sent, Failed: $failed\n";
    $log_message .= str_repeat('=', 60) . "\n";
    
    echo "Renewal reminders sent: $sent, Failed: $failed\n";
    
} catch (Exception $e) {
    $log_message .= "❌ ERROR: " . $e->getMessage() . "\n";
    $log_message .= str_repeat('=', 60) . "\n";
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Write to log file
file_put_contents($log_file, $log_message, FILE_APPEND);

// Also output the log file location for debugging
echo "\n📝 Log saved to: " . $log_file . "\n";
?>