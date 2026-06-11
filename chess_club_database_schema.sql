-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2026 at 06:29 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chess_club_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `academy_courses`
--

CREATE TABLE `academy_courses` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `coach_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `level` enum('beginner','intermediate','advanced','master') DEFAULT 'beginner',
  `duration` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academy_courses`
--

INSERT INTO `academy_courses` (`id`, `title`, `description`, `coach_id`, `price`, `level`, `duration`, `created_at`) VALUES
(1, 'Chess Foundations & Basics', 'Master the absolute basics of chess: the board, piece movements, capture rules, special moves (castling, en passant), and fundamental checkmate patterns. Perfect for raw beginners.', 7, 0.00, 'beginner', '4 Weeks', '2026-05-26 12:57:49'),
(2, 'Tactics & Calculation Lab', 'Develop a sharp eye for tactical opportunities. Learn to spot forks, pins, skewers, double attacks, and discovered checks. Train your brain to calculate combinations 3-4 moves deep.', 8, 1500.00, 'intermediate', '6 Weeks', '2026-05-26 12:57:49'),
(3, 'Positional Mastery & Strategy', 'Go beyond tactics. Understand the principles of positional play: space control, pawn structures, outpost utilization, weak square exploitation, and building a plans in the middlegame.', 6, 3500.00, 'advanced', '8 Weeks', '2026-05-26 12:57:49'),
(4, 'Elite Endgame & Tournament Prep', 'Unlock elite status. Study complex endgames (rook vs. minor pieces, opposing pawn chains), learn to optimize clock management, control nerves under pressure, and prepare targeted opening repertoires.', 3, 5000.00, 'master', '10 Weeks', '2026-05-26 12:57:49');

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `app_settings`
--

INSERT INTO `app_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('paystack_public_key', 'pk_live_placeholder', '2026-05-13 10:23:04'),
('paystack_secret_key', 'sk_live_placeholder', '2026-05-13 10:23:04');

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `max_points` int(11) DEFAULT 100,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_enrollments`
--

CREATE TABLE `course_enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','completed','dropped') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_enrollments`
--

INSERT INTO `course_enrollments` (`id`, `user_id`, `course_id`, `enrolled_at`, `status`) VALUES
(2, 2, 2, '2026-05-26 13:01:42', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `course_subtopics`
--

CREATE TABLE `course_subtopics` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `order_number` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_subtopics`
--

INSERT INTO `course_subtopics` (`id`, `topic_id`, `title`, `content`, `video_url`, `order_number`) VALUES
(1, 6, 'The Chess Battlefield - Deep Dive', 'Detailed study text, examples, and interactive diagrams on: Introduction to the board coordinates, ranks, files, and setup.. Make sure to review the diagrams and quiz questions at the end.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1),
(2, 7, 'How Pieces Move & Capture - Deep Dive', 'Detailed study text, examples, and interactive diagrams on: Detailed guide on pawns, knights, bishops, rooks, queens, and kings.. Make sure to review the diagrams and quiz questions at the end.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1),
(3, 8, 'Special Board Actions - Deep Dive', 'Detailed study text, examples, and interactive diagrams on: Mastering castling, pawn promotion, and the tricky en passant.. Make sure to review the diagrams and quiz questions at the end.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1),
(4, 9, 'First Checkmates - Deep Dive', 'Detailed study text, examples, and interactive diagrams on: Understanding check, checkmate, stalemate, and common basic checkmate patterns.. Make sure to review the diagrams and quiz questions at the end.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1),
(5, 10, 'Basic Tactical Motifs - Deep Dive', 'Detailed study text, examples, and interactive diagrams on: Forks, pins, skewers, and double attacks in action.. Make sure to review the diagrams and quiz questions at the end.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1),
(6, 11, 'Discovered and Double Checks - Deep Dive', 'Detailed study text, examples, and interactive diagrams on: Unleashing powerful hidden attacks on the board.. Make sure to review the diagrams and quiz questions at the end.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1),
(7, 12, 'Tactical Patterns in Openings - Deep Dive', 'Detailed study text, examples, and interactive diagrams on: Spotting early tactical mistakes in popular opening setups.. Make sure to review the diagrams and quiz questions at the end.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1),
(8, 13, 'Calculation & Visualization Drills - Deep Dive', 'Detailed study text, examples, and interactive diagrams on: Structured exercises to calculate lines with confidence.. Make sure to review the diagrams and quiz questions at the end.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1),
(9, 14, 'The Magic of Weak Squares - Deep Dive', 'Detailed study text, examples, and interactive diagrams on: Creating and exploiting weaknesses in the enemy camp.. Make sure to review the diagrams and quiz questions at the end.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1),
(10, 15, 'Pawn Structures & Major Plans - Deep Dive', 'Detailed study text, examples, and interactive diagrams on: How pawn structure dictates the flow of the middlegame.. Make sure to review the diagrams and quiz questions at the end.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1),
(11, 16, 'The Art of Prophylaxis - Deep Dive', 'Detailed study text, examples, and interactive diagrams on: Developing the habit of asking \"What is my opponent planning?\".. Make sure to review the diagrams and quiz questions at the end.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1),
(12, 17, 'Minor Piece Endgames - Deep Dive', 'Detailed study text, examples, and interactive diagrams on: Bishop vs. Knight dynamics and active piece positioning.. Make sure to review the diagrams and quiz questions at the end.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1),
(13, 18, 'Theoretical Endgame Mastery - Deep Dive', 'Detailed study text, examples, and interactive diagrams on: Rook endgames, key squares, Lucena, Philidor, and Vancura positions.. Make sure to review the diagrams and quiz questions at the end.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1),
(14, 19, 'Constructing an Opening Repertoire - Deep Dive', 'Detailed study text, examples, and interactive diagrams on: Customized openings tailored to your playing style.. Make sure to review the diagrams and quiz questions at the end.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1),
(15, 20, 'Psychology of Tournament Play - Deep Dive', 'Detailed study text, examples, and interactive diagrams on: Handling time trouble, rebound after a loss, and tournament prep.. Make sure to review the diagrams and quiz questions at the end.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1),
(16, 21, 'Analytical Engine Work - Deep Dive', 'Detailed study text, examples, and interactive diagrams on: How to correctly analyze your games using stockfish and database research.. Make sure to review the diagrams and quiz questions at the end.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1);

-- --------------------------------------------------------

--
-- Table structure for table `course_topics`
--

CREATE TABLE `course_topics` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `order_number` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_topics`
--

INSERT INTO `course_topics` (`id`, `course_id`, `title`, `description`, `order_number`) VALUES
(6, 1, 'The Chess Battlefield', 'Introduction to the board coordinates, ranks, files, and setup.', 1),
(7, 1, 'How Pieces Move & Capture', 'Detailed guide on pawns, knights, bishops, rooks, queens, and kings.', 2),
(8, 1, 'Special Board Actions', 'Mastering castling, pawn promotion, and the tricky en passant.', 3),
(9, 1, 'First Checkmates', 'Understanding check, checkmate, stalemate, and common basic checkmate patterns.', 4),
(10, 2, 'Basic Tactical Motifs', 'Forks, pins, skewers, and double attacks in action.', 1),
(11, 2, 'Discovered and Double Checks', 'Unleashing powerful hidden attacks on the board.', 2),
(12, 2, 'Tactical Patterns in Openings', 'Spotting early tactical mistakes in popular opening setups.', 3),
(13, 2, 'Calculation & Visualization Drills', 'Structured exercises to calculate lines with confidence.', 4),
(14, 3, 'The Magic of Weak Squares', 'Creating and exploiting weaknesses in the enemy camp.', 1),
(15, 3, 'Pawn Structures & Major Plans', 'How pawn structure dictates the flow of the middlegame.', 2),
(16, 3, 'The Art of Prophylaxis', 'Developing the habit of asking \"What is my opponent planning?\".', 3),
(17, 3, 'Minor Piece Endgames', 'Bishop vs. Knight dynamics and active piece positioning.', 4),
(18, 4, 'Theoretical Endgame Mastery', 'Rook endgames, key squares, Lucena, Philidor, and Vancura positions.', 1),
(19, 4, 'Constructing an Opening Repertoire', 'Customized openings tailored to your playing style.', 2),
(20, 4, 'Psychology of Tournament Play', 'Handling time trouble, rebound after a loss, and tournament prep.', 3),
(21, 4, 'Analytical Engine Work', 'How to correctly analyze your games using stockfish and database research.', 4);

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `donor_email` varchar(255) NOT NULL,
  `donor_name` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `message` text DEFAULT NULL,
  `transaction_reference` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `user_id`, `donor_email`, `donor_name`, `amount`, `message`, `transaction_reference`, `status`, `verified_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'munyakalawrence01@gmail.com', 'Lawrence Wanjohi', 500.00, '456', 'DON-25BBD04B740D', 'pending', NULL, '2026-05-16 13:23:16', '2026-05-16 13:23:16');

-- --------------------------------------------------------

--
-- Table structure for table `mailing_list`
--

CREATE TABLE `mailing_list` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mailing_list`
--

INSERT INTO `mailing_list` (`id`, `email`, `name`, `subscribed_at`) VALUES
(1, 'munyakalawrence01@gmail.com', 'Lawrence Munyaka', '2026-04-27 12:05:38');

-- --------------------------------------------------------

--
-- Table structure for table `membership_plans`
--

CREATE TABLE `membership_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_months` int(11) DEFAULT 1,
  `features` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `membership_plans`
--

INSERT INTO `membership_plans` (`id`, `name`, `description`, `price`, `duration_months`, `features`, `created_at`) VALUES
(1, 'Pro Pawn', 'For serious competitive players.', 2500.00, 1, 'Weekly Club Nights, Official FIDE Rating, Free Tournament Entry', '2026-03-25 14:43:06'),
(2, 'Master Knight', 'Full academy + club experience.', 5500.00, 1, 'All Pro Pawn Benefits, 2 Private Coaching Sessions, Exclusive Masterclasses', '2026-03-25 14:43:06'),
(3, 'Basic Bishop', 'For casual players and beginners.', 1500.00, 1, 'Monthly Club Nights, Community Access, Casual Matchmaking', '2026-03-25 14:43:06');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `content_id`, `is_read`, `read_at`, `created_at`) VALUES
(1, 1, 1, 1, '2026-04-22 16:25:45', '2026-04-22 16:24:07'),
(2, 3, 2, 0, NULL, '2026-04-27 11:21:36'),
(3, 2, 3, 1, '2026-04-27 11:37:26', '2026-04-27 11:22:37'),
(4, 1, 4, 0, NULL, '2026-05-27 15:35:21'),
(5, 2, 4, 1, '2026-05-27 15:35:32', '2026-05-27 15:35:21'),
(6, 4, 4, 0, NULL, '2026-05-27 15:35:21'),
(7, 5, 4, 0, NULL, '2026-05-27 15:35:21'),
(8, 6, 4, 0, NULL, '2026-05-27 15:35:21'),
(9, 7, 4, 0, NULL, '2026-05-27 15:35:21'),
(10, 8, 4, 0, NULL, '2026-05-27 15:35:21'),
(11, 3, 4, 0, NULL, '2026-05-27 15:35:21');

-- --------------------------------------------------------

--
-- Table structure for table `notification_content`
--

CREATE TABLE `notification_content` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('system','announcement','promotion','alert') DEFAULT 'system',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_content`
--

INSERT INTO `notification_content` (`id`, `title`, `message`, `type`, `created_by`, `created_at`) VALUES
(1, 'Hello sir', 'welcome to ascending Pawn', 'system', 2, '2026-04-22 16:24:07'),
(2, 'Hello sir', 'Testing', 'system', 2, '2026-04-27 11:21:36'),
(3, 'Contact Form: Greats gdgv', '<strong>From:</strong> Anderson Kimani (kimanianthony@gmail.com)<br><br>All the best testing', 'alert', NULL, '2026-04-27 11:22:37'),
(4, 'Hello, Welcome to seed.', 'der', 'system', 2, '2026-05-27 15:35:21');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','shipped','delivered','cancelled') DEFAULT 'pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `order_date`) VALUES
(1, 1, 145.00, 'pending', '2026-04-11 10:09:57');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_time` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `transaction_reference` varchar(100) NOT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tournament_registration_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `plan_id`, `order_id`, `amount`, `phone_number`, `transaction_reference`, `status`, `created_at`, `tournament_registration_id`) VALUES
(1, 3, 1, NULL, 2500.00, '254789545666', 'RFGC6B570F', 'completed', '2026-03-27 05:45:46', NULL),
(3, 1, 3, NULL, 1500.00, '0745493943', 'PSK-A965CA46E9A3', 'pending', '2026-05-12 10:50:17', NULL),
(4, 1, 3, NULL, 1500.00, '0745493943', 'PSK-B669B3F541AB', 'pending', '2026-05-12 11:00:12', NULL),
(5, 1, 3, NULL, 1500.00, '0745493943', 'PSK-39A2F04F26B2', 'completed', '2026-05-12 11:02:37', NULL),
(6, 1, 3, NULL, 10.00, '0745493943', 'PSK-EA38FA2965CC', 'completed', '2026-05-13 10:09:23', NULL),
(7, 1, 3, NULL, 10.00, '0745493943', 'PSK-D9D18D942561', 'completed', '2026-05-13 10:25:26', NULL),
(8, 1, NULL, NULL, 500.00, '', 'DON-25BBD04B740D', 'pending', '2026-05-16 13:23:16', NULL),
(9, 5, NULL, NULL, 1000.00, '0789546231', 'TRN-8737608130FF', 'pending', '2026-05-21 11:57:23', 5);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `category` varchar(100) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock_quantity`, `category`, `image_url`, `created_at`) VALUES
(1, 'Professional Tournament Set', 'Weighted plastic pieces with a roll-up vinyl board.', 3500.00, 25, 'Apparel', 'chess_set.png', '2026-04-11 12:43:30'),
(2, 'Luxury Wooden Set', 'Hand-carved Staunton pieces in premium rosewood.', 12500.00, 5, 'Sets', 'slotted_board.png', '2026-04-11 12:43:30'),
(3, 'Pocket Travel Set', 'Magnetic pieces for on-the-go analysis.', 1500.00, 50, 'Sets', 'magnetic_board.png', '2026-04-11 12:43:30'),
(4, 'DGT 2010 Digital Clock', 'Official FIDE tournament clock with delay/increment.', 8500.00, 12, 'Clocks', 'chess_clock.png', '2026-04-11 12:43:30'),
(5, 'Analog Wooden Clock', 'Classic mechanical ticking clock for blitz.', 4500.00, 8, 'Clocks', 'chess_clock.png', '2026-04-11 12:43:30'),
(6, 'My 60 Memorable Games', 'Bobby Fischer classic strategy book.', 2200.00, 15, 'Books', 'scorebook.png', '2026-04-11 12:43:30'),
(7, 'Modern Chess Strategy', 'Comprehensive guide to middle-game concepts.', 2800.00, 3, 'Books', 'scorebook.png', '2026-04-11 12:43:30'),
(8, 'Club Hoodie', 'Warm hoodie with embroidered club logo.', 3000.00, 20, 'Apparel', '1776263138_Cover.jpg', '2026-04-11 12:43:30');

-- --------------------------------------------------------

--
-- Table structure for table `student_assignments`
--

CREATE TABLE `student_assignments` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `submission_text` text DEFAULT NULL,
  `grade` int(11) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `status` enum('assigned','submitted','graded') DEFAULT 'assigned',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `graded_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `entry_fee` decimal(10,2) DEFAULT 0.00,
  `prize_pool` varchar(100) DEFAULT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `team_entry_fee` decimal(10,2) DEFAULT NULL,
  `poster_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tournaments`
--

INSERT INTO `tournaments` (`id`, `title`, `description`, `event_date`, `location`, `entry_fee`, `prize_pool`, `status`, `created_at`, `team_entry_fee`, `poster_url`) VALUES
(1, 'Tournament 1', 'This is the best chess tournament ever', '2026-03-31 06:32:00', 'Sagana', 150.00, '20000', 'upcoming', '2026-03-27 06:33:27', 4500.00, NULL),
(2, 'Adrenaline Rush 101', 'EXIT THE PAWN RACE', '2026-03-26 03:42:00', 'Nairobi', 1200.00, '1200', 'completed', '2026-03-27 06:43:04', 14999.99, 'uploads/tournaments/1779897761_IMG_20260515_110908.jpg'),
(3, 'Ringgg', 'Enjoyment top', '2026-03-28 10:06:00', 'Nairobi', 500.00, '12000', 'upcoming', '2026-03-27 07:06:40', NULL, NULL),
(4, 'Jm Kilo', 'ki ki ki', '2026-03-31 07:14:00', 'Nyeri', 1000.00, '12000', 'upcoming', '2026-03-27 07:15:13', 14999.96, 'uploads/tournaments/1779898293_IMG_20260518_132728.jpg'),
(5, 'KLO', 'Trat jdbb kdj klkf', '2026-03-30 10:23:00', 'Karatina', 1200.00, '20000', 'upcoming', '2026-03-27 07:23:34', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tournament_registrations`
--

CREATE TABLE `tournament_registrations` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'Open',
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `registration_type` enum('individual','team') DEFAULT 'individual',
  `team_name` varchar(255) DEFAULT NULL,
  `declared_participant_count` int(11) DEFAULT NULL,
  `participant_count` int(11) DEFAULT 1,
  `document_path` varchar(255) DEFAULT NULL,
  `entry_fee_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `payment_reference` varchar(100) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','cancelled') DEFAULT 'pending',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tournament_registrations`
--

INSERT INTO `tournament_registrations` (`id`, `tournament_id`, `user_id`, `full_name`, `email`, `phone`, `category`, `registration_date`, `status`, `registration_type`, `team_name`, `declared_participant_count`, `participant_count`, `document_path`, `entry_fee_amount`, `total_amount`, `payment_reference`, `payment_status`, `updated_at`) VALUES
(1, 1, NULL, 'Ashbake fajaji', 'munyakalawrence01@gmail.com', '0745493943', 'Open', '2026-03-27 07:03:12', 'pending', 'individual', NULL, 1, 1, NULL, 0.00, 0.00, NULL, 'pending', '2026-05-21 10:23:36'),
(2, 3, NULL, 'Charles Mwaki', 'munyakalawrence01@gmail.com', '0745493943', 'Open', '2026-03-27 07:07:13', 'pending', 'individual', NULL, 1, 1, NULL, 0.00, 0.00, NULL, 'pending', '2026-05-21 10:23:36'),
(3, 4, 1, 'Lawrence Munyaka', 'munyakalawrence01@gmail.com', '0745493943', 'Open', '2026-03-27 07:15:34', 'pending', 'individual', NULL, 1, 1, NULL, 0.00, 0.00, NULL, 'pending', '2026-05-21 10:23:36'),
(4, 5, 1, 'Salman Khan', 'munyakalawrence01@gmail.com', '0745493943', 'Open', '2026-03-27 07:27:15', 'pending', 'individual', NULL, 1, 1, NULL, 0.00, 0.00, NULL, 'pending', '2026-05-21 10:23:36'),
(5, 1, 5, 'Calvin Munyao', 'munyaocalvin@gmail.com', '0789546231', 'Open', '2026-05-21 11:57:23', 'pending', 'individual', '', 1, 1, NULL, 1000.00, 1000.00, 'TRN-8737608130FF', 'pending', '2026-05-21 11:57:23'),
(6, 3, NULL, 'Lucky Law', 'luckylaw95@gmail.com', '0745493943', 'Open', '2026-05-21 12:10:47', 'confirmed', 'individual', NULL, NULL, 1, NULL, 0.00, 0.00, NULL, 'paid', '2026-05-21 12:10:47');

-- --------------------------------------------------------

--
-- Table structure for table `tournament_registration_participants`
--

CREATE TABLE `tournament_registration_participants` (
  `id` int(11) NOT NULL,
  `registration_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `club_type` enum('chess','school') DEFAULT 'chess',
  `club_name` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `category` varchar(50) DEFAULT 'Open',
  `guardian_phone` varchar(20) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tournament_registration_participants`
--

INSERT INTO `tournament_registration_participants` (`id`, `registration_id`, `user_id`, `full_name`, `email`, `phone`, `date_of_birth`, `club_type`, `club_name`, `gender`, `category`, `guardian_phone`, `is_primary`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Ashbake fajaji', 'munyakalawrence01@gmail.com', '0745493943', NULL, 'chess', NULL, NULL, 'Open', NULL, 1, '2026-03-27 07:03:12', '2026-05-21 10:23:36'),
(2, 2, NULL, 'Charles Mwaki', 'munyakalawrence01@gmail.com', '0745493943', '2002-05-21', 'chess', 'Ascending Pawn', 'male', 'Open', '0789888564555', 1, '2026-03-27 07:07:13', '2026-05-21 12:10:37'),
(3, 3, 1, 'Lawrence Munyaka', 'munyakalawrence01@gmail.com', '0745493943', NULL, 'chess', NULL, NULL, 'Open', NULL, 1, '2026-03-27 07:15:34', '2026-05-21 10:23:36'),
(4, 4, 1, 'Salman Khan', 'munyakalawrence01@gmail.com', '0745493943', NULL, 'chess', NULL, NULL, 'Open', NULL, 1, '2026-03-27 07:27:15', '2026-05-21 10:23:36'),
(8, 5, 5, 'Calvin Munyao', 'munyaocalvin@gmail.com', '0789546231', '2002-06-21', 'chess', 'MTTI Chess', 'male', 'Under 7', '', 1, '2026-05-21 11:57:23', '2026-05-21 11:57:23'),
(9, 6, NULL, 'Lucky Law', 'luckylaw95@gmail.com', '0745493943', NULL, 'chess', NULL, NULL, 'Under 7', NULL, 1, '2026-05-21 12:10:47', '2026-05-21 12:10:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `elo_rating` int(11) DEFAULT 1200,
  `membership_plan_id` int(11) DEFAULT NULL,
  `membership_status` enum('active','inactive','expired') DEFAULT 'inactive',
  `member_since` date DEFAULT NULL,
  `renewal_date` date DEFAULT NULL,
  `global_rank` int(11) DEFAULT NULL,
  `achievements_count` int(11) DEFAULT 0,
  `role` enum('user','admin','coach') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `club_type` enum('chess','school') DEFAULT 'chess',
  `club_name` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `full_name`, `elo_rating`, `membership_plan_id`, `membership_status`, `member_since`, `renewal_date`, `global_rank`, `achievements_count`, `role`, `created_at`, `profile_picture`, `phone_number`, `date_of_birth`, `gender`, `club_type`, `club_name`, `bio`) VALUES
(1, 'lawrence.wanjohi', 'munyakalawrence01@gmail.com', '$2y$10$P92ks9x1/cFodQi4cjieSeys9SrwLtWq5ZIysn3EGKc2gmI8VBaea', 'Lawrence', 'Wanjohi', 'Lawrence Wanjohi', 1200, NULL, 'inactive', NULL, NULL, NULL, 0, 'user', '2026-03-25 16:35:34', 'uploads/profile_pictures/user_1_1777987184.jpg', NULL, NULL, NULL, 'chess', NULL, NULL),
(2, 'admin', 'admin@ascendingpawn.co.ke', '$2y$10$bQHH47/pU7Fuz5HCXL.u6uMkAZN0bPAHZn8M4PsQiGfQGZMz7W.ny', NULL, NULL, 'System Administrator', 1200, NULL, 'active', NULL, NULL, NULL, 0, 'admin', '2026-03-26 07:15:15', NULL, NULL, NULL, NULL, 'chess', NULL, NULL),
(3, 'magnus.carlsen', 'magnus@chess.com', '$2y$10$tRkP30KUuOzZhTSZGYhfCOQxaOaU74tGX3Gy5XaEYzCH0vmmm8XbK', 'Magnus', 'Carlsen', 'GM Magnus Carlsen', 2882, 1, 'active', NULL, '2026-04-27', NULL, 0, 'coach', '2026-03-26 09:38:27', 'uploads/coaches/magnus.jpg', '+254711111111', NULL, NULL, 'chess', NULL, 'Former World Chess Champion, widely considered one of the greatest chess players in history. Known for his deep endgame understanding and flexible style.'),
(4, 'marriam.wambui', 'wambuimarriam@gmail.com', '$2y$10$gMbg3a7B82J7oa5E4AWAI.2iUqYy1bM9EAwGFfd7TwtfW/e1NhMvi', 'Marriam', 'Wambui', NULL, 1212, NULL, 'active', NULL, NULL, NULL, 0, 'user', '2026-04-22 14:47:35', NULL, NULL, NULL, NULL, 'chess', NULL, NULL),
(5, 'calvin.munyao', 'munyaocalvin@gmail.com', '$2y$10$.cBqoiFPaZjPe1jlhzeM1uAPReu.up7BMLpe75NkPDFaPGwksDinK', 'Calvin', 'Munyao', 'Calvin Munyao', 1200, NULL, 'inactive', NULL, NULL, NULL, 0, 'user', '2026-05-21 11:56:15', NULL, '0789546231', '2002-06-21', 'male', 'chess', 'MTTI Chess', NULL),
(6, 'judit.polgar', 'judit@ascendingpawn.com', '$2y$10$/DOrBIi8B9aEPvTLqD9xLeuO1XcnjtvBwVfNmnRN3n1FBfXKR9pVC', 'Judit', 'Polgar', 'GM Judit Polgár', 2675, NULL, 'active', NULL, NULL, NULL, 0, 'coach', '2026-05-26 12:57:49', 'uploads/coaches/judit.jpg', '+254722222222', NULL, NULL, 'chess', NULL, 'The strongest female chess player of all time. Famous for her aggressive, sharp tactical play and pioneering contributions to women in chess.'),
(7, 'ben.nguku', 'ben@ascendingpawn.com', '$2y$10$ydczjH3lzLKfEmBw4eVX/u5pPsslSHvCgeZUsYMBBt0F73wweMDri', 'Ben', 'Nguku', 'CM Ben Nguku', 2150, NULL, 'active', NULL, NULL, NULL, 0, 'coach', '2026-05-26 12:57:49', 'uploads/coaches/ben.jpg', '+254733333333', NULL, NULL, 'chess', NULL, 'FIDE Candidate Master and Kenyan Chess Legend. Multiple-time national team member representing Kenya at international Olympiads.'),
(8, 'sasha.cherniaev', 'sasha@ascendingpawn.com', '$2y$10$XFLOIJcX7gHmKAw/ddXLUuUwg8UKw22pAIWw0kXcDc1UPQZDxMrlW', 'Sasha', 'Cherniaev', 'IM Alexander Cherniaev', 2420, NULL, 'active', NULL, NULL, NULL, 0, 'coach', '2026-05-26 12:57:49', 'uploads/coaches/sasha.jpg', '+254744444444', NULL, NULL, 'chess', NULL, 'International Master and highly experienced FIDE Trainer. Specializes in positional theory, tactical visualization, and middlegame structures.');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academy_courses`
--
ALTER TABLE `academy_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coach_id` (`coach_id`);

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `course_subtopics`
--
ALTER TABLE `course_subtopics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `course_topics`
--
ALTER TABLE `course_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_reference` (`transaction_reference`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_reference` (`transaction_reference`),
  ADD KEY `idx_email` (`donor_email`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `donations_ibfk_1` (`user_id`);

--
-- Indexes for table `mailing_list`
--
ALTER TABLE `mailing_list`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `membership_plans`
--
ALTER TABLE `membership_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notification_user` (`user_id`),
  ADD KEY `fk_notification_content` (`content_id`);

--
-- Indexes for table `notification_content`
--
ALTER TABLE `notification_content`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notification_content_author` (`created_by`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `payments_ibfk_1` (`user_id`),
  ADD KEY `tournament_registration_id` (`tournament_registration_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_assignments`
--
ALTER TABLE `student_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tournament_registrations`
--
ALTER TABLE `tournament_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_id` (`tournament_id`);

--
-- Indexes for table `tournament_registration_participants`
--
ALTER TABLE `tournament_registration_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registration_id` (`registration_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `membership_plan_id` (`membership_plan_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academy_courses`
--
ALTER TABLE `academy_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `course_subtopics`
--
ALTER TABLE `course_subtopics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `course_topics`
--
ALTER TABLE `course_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mailing_list`
--
ALTER TABLE `mailing_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `membership_plans`
--
ALTER TABLE `membership_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `notification_content`
--
ALTER TABLE `notification_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `student_assignments`
--
ALTER TABLE `student_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tournament_registrations`
--
ALTER TABLE `tournament_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tournament_registration_participants`
--
ALTER TABLE `tournament_registration_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academy_courses`
--
ALTER TABLE `academy_courses`
  ADD CONSTRAINT `academy_courses_ibfk_1` FOREIGN KEY (`coach_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `academy_courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD CONSTRAINT `course_enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `academy_courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_subtopics`
--
ALTER TABLE `course_subtopics`
  ADD CONSTRAINT `course_subtopics_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `course_topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_topics`
--
ALTER TABLE `course_topics`
  ADD CONSTRAINT `course_topics_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `academy_courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notification_content` FOREIGN KEY (`content_id`) REFERENCES `notification_content` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_content`
--
ALTER TABLE `notification_content`
  ADD CONSTRAINT `fk_notification_content_author` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `membership_plans` (`id`),
  ADD CONSTRAINT `payments_ibfk_4` FOREIGN KEY (`tournament_registration_id`) REFERENCES `tournament_registrations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_assignments`
--
ALTER TABLE `student_assignments`
  ADD CONSTRAINT `student_assignments_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_assignments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tournament_registrations`
--
ALTER TABLE `tournament_registrations`
  ADD CONSTRAINT `tournament_registrations_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tournament_registration_participants`
--
ALTER TABLE `tournament_registration_participants`
  ADD CONSTRAINT `tournament_registration_participants_ibfk_1` FOREIGN KEY (`registration_id`) REFERENCES `tournament_registrations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tournament_registration_participants_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`membership_plan_id`) REFERENCES `membership_plans` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
