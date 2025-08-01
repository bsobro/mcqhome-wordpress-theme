/**
 * MCQHome Theme Main JavaScript Entry Point
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Import Alpine.js for reactive components
import Alpine from "alpinejs";

// Make Alpine available globally
window.Alpine = Alpine;

// Initialize Alpine
Alpine.start();

// Theme-specific JavaScript
document.addEventListener("DOMContentLoaded", function () {
  console.log("MCQHome Theme loaded successfully");

  // Initialize theme components
  initMobileMenu();
  initFormValidation();
  initTooltips();
  initLoadingStates();
});

/**
 * Mobile menu functionality
 */
function initMobileMenu() {
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle");
  const mobileMenu = document.querySelector(".mobile-menu");

  if (mobileMenuToggle && mobileMenu) {
    mobileMenuToggle.addEventListener("click", function () {
      mobileMenu.classList.toggle("hidden");

      // Update aria-expanded attribute
      const isExpanded = !mobileMenu.classList.contains("hidden");
      mobileMenuToggle.setAttribute("aria-expanded", isExpanded);
    });

    // Close mobile menu when clicking outside
    document.addEventListener("click", function (e) {
      if (
        !mobileMenuToggle.contains(e.target) &&
        !mobileMenu.contains(e.target)
      ) {
        mobileMenu.classList.add("hidden");
        mobileMenuToggle.setAttribute("aria-expanded", "false");
      }
    });
  }
}

/**
 * Form validation
 */
function initFormValidation() {
  const forms = document.querySelectorAll("form[data-validate]");

  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      if (!validateForm(this)) {
        e.preventDefault();
      }
    });
  });
}

/**
 * Validate form
 */
function validateForm(form) {
  const requiredFields = form.querySelectorAll("[required]");
  let isValid = true;

  requiredFields.forEach((field) => {
    if (!field.value.trim()) {
      field.classList.add("border-error-500");
      isValid = false;
    } else {
      field.classList.remove("border-error-500");
    }
  });

  return isValid;
}

/**
 * Initialize tooltips
 */
function initTooltips() {
  const tooltipTriggers = document.querySelectorAll("[data-tooltip]");

  tooltipTriggers.forEach((trigger) => {
    trigger.addEventListener("mouseenter", function () {
      showTooltip(this);
    });

    trigger.addEventListener("mouseleave", function () {
      hideTooltip();
    });
  });
}

/**
 * Show tooltip
 */
function showTooltip(element) {
  const text = element.getAttribute("data-tooltip");
  const tooltip = document.createElement("div");

  tooltip.className =
    "tooltip absolute z-50 px-2 py-1 text-sm text-white bg-gray-900 rounded shadow-lg";
  tooltip.textContent = text;
  tooltip.id = "mcqhome-tooltip";

  document.body.appendChild(tooltip);

  // Position tooltip
  const rect = element.getBoundingClientRect();
  tooltip.style.left =
    rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + "px";
  tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + "px";
}

/**
 * Hide tooltip
 */
function hideTooltip() {
  const tooltip = document.getElementById("mcqhome-tooltip");
  if (tooltip) {
    tooltip.remove();
  }
}

/**
 * Loading states for buttons
 */
function initLoadingStates() {
  const loadingButtons = document.querySelectorAll("[data-loading]");

  loadingButtons.forEach((button) => {
    button.addEventListener("click", function () {
      setLoadingState(this, true);

      // Auto-remove loading state after 10 seconds (fallback)
      setTimeout(() => {
        setLoadingState(this, false);
      }, 10000);
    });
  });
}

/**
 * Set loading state for button
 */
function setLoadingState(button, loading) {
  if (loading) {
    button.disabled = true;
    button.classList.add("opacity-75", "cursor-not-allowed");

    const originalText = button.textContent;
    button.setAttribute("data-original-text", originalText);
    button.textContent = "Loading...";
  } else {
    button.disabled = false;
    button.classList.remove("opacity-75", "cursor-not-allowed");

    const originalText = button.getAttribute("data-original-text");
    if (originalText) {
      button.textContent = originalText;
      button.removeAttribute("data-original-text");
    }
  }
}
