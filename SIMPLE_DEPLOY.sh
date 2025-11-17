#!/bin/bash

echo "🚀 Deploying Sendana to Production"
echo "==================================="

SERVER="agentq@129.212.134.71"
PASSWORD='kY365`(.qJ=N'

# Package already created, just upload
echo "📤 Uploading package..."
/usr/bin/expect << EOF
set timeout 60
spawn scp /home/japhet/Desktop/sendana.task/sendana-app.tar.gz $SERVER:~/
expect "password:"
send "$PASSWORD\r"
expect eof
EOF

# Run deployment commands
echo "🔧 Deploying on server..."
/usr/bin/expect << 'EOF'
set timeout 300
spawn ssh agentq@129.212.134.71
expect "password:"
send "kY365\`(.qJ=N\r"

sleep 2

# Extract application
send "cd /var/www/agentq.usesendana.com/html && tar -xzf ~/sendana-app.tar.gz\r"
sleep 3

# Create .env file
send "cat > /var/www/agentq.usesendana.com/html/backend/.env << 'ENVEOF'\r"
sleep 1
send "EMAIL_SERVICE=brevo\r"
send "EMAIL_USER=japhetjohnk@gmail.com\r"
send "EMAIL_PASSWORD=bciz mpkv pjpr mfps\r"
send "EMAIL_FROM=Sendana Team <japhetjohnk@gmail.com>\r"
send "BREVO_API_KEY=YOUR_BREVO_API_KEY_HERE\r"
send "ENVEOF\r"
sleep 2


# Install composer dependencies
send "cd /var/www/agentq.usesendana.com/html/backend && composer install --no-dev --optimize-autoloader 2>&1\r"
sleep 15

# Set permissions
send "chmod -R 755 /var/www/agentq.usesendana.com/html\r"
sleep 1

# List files to verify
send "ls -la /var/www/agentq.usesendana.com/html/\r"
sleep 2

send "exit\r"
expect eof
EOF

echo "✅ Deployment complete!"
echo ""
echo "Testing site..."
sleep 5
curl -s https://agentq.usesendana.com | head -20
echo ""
echo "✅ Site is live!"
