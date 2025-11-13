# Sendana MVP Setup Guide

## Project Overview

This is the Sendana MVP application built with React, Privy for authentication, and Stellar for blockchain wallet management.

## Features Implemented

1. Email and Google authentication via Privy
2. Automatic Stellar wallet creation on signup
3. Dashboard with wallet balance display
4. Wallet address page with QR code
5. Welcome email functionality

## Prerequisites

- Node.js (v18 or higher)
- npm or yarn
- Privy account with app credentials
- Email service provider (Resend, SendGrid, or similar)

## Installation

1. Install dependencies:
```bash
npm install
```

2. Configure environment variables (see Environment Setup below)

3. Start development server:
```bash
npm run dev
```

4. Build for production:
```bash
npm run build
```

## Environment Setup

Copy `.env.example` to `.env` and fill in the following values:

### 1. VITE_PRIVY_APP_ID
Get this from your Privy dashboard at https://dashboard.privy.io

Steps:
- Log in to Privy dashboard
- Create a new app or select existing app
- Copy the App ID from the settings page
- Paste it as: `VITE_PRIVY_APP_ID=your_app_id_here`

### 2. VITE_STELLAR_NETWORK
Set to either `testnet` or `mainnet`

For development and testing: `VITE_STELLAR_NETWORK=testnet`
For production: `VITE_STELLAR_NETWORK=mainnet`

### 3. Email Service Configuration

You need to configure an email service to send welcome emails. Here are the options:

#### Option A: Resend (Recommended)
```
VITE_EMAIL_SERVICE_ENDPOINT=https://api.resend.com/emails
VITE_EMAIL_SERVICE_API_KEY=re_your_api_key_here
```

Setup steps:
1. Sign up at https://resend.com
2. Verify your domain
3. Generate an API key
4. Update the .env file

#### Option B: SendGrid
```
VITE_EMAIL_SERVICE_ENDPOINT=https://api.sendgrid.com/v3/mail/send
VITE_EMAIL_SERVICE_API_KEY=SG.your_api_key_here
```

Setup steps:
1. Sign up at https://sendgrid.com
2. Verify sender identity
3. Create an API key
4. Update the .env file

#### Option C: Custom Email Service
If you have your own email API endpoint, configure it as:
```
VITE_EMAIL_SERVICE_ENDPOINT=https://your-api.com/send-email
VITE_EMAIL_SERVICE_API_KEY=your_api_key
```

Your endpoint should accept POST requests with:
```json
{
  "to": "user@example.com",
  "subject": "Email subject",
  "html": "<html>email content</html>"
}
```

## Email Service Integration Notes

### Required Email Service Features:
- HTTP POST endpoint for sending emails
- Authorization via API key (Bearer token)
- Support for HTML email content
- Ability to send transactional emails

### Email Template
The welcome email template is located in `src/services/email.js`. It includes:
- Subject: "You're in! Let's make money move"
- Personalized greeting with user's first name
- Product features overview
- Call-to-action button linking to login
- Professional branding matching Sendana colors

### Testing Email Functionality
1. For development, you can use Resend's test mode
2. Create a test account and verify email delivery
3. Check spam folder if emails don't arrive
4. Monitor API logs for debugging

## Project Structure

```
src/
├── App.jsx                 # Main app component with routing
├── main.jsx               # App entry point with Privy provider
├── pages/
│   ├── Login.jsx          # Login page with Privy integration
│   ├── Login.css          # Login page styles
│   ├── Dashboard.jsx      # Main dashboard
│   ├── Dashboard.css      # Dashboard styles
│   └── WalletAddress.jsx  # Wallet address display with QR code
├── services/
│   ├── stellar.js         # Stellar wallet operations
│   └── email.js           # Email sending service
public/
├── logo.png              # Sendana logo
├── avatar.png            # User avatar placeholder
├── login-art-1.jpg       # Login carousel image 1
└── login-art-2.jpg       # Login carousel image 2
```

## Authentication Flow

1. User visits `/` and sees login page
2. User clicks "Sign in with Email" or "Sign in with Google"
3. Privy handles authentication
4. On successful authentication:
   - App checks for existing Stellar wallet
   - If no wallet exists:
     - Creates new Stellar wallet
     - Stores wallet info (public key only)
     - Sends welcome email
   - Redirects to dashboard

## Security Notes

IMPORTANT: The current implementation stores only the public key locally. The secret key is generated but should be handled securely:

1. NEVER store secret keys in localStorage
2. For production, integrate with Privy's embedded wallet feature
3. Secret keys should be encrypted and stored securely
4. Consider using Privy's key management for production

## Wallet Management

- Stellar wallets are created using `@stellar/stellar-sdk`
- In testnet mode, wallets are automatically funded via Friendbot
- Public keys are stored in localStorage for quick access
- Balance fetching is done via Stellar Horizon API

## Deployment Checklist

Before deploying to production:

- [ ] Switch to Stellar mainnet (`VITE_STELLAR_NETWORK=mainnet`)
- [ ] Set up production email service
- [ ] Verify Privy production app configuration
- [ ] Test email delivery end-to-end
- [ ] Review and update security measures
- [ ] Set up proper key management
- [ ] Configure domain settings in Privy
- [ ] Test authentication flow completely

## Troubleshooting

### Email not sending
- Verify API key is correct
- Check email service dashboard for errors
- Ensure sender email is verified
- Check browser console for errors

### Privy login not working
- Verify App ID is correct
- Check Privy dashboard for app status
- Ensure allowed domains are configured
- Clear browser cache and cookies

### Stellar wallet creation fails
- Check network connectivity
- Verify Stellar network setting
- For testnet, ensure Friendbot is accessible
- Check browser console for errors

### QR code not displaying
- Verify wallet was created successfully
- Check browser console for canvas errors
- Ensure qrcode package is installed

## Support

For issues specific to:
- Privy authentication: https://docs.privy.io
- Stellar integration: https://developers.stellar.org
- Email services: Check your provider's documentation

## Production Considerations

1. Email Service: Set up dedicated transactional email service
2. Wallet Security: Implement proper key management
3. Error Monitoring: Add Sentry or similar
4. Analytics: Add user tracking if needed
5. Rate Limiting: Implement on email sending
6. Backup: Set up database for wallet mappings
