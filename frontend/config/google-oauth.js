// Google OAuth Configuration
//
// ⚠️ YOU MUST SET UP YOUR OWN GOOGLE OAUTH CLIENT ID ⚠️
//
// Follow these steps:
// 1. Go to: https://console.cloud.google.com/
// 2. Create a new project (or select existing)
// 3. Go to: APIs & Services > Credentials
// 4. Click "Create Credentials" > "OAuth 2.0 Client ID"
// 5. If prompted, configure OAuth consent screen first:
//    - User Type: External
//    - App name: Sendana
//    - User support email: Your email
//    - Developer contact: Your email
//    - Click Save and Continue through all steps
// 6. Back to Create OAuth Client ID:
//    - Application type: Web application
//    - Name: Sendana Web Client
//    - Authorized JavaScript origins: http://localhost:8000
//    - Click CREATE
// 7. Copy the Client ID and paste it below

const GOOGLE_OAUTH_CONFIG = {
    // Your Google OAuth Client ID
    clientId: '58343427564-pkoejrf47ei2bsefa66u9hsu0rc95t2d.apps.googleusercontent.com',

    // Authorized redirect URI
    redirectUri: window.location.origin
};

// Make it available globally
window.GOOGLE_OAUTH_CONFIG = GOOGLE_OAUTH_CONFIG;
