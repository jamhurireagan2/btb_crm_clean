<?php

function getPaymentStats() {
    global $pdo;
    
    try {
        $table_check = $pdo->query("SHOW TABLES LIKE 'payments'");
        if ($table_check->rowCount() == 0) {
            return ['total' => 0, 'total_amount' => 0, 'pending' => 0];
        }
        
        $total = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'completed'")->fetchColumn();
        $total_amount = $pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'completed'")->fetchColumn() ?: 0;
        $pending = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'")->fetchColumn();
        
        return ['total' => $total, 'total_amount' => $total_amount, 'pending' => $pending];
    } catch (Exception $e) {
        return ['total' => 0, 'total_amount' => 0, 'pending' => 0];
    }
}

function generateTransactionId() {
    return 'TXN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function createPayment($client_id, $policy_number, $amount, $method) {
    global $pdo;
    $transaction_id = generateTransactionId();
    
    $stmt = $pdo->prepare("INSERT INTO payments (client_id, policy_number, amount, payment_method, transaction_id, status) 
                           VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$client_id, $policy_number, $amount, $method, $transaction_id]);
    return $pdo->lastInsertId();
}

function updatePaymentStatus($payment_id, $status, $transaction_id = null) {
    global $pdo;
    
    $sql = "UPDATE payments SET status = ?, payment_date = NOW()";
    $params = [$status];
    
    if ($transaction_id) {
        $sql .= ", transaction_id = ?";
        $params[] = $transaction_id;
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $payment_id;
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function getPayment($payment_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT p.*, c.full_name, c.email, c.phone 
                           FROM payments p 
                           JOIN clients c ON p.client_id = c.id 
                           WHERE p.id = ?");
    $stmt->execute([$payment_id]);
    return $stmt->fetch();
}

function getClientPayments($client_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE client_id = ? ORDER BY created_at DESC");
    $stmt->execute([$client_id]);
    return $stmt->fetchAll();
}

function getAllPayments($limit = 50) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT p.*, c.full_name, c.email 
                           FROM payments p 
                           JOIN clients c ON p.client_id = c.id 
                           ORDER BY p.created_at DESC 
                           LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// ============================================
// NEW: Payment Settings Functions
// ============================================

function getPaymentSetting($key) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT setting_value FROM payment_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : null;
}

function updatePaymentSetting($key, $value) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO payment_settings (setting_key, setting_value) 
                           VALUES (?, ?) 
                           ON DUPLICATE KEY UPDATE setting_value = ?");
    return $stmt->execute([$key, $value, $value]);
}

function getPolicyPrice($policy_type) {
    $price = getPaymentSetting('policy_price_' . $policy_type);
    return $price ? (float)$price : 5000;
}

function getAllPolicyPrices() {
    global $pdo;
    
    $policy_types = ['Motor', 'Life', 'Health', 'Property', 'Travel', 'Business'];
    $prices = [];
    
    foreach ($policy_types as $type) {
        $prices[$type] = getPolicyPrice($type);
    }
    
    return $prices;
}

function getCurrencySymbol() {
    $symbol = getPaymentSetting('currency_symbol');
    return $symbol ? $symbol : 'KSh';
}

function getCurrency() {
    $currency = getPaymentSetting('currency');
    return $currency ? $currency : 'KES';
}
?>