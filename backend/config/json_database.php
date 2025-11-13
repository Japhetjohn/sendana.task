<?php
// Simple JSON File-Based Database (for development/testing without MongoDB)
class JsonDatabase {
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
        if ($collection !== 'users') return false;

        $users = $this->readUsers();

        // Convert MongoDB Date objects to timestamps
        if (isset($document['createdAt'])) {
            $document['createdAt'] = time();
        }
        if (isset($document['updatedAt'])) {
            $document['updatedAt'] = time();
        }

        // Generate ID
        $document['_id'] = uniqid('user_', true);

        $users[] = $document;
        $this->writeUsers($users);

        return true;
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
                    // Handle nested keys (e.g., 'profile.name')
                    if (strpos($key, '.') !== false) {
                        $parts = explode('.', $key);
                        if (count($parts) == 2) {
                            if (!isset($users[$index][$parts[0]])) {
                                $users[$index][$parts[0]] = [];
                            }
                            $users[$index][$parts[0]][$parts[1]] = $value;
                        }
                    } else {
                        if ($key === 'updatedAt') {
                            $users[$index][$key] = time();
                        } else {
                            $users[$index][$key] = $value;
                        }
                    }
                }

                $this->writeUsers($users);
                return true;
            }
        }

        return false;
    }
}
