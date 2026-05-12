<?php
// ============================================
// SSO Configuration for Client App
// ============================================
// Copy this file to your app and update the values.

define('SSO_APP_ID', 'geekyhub');
define('SSO_APP_SECRET', 'd4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5');

define('SSO_BASE_URL', 'https://ourmarketplace.com/ourmarketplace');

define('SSO_AUTHORIZE_URL', SSO_BASE_URL . '/sso/authorize.php');
define('SSO_TOKEN_URL', SSO_BASE_URL . '/sso/token.php');
define('SSO_VERIFY_URL', SSO_BASE_URL . '/sso/verify.php');

define('SSO_CALLBACK_URL', 'http://geekyhub.me/sso/callback.php');

/**
 * Build the URL that the "Login with OurMarketplace" button should link to.
 */
function ssoLoginUrl() {
    return SSO_AUTHORIZE_URL . '?' . http_build_query([
        'app_id' => SSO_APP_ID,
        'redirect_url' => SSO_CALLBACK_URL
    ]);
}

/**
 * Exchange an authorization code for a token + user info.
 * Returns the decoded JSON response array, or null on failure.
 */
function ssoExchangeCode($code) {
    $payload = json_encode([
        'code' => $code,
        'app_id' => SSO_APP_ID,
        'app_secret' => SSO_APP_SECRET
    ]);

    $ch = curl_init(SSO_TOKEN_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return null;
    }

    return json_decode($response, true);
}

/**
 * Verify an SSO token is still valid.
 * Returns the decoded JSON response array with logged_in boolean.
 */
function ssoVerifyToken($token) {
    $url = SSO_VERIFY_URL . '?token=' . urlencode($token);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}
