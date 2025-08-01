/**
 * MCQHome Theme Main JavaScript
 *
 * @package MCQHome
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  // Initialize theme functionality when DOM is ready
  $(document).ready(function () {
    MCQHome.init();
  });

  // Main theme object
  window.MCQHome = {
    /**
     * Initialize all theme functionality
     */
    init: function () {
      this.mobileMenu();
      this.smoothScroll();
      this.formValidation();
      this.tooltips();
      this.loadingStates();
      this.accessibility();
    },

    /**
     * Mobile menu functionality
     */
    mobileMenu: function () {
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

        // Close mobile menu on escape key
        document.addEventListener("keydown", function (e) {
          if (e.key === "Escape" && !mobileMenu.classList.contains("hidden")) {
            mobileMenu.classList.add("hidden");
            mobileMenuToggle.setAttribute("aria-expanded", "false");
            mobileMenuToggle.focus();
          }
        });
      }
    },

    /**
     * Smooth scrolling for anchor links
     */
    smoothScroll: function () {
      const anchorLinks = document.querySelectorAll('a[href^="#"]');

      anchorLinks.forEach((link) => {
        link.addEventListener("click", function (e) {
          const href = this.getAttribute("href");
          const target = document.querySelector(href);

          if (target) {
            e.preventDefault();
            target.scrollIntoView({
              behavior: "smooth",
              block: "start",
            });
          }
        });
      });
    },

    /**
     * Form validation helpers
     */
    formValidation: function () {
      const forms = document.querySelectorAll("form[data-validate]");

      forms.forEach((form) => {
        form.addEventListener("submit", function (e) {
          if (!MCQHome.validateForm(this)) {
            e.preventDefault();
          }
        });

        // Real-time validation
        const inputs = form.querySelectorAll("input, textarea, select");
        inputs.forEach((input) => {
          input.addEventListener("blur", function () {
            MCQHome.validateField(this);
          });
        });
      });
    },

    /**
     * Validate individual form field
     */
    validateField: function (field) {
      const value = field.value.trim();
      const type = field.type;
      const required = field.hasAttribute("required");
      let isValid = true;
      let message = "";

      // Remove existing error states
      field.classList.remove("border-error-500");
      const existingError = field.parentNode.querySelector(".field-error");
      if (existingError) {
        existingError.remove();
      }

      // Required field validation
      if (required && !value) {
        isValid = false;
        message = "This field is required.";
      }

      // Email validation
      if (type === "email" && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
          isValid = false;
          message = "Please enter a valid email address.";
        }
      }

      // Password validation
      if (type === "password" && value && value.length < 8) {
        isValid = false;
        message = "Password must be at least 8 characters long.";
      }

      // Show error if validation failed
      if (!isValid) {
        field.classList.add("border-error-500");
        const errorElement = document.createElement("div");
        errorElement.className = "field-error text-error-600 text-sm mt-1";
        errorElement.textContent = message;
        field.parentNode.appendChild(errorElement);
      }

      return isValid;
    },

    /**
     * Validate entire form
     */
    validateForm: function (form) {
      const fields = form.querySelectorAll("input, textarea, select");
      let isValid = true;

      fields.forEach((field) => {
        if (!MCQHome.validateField(field)) {
          isValid = false;
        }
      });

      return isValid;
    },

    /**
     * Initialize tooltips
     */
    tooltips: function () {
      const tooltipTriggers = document.querySelectorAll("[data-tooltip]");

      tooltipTriggers.forEach((trigger) => {
        trigger.addEventListener("mouseenter", function () {
          MCQHome.showTooltip(this);
        });

        trigger.addEventListener("mouseleave", function () {
          MCQHome.hideTooltip();
        });
      });
    },

    /**
     * Show tooltip
     */
    showTooltip: function (element) {
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
    },

    /**
     * Hide tooltip
     */
    hideTooltip: function () {
      const tooltip = document.getElementById("mcqhome-tooltip");
      if (tooltip) {
        tooltip.remove();
      }
    },

    /**
     * Loading states for buttons and forms
     */
    loadingStates: function () {
      const loadingButtons = document.querySelectorAll("[data-loading]");

      loadingButtons.forEach((button) => {
        button.addEventListener("click", function () {
          if (this.form && !MCQHome.validateForm(this.form)) {
            return;
          }

          MCQHome.setLoadingState(this, true);

          // Auto-remove loading state after 10 seconds (fallback)
          setTimeout(() => {
            MCQHome.setLoadingState(this, false);
          }, 10000);
        });
      });
    },

    /**
     * Set loading state for button
     */
    setLoadingState: function (button, loading) {
      if (loading) {
        button.disabled = true;
        button.classList.add("opacity-75", "cursor-not-allowed");

        const originalText = button.textContent;
        button.setAttribute("data-original-text", originalText);

        const spinner =
          '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        button.innerHTML = spinner + "Loading...";
      } else {
        button.disabled = false;
        button.classList.remove("opacity-75", "cursor-not-allowed");

        const originalText = button.getAttribute("data-original-text");
        if (originalText) {
          button.textContent = originalText;
          button.removeAttribute("data-original-text");
        }
      }
    },

    /**
     * Accessibility enhancements
     */
    accessibility: function () {
      // Skip link functionality
      const skipLink = document.querySelector(".skip-link");
      if (skipLink) {
        skipLink.addEventListener("click", function (e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute("href"));
          if (target) {
            target.focus();
            target.scrollIntoView();
          }
        });
      }

      // Focus management for modals and dropdowns
      document.addEventListener("keydown", function (e) {
        // Escape key handling
        if (e.key === "Escape") {
          const activeModal = document.querySelector(".modal.active");
          if (activeModal) {
            MCQHome.closeModal(activeModal);
          }
        }
      });
    },

    /**
     * Utility function to show notifications
     */
    showNotification: function (message, type = "info", duration = 5000) {
      const notification = document.createElement("div");
      notification.className = `notification fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm alert-${type} animate-slide-down`;
      notification.innerHTML = `
                <div class="flex items-center justify-between">
                    <span>${message}</span>
                    <button class="ml-4 text-lg leading-none" onclick="this.parentElement.parentElement.remove()">&times;</button>
                </div>
            `;

      document.body.appendChild(notification);

      // Auto-remove notification
      setTimeout(() => {
        if (notification.parentNode) {
          notification.remove();
        }
      }, duration);
    },

    /**
     * AJAX helper function
     */
    ajax: function (url, data, callback, method = "POST") {
      const xhr = new XMLHttpRequest();
      xhr.open(method, url, true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            try {
              const response = JSON.parse(xhr.responseText);
              callback(response);
            } catch (e) {
              callback({ success: false, message: "Invalid response format" });
            }
          } else {
            callback({ success: false, message: "Request failed" });
          }
        }
      };

      // Convert data object to URL-encoded string
      const params = new URLSearchParams(data).toString();
      xhr.send(params);
    },
  };
})(jQuery);
