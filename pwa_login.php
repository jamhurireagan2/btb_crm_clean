<?php
session_start();
require_once 'config/database.php';

$policy_number = $_POST['policy_number'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($policy_number) || empty($password)) {
    header('Location: index_pwa.php?error=Please enter policy number and password');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM clients WHERE policy_number = ?");
$stmt->execute([$policy_number]);
$client = $stmt->fetch();

if ($client) {
    // Check password - supports both MD5 and bcrypt
    $password_valid = false;
    
    // Check if it's MD5 (32 characters hex)
    if (strlen($client['user_password']) == 32 && ctype_xdigit($client['user_password'])) {
        if ($client['user_password'] == md5($password)) {
            $password_valid = true;
        }
    } else {
        // bcrypt check
        if (password_verify($password, $client['user_password'])) {
            $password_valid = true;
        }
    }
    
    if ($password_valid) {
        $_SESSION['user_id'] = $client['id'];
        $_SESSION['username'] = $client['full_name'];
        $_SESSION['policy_number'] = $client['policy_number'];
        $_SESSION['user_type'] = 'client';
        
        header('Location: pwa_dashboard.php');
        exit;
    }
}

// If we get here, login failed
header('Location: index_pwa.php?error=Invalid policy number or password');
exit;
?>