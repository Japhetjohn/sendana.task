#!/bin/bash

SERVER_USER="agentq"
SERVER_IP="129.212.134.71"
SERVER_PASS='kY365`(.qJ=N'

echo "🔧 Setting up server prerequisites..."

expect << 'EOF'
set timeout 600
set SERVER_PASS {kY365`(.qJ=N}

spawn ssh agentq@129.212.134.71
expect {
    "password:" {
        send "$SERVER_PASS\r"
    }
    "yes/no" {
        send "yes\r"
        expect "password:"
        send "$SERVER_PASS\r"
    }
}

expect "$ "
send "echo 'Installing PHP and dependencies...'\r"
expect "$ "
send "sudo apt-get update\r"
expect {
    "password" {
        send "$SERVER_PASS\r"
    }
}
expect "$ "
send "sudo apt-get install -y php php-cli php-mbstring php-xml php-curl php-mongodb unzip\r"
expect "$ "
send "echo 'Installing Composer...'\r"
expect "$ "
send "curl -sS https://getcomposer.org/installer | php\r"
expect "$ "
send "sudo mv composer.phar /usr/local/bin/composer\r"
expect "$ "
send "sudo chmod +x /usr/local/bin/composer\r"
expect "$ "
send "composer --version\r"
expect "$ "
send "php --version\r"
expect "$ "
send "echo ''\r"
expect "$ "
send "echo '✅ Server setup complete!'\r"
expect "$ "
send "exit\r"
expect eof
EOF

echo "✅ Prerequisites installed!"
