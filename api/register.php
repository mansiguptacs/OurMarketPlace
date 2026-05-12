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

$username  = trim($input['username'] ?? '');
$email     = trim($input['email'] ?? '');
$full_name = trim($input['full_name'] ?? '');
$password  = $input['password'] ?? '';

$errors = [];

if (empty($username)) {
    $errors[] = 'Username is required.';
} elseif (strlen($username) < 3) {
    $errors[] = 'Username must be at least 3 characters.';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'Username may only contain letters, numbers, and underscores.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email is required.';
}

if (empty($full_name)) {
    $errors[] = 'Full name is required.';
}

if (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['errors' => $errors]);
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$existing = $stmt->get_result();

if ($existing->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'Username or email already exists.']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $email, $password_hash, $full_name);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    $stmt->close();
    $conn->close();

    http_response_code(201);
    echo json_encode([
        'message' => 'User registered successfully.',
        'user' => [
            'id' => $user_id,
            'username' => $username,
            'email' => $email,
            'full_name' => $full_name
        ]
    ]);
} else {
    $stmt->close();
    $conn->close();

    http_response_code(500);
    echo json_encode(['error' => 'Registration failed. Please try again.']);
}
