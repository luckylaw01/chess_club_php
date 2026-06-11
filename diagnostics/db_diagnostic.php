<?php
/**
 * Database Diagnostic Script for Chess Club
 * This script checks for table existence and column constraints
 * that might cause issues with donations and payments.
 */

require_once '../includes/db_connect.php';

header('Content-Type: text/html; charset=utf-8');
echo "<html><head><title>DB Diagnostic</title><style>
    body { font-family: sans-serif; line-height: 1.5; padding: 20px; background: #f8fafc; }
    .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
    h2 { margin-top: 0; color: #1e293b; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
    .status { font-weight: bold; padding: 2px 8px; border-radius: 4px; }
    .success { background: #dcfce7; color: #166534; }
    .error { background: #fee2e2; color: #991b1b; }
    .warning { background: #fef9c3; color: #854d0e; }
    pre { background: #f1f5f9; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style></head><body>";

echo "<h1>Database Diagnostics</h1>";

// 1. Check Payments Table
echo "<div class='card'><h2>Table: payments</h2>";
$res = $conn->query("SHOW COLUMNS FROM payments LIKE 'user_id'");
if ($res && $row = $res->fetch_assoc()) {
    $null = $row['Null'];
    if ($null === 'NO') {
        echo "<p class='status error'>CRITICAL: user_id is NOT NULL.</p>";
        echo "<p>Donations (especially guest ones) will fail because they try to insert a NULL user_id into this table.</p>";
        echo "<p><strong>Fix:</strong> ALTER TABLE payments MODIFY user_id INT NULL;</p>";
    } else {
        echo "<p class='status success'>OK: user_id is NULLABLE.</p>";
    }
    echo "<pre>" . print_r($row, true) . "</pre>";
} else {
    echo "<p class='status error'>Error: Could not find user_id column or payments table.</p>";
}
echo "</div>";

// 2. Check Donations Table
echo "<div class='card'><h2>Table: donations</h2>";
$res = $conn->query("SHOW TABLES LIKE 'donations'");
if ($res->num_rows > 0) {
    echo "<p class='status success'>OK: donations table exists.</p>";
    $res = $conn->query("DESCRIBE donations");
    echo "<pre>";
    while($row = $res->fetch_assoc()) {
        printf("%-20s %-20s %-10s\n", $row['Field'], $row['Type'], $row['Null']);
    }
    echo "</pre>";
} else {
    echo "<p class='status error'>Error: donations table DOES NOT EXIST.</p>";
}
echo "</div>";

// 3. Check Paystack Keys
echo "<div class='card'><h2>Settings: Paystack Keys</h2>";
$res = $conn->query("SELECT * FROM app_settings WHERE setting_key IN ('paystack_secret_key', 'paystack_public_key')");
$keys = [];
while($row = $res->fetch_assoc()) {
    $keys[$row['setting_key']] = $row['setting_value'];
}

if (isset($keys['paystack_secret_key']) && !empty($keys['paystack_secret_key']) && $keys['paystack_secret_key'] !== 'YOUR_SECRET_KEY_HERE') {
    echo "<p class='status success'>OK: Secret key is set.</p>";
} else {
    echo "<p class='status error'>Error: Secret key is missing or default.</p>";
}

if (isset($keys['paystack_public_key']) && !empty($keys['paystack_public_key']) && $keys['paystack_public_key'] !== 'YOUR_PUBLIC_KEY_HERE') {
    echo "<p class='status success'>OK: Public key is set.</p>";
} else {
    echo "<p class='status error'>Error: Public key is missing or default.</p>";
}
echo "</div>";

echo "</body></html>";
?>
