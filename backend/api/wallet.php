<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

$userModel = new User();

function getAuthToken() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        if (strpos($authHeader, 'Bearer ') === 0) {
            return substr($authHeader, 7);
        }
    }
    return null;
}

function verifyToken($token) {
    try {
        $decoded = json_decode(base64_decode($token), true);
        if (!$decoded || !isset($decoded['userId'])) {
            return null;
        }
        // Check if token is expired (24 hours)
        if (time() - $decoded['timestamp'] > 86400) {
            return null;
        }
        return $decoded['userId'];
    } catch (Exception $e) {
        return null;
    }
}

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

// Get Authorization header
$token = getAuthToken();

if (!$token) {
    sendResponse(['error' => 'No authorization token provided'], 401);
}

// Verify token
$userId = verifyToken($token);

if (!$userId) {
    sendResponse(['error' => 'Invalid or expired token'], 401);
}

// Get user data
$user = $userModel->findByPrivyId($userId);

if (!$user) {
    sendResponse(['error' => 'User not found'], 404);
}

// Return wallet information
sendResponse([
    'success' => true,
    'wallet' => [
        'publicKey' => $user->stellarPublicKey ?? null,
        'network' => 'MAINNET',
        'balance' => '0', // TODO: Implement balance checking from Horizon API
        'created' => $user->created_at ?? null
    ]
]);
