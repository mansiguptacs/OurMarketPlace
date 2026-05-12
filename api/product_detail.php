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

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'id parameter is required']);
    $conn->close();
    exit;
}

// Fetch product with company info and aggregate ratings
$stmt = $conn->prepare("
    SELECT p.id, p.name, p.description, p.price, p.image_url, p.category,
           p.company_id, c.name AS company_name, c.slug AS company_slug,
           c.website_url AS company_website, c.category AS company_category,
           COALESCE(AVG(r.rating), 0) AS avg_rating,
           COUNT(r.id) AS review_count,
           COUNT(DISTINCT v.id) AS visit_count
    FROM products p
    JOIN companies c ON c.id = p.company_id
    LEFT JOIN reviews r ON r.product_id = p.id
    LEFT JOIN user_visits v ON v.product_id = p.id
    WHERE p.id = ?
    GROUP BY p.id
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
    $conn->close();
    exit;
}

$product['price'] = floatval($product['price']);
$product['avg_rating'] = round(floatval($product['avg_rating']), 1);
$product['review_count'] = intval($product['review_count']);
$product['visit_count'] = intval($product['visit_count']);

if (!empty($product['image_url'])) {
    $product['image_url'] = 'https://mansiguptacs.com/ourmarketplace/' . ltrim($product['image_url'], '/');
}

// Rating breakdown (how many 1-star, 2-star, ... 5-star)
$stmt = $conn->prepare("
    SELECT rating, COUNT(*) AS count
    FROM reviews
    WHERE product_id = ?
    GROUP BY rating
    ORDER BY rating DESC
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

$rating_breakdown = ['5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0];
while ($row = $result->fetch_assoc()) {
    $rating_breakdown[$row['rating']] = intval($row['count']);
}
$stmt->close();

// Fetch all reviews with user info
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
    'rating_breakdown' => $rating_breakdown,
    'reviews' => $reviews
]);
