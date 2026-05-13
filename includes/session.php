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

function compareProductIds(): array {
    $raw = $_SESSION['compare_product_ids'] ?? [];
    if (!is_array($raw)) {
        return [];
    }
    $ids = [];
    foreach ($raw as $id) {
        $id = (int) $id;
        if ($id > 0) {
            $ids[$id] = $id;
        }
    }
    return array_values($ids);
}

function compareHasProduct(int $productId): bool {
    return in_array($productId, compareProductIds(), true);
}

function compareCount(): int {
    return count(compareProductIds());
}

function compareAddProduct(int $productId, int $limit = 3): string {
    if ($productId <= 0) {
        return 'invalid';
    }
    $ids = compareProductIds();
    if (in_array($productId, $ids, true)) {
        return 'exists';
    }
    if (count($ids) >= $limit) {
        return 'limit';
    }
    $ids[] = $productId;
    $_SESSION['compare_product_ids'] = array_values($ids);
    return 'added';
}

function compareRemoveProduct(int $productId): void {
    $ids = array_values(array_filter(compareProductIds(), function ($id) use ($productId) {
        return (int) $id !== $productId;
    }));
    $_SESSION['compare_product_ids'] = $ids;
}

function compareClearProducts(): void {
    unset($_SESSION['compare_product_ids']);
}

function compareSetFlash(string $message, string $type = 'info'): void {
    $_SESSION['compare_flash'] = [
        'message' => $message,
        'type' => $type,
    ];
}

function comparePullFlash(): ?array {
    $flash = $_SESSION['compare_flash'] ?? null;
    unset($_SESSION['compare_flash']);
    return is_array($flash) ? $flash : null;
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
