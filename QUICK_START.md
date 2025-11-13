# Quick Start - Get Your Privy App ID in 2 Minutes

## Step 1: Go to Privy Dashboard
Visit: https://dashboard.privy.io

## Step 2: Sign Up or Log In
- Click "Sign Up" if you don't have an account
- Or "Log In" if you already have one

## Step 3: Create Your App
1. Click "Create App" or "New App"
2. Give it a name (e.g., "Sendana")
3. Click "Create"

## Step 4: Copy Your App ID
1. You'll see your App ID on the dashboard
2. It looks like: `clxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`
3. Click the copy icon next to it

## Step 5: Add to .env File
Open the `.env` file and add your App ID:

```
VITE_PRIVY_APP_ID=clxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
VITE_STELLAR_NETWORK=testnet
VITE_EMAIL_SERVICE_API_KEY=
VITE_EMAIL_SERVICE_ENDPOINT=
```

## Step 6: Restart Server
```bash
# Stop the server (Ctrl+C)
# Then restart:
npm run dev
```

## That's It!
The app will now load and you'll see the login page.

## Need Help?
If you get stuck:
1. Make sure you copied the entire App ID
2. Make sure there are no extra spaces
3. Restart the dev server completely
4. Check the browser console for any errors
