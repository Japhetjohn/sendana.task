# Sendana - Borderless Banking Application

A modern borderless banking application built with React, Privy authentication, and Stellar blockchain integration.

## Features

- Email and Google OAuth authentication via Privy
- Automatic Stellar wallet creation on signup
- Non-custodial wallet architecture
- USDC balance management
- QR code generation for wallet addresses
- Responsive dashboard
- Transaction history
- Welcome email automation

## Tech Stack

- **Frontend**: React 18, Vite
- **Authentication**: Privy SDK
- **Blockchain**: Stellar SDK
- **Routing**: React Router DOM
- **Styling**: Tailwind CSS (via CDN)
- **Icons**: Font Awesome
- **QR Codes**: qrcode library
- **Backend API**: PHP (for email service)

## Setup Instructions

### 1. Install Dependencies

```bash
npm install
```

### 2. Configure Environment Variables

Create a `.env` file in the root directory:

```env
VITE_PRIVY_APP_ID=your_privy_app_id
VITE_PRIVY_APP_SECRET=your_privy_app_secret

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASSWORD=your_app_password
FROM_EMAIL=noreply@sendana.com
FROM_NAME=Sendana
```

### 3. Run Development Server

```bash
npm run dev
```

The application will be available at `http://localhost:3000`

### 4. Build for Production

```bash
npm run build
```

### 5. Preview Production Build

```bash
npm run preview
```

## Project Structure

```
sendana.task/
├── src/
│   ├── assets/
│   │   └── images/          # Logo, avatars, carousel images
│   ├── components/
│   │   └── Sidebar.jsx      # Reusable sidebar navigation
│   ├── pages/
│   │   ├── LoginPage.jsx    # Login/signup page with Privy
│   │   ├── Dashboard.jsx    # Main dashboard
│   │   └── WalletPage.jsx   # Wallet address and QR code
│   ├── styles/
│   │   ├── login.css        # Login page styles
│   │   └── dashboard.css    # Dashboard styles
│   ├── App.jsx              # Main app component with routing
│   └── main.jsx             # Entry point
├── api/
│   └── send-welcome-email.php  # Welcome email endpoint
├── index.html               # HTML template
├── vite.config.js          # Vite configuration
├── package.json            # Dependencies
└── .env                    # Environment variables (gitignored)
```

## Key Features Explained

### Authentication Flow

1. User visits login page
2. Enters email or clicks "Sign in with Google"
3. Privy handles authentication and wallet creation
4. User is redirected to dashboard
5. Welcome email is sent (if SMTP is configured)

### Wallet Management

- Stellar wallets are automatically created on first login
- Wallets are non-custodial (user owns the keys)
- QR codes are generated for easy sharing
- Wallet addresses can be copied with one click

### Navigation

- Sidebar navigation with active state highlighting
- Protected routes (require authentication)
- Responsive mobile menu
- Account dropdown with logout functionality

## API Endpoints

### POST /api/send-welcome-email.php

Sends a welcome email to new users.

**Request Body:**
```json
{
  "email": "user@example.com",
  "firstName": "John"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Welcome email sent successfully",
  "email": "user@example.com"
}
```

## Environment Variables

| Variable | Description | Required |
|----------|-------------|----------|
| `VITE_PRIVY_APP_ID` | Privy application ID | Yes |
| `VITE_PRIVY_APP_SECRET` | Privy app secret | Yes |
| `SMTP_HOST` | SMTP server hostname | No (defaults to logging) |
| `SMTP_PORT` | SMTP server port | No |
| `SMTP_USER` | SMTP username | No |
| `SMTP_PASSWORD` | SMTP password | No |
| `FROM_EMAIL` | Sender email address | No |
| `FROM_NAME` | Sender name | No |

## Development Notes

- The app uses Vite for fast development and building
- Swiper carousel is loaded via CDN for the login page
- Font Awesome icons are loaded via CDN
- Tailwind CSS classes are used throughout
- Privy SDK handles all authentication and wallet creation

## License

© 2025 Sendana. All rights reserved.
