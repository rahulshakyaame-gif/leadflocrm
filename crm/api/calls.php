<?php
header('Content-Type: application/json');
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['lead_id'])) {
            // Get calls for a specific lead
            $stmt = $pdo->prepare("SELECT * FROM calls WHERE lead_id = ? ORDER BY call_date DESC");
            $stmt->execute([$_GET['lead_id']]);
            echo json_encode($stmt->fetchAll());
        } else {
            // Get all calls (optional, maybe for dashboard)
            $stmt = $pdo->query("SELECT c.*, l.name as lead_name FROM calls c JOIN leads l ON c.lead_id = l.id ORDER BY c.call_date DESC LIMIT 50");
            echo json_encode($stmt->fetchAll());
        }
        break;

    case 'POST':
        // Log a new call
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['lead_id'], $data['outcome'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Lead ID and Outcome are required']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO calls (lead_id, duration, outcome, notes) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['lead_id'],
            $data['duration'] ?? 0,
            $data['outcome'],
            $data['notes'] ?? ''
        ]);

        echo json_encode(['id' => $pdo->lastInsertId(), 'message' => 'Call logged successfully']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
