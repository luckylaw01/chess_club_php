<?php
// Local configuration for CLI
$host = 'localhost';
$db_name = 'chess_club_db';
$username = 'root';
$password = '';

// Create a mysqli connection
$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add profile_picture column to users table
$sql_add_photo = "ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255) DEFAULT NULL;";
if ($conn->query($sql_add_photo) === TRUE) {
    echo "Column 'profile_picture' checked/added successfully.\n";
} else {
    echo "Error adding column: " . $conn->error . "\n";
}

// Ensure payments table can store both membership and cart order payments
$sql_payments = "CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NULL,
    order_id INT NULL,
    amount DECIMAL(10,2) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    transaction_reference VARCHAR(100) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY (user_id),
    KEY (plan_id),
    KEY (order_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (plan_id) REFERENCES membership_plans(id),
    FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql_payments) === TRUE) {
    echo "Table 'payments' created successfully or already exists.\n";
} else {
    echo "Error creating payments table: " . $conn->error . "\n";
}

$alterPlanId = "ALTER TABLE payments MODIFY plan_id INT NULL";
if ($conn->query($alterPlanId) === TRUE) {
    echo "Column 'plan_id' updated to allow NULL.\n";
} else {
    echo "Error updating plan_id column: " . $conn->error . "\n";
}

$alterOrderId = "ALTER TABLE payments ADD COLUMN IF NOT EXISTS order_id INT NULL AFTER plan_id";
if ($conn->query($alterOrderId) === TRUE) {
    echo "Column 'order_id' checked/added successfully.\n";
} else {
    echo "Error updating order_id column: " . $conn->error . "\n";
}

// Store Paystack keys in database
$sql_settings = "CREATE TABLE IF NOT EXISTS app_settings (
    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql_settings) === TRUE) {
    echo "Table 'app_settings' created successfully or already exists.\n";
} else {
    echo "Error creating app_settings table: " . $conn->error . "\n";
}

$paystackSecret = 'YOUR_SECRET_KEY_HERE';
$paystackPublic = 'pk_live_b4f0c19885752db1a1b96d1d587343ec49626f1b';
$stmt = $conn->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
if ($stmt) {
    $key = 'paystack_secret_key';
    $value = $paystackSecret;
    $stmt->bind_param('ss', $key, $value);
    $stmt->execute();

    $key = 'paystack_public_key';
    $value = $paystackPublic;
    $stmt->bind_param('ss', $key, $value);
    $stmt->execute();
    $stmt->close();
    echo "Paystack keys saved in app_settings.\n";
} else {
    echo "Error preparing app_settings insert: " . $conn->error . "\n";
}

$conn->close();
?>