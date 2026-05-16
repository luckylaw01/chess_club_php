<?php
session_start();
header('Content-Type: application/json');

require_once 'includes/db_connect.php';
require_once 'includes/paystack_gateway.php';

$keys = paystack_get_keys($conn);
if ($keys['secret_key'] === '' || $keys['public_key'] === '') {
    echo json_encode(['success' => false, 'message' => 'Paystack keys are not configured in the database.']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action !== 'initialize_donation') {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;
}

$amount = (float)($_POST['amount'] ?? 0);
if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid donation amount.']);
    exit;
}

// Get user details
$email = '';
$donorName = '';
$userId = null;

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Logged in user
    $userId = (int)$_SESSION['id'];
    $userStmt = $conn->prepare('SELECT email, first_name, last_name, full_name FROM users WHERE id = ? LIMIT 1');
    if ($userStmt) {
        $userStmt->bind_param('i', $userId);
        $userStmt->execute();
        $result = $userStmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $email = $row['email'] ?? '';
            $donorName = trim($row['full_name'] ?? '');
            if (!$donorName) {
                $donorName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            }
        }
        $userStmt->close();
    }
} else {
    // Guest donor
    $email = trim($_POST['email'] ?? '');
    $donorName = trim($_POST['donor_name'] ?? '');

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Valid email address is required.']);
        exit;
    }
}

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email address is required for donation processing.']);
    exit;
}

$message = trim($_POST['message'] ?? '');

// Generate reference and callback URL
$reference = paystack_generate_reference('DON');
$callbackUrl = paystack_page_url('donate.php', ['status' => 'callback', 'reference' => $reference]);

$conn->begin_transaction();

try {
    // Create donations table record
    $donationStmt = $conn->prepare("
        INSERT INTO donations (user_id, donor_email, donor_name, amount, message, transaction_reference, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    if (!$donationStmt) {
        throw new Exception('Unable to prepare donation record: ' . $conn->error);
    }

    $donationStmt->bind_param('issids', $userId, $email, $donorName, $amount, $message, $reference);
    if (!$donationStmt->execute()) {
        throw new Exception('Failed to insert donation record: ' . $donationStmt->error);
    }
    $donationStmt->close();

    // Also create a payment record for tracking
    $paymentStmt = $conn->prepare("
        INSERT INTO payments (user_id, plan_id, order_id, amount, transaction_reference, status, created_at) 
        VALUES (?, NULL, NULL, ?, ?, 'pending', NOW())
    ");
    
    if (!$paymentStmt) {
        throw new Exception('Unable to prepare payment record: ' . $conn->error);
    }

    $paymentStmt->bind_param('ids', $userId, $amount, $reference);
    if (!$paymentStmt->execute()) {
        throw new Exception('Failed to insert payment record: ' . $paymentStmt->error);
    }
    $paymentStmt->close();

    // Initialize Paystack transaction
    $response = paystack_api_request('POST', 'https://api.paystack.co/transaction/initialize', $keys['secret_key'], [
        'email' => $email,
        'amount' => (int)round($amount * 100), // Paystack expects amount in cents
        'reference' => $reference,
        'callback_url' => $callbackUrl,
        'metadata' => [
            'payment_type' => 'donation',
            'user_id' => $userId,
            'donor_email' => $email,
            'donor_name' => $donorName,
            'message' => $message,
        ],
    ]);

    if (empty($response['status']) || $response['status'] !== true || empty($response['data']['authorization_url'])) {
        throw new Exception($response['message'] ?? 'Paystack initialization failed.');
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Donation initialized successfully.',
        'authorization_url' => $response['data']['authorization_url'],
        'reference' => $reference,
    ]);
} catch (Throwable $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Donation initialization failed: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
exit;
?>
