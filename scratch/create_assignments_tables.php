<?php
require_once __DIR__ . '/../includes/db_connect.php';

echo "=== CREATING ASSIGNMENTS TABLES ===\n";

$sql1 = "CREATE TABLE IF NOT EXISTS `assignments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `max_points` INT DEFAULT 100,
  `due_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`course_id`) REFERENCES `academy_courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql1) === TRUE) {
    echo "Table 'assignments' checked/created successfully.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$sql2 = "CREATE TABLE IF NOT EXISTS `student_assignments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `assignment_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `submission_text` TEXT DEFAULT NULL,
  `grade` INT DEFAULT NULL,
  `feedback` TEXT DEFAULT NULL,
  `status` ENUM('assigned', 'submitted', 'graded') DEFAULT 'assigned',
  `submitted_at` TIMESTAMP NULL DEFAULT NULL,
  `graded_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql2) === TRUE) {
    echo "Table 'student_assignments' checked/created successfully.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

echo "Database updates complete!\n";
$conn->close();
