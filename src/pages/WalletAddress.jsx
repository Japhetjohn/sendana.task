import { useEffect, useState, useRef } from 'react';
import { usePrivy } from '@privy-io/react-auth';
import { useNavigate } from 'react-router-dom';
import { getStoredWallet } from '../services/stellar';
import QRCode from 'qrcode';
import './Dashboard.css';

function WalletAddress() {
  const { user, logout } = usePrivy();
  const navigate = useNavigate();
  const [wallet, setWallet] = useState(null);
  const [copied, setCopied] = useState(false);
  const qrRef = useRef(null);

  useEffect(() => {
    const initializeWallet = async () => {
      if (user) {
        const storedWallet = getStoredWallet(user.id);
        setWallet(storedWallet);

        if (storedWallet && qrRef.current) {
          try {
            await QRCode.toCanvas(qrRef.current, storedWallet.publicKey, {
              width: 256,
              margin: 2,
              color: {
                dark: '#111827',
                light: '#FFFFFF',
              },
            });
          } catch (error) {
            console.error('Error generating QR code:', error);
          }
        }
      }
    };

    initializeWallet();
  }, [user]);

  const copyToClipboard = async () => {
    if (wallet?.publicKey) {
      try {
        await navigator.clipboard.writeText(wallet.publicKey);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
      } catch (error) {
        console.error('Error copying to clipboard:', error);
      }
    }
  };

  const handleLogout = async () => {
    await logout();
    navigate('/');
  };

  const handleNavigateToDashboard = () => {
    navigate('/dashboard');
  };

  return (
    <div className="flex">
      <div id="sidebarOverlay" className="sidebar-overlay"></div>

      <div
        id="sidebar"
        className="w-64 sm:w-72 h-screen pt-4 lg:pt-8 bg-white shadow-md fixed left-0 top-0 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40"
      >
        <div className="px-4 lg:px-6 flex justify-between items-center">
          <a href="#" className="flex items-center gap-x-2.5">
            <span>
              <img src="/logo.png" alt="logo" className="img-fluid" />
            </span>
            <span className="font-semibold text-xl leading-6 text-col-5">Sendana</span>
          </a>
          <button id="sidebarClose" className="lg:hidden cursor-pointer text-col-6 hover:text-gray-700">
            <i className="fas fa-times text-xl"></i>
          </button>
        </div>

        <nav className="mt-8 px-4 overflow-y-auto h-[calc(100vh-100px)]">
          <div className="pb-[18px]">
            <ul className="grid gap-y-2">
              <li>
                <button onClick={handleNavigateToDashboard} className="sidebar-nav-link group w-full text-left">
                  <span><i className="fa-solid fa-house"></i></span>
                  <span>Home</span>
                </button>
              </li>
              <li>
                <a href="#" className="sidebar-nav-link group">
                  <span>
                    <i className="fa-solid fa-clock-rotate-left transition-all duration-300 text-col-6 group-hover:text-col-4"></i>
                  </span>
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
                  <span>
                    <i className="fa-solid fa-credit-card transition-all duration-300 text-col-6 group-hover:text-col-4"></i>
                  </span>
                  <span> Virtual USD Account</span>
                </a>
              </li>
              <li>
                <a href="#" className="sidebar-nav-link bg-col-3 text-col-4">
                  <span>
                    <i className="fa-solid fa-wallet"></i>
                  </span>
                  <span> Wallet Address</span>
                </a>
              </li>
            </ul>
          </div>

          <div className="border-t border-col-2 pt-[18px] py-3">
            <ul className="grid gap-y-2">
              <li>
                <a href="#" className="sidebar-nav-link group">
                  <span>
                    <i className="fa-solid fa-gift transition-all duration-300 text-col-6 group-hover:text-col-4"></i>
                  </span>
                  <span> Refer & Earn</span>
                </a>
              </li>
              <li>
                <a href="#" className="sidebar-nav-link group">
                  <span>
                    <i className="fa-solid fa-comment transition-all duration-300 text-col-6 group-hover:text-col-4"></i>
                  </span>
                  <span> Share Feedback</span>
                </a>
              </li>
              <li>
                <a href="#" className="sidebar-nav-link group">
                  <span>
                    <i className="fa-regular fa-circle-question transition-all duration-300 text-col-6 group-hover:text-col-4"></i>
                  </span>
                  <span> Help & Support</span>
                </a>
              </li>
            </ul>
          </div>
        </nav>
      </div>

      <div className="flex-1 min-h-screen p-4 lg:p-8 transition-all duration-300 lg:ml-72">
        <div className="grid grid-cols-12 max-lg:items-center mb-6 lg:mb-8">
          <div className="lg:col-span-7 col-span-12 order-3 lg:order-1 max-lg:mt-6">
            <h1 className="font-semibold text-lg lg:text-3xl leading-[135%] lg:leading-[38px] mb-1 text-col-7">
              Your Wallet Address
            </h1>
            <p className="font-normal text-xs leading-[150%] lg:text-base text-col-8">
              Share this address to receive payments
            </p>
          </div>
          <div className="col-span-5 order-2">
            <ul className="flex items-center gap-x-4 lg:gap-x-5 justify-end">
              <li className="flex">
                <button className="cursor-pointer relative" id="accountDropdownButton">
                  <img src="/avatar.png" alt="avatar" className="lg:w-8 w-[26px] lg:h-8 h-[26px] rounded-full" />
                  <div id="accountDropdown" className="hidden absolute right-0 mt-2 w-60 text-left origin-top-right bg-white rounded-lg border border-col-9 z-30 drop-shadow-1">
                    <div>
                      <h4 className="px-4 py-3 border-b border-col-9 font-medium text-sm text-col-6 leading-5">
                        Account Menu
                      </h4>
                      <a href="#" className="flex gap-x-3 px-4 py-2.5 font-inter font-normal text-sm leading-5 text-col-5">
                        <span><i className="fa-regular fa-user text-col-6"></i></span>
                        <span> View Profile</span>
                      </a>
                      <button onClick={handleLogout} className="flex gap-x-3 px-4 py-2.5 font-inter font-normal text-sm leading-5 text-col-5 w-full text-left">
                        <span><i className="fa-solid fa-arrow-right-from-bracket text-col-6"></i></span>
                        <span> Log out</span>
                      </button>
                    </div>
                  </div>
                </button>
              </li>
            </ul>
          </div>
          <div className="max-lg:col-span-7 lg:hidden order-1 lg:order-3 flex items-center gap-x-3">
            <button id="sidebarToggle" className="cursor-pointer">
              <i className="fas fa-bars text-col-6"></i>
            </button>
            <h3 className="font-semibold text-xl -tracking-[0.5px] text-col-5">Sendana</h3>
          </div>
        </div>

        <div className="max-w-2xl mx-auto">
          <div className="bg-white rounded-2xl p-6 lg:p-8 shadow-md">
            <div className="flex flex-col items-center">
              <div className="mb-6 p-4 bg-gray-50 rounded-xl">
                <canvas ref={qrRef}></canvas>
              </div>

              <div className="w-full mb-6">
                <h3 className="font-medium text-base text-col-5 mb-3">Your Stellar Wallet Address</h3>
                <div className="bg-col-3 rounded-xl p-4">
                  <div className="flex items-center justify-between gap-4">
                    <p className="font-mono text-sm text-col-5 break-all flex-1">
                      {wallet?.publicKey || 'Loading...'}
                    </p>
                    <button
                      onClick={copyToClipboard}
                      className="h-10 w-10 cursor-pointer flex items-center justify-center hover:bg-gray-200 rounded-lg transition-colors"
                    >
                      {copied ? (
                        <i className="fa-solid fa-check text-green-600"></i>
                      ) : (
                        <i className="fa-regular fa-clone fa-flip-horizontal text-col-6"></i>
                      )}
                    </button>
                  </div>
                </div>
              </div>

              <div className="w-full bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p className="text-sm text-blue-800">
                  <i className="fa-solid fa-circle-info mr-2"></i>
                  This is your Stellar wallet address. Share it with others to receive USDC and other Stellar assets.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default WalletAddress;
