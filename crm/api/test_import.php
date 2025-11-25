<?php
// Simple test endpoint
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

header('Content-Type: application/json');

echo json_encode([
    'test' => 'success',
    'session_exists' => isset($_SESSION['user_id']),
    'user_id' => $_SESSION['user_id'] ?? null,
    'user_role' => $_SESSION['user_role'] ?? null,
    'post_action' => $_POST['action'] ?? null,
    'files_count' => count($_FILES)
]);
