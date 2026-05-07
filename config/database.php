<?php
// Database Configuration
define('DB_HOST', '');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_NAME', 'ourmarketplace');
define('DB_PORT', '3306');

// Base URL - change this to match your deployment path
// If site is at root: ''
// If site is in subdirectory: '/subfolder'
define('BASE_URL', '/ourmarketplace');

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

function baseUrl($path = '') {
    return BASE_URL . $path;
}