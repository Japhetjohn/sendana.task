<?php
// Privy Authentication Configuration
class PrivyAuth {
    private $app_id;
    private $app_secret;
    private $api_base_url = 'https://auth.privy.io';

    public function __construct() {
        $this->app_id = 'cmhow02lw00b3l10cz7f0gbpu';
        $this->app_secret = '3hRZCYhv4CP9iRsT33GVD8TCtzJhAmooMaQ94CWvDXbwSS75wvgbKuCMbFLfLgCfacSRwxyfK11qq6jNjh3BCciE';
    }

    // Verify Privy access token
    public function verifyToken($token) {
        try {
            $url = $this->api_base_url . '/v1/tokens/verify';

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->app_secret
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'token' => $token
            ]));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                return null;
            }

            $data = json_decode($response, true);
            return $data;
        } catch (Exception $e) {
            error_log("Privy token verification error: " . $e->getMessage());
            return null;
        }
    }

    // Get user from Privy
    public function getUser($userId) {
        try {
            $url = $this->api_base_url . '/v1/users/' . $userId;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->app_secret,
                'privy-app-id: ' . $this->app_id
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                return null;
            }

            $data = json_decode($response, true);
            return $data;
        } catch (Exception $e) {
            error_log("Privy get user error: " . $e->getMessage());
            return null;
        }
    }

    public function getAppId() {
        return $this->app_id;
    }

    // Create Stellar wallet via Privy
    public function createStellarWallet($userId = null) {
        try {
            $url = $this->api_base_url . '/v1/wallets';

            $requestBody = [
                'chain_type' => 'stellar'
            ];

            // If userId provided, link wallet to user
            if ($userId) {
                $requestBody['owner_id'] = $userId;
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: ' . $this->getAuthHeader(),
                'privy-app-id: ' . $this->app_id,
                'privy-idempotency-key: ' . uniqid('stellar_wallet_', true)
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            error_log("Privy create Stellar wallet response code: " . $httpCode);
            error_log("Privy create Stellar wallet response: " . $response);

            if ($httpCode !== 200 && $httpCode !== 201) {
                error_log("Privy create Stellar wallet failed with code " . $httpCode);
                return null;
            }

            $data = json_decode($response, true);
            return $data;
        } catch (Exception $e) {
            error_log("Privy create Stellar wallet error: " . $e->getMessage());
            return null;
        }
    }

    // Get Stellar wallet by wallet ID
    public function getStellarWallet($walletId) {
        try {
            $url = $this->api_base_url . '/v1/wallets/' . $walletId;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: ' . $this->getAuthHeader(),
                'privy-app-id: ' . $this->app_id
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                error_log("Privy get Stellar wallet failed with code " . $httpCode);
                return null;
            }

            $data = json_decode($response, true);
            return $data;
        } catch (Exception $e) {
            error_log("Privy get Stellar wallet error: " . $e->getMessage());
            return null;
        }
    }

    // Get authorization header using Basic Auth
    private function getAuthHeader() {
        $credentials = base64_encode($this->app_id . ':' . $this->app_secret);
        return 'Basic ' . $credentials;
    }
}
