<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

requireLogin();

$product_id = intval($_GET['product_id'] ?? 0);
if ($product_id <= 0) {
    header("Location: " . baseUrl('/wishlist/index.php'));
    exit;
}

$user_id = getCurrentUserId();
$conn = getDBConnection();

$stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$stmt->close();
$conn->close();

$referer = $_SERVER['HTTP_REFERER'] ?? baseUrl('/wishlist/index.php');
header("Location: " . $referer);
exit;
