<?php
require __DIR__ . '/../includes/db_connect.php';
$sql = "ALTER TABLE tournaments ADD COLUMN poster_url VARCHAR(255) DEFAULT NULL;";
if ($conn->query($sql)) {
    echo "Success: poster_url added.\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
