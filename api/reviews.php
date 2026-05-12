<?php
/**
 * Product reviews API (cross-origin friendly).
 *
 * GET  ?product_id= — list reviews for a product (unchanged).
 * POST — create or update the caller's review for one product (one review per user per product).
 *        Auth (any one): Authorization: Bearer <token>, header X-Marketplace-Token: <token>, or JSON access_token
 *        (some hosts strip Authorization on POST; use X-Marketplace-Token or access_token from the client).
 *        Body (JSON): { "product_id": int, "rating": 1-5, "review_text": string (optional), "access_token": string (optional) }
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Marketplace-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/api_token.php';

$method = $_SERVER['REQUEST_METHOD'] ?? '';

if ($method === 'GET') {
    $conn = getDBConnection();

    $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

    if ($product_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'product_id parameter is required']);
        $conn->close();
        exit;
    }

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
    exit;
}

if ($method === 'POST') {
    $rawBody = file_get_contents('php://input');
    $input = json_decode($rawBody, true);
    if (!is_array($input)) {
        $input = [];
    }

    $token = om_api_read_bearer_token();
    if ($token === '' && !empty($input['access_token'])) {
        $token = trim((string) $input['access_token']);
    }

    if ($token === '') {
        http_response_code(401);
        echo json_encode([
            'error' => 'Missing token. Use Authorization: Bearer <token>, header X-Marketplace-Token, or JSON field access_token.',
            'hint' => 'Some hosts strip Authorization on POST; send access_token in the JSON body from your client.',
        ]);
        exit;
    }

    $product_id = intval($input['product_id'] ?? 0);
    $rating = intval($input['rating'] ?? 0);
    $review_text = isset($input['review_text']) ? trim((string) $input['review_text']) : '';

    if ($product_id <= 0 || $rating < 1 || $rating > 5) {
        http_response_code(422);
        echo json_encode(['error' => 'Invalid product_id or rating. product_id must be positive; rating must be 1–5.']);
        exit;
    }

    $conn = getDBConnection();

    $stmt = $conn->prepare("
        SELECT u.id AS user_id
        FROM user_tokens t
        JOIN users u ON u.id = t.user_id
        WHERE t.token = ? AND t.expires_at > NOW()
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $tokRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$tokRow) {
        http_response_code(401);
        echo json_encode(['error' => 'Token is invalid or expired.']);
        $conn->close();
        exit;
    }

    $user_id = (int) $tokRow['user_id'];

    $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        $stmt->close();
        $conn->close();
        http_response_code(404);
        echo json_encode(['error' => 'Product not found.']);
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("
        INSERT INTO reviews (user_id, product_id, rating, review_text)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE rating = VALUES(rating), review_text = VALUES(review_text), created_at = CURRENT_TIMESTAMP
    ");
    $stmt->bind_param("iiis", $user_id, $product_id, $rating, $review_text);
    if (!$stmt->execute()) {
        $stmt->close();
        $conn->close();
        http_response_code(500);
        echo json_encode(['error' => 'Could not save review. Check server logs or database constraints.']);
        exit;
    }
    $insert_id = (int) $conn->insert_id;
    $stmt->close();

    $was_update = ($insert_id === 0);

    $stmt = $conn->prepare("
        SELECT r.id, r.user_id, r.product_id, r.rating, r.review_text, r.created_at,
               u.full_name, u.username
        FROM reviews r
        JOIN users u ON u.id = r.user_id
        WHERE r.user_id = ? AND r.product_id = ?
    ");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $review = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($review) {
        $review['id'] = (int) $review['id'];
        $review['user_id'] = (int) $review['user_id'];
        $review['product_id'] = (int) $review['product_id'];
        $review['rating'] = (int) $review['rating'];
    }

    http_response_code($was_update ? 200 : 201);
    echo json_encode([
        'ok' => true,
        'updated' => $was_update,
        'review' => $review,
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed. Use GET or POST.']);
