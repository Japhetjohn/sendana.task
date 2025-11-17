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
require_once __DIR__ . '/../../services/EmailService.php';

$userModel = new User();
$emailService = new EmailService();

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

    $password = $input['password'];
    if (strlen($password) < 8) {
        sendResponse(['error' => 'Password must be at least 8 characters'], 400);
    }

    $existingUser = $userModel->findByEmail($email);
    if ($existingUser) {
        sendResponse(['error' => 'Email already registered'], 409);
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Wallet will be created by frontend via Privy SDK
    // Create user without wallet initially
    $userData = [
        'email' => $email,
        'passwordHash' => $passwordHash,
        'authProvider' => 'email'
        // Wallet fields will be added later by /privy/create-wallet endpoint
    ];

    $user = $userModel->create($userData);

    if (!$user) {
        sendResponse(['error' => 'Failed to create user'], 500);
    }

    error_log("User created successfully: " . $email);

    // Generate token using user ID (wallet will be added later by frontend)
    $userId = isset($user->privyId) ? $user->privyId : (string)$user->_id;
    $token = generateToken($userId);

    try {
        $firstName = explode('@', $email)[0];
        $firstName = ucfirst($firstName);
        $emailService->sendWelcomeEmail($email, $firstName);
    } catch (Exception $e) {
        error_log("Failed to queue welcome email: " . $e->getMessage());
    }

    sendResponse([
        'success' => true,
        'message' => 'Account created successfully',
        'token' => $token,
        'user' => $userModel->toArray($user)
    ]);

} catch (Exception $e) {
    error_log("Signup error: " . $e->getMessage());
    sendResponse(['error' => 'Server error during signup: ' . $e->getMessage()], 500);
}
