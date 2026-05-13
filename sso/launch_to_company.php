<?php
/**
 * Redirect logged-in marketplace user to a partner site with a signed SSO token.
 *
 * Usage: /sso/launch_to_company.php?company_id=3
 * Optional: &return=products.php  (relative path on partner site only)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/sso_token.php';

requireLogin();

$company_id = intval($_GET['company_id'] ?? 0);
if ($company_id <= 0) {
    header('Location: ' . baseUrl('/companies/index.php'));
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare('SELECT id, name, slug, website_url FROM companies WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $company_id);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$company || empty($company['website_url'])) {
    header('Location: ' . baseUrl('/companies/index.php'));
    exit;
}

$base = rtrim((string) $company['website_url'], '/');

// Cookie business now follows the same marketplace-managed SSO flow as Komal's site:
// send the user to the company site's own /sso/start.php entrypoint.
if (($company['slug'] ?? '') === 'cookie-business') {
    header('Location: ' . $base . '/sso/start.php', true, 302);
    exit;
}

$uid   = (int) getCurrentUserId();
$user  = (string) getCurrentUsername();
$name  = (string) getCurrentFullName();

$token = marketplace_sso_build_token($uid, $user, $name);
if ($token === null) {
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SSO not configured</title></head><body>';
    echo '<p>Marketplace SSO is not configured. Add <code>config/sso_config.php</code> with <code>MARKETPLACE_SSO_SECRET</code> (same value as on the partner site).</p>';
    echo '<p><a href="' . htmlspecialchars(baseUrl('/companies/view.php?id=' . (int) $company_id)) . '">Back to company</a></p>';
    echo '</body></html>';
    exit;
}

$target = $base . '/sso.php?token=' . rawurlencode($token);

$return = isset($_GET['return']) ? trim((string) $_GET['return']) : '';
if ($return !== '') {
    if (stripos($return, 'http') !== 0 && strpos($return, '..') === false && strlen($return) <= 200) {
        $return = ltrim($return, '/');
        if (preg_match('#^[a-zA-Z0-9_./?=&%-]{1,200}$#', $return)) {
            $target .= '&return=' . rawurlencode($return);
        }
    }
}

header('Location: ' . $target, true, 302);
exit;
