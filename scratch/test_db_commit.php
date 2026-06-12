<?php
// Integration test to verify registration commit in database
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

// 3. Define a helper function to run registration and print output
function test_register($type) {
    global $conn;
    $_SESSION['id'] = 1;
    $_SERVER['REQUEST_METHOD'] = 'POST';

    if ($type === 'individual') {
        $_POST = [
            'tournament_id' => 1,
            'registration_type' => 'individual',
            'full_name' => 'DB Commit Indiv Test',
            'date_of_birth' => '2000-01-01',
            'gender' => 'male',
            'category' => 'Open'
        ];
    } else {
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
    }

    ob_start();
    include __DIR__ . '/../register_tournament.php';
    $output = ob_get_clean();
    return json_decode($output, true);
}

// Run tests
echo "\nRunning Individual Registration...\n";
$indivRes = test_register('individual');
print_r($indivRes);

// Clean up individual test registrations from database to avoid cluttering
if (isset($indivRes['status']) && $indivRes['status'] === 'success') {
    $regId = $indivRes['registration_id'];
    $conn->query("DELETE FROM tournament_registration_participants WHERE registration_id = $regId");
    $conn->query("DELETE FROM tournament_registrations WHERE id = $regId");
    echo "Cleaned up individual test registration record (ID: $regId) from database.\n";
}

// Reset session to mock team registration in a separate run
// (Since we can't easily re-include the file due to redeclared function, we will exit and let the wrapper call it for team)
// Wait! To run both in the same file, we can execute team test in a separate PHP CLI process!
// That's much cleaner. Let's do that!

// 4. Restore original tournament fees
$conn->query("UPDATE tournaments SET entry_fee = $origEntry, team_entry_fee = " . ($origTeam !== null ? $origTeam : "NULL") . " WHERE id = 1");
echo "\nRestored tournament 1 fees to original values.\n";
