// Sendana Authentication Module
const API_URL = '/backend/api';

class SendanaAuth {
    constructor() {
        this.currentUser = null;
        this.init();
    }

    async init() {
        // Check if user is already logged in
        const user = this.getCurrentUser();
        const token = this.getToken();

        if (user && token && this.isOnLoginPage()) {
            // Redirect to dashboard if already logged in
            window.location.href = 'dashboard.html';
        }
    }

    isOnLoginPage() {
        return window.location.pathname.includes('index.html') ||
               window.location.pathname.includes('signup.html') ||
               window.location.pathname === '/';
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

            // Save token and user
            this.saveSession(data.token, data.user);

            // Redirect to sign in page after successful signup
            alert('Account created successfully! Please sign in.');
            window.location.href = 'index.html';

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

    // Save session data
    saveSession(token, user) {
        localStorage.setItem('sendana_token', token);
        localStorage.setItem('sendana_user', JSON.stringify(user));
        this.currentUser = user;
    }

    // Get stored token
    getToken() {
        return localStorage.getItem('sendana_token');
    }

    // Get current user
    getCurrentUser() {
        const userStr = localStorage.getItem('sendana_user');
        return userStr ? JSON.parse(userStr) : null;
    }

    // Check if authenticated
    isAuthenticated() {
        const token = this.getToken();
        const user = this.getCurrentUser();
        return !!(token && user);
    }

    // Logout
    logout() {
        localStorage.removeItem('sendana_user');
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

            // Update stored user data
            localStorage.setItem('sendana_user', JSON.stringify(data.user));
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

    // Handle Google button (for future implementation)
    const googleBtn = document.getElementById('google-signin-btn');
    if (googleBtn) {
        googleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            alert('Google authentication will be available soon!');
        });
    }
});
