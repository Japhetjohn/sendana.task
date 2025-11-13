<?php
// MongoDB Connection Configuration
class Database {
    private $connection;
    private $database;

    // MongoDB connection string from environment
    private $mongodb_uri = 'mongodb+srv://easygasproject_db_user:kuulsinim45@sendana.3tnvvjr.mongodb.net/sendana-db?retryWrites=true&w=majority';
    private $db_name = 'sendana-db';

    public function __construct() {
        try {
            // Create MongoDB client
            $this->connection = new MongoDB\Driver\Manager($this->mongodb_uri);
            echo "MongoDB connected successfully\n";
        } catch (Exception $e) {
            die("MongoDB connection error: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function getDatabase() {
        return $this->db_name;
    }

    // Execute a query
    public function executeQuery($collection, $filter = [], $options = []) {
        $query = new MongoDB\Driver\Query($filter, $options);
        $namespace = $this->db_name . '.' . $collection;
        return $this->connection->executeQuery($namespace, $query);
    }

    // Insert document
    public function insertOne($collection, $document) {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert($document);
        $namespace = $this->db_name . '.' . $collection;
        return $this->connection->executeBulkWrite($namespace, $bulk);
    }

    // Update document
    public function updateOne($collection, $filter, $update) {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->update($filter, ['$set' => $update], ['multi' => false, 'upsert' => false]);
        $namespace = $this->db_name . '.' . $collection;
        return $this->connection->executeBulkWrite($namespace, $bulk);
    }

    // Find one document
    public function findOne($collection, $filter) {
        $query = new MongoDB\Driver\Query($filter, ['limit' => 1]);
        $namespace = $this->db_name . '.' . $collection;
        $cursor = $this->connection->executeQuery($namespace, $query);
        $result = current($cursor->toArray());
        return $result ? $result : null;
    }
}
