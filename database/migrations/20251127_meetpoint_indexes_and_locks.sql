-- Migration: Ensure meetpoint_suggestions has indexes and description column

ALTER TABLE `meetpoint_suggestions`
  ADD COLUMN IF NOT EXISTS `description` TEXT NULL AFTER `meet_point_name`;

-- Add indexes to speed lookups
CREATE INDEX IF NOT EXISTS idx_meetpoint_suggestions_sender_status ON meetpoint_suggestions (sender_id, status(10));
CREATE INDEX IF NOT EXISTS idx_meetpoint_suggestions_chat_status ON meetpoint_suggestions (chat_id, status(10));

-- Note: MySQL/MariaDB may not allow IF NOT EXISTS for ADD COLUMN in older versions; run with caution.
-- These statements are intended as guidance; run them in your local DB client adjusted for your server version.
