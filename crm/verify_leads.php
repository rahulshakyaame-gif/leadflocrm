<?php
require_once 'api/db.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM leads");
    $count = $stmt->fetchColumn();
    echo "Total leads in database: " . $count . "\n";

    $stmt = $pdo->query("SELECT name, email FROM leads LIMIT 5");
    echo "First 5 leads:\n";
    while ($row = $stmt->fetch()) {
        echo "- " . $row['name'] . " (" . $row['email'] . ")\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
