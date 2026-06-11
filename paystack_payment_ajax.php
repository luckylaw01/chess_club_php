<?php
session_start();
header('Content-Type: application/json');

require_once 'includes/db_connect.php';
require_once 'includes/paystack_gateway.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$keys = paystack_get_keys($conn);
if ($keys['secret_key'] === '' || $keys['public_key'] === '') {
    echo json_encode(['success' => false, 'message' => 'Paystack keys are not configured in the database.']);
    exit;
}

function paystack_get_user_details(mysqli $conn, int $userId): array
{
    $stmt = $conn->prepare('SELECT first_name, last_name, full_name, email, phone_number FROM users WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return ['name' => '', 'email' => '', 'phone_number' => ''];
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = ['name' => '', 'email' => '', 'phone_number' => ''];

    if ($result && ($row = $result->fetch_assoc())) {
        $name = trim((string)($row['full_name'] ?? ''));
        if ($name === '') {
            $name = trim(((string)($row['first_name'] ?? '')) . ' ' . ((string)($row['last_name'] ?? '')));
        }

        $user = [
            'name' => $name,
            'email' => (string)($row['email'] ?? ''),
            'phone_number' => (string)($row['phone_number'] ?? ''),
        ];
    }

    $stmt->close();
    return $user;
}

function paystack_update_payment_status(mysqli $conn, string $reference, string $status): void
{
    $stmt = $conn->prepare('UPDATE payments SET status = ? WHERE transaction_reference = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('ss', $status, $reference);
        $stmt->execute();
        $stmt->close();
    }
}

function paystack_mark_membership_paid(mysqli $conn, int $userId, int $planId): void
{
    $stmt = $conn->prepare('SELECT duration_months FROM membership_plans WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return;
    }

    $stmt->bind_param('i', $planId);
    $stmt->execute();
    $result = $stmt->get_result();
    $durationMonths = 1;

    if ($result && ($row = $result->fetch_assoc())) {
        $durationMonths = max(1, (int)($row['duration_months'] ?? 1));
    }

    $stmt->close();

    $renewalDate = date('Y-m-d', strtotime('+' . $durationMonths . ' month'));
    $update = $conn->prepare("UPDATE users SET membership_plan_id = ?, membership_status = 'active', renewal_date = ? WHERE id = ?");
    if ($update) {
        $update->bind_param('isi', $planId, $renewalDate, $userId);
        $update->execute();
        $update->close();
    }
}

function paystack_mark_order_paid(mysqli $conn, int $orderId): void
{
    $stmt = $conn->prepare("UPDATE orders SET status = 'paid' WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $stmt->close();
    }
}

function paystack_mark_tournament_registration_paid(mysqli $conn, int $registrationId): void
{
    $stmt = $conn->prepare("UPDATE tournament_registrations SET payment_status = 'paid', status = 'confirmed' WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $registrationId);
        $stmt->execute();
        $stmt->close();
    }
}

$action = $_POST['action'] ?? '';
$userId = (int)$_SESSION['id'];
$user = paystack_get_user_details($conn, $userId);

if ($action === 'initialize_subscription') {
    $planId = (int)($_POST['plan_id'] ?? 0);
    $phoneNumber = trim($_POST['phone_number'] ?? '');

    if ($planId <= 0 || $phoneNumber === '') {
        echo json_encode(['success' => false, 'message' => 'Please select a plan and enter a phone number.']);
        exit;
    }

    $planStmt = $conn->prepare('SELECT id, name, price, duration_months FROM membership_plans WHERE id = ? LIMIT 1');
    if (!$planStmt) {
        echo json_encode(['success' => false, 'message' => 'Unable to load membership plan.']);
        exit;
    }

    $planStmt->bind_param('i', $planId);
    $planStmt->execute();
    $planResult = $planStmt->get_result();
    $plan = $planResult ? $planResult->fetch_assoc() : null;
    $planStmt->close();

    if (!$plan) {
        echo json_encode(['success' => false, 'message' => 'Selected plan was not found.']);
        exit;
    }

    if ($user['email'] === '') {
        echo json_encode(['success' => false, 'message' => 'User email is required for Paystack checkout.']);
        exit;
    }

    $reference = paystack_generate_reference('SUB');
    $amount = (float)$plan['price'];
    $callbackUrl = paystack_page_url('club.php', ['status' => 'callback', 'reference' => $reference]);

    $conn->begin_transaction();

    try {
        $paymentStmt = $conn->prepare("INSERT INTO payments (user_id, plan_id, order_id, amount, phone_number, transaction_reference, status) VALUES (?, ?, NULL, ?, ?, ?, 'pending')");
        if (!$paymentStmt) {
            throw new Exception('Unable to prepare payment record.');
        }

        $paymentStmt->bind_param('iidss', $userId, $planId, $amount, $phoneNumber, $reference);
        if (!$paymentStmt->execute()) {
            throw new Exception($paymentStmt->error);
        }
        $paymentStmt->close();

        $response = paystack_api_request('POST', 'https://api.paystack.co/transaction/initialize', $keys['secret_key'], [
            'email' => $user['email'],
            'amount' => (int)round($amount * 100),
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'metadata' => [
                'payment_type' => 'subscription',
                'user_id' => $userId,
                'plan_id' => $planId,
                'plan_name' => $plan['name'],
                'duration_months' => (int)$plan['duration_months'],
                'customer_name' => $user['name'],
                'phone_number' => $phoneNumber,
            ],
        ]);

        if (empty($response['status']) || $response['status'] !== true || empty($response['data']['authorization_url'])) {
            throw new Exception($response['message'] ?? 'Paystack initialization failed.');
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Subscription payment initialized successfully.',
            'authorization_url' => $response['data']['authorization_url'],
            'reference' => $reference,
        ]);
    } catch (Throwable $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Initialization failed: ' . $e->getMessage()]);
    }

    $conn->close();
    exit;
}

if ($action === 'initialize_checkout') {
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $cart = $_SESSION['cart'] ?? [];

    if (empty($cart)) {
        echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
        exit;
    }

    if ($phoneNumber === '') {
        echo json_encode(['success' => false, 'message' => 'Please enter a phone number.']);
        exit;
    }

    $ids = array_map('intval', array_keys($cart));
    $idList = implode(',', $ids);
    $query = $conn->query('SELECT * FROM products WHERE id IN (' . $idList . ')');
    if (!$query) {
        echo json_encode(['success' => false, 'message' => 'Unable to load cart products.']);
        exit;
    }

    $cartItems = [];
    $total = 0;
    while ($row = $query->fetch_assoc()) {
        $row['quantity'] = (int)$cart[$row['id']];
        $row['subtotal'] = (float)$row['price'] * $row['quantity'];
        $cartItems[] = $row;
        $total += $row['subtotal'];
    }

    if ($total <= 0) {
        echo json_encode(['success' => false, 'message' => 'Unable to calculate the cart total.']);
        exit;
    }

    if ($user['email'] === '') {
        echo json_encode(['success' => false, 'message' => 'User email is required for Paystack checkout.']);
        exit;
    }

    $reference = paystack_generate_reference('ORD');
    $callbackUrl = paystack_page_url('checkout.php', ['status' => 'callback', 'reference' => $reference]);

    $conn->begin_transaction();

    try {
        $orderStmt = $conn->prepare('INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, \'pending\')');
        if (!$orderStmt) {
            throw new Exception('Unable to prepare order.');
        }

        $orderStmt->bind_param('id', $userId, $total);
        if (!$orderStmt->execute()) {
            throw new Exception($orderStmt->error);
        }
        $orderId = (int)$conn->insert_id;
        $orderStmt->close();

        foreach ($cartItems as $item) {
            $productId = (int)$item['id'];
            $quantity = (int)$item['quantity'];
            $priceAtTime = (float)$item['price'];

            $itemStmt = $conn->prepare('INSERT INTO order_items (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)');
            if (!$itemStmt) {
                throw new Exception('Unable to prepare order item.');
            }

            $itemStmt->bind_param('iiid', $orderId, $productId, $quantity, $priceAtTime);
            if (!$itemStmt->execute()) {
                throw new Exception($itemStmt->error);
            }
            $itemStmt->close();

            $stockStmt = $conn->prepare('UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?');
            if (!$stockStmt) {
                throw new Exception('Unable to update stock.');
            }

            $stockStmt->bind_param('ii', $quantity, $productId);
            if (!$stockStmt->execute()) {
                throw new Exception($stockStmt->error);
            }
            $stockStmt->close();
        }

        $paymentStmt = $conn->prepare("INSERT INTO payments (user_id, plan_id, order_id, amount, phone_number, transaction_reference, status) VALUES (?, NULL, ?, ?, ?, ?, 'pending')");
        if (!$paymentStmt) {
            throw new Exception('Unable to prepare payment record.');
        }

        $paymentStmt->bind_param('iidss', $userId, $orderId, $total, $phoneNumber, $reference);
        if (!$paymentStmt->execute()) {
            throw new Exception($paymentStmt->error);
        }
        $paymentStmt->close();

        $response = paystack_api_request('POST', 'https://api.paystack.co/transaction/initialize', $keys['secret_key'], [
            'email' => $user['email'],
            'amount' => (int)round($total * 100),
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'metadata' => [
                'payment_type' => 'checkout',
                'user_id' => $userId,
                'order_id' => $orderId,
                'phone_number' => $phoneNumber,
                'items_count' => count($cartItems),
                'total_amount' => $total,
                'customer_name' => $user['name'],
            ],
        ]);

        if (empty($response['status']) || $response['status'] !== true || empty($response['data']['authorization_url'])) {
            throw new Exception($response['message'] ?? 'Paystack initialization failed.');
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Checkout payment initialized successfully.',
            'authorization_url' => $response['data']['authorization_url'],
            'reference' => $reference,
        ]);
    } catch (Throwable $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Initialization failed: ' . $e->getMessage()]);
    }

    $conn->close();
    exit;
}

if ($action === 'verify') {
    $reference = trim($_POST['reference'] ?? '');

    if ($reference === '') {
        echo json_encode(['success' => false, 'message' => 'Missing payment reference.']);
        exit;
    }

    $paymentStmt = $conn->prepare('SELECT * FROM payments WHERE transaction_reference = ? LIMIT 1');
    if (!$paymentStmt) {
        echo json_encode(['success' => false, 'message' => 'Unable to load payment record.']);
        exit;
    }

    $paymentStmt->bind_param('s', $reference);
    $paymentStmt->execute();
    $paymentResult = $paymentStmt->get_result();
    $payment = $paymentResult ? $paymentResult->fetch_assoc() : null;
    $paymentStmt->close();

    if (!$payment) {
        echo json_encode(['success' => false, 'message' => 'Payment record not found.']);
        exit;
    }

    $response = paystack_api_request('GET', 'https://api.paystack.co/transaction/verify/' . rawurlencode($reference), $keys['secret_key']);
    if (empty($response['status']) || $response['status'] !== true || empty($response['data']['status'])) {
        echo json_encode(['success' => false, 'message' => $response['message'] ?? 'Unable to verify payment.']);
        exit;
    }

    $paystackStatus = (string)$response['data']['status'];
    $dbStatus = $paystackStatus === 'success' ? 'completed' : ($paystackStatus === 'failed' ? 'failed' : 'pending');

    $conn->begin_transaction();
    try {
        paystack_update_payment_status($conn, $reference, $dbStatus);

        if ($dbStatus === 'completed') {
            if (!empty($payment['plan_id'])) {
                paystack_mark_membership_paid($conn, (int)$payment['user_id'], (int)$payment['plan_id']);
            }

            if (!empty($payment['order_id'])) {
                paystack_mark_order_paid($conn, (int)$payment['order_id']);
                if (isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }
            }

            if (!empty($payment['tournament_registration_id'])) {
                paystack_mark_tournament_registration_paid($conn, (int)$payment['tournament_registration_id']);
            }
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Payment verified successfully. Your transaction is complete.',
            'status' => $dbStatus,
        ]);
    } catch (Throwable $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Verification update failed: ' . $e->getMessage()]);
    }

    $conn->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);