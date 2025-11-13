// Privy Authentication Module
// This module handles Email and Google authentication using Privy SDK
import { PrivyClient } from '@privy-io/js-sdk-core';

const PRIVY_APP_ID = 'cmhow02lw00b3l10cz7f0gbpu';
const API_URL = 'http://localhost:3000';

class SendanaAuth {
    constructor() {
        this.privyClient = null;
        this.currentUser = null;
        this.pendingEmailAuth = null;
        this.init();
    }

    async init() {
        try {
            // Initialize Privy Client
            this.privyClient = new PrivyClient({
                appId: PRIVY_APP_ID,
            });
            console.log('Privy Auth initialized successfully');
        } catch (error) {
            console.error('Failed to initialize Privy:', error);
            throw error;
        }
    }

    // Email Authentication - Step 1: Send OTP code
    async sendEmailCode(email) {
        try {
            if (!this.privyClient) {
                throw new Error('Privy client not initialized');
            }

            await this.privyClient.auth.email.sendCode({ email });
            this.pendingEmailAuth = email;
            console.log('Email code sent to:', email);
            return true;
        } catch (error) {
            console.error('Failed to send email code:', error);
            throw error;
        }
    }

    // Email Authentication - Step 2: Verify code and login
    async loginWithEmailCode(code) {
        try {
            if (!this.privyClient || !this.pendingEmailAuth) {
                throw new Error('No pending email authentication');
            }

            const authResult = await this.privyClient.auth.email.loginWithCode({
                email: this.pendingEmailAuth,
                code: code,
            });

            // Get the access token
            const accessToken = authResult.token;

            // Authenticate with our backend
            await this.authenticateWithBackend(accessToken, {
                email: this.pendingEmailAuth,
                provider: 'email'
            });

            this.pendingEmailAuth = null;
            return true;
        } catch (error) {
            console.error('Email login error:', error);
            throw error;
        }
    }

    // Google OAuth Authentication
    async loginWithGoogle() {
        try {
            if (!this.privyClient) {
                throw new Error('Privy client not initialized');
            }

            // Generate OAuth URL
            const { url, state } = await this.privyClient.auth.oauth.generateURL({
                provider: 'google',
            });

            // Store state for verification after redirect
            localStorage.setItem('privy_oauth_state', state);
            localStorage.setItem('privy_oauth_provider', 'google');

            // Redirect to Google OAuth
            window.location.href = url;
        } catch (error) {
            console.error('Google OAuth error:', error);
            throw error;
        }
    }

    // Handle OAuth callback after redirect
    async handleOAuthCallback() {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const code = urlParams.get('code');
            const state = urlParams.get('state');
            const storedState = localStorage.getItem('privy_oauth_state');
            const provider = localStorage.getItem('privy_oauth_provider');

            if (!code || !state || state !== storedState) {
                throw new Error('Invalid OAuth callback');
            }

            // Login with OAuth code
            const authResult = await this.privyClient.auth.oauth.loginWithCode({
                code,
                state,
            });

            // Get the access token
            const accessToken = authResult.token;

            // Authenticate with our backend
            await this.authenticateWithBackend(accessToken, {
                provider: provider
            });

            // Clean up
            localStorage.removeItem('privy_oauth_state');
            localStorage.removeItem('privy_oauth_provider');

            return true;
        } catch (error) {
            console.error('OAuth callback error:', error);
            throw error;
        }
    }

    // Authenticate with backend and save user to MongoDB
    async authenticateWithBackend(privyToken, additionalData = {}) {
        try {
            const response = await fetch(`${API_URL}/api/auth/login`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${privyToken}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(additionalData)
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || 'Authentication failed');
            }

            const data = await response.json();
            this.currentUser = data.user;

            // Save session
            localStorage.setItem('sendana_user', JSON.stringify(data.user));
            localStorage.setItem('sendana_token', privyToken);

            // Redirect to dashboard
            window.location.href = '/dashboard.html';
        } catch (error) {
            console.error('Backend authentication error:', error);
            throw error;
        }
    }

    // Get current user
    getCurrentUser() {
        const userStr = localStorage.getItem('sendana_user');
        return userStr ? JSON.parse(userStr) : null;
    }

    // Check if user is authenticated
    isAuthenticated() {
        const token = localStorage.getItem('sendana_token');
        const user = localStorage.getItem('sendana_user');
        return !!(token && user);
    }

    // Logout
    logout() {
        localStorage.removeItem('sendana_user');
        localStorage.removeItem('sendana_token');
        window.location.href = '/index.html';
    }
}

// Initialize and export global auth instance
const sendanaAuth = new SendanaAuth();
window.sendanaAuth = sendanaAuth;

export default sendanaAuth;
