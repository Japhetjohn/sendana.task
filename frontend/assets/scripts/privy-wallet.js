// Privy Wallet Integration for Sendana
const PRIVY_APP_ID = 'cmhow02lw00b3l10cz7f0gbpu';

class PrivyWalletManager {
    constructor() {
        this.privy = null;
        this.initialized = false;
    }

    // Initialize Privy SDK
    async initialize() {
        if (this.initialized) return true;

        try {
            // Check if Privy SDK is loaded
            if (typeof window.Privy === 'undefined') {
                console.error('Privy SDK not loaded');
                return false;
            }

            // Initialize Privy client
            this.privy = new window.Privy({
                appId: PRIVY_APP_ID
            });

            this.initialized = true;
            console.log('Privy SDK initialized successfully');
            return true;
        } catch (error) {
            console.error('Failed to initialize Privy SDK:', error);
            return false;
        }
    }

    // Create Stellar embedded wallet for user
    async createStellarWallet(userId, userToken) {
        try {
            console.log('Creating Stellar wallet via Privy...');

            // Initialize if not already done
            if (!this.initialized) {
                const initSuccess = await this.initialize();
                if (!initSuccess) {
                    throw new Error('Failed to initialize Privy SDK');
                }
            }

            // Request wallet creation from Privy
            // Note: Privy's embedded wallet is created through their UI/API
            // For vanilla JS, we use the Privy REST API via our backend
            const response = await fetch(`${API_URL}/privy/create-wallet`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${userToken}`
                },
                body: JSON.stringify({
                    userId: userId,
                    chainType: 'stellar'
                })
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Failed to create wallet');
            }

            const walletData = await response.json();
            console.log('Stellar wallet created:', walletData);

            return walletData;
        } catch (error) {
            console.error('Error creating Stellar wallet:', error);
            throw error;
        }
    }

    // Show wallet creation UI (modal/overlay)
    async showWalletCreationUI(userId, userToken, onSuccess, onError) {
        try {
            // Create modal overlay
            const modal = document.createElement('div');
            modal.id = 'privy-wallet-modal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
            `;

            const modalContent = document.createElement('div');
            modalContent.style.cssText = `
                background: white;
                padding: 32px;
                border-radius: 16px;
                max-width: 500px;
                text-align: center;
            `;

            modalContent.innerHTML = `
                <h2 style="font-size: 24px; margin-bottom: 16px; color: #333;">Creating Your Wallet</h2>
                <p style="margin-bottom: 24px; color: #666;">Please wait while we set up your secure Stellar wallet...</p>
                <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #5F2DC4; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <style>
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                </style>
            `;

            modal.appendChild(modalContent);
            document.body.appendChild(modal);

            // Create wallet
            const walletData = await this.createStellarWallet(userId, userToken);

            // Remove modal
            document.body.removeChild(modal);

            // Success
            if (onSuccess) {
                onSuccess(walletData);
            }

            return walletData;
        } catch (error) {
            // Remove modal if exists
            const modal = document.getElementById('privy-wallet-modal');
            if (modal) {
                document.body.removeChild(modal);
            }

            // Error
            if (onError) {
                onError(error);
            }

            throw error;
        }
    }
}

// Export singleton instance
window.privyWalletManager = new PrivyWalletManager();
