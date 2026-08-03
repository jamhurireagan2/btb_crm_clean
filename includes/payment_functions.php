<?php
require_once '../config/payment.php';

function getPaymentStats() {
    global $pdo;
    
    try {
        // Check if payments table exists
        $table_check = $pdo->query("SHOW TABLES LIKE 'payments'");
        if ($table_check->rowCount() == 0) {
            return [
                'total' => 0,
                'total_amount' => 0,
                'pending' => 0
            ];
        }
        
        $total = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'completed'")->fetchColumn();
        $total_amount = $pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'completed'")->fetchColumn() ?: 0;
        $pending = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'")->fetchColumn();
        
        return [
            'total' => $total,
            'total_amount' => $total_amount,
            'pending' => $pending
        ];
    } catch (Exception $e) {
        return [
            'total' => 0,
            'total_amount' => 0,
            'pending' => 0
        ];
    }
}
?>