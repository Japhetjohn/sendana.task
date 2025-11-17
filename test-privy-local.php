<?php
// Quick Privy Test
$app_id = 'cmhow02lw00b3l10cz7f0gbpu';
$app_secret = '3hRZCYhv4CP9iRsT33GVD8TCtzJhAmooMaQ94CWvDXbwSS75wvgbKuCMbFLfLgCfacSRwxyfK11qq6jNjh3BCciE';
$credentials = base64_encode($app_id . ':' . $app_secret);

$requestBody = json_encode([
    'linked_accounts' => [
        [
            'type' => 'email',
            'address' => 'test-' . time() . '@sendana.app'
        ]
    ],
    'wallets' => [
        ['chain_type' => 'ethereum'],
        ['chain_type' => 'solana']
    ]
]);

$ch = curl_init('https://api.privy.io/v1/users');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . $credentials,
    'privy-app-id: ' . $app_id
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Error: " . ($error ?: "none") . "\n";
echo "Response: " . $response . "\n\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "✅ Success!\n";
    echo "User ID: " . $data['id'] . "\n";

    foreach ($data['linked_accounts'] as $account) {
        if ($account['type'] === 'wallet') {
            echo "Wallet (" . $account['chain_type'] . "): " . $account['address'] . "\n";
        }
    }
} else {
    echo "❌ Failed!\n";
}
