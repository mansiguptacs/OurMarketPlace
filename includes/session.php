<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

function getCurrentFullName() {
    return $_SESSION['full_name'] ?? 'Guest';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . baseUrl('/auth/login.php'));
        exit;
    }
}

/**
 * Clear login keys if user_id no longer exists (e.g. DB re-import deleted users).
 * Call after database.php is loaded. Safe if getDBConnection is unavailable.
 */
function invalidateStaleSessionUser() {
    if (empty($_SESSION['user_id'])) {
        return;
    }
    $uid = (int) $_SESSION['user_id'];
    if ($uid <= 0) {
        unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['full_name']);
        return;
    }
    if (!function_exists('getDBConnection')) {
        return;
    }
    $conn = getDBConnection();
    $stmt = $conn->prepare('SELECT 1 FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['full_name']);
    }
    $stmt->close();
    $conn->close();
}
