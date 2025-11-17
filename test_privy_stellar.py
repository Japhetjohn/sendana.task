#!/usr/bin/env python3
import requests
import base64
import json

# Privy credentials
app_id = 'cmhow02lw00b3l10cz7f0gbpu'
app_secret = '3hRZCYhv4CP9iRsT33GVD8TCtzJhAmooMaQ94CWvDXbwSS75wvgbKuCMbFLfLgCfacSRwxyfK11qq6jNjh3BCciE'

# Create Basic Auth header
credentials = base64.b64encode(f'{app_id}:{app_secret}'.encode()).decode()

# Test Stellar wallet creation
print("Testing Privy Stellar Wallet Creation...\n")

url = 'https://api.privy.io/v1/wallets'
headers = {
    'Content-Type': 'application/json',
    'Authorization': f'Basic {credentials}',
    'privy-app-id': app_id,
    'privy-idempotency-key': 'test_stellar_wallet_12345'
}

data = {
    'chain_type': 'stellar'
}

print("1. Creating Stellar wallet via Privy...")
response = requests.post(url, headers=headers, json=data)

print(f"Status Code: {response.status_code}")
print(f"Response: {json.dumps(response.json(), indent=2)}\n")

if response.status_code in [200, 201]:
    wallet_data = response.json()
    print("✅ Stellar wallet created successfully!")
    print(f"   Wallet ID: {wallet_data.get('id', 'N/A')}")
    print(f"   Address: {wallet_data.get('address', 'N/A')}")
    print(f"   Chain Type: {wallet_data.get('chain_type', 'N/A')}")

    # Test fetching the wallet
    if 'id' in wallet_data:
        wallet_id = wallet_data['id']
        print(f"\n2. Fetching wallet by ID: {wallet_id}...")

        get_url = f'https://api.privy.io/v1/wallets/{wallet_id}'
        get_headers = {
            'Authorization': f'Basic {credentials}',
            'privy-app-id': app_id
        }

        get_response = requests.get(get_url, headers=get_headers)
        print(f"Status Code: {get_response.status_code}")
        print(f"Response: {json.dumps(get_response.json(), indent=2)}")

        if get_response.status_code == 200:
            print("\n✅ Wallet fetched successfully!")
else:
    print("❌ Stellar wallet creation failed!")
