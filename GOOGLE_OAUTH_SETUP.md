# Google OAuth Setup Guide

## Google OAuth is Now Working!

I've set up Google Sign In/Sign Up using Google's official OAuth SDK. Here's what's configured:

## Current Configuration

The app is configured with a Google OAuth Client ID that's already set up in:
- **File:** `/frontend/config/google-oauth.js`
- **Client ID:** `440348919935-8tjjbg0l8q98hbptasvp14vvq45e1b8p.apps.googleusercontent.com`

## How It Works

1. **User clicks "Sign in with Google"**
2. **Google OAuth popup appears** asking for permission
3. **User authorizes the app**
4. **Google returns user info** (email, name, profile picture)
5. **Backend creates/updates user** in MongoDB
6. **User is logged in** and redirected to dashboard

## Testing Google OAuth

1. Start the server:
   ```bash
   ./start-server.sh
   ```

2. Go to: http://localhost:8000

3. Click "Sign in with Google" or "Sign up with Google"

4. Complete the Google authorization

5. You'll be logged in and redirected to the dashboard

## Important Notes

- ✅ Google OAuth saves all data to **MongoDB**, not localStorage
- ✅ Works for both **sign in** (existing users) and **sign up** (new users)
- ✅ If a user signs up with email first, then uses Google with the same email, their account is updated to use Google auth
- ✅ User profile picture and name from Google are automatically saved

## If You Want to Use Your Own Google OAuth Client ID

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable "Google Identity Services"
4. Go to "Credentials" > "Create Credentials" > "OAuth 2.0 Client ID"
5. Add authorized JavaScript origins:
   - `http://localhost:8000`
   - `http://localhost:3000` (if using different port)
   - Your production domain
6. Copy the Client ID
7. Edit `/frontend/config/google-oauth.js` and replace the `clientId`

## Troubleshooting

### "Google OAuth not loaded" error
- Make sure you have internet connection
- Google SDK loads from `https://accounts.google.com/gsi/client`
- Check browser console for errors

### "Popup blocked"
- Allow popups for localhost:8000 in your browser
- Try clicking the button again

### "Invalid client ID"
- Make sure the client ID in `google-oauth.js` is correct
- Verify authorized origins in Google Cloud Console

## Security

- ✅ Google tokens are verified on the backend
- ✅ User emails are validated
- ✅ All data stored securely in MongoDB
- ✅ Session tokens expire after 24 hours
