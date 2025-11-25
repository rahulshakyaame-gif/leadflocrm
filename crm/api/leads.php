<?php
header('Content-Type: application/json');
require_once 'db.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get single lead
            $stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $lead = $stmt->fetch();

            if ($lead) {
                // Check access
                if ($user_role !== 'admin' && $lead['assigned_to'] != $user_id) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Access denied']);
                    exit;
                }
                echo json_encode($lead);
            } else {
                echo json_encode(['error' => 'Lead not found']);
            }
        } else {
            // List all leads
            $sql = "SELECT leads.*, users.name as assigned_user_name FROM leads LEFT JOIN users ON leads.assigned_to = users.id";
            $params = [];

            if ($user_role !== 'admin') {
                $sql .= " WHERE assigned_to = ?";
                $params[] = $user_id;
            }

            $sql .= " ORDER BY created_at DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        }
        break;

    case 'POST':
        // Create new lead
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['name'], $data['phone'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Name and Phone are required']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, source, status, assigned_to) VALUES (?, ?, ?, ?, ?, ?)");
        $status = $data['status'] ?? 'New';

        // Only admin can assign, otherwise null (unassigned) or maybe assign to self? Let's default to null for now or handle logic.
        // If admin sends assigned_to, use it.
        $assigned_to = ($user_role === 'admin' && isset($data['assigned_to'])) ? $data['assigned_to'] : null;
        // If not admin, maybe they can't assign? Or assign to self? Let's leave as null (unassigned) if not admin for now, or maybe the creator?
        // Requirement says "assign leads to team". Usually admin does this.

        $stmt->execute([$data['name'], $data['email'] ?? '', $data['phone'], $data['source'] ?? '', $status, $assigned_to]);

        echo json_encode(['id' => $pdo->lastInsertId(), 'message' => 'Lead created successfully']);
        break;

    case 'PUT':
        // Update lead
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required']);
            exit;
        }

        // Check permission for this lead
        if ($user_role !== 'admin') {
            $stmt = $pdo->prepare("SELECT assigned_to FROM leads WHERE id = ?");
            $stmt->execute([$data['id']]);
            $lead = $stmt->fetch();
            if (!$lead || $lead['assigned_to'] != $user_id) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                exit;
            }
        }

        $fields = [];
        $values = [];
        foreach (['name', 'email', 'phone', 'source', 'status'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        // Admin can update assignment
        if ($user_role === 'admin' && isset($data['assigned_to'])) {
            $fields[] = "assigned_to = ?";
            $values[] = $data['assigned_to'] ?: null; // Handle empty string as null
        }

        if (empty($fields)) {
            echo json_encode(['message' => 'No changes provided']);
            exit;
        }

        $values[] = $data['id'];
        $sql = "UPDATE leads SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

        echo json_encode(['message' => 'Lead updated successfully']);
        break;

    case 'DELETE':
        // Delete lead
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required']);
            exit;
        }

        if ($user_role !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Only admin can delete leads']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM leads WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['message' => 'Lead deleted successfully']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
