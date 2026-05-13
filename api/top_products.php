<?php
/**
 * GET /api/top_products.php
 *
 * Returns the top-N products ranked by one of three methods. Mirrors the
 * SQL used by /rankings/marketplace_top5.php and /rankings/company_top5.php
 * so the JSON results match what those pages display.
 *
 * Query params:
 *   company_id (optional, int) — if provided, limits to a single company
 *   method     (optional)      — best_rated | most_visited | most_reviewed (default: best_rated)
 *   limit      (optional, int) — 1..20, default 5
 *
 * Response:
 *   {
 *     "method": "best_rated",
 *     "company_id": 1,             // present only when filtered
 *     "count": 5,
 *     "products": [
 *       { id, name, description, price, image_url, category,
 *         company_id, company_name, company_slug,
 *         avg_rating, review_count, visit_count },
 *       ...
 *     ]
 *   }
 */
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
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

function om_api_image_url(string $imageUrl): string
{
    $imageUrl = trim($imageUrl);
    if ($imageUrl === '') {
        return '';
    }
    if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        return $imageUrl;
    }
    return 'https://mansiguptacs.com/ourmarketplace/' . ltrim($imageUrl, '/');
}

$company_id = isset($_GET['company_id']) ? (int)$_GET['company_id'] : 0;
$method     = isset($_GET['method']) ? trim((string)$_GET['method']) : 'best_rated';
$limit      = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

$valid_methods = ['best_rated', 'most_visited', 'most_reviewed'];
if (!in_array($method, $valid_methods, true)) {
    $method = 'best_rated';
}

if ($limit < 1)  $limit = 1;
if ($limit > 20) $limit = 20;

switch ($method) {
    case 'most_visited':
        $order_by = 'visit_count DESC, avg_rating DESC';
        break;
    case 'most_reviewed':
        $order_by = 'review_count DESC, avg_rating DESC';
        break;
    default:
        $order_by = 'avg_rating DESC, review_count DESC';
        break;
}

$conn = getDBConnection();

// Optionally validate the company exists so we can return a useful 404
if ($company_id > 0) {
    $check = $conn->prepare("SELECT id, name, slug FROM companies WHERE id = ?");
    $check->bind_param("i", $company_id);
    $check->execute();
    $company_row = $check->get_result()->fetch_assoc();
    $check->close();

    if (!$company_row) {
        http_response_code(404);
        echo json_encode(['error' => 'Company not found.']);
        $conn->close();
        exit;
    }
}

$base_sql = "
    SELECT p.id, p.name, p.description, p.price, p.image_url, p.category,
           p.company_id, c.name AS company_name, c.slug AS company_slug,
           COALESCE(AVG(r.rating), 0) AS avg_rating,
           COUNT(DISTINCT r.id) AS review_count,
           COUNT(DISTINCT v.id) AS visit_count
    FROM products p
    JOIN companies c ON c.id = p.company_id
    LEFT JOIN reviews r ON r.product_id = p.id
    LEFT JOIN user_visits v ON v.product_id = p.id
";

if ($company_id > 0) {
    $sql = $base_sql . " WHERE p.company_id = ? GROUP BY p.id ORDER BY $order_by LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $company_id, $limit);
} else {
    $sql = $base_sql . " GROUP BY p.id ORDER BY $order_by LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
}

$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $row['price']        = (float)$row['price'];
    $row['avg_rating']   = round((float)$row['avg_rating'], 1);
    $row['review_count'] = (int)$row['review_count'];
    $row['visit_count']  = (int)$row['visit_count'];
    $row['image_url'] = om_api_image_url((string) ($row['image_url'] ?? ''));
    $products[] = $row;
}
$stmt->close();
$conn->close();

$response = [
    'method' => $method,
    'count'  => count($products),
    'products' => $products
];
if ($company_id > 0) {
    $response['company_id'] = $company_id;
}

echo json_encode($response);
