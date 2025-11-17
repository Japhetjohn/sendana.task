<?php
// Router for PHP Built-in Server
// This handles routing for the Sendana application

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Handle CORS preflight
if ($method === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(200);
    exit;
}

// Route backend API requests
if (preg_match('#^/backend/api/(.+)$#', $uri, $matches)) {
    $apiPath = $matches[1];

    // Map routes to files
    $routes = [
        'health' => 'backend/api/health.php',
        'auth/signup' => 'backend/api/auth/signup.php',
        'auth/login' => 'backend/api/auth/login.php',
        'auth/google' => 'backend/api/auth/google.php',
        'auth/user' => 'backend/api/auth.php',
        'privy/create-wallet' => 'backend/api/privy/create-wallet.php',
        'wallet' => 'backend/api/wallet.php',
    ];

    // Check if route exists
    if (isset($routes[$apiPath])) {
        $file = __DIR__ . '/' . $routes[$apiPath];
        if (file_exists($file)) {
            require $file;
            exit;
        }
    }

    // If no specific route, try the generic auth.php handler
    if (preg_match('#^auth/#', $apiPath)) {
        $file = __DIR__ . '/backend/api/auth.php';
        if (file_exists($file)) {
            require $file;
            exit;
        }
    }

    // API route not found
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['error' => 'API endpoint not found: ' . $apiPath]);
    exit;
}

// Serve frontend files
if (preg_match('#^/frontend/(.+)$#', $uri, $matches)) {
    $file = __DIR__ . '/frontend/' . $matches[1];
    if (file_exists($file) && is_file($file)) {
        return false; // Let PHP serve the static file
    }
}

// Root redirects to signup
if ($uri === '/' || $uri === '') {
    header('Location: /frontend/pages/index.html');
    exit;
}

// Default: try to serve as static file
$file = __DIR__ . $uri;
if (file_exists($file) && is_file($file)) {
    return false; // Let PHP serve the file
}

// 404 for everything else
http_response_code(404);
echo "404 - Not Found: $uri";
