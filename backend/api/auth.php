<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/privy.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../services/EmailService.php';
require_once __DIR__ . '/../services/StellarService.php';

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Initialize services
$userModel = new User();
$privyAuth = new PrivyAuth();
$emailService = new EmailService();
$stellarService = new StellarService(true); // true = testnet

// Helper function to get authorization token
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

// Helper function to send JSON response
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

// Helper function to generate token
function generateToken($userId) {
    return base64_encode(json_encode([
        'userId' => $userId,
        'timestamp' => time(),
        'random' => bin2hex(random_bytes(16))
    ]));
}

// Helper function to verify token
function verifyToken($token) {
    try {
        $decoded = json_decode(base64_decode($token), true);
        if (!$decoded || !isset($decoded['userId'])) {
            return null;
        }
        // Token expires after 24 hours
        if (time() - $decoded['timestamp'] > 86400) {
            return null;
        }
        return $decoded['userId'];
    } catch (Exception $e) {
        return null;
    }
}

// POST /signup - Register new user
if ($method === 'POST' && strpos($path, '/signup') !== false) {
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

        // Check if user already exists
        $existingUser = $userModel->findByEmail($email);
        if ($existingUser) {
            sendResponse(['error' => 'Email already registered'], 409);
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Create Stellar wallet
        $stellarWallet = $stellarService->createAndFundTestnetWallet();

        if (!$stellarWallet['success']) {
            error_log("Failed to create Stellar wallet: " . ($stellarWallet['error'] ?? 'Unknown error'));
            sendResponse(['error' => 'Failed to create wallet'], 500);
        }

        // Create user
        $userData = [
            'privyId' => 'user_' . bin2hex(random_bytes(16)),
            'email' => $email,
            'passwordHash' => $passwordHash,
            'authProvider' => 'email',
            'stellarPublicKey' => $stellarWallet['publicKey'],
            'stellarSecretKey' => $stellarWallet['secretKey']
        ];

        $user = $userModel->create($userData);

        if (!$user) {
            sendResponse(['error' => 'Failed to create user'], 500);
        }

        error_log("Stellar wallet created and funded for user: " . $stellarWallet['publicKey']);

        // Generate token
        $token = generateToken($user->privyId);

        // Send welcome email (async with delay is acceptable)
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
}

// POST /login - Handle user login
elseif ($method === 'POST' && strpos($path, '/login') !== false) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['email']) || !isset($input['password'])) {
            sendResponse(['error' => 'Email and password are required'], 400);
        }

        $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            sendResponse(['error' => 'Invalid email format'], 400);
        }

        // Find user by email
        $user = $userModel->findByEmail($email);

        if (!$user) {
            sendResponse(['error' => 'Invalid email or password'], 401);
        }

        // Verify password
        if (!isset($user->passwordHash) || !password_verify($input['password'], $user->passwordHash)) {
            sendResponse(['error' => 'Invalid email or password'], 401);
        }

        // Generate token
        $token = generateToken($user->privyId);

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
}

// GET /user - Get current user data
elseif ($method === 'GET' && strpos($path, '/user') !== false) {
    try {
        $token = getAuthToken();
        if (!$token) {
            sendResponse(['error' => 'No token provided'], 401);
        }

        $userId = verifyToken($token);
        if (!$userId) {
            sendResponse(['error' => 'Invalid or expired token'], 401);
        }

        $user = $userModel->findByPrivyId($userId);

        if (!$user) {
            sendResponse(['error' => 'User not found'], 404);
        }

        sendResponse([
            'success' => true,
            'user' => $userModel->toArray($user)
        ]);

    } catch (Exception $e) {
        error_log("Get user error: " . $e->getMessage());
        sendResponse(['error' => 'Server error fetching user'], 500);
    }
}

// PUT /user - Update user profile
elseif ($method === 'PUT' && strpos($path, '/user') !== false) {
    try {
        $token = getAuthToken();
        if (!$token) {
            sendResponse(['error' => 'No token provided'], 401);
        }

        $userId = verifyToken($token);
        if (!$userId) {
            sendResponse(['error' => 'Invalid or expired token'], 401);
        }

        $user = $userModel->findByPrivyId($userId);

        if (!$user) {
            sendResponse(['error' => 'User not found'], 404);
        }

        // Get request body
        $input = json_decode(file_get_contents('php://input'), true);

        $updateData = [];
        if (isset($input['name'])) $updateData['name'] = $input['name'];
        if (isset($input['profilePicture'])) $updateData['profilePicture'] = $input['profilePicture'];
        if (isset($input['stellarPublicKey'])) $updateData['stellarPublicKey'] = $input['stellarPublicKey'];
        if (isset($input['stellarSecretKey'])) $updateData['stellarSecretKey'] = $input['stellarSecretKey'];

        $updatedUser = $userModel->update($userId, $updateData);

        sendResponse([
            'success' => true,
            'user' => $userModel->toArray($updatedUser)
        ]);

    } catch (Exception $e) {
        error_log("Update user error: " . $e->getMessage());
        sendResponse(['error' => 'Server error updating user'], 500);
    }
}

// POST /auth/google - Handle Google OAuth
elseif ($method === 'POST' && strpos($path, '/auth/google') !== false) {
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
                $user = $userModel->update($user->privyId, $updateData);
            }
        } else {
            // Create Stellar wallet for new user
            $stellarWallet = $stellarService->createAndFundTestnetWallet();

            if (!$stellarWallet['success']) {
                error_log("Failed to create Stellar wallet: " . ($stellarWallet['error'] ?? 'Unknown error'));
                sendResponse(['error' => 'Failed to create wallet'], 500);
            }

            // Create new user
            $userData = [
                'privyId' => 'google_' . $googleId,
                'email' => $email,
                'authProvider' => 'google',
                'name' => $name,
                'profilePicture' => $profilePicture,
                'stellarPublicKey' => $stellarWallet['publicKey'],
                'stellarSecretKey' => $stellarWallet['secretKey']
            ];

            $user = $userModel->create($userData);

            if (!$user) {
                sendResponse(['error' => 'Failed to create user'], 500);
            }

            error_log("Stellar wallet created and funded for Google user: " . $stellarWallet['publicKey']);

            // Send welcome email (async with delay is acceptable)
            try {
                $firstName = $name ? explode(' ', $name)[0] : explode('@', $email)[0];
                $firstName = ucfirst($firstName);
                $emailService->sendWelcomeEmail($email, $firstName);
            } catch (Exception $e) {
                error_log("Failed to queue welcome email: " . $e->getMessage());
            }
        }

        // Generate token
        $token = generateToken($user->privyId);

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
}

// GET /wallet - Get user's wallet information
elseif ($method === 'GET' && strpos($path, '/wallet') !== false) {
    try {
        $token = getAuthToken();
        if (!$token) {
            sendResponse(['error' => 'No token provided'], 401);
        }

        $userId = verifyToken($token);
        if (!$userId) {
            sendResponse(['error' => 'Invalid or expired token'], 401);
        }

        $user = $userModel->findByPrivyId($userId);

        if (!$user) {
            sendResponse(['error' => 'User not found'], 404);
        }

        // Return wallet information
        sendResponse([
            'success' => true,
            'wallet' => [
                'address' => $user->stellarPublicKey ?? null,
                'publicKey' => $user->stellarPublicKey ?? null,
                'balance' => $user->balance ?? ['USD' => 0, 'EUR' => 0, 'GBP' => 0],
                'email' => $user->email ?? null
            ]
        ]);

    } catch (Exception $e) {
        error_log("Get wallet error: " . $e->getMessage());
        sendResponse(['error' => 'Server error fetching wallet'], 500);
    }
}

// GET /auth/privy-config - Get Privy app ID for frontend
elseif ($method === 'GET' && strpos($path, '/auth/privy-config') !== false) {
    sendResponse([
        'appId' => $privyAuth->getAppId()
    ]);
}

// Route not found
else {
    sendResponse(['error' => 'Route not found'], 404);
}
