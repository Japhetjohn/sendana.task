<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/privy.php';
require_once __DIR__ . '/../models/User.php';

$userModel = new User();
$privyAuth = new PrivyAuth();

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

// Simple admin authentication - you can enhance this
$adminKey = $_GET['admin_key'] ?? '';
if ($adminKey !== 'migrate_stellar_to_privy_2025') {
    sendResponse(['error' => 'Unauthorized'], 401);
}

try {
    // Get all users without Privy wallet ID
    $db = Database::getInstance();
    $usersCollection = $db->getCollection('users');

    // Find users who have Stellar wallets but no Privy wallet ID
    $usersToMigrate = $usersCollection->find([
        'stellarPublicKey' => ['$exists' => true],
        '$or' => [
            ['privyWalletId' => ['$exists' => false]],
            ['privyWalletId' => null]
        ]
    ])->toArray();

    $migratedCount = 0;
    $failedCount = 0;
    $results = [];

    foreach ($usersToMigrate as $user) {
        try {
            $privyUserId = $user->privyId ?? ('user_' . bin2hex(random_bytes(16)));

            // Create Privy Stellar wallet
            $stellarWallet = $privyAuth->createStellarWallet($privyUserId);

            if ($stellarWallet && isset($stellarWallet['address'])) {
                // Update user with new Privy wallet
                $updateData = [
                    'privyWalletId' => $stellarWallet['id'],
                    'stellarPublicKey' => $stellarWallet['address'],
                    'privyId' => $privyUserId,
                    'migratedToPrivy' => true,
                    'migrationDate' => new MongoDB\BSON\UTCDateTime()
                ];

                $usersCollection->updateOne(
                    ['_id' => $user->_id],
                    ['$set' => $updateData]
                );

                $migratedCount++;
                $results[] = [
                    'email' => $user->email ?? 'N/A',
                    'oldWallet' => $user->stellarPublicKey ?? 'N/A',
                    'newWallet' => $stellarWallet['address'],
                    'privyWalletId' => $stellarWallet['id'],
                    'status' => 'success'
                ];

                error_log("Migrated user {$user->email} to Privy wallet: " . $stellarWallet['address']);
            } else {
                $failedCount++;
                $results[] = [
                    'email' => $user->email ?? 'N/A',
                    'status' => 'failed',
                    'error' => 'Failed to create Privy wallet'
                ];
                error_log("Failed to migrate user {$user->email} to Privy");
            }

            // Rate limit to avoid overwhelming Privy API
            usleep(500000); // 0.5 second delay between requests

        } catch (Exception $e) {
            $failedCount++;
            $results[] = [
                'email' => $user->email ?? 'N/A',
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
            error_log("Migration error for user {$user->email}: " . $e->getMessage());
        }
    }

    sendResponse([
        'success' => true,
        'message' => 'Migration completed',
        'totalUsers' => count($usersToMigrate),
        'migrated' => $migratedCount,
        'failed' => $failedCount,
        'results' => $results
    ]);

} catch (Exception $e) {
    error_log("Migration script error: " . $e->getMessage());
    sendResponse(['error' => 'Migration failed: ' . $e->getMessage()], 500);
}
