import { Routes, Route, Navigate } from 'react-router-dom';
import { usePrivy } from '@privy-io/react-auth';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import WalletAddress from './pages/WalletAddress';

function App() {
  const { ready, authenticated } = usePrivy();

  if (!ready) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh' }}>
        <p>Loading...</p>
      </div>
    );
  }

  return (
    <Routes>
      <Route
        path="/"
        element={authenticated ? <Navigate to="/dashboard" /> : <Login />}
      />
      <Route
        path="/dashboard"
        element={authenticated ? <Dashboard /> : <Navigate to="/" />}
      />
      <Route
        path="/wallet"
        element={authenticated ? <WalletAddress /> : <Navigate to="/" />}
      />
    </Routes>
  );
}

export default App;
