// Sidebar Toggle
const sidebarToggle = document.getElementById("sidebarToggle");
const sidebar = document.getElementById("sidebar");

sidebarToggle.addEventListener("click", function () {
  sidebar.classList.toggle("collapsed");
});

// Mobile sidebar toggle
if (window.innerWidth <= 768) {
  sidebarToggle.addEventListener("click", function () {
    sidebar.classList.toggle("show");
  });
}

// Auto-collapse sidebar on mobile
window.addEventListener("resize", function () {
  if (window.innerWidth <= 768) {
    sidebar.classList.remove("collapsed");
  }
});

// Function to set active menu based on current page
function setActiveMenu() {
  const currentPage = window.location.pathname.split("/").pop() || "index.php";

  // Remove active class from all menu links
  document
    .querySelectorAll(".menu-link")
    .forEach((l) => l.classList.remove("active"));

  // Define page mappings
  const pageMenuMap = {
    "index.php": "index.php",
    "admin.php": "admin.php",
    "pimpinan.php": "pimpinan.php",
    "pelanggan.php": "pelanggan.php",
    "barang.php": "barang.php",
    "services.php": "services.php",
    "transaksi-services.php": "transaksi-services.php",
    "transaksi-barang.php": "transaksi-barang.php",
    "histori-service.php": "histori-service.php",
    "histori-barang.php": "histori-barang.php",
    "laporan.php": "laporan.php",
  };

  // Set active for direct menu links
  const directMenuLink = document.querySelector(`a[href="${currentPage}"]`);
  if (directMenuLink && directMenuLink.classList.contains("menu-link")) {
    directMenuLink.classList.add("active");
    return;
  }

  // Set active for dropdown items and their parent
  const dropdownItem = document.querySelector(
    `.dropdown-item[href="${currentPage}"]`
  );
  if (dropdownItem) {
    const parentMenuLink = dropdownItem
      .closest(".menu-item")
      .querySelector(".menu-link");
    if (parentMenuLink) {
      parentMenuLink.classList.add("active");

      // Also expand the dropdown
      const collapseTarget = parentMenuLink.getAttribute("data-bs-target");
      if (collapseTarget) {
        const collapseElement = document.querySelector(collapseTarget);
        if (collapseElement) {
          collapseElement.classList.add("show");
        }
      }
    }
  }
}

// Menu item click handling for non-dropdown links
document.querySelectorAll(".menu-link").forEach((link) => {
  link.addEventListener("click", function (e) {
    // Only handle direct navigation links (not dropdown toggles)
    if (!this.hasAttribute("data-bs-toggle")) {
      const href = this.getAttribute("href");
      if (href && href !== "#") {
        // Let the browser handle navigation naturally
        // The active class will be set on page load
        return;
      }
    }
  });
});

// Dropdown menu items click handling
document.querySelectorAll(".dropdown-item").forEach((item) => {
  item.addEventListener("click", function (e) {
    const href = this.getAttribute("href");

    // If href exists and is not just '#', allow normal navigation
    if (href && href !== "#") {
      // Let the browser navigate normally
      // Don't prevent default - let the link work
      return;
    }

    // Only prevent default if no valid href
    e.preventDefault();
    console.log("No valid href found for:", this.textContent.trim());
  });
});

// Initialize tooltips for collapsed sidebar
function initTooltips() {
  if (sidebar && sidebar.classList.contains("collapsed")) {
    document.querySelectorAll(".menu-link").forEach((link) => {
      const textElement = link.querySelector(".menu-text");
      if (textElement) {
        const text = textElement.textContent;
        link.setAttribute("title", text);
        link.setAttribute("data-bs-placement", "right");
      }
    });
  } else {
    document.querySelectorAll(".menu-link").forEach((link) => {
      link.removeAttribute("title");
      link.removeAttribute("data-bs-placement");
    });
  }
}

// Listen for sidebar toggle to update tooltips
if (sidebarToggle) {
  sidebarToggle.addEventListener("click", function () {
    setTimeout(initTooltips, 300); // Wait for animation to complete
  });
}

// Initialize active menu on page load
document.addEventListener("DOMContentLoaded", function () {
  setActiveMenu();
  initTooltips();
});

// Handle browser back/forward navigation
window.addEventListener("popstate", function () {
  setActiveMenu();
});
