<?php
// Main PHP Backend Entry Point
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

// Simple router
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($requestMethod === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Route to authentication API
if (strpos($requestUri, '/api/auth') !== false) {
    require_once __DIR__ . '/api/auth.php';
    exit();
}

// Health check endpoint
if ($requestUri === '/api/health' || $requestUri === '/health') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'ok',
        'message' => 'Sendana PHP Backend is running',
        'timestamp' => date('c')
    ]);
    exit();
}

// Default response
header('Content-Type: application/json');
http_response_code(404);
echo json_encode([
    'error' => 'Route not found',
    'path' => $requestUri
]);
