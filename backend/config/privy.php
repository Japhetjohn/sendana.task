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

    // Generate a CUID2-compatible ID for Privy
    private function generateCuid2() {
        // CUID2 format: lowercase alphanumeric, starts with letter, ~24-32 chars
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $letters = 'abcdefghijklmnopqrstuvwxyz';

        // Start with a letter
        $cuid = $letters[random_int(0, 25)];

        // Add random characters (total length ~24)
        for ($i = 0; $i < 23; $i++) {
            $cuid .= $chars[random_int(0, 35)];
        }

        return $cuid;
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

            // For embedded wallets, don't send owner_id - Privy manages this
            $requestBody = [
                'chain_type' => 'stellar'
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);  // 10 second timeout
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);  // 5 second connection timeout
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: ' . $this->getAuthHeader(),
                'privy-app-id: ' . $this->app_id,
                'privy-idempotency-key: ' . uniqid('stellar_wallet_', true)
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));

            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($curlError) {
                error_log("Privy create Stellar wallet CURL error: " . $curlError);
                return null;
            }

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
