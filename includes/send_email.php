<?php
require_once '../config/email.php';

/**
 * Send email using SendGrid API
 */
function sendEmail($to_email, $to_name, $subject, $html_content) {
    $api_key = SENDGRID_API_KEY;
    
    $data = [
        'personalizations' => [
            [
                'to' => [
                    ['email' => $to_email, 'name' => $to_name]
                ]
            ]
        ],
        'from' => [
            'email' => SENDGRID_FROM_EMAIL,
            'name' => SENDGRID_FROM_NAME
        ],
        'reply_to' => [
            'email' => COMPANY_EMAIL,
            'name' => SENDGRID_FROM_NAME
        ],
        'subject' => $subject,
        'content' => [
            [
                'type' => 'text/html',
                'value' => $html_content
            ]
        ]
    ];
    
    $json = json_encode($data);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.sendgrid.com/v3/mail/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    // Log detailed error for debugging
    if ($http_code != 202) {
        $error_log = "SendGrid Error: HTTP $http_code\n";
        $error_log .= "Response: " . $response . "\n";
        $error_log .= "To: $to_email\n";
        $error_log .= "Subject: $subject\n";
        
        // If response contains error message, parse it
        if ($response) {
            $response_data = json_decode($response, true);
            if (isset($response_data['errors'])) {
                foreach ($response_data['errors'] as $error) {
                    $error_log .= "Error: " . $error['message'] . "\n";
                }
            }
        }
        
        error_log($error_log);
        file_put_contents('../logs/email_errors.txt', 
            date('Y-m-d H:i:s') . "\n" . $error_log . str_repeat('-', 60) . "\n", 
            FILE_APPEND
        );
    }
    
    return $http_code == 202;
}

/**
 * Get renewal email template
 */
function getRenewalEmailTemplate($client, $days_remaining) {
    $status = $days_remaining <= 7 ? '⚠️ URGENT' : '📋 Reminder';
    $message = $days_remaining <= 7 
        ? 'Your policy expires in less than a week! Please contact us immediately to renew.'
        : 'Please contact us to discuss your renewal options.';

    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Policy Renewal Reminder</title>
        <style>
            body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc2626; color: white; padding: 25px; text-align: center; border-radius: 10px 10px 0 0; }
            .header h2 { margin: 0; font-size: 22px; }
            .content { background: #f8fafc; padding: 30px; border-radius: 0 0 10px 10px; }
            .policy-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc2626; }
            .policy-details p { margin: 8px 0; }
            .btn { background: #dc2626; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #999; padding-top: 20px; border-top: 1px solid #e2e8f0; }
            .status-urgent { color: #dc2626; font-weight: bold; }
            .status-warning { color: #f59e0b; font-weight: bold; }
            .company-info { margin-top: 20px; padding: 15px; background: #f1f5f9; border-radius: 8px; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>📋 Policy Renewal Reminder</h2>
            </div>
            <div class='content'>
                <h3>Hello " . htmlspecialchars($client['full_name']) . ",</h3>
                <p>This is a reminder that your insurance policy is expiring soon.</p>
                
                <div class='policy-details'>
                    <p><strong>Policy Number:</strong> " . htmlspecialchars($client['policy_number']) . "</p>
                    <p><strong>Policy Type:</strong> " . htmlspecialchars($client['policy_type']) . "</p>
                    <p><strong>Expiry Date:</strong> " . date('d M Y', strtotime($client['expiry_date'])) . "</p>
                    <p><strong>Days Remaining:</strong> <span class='" . ($days_remaining <= 7 ? 'status-urgent' : 'status-warning') . "'>$days_remaining days</span></p>
                    <p><strong>Status:</strong> $status</p>
                </div>
                
                <p style='font-size: 16px;'>$message</p>
                
                <p style='margin-top: 25px;'>
                    <a href='" . SITE_URL . "/user/' class='btn'>🔗 View My Policy</a>
                </p>
                
                <div class='company-info'>
                    <p><strong>📞 Need help?</strong> Call us at <strong>" . COMPANY_PHONE . "</strong></p>
                    <p><strong>📧 Email:</strong> <a href='mailto:" . COMPANY_EMAIL . "' style='color:#dc2626;'>" . COMPANY_EMAIL . "</a></p>
                </div>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Client Management System. All rights reserved.</p>
                <p>This is an automated email. Please do not reply directly.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Send renewal reminder to a client
 */
function sendRenewalReminder($client) {
    $today = new DateTime();
    $expiry = new DateTime($client['expiry_date']);
    $days_remaining = $today->diff($expiry)->days;
    
    // Check if email should be sent
    if (empty($client['email']) || $days_remaining <= 0 || $days_remaining > 30) {
        return false;
    }
    
    $subject = "📋 Policy Renewal Reminder - " . date('d M Y', strtotime($client['expiry_date']));
    $html = getRenewalEmailTemplate($client, $days_remaining);
    
    return sendEmail(
        $client['email'],
        $client['full_name'],
        $subject,
        $html
    );
}

/**
 * Check and send renewal reminders to all expiring clients
 */
function checkAndSendRenewals($pdo) {
    // Get clients expiring within 30 days with emails
    $sql = "SELECT * FROM clients 
            WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
            AND expiry_date >= CURDATE() 
            AND email IS NOT NULL 
            AND email != '' 
            AND TRIM(email) != ''";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $clients = $stmt->fetchAll();
    
    $sent = 0;
    $failed = 0;
    
    foreach ($clients as $client) {
        if (sendRenewalReminder($client)) {
            $sent++;
        } else {
            $failed++;
        }
    }
    
    return ['sent' => $sent, 'failed' => $failed];
}
?>