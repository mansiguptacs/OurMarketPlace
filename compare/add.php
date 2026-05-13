<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

$product_id = (int) ($_GET['product_id'] ?? $_POST['product_id'] ?? 0);
$redirect = baseUrl('/compare/index.php');

if ($product_id <= 0) {
    compareSetFlash('Pick a valid product to compare.', 'danger');
    header("Location: " . $redirect);
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, name FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$product) {
    compareSetFlash('That product could not be found.', 'danger');
    header("Location: " . $redirect);
    exit;
}

$status = compareAddProduct($product_id);
if ($status === 'added') {
    compareSetFlash('Added "' . $product['name'] . '" to compare.', 'success');
} elseif ($status === 'exists') {
    compareSetFlash('"' . $product['name'] . '" is already in compare.', 'info');
} elseif ($status === 'limit') {
    compareSetFlash('You can compare up to 3 products at a time.', 'danger');
} else {
    compareSetFlash('Could not add that product to compare.', 'danger');
}

header("Location: " . $redirect);
exit;
