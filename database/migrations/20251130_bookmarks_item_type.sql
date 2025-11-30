-- Migration: Add item_type/item_id to bookmarks so bookmarks can refer to listings or lost_found
ALTER TABLE `bookmarks`
  MODIFY `listing_id` int(11) DEFAULT NULL;

ALTER TABLE `bookmarks`
  ADD COLUMN `item_type` ENUM('listing','lost_found') NOT NULL DEFAULT 'listing' AFTER `listing_id`,
  ADD COLUMN `item_id` int(11) DEFAULT NULL AFTER `item_type`;

-- Optional unique constraint to prevent duplicate bookmarks per user/item-type
ALTER TABLE `bookmarks`
  ADD UNIQUE KEY `unique_bookmark_type` (`user_id`,`item_type`,`item_id`);
