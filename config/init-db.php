<?php
/**
 * Database initialization script
 * Run this once to create all necessary tables
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core.php';

$db = Database::getInstance()->getConnection();

// SQL statements to create tables
$statements = [
    // Users table
    "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        company VARCHAR(255),
        role ENUM('admin', 'organizer', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_role (role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Events table
    "CREATE TABLE IF NOT EXISTS events (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description LONGTEXT,
        location VARCHAR(255),
        start_date DATETIME NOT NULL,
        end_date DATETIME,
        max_attendees INT DEFAULT 100,
        organizer_id INT NOT NULL,
        status ENUM('scheduled', 'ongoing', 'completed', 'cancelled') DEFAULT 'scheduled',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_status (status),
        INDEX idx_start_date (start_date),
        FULLTEXT INDEX ft_title_desc (title, description)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Attendees table
    "CREATE TABLE IF NOT EXISTS attendees (
        id INT PRIMARY KEY AUTO_INCREMENT,
        event_id INT NOT NULL,
        user_id INT NOT NULL,
        registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        check_in_time DATETIME,
        check_out_time DATETIME,
        status ENUM('registered', 'checked_in', 'checked_out', 'cancelled') DEFAULT 'registered',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_registration (event_id, user_id),
        INDEX idx_status (status),
        INDEX idx_event_id (event_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Audit log table
    "CREATE TABLE IF NOT EXISTS audit_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        action VARCHAR(255),
        table_name VARCHAR(255),
        record_id INT,
        old_values JSON,
        new_values JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

// Execute each statement
foreach ($statements as $statement) {
    try {
        if ($db->query($statement)) {
            echo "✓ Table created/verified successfully\n";
        } else {
            echo "✗ Error: " . $db->error . "\n";
        }
    } catch (Exception $e) {
        echo "✗ Exception: " . $e->getMessage() . "\n";
    }
}

// Create a default admin user.
$defaultAdmin = "INSERT IGNORE INTO users (name, email, password, phone, company, role) 
                 VALUES ('Admin User', 'admin@bems.local', '" . password_hash('admin@123', PASSWORD_BCRYPT) . "', '0712345678', 'BEMS', 'admin')";

if ($db->query($defaultAdmin)) {
    echo "✓ Default admin user created (if not exists)\n";
}

echo "\n✓ Database initialization completed successfully!\n";
echo "✓ Default admin credentials:\n";
echo "   Email: admin@bems.local\n";
echo "   Password: admin@123\n";
