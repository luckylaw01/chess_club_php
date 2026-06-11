<?php
require_once __DIR__ . '/../includes/db_connect.php';

echo "=== USERS (COACHES) ===\n";
$res = $conn->query("SELECT id, username, email, full_name, role, elo_rating FROM users WHERE role = 'coach'");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Query error: " . $conn->error . "\n";
}

echo "=== ACADEMY COURSES ===\n";
$res2 = $conn->query("SELECT id, title, description, coach_id, price, level FROM academy_courses");
if ($res2) {
    while ($row = $res2->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Query error: " . $conn->error . "\n";
}
