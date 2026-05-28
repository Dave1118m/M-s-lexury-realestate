<?php
/**
 * Hawassa Luxury Real Estate - Database Connection (PDO Singleton)
 */
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->ensureReservationSchema();
        } catch (PDOException $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage(), 0, $e);
        }
    }

    private function ensureReservationSchema() {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS `reservations` (
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
            FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS `payments` (
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
            FOREIGN KEY (`reservation_id`) REFERENCES `reservations`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB");

        $this->ensurePaymentsReservationId();
    }

    private function ensurePaymentsReservationId() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'reservation_id'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $this->pdo->exec("ALTER TABLE `payments` ADD COLUMN `reservation_id` INT NULL AFTER `property_id`");
            // Add the foreign key if it does not already exist.
            $this->pdo->exec("ALTER TABLE `payments` ADD CONSTRAINT `fk_payments_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations`(`id`) ON DELETE SET NULL");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function checkConnection() {
        try {
            self::getInstance();
            return true;
        } catch (RuntimeException $e) {
            return false;
        }
    }

    public function getConnection() {
        return $this->pdo;
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {}
}

// Helper function
function db() {
    return Database::getInstance()->getConnection();
}