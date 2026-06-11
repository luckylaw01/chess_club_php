<?php
require_once '../includes/db_connect.php';

header('Content-Type: text/plain');

$tablesQuery = $conn->query("SHOW TABLES");
if (!$tablesQuery) {
    die("Error showing tables: " . $conn->error);
}

echo "--- Hosted Database Schema Dump ---\n\n";

while ($row = $tablesQuery->fetch_row()) {
    $table = $row[0];
    echo "TABLE: $table\n";
    echo str_repeat("=", strlen($table) + 7) . "\n";
    
    $createTableQuery = $conn->query("SHOW CREATE TABLE `$table`");
    if ($createTableQuery) {
        $createRow = $createTableQuery->fetch_assoc();
        echo $createRow['Create Table'] . ";\n\n";
    } else {
        echo "Error getting create table for $table: " . $conn->error . "\n\n";
    }
}
echo "--- End of Schema Dump ---\n";
?>
