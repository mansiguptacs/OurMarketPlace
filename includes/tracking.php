<?php
/**
 * Track a user's visit to a page.
 * Call this function on any page where you want to log the visit.
 * Works for both logged-in users (tracked by user_id) and guests (user_id = NULL).
 */
function trackVisit($company_id, $product_id = null) {
    $rawUser = getCurrentUserId();
    $user_id = null;
    if ($rawUser !== null && $rawUser !== '' && (int) $rawUser > 0) {
        $user_id = (int) $rawUser;
    }

    $company_id = (int) $company_id;
    $product_id = ($product_id !== null && $product_id !== '' && (int) $product_id > 0)
        ? (int) $product_id
        : null;

    $page_url = $_SERVER['REQUEST_URI'] ?? '';
    if (strlen($page_url) > 255) {
        $page_url = substr($page_url, 0, 255);
    }

    $conn = getDBConnection();

    // Stale session after DB re-import: user_id must exist in users or we treat as guest
    if ($user_id !== null) {
        $check = $conn->prepare('SELECT 1 FROM users WHERE id = ? LIMIT 1');
        $check->bind_param('i', $user_id);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            $user_id = null;
        }
        $check->close();
    }

    // Use explicit NULL in SQL so mysqli never binds 0 for missing user/product (FK errors)
    if ($user_id === null && $product_id === null) {
        $stmt = $conn->prepare('INSERT INTO user_visits (user_id, company_id, product_id, page_url) VALUES (NULL, ?, NULL, ?)');
        $stmt->bind_param('is', $company_id, $page_url);
    } elseif ($user_id === null) {
        $stmt = $conn->prepare('INSERT INTO user_visits (user_id, company_id, product_id, page_url) VALUES (NULL, ?, ?, ?)');
        $stmt->bind_param('iis', $company_id, $product_id, $page_url);
    } elseif ($product_id === null) {
        $stmt = $conn->prepare('INSERT INTO user_visits (user_id, company_id, product_id, page_url) VALUES (?, ?, NULL, ?)');
        $stmt->bind_param('iis', $user_id, $company_id, $page_url);
    } else {
        $stmt = $conn->prepare('INSERT INTO user_visits (user_id, company_id, product_id, page_url) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('iiis', $user_id, $company_id, $product_id, $page_url);
    }

    $stmt->execute();
    $stmt->close();
    $conn->close();
}
