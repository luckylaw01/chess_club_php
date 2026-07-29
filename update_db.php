<?php
// Local configuration for CLI
$host = 'localhost';
$db_name = 'chess_club_db';
$username = 'root';
$password = '';

// Create a mysqli connection
$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add profile_picture column to users table
$sql_add_photo = "ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255) DEFAULT NULL;";
if ($conn->query($sql_add_photo) === TRUE) {
    echo "Column 'profile_picture' checked/added successfully.\n";
} else {
    echo "Error adding column: " . $conn->error . "\n";
}

// Add tournament profile fields to users table
$sql_user_fields = [
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS phone_number VARCHAR(20) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS date_of_birth DATE DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS gender ENUM('male','female','other') DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS club_type ENUM('chess','school') DEFAULT 'chess'",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS club_name VARCHAR(255) DEFAULT NULL",
];

foreach ($sql_user_fields as $sql_user_field) {
    if ($conn->query($sql_user_field) === TRUE) {
        echo "Checked user field: {$sql_user_field}\n";
    } else {
        echo "Error adding user field: " . $conn->error . "\n";
    }
}

// Create the tournament tables before wiring payments to them.
$sql_tournaments_table = "CREATE TABLE IF NOT EXISTS tournaments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    event_date DATETIME NOT NULL,
    location VARCHAR(255) DEFAULT NULL,
    entry_fee DECIMAL(10,2) DEFAULT 0.00,
    team_entry_fee DECIMAL(10,2) DEFAULT NULL,
    prize_pool VARCHAR(100) DEFAULT NULL,
    status ENUM('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql_tournaments_table) === TRUE) {
    echo "Table 'tournaments' created successfully or already exists.\n";
} else {
    echo "Error creating tournaments table: " . $conn->error . "\n";
}

$sql_tournament_registrations = "CREATE TABLE IF NOT EXISTS tournament_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT NOT NULL,
    user_id INT NULL,
    registration_type ENUM('individual', 'team') DEFAULT 'individual',
    team_name VARCHAR(255) DEFAULT NULL,
    full_name VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    declared_participant_count INT(11) DEFAULT NULL,
    participant_count INT(11) DEFAULT 1,
    document_path VARCHAR(255) DEFAULT NULL,
    entry_fee_amount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) DEFAULT 0.00,
    payment_reference VARCHAR(100) DEFAULT NULL,
    payment_status ENUM('pending','paid','failed','cancelled') DEFAULT 'pending',
    status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY (tournament_id),
    KEY (user_id),
    KEY (payment_reference),
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql_tournament_registrations) === TRUE) {
    echo "Table 'tournament_registrations' created successfully or already exists.\n";
} else {
    echo "Error creating tournament registrations table: " . $conn->error . "\n";
}

$sql_tournament_participants = "CREATE TABLE IF NOT EXISTS tournament_registration_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,
    user_id INT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    date_of_birth DATE NULL,
    club_type ENUM('chess', 'school') DEFAULT 'chess',
    club_name VARCHAR(255) DEFAULT NULL,
    gender ENUM('male', 'female', 'other') DEFAULT NULL,
    category VARCHAR(50) DEFAULT 'Open',
    guardian_phone VARCHAR(20) DEFAULT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY (registration_id),
    KEY (user_id),
    FOREIGN KEY (registration_id) REFERENCES tournament_registrations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql_tournament_participants) === TRUE) {
    echo "Table 'tournament_registration_participants' created successfully or already exists.\n";
} else {
    echo "Error creating tournament participants table: " . $conn->error . "\n";
}

// Ensure payments table can store both membership and cart order payments
$sql_payments = "CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    plan_id INT NULL,
    order_id INT NULL,
    tournament_registration_id INT NULL,
    amount DECIMAL(10,2) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    transaction_reference VARCHAR(100) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY (user_id),
    KEY (plan_id),
    KEY (order_id),
    KEY (tournament_registration_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (plan_id) REFERENCES membership_plans(id),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (tournament_registration_id) REFERENCES tournament_registrations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql_payments) === TRUE) {
    echo "Table 'payments' created successfully or already exists.\n";
} else {
    echo "Error creating payments table: " . $conn->error . "\n";
}

$alterUserId = "ALTER TABLE payments MODIFY user_id INT NULL";
if ($conn->query($alterUserId) === TRUE) {
    echo "Column 'user_id' updated to allow NULL.\n";
} else {
    echo "Error updating user_id column: " . $conn->error . "\n";
}

// 4. Create donations table
$sql_donations = "CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    donor_email VARCHAR(255) NOT NULL,
    donor_name VARCHAR(255),
    amount DECIMAL(10, 2) NOT NULL,
    message TEXT,
    transaction_reference VARCHAR(100) UNIQUE NOT NULL,
    status VARCHAR(20) DEFAULT 'pending' NOT NULL,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_reference (transaction_reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql_donations) === TRUE) {
    echo "Table 'donations' created successfully or already exists.\n";
} else {
    echo "Error creating donations table: " . $conn->error . "\n";
}

// 5. Store Paystack keys in database
$sql_settings = "CREATE TABLE IF NOT EXISTS app_settings (
    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql_settings) === TRUE) {
    echo "Table 'app_settings' created successfully or already exists.\n";
} else {
    echo "Error creating app_settings table: " . $conn->error . "\n";
}

// Load Paystack keys from environment variables or configuration file
$paystackSecret = getenv('PAYSTACK_SECRET_KEY') ?: 'YOUR_SECRET_KEY_HERE';
$paystackPublic = getenv('PAYSTACK_PUBLIC_KEY') ?: 'YOUR_PUBLIC_KEY_HERE';
$stmt = $conn->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
if ($stmt) {
    $key = 'paystack_secret_key';
    $value = $paystackSecret;
    $stmt->bind_param('ss', $key, $value);
    $stmt->execute();

    $key = 'paystack_public_key';
    $value = $paystackPublic;
    $stmt->bind_param('ss', $key, $value);
    $stmt->execute();
    $stmt->close();
    echo "Paystack keys saved in app_settings.\n";
} else {
    echo "Error preparing app_settings insert: " . $conn->error . "\n";
}

// Tournament registration foundation tables.
$sql_tournaments = "ALTER TABLE tournaments ADD COLUMN IF NOT EXISTS team_entry_fee DECIMAL(10,2) DEFAULT NULL";
if ($conn->query($sql_tournaments) === TRUE) {
    echo "Tournament pricing column checked/added successfully.\n";
} else {
    echo "Error adding tournament pricing column: " . $conn->error . "\n";
}

$sql_registrations = [
    "ALTER TABLE tournament_registrations ADD COLUMN IF NOT EXISTS registration_type ENUM('individual','team') DEFAULT 'individual'",
    "ALTER TABLE tournament_registrations ADD COLUMN IF NOT EXISTS team_name VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE tournament_registrations ADD COLUMN IF NOT EXISTS declared_participant_count INT(11) DEFAULT NULL",
    "ALTER TABLE tournament_registrations ADD COLUMN IF NOT EXISTS participant_count INT(11) DEFAULT 1",
    "ALTER TABLE tournament_registrations ADD COLUMN IF NOT EXISTS document_path VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE tournament_registrations ADD COLUMN IF NOT EXISTS entry_fee_amount DECIMAL(10,2) DEFAULT 0.00",
    "ALTER TABLE tournament_registrations ADD COLUMN IF NOT EXISTS total_amount DECIMAL(10,2) DEFAULT 0.00",
    "ALTER TABLE tournament_registrations ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(100) DEFAULT NULL",
    "ALTER TABLE tournament_registrations ADD COLUMN IF NOT EXISTS payment_status ENUM('pending','paid','failed','cancelled') DEFAULT 'pending'",
    "ALTER TABLE tournament_registrations ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
];

foreach ($sql_registrations as $sql_registration) {
    if ($conn->query($sql_registration) === TRUE) {
        echo "Checked tournament registration field: {$sql_registration}\n";
    } else {
        echo "Error adding tournament registration field: " . $conn->error . "\n";
    }
}

$sql_participants = "CREATE TABLE IF NOT EXISTS tournament_registration_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,
    user_id INT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    date_of_birth DATE NULL,
    club_type ENUM('chess','school') DEFAULT 'chess',
    club_name VARCHAR(255) DEFAULT NULL,
    gender ENUM('male','female','other') DEFAULT NULL,
    category VARCHAR(50) DEFAULT 'Open',
    guardian_phone VARCHAR(20) DEFAULT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY (registration_id),
    KEY (user_id),
    FOREIGN KEY (registration_id) REFERENCES tournament_registrations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql_participants) === TRUE) {
    echo "Table 'tournament_registration_participants' created successfully or already exists.\n";
} else {
    echo "Error creating tournament participants table: " . $conn->error . "\n";
}

// Alter existing table columns to allow NULL for email and phone
$sql_alter_email = "ALTER TABLE tournament_registration_participants MODIFY email VARCHAR(255) NULL";
if ($conn->query($sql_alter_email) === TRUE) {
    echo "Altered email column in tournament_registration_participants successfully.\n";
} else {
    echo "Error altering email column: " . $conn->error . "\n";
}

$sql_alter_phone = "ALTER TABLE tournament_registration_participants MODIFY phone VARCHAR(20) NULL";
if ($conn->query($sql_alter_phone) === TRUE) {
    echo "Altered phone column in tournament_registration_participants successfully.\n";
} else {
    echo "Error altering phone column: " . $conn->error . "\n";
}

// Ensure foreign key constraints on academy_courses and orders allow deleting user parent rows
@$conn->query("ALTER TABLE academy_courses MODIFY coach_id INT NULL");
@$conn->query("ALTER TABLE academy_courses DROP FOREIGN KEY academy_courses_ibfk_1");
if ($conn->query("ALTER TABLE academy_courses ADD CONSTRAINT academy_courses_ibfk_1 FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE SET NULL")) {
    echo "Updated academy_courses foreign key constraint successfully.\n";
} else {
    echo "Info: academy_courses FK update note: " . $conn->error . "\n";
}

@$conn->query("ALTER TABLE orders MODIFY user_id INT NULL");
@$conn->query("ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_1");
if ($conn->query("ALTER TABLE orders ADD CONSTRAINT orders_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL")) {
    echo "Updated orders foreign key constraint successfully.\n";
} else {
    echo "Info: orders FK update note: " . $conn->error . "\n";
}

$conn->close();
?>