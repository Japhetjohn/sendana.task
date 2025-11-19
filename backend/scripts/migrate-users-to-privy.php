#!/usr/bin/env php
<?php
/**
 * CLI Script to migrate existing MongoDB users to Privy
 * Usage: php backend/scripts/migrate-users-to-privy.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/privy.php';
require_once __DIR__ . '/../models/User.php';

echo "===========================================\n";
echo "Privy User Migration Script\n";
echo "===========================================\n\n";

$userModel = new User();
$privyAuth = new PrivyAuth();

try {
    // Get all users from MongoDB
    echo "Connecting to MongoDB...\n";
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

    echo "Starting migration...\n\n";

    foreach ($cursor as $document) {
        $results['total']++;
        $email = $document->email;

        // Skip if user already has a Privy ID
        if (isset($document->privyId) && !empty($document->privyId)) {
            echo "  ✓ Skipping $email (already has Privy ID: {$document->privyId})\n";
            $results['skipped']++;
            continue;
        }

        try {
            // Create user in Privy
            echo "  → Migrating $email to Privy...\n";
            $privyUser = $privyAuth->createUser($email);

            if (!$privyUser || !isset($privyUser['id'])) {
                throw new Exception("Failed to create Privy user");
            }

            $privyUserId = $privyUser['id'];
            echo "    ✓ Created Privy user with ID: $privyUserId\n";

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
            echo "    ✓ Updated MongoDB record\n";

        } catch (Exception $e) {
            echo "    ✗ Failed: " . $e->getMessage() . "\n";
            $results['failed']++;
            $results['errors'][] = [
                'email' => $email,
                'error' => $e->getMessage()
            ];
        }

        echo "\n";
    }

    echo "===========================================\n";
    echo "Migration Summary\n";
    echo "===========================================\n";
    echo "Total users found:     {$results['total']}\n";
    echo "Successfully migrated: {$results['migrated']}\n";
    echo "Already had Privy ID:  {$results['skipped']}\n";
    echo "Failed:                {$results['failed']}\n";
    echo "===========================================\n";

    if (count($results['errors']) > 0) {
        echo "\nErrors:\n";
        foreach ($results['errors'] as $error) {
            echo "  - {$error['email']}: {$error['error']}\n";
        }
    }

    echo "\nMigration completed!\n";

} catch (Exception $e) {
    echo "ERROR: Migration failed - " . $e->getMessage() . "\n";
    exit(1);
}
