<?php
// Test Privy Stellar Wallet Creation
require_once __DIR__ . '/backend/config/privy.php';

echo "Testing Privy Stellar Wallet Creation...\n\n";

$privyAuth = new PrivyAuth();

// Test: Create a Stellar wallet
echo "1. Creating Stellar wallet via Privy...\n";
$testUserId = 'test_stellar_' . time();
$result = $privyAuth->createStellarWallet($testUserId);

if ($result && isset($result['address'])) {
    echo "✅ Stellar wallet created successfully!\n";
    echo "   Wallet ID: " . ($result['id'] ?? 'N/A') . "\n";
    echo "   Address: " . $result['address'] . "\n";
    echo "   Chain Type: " . ($result['chain_type'] ?? 'N/A') . "\n";
    echo "   Owner ID: " . ($result['owner_id'] ?? 'N/A') . "\n\n";

    // Test: Get the wallet by ID
    if (isset($result['id'])) {
        echo "2. Fetching wallet by ID...\n";
        $walletId = $result['id'];
        $fetchedWallet = $privyAuth->getStellarWallet($walletId);

        if ($fetchedWallet && isset($fetchedWallet['address'])) {
            echo "✅ Wallet fetched successfully!\n";
            echo "   Address matches: " . ($fetchedWallet['address'] === $result['address'] ? "Yes" : "No") . "\n";
        } else {
            echo "❌ Failed to fetch wallet\n";
        }
    }
} else {
    echo "❌ Stellar wallet creation failed!\n";
    echo "Response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
}
