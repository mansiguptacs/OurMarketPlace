<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

requireLogin();

$product_id = intval($_GET['product_id'] ?? 0);
if ($product_id <= 0) {
    header("Location: " . baseUrl('/products/index.php'));
    exit;
}

$user_id = getCurrentUserId();
$conn = getDBConnection();

// Insert into wishlist (ignore if already exists)
$stmt = $conn->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$stmt->close();
$conn->close();

// Redirect back to where they came from
$referer = $_SERVER['HTTP_REFERER'] ?? baseUrl('/products/view.php?id=' . $product_id);
header("Location: " . $referer);
exit;
