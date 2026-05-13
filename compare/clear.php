<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

compareClearProducts();
compareSetFlash('Cleared compare tray.', 'info');

header("Location: " . baseUrl('/compare/index.php'));
exit;
