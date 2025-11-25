<?php

/**
 * Admin User Creation Script
 * Usage: php create_admin.php <email> <password> [name]
 */

if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.\n");
}

require_once 'api/db.php';

// Parse arguments
$email = $argv[1] ?? null;
$password = $argv[2] ?? null;
$name = $argv[3] ?? 'Admin User';

if (!$email || !$password) {
    echo "Usage: php create_admin.php <email> <password> [name]\n";
    echo "Example: php create_admin.php admin@example.com password123 \"John Admin\"\n";
    exit(1);
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Error: Invalid email address.\n";
    exit(1);
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        echo "Error: User with email '$email' already exists.\n";
        exit(1);
    }

    // Create admin user
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->execute([$name, $email, $hashed_password]);

    echo "✓ Admin user created successfully!\n";
    echo "  Email: $email\n";
    echo "  Name: $name\n";
    echo "  Role: admin\n";
    echo "\nYou can now log in with these credentials.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
