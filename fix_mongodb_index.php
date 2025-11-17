<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Fixing MongoDB privyId index...\n";
echo "================================\n\n";

try {
    // MongoDB connection details
    $mongodb_uri = 'mongodb+srv://easygasproject_db_user:kuulsinim45@sendana.3tnvvjr.mongodb.net/sendana-db?retryWrites=true&w=majority';
    $db_name = 'sendana-db';
    $collection_name = 'users';

    // Create MongoDB Manager (low-level driver)
    $manager = new MongoDB\Driver\Manager($mongodb_uri);
    echo "✓ Connected to MongoDB\n\n";

    // Drop the old index
    echo "Dropping old privyId_1 index...\n";
    $command = new MongoDB\Driver\Command([
        'dropIndexes' => $collection_name,
        'index' => 'privyId_1'
    ]);

    try {
        $result = $manager->executeCommand($db_name, $command);
        $response = current($result->toArray());
        echo "✓ Old index dropped: " . json_encode($response) . "\n\n";
    } catch (Exception $e) {
        echo "Note: " . $e->getMessage() . "\n\n";
    }

    // Create new sparse unique index
    echo "Creating sparse unique index on privyId...\n";
    $command = new MongoDB\Driver\Command([
        'createIndexes' => $collection_name,
        'indexes' => [
            [
                'key' => ['privyId' => 1],
                'name' => 'privyId_1',
                'unique' => true,
                'sparse' => true  // Only enforce uniqueness on docs that have privyId
            ]
        ]
    ]);

    $result = $manager->executeCommand($db_name, $command);
    $response = current($result->toArray());
    echo "✓ Sparse unique index created: " . json_encode($response) . "\n\n";

    // List indexes to verify
    echo "Verifying indexes...\n";
    $command = new MongoDB\Driver\Command([
        'listIndexes' => $collection_name
    ]);

    $result = $manager->executeCommand($db_name, $command);
    $indexes = $result->toArray();

    foreach ($indexes as $index) {
        echo "- " . $index->name . ": ";
        echo json_encode($index->key);
        if (isset($index->sparse) && $index->sparse) {
            echo " (SPARSE)";
        }
        if (isset($index->unique) && $index->unique) {
            echo " (UNIQUE)";
        }
        echo "\n";
    }

    echo "\n✅ MongoDB index fixed successfully!\n";
    echo "Now users without privyId can be created without conflicts.\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
