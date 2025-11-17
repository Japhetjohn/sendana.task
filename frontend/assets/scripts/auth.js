// Sendana Authentication Module
const API_URL = '/backend/api';

class SendanaAuth {
    constructor() {
        this.currentUser = null;
        this.privyAppId = null;
        this.privy = null;
        this.init();
    }

    async init() {
        // Initialize Google OAuth
        this.initGoogleOAuth();

        // Only protect dashboard page - don't auto-redirect from login/signup
        // Users should be able to access login/signup pages freely
    }

    initGoogleOAuth() {
        // Wait for Google Identity Services to load
        if (typeof google !== 'undefined' && google.accounts) {
            this.googleLoaded = true;
            console.log('Google OAuth SDK loaded successfully');
        } else {
            // Retry after a delay
            setTimeout(() => this.initGoogleOAuth(), 500);
        }
    }

    // Wait for Google SDK to load (with timeout)
    waitForGoogleSDK(timeout = 10000) {
        return new Promise((resolve, reject) => {
            const startTime = Date.now();
            const checkInterval = setInterval(() => {
                if (typeof google !== 'undefined' && google.accounts) {
                    clearInterval(checkInterval);
                    resolve(true);
                } else if (Date.now() - startTime > timeout) {
                    clearInterval(checkInterval);
                    reject(new Error('Google OAuth SDK failed to load. Please check your internet connection.'));
                }
            }, 100);
        });
    }

    // Check if user should have access to dashboard
    async checkDashboardAccess() {
        const token = this.getToken();

        if (window.location.pathname.includes('dashboard.html')) {
            if (!token) {
                // Not logged in, redirect to login
                window.location.href = 'index.html';
                return;
            }
            // Fetch fresh user data from database
            try {
                await this.fetchUserData();
            } catch (error) {
                // Token invalid or expired, redirect to login
                window.location.href = 'index.html';
            }
        }
    }

    // Sign up with email and password
    async signup(email, password) {
        try {
            const response = await fetch(`${API_URL}/auth/signup`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Signup failed');
            }

            // After successful signup, create Privy wallet
            console.log('Account created, now creating Privy wallet...');

            try {
                // Create Privy wallet via frontend
                const walletData = await window.privyWalletManager.showWalletCreationUI(
                    data.user.privyId || data.user.email,
                    data.token,
                    (wallet) => {
                        console.log('Wallet created successfully:', wallet);
                    },
                    (error) => {
                        console.error('Wallet creation failed:', error);
                    }
                );

                console.log('Privy wallet created:', walletData);

                // Save token and user
                this.saveSession(data.token, data.user);

                // Redirect to dashboard with wallet
                alert('Account and wallet created successfully!');
                window.location.href = 'dashboard.html';

            } catch (walletError) {
                console.error('Wallet creation error:', walletError);
                // Still save session even if wallet creation fails
                this.saveSession(data.token, data.user);
                alert('Account created, but wallet creation failed. You can try again from the dashboard.');
                window.location.href = 'dashboard.html';
            }

        } catch (error) {
            console.error('Signup error:', error);
            throw error;
        }
    }

    // Login with email and password
    async login(email, password) {
        try {
            const response = await fetch(`${API_URL}/auth/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Login failed');
            }

            // Save token and user
            this.saveSession(data.token, data.user);

            // Redirect to dashboard
            window.location.href = 'dashboard.html';

        } catch (error) {
            console.error('Login error:', error);
            throw error;
        }
    }

    // Save session data (token only - user data fetched from database)
    saveSession(token, user) {
        localStorage.setItem('sendana_token', token);
        this.currentUser = user;
    }

    // Get stored token
    getToken() {
        return localStorage.getItem('sendana_token');
    }

    // Get current user from memory (use fetchUserData to refresh from database)
    getCurrentUser() {
        return this.currentUser;
    }

    // Check if authenticated
    isAuthenticated() {
        const token = this.getToken();
        return !!token;
    }

    // Logout
    logout() {
        localStorage.removeItem('sendana_token');
        this.currentUser = null;
        window.location.href = 'index.html';
    }

    // Get user data from API
    async fetchUserData() {
        try {
            const token = this.getToken();
            if (!token) {
                throw new Error('No authentication token');
            }

            const response = await fetch(`${API_URL}/auth/user`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Failed to fetch user data');
            }

            // Update current user in memory only
            this.currentUser = data.user;

            return data.user;

        } catch (error) {
            console.error('Fetch user error:', error);
            // If token is invalid, logout
            if (error.message.includes('token') || error.message.includes('expired')) {
                this.logout();
            }
            throw error;
        }
    }

    // Sign in with Google using Google OAuth
    async signInWithGoogle() {
        try {
            // Wait for Google OAuth SDK to load
            await this.waitForGoogleSDK();

            // Check if Client ID is configured
            if (!window.GOOGLE_OAUTH_CONFIG ||
                !window.GOOGLE_OAUTH_CONFIG.clientId ||
                window.GOOGLE_OAUTH_CONFIG.clientId.includes('YOUR_GOOGLE_CLIENT_ID_HERE')) {
                throw new Error('Google OAuth not configured!\n\nPlease follow these steps:\n1. Open: SETUP_GOOGLE_AUTH.md\n2. Follow the 5-minute setup guide\n3. Update /frontend/config/google-oauth.js with your Client ID');
            }
        } catch (error) {
            return Promise.reject(error);
        }

        return new Promise((resolve, reject) => {

            const client = google.accounts.oauth2.initTokenClient({
                client_id: window.GOOGLE_OAUTH_CONFIG.clientId,
                scope: 'email profile',
                callback: async (response) => {
                    if (response.error) {
                        reject(new Error(response.error));
                        return;
                    }

                    try {
                        // Get user info from Google
                        const userInfoResponse = await fetch('https://www.googleapis.com/oauth2/v3/userinfo', {
                            headers: {
                                'Authorization': `Bearer ${response.access_token}`
                            }
                        });

                        const userInfo = await userInfoResponse.json();

                        // Send to backend
                        const backendResponse = await fetch(`${API_URL}/auth/google`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                googleToken: response.access_token,
                                email: userInfo.email,
                                name: userInfo.name,
                                picture: userInfo.picture,
                                sub: userInfo.sub
                            })
                        });

                        const data = await backendResponse.json();

                        if (!backendResponse.ok) {
                            throw new Error(data.error || 'Google sign in failed');
                        }

                        // Save token and user
                        this.saveSession(data.token, data.user);

                        // Redirect to dashboard
                        window.location.href = 'dashboard.html';
                        resolve(data);
                    } catch (error) {
                        reject(error);
                    }
                },
            });

            // Request access token
            client.requestAccessToken();
        });
    }
}

// Initialize auth
const auth = new SendanaAuth();
window.sendanaAuth = auth;

// Handle DOM events
document.addEventListener('DOMContentLoaded', function() {
    const emailForm = document.getElementById('email-form');

    if (emailForm) {
        emailForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm-password');

            const email = emailInput.value.trim();
            const password = passwordInput.value;

            // Validation
            if (!email) {
                alert('Please enter your email address');
                return;
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                alert('Please enter a valid email address');
                return;
            }

            if (!password) {
                alert('Please enter your password');
                return;
            }

            if (password.length < 8) {
                alert('Password must be at least 8 characters');
                return;
            }

            try {
                // Check if this is signup page
                if (confirmPasswordInput) {
                    // Signup flow
                    const confirmPassword = confirmPasswordInput.value;

                    if (password !== confirmPassword) {
                        alert('Passwords do not match');
                        return;
                    }

                    // Disable submit button
                    const submitBtn = emailForm.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Creating account...';

                    await auth.signup(email, password);

                } else {
                    // Login flow
                    const submitBtn = emailForm.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Signing in...';

                    await auth.login(email, password);
                }
            } catch (error) {
                alert(error.message);
                // Re-enable submit button
                const submitBtn = emailForm.querySelector('button[type="submit"]');
                submitBtn.disabled = false;
                submitBtn.textContent = confirmPasswordInput ? 'Sign up' : 'Sign in';
            }
        });
    }

    // Handle Google button
    const googleBtn = document.getElementById('google-signin-btn');
    if (googleBtn) {
        googleBtn.addEventListener('click', async function(e) {
            e.preventDefault();

            // Disable button during auth
            googleBtn.disabled = true;
            const originalText = googleBtn.innerHTML;
            googleBtn.innerHTML = '<span>Signing in with Google...</span>';

            try {
                await auth.signInWithGoogle();
            } catch (error) {
                alert(error.message || 'Google authentication failed');
                // Re-enable button
                googleBtn.disabled = false;
                googleBtn.innerHTML = originalText;
            }
        });
    }
});
