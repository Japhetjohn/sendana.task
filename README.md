# Sendana - Borderless Banking Platform

A modern banking platform with Email and Google authentication.

## Project Structure

```
sendana.task/
├── backend/                    # PHP Backend (Clean)
│   ├── api/
│   │   └── auth.php           # Authentication endpoints
│   ├── config/
│   │   ├── database.php       # MongoDB configuration
│   │   └── privy.php          # Privy authentication helper
│   ├── models/
│   │   └── User.php           # User model
│   └── index.php              # Main entry point
│
├── frontend/                   # Frontend (Clean)
│   ├── assets/
│   │   ├── images/            # Images and logos
│   │   ├── styles/            # CSS stylesheets
│   │   └── scripts/           # JavaScript files (auth.js)
│   └── pages/
│       ├── index.html         # Login page
│       ├── signup.html        # Signup page
│       └── dashboard.html     # Dashboard
│
├── .env                        # Environment variables
├── router.php                  # PHP server router
├── README.md                   # This file
└── QUICK_START.md             # Quick start guide
```

**✨ Clean Structure:**
- All backend code in `backend/`
- All frontend code in `frontend/`
- No scattered JavaScript/Node.js files
- No unused dependencies

## Requirements

- PHP 7.4 or higher
- MongoDB PHP Extension
- Composer (for PHP dependencies - optional)
- Modern web browser

## Installation

### 1. Install PHP MongoDB Extension

**Ubuntu/Debian:**
```bash
sudo apt-get install php-mongodb
```

**macOS (with Homebrew):**
```bash
brew install php
pecl install mongodb
```

**Windows:**
Download MongoDB extension from PECL and add to php.ini

### 2. Configure Environment

The `.env` file already contains the necessary configuration:
```
MONGODB_URI=mongodb+srv://easygasproject_db_user:kuulsinim45@sendana.3tnvvjr.mongodb.net/sendana-db
PRIVY_APP_ID=cmhow02lw00b3l10cz7f0gbpu
PRIVY_APP_SECRET=3hRZCYhv4CP9iRsT33GVD8TCtzJhAmooMaQ94CWvDXbwSS75wvgbKuCMbFLfLgCfacSRwxyfK11qq6jNjh3BCciE
```

## Running the Application

### Start PHP Backend Server

```bash
cd /path/to/sendana.task
php -S localhost:8000 router.php
```

The server will be accessible at `http://localhost:8000/`

### Accessing the Application

Open your browser and navigate to:

**Root (Auto-redirects to Login):**
```
http://localhost:8000/
```

**Or directly:**
```
http://localhost:8000/frontend/pages/index.html
```

## API Endpoints

### Authentication

**POST** `/backend/api/auth/login`
- Headers: `Authorization: Bearer <privy_token>`
- Body: `{ "email": "user@example.com", "provider": "email" }`
- Response: `{ "success": true, "user": {...} }`

**GET** `/backend/api/auth/user`
- Headers: `Authorization: Bearer <privy_token>`
- Response: `{ "success": true, "user": {...} }`

**PUT** `/backend/api/auth/user`
- Headers: `Authorization: Bearer <privy_token>`
- Body: `{ "name": "John Doe", ... }`
- Response: `{ "success": true, "user": {...} }`

### Health Check

**GET** `/backend/api/health`
- Response: `{ "status": "ok", "message": "Sendana PHP Backend is running" }`

## Features

- ✅ Email Authentication
- ✅ Google OAuth Authentication
- ✅ MongoDB Database Integration
- ✅ User Session Management
- ✅ Responsive Design
- ✅ Clean Code Architecture

## Development

### Backend (PHP)

The PHP backend uses:
- Native PHP MongoDB driver
- Privy authentication
- RESTful API architecture
- CORS enabled for development

### Frontend

The frontend uses:
- Vanilla JavaScript
- Swiper.js for carousels
- Tailwind CSS for styling
- LocalStorage for session management

## Production Deployment

### Apache

Create `.htaccess` in root:
```apache
RewriteEngine On
RewriteCond %{REQUEST_URI} ^/backend/
RewriteRule ^backend/(.*)$ backend/index.php [L]
```

### Nginx

Add to your server configuration:
```nginx
location /backend/ {
    try_files $uri /backend/index.php$is_args$args;
}
```

## Security Notes

- ⚠️ Never commit `.env` file to version control in production
- ⚠️ Use HTTPS in production
- ⚠️ Rotate Privy secrets regularly
- ⚠️ Implement rate limiting for API endpoints

## Support

For issues or questions, please refer to the Privy documentation at https://docs.privy.io/

## License

© 2025 Sendana. All Rights Reserved.
