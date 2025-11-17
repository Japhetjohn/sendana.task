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

    if (!isset($input['email']) || !isset($input['sub'])) {
        sendResponse(['error' => 'Google user data is required'], 400);
    }

    $email = $input['email'];
    $name = $input['name'] ?? null;
    $profilePicture = $input['picture'] ?? null;
    $googleId = $input['sub'];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(['error' => 'Invalid email format'], 400);
    }

    // Check if user exists
    $user = $userModel->findByEmail($email);

    if ($user) {
        // User exists, update if needed
        $updateData = [];
        if ($name && (!isset($user->profile->name) || !$user->profile->name)) {
            $updateData['name'] = $name;
        }
        if ($profilePicture && (!isset($user->profile->profilePicture) || !$user->profile->profilePicture)) {
            $updateData['profilePicture'] = $profilePicture;
        }
        // Update auth provider if it was email before
        if ($user->authProvider === 'email') {
            $updateData['authProvider'] = 'google';
        }

        if (!empty($updateData)) {
            $userId = isset($user->privyId) ? $user->privyId : (string)$user->_id;
            $user = $userModel->update($userId, $updateData);
        }
    } else {
        // Wallet will be created by frontend via Privy SDK
        // Create new user without wallet initially
        $userData = [
            'email' => $email,
            'authProvider' => 'google',
            'name' => $name,
            'profilePicture' => $profilePicture
            // Wallet fields will be added later by /privy/create-wallet endpoint
        ];

        $user = $userModel->create($userData);

        if (!$user) {
            sendResponse(['error' => 'Failed to create user'], 500);
        }

        error_log("Google user created successfully: " . $email);

        // Send welcome email (async with delay is acceptable)
        try {
            $firstName = $name ? explode(' ', $name)[0] : explode('@', $email)[0];
            $firstName = ucfirst($firstName);
            $emailService->sendWelcomeEmail($email, $firstName);
        } catch (Exception $e) {
            error_log("Failed to queue welcome email: " . $e->getMessage());
        }
    }

    // Generate token using user ID (wallet will be added later by frontend)
    $userId = isset($user->privyId) ? $user->privyId : (string)$user->_id;
    $token = generateToken($userId);

    sendResponse([
        'success' => true,
        'message' => 'Google sign in successful',
        'token' => $token,
        'user' => $userModel->toArray($user)
    ]);

} catch (Exception $e) {
    error_log("Google auth error: " . $e->getMessage());
    sendResponse(['error' => 'Server error during Google authentication: ' . $e->getMessage()], 500);
}
