CREATE DATABASE IF NOT EXISTS crm_system;
USE crm_system;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS integrations;
DROP TABLE IF EXISTS calls;
DROP TABLE IF EXISTS leads;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'team') DEFAULT 'team',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
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
    SET NULL
);
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
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS integrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    platform ENUM('google_ads', 'facebook', 'custom') NOT NULL,
    webhook_token VARCHAR(64) NOT NULL UNIQUE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    leads_received INT DEFAULT 0,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_webhook_token (webhook_token)
);