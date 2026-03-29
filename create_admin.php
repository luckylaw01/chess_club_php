<?php
/**
 * ONE-TIME USE SCRIPT: Create Admin Account
 * DELETE THIS FILE IMMEDIATELY AFTER USE FOR SECURITY REASONS.
 */

// Include the database connection (which now uses $conn as per your settings)
require_once "includes/db_connect.php";

// Configuration for your admin account
$adminUsername = "admin";
$adminEmail = "admin@ascendingpawn.co.ke";
$adminPassword = "ChangeMe123!"; // You should change this immediately
$adminFullName = "System Administrator";

// Check if admin already exists
$checkSql = "SELECT id FROM users WHERE username = ? OR email = ?";
$stmt = $conn->prepare($checkSql);
$stmt->bind_param("ss", $adminUsername, $adminEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die("Error: An admin user with that username or email already exists.");
}

// Hash the password
$hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

// Insert the admin user
$insertSql = "INSERT INTO users (username, email, password, full_name, role, membership_status) 
              VALUES (?, ?, ?, ?, 'admin', 'active')";

$stmt = $conn->prepare($insertSql);
$stmt->bind_param("ssss", $adminUsername, $adminEmail, $hashedPassword, $adminFullName);

if ($stmt->execute()) {
    echo "<h1>Success!</h1>";
    echo "<p>Admin account created successfully using MySQLi.</p>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> " . htmlspecialchars($adminUsername) . "</li>";
    echo "<li><strong>Password:</strong> " . htmlspecialchars($adminPassword) . "</li>";
    echo "</ul>";
    echo "<p style='color: red; font-weight: bold;'>CRITICAL: Delete this file (create_admin.php) from your server right now!</p>";
} else {
    echo "Error creating account: " . $stmt->error;
}
?>
?>