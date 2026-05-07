<?php
    echo "<h1>Testing Notifications System Database Fetch...</h1>";

    // Include the actual database connection file used by the site
    require_once 'includes/db_connect.php';

    if (!$conn || $conn->connect_error) {
        die("Connection failed: " . ($conn ? $conn->connect_error : "No connection object found"));
    }
    
    // Pick a test User ID (let's take id = 1 or just grab any first user id)
    $res = $conn->query("SELECT id FROM users LIMIT 1");
    if($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $user_id = $row['id'];
        echo "Testing with user_id: " . $user_id . "<br>";
        
        $query = "SELECT n.id, n.is_read, n.created_at, nc.title, nc.message, nc.type 
                            FROM notifications n 
                            JOIN notification_content nc ON n.content_id = nc.id 
                            WHERE n.user_id = ? 
                            ORDER BY n.created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo "Found " . count($notifications) . " notifications for this user.<br>";
        echo "<pre>";
        print_r($notifications);
        echo "</pre>";

    } else {
        echo "No users found in the database. Cannot run fetch test properly.";
    }
?>