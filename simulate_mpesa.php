<?php
session_start();
header('Content-Type: application/json');
include "includes/db_connect.php";

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["id"];
    $plan_id = (int)$_POST["plan_id"];
    $amount = (float)$_POST["amount"];
    $phone_number = $_POST["phone_number"];
    
    // Simple validation
    if (empty($phone_number) || strlen($phone_number) < 10) {
        echo json_encode(['success' => false, 'message' => 'Invalid phone number.']);
        exit;
    }

    // In a real scenario, you'd trigger STK Push here via Safaricom Daraja API
    // and wait for a callback. For simulation, we proceed as successful.
    
    // Generate a simulated transaction reference
    $prefixes = ["OEK", "RFG", "MTP", "QLS", "JNX"];
    $reference = $prefixes[array_rand($prefixes)] . strtoupper(substr(md5(time()), 0, 7));

    // Begin transaction
    $conn->begin_transaction();

    try {
        // 1. Record the payment
        $sql_payment = "INSERT INTO payments (user_id, plan_id, amount, phone_number, transaction_reference, status) VALUES (?, ?, ?, ?, ?, 'completed')";
        $stmt_payment = $conn->prepare($sql_payment);
        $stmt_payment->bind_param("iidss", $user_id, $plan_id, $amount, $phone_number, $reference);
        $stmt_payment->execute();
        $stmt_payment->close();

        // 2. Update user's membership
        $renewal_date = date('Y-m-d', strtotime('+1 month')); // Default to 1 month for now
        $sql_user = "UPDATE users SET membership_plan_id = ?, membership_status = 'active', renewal_date = ? WHERE id = ?";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("isi", $plan_id, $renewal_date, $user_id);
        $stmt_user->execute();
        $stmt_user->close();

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true, 
            'message' => 'Payment processed successfully!', 
            'reference' => $reference
        ]);

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()]);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>