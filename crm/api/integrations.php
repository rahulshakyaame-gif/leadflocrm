<?php
session_start();

require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Only admins can manage integrations']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET - List all integrations
if ($method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM integrations ORDER BY created_at DESC");
        $integrations = $stmt->fetchAll();

        echo json_encode(['success' => true, 'integrations' => $integrations]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch integrations: ' . $e->getMessage()]);
    }
    exit;
}

// POST - Create new integration
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $name = $data['name'] ?? '';
    $platform = $data['platform'] ?? '';

    if (empty($name) || empty($platform)) {
        http_response_code(400);
        echo json_encode(['error' => 'Name and platform are required']);
        exit;
    }

    if (!in_array($platform, ['google_ads', 'facebook', 'custom'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid platform']);
        exit;
    }

    // Generate unique webhook token
    $webhook_token = bin2hex(random_bytes(32));

    try {
        $stmt = $pdo->prepare("INSERT INTO integrations (name, platform, webhook_token) VALUES (?, ?, ?)");
        $stmt->execute([$name, $platform, $webhook_token]);

        $integration_id = $pdo->lastInsertId();

        // Get the created integration
        $stmt = $pdo->prepare("SELECT * FROM integrations WHERE id = ?");
        $stmt->execute([$integration_id]);
        $integration = $stmt->fetch();

        echo json_encode(['success' => true, 'integration' => $integration]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create integration: ' . $e->getMessage()]);
    }
    exit;
}

// DELETE - Remove integration
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? '';

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Integration ID is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM integrations WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete integration: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
