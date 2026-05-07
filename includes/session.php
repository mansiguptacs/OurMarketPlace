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
        header("Location: /auth/login.php");
        exit;
    }
}
