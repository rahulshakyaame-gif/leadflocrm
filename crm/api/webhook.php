<?php
require_once 'db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Only accept POST requests
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get webhook token from URL parameter
$webhook_token = $_GET['token'] ?? '';

if (empty($webhook_token)) {
    http_response_code(400);
    echo json_encode(['error' => 'Webhook token is required']);
    exit;
}

// Verify webhook token exists
try {
    $stmt = $pdo->prepare("SELECT * FROM integrations WHERE webhook_token = ? AND status = 'active'");
    $stmt->execute([$webhook_token]);
    $integration = $stmt->fetch();

    if (!$integration) {
        http_response_code(404);
        echo json_encode(['error' => 'Invalid webhook token']);
        exit;
    }

    // Get POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON payload']);
        exit;
    }

    // Parse lead data based on platform
    $lead_data = parsePlatformData($integration['platform'], $data);

    if (!$lead_data) {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to parse lead data']);
        exit;
    }

    // Insert lead into database
    $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, source, status) VALUES (?, ?, ?, ?, 'New')");
    $stmt->execute([
        $lead_data['name'],
        $lead_data['email'] ?? '',
        $lead_data['phone'],
        $integration['platform']
    ]);

    $lead_id = $pdo->lastInsertId();

    // Update integration stats
    $stmt = $pdo->prepare("UPDATE integrations SET leads_received = leads_received + 1, last_used_at = NOW() WHERE id = ?");
    $stmt->execute([$integration['id']]);

    echo json_encode([
        'success' => true,
        'lead_id' => $lead_id,
        'message' => 'Lead created successfully'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

function parsePlatformData($platform, $data)
{
    switch ($platform) {
        case 'google_ads':
            return parseGoogleAdsData($data);
        case 'facebook':
            return parseFacebookData($data);
        case 'custom':
            return parseCustomData($data);
        default:
            return null;
    }
}

function parseGoogleAdsData($data)
{
    // Google Ads Lead Form Extension format
    // https://developers.google.com/google-ads/api/docs/conversions/upload-leads

    $lead = [
        'name' => '',
        'email' => '',
        'phone' => ''
    ];

    // Check for form_data array
    if (isset($data['form_data'])) {
        foreach ($data['form_data'] as $field) {
            $key = strtolower($field['key'] ?? '');
            $value = $field['value'] ?? '';

            if (strpos($key, 'name') !== false || strpos($key, 'full_name') !== false) {
                $lead['name'] = $value;
            } elseif (strpos($key, 'email') !== false) {
                $lead['email'] = $value;
            } elseif (strpos($key, 'phone') !== false) {
                $lead['phone'] = $value;
            }
        }
    }

    // Fallback to direct fields
    $lead['name'] = $lead['name'] ?: ($data['name'] ?? $data['full_name'] ?? '');
    $lead['email'] = $lead['email'] ?: ($data['email'] ?? '');
    $lead['phone'] = $lead['phone'] ?: ($data['phone'] ?? $data['phone_number'] ?? '');

    return (!empty($lead['name']) && !empty($lead['phone'])) ? $lead : null;
}

function parseFacebookData($data)
{
    // Facebook Lead Ads format
    // https://developers.facebook.com/docs/marketing-api/guides/lead-ads

    $lead = [
        'name' => '',
        'email' => '',
        'phone' => ''
    ];

    // Check for field_data array
    if (isset($data['field_data'])) {
        foreach ($data['field_data'] as $field) {
            $name = strtolower($field['name'] ?? '');
            $values = $field['values'] ?? [];
            $value = is_array($values) && !empty($values) ? $values[0] : '';

            if (strpos($name, 'name') !== false || $name === 'full_name') {
                $lead['name'] = $value;
            } elseif (strpos($name, 'email') !== false) {
                $lead['email'] = $value;
            } elseif (strpos($name, 'phone') !== false) {
                $lead['phone'] = $value;
            }
        }
    }

    // Fallback to direct fields
    $lead['name'] = $lead['name'] ?: ($data['name'] ?? $data['full_name'] ?? '');
    $lead['email'] = $lead['email'] ?: ($data['email'] ?? '');
    $lead['phone'] = $lead['phone'] ?: ($data['phone'] ?? $data['phone_number'] ?? '');

    return (!empty($lead['name']) && !empty($lead['phone'])) ? $lead : null;
}

function parseCustomData($data)
{
    // Simple field mapping for custom webhooks
    $lead = [
        'name' => $data['name'] ?? $data['full_name'] ?? $data['fullname'] ?? '',
        'email' => $data['email'] ?? '',
        'phone' => $data['phone'] ?? $data['phone_number'] ?? $data['mobile'] ?? ''
    ];

    return (!empty($lead['name']) && !empty($lead['phone'])) ? $lead : null;
}
