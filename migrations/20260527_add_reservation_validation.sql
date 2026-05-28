-- Migration Script: Add Reservation Validation System
-- Date: May 27, 2026
-- This script adds the necessary tables and constraints for property reservation validation

-- 1. Create reservations table
CREATE TABLE IF NOT EXISTS `reservations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `property_id` INT NOT NULL,
    `reservation_date` DATE NOT NULL,
    `status` ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    `payment_method` VARCHAR(50),
    `transaction_reference` VARCHAR(100),
    `hold_amount` DECIMAL(15,2),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_active_reservation` (`property_id`, `status`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_status` (`user_id`, `status`),
    INDEX `idx_property_status` (`property_id`, `status`),
    INDEX `idx_reservation_date` (`reservation_date`)
) ENGINE=InnoDB;

-- 2. Create saved_properties table if not exists
CREATE TABLE IF NOT EXISTS `saved_properties` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `property_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_saved` (`user_id`, `property_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB;

-- 3. Create saved_searches table if not exists
CREATE TABLE IF NOT EXISTS `saved_searches` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `search_criteria` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB;

-- 4. Create payments table with reservation_id reference
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `property_id` INT NOT NULL,
    `reservation_id` INT,
    `amount` DECIMAL(15,2) NOT NULL,
    `payment_method` VARCHAR(50) NOT NULL,
    `transaction_reference` VARCHAR(100) NOT NULL,
    `status` VARCHAR(20) DEFAULT 'success',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`reservation_id`) REFERENCES `reservations`(`id`) ON DELETE SET NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_property` (`property_id`),
    INDEX `idx_reservation` (`reservation_id`)
) ENGINE=InnoDB;

-- 5. Add indexes for performance
ALTER TABLE `reservations` ADD INDEX `idx_created_at` (`created_at`);
ALTER TABLE `payments` ADD INDEX `idx_created_at` (`created_at`);

-- 6. Verify installation
SELECT 'Reservation Validation System Installed Successfully!' as Status;

-- Show table structure
DESCRIBE reservations;
DESCRIBE saved_properties;
DESCRIBE saved_searches;
DESCRIBE payments;
