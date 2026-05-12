<?php
/**
 * Read API bearer token when Authorization is missing (common on Apache/LiteSpeed
 * without SetEnvIf / RewriteRule for HTTP_AUTHORIZATION).
 */
function om_api_read_bearer_token(): string {
    $h = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/^Bearer\s+(.+)$/i', $h, $m)) {
        return trim($m[1]);
    }
    $x = $_SERVER['HTTP_X_MARKETPLACE_TOKEN'] ?? $_SERVER['REDIRECT_HTTP_X_MARKETPLACE_TOKEN'] ?? '';
    $x = trim((string) $x);
    if ($x !== '') {
        return $x;
    }
    return '';
}
