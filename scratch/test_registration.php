<?php
// Test tournament registration with empty optional fields
session_start();

// Mock session
$_SESSION['id'] = 1; // Assuming user ID 1 exists in the database

$type = isset($argv[1]) ? $argv[1] : 'individual';

if ($type === 'individual') {
    $_POST = [
        'tournament_id' => 1, // Assumes tournament ID 1 exists and status is 'upcoming'
        'registration_type' => 'individual',
        'full_name' => 'Individual Test Runner',
        'date_of_birth' => '2000-01-01',
        'gender' => 'male',
        'category' => 'Open'
    ];
} else {
    $_POST = [
        'tournament_id' => 1,
        'registration_type' => 'team',
        'team_name' => 'Test Team Auto',
        'team_contact_name' => 'Team Contact Auto',
        'declared_participant_count' => 1,
        'participants' => [
            [
                'full_name' => 'Team Player One',
                'date_of_birth' => '1995-10-10',
                'gender' => 'female',
                'category' => 'Open',
                'guardian_phone' => '',
                'user_id' => ''
            ]
        ]
    ];
}

$_SERVER['REQUEST_METHOD'] = 'POST';

// Execute
include __DIR__ . '/../register_tournament.php';
