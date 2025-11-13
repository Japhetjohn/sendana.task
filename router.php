<?php
// Router for PHP Built-in Server

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Redirect root to login page
if ($uri === '/') {
    header('Location: /frontend/pages/index.html');
    exit();
}

// Serve static files
if (file_exists(__DIR__ . $uri)) {
    return false; // Let PHP serve the static file
}

// Route backend API requests
if (strpos($uri, '/backend/api/auth') !== false || strpos($uri, '/backend/api/signup') !== false) {
    require __DIR__ . '/backend/api/auth.php';
    exit();
}

if (strpos($uri, '/backend/api/health') !== false || strpos($uri, '/backend/health') !== false) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'ok',
        'message' => 'Sendana PHP Backend is running',
        'timestamp' => date('c')
    ]);
    exit();
}

if (strpos($uri, '/backend') !== false) {
    require __DIR__ . '/backend/index.php';
    exit();
}

// Default: return false to let PHP serve the file
return false;
