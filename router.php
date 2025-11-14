<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/') {
    header('Location: /frontend/pages/index.html');
    exit();
}

if (file_exists(__DIR__ . $uri)) {
    return false;
}

if (strpos($uri, '/backend/api/auth') !== false || strpos($uri, '/api/auth') !== false ||
    strpos($uri, '/backend/api/wallet') !== false || strpos($uri, '/api/wallet') !== false) {
    require __DIR__ . '/backend/api/auth.php';
    exit();
}

if (strpos($uri, '/backend/api/health') !== false || strpos($uri, '/api/health') !== false) {
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

return false;
