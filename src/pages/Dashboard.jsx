import { useEffect, useState } from 'react';
import { usePrivy } from '@privy-io/react-auth';
import { useNavigate } from 'react-router-dom';
import { getStoredWallet, getAccountBalance } from '../services/stellar';
import './Dashboard.css';

function Dashboard() {
  const { user, logout } = usePrivy();
  const navigate = useNavigate();
  const [balance, setBalance] = useState('0.00');
  const [wallet, setWallet] = useState(null);

  useEffect(() => {
    const initializeDashboard = async () => {
      if (user) {
        const storedWallet = getStoredWallet(user.id);
        setWallet(storedWallet);

        if (storedWallet) {
          const balances = await getAccountBalance(storedWallet.publicKey);
          const usdcBalance = balances.find(b => b.asset_code === 'USDC');
          if (usdcBalance) {
            setBalance(parseFloat(usdcBalance.balance).toFixed(2));
          }
        }
      }
    };

    initializeDashboard();
  }, [user]);

  useEffect(() => {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const body = document.body;

    const openSidebar = () => {
      sidebar?.classList.remove('-translate-x-full');
      sidebar?.classList.add('translate-x-0');
      sidebarOverlay?.classList.add('active');
      body.classList.add('sidebar-open');
    };

    const closeSidebar = () => {
      sidebar?.classList.add('-translate-x-full');
      sidebar?.classList.remove('translate-x-0');
      sidebarOverlay?.classList.remove('active');
      body.classList.remove('sidebar-open');
    };

    sidebarToggle?.addEventListener('click', (e) => {
      e.stopPropagation();
      if (sidebar?.classList.contains('-translate-x-full')) {
        openSidebar();
      } else {
        closeSidebar();
      }
    });

    sidebarClose?.addEventListener('click', (e) => {
      e.stopPropagation();
      closeSidebar();
    });

    sidebarOverlay?.addEventListener('click', (e) => {
      e.stopPropagation();
      closeSidebar();
    });

    return () => {
      sidebarToggle?.removeEventListener('click', openSidebar);
      sidebarClose?.removeEventListener('click', closeSidebar);
      sidebarOverlay?.removeEventListener('click', closeSidebar);
    };
  }, []);

  const handleLogout = async () => {
    await logout();
    navigate('/');
  };

  const handleNavigateToWallet = () => {
    navigate('/wallet');
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
                <a href="#" className="sidebar-nav-link bg-col-3 text-col-4">
                  <span><i className="fa-solid fa-house"></i></span>
                  <span>Home</span>
                </a>
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
                <button onClick={handleNavigateToWallet} className="sidebar-nav-link group w-full text-left">
                  <span>
                    <i className="fa-solid fa-wallet transition-all duration-300 text-col-6 group-hover:text-col-4"></i>
                  </span>
                  <span> Wallet Address</span>
                </button>
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
              Good morning, {user?.google?.name?.split(' ')[0] || 'there'}!
            </h1>
            <p className="font-normal text-xs leading-[150%] lg:text-base text-col-8">
              Send. Receive. Smile. Repeat.
            </p>
          </div>
          <div className="col-span-5 order-2">
            <ul className="flex items-center gap-x-4 lg:gap-x-5 justify-end">
              <li>
                <span>
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11.9998 2C12.8648 2 13.694 2.16862 14.4549 2.47461C13.9709 2.96132 13.5876 3.54742 13.3357 4.19922C12.912 4.07026 12.4633 4 11.9998 4C9.66296 4.00014 7.69927 5.7575 7.44121 8.08008L7.18926 10.3457C7.18612 10.3739 7.18431 10.3907 7.18242 10.4072C7.03735 11.6714 6.62612 12.8909 5.97539 13.9844C5.96701 13.9985 5.95855 14.0131 5.94414 14.0371L5.36601 15C5.09255 15.4558 4.9274 15.7334 4.82988 15.9404C4.8251 15.9506 4.82129 15.9606 4.81719 15.9697C4.82753 15.9709 4.83862 15.9744 4.85039 15.9756C5.07809 15.9985 5.40102 16 5.93242 16H18.0672C18.5985 16 18.9215 15.9985 19.1492 15.9756C19.1606 15.9744 19.1714 15.9709 19.1814 15.9697C19.1774 15.9607 19.1745 15.9505 19.1697 15.9404C19.0722 15.7334 18.907 15.4558 18.6336 15L18.0555 14.0371C18.0411 14.0131 18.0326 13.9985 18.0242 13.9844C17.4538 13.0259 17.068 11.9704 16.8836 10.873C17.2426 10.955 17.616 11 17.9998 11C18.3165 11 18.6262 10.9684 18.9266 10.9121C19.0881 11.6323 19.3639 12.3239 19.743 12.9609C19.7492 12.9714 19.7551 12.9825 19.7703 13.0078L20.3484 13.9707C20.5995 14.3891 20.8287 14.7682 20.9793 15.0879C21.1274 15.4024 21.285 15.8285 21.2088 16.3115C21.1334 16.7889 20.888 17.2229 20.5174 17.5332C20.1423 17.847 19.6953 17.931 19.3494 17.9658C18.9978 18.0012 18.555 18 18.0672 18H5.93242C5.44461 18 5.00179 18.0012 4.65019 17.9658C4.30434 17.931 3.8572 17.8471 3.48223 17.5332C3.11173 17.2229 2.86614 16.7889 2.79082 16.3115C2.71467 15.8286 2.87224 15.4023 3.02031 15.0879C3.17089 14.7682 3.40015 14.3891 3.65117 13.9707L4.2293 13.0078C4.2445 12.9825 4.25045 12.9713 4.25664 12.9609C4.7627 12.1106 5.08322 11.1628 5.19609 10.1797C5.19748 10.1676 5.19867 10.1545 5.20195 10.125L5.45391 7.85938C5.82451 4.52393 8.64387 2.00014 11.9998 2ZM16.9822 4.2793C17.8281 5.25677 18.3935 6.48945 18.5457 7.85937L18.5525 7.91992C18.3768 7.97038 18.1917 8 17.9998 8C17.313 7.99991 16.7075 7.65341 16.3475 7.12598C16.2661 6.88341 16.1651 6.6506 16.0467 6.42871C16.0165 6.29057 15.9998 6.1472 15.9998 6C15.9998 5.26734 16.3946 4.62765 16.9822 4.2793Z" fill="#6B7280" />
                    <path d="M9.10222 17.6647C9.27315 18.6215 9.64978 19.467 10.1737 20.0701C10.6976 20.6731 11.3396 21 12 21C12.6604 21 13.3024 20.6731 13.8263 20.0701C14.3502 19.467 14.7269 18.6215 14.8978 17.6647" stroke="#6B7280" strokeWidth="2" strokeLinecap="round" />
                    <circle cx="18" cy="6" r="2.5" fill="#E25C5C" stroke="#E25C5C" />
                  </svg>
                </span>
              </li>
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

        <div className="bg-col-4 p-6 rounded-2xl mb-6 lg:mb-8">
          <div className="flex justify-between gap-x-2 lg:gap-x-4 pb-6 lg:pb-8 border-b border-col-10">
            <div>
              <h3 className="font-medium text-base lg:text-lg leading-6 text-col-11 mb-2.5">Wallet Balance</h3>
              <h2 className="lg:mb-2.5 mb-1 font-bold text-4xl lg:text-[42.75px] leading-10 lg:leading-12 text-white">
                ${balance.split('.')[0]}<span className="lg:text-[27.3px] text-2xl leading-8 ml-2 lg:leading-9">.{balance.split('.')[1]}</span>
              </h2>
              <p className="font-normal text-base text-col-12">USDC Balance</p>
            </div>
            <div>
              <span className="inline-flex items-center justify-center min-w-14 min-h-14 rounded-2xl bg-col-10">
                <i className="fa-solid fa-wallet text-white text-xl"></i>
              </span>
            </div>
          </div>
        </div>

        <div className="mb-6 lg:mb-8 bg-white rounded-2xl flex justify-between items-center px-6 py-5 lg:px-10 lg:py-8">
          <button className="flex max-lg:flex-col cursor-pointer items-center gap-3 lg:h-8">
            <span className="flex items-center justify-center">
              <i className="fa-solid fa-circle-plus text-col-4 text-xl lg:text-[26px]"></i>
            </span>
            <h3 className="font-normal text-sm leading-[150%] lg:text-base text-col-5">Add</h3>
          </button>
          <button className="flex cursor-pointer max-lg:flex-col items-center gap-3 lg:h-8">
            <span className="flex items-center justify-center">
              <i className="fa-regular fa-paper-plane text-col-4 text-xl lg:text-[26px]"></i>
            </span>
            <h3 className="font-normal text-sm leading-[150%] lg:text-base text-col-5">Send</h3>
          </button>
          <button className="flex cursor-pointer max-lg:flex-col items-center gap-3 lg:h-8">
            <span className="flex items-center justify-center">
              <i className="fa-solid fa-money-bill-transfer text-col-4 text-xl lg:text-[26px]"></i>
            </span>
            <h3 className="font-normal text-sm leading-[150%] lg:text-base text-col-5">Withdraw</h3>
          </button>
          <button className="flex cursor-pointer max-lg:flex-col items-center gap-3 lg:h-8">
            <span className="flex items-center justify-center">
              <i className="fa-solid fa-credit-card text-col-4 text-xl lg:text-[26px]"></i>
            </span>
            <h3 className="font-normal text-sm leading-[150%] lg:text-base text-col-5">Get Paid</h3>
          </button>
        </div>
      </div>
    </div>
  );
}

export default Dashboard;
