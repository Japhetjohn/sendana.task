<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Soneso\StellarSDK\Crypto\KeyPair;

class StellarService {
    public function createWallet() {
        try {
            $keyPair = KeyPair::random();

            $publicKey = $keyPair->getAccountId();
            $secretKey = $keyPair->getSecretSeed();

            error_log("Stellar wallet created - Public Key: $publicKey");

            return [
                'publicKey' => $publicKey,
                'secretKey' => $secretKey,
                'success' => true
            ];

        } catch (Exception $e) {
            error_log("Failed to create Stellar wallet: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function createAndFundTestnetWallet() {
        return $this->createWallet();
    }
}
