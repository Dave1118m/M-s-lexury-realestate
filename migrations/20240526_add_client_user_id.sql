CREATE TABLE IF NOT EXISTS `chat_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `session_id` VARCHAR(64) NOT NULL,
    `sender_id` INT NULL,
    `agent_id` INT NOT NULL,
    `client_user_id` INT NULL,
    `message` TEXT NOT NULL,
    `is_agent` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add column if table already exists
ALTER TABLE `chat_messages`
    ADD COLUMN `client_user_id` INT NULL AFTER `agent_id`;
