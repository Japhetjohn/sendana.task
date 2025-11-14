<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Soneso\StellarSDK\KeyPair;
use Soneso\StellarSDK\Network;
use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\Util\FriendBot;

class StellarService {
    private $isTestnet;
    private $sdk;

    public function __construct($testnet = true) {
        $this->isTestnet = $testnet;

        if ($testnet) {
            Network::useTestNetwork();
            $this->sdk = StellarSDK::getTestNetInstance();
        } else {
            Network::usePublicNetwork();
            $this->sdk = StellarSDK::getPublicNetInstance();
        }
    }

    /**
     * Create a new Stellar wallet (keypair)
     * Returns array with publicKey and secretKey
     */
    public function createWallet() {
        try {
            // Generate a random keypair
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

    /**
     * Fund a testnet account using Friendbot
     * This activates the account on the Stellar testnet
     */
    public function fundTestnetAccount($publicKey) {
        if (!$this->isTestnet) {
            return [
                'success' => false,
                'error' => 'Friendbot only works on testnet'
            ];
        }

        try {
            $funded = FriendBot::fundTestAccount($publicKey);

            if ($funded) {
                error_log("Testnet account funded successfully: $publicKey");
                return [
                    'success' => true,
                    'message' => 'Account funded with 10,000 XLM on testnet'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to fund account'
                ];
            }

        } catch (Exception $e) {
            error_log("Failed to fund testnet account: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create and fund a complete testnet wallet
     * Returns wallet details with funding status
     */
    public function createAndFundTestnetWallet() {
        // Create the wallet
        $wallet = $this->createWallet();

        if (!$wallet['success']) {
            return $wallet;
        }

        // Fund it on testnet
        $fundingResult = $this->fundTestnetAccount($wallet['publicKey']);

        return [
            'success' => true,
            'publicKey' => $wallet['publicKey'],
            'secretKey' => $wallet['secretKey'],
            'funded' => $fundingResult['success'],
            'fundingMessage' => $fundingResult['message'] ?? $fundingResult['error'] ?? null
        ];
    }

    /**
     * Get account balance
     */
    public function getAccountBalance($publicKey) {
        try {
            $account = $this->sdk->requestAccount($publicKey);

            $balances = [];
            foreach ($account->getBalances() as $balance) {
                $balances[] = [
                    'asset' => $balance->getAssetType() === 'native' ? 'XLM' : $balance->getAssetCode(),
                    'balance' => $balance->getBalance()
                ];
            }

            return [
                'success' => true,
                'balances' => $balances
            ];

        } catch (Exception $e) {
            error_log("Failed to get account balance: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
