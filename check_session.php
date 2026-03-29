<?php
session_start();
include 'includes/db_connect.php';

echo "<h3>Current Session Data:</h3><pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['id'])) {
    $uid = (int)$_SESSION['id'];
    echo "<p>User ID in session: $uid</p>";
    
    $query = "SELECT id, username, email, full_name, first_name, last_name, phone FROM users WHERE id = $uid";
    $result = $conn->query($query);
    
    if ($result && $row = $result->fetch_assoc()) {
        echo "<h3>Database User Record:</h3><pre>";
        print_r($row);
        echo "</pre>";
        
        $fullName = $row['full_name'];
        if (empty($fullName)) {
            $fullName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        }
        echo "<p>Calculated Full Name: <strong>" . ($fullName ?: 'EMPTY') . "</strong></p>";
    } else {
        echo "<p style='color:red'>No user found in database with ID $uid</p>";
    }
} else {
    echo "<p style='color:red'>User ID is NOT set in session.</p>";
}
?>