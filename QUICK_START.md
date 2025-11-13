# Quick Start Guide

## Start the Server

```bash
cd /home/japhet/Desktop/sendana.task
php -S localhost:8000 router.php
```

## Access the Application

Open your browser and navigate to:

**Root (redirects to Login):**
```
http://localhost:8000/
```

**Login Page:**
```
http://localhost:8000/frontend/pages/index.html
```

**Signup Page:**
```
http://localhost:8000/frontend/pages/signup.html
```

**Dashboard:**
```
http://localhost:8000/frontend/pages/dashboard.html
```

## Test the API

**Health Check:**
```bash
curl http://localhost:8000/backend/api/health
```

Expected response:
```json
{
  "status": "ok",
  "message": "Sendana PHP Backend is running",
  "timestamp": "2025-11-13T18:00:00+00:00"
}
```

## Project Structure

```
sendana.task/
├── backend/           # PHP Backend
│   ├── api/          # API endpoints
│   ├── config/       # Database & Privy config
│   ├── models/       # User model
│   └── index.php     # Entry point
├── frontend/         # Frontend
│   ├── assets/
│   │   ├── images/   # Images
│   │   ├── styles/   # CSS files
│   │   └── scripts/  # JavaScript
│   └── pages/
│       ├── index.html      # Login
│       ├── signup.html     # Signup
│       └── dashboard.html  # Dashboard
├── .env              # Environment config
└── router.php        # PHP Server Router
```

## Clean Structure

All code is now organized in two main directories:
- **backend/** - All PHP backend code
- **frontend/** - All frontend code (HTML, CSS, JS, images)

No scattered files or JavaScript/Node.js dependencies outside these folders!

## Authentication Flow

1. User enters email on login/signup page
2. User is authenticated (simplified without Privy SDK)
3. Session is saved to localStorage
4. User is redirected to dashboard

## Next Steps

1. Integrate full Privy authentication
2. Add proper email verification
3. Implement Google OAuth flow
4. Add Stellar blockchain integration
5. Deploy to production

## Troubleshooting

**Server won't start:**
- Check if port 8000 is available: `lsof -i:8000`
- Kill existing PHP processes: `pkill -f "php -S"`

**MongoDB connection issues:**
- Verify MongoDB URI in backend/config/database.php
- Ensure MongoDB PHP extension is installed

**404 errors:**
- Make sure you're using router.php: `php -S localhost:8000 router.php`
- Root URL (http://localhost:8000/) should redirect to login page
