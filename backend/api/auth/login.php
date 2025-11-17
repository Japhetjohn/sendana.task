<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';

$userModel = new User();

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

function generateToken($userId) {
    return base64_encode(json_encode([
        'userId' => $userId,
        'timestamp' => time(),
        'random' => bin2hex(random_bytes(16))
    ]));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['error' => 'Method not allowed'], 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['email']) || !isset($input['password'])) {
        sendResponse(['error' => 'Email and password are required'], 400);
    }

    $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        sendResponse(['error' => 'Invalid email format'], 400);
    }

    $user = $userModel->findByEmail($email);

    if (!$user) {
        sendResponse(['error' => 'Invalid email or password'], 401);
    }

    if (!isset($user->passwordHash) || !password_verify($input['password'], $user->passwordHash)) {
        sendResponse(['error' => 'Invalid email or password'], 401);
    }

    $userId = isset($user->privyId) ? $user->privyId : (string)$user->_id;
    $token = generateToken($userId);

    sendResponse([
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,
        'user' => $userModel->toArray($user)
    ]);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    sendResponse(['error' => 'Server error during login: ' . $e->getMessage()], 500);
}
