import { useState, useEffect } from 'react';
import { usePrivy, useWallets } from '@privy-io/react-auth';
import QRCode from 'qrcode';
import Sidebar from '../components/Sidebar';
import '../styles/dashboard.css';

export default function WalletPage() {
  const { user } = usePrivy();
  const { wallets } = useWallets();
  const [qrCodeUrl, setQrCodeUrl] = useState('');
  const [copied, setCopied] = useState(false);

  const stellarWallet = wallets && wallets.length > 0 ? wallets[0] : null;

  useEffect(() => {
    if (stellarWallet?.address) {
      QRCode.toDataURL(stellarWallet.address, {
        width: 300,
        margin: 2,
        color: {
          dark: '#5f2dc4',
          light: '#ffffff',
        },
      }).then((url) => setQrCodeUrl(url));
    }
  }, [stellarWallet]);

  const copyToClipboard = () => {
    if (stellarWallet?.address) {
      navigator.clipboard.writeText(stellarWallet.address);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    }
  };

  return (
    <div className="flex">
      <Sidebar />

      <div className="flex-1 min-h-screen p-4 lg:p-8 transition-all duration-300 lg:ml-72">
        <div className="mb-6 lg:mb-8">
          <h1 className="font-semibold text-2xl lg:text-3xl leading-[135%] text-col-7 mb-2">
            Wallet Address
          </h1>
          <p className="font-normal text-sm lg:text-base text-col-8">
            Your Stellar wallet address for receiving payments
          </p>
        </div>

        {stellarWallet ? (
          <div className="max-w-2xl">
            <div className="bg-white rounded-2xl p-6 lg:p-8 shadow-sm border border-col-10">
              <div className="flex flex-col items-center">
                {qrCodeUrl && (
                  <div className="mb-6">
                    <img src={qrCodeUrl} alt="Wallet QR Code" className="rounded-lg" />
                  </div>
                )}

                <div className="w-full mb-6">
                  <label className="block text-sm font-medium text-col-8 mb-2">
                    Your Wallet Address
                  </label>
                  <div className="flex gap-2">
                    <input
                      type="text"
                      readOnly
                      value={stellarWallet.address}
                      className="flex-1 px-4 py-3 border border-col-9 rounded-lg font-mono text-sm bg-col-13"
                    />
                    <button
                      onClick={copyToClipboard}
                      className="px-6 py-3 bg-col-4 text-white rounded-lg hover:bg-opacity-90 transition-all flex items-center gap-2"
                    >
                      <i className={`fas ${copied ? 'fa-check' : 'fa-copy'}`}></i>
                      {copied ? 'Copied!' : 'Copy'}
                    </button>
                  </div>
                </div>

                <div className="w-full bg-col-3 rounded-lg p-4">
                  <div className="flex items-start gap-3">
                    <i className="fas fa-info-circle text-col-4 mt-0.5"></i>
                    <div className="text-sm text-col-6">
                      <p className="font-medium mb-1">How to receive funds</p>
                      <p className="text-col-8">
                        Share this wallet address or QR code with anyone who wants to send you USDC or other Stellar-based assets.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div className="mt-6 bg-white rounded-2xl p-6 lg:p-8 shadow-sm border border-col-10">
              <h3 className="font-semibold text-lg text-col-7 mb-4">Wallet Information</h3>
              <div className="space-y-4">
                <div>
                  <p className="text-sm text-col-8">Blockchain</p>
                  <p className="text-base text-col-5 font-medium">Stellar</p>
                </div>
                <div>
                  <p className="text-sm text-col-8">Wallet Type</p>
                  <p className="text-base text-col-5 font-medium">Non-custodial</p>
                </div>
                <div>
                  <p className="text-sm text-col-8">Supported Assets</p>
                  <p className="text-base text-col-5 font-medium">USDC, XLM, and other Stellar assets</p>
                </div>
                <div>
                  <p className="text-sm text-col-8">Account Email</p>
                  <p className="text-base text-col-5 font-medium">{user?.email?.address || 'N/A'}</p>
                </div>
              </div>
            </div>
          </div>
        ) : (
          <div className="bg-white rounded-2xl p-8 text-center">
            <i className="fas fa-wallet text-5xl text-col-8 mb-4"></i>
            <h3 className="font-semibold text-xl text-col-7 mb-2">No Wallet Found</h3>
            <p className="text-col-8">
              Your Stellar wallet is being created. Please refresh the page.
            </p>
          </div>
        )}
      </div>
    </div>
  );
}
