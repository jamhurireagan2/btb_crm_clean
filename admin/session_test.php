<?php
session_start();
echo "<pre>";
echo "SESSION DATA:\n";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['user_id'])) {
    echo "User ID: " . $_SESSION['user_id'] . "\n";
    echo "User Type: " . ($_SESSION['user_type'] ?? 'Not set') . "\n";
} else {
    echo "❌ No user logged in.\n";
}
?>