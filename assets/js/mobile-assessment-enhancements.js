/**
 * Mobile Assessment Enhancements
 * MCQHome Theme - Task 21 Implementation
 *
 * Enhanced mobile responsiveness and touch-friendly features
 */

(function ($) {
  "use strict";

  /**
   * Mobile Assessment Enhancement Class
   */
  class MobileAssessmentEnhancements {
    constructor() {
      this.isMobile = this.detectMobile();
      this.isTablet = this.detectTablet();
      this.touchStartTime = 0;
      this.lastTouchEnd = 0;

      if (this.isMobile || this.isTablet) {
        this.init();
      }
    }

    /**
     * Initialize mobile enhancements
     */
    init() {
      this.initializeViewportHandling();
      this.initializeEnhancedTouchTargets();
      this.initializeMobileNavigation();
      this.initializeAccessibilityFeatures();
      this.initializePerformanceOptimizations();
      this.initializeOrientationHandling();
      this.initializeMobileNotifications();
      this.initializeGestureEnhancements();

      console.log("Mobile assessment enhancements initialized");
    }

    /**
     * Detect mobile device
     */
    detectMobile() {
      return (
        window.innerWidth <= 768 ||
        /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
          navigator.userAgent
        )
      );
    }

    /**
     * Detect tablet device
     */
    detectTablet() {
      return (
        window.innerWidth > 768 &&
        window.innerWidth <= 1024 &&
        /iPad|Android|Tablet/i.test(navigator.userAgent)
      );
    }

    /**
     * Initialize enhanced viewport handling
     */
    initializeViewportHandling() {
      // Handle viewport changes for mobile browsers
      let initialViewportHeight = window.innerHeight;
      let resizeTimeout;

      const handleViewportChange = () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
          const currentHeight = window.visualViewport
            ? window.visualViewport.height
            : window.innerHeight;

          const heightDifference = initialViewportHeight - currentHeight;

          // Keyboard detection
          if (heightDifference > 150) {
            document.body.classList.add("mobile-keyboard-open");
            this.handleKeyboardOpen();
          } else {
            document.body.classList.remove("mobile-keyboard-open");
            this.handleKeyboardClose();
          }
        }, 100);
      };

      // Use Visual Viewport API if available
      if (window.visualViewport) {
        window.visualViewport.addEventListener("resize", handleViewportChange);
      } else {
        window.addEventListener("resize", handleViewportChange);
      }

      // Handle orientation changes
      window.addEventListener("orientationchange", () => {
        setTimeout(() => {
          initialViewportHeight = window.innerHeight;
          this.handleOrientationChange();
        }, 500);
      });
    }

    /**
     * Handle mobile keyboard open
     */
    handleKeyboardOpen() {
      // Adjust layout for keyboard
      const assessmentContainer = document.querySelector(
        ".mcq-assessment-container"
      );
      if (assessmentContainer) {
        assessmentContainer.style.paddingBottom = "2rem";
      }

      // Make navigation sticky when keyboard is open
      const questionNav = document.querySelector(".question-navigation");
      if (questionNav) {
        questionNav.style.position = "fixed";
        questionNav.style.bottom = "0";
        questionNav.style.left = "0";
        questionNav.style.right = "0";
        questionNav.style.zIndex = "1000";
        questionNav.style.background = "white";
        questionNav.style.boxShadow = "0 -2px 10px rgba(0, 0, 0, 0.1)";
      }

      // Scroll focused element into view
      const focusedElement = document.activeElement;
      if (
        focusedElement &&
        (focusedElement.tagName === "INPUT" ||
          focusedElement.tagName === "TEXTAREA")
      ) {
        setTimeout(() => {
          focusedElement.scrollIntoView({
            behavior: "smooth",
            block: "center",
          });
        }, 300);
      }
    }

    /**
     * Handle mobile keyboard close
     */
    handleKeyboardClose() {
      // Reset layout
      const assessmentContainer = document.querySelector(
        ".mcq-assessment-container"
      );
      if (assessmentContainer) {
        assessmentContainer.style.paddingBottom = "";
      }

      // Reset navigation position
      const questionNav = document.querySelector(".question-navigation");
      if (questionNav) {
        questionNav.style.position = "";
        questionNav.style.bottom = "";
        questionNav.style.left = "";
        questionNav.style.right = "";
        questionNav.style.zIndex = "";
        questionNav.style.background = "";
        questionNav.style.boxShadow = "";
      }
    }

    /**
     * Initialize enhanced touch targets
     */
    initializeEnhancedTouchTargets() {
      // Enhance all interactive elements
      const interactiveElements = document.querySelectorAll(
        "button, .option-label, .question-number, input[type='radio']"
      );

      interactiveElements.forEach((element) => {
        // Ensure minimum touch target size (44px)
        const rect = element.getBoundingClientRect();
        if (rect.width < 44 || rect.height < 44) {
          element.style.minWidth = "44px";
          element.style.minHeight = "44px";
          element.style.display = "flex";
          element.style.alignItems = "center";
          element.style.justifyContent = "center";
        }

        // Add touch-action for better performance
        element.style.touchAction = "manipulation";

        // Remove tap highlight
        element.style.webkitTapHighlightColor = "transparent";

        // Add enhanced touch feedback
        this.addTouchFeedback(element);
      });
    }

    /**
     * Add touch feedback to element
     */
    addTouchFeedback(element) {
      let touchTimeout;

      element.addEventListener(
        "touchstart",
        (e) => {
          this.touchStartTime = Date.now();

          // Add pressed state
          element.classList.add("touch-pressed");

          // Create ripple effect
          this.createRippleEffect(e, element);

          // Clear any existing timeout
          clearTimeout(touchTimeout);
        },
        { passive: true }
      );

      element.addEventListener(
        "touchend",
        (e) => {
          const touchDuration = Date.now() - this.touchStartTime;

          // Remove pressed state after minimum duration
          touchTimeout = setTimeout(() => {
            element.classList.remove("touch-pressed");
          }, Math.max(100, 150 - touchDuration));

          // Prevent double-tap zoom
          const now = Date.now();
          if (now - this.lastTouchEnd <= 300) {
            e.preventDefault();
          }
          this.lastTouchEnd = now;
        },
        { passive: false }
      );

      element.addEventListener("touchcancel", () => {
        element.classList.remove("touch-pressed");
        clearTimeout(touchTimeout);
      });
    }

    /**
     * Create ripple effect for touch feedback
     */
    createRippleEffect(event, element) {
      const rect = element.getBoundingClientRect();
      const ripple = document.createElement("div");

      ripple.className = "touch-ripple";
      ripple.style.position = "absolute";
      ripple.style.borderRadius = "50%";
      ripple.style.background = "rgba(59, 130, 246, 0.3)";
      ripple.style.transform = "scale(0)";
      ripple.style.animation = "ripple 0.6s linear";
      ripple.style.pointerEvents = "none";
      ripple.style.zIndex = "1";

      const size = Math.max(rect.width, rect.height);
      const x = event.touches[0].clientX - rect.left - size / 2;
      const y = event.touches[0].clientY - rect.top - size / 2;

      ripple.style.width = ripple.style.height = size + "px";
      ripple.style.left = x + "px";
      ripple.style.top = y + "px";

      // Ensure element has relative positioning
      if (getComputedStyle(element).position === "static") {
        element.style.position = "relative";
      }

      element.appendChild(ripple);

      setTimeout(() => {
        if (ripple.parentNode) {
          ripple.parentNode.removeChild(ripple);
        }
      }, 600);
    }

    /**
     * Initialize mobile navigation enhancements
     */
    initializeMobileNavigation() {
      // Enhanced question navigation for mobile
      const questionNumbers = document.querySelectorAll(".question-number");

      questionNumbers.forEach((btn, index) => {
        // Add long press for additional options
        let longPressTimer;

        btn.addEventListener("touchstart", () => {
          longPressTimer = setTimeout(() => {
            this.showQuestionOptions(index, btn);
          }, 800);
        });

        btn.addEventListener("touchend", () => {
          clearTimeout(longPressTimer);
        });

        btn.addEventListener("touchmove", () => {
          clearTimeout(longPressTimer);
        });
      });

      // Enhanced navigation panel scrolling
      const navPanel = document.querySelector(".question-navigation-panel");
      if (navPanel) {
        // Smooth scrolling for mobile
        navPanel.style.scrollBehavior = "smooth";
        navPanel.style.webkitOverflowScrolling = "touch";

        // Add momentum scrolling
        let isScrolling = false;
        let scrollTimeout;

        navPanel.addEventListener("scroll", () => {
          isScrolling = true;
          clearTimeout(scrollTimeout);

          scrollTimeout = setTimeout(() => {
            isScrolling = false;
          }, 150);
        });
      }
    }

    /**
     * Show question options on long press
     */
    showQuestionOptions(questionIndex, buttonElement) {
      // Haptic feedback
      if (navigator.vibrate) {
        navigator.vibrate(100);
      }

      // Create options menu
      const menu = document.createElement("div");
      menu.className = "question-options-menu";
      menu.innerHTML = `
        <div class="question-options-content">
          <h4>Question ${questionIndex + 1}</h4>
          <button class="option-btn" data-action="navigate">Go to Question</button>
          <button class="option-btn" data-action="mark">Mark for Review</button>
          <button class="option-btn" data-action="clear">Clear Answer</button>
          <button class="option-btn cancel" data-action="cancel">Cancel</button>
        </div>
      `;

      // Position menu
      const rect = buttonElement.getBoundingClientRect();
      menu.style.position = "fixed";
      menu.style.top = rect.bottom + 10 + "px";
      menu.style.left = Math.max(10, rect.left - 50) + "px";
      menu.style.zIndex = "10000";

      document.body.appendChild(menu);

      // Handle menu actions
      menu.addEventListener("click", (e) => {
        const action = e.target.dataset.action;

        switch (action) {
          case "navigate":
            if (window.navigateToQuestion) {
              window.navigateToQuestion(questionIndex);
            }
            break;
          case "mark":
            this.markQuestionForReview(questionIndex);
            break;
          case "clear":
            this.clearQuestionAnswer(questionIndex);
            break;
        }

        document.body.removeChild(menu);
      });

      // Auto-hide menu
      setTimeout(() => {
        if (menu.parentNode) {
          document.body.removeChild(menu);
        }
      }, 5000);
    }

    /**
     * Initialize accessibility features
     */
    initializeAccessibilityFeatures() {
      // Enhanced focus management
      document.addEventListener("focusin", (e) => {
        if (e.target.matches("input, button, .option-label")) {
          // Ensure focused element is visible
          setTimeout(() => {
            e.target.scrollIntoView({
              behavior: "smooth",
              block: "center",
            });
          }, 100);
        }
      });

      // Add ARIA labels for better screen reader support
      const questionNumbers = document.querySelectorAll(".question-number");
      questionNumbers.forEach((btn, index) => {
        if (!btn.getAttribute("aria-label")) {
          btn.setAttribute("aria-label", `Question ${index + 1}`);
        }
      });

      // Enhanced keyboard navigation
      document.addEventListener("keydown", (e) => {
        if (e.target.matches(".question-number")) {
          this.handleQuestionNumberKeyboard(e);
        }
      });

      // Add screen reader announcements
      this.setupScreenReaderAnnouncements();
    }

    /**
     * Handle keyboard navigation for question numbers
     */
    handleQuestionNumberKeyboard(e) {
      const currentBtn = e.target;
      const allBtns = Array.from(document.querySelectorAll(".question-number"));
      const currentIndex = allBtns.indexOf(currentBtn);

      switch (e.key) {
        case "ArrowRight":
        case "ArrowDown":
          e.preventDefault();
          if (currentIndex < allBtns.length - 1) {
            allBtns[currentIndex + 1].focus();
          }
          break;

        case "ArrowLeft":
        case "ArrowUp":
          e.preventDefault();
          if (currentIndex > 0) {
            allBtns[currentIndex - 1].focus();
          }
          break;

        case "Home":
          e.preventDefault();
          allBtns[0].focus();
          break;

        case "End":
          e.preventDefault();
          allBtns[allBtns.length - 1].focus();
          break;
      }
    }

    /**
     * Setup screen reader announcements
     */
    setupScreenReaderAnnouncements() {
      // Create live region for announcements
      const liveRegion = document.createElement("div");
      liveRegion.setAttribute("aria-live", "polite");
      liveRegion.setAttribute("aria-atomic", "true");
      liveRegion.className = "sr-only";
      liveRegion.id = "mobile-announcements";
      document.body.appendChild(liveRegion);

      // Announce navigation changes
      document.addEventListener("questionNavigate", (e) => {
        const questionNumber = e.detail.questionNumber + 1;
        this.announce(`Navigated to question ${questionNumber}`);
      });

      // Announce answer changes
      document.addEventListener("mcq-answer-changed", (e) => {
        const questionNumber = e.detail.questionNumber + 1;
        this.announce(`Answer selected for question ${questionNumber}`);
      });
    }

    /**
     * Announce message to screen readers
     */
    announce(message) {
      const liveRegion = document.getElementById("mobile-announcements");
      if (liveRegion) {
        liveRegion.textContent = message;

        // Clear after announcement
        setTimeout(() => {
          liveRegion.textContent = "";
        }, 1000);
      }
    }

    /**
     * Initialize performance optimizations
     */
    initializePerformanceOptimizations() {
      // Debounced scroll events for better performance
      let scrollTimeout;
      const handleScroll = () => {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
          this.updateVisibleElements();
        }, 100);
      };

      window.addEventListener("scroll", handleScroll, { passive: true });

      // Optimize touch events
      document.addEventListener(
        "touchstart",
        () => {
          // Prepare for interaction
        },
        { passive: true }
      );

      // Lazy load images in questions
      this.initializeLazyLoading();

      // Optimize animations for mobile
      this.optimizeAnimations();
    }

    /**
     * Update visible elements for performance
     */
    updateVisibleElements() {
      const questions = document.querySelectorAll(
        ".question-card, .question-slide"
      );
      const viewportHeight = window.innerHeight;

      questions.forEach((question, index) => {
        const rect = question.getBoundingClientRect();
        const isVisible = rect.top < viewportHeight && rect.bottom > 0;

        if (isVisible) {
          question.classList.add("visible");
        } else {
          question.classList.remove("visible");
        }
      });
    }

    /**
     * Initialize lazy loading for images
     */
    initializeLazyLoading() {
      if ("IntersectionObserver" in window) {
        const imageObserver = new IntersectionObserver((entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              const img = entry.target;
              if (img.dataset.src) {
                img.src = img.dataset.src;
                img.removeAttribute("data-src");
                imageObserver.unobserve(img);
              }
            }
          });
        });

        document.querySelectorAll("img[data-src]").forEach((img) => {
          imageObserver.observe(img);
        });
      }
    }

    /**
     * Optimize animations for mobile
     */
    optimizeAnimations() {
      // Reduce animations on low-end devices
      if (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4) {
        document.body.classList.add("reduced-animations");
      }

      // Respect user's motion preferences
      if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
        document.body.classList.add("reduced-animations");
      }
    }

    /**
     * Handle orientation changes
     */
    handleOrientationChange() {
      const isLandscape = window.innerHeight < window.innerWidth;
      const isSmallScreen = window.innerHeight < 500;

      if (isLandscape && isSmallScreen && this.isMobile) {
        this.showOrientationWarning();
      } else {
        this.hideOrientationWarning();
      }

      // Recalculate layout
      setTimeout(() => {
        if (window.updateMobileProgressDetails) {
          window.updateMobileProgressDetails();
        }
      }, 100);
    }

    /**
     * Show orientation warning for very small landscape screens
     */
    showOrientationWarning() {
      let warning = document.querySelector(".orientation-warning");
      if (!warning) {
        warning = document.createElement("div");
        warning.className = "orientation-warning";
        warning.innerHTML = `
          <div class="orientation-content">
            <h3>Better Experience Available</h3>
            <p>For the best experience, please rotate to portrait mode or use a larger screen.</p>
            <button class="dismiss-orientation">Continue Anyway</button>
          </div>
        `;
        document.body.appendChild(warning);

        warning
          .querySelector(".dismiss-orientation")
          .addEventListener("click", () => {
            warning.style.display = "none";
          });
      }
      warning.style.display = "flex";
    }

    /**
     * Hide orientation warning
     */
    hideOrientationWarning() {
      const warning = document.querySelector(".orientation-warning");
      if (warning) {
        warning.style.display = "none";
      }
    }

    /**
     * Initialize mobile notifications
     */
    initializeMobileNotifications() {
      // Create notification container
      if (!document.querySelector(".mobile-notifications")) {
        const container = document.createElement("div");
        container.className = "mobile-notifications";
        document.body.appendChild(container);
      }

      // Store notification function globally
      window.showMobileNotification = (
        message,
        type = "info",
        duration = 3000
      ) => {
        this.showNotification(message, type, duration);
      };
    }

    /**
     * Show mobile notification
     */
    showNotification(message, type = "info", duration = 3000) {
      const container = document.querySelector(".mobile-notifications");
      if (!container) return;

      const notification = document.createElement("div");
      notification.className = `mobile-notification ${type}`;
      notification.innerHTML = `
        <div class="notification-content">
          <span class="notification-message">${message}</span>
          <button class="notification-close">&times;</button>
        </div>
      `;

      container.appendChild(notification);

      // Show notification
      setTimeout(() => {
        notification.classList.add("show");
      }, 100);

      // Handle close button
      notification
        .querySelector(".notification-close")
        .addEventListener("click", () => {
          this.hideNotification(notification);
        });

      // Auto-hide
      setTimeout(() => {
        this.hideNotification(notification);
      }, duration);
    }

    /**
     * Hide notification
     */
    hideNotification(notification) {
      notification.classList.remove("show");
      setTimeout(() => {
        if (notification.parentNode) {
          notification.parentNode.removeChild(notification);
        }
      }, 300);
    }

    /**
     * Initialize gesture enhancements
     */
    initializeGestureEnhancements() {
      // Enhanced pinch-to-zoom prevention for assessment content
      document.addEventListener("gesturestart", (e) => {
        e.preventDefault();
      });

      document.addEventListener("gesturechange", (e) => {
        e.preventDefault();
      });

      document.addEventListener("gestureend", (e) => {
        e.preventDefault();
      });

      // Enhanced double-tap prevention
      let lastTap = 0;
      document.addEventListener("touchend", (e) => {
        const currentTime = new Date().getTime();
        const tapLength = currentTime - lastTap;

        if (tapLength < 500 && tapLength > 0) {
          e.preventDefault();
        }
        lastTap = currentTime;
      });
    }

    /**
     * Mark question for review
     */
    markQuestionForReview(questionIndex) {
      // Implementation for marking questions for review
      const questionBtn = document.querySelector(
        `[data-question="${questionIndex}"]`
      );
      if (questionBtn) {
        questionBtn.classList.toggle("marked-for-review");

        const isMarked = questionBtn.classList.contains("marked-for-review");
        this.announce(
          isMarked
            ? `Question ${questionIndex + 1} marked for review`
            : `Question ${questionIndex + 1} unmarked for review`
        );
      }
    }

    /**
     * Clear question answer
     */
    clearQuestionAnswer(questionIndex) {
      // Implementation for clearing question answers
      const questionInputs = document.querySelectorAll(
        `input[name="question_${questionIndex}"]`
      );
      questionInputs.forEach((input) => {
        input.checked = false;
      });

      // Update navigation panel if available
      if (window.questionNavigationPanel) {
        window.questionNavigationPanel.updateQuestionStatus(
          questionIndex,
          "unanswered"
        );
      }

      this.announce(`Answer cleared for question ${questionIndex + 1}`);
    }
  }

  // Initialize when document is ready
  $(document).ready(function () {
    // Only initialize if we're on an assessment page
    if (document.querySelector(".mcq-assessment-container")) {
      window.mobileAssessmentEnhancements = new MobileAssessmentEnhancements();
    }
  });
})(jQuery);
