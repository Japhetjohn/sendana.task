# Sendana Deployment Guide for agentq.usesendana.com

## Quick Deployment Steps

### 1. Upload Files to Server

SSH into your server:
```bash
ssh agentq@129.212.134.71
# Password: kY365`(.qJ=N
```

### 2. Download or Upload the Code

Option A - Clone from Git (recommended):
```bash
cd /home/agentq
git clone https://github.com/Japhetjohn/sendana.task.git sendana
cd sendana
git checkout claude/fix-signup-errors-019nZgnRCrFAgN1xiqRiTga2
```

Option B - Upload tarball (if git not available):
```bash
# On your local machine, upload the tarball:
scp /tmp/sendana-production.tar.gz agentq@129.212.134.71:/home/agentq/

# Then on the server:
cd /home/agentq
mkdir -p sendana
cd sendana
tar -xzf ../sendana-production.tar.gz
```

### 3. Install PHP Dependencies

```bash
cd /home/agentq/sendana/backend

# Check if composer is installed
which composer || curl -sS https://getcomposer.org/installer | php

# Install dependencies
php composer.phar install
# OR if composer is globally installed:
composer install
```

### 4. Set Up Environment Variables

```bash
cd /home/agentq/sendana
cp .env.example .env
nano .env  # Edit with your configuration
```

Configure these variables in `.env`:
```env
VITE_PRIVY_APP_ID=cmhow02lw00b3l10cz7f0gbpu
VITE_STELLAR_NETWORK=testnet
VITE_EMAIL_SERVICE_API_KEY=your_email_service_api_key
VITE_EMAIL_SERVICE_ENDPOINT=your_email_service_endpoint
```

### 5. Set Correct Permissions

```bash
cd /home/agentq/sendana

# Set directory permissions
chmod -R 755 frontend backend
chmod -R 775 backend/data

# Ensure users.json is writable
chmod 666 backend/data/users.json

# Set backend permissions
find backend -type f -name "*.php" -exec chmod 644 {} \;
```

### 6. Start PHP Built-in Server (for testing)

```bash
cd /home/agentq/sendana

# Start PHP server on port 8080 (matches nginx proxy config)
php -S 0.0.0.0:8080 -t . &
```

Or use a process manager like PM2:
```bash
# Install PM2 if needed
npm install -g pm2

# Create PHP server script
cat > server.php << 'EOF'
<?php
// Router script for PHP built-in server
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve frontend files
if (preg_match('/^\/frontend/', $uri)) {
    return false;  // Serve static files
}

// Route backend API requests
if (preg_match('/^\/backend\/api/', $uri)) {
    // Let PHP handle the routing
    return false;
}

// Default index
if ($uri === '/' || $uri === '/index.html') {
    readfile('frontend/pages/index.html');
    return true;
}

return false;
EOF

# Start with PM2
pm2 start "php -S 0.0.0.0:8080 server.php" --name sendana
pm2 save
pm2 startup
```

### 7. Configure Nginx (if not already set up)

```bash
sudo nano /etc/nginx/sites-available/sendana
```

Add this configuration:
```nginx
server {
    listen 80;
    listen [::]:80;

    server_name agentq.usesendana.com;

    root /home/agentq/sendana;

    # Frontend static files
    location /frontend/ {
        alias /home/agentq/sendana/frontend/;
        try_files $uri $uri/ =404;
    }

    # Backend API
    location /backend/api/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Main pages
    location / {
        try_files /frontend/pages$uri /frontend/pages/index.html =404;
    }
}
```

Enable the site and restart nginx:
```bash
sudo ln -s /etc/nginx/sites-available/sendana /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 8. Test the Deployment

Visit: http://agentq.usesendana.com

Test these flows:
1. **Sign Up** - Go to `/frontend/pages/signup.html`
   - Test email signup with: test@example.com
   - Should create account and Privy wallet

2. **Sign In** - Go to `/frontend/pages/index.html`
   - Test login with created account
   - Should redirect to dashboard

3. **Google Auth** - Click "Sign in with Google"
   - Should show Google OAuth popup
   - Should work if Google Client ID is configured

### 9. Troubleshooting

**Check PHP errors:**
```bash
tail -f /var/log/nginx/error.log
```

**Check if PHP server is running:**
```bash
ps aux | grep php
netstat -tulpn | grep 8080
```

**Test backend API directly:**
```bash
curl http://127.0.0.1:8080/backend/api/health.php
```

**Check file permissions:**
```bash
ls -la /home/agentq/sendana/backend/data/
```

**Clear users database (if needed):**
```bash
echo "[]" > /home/agentq/sendana/backend/data/users.json
```

## Common Issues Fixed

1. ✅ **Google Auth SDK loading error** - Fixed by loading SDK synchronously
2. ✅ **Signup errors** - Fixed JSON parsing and error handling
3. ✅ **Privy wallet creation** - Added privy-wallet.js and backend endpoint
4. ✅ **Missing backend files** - Added all API endpoints and services

## Files Structure

```
/home/agentq/sendana/
├── frontend/
│   ├── assets/
│   │   ├── images/        # Logo, login art
│   │   ├── scripts/       # auth.js, privy-wallet.js
│   │   └── styles/        # login.css
│   ├── config/
│   │   └── google-oauth.js
│   └── pages/
│       ├── index.html     # Login page
│       ├── signup.html    # Signup page
│       └── dashboard.html # Dashboard
├── backend/
│   ├── api/
│   │   ├── auth/          # signup.php, login.php, google.php
│   │   ├── privy/         # create-wallet.php
│   │   ├── auth.php       # Main API router
│   │   ├── wallet.php
│   │   └── health.php
│   ├── config/            # database.php, privy.php, email.php
│   ├── models/            # User.php
│   ├── services/          # EmailService.php, StellarService.php
│   ├── data/
│   │   └── users.json     # User database (JSON)
│   └── vendor/            # Composer dependencies
└── .env                   # Environment configuration
```

## Next Steps After Deployment

1. Set up SSL certificate (Let's Encrypt):
   ```bash
   sudo certbot --nginx -d agentq.usesendana.com
   ```

2. Set up log rotation for users.json and error logs

3. Configure email service for welcome emails

4. Set up backups for backend/data/users.json

5. Configure CORS properly for production

6. Set up monitoring and error tracking
