<?php
/**
 * ONE-TIME USE SCRIPT: Create Admin Account for luckylaw19
 * DELETE THIS FILE IMMEDIATELY AFTER USE FOR SECURITY REASONS.
 */

// Include the database connection
require_once "includes/db_connect.php";

// Configuration for your admin account
$adminUsername = "luckylaw19";
$adminEmail = "luckylaw@gmail.com";
$adminPassword = "entebe2016";
$firstName = "Lucky";
$lastName = "Law";
$fullName = "Lucky Law";

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
$insertSql = "INSERT INTO users (username, email, password, first_name, last_name, full_name, role, membership_status) 
              VALUES (?, ?, ?, ?, ?, ?, 'admin', 'active')";

$stmt = $conn->prepare($insertSql);
$stmt->bind_param("ssssss", $adminUsername, $adminEmail, $hashedPassword, $firstName, $lastName, $fullName);

if ($stmt->execute()) {
    echo "<h1>Success!</h1>";
    echo "<p>Admin account created successfully for <strong>" . htmlspecialchars($adminUsername) . "</strong>.</p>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> " . htmlspecialchars($adminUsername) . "</li>";
    echo "<li><strong>Email:</strong> " . htmlspecialchars($adminEmail) . "</li>";
    echo "<li><strong>Full Name:</strong> " . htmlspecialchars($fullName) . "</li>";
    echo "</ul>";
    echo "<p style='color: red; font-weight: bold;'>CRITICAL: Delete this file (create_luckylaw_admin.php) from your server right now!</p>";
} else {
    echo "Error creating account: " . $stmt->error;
}
?>
