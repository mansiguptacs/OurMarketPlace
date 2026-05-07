<?php
/**
 * Track a user's visit to a page.
 * Call this function on any page where you want to log the visit.
 * Works for both logged-in users (tracked by user_id) and guests (user_id = NULL).
 */
function trackVisit($company_id, $product_id = null) {
    $user_id = getCurrentUserId();
    $page_url = $_SERVER['REQUEST_URI'] ?? '';

    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO user_visits (user_id, company_id, product_id, page_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $user_id, $company_id, $product_id, $page_url);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}
