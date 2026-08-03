<?php
// Payment Configuration

// PayPal Settings
define('PAYPAL_ENABLED', true);
define('PAYPAL_MODE', 'sandbox'); // 'sandbox' or 'live'
define('PAYPAL_CLIENT_ID', 'YOUR_PAYPAL_CLIENT_ID');
define('PAYPAL_SECRET', 'YOUR_PAYPAL_SECRET');

// M-Pesa Settings (Safaricom)
define('MPESA_ENABLED', true);
define('MPESA_CONSUMER_KEY', 'YOUR_CONSUMER_KEY');
define('MPESA_CONSUMER_SECRET', 'YOUR_CONSUMER_SECRET');
define('MPESA_PASSKEY', 'YOUR_PASSKEY');
define('MPESA_SHORTCODE', '174379'); // Paybill number
define('MPESA_ENVIRONMENT', 'sandbox'); // 'sandbox' or 'live'

// Currency
define('CURRENCY', 'KES');
define('CURRENCY_SYMBOL', 'KSh');

// Company Details
define('COMPANY_NAME', 'Client Management System');
define('COMPANY_EMAIL', 'info@btbinsurance.com');
define('COMPANY_PHONE', '0712345678');
?>