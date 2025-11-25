<?php
require_once 'db.php';

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Only admin can see full reports? Or maybe team can see their own stats?
// Let's assume admin sees all, team sees their own.

$where_clause = "";
$params = [];

if ($user_role !== 'admin') {
    $where_clause = "WHERE assigned_to = ?";
    $params[] = $user_id;
}

try {
    // Leads by Status
    $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM leads $where_clause GROUP BY status");
    $stmt->execute($params);
    $leads_by_status = $stmt->fetchAll();

    // Leads by Source
    $stmt = $pdo->prepare("SELECT source, COUNT(*) as count FROM leads $where_clause GROUP BY source");
    $stmt->execute($params);
    $leads_by_source = $stmt->fetchAll();

    // Recent Activity (Calls) - maybe graph over time?
    // Calls per day for last 7 days
    $call_where = "";
    $call_params = [];
    if ($user_role !== 'admin') {
        $call_where = "WHERE lead_id IN (SELECT id FROM leads WHERE assigned_to = ?)";
        $call_params[] = $user_id;
    }

    $stmt = $pdo->prepare("
        SELECT DATE(call_date) as date, COUNT(*) as count 
        FROM calls 
        $call_where
        GROUP BY DATE(call_date) 
        ORDER BY date DESC 
        LIMIT 7
    ");
    $stmt->execute($call_params);
    $calls_over_time = $stmt->fetchAll();

    echo json_encode([
        'leads_by_status' => $leads_by_status,
        'leads_by_source' => $leads_by_source,
        'calls_over_time' => $calls_over_time
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error generating reports: ' . $e->getMessage()]);
}
