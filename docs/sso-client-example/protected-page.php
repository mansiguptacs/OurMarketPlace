<?php
session_start();
require_once __DIR__ . '/sso-config.php';

// Check if user has an SSO session
if (empty($_SESSION['sso_token'])) {
    header('Location: ' . ssoLoginUrl());
    exit;
}

// Verify the token is still valid with OurMarketplace
$verification = ssoVerifyToken($_SESSION['sso_token']);

if (!$verification || !$verification['logged_in']) {
    session_unset();
    session_destroy();
    session_start();
    header('Location: ' . ssoLoginUrl());
    exit;
}

$username = $_SESSION['sso_username'];
$fullName = $_SESSION['sso_full_name'];
?>
<!DOCTYPE html>
<html>
<head><title>Protected Page</title></head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($fullName); ?>!</h1>
    <p>Username: <?php echo htmlspecialchars($username); ?></p>
    <p>This page is protected by OurMarketplace SSO.</p>
    <a href="logout.php">Logout</a>
</body>
</html>
