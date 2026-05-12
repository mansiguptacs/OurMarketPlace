<?php
session_start();
require_once __DIR__ . '/sso-config.php';

$code = $_GET['code'] ?? '';

if (empty($code)) {
    die('Error: No authorization code received.');
}

$result = ssoExchangeCode($code);

if (!$result || !isset($result['token'])) {
    $err = is_array($result) ? ($result['error'] ?? 'Unknown error.') : 'Unknown error.';
    die('Error: Failed to exchange authorization code. ' . $err);
}

$_SESSION['sso_token'] = $result['token'];
$_SESSION['sso_expires_at'] = $result['expires_at'];
$_SESSION['sso_user_id'] = $result['user']['id'];
$_SESSION['sso_username'] = $result['user']['username'];
$_SESSION['sso_full_name'] = $result['user']['full_name'];

header('Location: /');
exit;
