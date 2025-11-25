<?php
require_once 'api/db.php';

$leads = [
    ['John Doe', 'john@example.com', '555-0101', 'Website', 'New'],
    ['Jane Smith', 'jane@company.com', '555-0102', 'Referral', 'Contacted'],
    ['Michael Johnson', 'michael@tech.net', '555-0103', 'Ads', 'Interested'],
    ['Emily Davis', 'emily@design.io', '555-0104', 'Cold Call', 'Qualified'],
    ['Chris Brown', 'chris@marketing.biz', '555-0105', 'Website', 'Lost'],
    ['Sarah Wilson', 'sarah@consulting.com', '555-0106', 'Referral', 'Converted'],
    ['David Miller', 'david@logistics.co', '555-0107', 'Ads', 'New'],
    ['Jessica Taylor', 'jessica@creative.agency', '555-0108', 'Website', 'Contacted'],
    ['Daniel Anderson', 'daniel@finance.group', '555-0109', 'Cold Call', 'Interested'],
    ['Laura Thomas', 'laura@healthcare.org', '555-0110', 'Referral', 'Qualified']
];

try {
    // Seed Users
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Admin User', 'admin@example.com', $password, 'admin']);

    // Seed Leads
    $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, source, status) VALUES (?, ?, ?, ?, ?)");

    foreach ($leads as $lead) {
        $stmt->execute($lead);
    }

    echo "Successfully added admin user and 10 dummy leads.";
} catch (PDOException $e) {
    echo "Error seeding data: " . $e->getMessage();
}
