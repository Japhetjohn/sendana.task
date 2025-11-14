<?php
require_once __DIR__ . '/backend/vendor/autoload.php';

use Soneso\StellarSDK\Crypto\KeyPair;

echo "Testing Stellar SDK...\n\n";

try {
    echo "Creating random keypair...\n";
    $keyPair = KeyPair::random();

    $publicKey = $keyPair->getAccountId();
    $secretKey = $keyPair->getSecretSeed();

    echo "Success!\n";
    echo "Public Key: $publicKey\n";
    echo "Secret Key: $secretKey\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
