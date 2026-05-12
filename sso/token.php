<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$code = trim($input['code'] ?? '');
$app_id = trim($input['app_id'] ?? '');
$app_secret = trim($input['app_secret'] ?? '');

if (empty($code) || empty($app_id) || empty($app_secret)) {
    http_response_code(422);
    echo json_encode(['error' => 'Missing required fields: code, app_id, app_secret.']);
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT app_secret FROM sso_apps WHERE app_id = ?");
$stmt->bind_param("s", $app_id);
$stmt->execute();
$app_result = $stmt->get_result();

if ($app_result->num_rows !== 1) {
    $stmt->close();
    $conn->close();
    http_response_code(401);
    echo json_encode(['error' => 'Invalid app_id.']);
    exit;
}

$app = $app_result->fetch_assoc();
$stmt->close();

if (!hash_equals($app['app_secret'], $app_secret)) {
    $conn->close();
    http_response_code(401);
    echo json_encode(['error' => 'Invalid app_secret.']);
    exit;
}

$stmt = $conn->prepare("
    SELECT ac.id, ac.user_id, ac.app_id
    FROM sso_auth_codes ac
    WHERE ac.code = ? AND ac.used = 0 AND ac.expires_at > NOW()
");
$stmt->bind_param("s", $code);
$stmt->execute();
$code_result = $stmt->get_result();

if ($code_result->num_rows !== 1) {
    $stmt->close();
    $conn->close();
    http_response_code(401);
    echo json_encode(['error' => 'Invalid, expired, or already-used authorization code.']);
    exit;
}

$auth_code = $code_result->fetch_assoc();
$stmt->close();

if ($auth_code['app_id'] !== $app_id) {
    $conn->close();
    http_response_code(401);
    echo json_encode(['error' => 'Authorization code was not issued for this app.']);
    exit;
}

$mark = $conn->prepare("UPDATE sso_auth_codes SET used = 1 WHERE id = ?");
$mark->bind_param("i", $auth_code['id']);
$mark->execute();
$mark->close();

$token = bin2hex(random_bytes(32));
$expires_at = date('Y-m-d H:i:s', strtotime('+7 days'));

$cleanup = $conn->prepare("DELETE FROM user_tokens WHERE user_id = ? AND expires_at < NOW()");
$cleanup->bind_param("i", $auth_code['user_id']);
$cleanup->execute();
$cleanup->close();

$stmt = $conn->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $auth_code['user_id'], $token, $expires_at);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("SELECT id, username, full_name FROM users WHERE id = ?");
$stmt->bind_param("i", $auth_code['user_id']);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();
$conn->close();

echo json_encode([
    'token' => $token,
    'expires_at' => $expires_at,
    'user' => [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'full_name' => $user['full_name']
    ]
]);
