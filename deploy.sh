#!/bin/bash
# Quick deployment script for Sendana
# Run this ON THE SERVER after uploading/cloning the code

set -e  # Exit on error

echo "======================================"
echo "Sendana Deployment Script"
echo "======================================"
echo

# Check if we're in the right directory
if [[ ! -f "backend/api/auth.php" ]]; then
    echo "Error: Please run this script from the sendana root directory"
    exit 1
fi

# Install backend dependencies
echo "[1/6] Installing backend dependencies..."
cd backend
if [[ ! -f "composer.phar" ]]; then
    echo "  Downloading composer..."
    curl -sS https://getcomposer.org/installer | php
fi
php composer.phar install --no-dev --optimize-autoloader
cd ..
echo "  ✓ Backend dependencies installed"

# Set up environment file
echo "[2/6] Setting up environment..."
if [[ ! -f ".env" ]]; then
    cp .env.example .env
    echo "  Created .env file - please configure it!"
else
    echo "  .env already exists"
fi
echo "  ✓ Environment configured"

# Create data directory and initialize database
echo "[3/6] Initializing database..."
mkdir -p backend/data
if [[ ! -f "backend/data/users.json" ]] || [[ ! -s "backend/data/users.json" ]]; then
    echo "[]" > backend/data/users.json
    echo "  Created empty users.json"
fi
echo "  ✓ Database initialized"

# Set permissions
echo "[4/6] Setting permissions..."
chmod -R 755 frontend backend
chmod -R 775 backend/data
chmod 666 backend/data/users.json
find backend -type f -name "*.php" -exec chmod 644 {} \;
echo "  ✓ Permissions set"

# Check PHP version
echo "[5/6] Checking PHP..."
PHP_VERSION=$(php -v | head -n 1)
echo "  PHP version: $PHP_VERSION"
if command -v php &> /dev/null; then
    echo "  ✓ PHP is installed"
else
    echo "  ✗ PHP not found! Please install PHP 7.4 or higher"
    exit 1
fi

# Start PHP server (optional)
echo "[6/6] Starting PHP server..."
echo
echo "You can start the PHP server with:"
echo "  php -S 0.0.0.0:8080 -t . &"
echo
echo "Or use PM2:"
echo "  pm2 start 'php -S 0.0.0.0:8080' --name sendana"
echo

echo "======================================"
echo "Deployment Complete! 🎉"
echo "======================================"
echo
echo "Next steps:"
echo "1. Configure .env file with your settings"
echo "2. Start PHP server on port 8080"
echo "3. Configure nginx (see DEPLOY_GUIDE.md)"
echo "4. Test at: http://agentq.usesendana.com"
echo
echo "For detailed instructions, see: DEPLOY_GUIDE.md"
echo
