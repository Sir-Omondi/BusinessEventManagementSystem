<?php
/**
 * Business Event Management System (BEMS)
 * Configuration File
 * 
 * @author Senior Developer Team
 * @version 1.0.0
 * @date 2026-05-15
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'business_event_system');
define('DB_PORT', 3306);

// Application Configuration
define('APP_NAME', 'Business Event Management System');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'production'); // production | development
define('APP_DEBUG', APP_ENV === 'development');

// Base URLs
define('BASE_URL', 'http://localhost/BusinessEventManagementSystem/');
define('API_URL', BASE_URL . 'api/');

// Security
define('JWT_SECRET', 'your_super_secret_jwt_key_change_this_in_production_2026');
define('JWT_EXPIRATION', 86400); // 24 hours in seconds
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);

// Session Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('REMEMBER_ME_DURATION', 604800); // 7 days

// File Upload Configuration
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('UPLOAD_DIR', __DIR__ . '/../storage/uploads/');
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

// Pagination
define('ITEMS_PER_PAGE', 20);

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
}

// Set default timezone
date_default_timezone_set('UTC');

if (!headers_sent()) {
    // Enable CORS for API requests (guarded to prevent "headers already sent")
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=UTF-8');
}
