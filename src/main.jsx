import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { PrivyProvider } from '@privy-io/react-auth';
import App from './App';

const privyAppId = import.meta.env.VITE_PRIVY_APP_ID;

const SetupRequired = () => (
  <div style={{
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    justifyContent: 'center',
    height: '100vh',
    fontFamily: 'Inter, system-ui, sans-serif',
    padding: '2rem',
    backgroundColor: '#f9fafb'
  }}>
    <div style={{
      maxWidth: '600px',
      backgroundColor: 'white',
      padding: '2rem',
      borderRadius: '12px',
      boxShadow: '0 4px 6px rgba(0,0,0,0.1)',
      border: '2px solid #fbbf24'
    }}>
      <h1 style={{
        color: '#dc2626',
        marginBottom: '1rem',
        fontSize: '1.5rem',
        fontWeight: '600'
      }}>
        Setup Required
      </h1>
      <p style={{
        color: '#374151',
        marginBottom: '1rem',
        lineHeight: '1.6'
      }}>
        Please configure your Privy App ID to continue.
      </p>
      <div style={{
        backgroundColor: '#f9fafb',
        padding: '1rem',
        borderRadius: '8px',
        marginBottom: '1rem',
        border: '1px solid #e5e7eb'
      }}>
        <p style={{
          fontWeight: '600',
          marginBottom: '0.5rem',
          color: '#1f2937'
        }}>
          Steps to setup:
        </p>
        <ol style={{
          paddingLeft: '1.5rem',
          color: '#4b5563',
          lineHeight: '1.8'
        }}>
          <li>Go to <a href="https://dashboard.privy.io" target="_blank" style={{ color: '#5f2dc4', textDecoration: 'underline' }}>dashboard.privy.io</a></li>
          <li>Create a new app or select existing app</li>
          <li>Copy your App ID</li>
          <li>Add it to the <code style={{
            backgroundColor: '#f3f4f6',
            padding: '2px 6px',
            borderRadius: '4px',
            fontFamily: 'monospace'
          }}>.env</code> file:</li>
        </ol>
      </div>
      <pre style={{
        backgroundColor: '#1f2937',
        color: '#f9fafb',
        padding: '1rem',
        borderRadius: '8px',
        overflow: 'auto',
        fontSize: '0.875rem',
        fontFamily: 'monospace'
      }}>
VITE_PRIVY_APP_ID=your_app_id_here
      </pre>
      <p style={{
        marginTop: '1rem',
        color: '#6b7280',
        fontSize: '0.875rem'
      }}>
        After adding the App ID, restart the development server.
      </p>
    </div>
  </div>
);

if (!privyAppId) {
  ReactDOM.createRoot(document.getElementById('root')).render(
    <React.StrictMode>
      <SetupRequired />
    </React.StrictMode>
  );
} else {
  ReactDOM.createRoot(document.getElementById('root')).render(
    <React.StrictMode>
      <PrivyProvider
        appId={privyAppId}
        config={{
          loginMethods: ['email', 'google'],
          appearance: {
            theme: 'light',
            accentColor: '#5f2dc4',
            logo: '/logo.png',
          },
          embeddedWallets: {
            createOnLogin: 'users-without-wallets',
          },
        }}
      >
        <BrowserRouter>
          <App />
        </BrowserRouter>
      </PrivyProvider>
    </React.StrictMode>
  );
}
