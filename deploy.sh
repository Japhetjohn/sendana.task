#!/bin/bash
# Sendana Production Deployment Script

SERVER_USER="agentq"
SERVER_IP="129.212.134.71"
SERVER_PATH="~/sendana"
LOCAL_PATH="/home/japhet/Desktop/sendana.task"

echo "🚀 Sendana Deployment Script"
echo "=============================="
echo ""

# Step 1: Sync files to server
echo "📦 Step 1: Uploading files to server..."
rsync -avz --progress \
  --exclude 'node_modules' \
  --exclude '.git' \
  --exclude '*.log' \
  --exclude 'vendor' \
  --exclude '.env.example' \
  "${LOCAL_PATH}/" \
  "${SERVER_USER}@${SERVER_IP}:${SERVER_PATH}/"

if [ $? -eq 0 ]; then
    echo "✅ Files uploaded successfully"
else
    echo "❌ Failed to upload files"
    exit 1
fi

echo ""
echo "🔧 Step 2: Installing dependencies on server..."

# Step 2: Install composer dependencies
ssh "${SERVER_USER}@${SERVER_IP}" << 'ENDSSH'
cd ~/sendana/backend
echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

echo ""
echo "Setting permissions..."
chmod -R 755 ~/sendana
chmod 600 ~/sendana/backend/.env 2>/dev/null || true

echo ""
echo "Checking PHP extensions..."
php -m | grep -E "(mongodb|json|curl)"

ENDSSH

if [ $? -eq 0 ]; then
    echo "✅ Server setup complete"
else
    echo "❌ Server setup failed"
    exit 1
fi

echo ""
echo "🎉 Deployment Complete!"
echo ""
echo "Next Steps:"
echo "1. SSH into the server: ssh ${SERVER_USER}@${SERVER_IP}"
echo "2. Start the server: cd ~/sendana && php -S 0.0.0.0:8080 router.php"
echo "3. Test: curl http://agentq.usesendana.com/api/health"
echo ""
echo "📝 See DEPLOYMENT_GUIDE.md for detailed instructions"
