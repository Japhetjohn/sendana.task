# Sendana MVP - Borderless Banking Application

## Overview

Sendana is a borderless banking application built with React, Privy authentication, and Stellar blockchain integration.

## Features

1. Email and Google Sign-in/Sign-up via Privy
2. Automatic Stellar wallet creation on account creation
3. Dashboard with wallet balance display
4. Wallet address page with QR code for receiving payments
5. Welcome email sent on account creation

## Quick Start

### 1. Install Dependencies
```bash
npm install
```

### 2. Configure Environment
Create a `.env` file in the root directory:
```
VITE_PRIVY_APP_ID=your_privy_app_id
VITE_STELLAR_NETWORK=testnet
VITE_EMAIL_SERVICE_ENDPOINT=your_email_api_endpoint
VITE_EMAIL_SERVICE_API_KEY=your_api_key
```

See `SETUP.md` for detailed setup instructions.
See `EMAIL_SETUP.md` for email service configuration.

### 3. Run Development Server
```bash
npm run dev
```

### 4. Build for Production
```bash
npm run build
```

## Documentation

- `SETUP.md` - Complete setup and configuration guide
- `EMAIL_SETUP.md` - Email service setup requirements and options

## Tech Stack

- **Frontend:** React 18, React Router
- **Authentication:** Privy
- **Blockchain:** Stellar
- **Build Tool:** Vite
- **Styling:** Tailwind CSS (via CDN in existing designs)

## Project Structure

```
src/
├── App.jsx              # Main app with routing
├── main.jsx             # Entry point with Privy provider
├── pages/
│   ├── Login.jsx        # Login/Signup page
│   ├── Dashboard.jsx    # Main dashboard
│   └── WalletAddress.jsx # Wallet display with QR
├── services/
│   ├── stellar.js       # Stellar wallet operations
│   └── email.js         # Email sending service
public/
├── logo.png             # Sendana logo
├── avatar.png           # User avatar
└── *.jpg                # Login carousel images
```

## Development Notes

- Testnet mode creates funded Stellar wallets via Friendbot
- Public keys are stored in localStorage for quick access
- Secret keys are generated but not stored (security)
- For production, use Stellar mainnet and proper key management

## Security

IMPORTANT: Current implementation is for MVP/testing purposes.

For production:
- Implement proper key management
- Use Privy's embedded wallet features
- Never store secret keys in localStorage
- Set up proper backend for sensitive operations

## Support

For detailed setup help, see:
- `SETUP.md` for general setup
- `EMAIL_SETUP.md` for email configuration
- Privy docs: https://docs.privy.io
- Stellar docs: https://developers.stellar.org
