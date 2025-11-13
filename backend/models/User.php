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
            'createdAt' => new MongoDB\BSON\UTCDateTime(),
            'updatedAt' => new MongoDB\BSON\UTCDateTime()
        ];

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

        return [
            'id' => (string) $user->_id,
            'privyId' => $user->privyId,
            'email' => $user->email,
            'authProvider' => $user->authProvider ?? 'email',
            'profile' => [
                'name' => $user->profile->name ?? null,
                'profilePicture' => $user->profile->profilePicture ?? null
            ],
            'balance' => [
                'USD' => $user->balance->USD ?? 0,
                'EUR' => $user->balance->EUR ?? 0,
                'GBP' => $user->balance->GBP ?? 0
            ],
            'stellarPublicKey' => $user->stellarPublicKey ?? null,
            'createdAt' => isset($user->createdAt) ? $user->createdAt->toDateTime()->format('c') : null
        ];
    }
}
