<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: dashboard.php?error=Client ID missing');
    exit;
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->execute([$id]);
    
    header('Location: dashboard.php?msg=Client deleted successfully!');
    exit;
} catch(PDOException $e) {
    header('Location: dashboard.php?error=Could not delete client: ' . $e->getMessage());
    exit;
}
?>