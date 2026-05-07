<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . baseUrl('/products/index.php'));
    exit;
}

$product_id  = intval($_POST['product_id'] ?? 0);
$rating      = intval($_POST['rating'] ?? 0);
$review_text = trim($_POST['review_text'] ?? '');
$user_id     = getCurrentUserId();

// Validation
if ($product_id <= 0 || $rating < 1 || $rating > 5) {
    header("Location: " . baseUrl('/products/index.php'));
    exit;
}

$conn = getDBConnection();

// Check if product exists
$stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: " . baseUrl('/products/index.php'));
    exit;
}
$stmt->close();

// Insert or update review (one review per user per product)
$stmt = $conn->prepare("
    INSERT INTO reviews (user_id, product_id, rating, review_text)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE rating = VALUES(rating), review_text = VALUES(review_text), created_at = CURRENT_TIMESTAMP
");
$stmt->bind_param("iiis", $user_id, $product_id, $rating, $review_text);
$stmt->execute();
$stmt->close();
$conn->close();

// Redirect back to product page
header("Location: " . baseUrl('/products/view.php?id=' . $product_id));
exit;
