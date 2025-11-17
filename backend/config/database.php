<?php
// Database Connection Configuration
// Auto-detects MongoDB availability and falls back to JSON database

// Check if MongoDB extension is available
if (extension_loaded('mongodb')) {
    // Use MongoDB if available
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
                error_log("Using MongoDB database");
            } catch (Exception $e) {
                error_log("MongoDB connection error: " . $e->getMessage());
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
} else {
    // Fall back to JSON database if MongoDB is not available
    error_log("MongoDB extension not found, using JSON file database");

    class Database {
        private $dataDir;
        private $usersFile;

        public function __construct() {
            $this->dataDir = __DIR__ . '/../data';
            $this->usersFile = $this->dataDir . '/users.json';

            // Create data directory if it doesn't exist
            if (!is_dir($this->dataDir)) {
                mkdir($this->dataDir, 0755, true);
            }

            // Create users file if it doesn't exist
            if (!file_exists($this->usersFile)) {
                file_put_contents($this->usersFile, json_encode([]));
            }
        }

        // Read all users
        private function readUsers() {
            $data = file_get_contents($this->usersFile);
            return json_decode($data, true) ?: [];
        }

        // Write all users
        private function writeUsers($users) {
            file_put_contents($this->usersFile, json_encode($users, JSON_PRETTY_PRINT));
        }

        // Find one user by filter
        public function findOne($collection, $filter) {
            if ($collection !== 'users') return null;

            $users = $this->readUsers();

            foreach ($users as $user) {
                $match = true;
                foreach ($filter as $key => $value) {
                    if (!isset($user[$key]) || $user[$key] !== $value) {
                        $match = false;
                        break;
                    }
                }
                if ($match) {
                    return (object) $user;
                }
            }

            return null;
        }

        // Insert one document
        public function insertOne($collection, $document) {
            if ($collection !== 'users') {
                return (object)['inserted_id' => null];
            }

            $users = $this->readUsers();

            // Convert timestamps
            if (isset($document['createdAt'])) {
                $document['createdAt'] = time();
            }
            if (isset($document['updatedAt'])) {
                $document['updatedAt'] = time();
            }

            // Generate ID
            $id = uniqid('user_', true);
            $document['_id'] = $id;

            $users[] = $document;
            $this->writeUsers($users);

            // Return object with getInsertedId method
            return (object)[
                'inserted_id' => $id,
                'getInsertedId' => function() use ($id) { return $id; }
            ];
        }

        // Update one document
        public function updateOne($collection, $filter, $update) {
            if ($collection !== 'users') return false;

            $users = $this->readUsers();

            foreach ($users as $index => $user) {
                $match = true;
                foreach ($filter as $key => $value) {
                    if (!isset($user[$key]) || $user[$key] !== $value) {
                        $match = false;
                        break;
                    }
                }

                if ($match) {
                    // Apply updates
                    foreach ($update as $key => $value) {
                        if ($key === 'updatedAt') {
                            $users[$index][$key] = time();
                        } else {
                            $users[$index][$key] = $value;
                        }
                    }

                    $this->writeUsers($users);
                    return true;
                }
            }

            return false;
        }

        public function getConnection() {
            return null;
        }

        public function getDatabase() {
            return 'json-file-db';
        }
    }
}
