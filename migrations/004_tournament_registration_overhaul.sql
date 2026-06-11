-- Tournament registration overhaul migration
-- Preserves existing data and backfills current tournament registrations into participant rows.

START TRANSACTION;

-- 1) Extend users with tournament profile fields.
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS phone_number VARCHAR(20) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS date_of_birth DATE DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS gender ENUM('male', 'female', 'other') DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS club_type ENUM('chess', 'school') DEFAULT 'chess',
    ADD COLUMN IF NOT EXISTS club_name VARCHAR(255) DEFAULT NULL;

-- 2) Allow tournaments to define a separate team fee.
ALTER TABLE tournaments
    ADD COLUMN IF NOT EXISTS team_entry_fee DECIMAL(10,2) DEFAULT NULL;

-- 3) Expand tournament registrations to support individual/team headers.
ALTER TABLE tournament_registrations
    ADD COLUMN IF NOT EXISTS registration_type ENUM('individual', 'team') DEFAULT 'individual',
    ADD COLUMN IF NOT EXISTS team_name VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS declared_participant_count INT(11) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS participant_count INT(11) DEFAULT 1,
    ADD COLUMN IF NOT EXISTS document_path VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS entry_fee_amount DECIMAL(10,2) DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS total_amount DECIMAL(10,2) DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(100) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS payment_status ENUM('pending','paid','failed','cancelled') DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Align legacy columns with the new model where needed.
ALTER TABLE tournament_registrations
    MODIFY COLUMN full_name VARCHAR(255) DEFAULT NULL,
    MODIFY COLUMN email VARCHAR(255) DEFAULT NULL,
    MODIFY COLUMN phone VARCHAR(20) DEFAULT NULL;

-- 4) Create participant rows table for editable team/individual entries.
CREATE TABLE IF NOT EXISTS tournament_registration_participants (
    id INT(11) NOT NULL AUTO_INCREMENT,
    registration_id INT(11) NOT NULL,
    user_id INT(11) DEFAULT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    date_of_birth DATE DEFAULT NULL,
    club_type ENUM('chess', 'school') DEFAULT 'chess',
    club_name VARCHAR(255) DEFAULT NULL,
    gender ENUM('male', 'female', 'other') DEFAULT NULL,
    category VARCHAR(50) DEFAULT 'Open',
    guardian_phone VARCHAR(20) DEFAULT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY registration_id (registration_id),
    KEY user_id (user_id),
    CONSTRAINT tournament_registration_participants_ibfk_1
        FOREIGN KEY (registration_id) REFERENCES tournament_registrations (id) ON DELETE CASCADE,
    CONSTRAINT tournament_registration_participants_ibfk_2
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5) Keep existing registrations usable by marking them as individual registrations.
UPDATE tournament_registrations
SET
    registration_type = COALESCE(registration_type, 'individual'),
    participant_count = COALESCE(participant_count, 1),
    declared_participant_count = COALESCE(declared_participant_count, 1),
    entry_fee_amount = COALESCE(entry_fee_amount, 0.00),
    total_amount = COALESCE(total_amount, 0.00),
    payment_status = COALESCE(payment_status, 'pending'),
    status = COALESCE(status, 'pending')
WHERE registration_type IS NULL
   OR participant_count IS NULL
   OR declared_participant_count IS NULL
   OR entry_fee_amount IS NULL
   OR total_amount IS NULL
   OR payment_status IS NULL;

-- 6) Backfill one participant row per existing registration.
INSERT INTO tournament_registration_participants (
    registration_id,
    user_id,
    full_name,
    email,
    phone,
    date_of_birth,
    club_type,
    club_name,
    gender,
    category,
    guardian_phone,
    is_primary,
    created_at,
    updated_at
)
SELECT
    r.id,
    r.user_id,
    COALESCE(NULLIF(r.full_name, ''), u.full_name, NULLIF(TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))), '')),
    COALESCE(NULLIF(r.email, ''), u.email),
    COALESCE(NULLIF(r.phone, ''), u.phone_number),
    u.date_of_birth,
    COALESCE(u.club_type, 'chess'),
    u.club_name,
    u.gender,
    COALESCE(r.category, 'Open'),
    NULL,
    1,
    r.registration_date,
    COALESCE(r.updated_at, r.registration_date)
FROM tournament_registrations r
LEFT JOIN users u ON u.id = r.user_id
WHERE NOT EXISTS (
    SELECT 1
    FROM tournament_registration_participants p
    WHERE p.registration_id = r.id
);

-- 7) Add the team payment link to payments.
ALTER TABLE payments
    ADD COLUMN IF NOT EXISTS tournament_registration_id INT(11) DEFAULT NULL,
    ADD KEY tournament_registration_id (tournament_registration_id);

ALTER TABLE payments
    ADD CONSTRAINT payments_ibfk_4
        FOREIGN KEY (tournament_registration_id) REFERENCES tournament_registrations (id) ON DELETE SET NULL;

COMMIT;
