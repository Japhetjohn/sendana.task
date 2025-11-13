import React, { useEffect, useState } from 'react';
import { usePrivy } from '@privy-io/react-auth';

function DashboardPage() {
  const { user, logout, getAccessToken } = usePrivy();
  const [userData, setUserData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchUserData = async () => {
      try {
        const token = await getAccessToken();
        const response = await fetch('http://localhost:3000/api/auth/user', {
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });

        if (response.ok) {
          const data = await response.json();
          setUserData(data.user);
        }
      } catch (error) {
        console.error('Error fetching user data:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchUserData();
  }, [getAccessToken]);

  const handleLogout = async () => {
    await logout();
  };

  if (loading) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh' }}>
        Loading dashboard...
      </div>
    );
  }

  const userName = userData?.profile?.name || userData?.email?.split('@')[0] || 'User';

  return (
    <div className="dashboard-page">
      <div className="flex">
        <div id="sidebarOverlay" className="sidebar-overlay"></div>

        <div
          id="sidebar"
          className="w-64 sm:w-72 h-screen pt-4 lg:pt-8 bg-white shadow-md fixed left-0 top-0 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40"
        >
          <div className="px-4 lg:px-6 flex justify-between items-center">
            <a href="#" className="flex items-center gap-x-2.5">
              <span>
                <img src="/images/logo.png" alt="logo" className="img-fluid" />
              </span>
              <span className="font-semibold text-xl leading-6 text-col-5">Sendana</span>
            </a>
          </div>

          <nav className="mt-8 px-4 overflow-y-auto h-[calc(100vh-100px)]">
            <div className="pb-[18px]">
              <ul className="grid gap-y-2">
                <li>
                  <a href="#" className="sidebar-nav-link bg-col-3 text-col-4">
                    <span><i className="fa-solid fa-house"></i></span>
                    <span>Home</span>
                  </a>
                </li>
                <li>
                  <a href="#" className="sidebar-nav-link group">
                    <span><i className="fa-solid fa-clock-rotate-left transition-all duration-300 text-col-6 group-hover:text-col-4"></i></span>
                    <span> All Transactions</span>
                  </a>
                </li>
                <li>
                  <a href="#" className="sidebar-nav-link group">
                    <i className="fa-solid fa-link transition-all duration-300 text-col-6 group-hover:text-col-4"></i>
                    <span> Link Accounts</span>
                  </a>
                </li>
                <li>
                  <a href="#" className="sidebar-nav-link group">
                    <span><i className="fa-solid fa-credit-card transition-all duration-300 text-col-6 group-hover:text-col-4"></i></span>
                    <span> Virtual USD Account</span>
                  </a>
                </li>
                <li>
                  <a href="#" className="sidebar-nav-link group">
                    <span><i className="fa-solid fa-wallet transition-all duration-300 text-col-6 group-hover:text-col-4"></i></span>
                    <span> Wallet Address</span>
                  </a>
                </li>
              </ul>
            </div>

            <div className="border-t border-col-2 pt-[18px] py-3">
              <ul className="grid gap-y-2">
                <li>
                  <a href="#" className="sidebar-nav-link group">
                    <span><i className="fa-solid fa-gift transition-all duration-300 text-col-6 group-hover:text-col-4"></i></span>
                    <span> Refer & Earn</span>
                  </a>
                </li>
                <li>
                  <a href="#" className="sidebar-nav-link group">
                    <span><i className="fa-solid fa-comment transition-all duration-300 text-col-6 group-hover:text-col-4"></i></span>
                    <span> Share Feedback</span>
                  </a>
                </li>
                <li>
                  <a href="#" className="sidebar-nav-link group">
                    <span><i className="fa-regular fa-circle-question transition-all duration-300 text-col-6 group-hover:text-col-4"></i></span>
                    <span> Help & Support</span>
                  </a>
                </li>
                <li>
                  <button onClick={handleLogout} className="sidebar-nav-link group w-full text-left">
                    <span><i className="fa-solid fa-right-from-bracket transition-all duration-300 text-col-6 group-hover:text-col-4"></i></span>
                    <span> Logout</span>
                  </button>
                </li>
              </ul>
            </div>
          </nav>
        </div>

        <div className="flex-1 min-h-screen p-4 lg:p-8 transition-all duration-300 lg:ml-72">
          <div className="grid grid-cols-12 max-lg:items-center mb-6 lg:mb-8">
            <div className="lg:col-span-7 col-span-12 order-3 lg:order-1 max-lg:mt-6">
              <h1 className="font-semibold text-lg lg:text-3xl leading-[135%] lg:leading-[38px] mb-1 text-col-7">
                Good morning, {userName}!
              </h1>
              <p className="font-normal text-xs leading-[150%] lg:text-base text-col-8">
                Send. Receive. Smile. Repeat.
              </p>
            </div>
          </div>

          <div className="grid gap-6">
            <div className="bg-white p-6 rounded-lg shadow">
              <h2 className="text-xl font-semibold mb-4">Welcome to Sendana</h2>
              <p className="text-gray-600 mb-4">Your account has been successfully created!</p>

              <div className="bg-gray-50 p-4 rounded-lg">
                <h3 className="font-semibold mb-2">Account Information:</h3>
                <p className="text-sm text-gray-700"><strong>Email:</strong> {userData?.email}</p>
                <p className="text-sm text-gray-700"><strong>Auth Provider:</strong> {userData?.authProvider}</p>
                <p className="text-sm text-gray-700"><strong>Balance:</strong> ${userData?.balance || 0}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default DashboardPage;
