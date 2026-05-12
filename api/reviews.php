<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'product_id parameter is required']);
    $conn->close();
    exit;
}

// Verify product exists
$check = $conn->prepare("SELECT id, name FROM products WHERE id = ?");
$check->bind_param("i", $product_id);
$check->execute();
$product = $check->get_result()->fetch_assoc();
$check->close();

if (!$product) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
    $conn->close();
    exit;
}

$stmt = $conn->prepare("
    SELECT r.id, r.rating, r.review_text, r.created_at,
           u.full_name, u.username
    FROM reviews r
    JOIN users u ON u.id = r.user_id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

$reviews = [];
while ($row = $result->fetch_assoc()) {
    $row['rating'] = intval($row['rating']);
    $reviews[] = $row;
}
$stmt->close();
$conn->close();

echo json_encode([
    'product' => $product,
    'count' => count($reviews),
    'reviews' => $reviews
]);
