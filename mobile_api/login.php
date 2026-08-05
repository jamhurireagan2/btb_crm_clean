<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$policy_number = $data['policy_number'] ?? '';
$password = $data['password'] ?? '';

// Debug log
error_log("Login attempt: Policy: $policy_number");

if (empty($policy_number) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing credentials'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE policy_number = ?");
    $stmt->execute([$policy_number]);
    $client = $stmt->fetch();

    if ($client) {
        // Check if password matches (MD5 or bcrypt)
        $password_valid = false;
        
        // Check MD5
        if (md5($password) === $client['user_password']) {
            $password_valid = true;
        }
        // Check bcrypt
        if (password_verify($password, $client['user_password'])) {
            $password_valid = true;
        }
        
        if ($password_valid) {
            // Generate simple token
            $token = bin2hex(random_bytes(32));
            
            $days_remaining = ceil((strtotime($client['expiry_date']) - time()) / 86400);
            $status = $client['expiry_date'] > date('Y-m-d') ? 'Active' : 'Expired';
            
            echo json_encode([
                'success' => true,
                'token' => $token,
                'full_name' => $client['full_name'],
                'policy_number' => $client['policy_number'],
                'policy_type' => $client['policy_type'],
                'expiry_date' => $client['expiry_date'],
                'status' => $status,
                'days_remaining' => max(0, $days_remaining),
                'phone' => $client['phone'],
                'email' => $client['email']
            ]);
            exit;
        }
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Invalid policy number or password'
    ]);
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>