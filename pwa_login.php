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

if ($client && password_verify($password, $client['user_password'])) {
    $_SESSION['user_id'] = $client['id'];
    $_SESSION['username'] = $client['full_name'];
    $_SESSION['policy_number'] = $client['policy_number'];
    $_SESSION['user_type'] = 'client';
    
    // Redirect to client dashboard
    header('Location: pwa_dashboard.php');
    exit;
} else {
    header('Location: index_pwa.php?error=Invalid policy number or password');
    exit;
}
?>