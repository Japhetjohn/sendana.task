import * as StellarSdk from '@stellar/stellar-sdk';

const STELLAR_NETWORK = import.meta.env.VITE_STELLAR_NETWORK || 'testnet';

const server = new StellarSdk.Horizon.Server(
  STELLAR_NETWORK === 'mainnet'
    ? 'https://horizon.stellar.org'
    : 'https://horizon-testnet.stellar.org'
);

export const createStellarWallet = async () => {
  try {
    const pair = StellarSdk.Keypair.random();

    if (STELLAR_NETWORK === 'testnet') {
      await fetch(
        `https://friendbot.stellar.org?addr=${encodeURIComponent(pair.publicKey())}`
      );
    }

    return {
      publicKey: pair.publicKey(),
      secretKey: pair.secret(),
    };
  } catch (error) {
    console.error('Error creating Stellar wallet:', error);
    throw error;
  }
};

export const getAccountBalance = async (publicKey) => {
  try {
    const account = await server.loadAccount(publicKey);
    return account.balances;
  } catch (error) {
    console.error('Error fetching balance:', error);
    return [];
  }
};

export const encryptAndStoreWallet = async (wallet, privyUserId) => {
  try {
    const encrypted = {
      userId: privyUserId,
      publicKey: wallet.publicKey,
      timestamp: Date.now(),
    };

    localStorage.setItem(
      `stellar_wallet_${privyUserId}`,
      JSON.stringify(encrypted)
    );

    return encrypted;
  } catch (error) {
    console.error('Error storing wallet:', error);
    throw error;
  }
};

export const getStoredWallet = (privyUserId) => {
  try {
    const stored = localStorage.getItem(`stellar_wallet_${privyUserId}`);
    return stored ? JSON.parse(stored) : null;
  } catch (error) {
    console.error('Error retrieving wallet:', error);
    return null;
  }
};
