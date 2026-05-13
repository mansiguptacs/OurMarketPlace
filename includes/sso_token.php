<?php
/**
 * Build signed SSO tokens for partner company sites (e.g. cookie-business).
 */

function marketplace_sso_secret_from_config(): ?string {
    $path = __DIR__ . '/../config/sso_config.php';
    if (!is_file($path)) {
        return null;
    }
    require_once $path;
    if (!defined('MARKETPLACE_SSO_SECRET')) {
        return null;
    }
    $s = (string) MARKETPLACE_SSO_SECRET;
    if ($s === '' || $s === 'CHANGE_ME_TO_A_LONG_RANDOM_STRING_SHARED_WITH_MARKETPLACE') {
        return null;
    }
    return $s;
}

function marketplace_sso_build_token(int $userId, string $username, string $fullName): ?string {
    $secret = marketplace_sso_secret_from_config();
    if ($secret === null) {
        return null;
    }
    $now = time();
    $payload = array(
        'iss'       => 'ourmarketplace',
        'sub'       => $userId,
        'username'  => $username,
        'full_name' => $fullName,
        'iat'       => $now,
        'exp'       => $now + 300,
    );
    $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return null;
    }
    $b64 = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
    $sig = rtrim(strtr(base64_encode(hash_hmac('sha256', $b64, $secret, true)), '+/', '-_'), '=');
    return $b64 . '.' . $sig;
}

function marketplace_sso_launch_ready(): bool {
    return marketplace_sso_secret_from_config() !== null;
}
