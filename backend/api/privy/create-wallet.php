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
require_once __DIR__ . '/../../config/privy.php';
require_once __DIR__ . '/../../models/User.php';

$method = $_SERVER['REQUEST_METHOD'];

$userModel = new User();
$privyAuth = new PrivyAuth();

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

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
        if (time() - $decoded['timestamp'] > 86400) {
            return null;
        }
        return $decoded['userId'];
    } catch (Exception $e) {
        return null;
    }
}

// POST /privy/create-wallet - Create Privy Stellar wallet for authenticated user
if ($method === 'POST') {
    try {
        // Get and verify auth token
        $token = getAuthToken();
        if (!$token) {
            sendResponse(['error' => 'Authorization required'], 401);
        }

        $userId = verifyToken($token);
        if (!$userId) {
            sendResponse(['error' => 'Invalid or expired token'], 401);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $chainType = $input['chainType'] ?? 'stellar';

        if ($chainType !== 'stellar') {
            sendResponse(['error' => 'Only Stellar chain is supported'], 400);
        }

        // Find user
        $user = $userModel->findById($userId);
        if (!$user) {
            $user = $userModel->findByPrivyId($userId);
        }
        if (!$user) {
            $user = $userModel->findByEmail($userId);
        }

        if (!$user) {
            sendResponse(['error' => 'User not found'], 404);
        }

        // Check if user already has a wallet
        if (!empty($user->stellarPublicKey)) {
            sendResponse([
                'success' => true,
                'message' => 'Wallet already exists',
                'wallet' => [
                    'address' => $user->stellarPublicKey,
                    'id' => $user->privyWalletId ?? null
                ]
            ]);
        }

        // Create Stellar wallet via Privy
        error_log("Creating Privy Stellar wallet for user: " . $user->email);
        $stellarWallet = $privyAuth->createStellarWallet();

        if (!$stellarWallet || !isset($stellarWallet['address'])) {
            error_log("Failed to create Privy Stellar wallet for user: " . $user->email);
            sendResponse(['error' => 'Failed to create wallet'], 500);
        }

        $walletAddress = $stellarWallet['address'];
        $walletId = $stellarWallet['id'] ?? null;
        $privyOwnerId = $stellarWallet['owner_id'] ?? null;

        error_log("Privy Stellar wallet created: " . $walletAddress . " (ID: " . $walletId . ", Owner: " . $privyOwnerId . ")");

        // Update user with wallet info
        $updateData = [
            'stellarPublicKey' => $walletAddress,
            'privyWalletId' => $walletId,
            'privyId' => $privyOwnerId,
            'migratedToPrivy' => true
        ];

        $updateKey = $user->privyId ?? $user->_id;
        $userModel->update($updateKey, $updateData);

        sendResponse([
            'success' => true,
            'message' => 'Wallet created successfully',
            'wallet' => [
                'address' => $walletAddress,
                'id' => $walletId,
                'ownerId' => $privyOwnerId
            ]
        ]);

    } catch (Exception $e) {
        error_log("Wallet creation error: " . $e->getMessage());
        sendResponse(['error' => 'Server error: ' . $e->getMessage()], 500);
    }
} else {
    sendResponse(['error' => 'Method not allowed'], 405);
}
