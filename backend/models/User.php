<?php
// Try to use MongoDB if available, otherwise use JSON database
try {
    if (class_exists('MongoDB\Driver\Manager')) {
        require_once __DIR__ . '/../config/database.php';
        $useJsonDb = false;
    } else {
        throw new Exception('MongoDB not available');
    }
} catch (Exception $e) {
    require_once __DIR__ . '/../config/json_database.php';
    $useJsonDb = true;
}

class User {
    private $db;
    private $collection = 'users';
    private $useJsonDb;

    public function __construct() {
        try {
            if (class_exists('MongoDB\Driver\Manager')) {
                $this->db = new Database();
                $this->useJsonDb = false;
            } else {
                throw new Exception('MongoDB not available');
            }
        } catch (Exception $e) {
            $this->db = new JsonDatabase();
            $this->useJsonDb = true;
        }
    }

    // Find user by Privy ID
    public function findByPrivyId($privyId) {
        return $this->db->findOne($this->collection, ['privyId' => $privyId]);
    }

    // Find user by email
    public function findByEmail($email) {
        return $this->db->findOne($this->collection, ['email' => $email]);
    }

    // Create new user
    public function create($data) {
        $document = [
            'privyId' => $data['privyId'],
            'email' => $data['email'],
            'authProvider' => $data['authProvider'] ?? 'email',
            'profile' => [
                'name' => $data['name'] ?? null,
                'profilePicture' => $data['profilePicture'] ?? null
            ],
            'stellarPublicKey' => null,
            'stellarSecretKey' => null,
            'walletAddress' => null,
            'balance' => [
                'USD' => 0,
                'EUR' => 0,
                'GBP' => 0
            ],
            'transactions' => [],
        ];

        // Handle createdAt and updatedAt based on database type
        if ($this->useJsonDb) {
            $document['createdAt'] = time();
            $document['updatedAt'] = time();
        } else {
            $document['createdAt'] = new MongoDB\BSON\UTCDateTime();
            $document['updatedAt'] = new MongoDB\BSON\UTCDateTime();
        }

        // Add password hash if provided
        if (isset($data['passwordHash'])) {
            $document['passwordHash'] = $data['passwordHash'];
        }

        $result = $this->db->insertOne($this->collection, $document);

        // Fetch and return the created user
        return $this->findByPrivyId($data['privyId']);
    }

    // Update user
    public function update($privyId, $data) {
        $updateData = [];

        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }
        if (isset($data['authProvider'])) {
            $updateData['authProvider'] = $data['authProvider'];
        }
        if (isset($data['name'])) {
            $updateData['profile.name'] = $data['name'];
        }
        if (isset($data['profilePicture'])) {
            $updateData['profile.profilePicture'] = $data['profilePicture'];
        }
        if (isset($data['stellarPublicKey'])) {
            $updateData['stellarPublicKey'] = $data['stellarPublicKey'];
        }
        if (isset($data['stellarSecretKey'])) {
            $updateData['stellarSecretKey'] = $data['stellarSecretKey'];
        }

        if ($this->useJsonDb) {
            $updateData['updatedAt'] = time();
        } else {
            $updateData['updatedAt'] = new MongoDB\BSON\UTCDateTime();
        }

        $this->db->updateOne($this->collection, ['privyId' => $privyId], $updateData);

        return $this->findByPrivyId($privyId);
    }

    // Convert database object to array for JSON response
    public function toArray($user) {
        if (!$user) return null;

        // Handle both object and array (from JSON database)
        $user = is_object($user) ? (array) $user : $user;

        $result = [
            'id' => $user['_id'] ?? uniqid(),
            'privyId' => $user['privyId'] ?? null,
            'email' => $user['email'] ?? null,
            'authProvider' => $user['authProvider'] ?? 'email',
            'profile' => [
                'name' => null,
                'profilePicture' => null
            ],
            'balance' => [
                'USD' => 0,
                'EUR' => 0,
                'GBP' => 0
            ],
            'stellarPublicKey' => $user['stellarPublicKey'] ?? null,
            'createdAt' => null
        ];

        // Handle nested profile
        if (isset($user['profile'])) {
            $profile = is_object($user['profile']) ? (array) $user['profile'] : $user['profile'];
            $result['profile']['name'] = $profile['name'] ?? null;
            $result['profile']['profilePicture'] = $profile['profilePicture'] ?? null;
        }

        // Handle nested balance
        if (isset($user['balance'])) {
            $balance = is_object($user['balance']) ? (array) $user['balance'] : $user['balance'];
            $result['balance']['USD'] = $balance['USD'] ?? 0;
            $result['balance']['EUR'] = $balance['EUR'] ?? 0;
            $result['balance']['GBP'] = $balance['GBP'] ?? 0;
        }

        // Handle createdAt
        if (isset($user['createdAt'])) {
            if (is_numeric($user['createdAt'])) {
                // JSON database timestamp
                $result['createdAt'] = date('c', $user['createdAt']);
            } elseif (is_object($user['createdAt']) && method_exists($user['createdAt'], 'toDateTime')) {
                // MongoDB date
                $result['createdAt'] = $user['createdAt']->toDateTime()->format('c');
            }
        }

        return $result;
    }
}
