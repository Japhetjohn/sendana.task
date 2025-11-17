#!/bin/bash

echo "🚀 Deploying Privy Stellar Integration to Production"
echo "===================================================="

SERVER="mi6@agentq.usesendana.com"
DEPLOY_DIR="/var/www/agentq.usesendana.com/html"

echo "📤 Uploading files to production server..."

# Wait for scp to complete if it's still running
wait

# Deploy files via SSH
echo "🔧 Deploying on production server..."
ssh $SERVER << 'EOF'
cd /var/www/agentq.usesendana.com/html

echo "=== Extracting application ==="
sudo tar -xzf ~/sendana-privy-stellar.tar.gz

echo "=== Setting permissions ==="
sudo chown -R www-data:www-data backend frontend
sudo chmod -R 755 backend frontend

echo "=== Verifying Privy config ==="
sudo ls -la backend/config/privy.php
sudo head -n 20 backend/config/privy.php

echo "=== Restarting PHP-FPM ==="
sudo systemctl restart php8.3-fpm

echo "✅ Deployment complete!"
EOF

echo ""
echo "✅ PRIVY STELLAR DEPLOYMENT COMPLETE!"
echo "====================================="
echo "Production URL: https://agentq.usesendana.com"
echo "Test signup to create Privy Stellar wallet"
echo ""
