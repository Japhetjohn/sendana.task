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
}
