<?php

/**
 * Configuration File - Example
 * 
 * Copy this file to config.php and update with your production values
 * NEVER commit config.php to version control
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');  // Change from 3307 (XAMPP) to 3306 (standard MySQL)
define('DB_NAME', 'crm_system');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_secure_password');
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_ENV', 'production'); // production, staging, development
define('APP_DEBUG', false); // Set to false in production
define('APP_URL', 'https://yourdomain.com'); // Your production URL

// Security Settings
define('SESSION_LIFETIME', 3600); // Session timeout in seconds (1 hour)
define('SESSION_SECURE', true); // Set to true if using HTTPS
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Strict');

// Error Reporting
if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/php-error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Timezone
date_default_timezone_set('Asia/Kolkata'); // Adjust to your timezone

// CORS Settings (if needed for API access)
define('CORS_ALLOWED_ORIGINS', ''); // Comma-separated list of allowed origins, empty for same-origin only

// File Upload Settings
define('MAX_UPLOAD_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_FILE_TYPES', 'csv,txt');

// Webhook Security
define('WEBHOOK_RATE_LIMIT', 100); // Max requests per minute per token
