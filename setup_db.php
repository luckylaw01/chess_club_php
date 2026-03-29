<?php
include "includes/db_connect.php";

// Read and execute the SQL file
$sql = file_get_contents('chess_club.sql');

// Split the SQL file into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $statement) {
    if (!empty($statement) && !preg_match('/^--/', $statement)) {
        if ($conn->query($statement) === TRUE) {
            echo "Executed: " . substr($statement, 0, 50) . "...<br>";
        } else {
            echo "Error executing: " . $conn->error . "<br>";
        }
    }
}

echo "Database setup complete!";
$conn->close();
?>