import { useState } from 'react';
import { Link, useLocation } from 'react-router-dom';

export default function Sidebar() {
  const [isOpen, setIsOpen] = useState(false);
  const location = useLocation();

  const toggleSidebar = () => setIsOpen(!isOpen);
  const closeSidebar = () => setIsOpen(false);

  const isActive = (path) => location.pathname === path;

  return (
    <>
      <div
        id="sidebarOverlay"
        className={`sidebar-overlay ${isOpen ? 'active' : ''}`}
        onClick={closeSidebar}
      ></div>

      <div
        id="sidebar"
        className={`w-64 sm:w-72 h-screen pt-4 lg:pt-8 bg-white shadow-md fixed left-0 top-0 transform ${isOpen ? 'translate-x-0' : '-translate-x-full'} lg:translate-x-0 transition-transform duration-300 ease-in-out z-40`}
      >
        <div className="px-4 lg:px-6 flex justify-between items-center">
          <Link to="/dashboard" className="flex items-center gap-x-2.5">
            <span>
              <img src="/src/assets/images/logo.png" alt="logo" className="img-fluid" />
            </span>
            <span className="font-semibold text-xl leading-6 text-col-5">Sendana</span>
          </Link>
          <button
            id="sidebarClose"
            onClick={closeSidebar}
            className="lg:hidden cursor-pointer text-col-6 hover:text-gray-700"
          >
            <i className="fas fa-times text-xl"></i>
          </button>
        </div>

        <nav className="mt-8 px-4 overflow-y-auto h-[calc(100vh-100px)]">
          <div className="pb-[18px]">
            <ul className="grid gap-y-2">
              <li>
                <Link
                  to="/dashboard"
                  className={`sidebar-nav-link ${isActive('/dashboard') ? 'bg-col-3 text-col-4' : 'group'}`}
                >
                  <span><i className="fa-solid fa-house"></i></span>
                  <span>Home</span>
                </Link>
              </li>
              <li>
                <Link to="#" className="sidebar-nav-link group">
                  <span>
                    <i className="fa-solid fa-clock-rotate-left transition-all duration-300 text-col-6 group-hover:text-col-4"></i>
                  </span>
                  <span>All Transactions</span>
                </Link>
              </li>
              <li>
                <Link to="#" className="sidebar-nav-link group">
                  <i className="fa-solid fa-link transition-all duration-300 text-col-6 group-hover:text-col-4"></i>
                  <span>Link Accounts</span>
                </Link>
              </li>
              <li>
                <Link to="#" className="sidebar-nav-link group">
                  <span>
                    <i className="fa-solid fa-credit-card transition-all duration-300 text-col-6 group-hover:text-col-4"></i>
                  </span>
                  <span>Virtual USD Account</span>
                </Link>
              </li>
              <li>
                <Link
                  to="/wallet"
                  className={`sidebar-nav-link ${isActive('/wallet') ? 'bg-col-3 text-col-4' : 'group'}`}
                >
                  <span>
                    <i className="fa-solid fa-wallet transition-all duration-300 text-col-6 group-hover:text-col-4"></i>
                  </span>
                  <span>Wallet Address</span>
                </Link>
              </li>
            </ul>
          </div>

          <div className="border-t border-col-2 pt-[18px] py-3">
            <ul className="grid gap-y-2">
              <li>
                <Link to="#" className="sidebar-nav-link group">
                  <span>
                    <i className="fa-solid fa-gift transition-all duration-300 text-col-6 group-hover:text-col-4"></i>
                  </span>
                  <span>Refer & Earn</span>
                </Link>
              </li>
              <li>
                <Link to="#" className="sidebar-nav-link group">
                  <span>
                    <i className="fa-solid fa-comment transition-all duration-300 text-col-6 group-hover:text-col-4"></i>
                  </span>
                  <span>Share Feedback</span>
                </Link>
              </li>
              <li>
                <Link to="#" className="sidebar-nav-link group">
                  <span>
                    <i className="fa-regular fa-circle-question transition-all duration-300 text-col-6 group-hover:text-col-4"></i>
                  </span>
                  <span>Help & Support</span>
                </Link>
              </li>
            </ul>
          </div>
        </nav>
      </div>

      <button
        id="sidebarToggle"
        onClick={toggleSidebar}
        className="lg:hidden fixed top-6 left-4 cursor-pointer z-50"
      >
        <i className="fas fa-bars text-col-6"></i>
      </button>
    </>
  );
}
