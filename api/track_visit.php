<?php
/**
 * POST /api/track_visit.php
 *
 * Records a visit to a marketplace product or company from an external site
 * (or anywhere outside the marketplace's own session). Writes one row in
 * user_visits per call (mirrors the marketplace's existing trackVisit()).
 *
 * Request body (JSON):
 *   { "product_id": 1 }                            // visit to a product (company resolved from products row)
 *   { "company_id": 1 }                            // visit to a company page
 *   { "product_id": 1, "page_url": "..." }         // optional page_url
 *
 * Headers (optional):
 *   Authorization: Bearer <token>                  // attaches the visit to a logged-in marketplace user
 *
 * Responses:
 *   200 { ok: true, visit_id: N }
 *   400 invalid input
 *   404 product/company not found
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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

$product_id = isset($input['product_id']) ? (int)$input['product_id'] : 0;
$company_id = isset($input['company_id']) ? (int)$input['company_id'] : 0;
$page_url   = isset($input['page_url']) ? substr(trim((string)$input['page_url']), 0, 255) : '';

if ($product_id <= 0 && $company_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'product_id or company_id is required.']);
    exit;
}

$conn = getDBConnection();

// If product_id is provided, resolve its company_id (and validate existence)
if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT company_id FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found.']);
        $conn->close();
        exit;
    }
    $resolved_company_id = (int)$row['company_id'];
    // Caller-supplied company_id is ignored if it conflicts with the product's owner.
    $company_id = $resolved_company_id;
} else {
    // company_id only — validate it exists
    $stmt = $conn->prepare("SELECT id FROM companies WHERE id = ?");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Company not found.']);
        $conn->close();
        exit;
    }
}

// Optional: attach visit to a logged-in marketplace user via Bearer token
$user_id = null;
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $m)) {
    $token = $m[1];
    $stmt = $conn->prepare("SELECT user_id FROM user_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) {
        $user_id = (int)$row['user_id'];
    }
}

if ($page_url === '') {
    // Default to a sensible canonical URL so the rows look like the marketplace's own
    $page_url = $product_id > 0
        ? '/products/view.php?id=' . $product_id
        : '/companies/view.php?id=' . $company_id;
}

// product_id may be NULL in the schema; bind_param doesn't support NULL directly via "i".
// Use a sentinel and rebind via separate queries to keep this simple.
if ($product_id > 0) {
    $stmt = $conn->prepare("INSERT INTO user_visits (user_id, company_id, product_id, page_url) VALUES (?, ?, ?, ?)");
    // mysqli requires bind types for all 4; pass NULL via send_long_data trick or use a null variable.
    // Simplest: build the SQL with a literal NULL when needed — but here product_id > 0 so we always bind.
    $stmt->bind_param("iiis", $user_id, $company_id, $product_id, $page_url);
} else {
    $stmt = $conn->prepare("INSERT INTO user_visits (user_id, company_id, product_id, page_url) VALUES (?, ?, NULL, ?)");
    $stmt->bind_param("iis", $user_id, $company_id, $page_url);
}

if ($stmt->execute()) {
    $visit_id = $stmt->insert_id;
    $stmt->close();
    $conn->close();
    echo json_encode(['ok' => true, 'visit_id' => $visit_id]);
} else {
    $stmt->close();
    $conn->close();
    http_response_code(500);
    echo json_encode(['error' => 'Could not record visit.']);
}
