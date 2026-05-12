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

$company_id = isset($_GET['company_id']) ? intval($_GET['company_id']) : 0;
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Single product by ID
if ($product_id > 0) {
    $stmt = $conn->prepare("
        SELECT p.id, p.name, p.description, p.price, p.image_url, p.category,
               p.company_id, c.name AS company_name, c.slug AS company_slug,
               COALESCE(AVG(r.rating), 0) AS avg_rating,
               COUNT(r.id) AS review_count
        FROM products p
        JOIN companies c ON c.id = p.company_id
        LEFT JOIN reviews r ON r.product_id = p.id
        WHERE p.id = ?
        GROUP BY p.id
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
    } else {
        $product['price'] = floatval($product['price']);
        $product['avg_rating'] = round(floatval($product['avg_rating']), 1);
        $product['review_count'] = intval($product['review_count']);
        if (!empty($product['image_url'])) {
            $product['image_url'] = 'https://mansiguptacs.com/ourmarketplace/' . ltrim($product['image_url'], '/');
        }
        echo json_encode(['product' => $product]);
    }
    $conn->close();
    exit;
}

// Product listing — require company_id
if ($company_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'company_id parameter is required']);
    $conn->close();
    exit;
}

// Verify company exists
$check = $conn->prepare("SELECT id, name, slug FROM companies WHERE id = ?");
$check->bind_param("i", $company_id);
$check->execute();
$company = $check->get_result()->fetch_assoc();
$check->close();

if (!$company) {
    http_response_code(404);
    echo json_encode(['error' => 'Company not found']);
    $conn->close();
    exit;
}

$sql = "
    SELECT p.id, p.name, p.description, p.price, p.image_url, p.category,
           COALESCE(AVG(r.rating), 0) AS avg_rating,
           COUNT(r.id) AS review_count
    FROM products p
    LEFT JOIN reviews r ON r.product_id = p.id
    WHERE p.company_id = ?
";
$params = [$company_id];
$types = "i";

if ($category !== '') {
    $sql .= " AND p.category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " GROUP BY p.id ORDER BY p.name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $row['price'] = floatval($row['price']);
    $row['avg_rating'] = round(floatval($row['avg_rating']), 1);
    $row['review_count'] = intval($row['review_count']);
    if (!empty($row['image_url'])) {
        $row['image_url'] = 'https://mansiguptacs.com/ourmarketplace/' . ltrim($row['image_url'], '/');
    }
    $products[] = $row;
}
$stmt->close();
$conn->close();

echo json_encode([
    'company' => $company,
    'count' => count($products),
    'products' => $products
]);
