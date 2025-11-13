document.addEventListener("DOMContentLoaded", function () {
  // Sidebar Js
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

  // Close sidebar when clicking outside on mobile
  document.addEventListener("click", function (event) {
    if (
      window.innerWidth < 1024 &&
      !sidebar.contains(event.target) &&
      event.target !== sidebarToggle
    ) {
      closeSidebar();
    }
  });

  // Close sidebar when resizing to desktop
  window.addEventListener("resize", function () {
    if (window.innerWidth >= 1024) {
      sidebar.classList.remove("-translate-x-full", "translate-x-0");
      sidebarOverlay.classList.remove("active");
      body.classList.remove("sidebar-open");
    } else if (!sidebar.classList.contains("translate-x-0")) {
      sidebar.classList.add("-translate-x-full");
    }
  });

  // Dropdown Js

  const dropdownButton = document.getElementById("accountDropdownButton");
  const dropdownMenu = document.getElementById("accountDropdown");

  // Toggle dropdown visibility
  dropdownButton.addEventListener("click", function (e) {
    e.stopPropagation();
    dropdownMenu.classList.toggle("hidden");
  });

  // Close dropdown when clicking outside
  document.addEventListener("click", function (e) {
    if (
      !dropdownButton.contains(e.target) &&
      !dropdownMenu.contains(e.target)
    ) {
      dropdownMenu.classList.add("hidden");
    }
  });

  // Close dropdown when pressing Escape
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      dropdownMenu.classList.add("hidden");
    }
  });
});
