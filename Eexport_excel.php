<?php
require_once 'config/database.php';
require_once 'config/payment.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Get filters from URL
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$export_type = $_GET['export_type'] ?? 'clients';
$search = $_GET['search'] ?? '';

// Build query based on export type
if ($export_type == 'clients') {
    $sql = "SELECT * FROM clients WHERE 1=1";
    $params = [];
    
    if ($start_date && $end_date) {
        $sql .= " AND created_at BETWEEN ? AND ?";
        $params[] = $start_date . ' 00:00:00';
        $params[] = $end_date . ' 23:59:59';
    }
    
    if ($search) {
        $sql .= " AND (full_name LIKE ? OR phone LIKE ? OR policy_number LIKE ? OR email LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $sql .= " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    
    $filename = 'clients_export_' . date('Y-m-d') . '.xls';
    $headers = ['ID', 'Full Name', 'Phone', 'Email', 'Policy Number', 'Policy Type', 'Expiry Date', 'Created At', 'Status'];
    
} elseif ($export_type == 'payments') {
    $sql = "SELECT p.*, c.full_name as client_name, c.policy_number 
            FROM payments p 
            JOIN clients c ON p.client_id = c.id 
            WHERE 1=1";
    $params = [];
    
    if ($start_date && $end_date) {
        $sql .= " AND p.created_at BETWEEN ? AND ?";
        $params[] = $start_date . ' 00:00:00';
        $params[] = $end_date . ' 23:59:59';
    }
    
    if ($search) {
        $sql .= " AND (c.full_name LIKE ? OR c.policy_number LIKE ? OR p.transaction_id LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    
    $filename = 'payments_export_' . date('Y-m-d') . '.xls';
    $headers = ['ID', 'Client', 'Policy Number', 'Amount', 'Method', 'Transaction ID', 'Status', 'Payment Date', 'Created At'];
    
} else {
    $sql = "SELECT 
                DATE(created_at) as date,
                COUNT(*) as total_clients,
                SUM(CASE WHEN expiry_date < CURDATE() THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN expiry_date >= CURDATE() THEN 1 ELSE 0 END) as active
            FROM clients 
            WHERE 1=1";
    $params = [];
    
    if ($start_date && $end_date) {
        $sql .= " AND created_at BETWEEN ? AND ?";
        $params[] = $start_date . ' 00:00:00';
        $params[] = $end_date . ' 23:59:59';
    }
    
    $sql .= " GROUP BY DATE(created_at) ORDER BY date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    
    $filename = 'reports_export_' . date('Y-m-d') . '.xls';
    $headers = ['Date', 'Total Clients', 'Active Policies', 'Expired Policies'];
}

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Create Excel file
echo '<table border="1">';

// Headers
echo '<tr>';
foreach ($headers as $header) {
    echo '<th style="background:#dc2626;color:#ffffff;font-weight:bold;padding:8px;">' . htmlspecialchars($header) . '</th>';
}
echo '</tr>';

// Data
foreach ($data as $row) {
    echo '<tr>';
    foreach ($headers as $header) {
        $key = strtolower(str_replace(' ', '_', $header));
        $column_map = [
            'id' => 'id',
            'full_name' => 'full_name',
            'phone' => 'phone',
            'email' => 'email',
            'policy_number' => 'policy_number',
            'policy_type' => 'policy_type',
            'expiry_date' => 'expiry_date',
            'created_at' => 'created_at',
            'status' => 'status',
            'client' => 'client_name',
            'amount' => 'amount',
            'method' => 'payment_method',
            'transaction_id' => 'transaction_id',
            'payment_date' => 'payment_date',
            'date' => 'date',
            'total_clients' => 'total_clients',
            'active_policies' => 'active',
            'expired_policies' => 'expired'
        ];
        
        $db_key = $column_map[$key] ?? $key;
        $value = $row[$db_key] ?? '';
        
        if (in_array($db_key, ['expiry_date', 'created_at', 'payment_date', 'date'])) {
            if ($value && $value != '0000-00-00') {
                $value = date('d M Y', strtotime($value));
            }
        }
        
        if ($db_key == 'amount') {
            $value = CURRENCY_SYMBOL . ' ' . number_format($value, 2);
        }
        
        echo '<td style="padding:6px;">' . htmlspecialchars($value) . '</td>';
    }
    echo '</tr>';
}

echo '</table>';
exit;
?>