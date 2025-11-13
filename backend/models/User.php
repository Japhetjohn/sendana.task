<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    private $collection = 'users';

    public function __construct() {
        $this->db = new Database();
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

        // MongoDB timestamps
        $document['createdAt'] = new MongoDB\BSON\UTCDateTime();
        $document['updatedAt'] = new MongoDB\BSON\UTCDateTime();

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

        $updateData['updatedAt'] = new MongoDB\BSON\UTCDateTime();

        $this->db->updateOne($this->collection, ['privyId' => $privyId], $updateData);

        return $this->findByPrivyId($privyId);
    }

    // Convert MongoDB object to array for JSON response
    public function toArray($user) {
        if (!$user) return null;

        // Convert MongoDB object to array
        $user = is_object($user) ? (array) $user : $user;

        $result = [
            'id' => (string)($user['_id'] ?? uniqid()),
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

        // Handle MongoDB createdAt
        if (isset($user['createdAt']) && is_object($user['createdAt']) && method_exists($user['createdAt'], 'toDateTime')) {
            $result['createdAt'] = $user['createdAt']->toDateTime()->format('c');
        }

        return $result;
    }
}
