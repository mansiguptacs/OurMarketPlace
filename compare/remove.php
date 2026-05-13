<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

$product_id = (int) ($_GET['product_id'] ?? 0);
$fallback = baseUrl('/compare/index.php');
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$redirect = (is_string($referer) && $referer !== '') ? $referer : $fallback;

if ($product_id > 0) {
    compareRemoveProduct($product_id);
    compareSetFlash('Removed product from compare.', 'info');
}

header("Location: " . $redirect);
exit;
