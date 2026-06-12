<?php
// Integration test to verify team registration commit in database
session_start();

require_once __DIR__ . '/../includes/db_connect.php';

// 1. Store original entry fee of tournament 1
$res = $conn->query("SELECT entry_fee, team_entry_fee FROM tournaments WHERE id = 1");
$original = $res->fetch_assoc();
$origEntry = $original['entry_fee'];
$origTeam = $original['team_entry_fee'];

// 2. Temporarily set fees to 0 to bypass Paystack payment gateway call
$conn->query("UPDATE tournaments SET entry_fee = 0, team_entry_fee = 0 WHERE id = 1");
echo "Temporarily updated tournament 1 fees to 0.\n";

// 3. Mock POST data for team registration
$_SESSION['id'] = 1;
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'tournament_id' => 1,
    'registration_type' => 'team',
    'team_name' => 'DB Commit Team Test',
    'team_contact_name' => 'Team Contact DB',
    'declared_participant_count' => 1,
    'participants' => [
        [
            'full_name' => 'DB Team Player 1',
            'date_of_birth' => '1995-10-10',
            'gender' => 'female',
            'category' => 'Open',
            'guardian_phone' => '',
            'user_id' => ''
        ]
    ]
];

// Execute team registration
ob_start();
include __DIR__ . '/../register_tournament.php';
$output = ob_get_clean();

echo "\nRunning Team Registration...\n";
$teamRes = json_decode($output, true);
print_r($teamRes);

// Clean up team test registrations from database
if (isset($teamRes['status']) && $teamRes['status'] === 'success') {
    $regId = $teamRes['registration_id'];
    $conn->query("DELETE FROM tournament_registration_participants WHERE registration_id = $regId");
    $conn->query("DELETE FROM tournament_registrations WHERE id = $regId");
    echo "Cleaned up team test registration record (ID: $regId) from database.\n";
}

// 4. Restore original tournament fees
$conn->query("UPDATE tournaments SET entry_fee = $origEntry, team_entry_fee = " . ($origTeam !== null ? $origTeam : "NULL") . " WHERE id = 1");
echo "\nRestored tournament 1 fees to original values.\n";
