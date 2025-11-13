import React, { useState, useEffect } from 'react';
import { usePrivy } from '@privy-io/react-auth';
import { Link } from 'react-router-dom';
import { initSwiper } from '../utils/swiper';

function SignupPage() {
  const { login } = usePrivy();
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    // Initialize Swiper after component mounts
    const timer = setTimeout(() => {
      initSwiper();
    }, 100);
    return () => clearTimeout(timer);
  }, []);

  const handleSignup = async () => {
    setLoading(true);
    try {
      await login();
    } catch (error) {
      console.error('Signup error:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div id="signup-page">
      <section className="main-div">
        <div className="containerr max-lg:h-full grid lg:grid-cols-2 lg:items-center max-lg:py-5 max-lg:min-h-screen">
          <div className="max-lg:flex max-lg:flex-col max-lg:justify-between">
            <div className="logo flex">
              <Link to="/" className="inline-flex items-center gap-x-2.5">
                <img src="/images/logo.png" alt="logo" />
                <span className="text-white lg:text-col-4 font-semibold text-xl leading-6">
                  Sendana
                </span>
              </Link>
            </div>
            <div className="login-menu">
              <div>
                <h1 className="font-semibold text-2xl lg:text-4xl leading-full tracking-small text-col-6 mb-4 lg:mb-7">
                  Create Account
                </h1>
                <p className="font-normal text-sm lg:text-base leading-[160%] tracking-small text-col-7">
                  Join Sendana today and start experiencing borderless banking. Create your account in seconds.
                </p>
              </div>
              <div>
                <button
                  onClick={handleSignup}
                  disabled={loading}
                  className="form-btn w-full"
                  style={{ marginTop: '24px' }}
                >
                  {loading ? 'Creating account...' : 'Sign up'}
                </button>
              </div>
              <div>
                <div className="text-center middle-border relative">
                  <span className="font-normal text-sm leading-8.5 tracking-small text-col-12 mx-auto inline-block">
                    Or
                  </span>
                </div>
                <div className="grid max-lg:grid-cols-2 gap-4 mt-4 lg:mt-6">
                  <div>
                    <button onClick={handleSignup} className="extra-login-btn">
                      <span>
                        <svg
                          width="21"
                          height="20"
                          viewBox="0 0 21 20"
                          fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                        >
                          <g clipPath="url(#clip0_260_28785)">
                            <path
                              d="M20.305 10.2303C20.305 9.55056 20.2499 8.86711 20.1323 8.19836H10.7V12.0492H16.1015C15.8773 13.2911 15.1571 14.3898 14.1026 15.0879V17.5866H17.325C19.2174 15.8449 20.305 13.2728 20.305 10.2303Z"
                              fill="#4285F4"
                            />
                            <path
                              d="M10.7 20.0007C13.397 20.0007 15.6715 19.1152 17.3287 17.5866L14.1062 15.088C13.2096 15.6979 12.0522 16.0433 10.7037 16.0433C8.0948 16.0433 5.88279 14.2833 5.08911 11.9169H1.76373V14.4927C3.46133 17.8695 6.91898 20.0007 10.7 20.0007Z"
                              fill="#34A853"
                            />
                            <path
                              d="M5.08546 11.9169C4.66657 10.6749 4.66657 9.33008 5.08546 8.08811V5.51233H1.76376C0.345428 8.33798 0.345428 11.667 1.76376 14.4927L5.08546 11.9169Z"
                              fill="#FBBC04"
                            />
                            <path
                              d="M10.7 3.95805C12.1257 3.936 13.5036 4.47247 14.5361 5.45722L17.3911 2.60218C15.5833 0.904588 13.1839 -0.0287217 10.7 0.000673889C6.91898 0.000673889 3.46133 2.13185 1.76373 5.51234L5.08543 8.08813C5.87543 5.71811 8.09112 3.95805 10.7 3.95805Z"
                              fill="#EA4335"
                            />
                          </g>
                          <defs>
                            <clipPath id="clip0_260_28785">
                              <rect width="20" height="20" fill="white" transform="translate(0.5)" />
                            </clipPath>
                          </defs>
                        </svg>
                      </span>
                      <span className="max-lg:hidden lg:block">Sign up with Google</span>
                      <span className="max-lg:block lg:hidden">Google</span>
                    </button>
                  </div>
                </div>
              </div>
              <div>
                <p className="text-center font-roboto font-normal leading-[160%] tracking-small text-base text-col-7">
                  Already have an account?{' '}
                  <Link to="/" className="text-col-11">
                    Sign in
                  </Link>
                </p>
              </div>
            </div>
            <div>
              <p className="font-normal text-xs lg:text-sm leading-full tracking-small text-center text-col-5">
                © 2025 ALL RIGHTS RESERVED
              </p>
            </div>
          </div>
          <div className="max-lg:hidden">
            <div className="swiper loginart">
              <div className="swiper-wrapper">
                <div className="swiper-slide">
                  <div className="loginart-image">
                    <img src="/images/login-art-1.jpg" alt="login-art-1" />
                  </div>
                  <div className="loginart-text absolute bottom-16 xl:bottom-20 2xl:bottom-46 left-0 2xl:px-23 xl:px-13 z-2 px-8">
                    <h3>Borderless banking starts here.</h3>
                    <p>
                      Receive, send and manage multiple currencies in one app. Open a foreign bank
                      account for free.
                    </p>
                  </div>
                </div>
                <div className="swiper-slide">
                  <div className="loginart-image">
                    <img src="/images/login-art-2.jpg" alt="login-art-2" />
                  </div>
                  <div className="loginart-text absolute bottom-16 xl:bottom-20 2xl:bottom-46 left-0 2xl:px-23 xl:px-13 z-2 px-8">
                    <h3>From 'just down the road' to 'halfway across the globe'.</h3>
                    <p>
                      Send money to anyone, just about anywhere. Sendana-to-Sendana transfers are
                      free!
                    </p>
                  </div>
                </div>
              </div>
              <div className="swiper-button-next"></div>
              <div className="swiper-button-prev"></div>
              <div className="swiper-pagination !bottom-20 xl:!bottom-24 2xl:!bottom-46"></div>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}

export default SignupPage;
