export const privyConfig = {
  appId: import.meta.env.VITE_PRIVY_APP_ID,
  config: {
    loginMethods: ['email', 'google'],
    appearance: {
      theme: 'light',
      accentColor: '#5f2dc4',
      logo: '/logo.png',
    },
    embeddedWallets: {
      createOnLogin: 'users-without-wallets',
      requireUserPasswordOnCreate: false,
    },
    supportedChains: [],
  },
};
