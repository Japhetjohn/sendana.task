import React, { useEffect } from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import { usePrivy } from '@privy-io/react-auth';
import LoginPage from './pages/LoginPage';
import SignupPage from './pages/SignupPage';
import DashboardPage from './pages/DashboardPage';

function App() {
  const { ready, authenticated, user, getAccessToken } = usePrivy();

  useEffect(() => {
    const syncUserWithBackend = async () => {
      if (authenticated && user) {
        try {
          const token = await getAccessToken();

          // Sync user with backend
          const response = await fetch('http://localhost:3000/api/auth/login', {
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json',
            },
          });

          if (!response.ok) {
            console.error('Failed to sync user with backend');
          }
        } catch (error) {
          console.error('Error syncing user:', error);
        }
      }
    };

    if (ready && authenticated) {
      syncUserWithBackend();
    }
  }, [ready, authenticated, user, getAccessToken]);

  if (!ready) {
    return (
      <div style={{
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        height: '100vh',
        fontSize: '18px',
      }}>
        Loading...
      </div>
    );
  }

  return (
    <Routes>
      <Route
        path="/"
        element={authenticated ? <Navigate to="/dashboard" /> : <LoginPage />}
      />
      <Route
        path="/signup"
        element={authenticated ? <Navigate to="/dashboard" /> : <SignupPage />}
      />
      <Route
        path="/dashboard"
        element={authenticated ? <DashboardPage /> : <Navigate to="/" />}
      />
    </Routes>
  );
}

export default App;
