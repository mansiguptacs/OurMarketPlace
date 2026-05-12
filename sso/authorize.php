<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

$app_id = trim($_GET['app_id'] ?? '');
$redirect_url = trim($_GET['redirect_url'] ?? '');

if (empty($app_id) || empty($redirect_url)) {
    http_response_code(400);
    $pageTitle = "SSO Error - OurMarketplace";
    require_once __DIR__ . '/../includes/header.php';
    echo '<div class="row justify-content-center"><div class="col-md-6">';
    echo '<div class="alert alert-danger mt-4">Missing required parameters: app_id and redirect_url.</div>';
    echo '</div></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT app_id, app_name, redirect_url FROM sso_apps WHERE app_id = ?");
$stmt->bind_param("s", $app_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    $conn->close();
    http_response_code(400);
    $pageTitle = "SSO Error - OurMarketplace";
    require_once __DIR__ . '/../includes/header.php';
    echo '<div class="row justify-content-center"><div class="col-md-6">';
    echo '<div class="alert alert-danger mt-4">Unknown application: ' . htmlspecialchars($app_id) . '</div>';
    echo '</div></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$app = $result->fetch_assoc();
$stmt->close();

if ($redirect_url !== $app['redirect_url']) {
    $conn->close();
    http_response_code(400);
    $pageTitle = "SSO Error - OurMarketplace";
    require_once __DIR__ . '/../includes/header.php';
    echo '<div class="row justify-content-center"><div class="col-md-6">';
    echo '<div class="alert alert-danger mt-4">Invalid redirect URL for this application.</div>';
    echo '</div></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

if (!isLoggedIn()) {
    $current_url = baseUrl('/sso/authorize.php') . '?' . http_build_query([
        'app_id' => $app_id,
        'redirect_url' => $redirect_url
    ]);
    $login_url = baseUrl('/auth/login.php') . '?return_to=' . urlencode($current_url);
    $conn->close();
    header("Location: " . $login_url);
    exit;
}

$user_id = getCurrentUserId();
$code = bin2hex(random_bytes(32));
$expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

$stmt = $conn->prepare("INSERT INTO sso_auth_codes (code, user_id, app_id, expires_at) VALUES (?, ?, ?, ?)");
$stmt->bind_param("siss", $code, $user_id, $app_id, $expires_at);
$stmt->execute();
$stmt->close();
$conn->close();

$callback = $redirect_url . '?code=' . urlencode($code);
header("Location: " . $callback);
exit;
