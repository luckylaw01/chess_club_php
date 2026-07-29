-- Migration: Fix user deletion foreign key constraints for academy_courses and orders
-- Date: 2026-07-29
-- Description: Allows coach_id in academy_courses and user_id in orders to be set to NULL when a user is deleted, preventing FK deletion errors.

-- 1. Update academy_courses table to allow NULL coach_id and update FK constraint to ON DELETE SET NULL
ALTER TABLE `academy_courses` MODIFY `coach_id` INT(11) DEFAULT NULL;
ALTER TABLE `academy_courses` DROP FOREIGN KEY `academy_courses_ibfk_1`;
ALTER TABLE `academy_courses` ADD CONSTRAINT `academy_courses_ibfk_1` FOREIGN KEY (`coach_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- 2. Update orders table to allow NULL user_id and update FK constraint to ON DELETE SET NULL
ALTER TABLE `orders` MODIFY `user_id` INT(11) DEFAULT NULL;
ALTER TABLE `orders` DROP FOREIGN KEY `orders_ibfk_1`;
ALTER TABLE `orders` ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
