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

$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    http_response_code(422);
    echo json_encode(['error' => 'Username and password are required.']);
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT id, username, email, password_hash, full_name FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid username or password.']);
    $stmt->close();
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

if (!password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid username or password.']);
    $conn->close();
    exit;
}

// Generate a secure token (64 hex chars = 32 bytes of randomness)
$token = bin2hex(random_bytes(32));
$expires_at = date('Y-m-d H:i:s', strtotime('+7 days'));

// Clean up any expired tokens for this user
$cleanup = $conn->prepare("DELETE FROM user_tokens WHERE user_id = ? AND expires_at < NOW()");
$cleanup->bind_param("i", $user['id']);
$cleanup->execute();
$cleanup->close();

$stmt = $conn->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user['id'], $token, $expires_at);
$stmt->execute();
$stmt->close();
$conn->close();

echo json_encode([
    'token' => $token,
    'expires_at' => $expires_at,
    'user' => [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'full_name' => $user['full_name']
    ]
]);
