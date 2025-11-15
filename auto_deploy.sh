#!/bin/bash

SERVER_USER="agentq"
SERVER_IP="129.212.134.71"
SERVER_PASS='kY365`(.qJ=N'

echo "🚀 Starting Automated Deployment..."
echo ""

# Step 1: Upload tar file
echo "📦 Step 1: Uploading deployment package..."
expect << EOF
set timeout 300
spawn scp /home/japhet/Desktop/sendana-deploy.tar.gz ${SERVER_USER}@${SERVER_IP}:~/
expect {
    "password:" {
        send "${SERVER_PASS}\r"
        expect eof
    }
    "yes/no" {
        send "yes\r"
        expect "password:"
        send "${SERVER_PASS}\r"
        expect eof
    }
}
EOF

if [ $? -eq 0 ]; then
    echo "✅ Upload successful"
else
    echo "❌ Upload failed"
    exit 1
fi

echo ""
echo "🔧 Step 2: Setting up on server..."

# Step 2: SSH and setup
expect << EOF
set timeout 300
spawn ssh ${SERVER_USER}@${SERVER_IP}
expect {
    "password:" {
        send "${SERVER_PASS}\r"
    }
    "yes/no" {
        send "yes\r"
        expect "password:"
        send "${SERVER_PASS}\r"
    }
}

expect "$ "
send "tar -xzf sendana-deploy.tar.gz\r"
expect "$ "
send "rm -rf sendana\r"
expect "$ "
send "mv sendana.task sendana\r"
expect "$ "
send "cd sendana/backend\r"
expect "$ "
send "composer install --no-dev --optimize-autoloader\r"
expect "$ "
send "cd ..\r"
expect "$ "
send "chmod -R 755 .\r"
expect "$ "
send "chmod 600 backend/.env 2>/dev/null || true\r"
expect "$ "
send "pkill -f 'php -S' || true\r"
expect "$ "
send "nohup php -S 0.0.0.0:8080 router.php > server.log 2>&1 &\r"
expect "$ "
send "sleep 3\r"
expect "$ "
send "curl http://localhost:8080/api/health\r"
expect "$ "
send "echo ''\r"
expect "$ "
send "echo '✅ Server deployed and running!'\r"
expect "$ "
send "echo '🌐 Access at: http://agentq.usesendana.com'\r"
expect "$ "
send "exit\r"
expect eof
EOF

echo ""
echo "🎉 Deployment Complete!"
echo ""
echo "Testing from local machine..."
sleep 2
curl -s http://agentq.usesendana.com/api/health | python3 -m json.tool 2>/dev/null || curl -s http://agentq.usesendana.com/api/health
echo ""
echo ""
echo "✅ DEPLOYMENT SUCCESSFUL!"
echo "🌐 Your app is live at: http://agentq.usesendana.com"
