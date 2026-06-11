<?php
require_once __DIR__ . '/../includes/db_connect.php';

// Disable foreign key checks for clean seeding
$conn->query("SET FOREIGN_KEY_CHECKS = 0;");

// Clear existing courses and enrollment data if desired, or we can just upsert.
// Let's check who the admin is or other users. We want to be careful not to delete active users who might be admins/members.
// But coaches are fine to update. Let's delete coaches that we will replace, or insert them using INSERT IGNORE / ON DUPLICATE KEY UPDATE.
// Let's create specific coaches.
$coaches = [
    [
        'username' => 'magnus.carlsen',
        'email' => 'magnus@ascendingpawn.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'first_name' => 'Magnus',
        'last_name' => 'Carlsen',
        'full_name' => 'GM Magnus Carlsen',
        'role' => 'coach',
        'elo_rating' => 2882,
        'phone_number' => '+254711111111',
        'membership_status' => 'active',
        'profile_picture' => 'uploads/coaches/magnus.jpg',
        'bio' => 'Former World Chess Champion, widely considered one of the greatest chess players in history. Known for his deep endgame understanding and flexible style.'
    ],
    [
        'username' => 'judit.polgar',
        'email' => 'judit@ascendingpawn.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'first_name' => 'Judit',
        'last_name' => 'Polgar',
        'full_name' => 'GM Judit Polgár',
        'role' => 'coach',
        'elo_rating' => 2675,
        'phone_number' => '+254722222222',
        'membership_status' => 'active',
        'profile_picture' => 'uploads/coaches/judit.jpg',
        'bio' => 'The strongest female chess player of all time. Famous for her aggressive, sharp tactical play and pioneering contributions to women in chess.'
    ],
    [
        'username' => 'ben.nguku',
        'email' => 'ben@ascendingpawn.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'first_name' => 'Ben',
        'last_name' => 'Nguku',
        'full_name' => 'CM Ben Nguku',
        'role' => 'coach',
        'elo_rating' => 2150,
        'phone_number' => '+254733333333',
        'membership_status' => 'active',
        'profile_picture' => 'uploads/coaches/ben.jpg',
        'bio' => 'FIDE Candidate Master and Kenyan Chess Legend. Multiple-time national team member representing Kenya at international Olympiads.'
    ],
    [
        'username' => 'sasha.cherniaev',
        'email' => 'sasha@ascendingpawn.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'first_name' => 'Sasha',
        'last_name' => 'Cherniaev',
        'full_name' => 'IM Alexander Cherniaev',
        'role' => 'coach',
        'elo_rating' => 2420,
        'phone_number' => '+254744444444',
        'membership_status' => 'active',
        'profile_picture' => 'uploads/coaches/sasha.jpg',
        'bio' => 'International Master and highly experienced FIDE Trainer. Specializes in positional theory, tactical visualization, and middlegame structures.'
    ]
];

// Ensure bio field exists on users table (it doesn't exist in original schema, let's add it!)
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS bio TEXT DEFAULT NULL");

foreach ($coaches as $c) {
    // Check if user exists by username or email
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $c['username'], $c['email']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $id = $row['id'];
        // Update
        $up = $conn->prepare("UPDATE users SET first_name=?, last_name=?, full_name=?, role=?, elo_rating=?, phone_number=?, membership_status=?, profile_picture=?, bio=? WHERE id=?");
        $up->bind_param("ssssissssi", $c['first_name'], $c['last_name'], $c['full_name'], $c['role'], $c['elo_rating'], $c['phone_number'], $c['membership_status'], $c['profile_picture'], $c['bio'], $id);
        $up->execute();
        $up->close();
        echo "Updated coach: " . $c['full_name'] . "\n";
    } else {
        // Insert
        $ins = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, full_name, role, elo_rating, phone_number, membership_status, profile_picture, bio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->bind_param("sssssssissss", $c['username'], $c['email'], $c['password'], $c['first_name'], $c['last_name'], $c['full_name'], $c['role'], $c['elo_rating'], $c['phone_number'], $c['membership_status'], $c['profile_picture'], $c['bio']);
        $ins->execute();
        $ins->close();
        echo "Inserted coach: " . $c['full_name'] . "\n";
    }
    $stmt->close();
}

// Now clear courses and insert rich sample courses matching our levels!
$conn->query("DELETE FROM course_enrollments");
$conn->query("DELETE FROM course_subtopics");
$conn->query("DELETE FROM course_topics");
$conn->query("DELETE FROM academy_courses");
$conn->query("ALTER TABLE academy_courses AUTO_INCREMENT = 1");

// Fetch coach IDs
$coachIds = [];
$res = $conn->query("SELECT id, username FROM users WHERE role = 'coach'");
while ($row = $res->fetch_assoc()) {
    $coachIds[$row['username']] = $row['id'];
}

$courses = [
    [
        'title' => 'Chess Foundations & Basics',
        'description' => 'Master the absolute basics of chess: the board, piece movements, capture rules, special moves (castling, en passant), and fundamental checkmate patterns. Perfect for raw beginners.',
        'coach_id' => $coachIds['ben.nguku'],
        'price' => 0.00,
        'level' => 'beginner',
        'duration' => '4 Weeks'
    ],
    [
        'title' => 'Tactics & Calculation Lab',
        'description' => 'Develop a sharp eye for tactical opportunities. Learn to spot forks, pins, skewers, double attacks, and discovered checks. Train your brain to calculate combinations 3-4 moves deep.',
        'coach_id' => $coachIds['sasha.cherniaev'],
        'price' => 1500.00,
        'level' => 'intermediate',
        'duration' => '6 Weeks'
    ],
    [
        'title' => 'Positional Mastery & Strategy',
        'description' => 'Go beyond tactics. Understand the principles of positional play: space control, pawn structures, outpost utilization, weak square exploitation, and building a plans in the middlegame.',
        'coach_id' => $coachIds['judit.polgar'],
        'price' => 3500.00,
        'level' => 'advanced',
        'duration' => '8 Weeks'
    ],
    [
        'title' => 'Elite Endgame & Tournament Prep',
        'description' => 'Unlock elite status. Study complex endgames (rook vs. minor pieces, opposing pawn chains), learn to optimize clock management, control nerves under pressure, and prepare targeted opening repertoires.',
        'coach_id' => $coachIds['magnus.carlsen'],
        'price' => 5000.00,
        'level' => 'master',
        'duration' => '10 Weeks'
    ]
];

foreach ($courses as $co) {
    $stmt = $conn->prepare("INSERT INTO academy_courses (title, description, coach_id, price, level, duration) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssidss", $co['title'], $co['description'], $co['coach_id'], $co['price'], $co['level'], $co['duration']);
    $stmt->execute();
    $courseId = $stmt->insert_id;
    $stmt->close();
    
    // Add topics for each course
    if ($co['level'] == 'beginner') {
        $topics = [
            ['title' => 'The Chess Battlefield', 'desc' => 'Introduction to the board coordinates, ranks, files, and setup.'],
            ['title' => 'How Pieces Move & Capture', 'desc' => 'Detailed guide on pawns, knights, bishops, rooks, queens, and kings.'],
            ['title' => 'Special Board Actions', 'desc' => 'Mastering castling, pawn promotion, and the tricky en passant.'],
            ['title' => 'First Checkmates', 'desc' => 'Understanding check, checkmate, stalemate, and common basic checkmate patterns.']
        ];
    } else if ($co['level'] == 'intermediate') {
        $topics = [
            ['title' => 'Basic Tactical Motifs', 'desc' => 'Forks, pins, skewers, and double attacks in action.'],
            ['title' => 'Discovered and Double Checks', 'desc' => 'Unleashing powerful hidden attacks on the board.'],
            ['title' => 'Tactical Patterns in Openings', 'desc' => 'Spotting early tactical mistakes in popular opening setups.'],
            ['title' => 'Calculation & Visualization Drills', 'desc' => 'Structured exercises to calculate lines with confidence.']
        ];
    } else if ($co['level'] == 'advanced') {
        $topics = [
            ['title' => 'The Magic of Weak Squares', 'desc' => 'Creating and exploiting weaknesses in the enemy camp.'],
            ['title' => 'Pawn Structures & Major Plans', 'desc' => 'How pawn structure dictates the flow of the middlegame.'],
            ['title' => 'The Art of Prophylaxis', 'desc' => 'Developing the habit of asking "What is my opponent planning?".'],
            ['title' => 'Minor Piece Endgames', 'desc' => 'Bishop vs. Knight dynamics and active piece positioning.']
        ];
    } else {
        $topics = [
            ['title' => 'Theoretical Endgame Mastery', 'desc' => 'Rook endgames, key squares, Lucena, Philidor, and Vancura positions.'],
            ['title' => 'Constructing an Opening Repertoire', 'desc' => 'Customized openings tailored to your playing style.'],
            ['title' => 'Psychology of Tournament Play', 'desc' => 'Handling time trouble, rebound after a loss, and tournament prep.'],
            ['title' => 'Analytical Engine Work', 'desc' => 'How to correctly analyze your games using stockfish and database research.']
        ];
    }
    
    $order = 1;
    foreach ($topics as $tp) {
        $st = $conn->prepare("INSERT INTO course_topics (course_id, title, description, order_number) VALUES (?, ?, ?, ?)");
        $st->bind_param("issi", $courseId, $tp['title'], $tp['desc'], $order);
        $st->execute();
        $topicId = $st->insert_id;
        $st->close();
        
        // Add a subtopic
        $titleSub = $tp['title'] . " - Deep Dive";
        $contentSub = "Detailed study text, examples, and interactive diagrams on: " . $tp['desc'] . ". Make sure to review the diagrams and quiz questions at the end.";
        $videoUrl = "https://www.youtube.com/embed/dQw4w9WgXcQ"; // Sample video
        $orderSub = 1;
        
        $sub = $conn->prepare("INSERT INTO course_subtopics (topic_id, title, content, video_url, order_number) VALUES (?, ?, ?, ?, ?)");
        $sub->bind_param("isssi", $topicId, $titleSub, $contentSub, $videoUrl, $orderSub);
        $sub->execute();
        $sub->close();
        
        $order++;
    }
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1;");
echo "Seeding completed successfully!\n";
