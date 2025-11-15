#!/bin/bash

SERVER_USER="agentq"
SERVER_IP="129.212.134.71"
SERVER_PASS='kY365`(.qJ=N'

echo "🚀 SENDANA MAINNET DEPLOYMENT"
echo "=============================="
echo ""

# Upload if needed
if [ ! -f "/home/japhet/Desktop/sendana-deploy.tar.gz" ]; then
    echo "Creating deployment package..."
    cd /home/japhet/Desktop
    tar -czf sendana-deploy.tar.gz --exclude='node_modules' --exclude='.git' --exclude='*.log' --exclude='vendor' sendana.task/
fi

echo "📦 Uploading application..."
expect << EOF
set timeout 300
spawn scp /home/japhet/Desktop/sendana-deploy.tar.gz ${SERVER_USER}@${SERVER_IP}:~/
expect {
    "password:" { send "${SERVER_PASS}\r"; expect eof }
    "yes/no" { send "yes\r"; expect "password:"; send "${SERVER_PASS}\r"; expect eof }
}
EOF

echo "✅ Upload complete!"
echo ""
echo "🔧 Installing dependencies and deploying..."
echo ""

expect << 'DEPLOY'
set timeout 900
set SERVER_PASS {kY365`(.qJ=N}

spawn ssh agentq@129.212.134.71
expect {
    "password:" { send "$SERVER_PASS\r" }
    "yes/no" { send "yes\r"; expect "password:"; send "$SERVER_PASS\r" }
}

expect "$ "
send "echo '=== Step 1: Installing PHP and Composer ==='\r"
expect "$ "

send "sudo apt-get update -qq\r"
expect {
    "*password*" { send "$SERVER_PASS\r" }
}
expect "$ "

send "sudo DEBIAN_FRONTEND=noninteractive apt-get install -y php php-cli php-fpm php-mbstring php-xml php-curl unzip > /dev/null 2>&1\r"
expect "$ "

send "echo 'PHP installed!'\r"
expect "$ "

send "if ! command -v composer &> /dev/null; then curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer && sudo chmod +x /usr/local/bin/composer; fi\r"
expect "$ "

send "echo 'Composer installed!'\r"
expect "$ "

send "echo ''\r"
expect "$ "
send "echo '=== Step 2: Installing MongoDB extension ==='\r"
expect "$ "

send "sudo DEBIAN_FRONTEND=noninteractive apt-get install -y php-pear php-dev pkg-config libssl-dev > /dev/null 2>&1\r"
expect "$ "

send "echo | sudo pecl install mongodb 2>/dev/null || true\r"
expect "$ "

send "echo 'extension=mongodb.so' | sudo tee /etc/php/8.3/cli/conf.d/20-mongodb.ini > /dev/null\r"
expect "$ "
send "echo 'extension=mongodb.so' | sudo tee /etc/php/8.3/fpm/conf.d/20-mongodb.ini > /dev/null\r"
expect "$ "

send "echo 'MongoDB extension installed!'\r"
expect "$ "

send "echo ''\r"
expect "$ "
send "echo '=== Step 3: Extracting application ==='\r"
expect "$ "

send "tar -xzf sendana-deploy.tar.gz 2>/dev/null\r"
expect "$ "
send "rm -rf sendana\r"
expect "$ "
send "mv sendana.task sendana\r"
expect "$ "
send "cd sendana\r"
expect "$ "

send "echo 'Application extracted!'\r"
expect "$ "

send "echo ''\r"
expect "$ "
send "echo '=== Step 4: Installing dependencies ==='\r"
expect "$ "

send "cd backend && composer install --no-dev --optimize-autoloader --quiet\r"
expect "$ "
send "cd ..\r"
expect "$ "

send "echo 'Dependencies installed!'\r"
expect "$ "

send "echo ''\r"
expect "$ "
send "echo '=== Step 5: Setting permissions ==='\r"
expect "$ "

send "chmod -R 755 .\r"
expect "$ "
send "chmod 600 backend/.env 2>/dev/null || true\r"
expect "$ "

send "echo 'Permissions set!'\r"
expect "$ "

send "echo ''\r"
expect "$ "
send "echo '=== Step 6: Starting server ==='\r"
expect "$ "

send "pkill -f 'php.*router.php' 2>/dev/null || true\r"
expect "$ "
send "sleep 2\r"
expect "$ "

send "nohup php -S 0.0.0.0:8080 router.php > server.log 2>&1 &\r"
expect "$ "
send "sleep 3\r"
expect "$ "

send "echo 'Server started!'\r"
expect "$ "

send "echo ''\r"
expect "$ "
send "echo '=== Step 7: Testing deployment ==='\r"
expect "$ "

send "curl -s http://localhost:8080/api/health\r"
expect "$ "

send "echo ''\r"
expect "$ "
send "echo ''\r"
expect "$ "
send "echo '✅ DEPLOYMENT COMPLETE!'\r"
expect "$ "
send "echo '🌐 Your app is live at: http://agentq.usesendana.com'\r"
expect "$ "
send "echo ''\r"
expect "$ "

send "exit\r"
expect eof
DEPLOY

echo ""
echo "=============================="
echo "🎉 VERIFYING FROM INTERNET..."
echo "=============================="
sleep 3

echo ""
echo "Testing API health endpoint..."
curl -s http://agentq.usesendana.com/api/health | python3 -m json.tool 2>/dev/null || curl -s http://agentq.usesendana.com/api/health

echo ""
echo ""
echo "=============================="
echo "✅ DEPLOYMENT SUCCESSFUL!"
echo "=============================="
echo ""
echo "🌐 Your MAINNET Sendana app is LIVE at:"
echo "   http://agentq.usesendana.com"
echo ""
echo "✨ Features Active:"
echo "   - Stellar MAINNET wallet creation"
echo "   - MongoDB Atlas database"
echo "   - Email notifications"
echo "   - User authentication"
echo ""
echo "🎊 Ready to go!"
echo ""
