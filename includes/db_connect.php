<?php
// Database connection configuration
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
    // Local configuration
    $host = 'localhost';
    $db_name = 'chess_club_db';
    $username = 'root';
    $password = '';
} else {
    // Hosted configuration
    $host = 'localhost';
    $db_name = 'lawrirwd_chess_club_db';
    $username = 'lawrirwd_lucky_law';
    $password = 'twinBrothers2025#';
}

// Create a mysqli connection
$conn = new mysqli($host, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    echo "<div style='background: #fee2e2; color: #b91c1c; padding: 20px; border: 1px solid #f87171; border-radius: 8px; margin: 20px; font-family: sans-serif;'>";
    echo "<strong>Database Connection Failed!</strong><br>";
    echo "Error: " . $conn->connect_error . "<br>";
    echo "Host: " . $host . "<br>";
    echo "Database: " . $db_name . "<br>";
    echo "User: " . $username . "</div>";
    die();
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
?>

