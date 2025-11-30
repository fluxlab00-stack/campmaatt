-- Migration: Create comments table
-- Adds an internal comments table for listings and lost & found items

CREATE TABLE IF NOT EXISTS `comments` (
  `comment_id` INT NOT NULL AUTO_INCREMENT,
  `item_type` ENUM('listing','lost_found') NOT NULL,
  `item_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `comment_text` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`),
  INDEX `idx_item` (`item_type`, `item_id`),
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
