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

$company_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Single company
if ($company_id > 0) {
    $stmt = $conn->prepare("
        SELECT c.id, c.name, c.slug, c.description, c.owner_name,
               c.website_url, c.logo_url, c.category,
               COUNT(DISTINCT p.id) AS product_count,
               COALESCE(AVG(r.rating), 0) AS avg_rating,
               COUNT(DISTINCT r.id) AS total_reviews
        FROM companies c
        LEFT JOIN products p ON p.company_id = c.id
        LEFT JOIN reviews r ON r.product_id = p.id
        WHERE c.id = ?
        GROUP BY c.id
    ");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $company = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$company) {
        http_response_code(404);
        echo json_encode(['error' => 'Company not found']);
    } else {
        $company['product_count'] = intval($company['product_count']);
        $company['avg_rating'] = round(floatval($company['avg_rating']), 1);
        $company['total_reviews'] = intval($company['total_reviews']);
        echo json_encode(['company' => $company]);
    }
    $conn->close();
    exit;
}

// List all companies
$result = $conn->query("
    SELECT c.id, c.name, c.slug, c.description, c.owner_name,
           c.website_url, c.logo_url, c.category,
           COUNT(DISTINCT p.id) AS product_count,
           COALESCE(AVG(r.rating), 0) AS avg_rating,
           COUNT(DISTINCT r.id) AS total_reviews
    FROM companies c
    LEFT JOIN products p ON p.company_id = c.id
    LEFT JOIN reviews r ON r.product_id = p.id
    GROUP BY c.id
    ORDER BY c.name ASC
");

$companies = [];
while ($row = $result->fetch_assoc()) {
    $row['product_count'] = intval($row['product_count']);
    $row['avg_rating'] = round(floatval($row['avg_rating']), 1);
    $row['total_reviews'] = intval($row['total_reviews']);
    $companies[] = $row;
}
$conn->close();

echo json_encode([
    'count' => count($companies),
    'companies' => $companies
]);
