<?php
header('Content-Type: application/json');
session_start();

require_once 'includes/db_connect.php';
require_once 'includes/paystack_gateway.php';

function tournament_json_response(array $payload): void
{
    echo json_encode($payload);
    exit;
}

function tournament_uploaded_document_path(array $file): ?string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedExtensions = ['pdf', 'xls', 'xlsx'];
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension'] ?? '');

    if (!in_array($extension, $allowedExtensions, true)) {
        tournament_json_response(['status' => 'error', 'message' => 'Only PDF, XLS, and XLSX files are allowed.']);
    }

    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'tournament_documents';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $safeName = 'tournament_doc_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = $uploadDir . DIRECTORY_SEPARATOR . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        tournament_json_response(['status' => 'error', 'message' => 'Unable to upload the supporting document.']);
    }

    return 'uploads/tournament_documents/' . $safeName;
}

function tournament_update_user_profile(mysqli $conn, int $userId, array $payload): void
{
    $stmt = $conn->prepare('UPDATE users SET full_name = COALESCE(NULLIF(?, ""), full_name), email = COALESCE(NULLIF(?, ""), email), phone_number = COALESCE(NULLIF(?, ""), phone_number), date_of_birth = COALESCE(NULLIF(?, ""), date_of_birth), gender = COALESCE(NULLIF(?, ""), gender), club_type = COALESCE(NULLIF(?, ""), club_type), club_name = COALESCE(NULLIF(?, ""), club_name) WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return;
    }

    $fullName = trim((string)($payload['full_name'] ?? ''));
    $email = trim((string)($payload['email'] ?? ''));
    $phone = trim((string)($payload['phone'] ?? ''));
    $dateOfBirth = trim((string)($payload['date_of_birth'] ?? ''));
    $gender = trim((string)($payload['gender'] ?? ''));
    $clubType = trim((string)($payload['club_type'] ?? ''));
    $clubName = trim((string)($payload['club_name'] ?? ''));

    $stmt->bind_param('sssssssi', $fullName, $email, $phone, $dateOfBirth, $gender, $clubType, $clubName, $userId);
    $stmt->execute();
    $stmt->close();
}

function tournament_calculate_age(?string $dateOfBirth, string $eventDate): ?int
{
    if ($dateOfBirth === null || $dateOfBirth === '') {
        return null;
    }

    try {
        $birth = new DateTime($dateOfBirth);
        $event = new DateTime($eventDate);
        return (int)$birth->diff($event)->y;
    } catch (Throwable $e) {
        return null;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    tournament_json_response(['status' => 'error', 'message' => 'Invalid request method.']);
}

$tournamentId = (int)($_POST['tournament_id'] ?? 0);
$registrationType = strtolower(trim((string)($_POST['registration_type'] ?? 'individual')));
$teamName = trim((string)($_POST['team_name'] ?? ''));
$teamContactName = trim((string)($_POST['team_contact_name'] ?? ''));
$teamContactEmail = trim((string)($_POST['team_contact_email'] ?? ''));
$teamContactPhone = trim((string)($_POST['team_contact_phone'] ?? ''));
$declaredParticipantCount = (int)($_POST['declared_participant_count'] ?? 0);
$participants = $_POST['participants'] ?? [];
$userId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : null;

if ($tournamentId <= 0) {
    tournament_json_response(['status' => 'error', 'message' => 'Tournament not found.']);
}

$stmt = $conn->prepare('SELECT id, title, event_date, status, entry_fee, team_entry_fee FROM tournaments WHERE id = ? LIMIT 1');
if (!$stmt) {
    tournament_json_response(['status' => 'error', 'message' => 'Unable to load tournament.']);
}

$stmt->bind_param('i', $tournamentId);
$stmt->execute();
$result = $stmt->get_result();
$tournament = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$tournament) {
    tournament_json_response(['status' => 'error', 'message' => 'Tournament not found.']);
}

if (($tournament['status'] ?? '') !== 'upcoming') {
    tournament_json_response(['status' => 'error', 'message' => 'Registration is closed for this tournament.']);
}

$eventDate = (string)$tournament['event_date'];
$registrationRows = [];
$entryFee = (float)($tournament['entry_fee'] ?? 0);
$teamEntryFee = isset($tournament['team_entry_fee']) && $tournament['team_entry_fee'] !== null ? (float)$tournament['team_entry_fee'] : 0.0;
$documentPath = null;

if (isset($_FILES['supporting_document']) && ($_FILES['supporting_document']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    $documentPath = tournament_uploaded_document_path($_FILES['supporting_document']);
}

if ($registrationType === 'team') {
    if ($teamName === '') {
        tournament_json_response(['status' => 'error', 'message' => 'Please enter a team name.']);
    }

    if (empty($participants) || !is_array($participants)) {
        tournament_json_response(['status' => 'error', 'message' => 'Please add at least one participant.']);
    }

    foreach ($participants as $index => $participant) {
        $fullName = trim((string)($participant['full_name'] ?? ''));
        $email = trim((string)($participant['email'] ?? ''));
        $phone = trim((string)($participant['phone'] ?? ''));
        $dateOfBirth = trim((string)($participant['date_of_birth'] ?? ''));
        $clubType = trim((string)($participant['club_type'] ?? 'chess'));
        $clubName = trim((string)($participant['club_name'] ?? ''));
        $gender = trim((string)($participant['gender'] ?? ''));
        $category = trim((string)($participant['category'] ?? 'Open'));
        $guardianPhone = trim((string)($participant['guardian_phone'] ?? ''));
        $participantUserId = !empty($participant['user_id']) ? (int)$participant['user_id'] : null;

        if ($fullName === '' || $email === '' || $phone === '' || $dateOfBirth === '' || $clubName === '' || $gender === '' || $category === '') {
            tournament_json_response(['status' => 'error', 'message' => 'Please complete all participant details for the team registration.']);
        }

        $age = tournament_calculate_age($dateOfBirth, $eventDate);
        if ($age !== null && $age < 18 && $guardianPhone === '') {
            tournament_json_response(['status' => 'error', 'message' => 'Guardian phone number is required for participants under 18.']);
        }

        $registrationRows[] = [
            'user_id' => $participantUserId,
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'date_of_birth' => $dateOfBirth,
            'club_type' => $clubType !== '' ? $clubType : 'chess',
            'club_name' => $clubName,
            'gender' => $gender,
            'category' => $category,
            'guardian_phone' => $guardianPhone,
            'is_primary' => $index === 0 ? 1 : 0,
        ];
    }

    if ($userId !== null) {
        tournament_update_user_profile($conn, $userId, [
            'full_name' => $teamContactName,
            'email' => $teamContactEmail,
            'phone' => $teamContactPhone,
        ]);
    }
} else {
    if ($userId === null) {
        tournament_json_response(['status' => 'error', 'message' => 'Please log in to register individually.']);
    }

    $stmt = $conn->prepare('SELECT full_name, email, phone_number, date_of_birth, gender, club_type, club_name FROM users WHERE id = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $userResult = $stmt->get_result();
        $user = $userResult ? $userResult->fetch_assoc() : null;
        $stmt->close();
    } else {
        $user = null;
    }

    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $dateOfBirth = trim((string)($_POST['date_of_birth'] ?? ''));
    $clubType = trim((string)($_POST['club_type'] ?? 'chess'));
    $clubName = trim((string)($_POST['club_name'] ?? ''));
    $gender = trim((string)($_POST['gender'] ?? ''));
    $category = trim((string)($_POST['category'] ?? 'Open'));
    $guardianPhone = trim((string)($_POST['guardian_phone'] ?? ''));

    if ($fullName === '' || $email === '' || $phone === '' || $dateOfBirth === '' || $clubName === '' || $gender === '' || $category === '') {
        tournament_json_response(['status' => 'error', 'message' => 'Please complete all required fields.']);
    }

    $age = tournament_calculate_age($dateOfBirth, $eventDate);
    if ($age !== null && $age < 18 && $guardianPhone === '') {
        tournament_json_response(['status' => 'error', 'message' => 'Guardian phone number is required for participants under 18.']);
    }

    $registrationRows[] = [
        'user_id' => $userId,
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'date_of_birth' => $dateOfBirth,
        'club_type' => $clubType !== '' ? $clubType : 'chess',
        'club_name' => $clubName,
        'gender' => $gender,
        'category' => $category,
        'guardian_phone' => $guardianPhone,
        'is_primary' => 1,
    ];

    if ($userId !== null) {
        tournament_update_user_profile($conn, $userId, [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'date_of_birth' => $dateOfBirth,
            'gender' => $gender,
            'club_type' => $clubType,
            'club_name' => $clubName,
        ]);
    }
}

$participantCount = count($registrationRows);
$declaredParticipantCount = $declaredParticipantCount > 0 ? $declaredParticipantCount : $participantCount;
$entryFeeAmount = $registrationType === 'team' ? ($teamEntryFee > 0 ? $teamEntryFee : $entryFee) : $entryFee;
$totalAmount = $registrationType === 'team' ? ($teamEntryFee > 0 ? $teamEntryFee : ($entryFee * max(1, $participantCount))) : $entryFeeAmount;
$paymentStatus = $totalAmount > 0 ? 'pending' : 'paid';
$registrationStatus = $totalAmount > 0 ? 'pending' : 'confirmed';
$paymentReference = $totalAmount > 0 ? paystack_generate_reference('TRN') : null;

$conn->begin_transaction();

try {
    $registrationStmt = $conn->prepare('INSERT INTO tournament_registrations (tournament_id, user_id, registration_type, team_name, full_name, email, phone, declared_participant_count, participant_count, document_path, entry_fee_amount, total_amount, payment_reference, payment_status, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$registrationStmt) {
        throw new Exception('Unable to prepare tournament registration.');
    }

    $primaryRow = $registrationRows[0];
    $registrationStmt->bind_param(
        'iisssssiisddsss',
        $tournamentId,
        $userId,
        $registrationType,
        $teamName,
        $primaryRow['full_name'],
        $primaryRow['email'],
        $primaryRow['phone'],
        $declaredParticipantCount,
        $participantCount,
        $documentPath,
        $entryFeeAmount,
        $totalAmount,
        $paymentReference,
        $paymentStatus,
        $registrationStatus
    );

    if (!$registrationStmt->execute()) {
        throw new Exception($registrationStmt->error);
    }

    $registrationId = (int)$conn->insert_id;
    $registrationStmt->close();

    $participantStmt = $conn->prepare('INSERT INTO tournament_registration_participants (registration_id, user_id, full_name, email, phone, date_of_birth, club_type, club_name, gender, category, guardian_phone, is_primary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$participantStmt) {
        throw new Exception('Unable to prepare participant records.');
    }

    foreach ($registrationRows as $row) {
        $participantUserId = $row['user_id'];
        $participantStmt->bind_param(
            'iisssssssssi',
            $registrationId,
            $participantUserId,
            $row['full_name'],
            $row['email'],
            $row['phone'],
            $row['date_of_birth'],
            $row['club_type'],
            $row['club_name'],
            $row['gender'],
            $row['category'],
            $row['guardian_phone'],
            $row['is_primary']
        );

        if (!$participantStmt->execute()) {
            throw new Exception($participantStmt->error);
        }
    }
    $participantStmt->close();

    if ($totalAmount > 0) {
        $paymentStmt = $conn->prepare("INSERT INTO payments (user_id, plan_id, order_id, tournament_registration_id, amount, phone_number, transaction_reference, status) VALUES (?, NULL, NULL, ?, ?, ?, ?, 'pending')");
        if (!$paymentStmt) {
            throw new Exception('Unable to prepare payment record.');
        }

        $primaryPhone = $registrationType === 'team' ? ($teamContactPhone !== '' ? $teamContactPhone : $primaryRow['phone']) : $primaryRow['phone'];
        $paymentStmt->bind_param('iidss', $userId, $registrationId, $totalAmount, $primaryPhone, $paymentReference);
        if (!$paymentStmt->execute()) {
            throw new Exception($paymentStmt->error);
        }
        $paymentStmt->close();

        $keys = paystack_get_keys($conn);
        if ($keys['secret_key'] === '' || $keys['public_key'] === '') {
            throw new Exception('Paystack keys are not configured in the database.');
        }

        $payerEmail = $registrationType === 'team' ? ($teamContactEmail !== '' ? $teamContactEmail : $primaryRow['email']) : $primaryRow['email'];
        $payerName = $registrationType === 'team' ? ($teamContactName !== '' ? $teamContactName : $teamName) : $primaryRow['full_name'];

        $callbackUrl = paystack_page_url('tournaments.php', ['status' => 'callback', 'reference' => $paymentReference]);
        $response = paystack_api_request('POST', 'https://api.paystack.co/transaction/initialize', $keys['secret_key'], [
            'email' => $payerEmail,
            'amount' => (int)round($totalAmount * 100),
            'reference' => $paymentReference,
            'callback_url' => $callbackUrl,
            'metadata' => [
                'payment_type' => 'tournament',
                'tournament_id' => $tournamentId,
                'tournament_registration_id' => $registrationId,
                'registration_type' => $registrationType,
                'team_name' => $teamName,
                'participant_count' => $participantCount,
                'customer_name' => $payerName,
                'phone_number' => $primaryPhone,
            ],
        ]);

        if (empty($response['status']) || $response['status'] !== true || empty($response['data']['authorization_url'])) {
            throw new Exception($response['message'] ?? 'Paystack initialization failed.');
        }

        $conn->commit();

        tournament_json_response([
            'status' => 'success',
            'message' => 'Registration saved successfully. Complete payment to confirm your slot.',
            'requires_payment' => true,
            'authorization_url' => $response['data']['authorization_url'],
            'reference' => $paymentReference,
            'registration_id' => $registrationId,
        ]);
    }

    $conn->commit();

    tournament_json_response([
        'status' => 'success',
        'message' => 'Registration successful. Your slot has been confirmed.',
        'requires_payment' => false,
        'registration_id' => $registrationId,
    ]);
} catch (Throwable $e) {
    $conn->rollback();
    tournament_json_response(['status' => 'error', 'message' => 'Registration failed: ' . $e->getMessage()]);
}
