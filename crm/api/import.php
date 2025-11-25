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
    echo json_encode(['error' => 'Only admins can import leads']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $action = $data['action'] ?? '';

    if ($action === 'import') {
        if (!isset($data['mapping']) || !isset($data['rows'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing mapping or rows data']);
            exit;
        }

        $mapping = $data['mapping'];
        $rows = $data['rows'];

        if (!isset($mapping['name']) || !isset($mapping['phone'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Name and Phone fields are required']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, source, status, assigned_to) VALUES (?, ?, ?, ?, ?, ?)");

            $imported = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                $name = $row[$mapping['name']] ?? '';
                $phone = $row[$mapping['phone']] ?? '';
                $email = isset($mapping['email']) ? ($row[$mapping['email']] ?? '') : '';
                $source = isset($mapping['source']) ? ($row[$mapping['source']] ?? 'Import') : 'Import';
                $status = isset($mapping['status']) ? ($row[$mapping['status']] ?? 'New') : 'New';
                $assigned_to = null;

                if (empty($name) || empty($phone)) {
                    $errors[] = "Row " . ($index + 1) . ": Missing name or phone";
                    continue;
                }

                try {
                    $stmt->execute([$name, $email, $phone, $source, $status, $assigned_to]);
                    $imported++;
                } catch (PDOException $e) {
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'imported' => $imported,
                'total' => count($rows),
                'errors' => $errors
            ]);
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            http_response_code(500);
            echo json_encode(['error' => 'Import failed: ' . $e->getMessage()]);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        exit;
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}
