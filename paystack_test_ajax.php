<?php
session_start();
header('Content-Type: application/json');

require_once "includes/db_connect.php";
require_once "includes/paystack_gateway.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $reference = trim($_GET['reference'] ?? '');
    $status = trim($_GET['status'] ?? '');

    if ($status === 'callback' || $reference !== '') {
        $redirectUrl = 'paystack_test.php';
        $query = [];

        if ($status !== '') {
            $query['status'] = $status;
        }

        if ($reference !== '') {
            $query['reference'] = $reference;
        }

        if (!empty($_GET['trxref'])) {
            $query['trxref'] = trim($_GET['trxref']);
        }

        if ($query) {
            $redirectUrl .= '?' . http_build_query($query);
        }

        header('Location: ' . $redirectUrl);
        exit;
    }
}

function paystackRequest(string $method, string $url, string $secretKey, ?array $payload = null): array
{
    $ch = curl_init($url);
    $headers = [
        'Authorization: Bearer ' . $secretKey,
        'Content-Type: application/json',
        'Cache-Control: no-cache',
    ];

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 45,
    ];

    if ($payload !== null) {
        $options[CURLOPT_POSTFIELDS] = json_encode($payload);
    }

    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        return [
            'success' => false,
            'message' => $error ?: 'Unable to contact Paystack.',
            'status_code' => $statusCode,
        ];
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        return [
            'success' => false,
            'message' => 'Invalid response from Paystack.',
            'raw' => $response,
            'status_code' => $statusCode,
        ];
    }

    return $decoded + ['status_code' => $statusCode];
}

$action = $_POST['action'] ?? '';
$keys = paystack_get_keys($conn);
$secretKey = $keys['secret_key'];
$publicKey = $keys['public_key'];

if ($action === 'initialize') {
    $userId = (int)$_SESSION['id'];
    $planId = (int)($_POST['plan_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);

    if ($planId <= 0 || $amount <= 0 || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please complete the form with a valid plan, amount, name, and email.']);
        exit;
    }

    if ($phoneNumber !== '' && strlen(preg_replace('/\D+/', '', $phoneNumber)) < 10) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid phone number.']);
        exit;
    }

    if ($amount < 10) {
        echo json_encode(['success' => false, 'message' => 'Test amount must be at least KES 10.']);
        exit;
    }

    $planStmt = $conn->prepare('SELECT id, name, price FROM membership_plans WHERE id = ? LIMIT 1');
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

    $reference = 'PSK-' . strtoupper(bin2hex(random_bytes(6)));
    $callbackUrl = sprintf(
        '%s://%s%s?status=callback&reference=%s',
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http',
        $_SERVER['HTTP_HOST'],
        dirname($_SERVER['SCRIPT_NAME']) . '/paystack_test.php',
        urlencode($reference)
    );

    $conn->begin_transaction();

    try {
        $paymentStmt = $conn->prepare("INSERT INTO payments (user_id, plan_id, amount, phone_number, transaction_reference, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        if (!$paymentStmt) {
            throw new Exception('Unable to prepare payment record.');
        }

        $paymentStmt->bind_param('iidss', $userId, $planId, $amount, $phoneNumber, $reference);
        if (!$paymentStmt->execute()) {
            throw new Exception($paymentStmt->error);
        }
        $paymentStmt->close();

        $payload = [
            'email' => $email,
            'amount' => (int)round($amount * 100),
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'metadata' => [
                'user_id' => $userId,
                'plan_id' => $planId,
                'plan_name' => $plan['name'],
                'plan_price' => (float)$plan['price'],
                'customer_name' => $name,
                'phone_number' => $phoneNumber,
            ],
        ];

        $response = paystackRequest('POST', 'https://api.paystack.co/transaction/initialize', $secretKey, $payload);

        if (empty($response['status']) || $response['status'] !== true || empty($response['data']['authorization_url'])) {
            throw new Exception($response['message'] ?? 'Paystack initialization failed.');
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Payment initialized successfully.',
            'authorization_url' => $response['data']['authorization_url'],
            'access_code' => $response['data']['access_code'] ?? '',
            'reference' => $reference,
            'public_key' => $publicKey,
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

    $response = paystackRequest('GET', 'https://api.paystack.co/transaction/verify/' . rawurlencode($reference), $secretKey);

    if (empty($response['status']) || $response['status'] !== true || empty($response['data']['status'])) {
        echo json_encode(['success' => false, 'message' => $response['message'] ?? 'Unable to verify payment.']);
        exit;
    }

    $paystackStatus = $response['data']['status'];
    $dbStatus = $paystackStatus === 'success' ? 'completed' : ($paystackStatus === 'failed' ? 'failed' : 'pending');

    $updateStmt = $conn->prepare('UPDATE payments SET status = ? WHERE transaction_reference = ? AND user_id = ? LIMIT 1');
    if ($updateStmt) {
        $userId = (int)$_SESSION['id'];
        $updateStmt->bind_param('ssi', $dbStatus, $reference, $userId);
        $updateStmt->execute();
        $updateStmt->close();
    }

    if ($dbStatus === 'completed') {
        echo json_encode(['success' => true, 'message' => 'Payment verified successfully. Your transaction is complete.', 'status' => $dbStatus]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Payment verification returned status: ' . $paystackStatus, 'status' => $dbStatus]);
    }

    $conn->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);