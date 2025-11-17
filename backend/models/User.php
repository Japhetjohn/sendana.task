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

    // Find user by MongoDB _id
    public function findById($id) {
        try {
            // Convert string ID to MongoDB ObjectId
            if (is_string($id)) {
                $id = new MongoDB\BSON\ObjectId($id);
            }
            return $this->db->findOne($this->collection, ['_id' => $id]);
        } catch (Exception $e) {
            error_log("Error finding user by ID: " . $e->getMessage());
            return null;
        }
    }

    // Create new user
    public function create($data) {
        $document = [
            'email' => $data['email'],
            'authProvider' => $data['authProvider'] ?? 'email',
            'profile' => [
                'name' => $data['name'] ?? null,
                'profilePicture' => $data['profilePicture'] ?? null
            ],
            'stellarPublicKey' => $data['stellarPublicKey'] ?? null,
            'stellarSecretKey' => $data['stellarSecretKey'] ?? null,
            'walletAddress' => null,
            'balance' => [
                'USD' => 0,
                'EUR' => 0,
                'GBP' => 0
            ],
            'transactions' => [],
        ];

        // Only add privyId if provided (not null)
        if (isset($data['privyId']) && $data['privyId'] !== null) {
            $document['privyId'] = $data['privyId'];
        }

        // Add privyWalletId if provided
        if (isset($data['privyWalletId']) && $data['privyWalletId'] !== null) {
            $document['privyWalletId'] = $data['privyWalletId'];
        }

        // MongoDB timestamps
        $document['createdAt'] = new MongoDB\BSON\UTCDateTime();
        $document['updatedAt'] = new MongoDB\BSON\UTCDateTime();

        // Add password hash if provided
        if (isset($data['passwordHash'])) {
            $document['passwordHash'] = $data['passwordHash'];
        }

        $result = $this->db->insertOne($this->collection, $document);

        // Fetch and return the created user by _id
        return $this->findById($result->getInsertedId());
    }

    // Update user
    public function update($identifier, $data) {
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
        if (isset($data['privyId'])) {
            $updateData['privyId'] = $data['privyId'];
        }
        if (isset($data['privyWalletId'])) {
            $updateData['privyWalletId'] = $data['privyWalletId'];
        }
        if (isset($data['migratedToPrivy'])) {
            $updateData['migratedToPrivy'] = $data['migratedToPrivy'];
        }

        $updateData['updatedAt'] = new MongoDB\BSON\UTCDateTime();

        // Determine if identifier is privyId or _id
        $filter = [];
        if (is_object($identifier) && get_class($identifier) === 'MongoDB\BSON\ObjectId') {
            $filter['_id'] = $identifier;
        } else if (is_string($identifier) && strlen($identifier) === 24 && ctype_xdigit($identifier)) {
            // Looks like a MongoDB ObjectId string
            try {
                $filter['_id'] = new MongoDB\BSON\ObjectId($identifier);
            } catch (Exception $e) {
                $filter['privyId'] = $identifier;
            }
        } else {
            $filter['privyId'] = $identifier;
        }

        $this->db->updateOne($this->collection, $filter, $updateData);

        // Return updated user - try by privyId first, then by _id
        if (isset($filter['privyId'])) {
            return $this->findByPrivyId($filter['privyId']);
        } else {
            return $this->findById($filter['_id']);
        }
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
