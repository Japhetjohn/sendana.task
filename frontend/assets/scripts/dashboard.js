document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.getElementById("sidebar");
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebarClose = document.getElementById("sidebarClose");
  const sidebarOverlay = document.getElementById("sidebarOverlay");
  const body = document.body;

  function openSidebar() {
    sidebar.classList.remove("-translate-x-full");
    sidebar.classList.add("translate-x-0");
    sidebarOverlay.classList.add("active");
    body.classList.add("sidebar-open");
  }

  function closeSidebar() {
    sidebar.classList.add("-translate-x-full");
    sidebar.classList.remove("translate-x-0");
    sidebarOverlay.classList.remove("active");
    body.classList.remove("sidebar-open");
  }

  function toggleSidebar() {
    if (sidebar.classList.contains("-translate-x-full")) {
      openSidebar();
    } else {
      closeSidebar();
    }
  }

  sidebarToggle.addEventListener("click", function (e) {
    e.stopPropagation();
    toggleSidebar();
  });

  sidebarClose.addEventListener("click", function (e) {
    e.stopPropagation();
    closeSidebar();
  });

  sidebarOverlay.addEventListener("click", function (e) {
    e.stopPropagation();
    closeSidebar();
  });

  document.addEventListener("click", function (event) {
    if (
      window.innerWidth < 1024 &&
      !sidebar.contains(event.target) &&
      event.target !== sidebarToggle
    ) {
      closeSidebar();
    }
  });

  window.addEventListener("resize", function () {
    if (window.innerWidth >= 1024) {
      sidebar.classList.remove("-translate-x-full", "translate-x-0");
      sidebarOverlay.classList.remove("active");
      body.classList.remove("sidebar-open");
    } else if (!sidebar.classList.contains("translate-x-0")) {
      sidebar.classList.add("-translate-x-full");
    }
  });

  const dropdownButton = document.getElementById("accountDropdownButton");
  const dropdownMenu = document.getElementById("accountDropdown");

  dropdownButton.addEventListener("click", function (e) {
    e.stopPropagation();
    dropdownMenu.classList.toggle("hidden");
  });

  document.addEventListener("click", function (e) {
    if (
      !dropdownButton.contains(e.target) &&
      !dropdownMenu.contains(e.target)
    ) {
      dropdownMenu.classList.add("hidden");
    }
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      dropdownMenu.classList.add("hidden");
    }
  });

  // Wallet Address Modal Functionality
  const walletAddressBtn = document.getElementById("walletAddressBtn");
  const walletModal = document.getElementById("walletModal");
  const closeWalletModal = document.getElementById("closeWalletModal");
  const copyWalletBtn = document.getElementById("copyWalletBtn");
  const walletAddressText = document.getElementById("walletAddressText");
  const walletEmail = document.getElementById("walletEmail");
  const copySuccessMessage = document.getElementById("copySuccessMessage");

  // Get auth token from localStorage
  function getAuthToken() {
    return localStorage.getItem("authToken");
  }

  // Fetch wallet data from API
  async function fetchWalletData() {
    const token = getAuthToken();
    if (!token) {
      walletAddressText.textContent = "Error: Please log in";
      return;
    }

    try {
      const response = await fetch("http://localhost:8000/api/wallet", {
        method: "GET",
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
        },
      });

      if (!response.ok) {
        throw new Error("Failed to fetch wallet data");
      }

      const data = await response.json();
      if (data.success && data.wallet) {
        walletAddressText.textContent = data.wallet.address || "No address found";
        walletEmail.textContent = data.wallet.email || "-";
      } else {
        walletAddressText.textContent = "Error loading wallet";
      }
    } catch (error) {
      console.error("Error fetching wallet:", error);
      walletAddressText.textContent = "Error loading wallet";
    }
  }

  // Open wallet modal
  walletAddressBtn.addEventListener("click", function (e) {
    e.preventDefault();
    walletModal.classList.remove("hidden");
    fetchWalletData();
  });

  // Close wallet modal
  closeWalletModal.addEventListener("click", function () {
    walletModal.classList.add("hidden");
    copySuccessMessage.classList.add("hidden");
  });

  // Close modal when clicking outside
  walletModal.addEventListener("click", function (e) {
    if (e.target === walletModal) {
      walletModal.classList.add("hidden");
      copySuccessMessage.classList.add("hidden");
    }
  });

  // Copy wallet address to clipboard
  copyWalletBtn.addEventListener("click", async function () {
    const address = walletAddressText.textContent;
    if (address && address !== "Loading..." && address !== "Error loading wallet") {
      try {
        await navigator.clipboard.writeText(address);
        copySuccessMessage.classList.remove("hidden");
        setTimeout(() => {
          copySuccessMessage.classList.add("hidden");
        }, 3000);
      } catch (err) {
        console.error("Failed to copy:", err);
        alert("Failed to copy wallet address");
      }
    }
  });

  // Close modal on Escape key
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && !walletModal.classList.contains("hidden")) {
      walletModal.classList.add("hidden");
      copySuccessMessage.classList.add("hidden");
    }
  });
});
