-- Production Database Setup Script
-- Run this script on your production server to create the database and tables
CREATE DATABASE IF NOT EXISTS crm_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crm_system;
-- Drop existing tables if they exist (be careful in production!)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS integrations;
DROP TABLE IF EXISTS calls;
DROP TABLE IF EXISTS leads;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;
-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'team') DEFAULT 'team',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- Leads table
CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50) NOT NULL,
    source VARCHAR(100),
    status ENUM(
        'New',
        'Contacted',
        'Interested',
        'Qualified',
        'Lost',
        'Converted'
    ) DEFAULT 'New',
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE
    SET NULL,
        INDEX idx_status (status),
        INDEX idx_assigned_to (assigned_to),
        INDEX idx_created_at (created_at),
        INDEX idx_phone (phone),
        INDEX idx_email (email)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- Calls table
CREATE TABLE IF NOT EXISTS calls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    call_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    duration INT COMMENT 'Duration in seconds',
    outcome ENUM(
        'No Answer',
        'Busy',
        'Left Voicemail',
        'Connected',
        'Follow-up Scheduled'
    ) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    INDEX idx_lead_id (lead_id),
    INDEX idx_call_date (call_date),
    INDEX idx_outcome (outcome)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- Integrations table
CREATE TABLE IF NOT EXISTS integrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    platform ENUM('google_ads', 'facebook', 'custom') NOT NULL,
    webhook_token VARCHAR(64) NOT NULL UNIQUE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    leads_received INT DEFAULT 0,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_webhook_token (webhook_token),
    INDEX idx_status (status),
    INDEX idx_platform (platform)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- Create default admin user (password: admin123 - CHANGE THIS IMMEDIATELY!)
-- Password hash for 'admin123'
INSERT INTO users (name, email, password, role)
VALUES (
        'Admin User',
        'admin@example.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin'
    ) ON DUPLICATE KEY
UPDATE email = email;
-- Grant appropriate privileges (run this as root user)
-- GRANT SELECT, INSERT, UPDATE, DELETE ON crm_system.* TO 'your_database_user'@'localhost';
-- FLUSH PRIVILEGES;