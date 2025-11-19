<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Migration-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/privy.php';
require_once __DIR__ . '/../../models/User.php';

$userModel = new User();
$privyAuth = new PrivyAuth();

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['error' => 'Method not allowed'], 405);
}

// Simple security check - require migration key
$headers = getallheaders();
$migrationKey = $headers['X-Migration-Key'] ?? $_POST['migration_key'] ?? null;

// Use a simple key for this one-time migration
if ($migrationKey !== 'migrate_sendana_2024') {
    sendResponse(['error' => 'Unauthorized - Invalid migration key'], 401);
}

try {
    // Get all users from MongoDB
    $manager = new MongoDB\Driver\Manager('mongodb+srv://easygasproject_db_user:kuulsinim45@sendana.3tnvvjr.mongodb.net/sendana-db?retryWrites=true&w=majority');
    $query = new MongoDB\Driver\Query([]);
    $cursor = $manager->executeQuery('sendana-db.users', $query);

    $results = [
        'total' => 0,
        'migrated' => 0,
        'skipped' => 0,
        'failed' => 0,
        'errors' => []
    ];

    foreach ($cursor as $document) {
        $results['total']++;
        $email = $document->email;

        // Skip if user already has a Privy ID
        if (isset($document->privyId) && !empty($document->privyId)) {
            error_log("User $email already has Privy ID, skipping");
            $results['skipped']++;
            continue;
        }

        try {
            // Create user in Privy
            error_log("Migrating user to Privy: $email");
            $privyUser = $privyAuth->createUser($email);

            if (!$privyUser || !isset($privyUser['id'])) {
                throw new Exception("Failed to create Privy user");
            }

            $privyUserId = $privyUser['id'];
            error_log("Created Privy user $email with ID: $privyUserId");

            // Update user in MongoDB with Privy ID
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->update(
                ['_id' => $document->_id],
                ['$set' => [
                    'privyId' => $privyUserId,
                    'migratedToPrivyAt' => new MongoDB\BSON\UTCDateTime()
                ]]
            );

            $manager->executeBulkWrite('sendana-db.users', $bulk);

            $results['migrated']++;
            error_log("Successfully migrated user: $email");

        } catch (Exception $e) {
            error_log("Failed to migrate user $email: " . $e->getMessage());
            $results['failed']++;
            $results['errors'][] = [
                'email' => $email,
                'error' => $e->getMessage()
            ];
        }
    }

    sendResponse([
        'success' => true,
        'message' => 'Migration completed',
        'results' => $results
    ]);

} catch (Exception $e) {
    error_log("Migration error: " . $e->getMessage());
    sendResponse(['error' => 'Migration failed: ' . $e->getMessage()], 500);
}
