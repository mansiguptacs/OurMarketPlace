<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// Destroy all session data
session_unset();
session_destroy();

header("Location: " . baseUrl('/index.php'));
exit;
