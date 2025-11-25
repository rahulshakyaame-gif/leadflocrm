<?php

/**
 * Production Database Configuration
 * 
 * This file uses the config.php settings for database connection
 * Make sure to create config.php from config.example.php
 */

// Load configuration
if (!file_exists(__DIR__ . '/../config.php')) {
    die('Configuration file not found. Please create config.php from config.example.php');
}

require_once __DIR__ . '/../config.php';

try {
    $dsn = sprintf(
        "mysql:host=%s;port=%s;dbname=%s;charset=%s",
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Set session security settings
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', SESSION_HTTPONLY);
        ini_set('session.cookie_secure', SESSION_SECURE);
        ini_set('session.cookie_samesite', SESSION_SAMESITE);
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    }
} catch (PDOException $e) {
    // Log error in production
    if (APP_ENV === 'production') {
        error_log('Database connection failed: ' . $e->getMessage());
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Service temporarily unavailable']);
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
    }
    exit;
}
