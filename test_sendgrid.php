<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Test started...<br>";

// Check if config file exists
if (file_exists('config/email.php')) {
    echo "✅ config/email.php found<br>";
    require_once 'config/email.php';
    echo "✅ config/email.php loaded<br>";
} else {
    die("❌ config/email.php NOT found");
}

echo "API Key: " . substr(SENDGRID_API_KEY, 0, 10) . "...<br>";
echo "From Email: " . SENDGRID_FROM_EMAIL . "<br>";

// Check cURL
if (function_exists('curl_version')) {
    echo "✅ cURL is enabled<br>";
} else {
    echo "❌ cURL is NOT enabled<br>";
}

// Test sending a simple email
$to_email = 'jamhurireagan2@gmail.com';
$to_name = 'Test User';
$subject = 'Test Email from CMS';
$html_content = '<h1>Test Email</h1><p>This is a test email.</p>';

echo "<br>Sending test email...<br>";

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
$curl_error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $http_code . "<br>";

if ($http_code == 202) {
    echo "<h2 style='color:green;'>✅ Email sent successfully!</h2>";
} else {
    echo "<h2 style='color:red;'>❌ Email failed</h2>";
    echo "<pre>";
    echo "Response: " . htmlspecialchars($response) . "\n";
    if ($curl_error) {
        echo "cURL Error: " . $curl_error . "\n";
    }
    echo "</pre>";
}
?>