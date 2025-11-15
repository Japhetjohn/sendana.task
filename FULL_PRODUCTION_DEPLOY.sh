#!/bin/bash

echo "🚀 Sendana - Full Production Deployment"
echo "========================================="

SERVER="agentq@129.212.134.71"
PASSWORD='kY365`(.qJ=N'
DEPLOY_DIR="/var/www/agentq.usesendana.com/html"

# Create deployment package
echo "📦 Creating deployment package..."
cd /home/japhet/Desktop/sendana.task

# Create a clean deployment package
tar -czf sendana-app.tar.gz \
    --exclude='node_modules' \
    --exclude='.git' \
    --exclude='*.log' \
    --exclude='test_*' \
    --exclude='.env' \
    frontend/ backend/

echo "✅ Package created: sendana-app.tar.gz"

# Upload package using expect
echo "📤 Uploading to production server..."
/usr/bin/expect << EOF
set timeout 60
spawn scp sendana-app.tar.gz $SERVER:~/
expect "password:"
send "$PASSWORD\r"
expect eof
EOF

if [ $? -ne 0 ]; then
    echo "❌ Failed to upload package"
    exit 1
fi

echo "✅ Package uploaded"

# Deploy on server
echo "🔧 Deploying application on server..."
/usr/bin/expect << 'DEPLOY_EOF'
set timeout 120
set server "agentq@129.212.134.71"
set password "kY365`(.qJ=N"
set deploy_dir "/var/www/agentq.usesendana.com/html"

spawn ssh $server
expect "password:"
send "$password\r"

expect "~$"
send "echo '=== Extracting application ==='\r"

expect "~$"
send "cd /var/www/agentq.usesendana.com/html\r"

expect "html$"
send "tar -xzf ~/sendana-app.tar.gz\r"

expect "html$"
send "echo '=== Setting up backend .env ==='\r"

expect "html$"
send "cat > backend/.env << 'ENV_EOF'\r"
expect ">"
send "# Email Configuration\r"
expect ">"
send "EMAIL_SERVICE=brevo\r"
expect ">"
send "EMAIL_USER=japhetjohnk@gmail.com\r"
expect ">"
send "EMAIL_PASSWORD=bciz mpkv pjpr mfps\r"
expect ">"
send "EMAIL_FROM=Sendana Team <japhetjohnk@gmail.com>\r"
expect ">"
send "\r"
expect ">"
send "# Brevo (Sendinblue) API Configuration\r"
expect ">"
send "BREVO_API_KEY=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\r"
expect ">"
send "ENV_EOF\r"

expect "html$"
send "echo '=== Installing PHP dependencies ==='\r"

expect "html$"
send "cd backend\r"

expect "backend$"
send "composer install 2>&1\r"

expect {
    "backend$" { }
    timeout { send "\r" }
}

send "cd ..\r"

expect "html$"
send "echo '=== Setting permissions ==='\r"

expect "html$"
send "chmod -R 755 frontend backend\r"

expect "html$"
send "chmod -R 777 backend/vendor 2>/dev/null || true\r"

expect "html$"
send "echo '=== Deployment complete ==='\r"

expect "html$"
send "ls -la\r"

expect "html$"
send "exit\r"

expect eof
DEPLOY_EOF

if [ $? -ne 0 ]; then
    echo "❌ Deployment failed"
    exit 1
fi

echo "✅ Application deployed successfully"

# Update nginx configuration
echo "🔧 Updating nginx configuration..."
/usr/bin/expect << 'NGINX_EOF'
set timeout 60
set server "agentq@129.212.134.71"
set password "kY365`(.qJ=N"

spawn ssh $server
expect "password:"
send "$password\r"

expect "~$"
send "sudo tee /etc/nginx/sites-available/agentq.usesendana.com > /dev/null << 'NGINX_CONF'\r"

expect ">"
send "server {\r"
expect ">"
send "    listen 80;\r"
expect ">"
send "    listen [::]:80;\r"
expect ">"
send "    server_name agentq.usesendana.com;\r"
expect ">"
send "    return 301 https://\$host\$request_uri;\r"
expect ">"
send "}\r"
expect ">"
send "\r"
expect ">"
send "server {\r"
expect ">"
send "    listen 443 ssl;\r"
expect ">"
send "    listen [::]:443 ssl;\r"
expect ">"
send "    server_name agentq.usesendana.com;\r"
expect ">"
send "\r"
expect ">"
send "    root /var/www/agentq.usesendana.com/html/frontend/pages;\r"
expect ">"
send "    index index.html;\r"
expect ">"
send "\r"
expect ">"
send "    ssl_certificate /etc/letsencrypt/live/agentq.usesendana.com/fullchain.pem;\r"
expect ">"
send "    ssl_certificate_key /etc/letsencrypt/live/agentq.usesendana.com/privkey.pem;\r"
expect ">"
send "    include /etc/letsencrypt/options-ssl-nginx.conf;\r"
expect ">"
send "    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;\r"
expect ">"
send "\r"
expect ">"
send "    # Backend API - PHP\r"
expect ">"
send "    location /backend/ {\r"
expect ">"
send "        alias /var/www/agentq.usesendana.com/html/backend/;\r"
expect ">"
send "        try_files \$uri \$uri/ /backend/index.php?\$query_string;\r"
expect ">"
send "\r"
expect ">"
send "        location ~ \\.php\$ {\r"
expect ">"
send "            fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;\r"
expect ">"
send "            fastcgi_index index.php;\r"
expect ">"
send "            include fastcgi_params;\r"
expect ">"
send "            fastcgi_param SCRIPT_FILENAME \$request_filename;\r"
expect ">"
send "        }\r"
expect ">"
send "    }\r"
expect ">"
send "\r"
expect ">"
send "    # Frontend assets\r"
expect ">"
send "    location /frontend/ {\r"
expect ">"
send "        alias /var/www/agentq.usesendana.com/html/frontend/;\r"
expect ">"
send "        try_files \$uri \$uri/ =404;\r"
expect ">"
send "    }\r"
expect ">"
send "\r"
expect ">"
send "    # Root location\r"
expect ">"
send "    location / {\r"
expect ">"
send "        try_files \$uri \$uri/ /index.html;\r"
expect ">"
send "    }\r"
expect ">"
send "}\r"
expect ">"
send "NGINX_CONF\r"

expect "~$"
send "$password\r"

expect "~$"
send "sudo nginx -t\r"

expect {
    "successful" {
        send "sudo systemctl reload nginx\r"
    }
    "failed" {
        send "echo 'Nginx config test failed'\r"
    }
}

expect "~$"
send "exit\r"

expect eof
NGINX_EOF

echo ""
echo "✅ DEPLOYMENT COMPLETE!"
echo "========================"
echo "Production URL: https://agentq.usesendana.com"
echo "API Health: https://agentq.usesendana.com/backend/api/health"
echo ""
