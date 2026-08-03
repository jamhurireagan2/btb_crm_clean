<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>📧 SendGrid Final Test</h1>";

// Load config
require_once 'config/email.php';
echo "<p>✅ API Key loaded</p>";

// Test cURL
if (function_exists('curl_version')) {
    echo "<p>✅ cURL is enabled</p>";
} else {
    die("<p style='color:red;'>❌ cURL is NOT enabled</p>");
}

// Test sending email
$to_email = 'jamhurireagan2@gmail.com';
$to_name = 'Test User';
$subject = 'Test Email from CMS';
$html_content = '<h1>✅ Test Email</h1><p>This is a test email from your Client Management System.</p>';

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
    'Authorization: Bearer ' . SENDGRID_API_KEY,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> " . $http_code . "</p>";

if ($http_code == 202) {
    echo "<p style='color:green;font-weight:bold;font-size:20px;'>✅ EMAIL SENT SUCCESSFULLY!</p>";
} else {
    echo "<p style='color:red;font-weight:bold;font-size:18px;'>❌ Email failed</p>";
}
?>