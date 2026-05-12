<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Marketplace-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use GET.']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/api_token.php';

// Accept token from Authorization, X-Marketplace-Token, or ?token=
$token = om_api_read_bearer_token();

if (empty($token)) {
    $token = trim($_GET['token'] ?? '');
}

if (empty($token)) {
    http_response_code(401);
    echo json_encode(['logged_in' => false, 'error' => 'No token provided. Send via Authorization: Bearer <token> header or ?token= query parameter.']);
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("
    SELECT u.id, u.username, u.email, u.full_name, t.expires_at
    FROM user_tokens t
    JOIN users u ON u.id = t.user_id
    WHERE t.token = ? AND t.expires_at > NOW()
");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    $conn->close();
    http_response_code(401);
    echo json_encode(['logged_in' => false, 'error' => 'Token is invalid or expired.']);
    exit;
}

$user = $result->fetch_assoc();
$expires_at = $user['expires_at'];
unset($user['expires_at']);

$stmt->close();
$conn->close();

echo json_encode([
    'logged_in' => true,
    'expires_at' => $expires_at,
    'user' => $user
]);
