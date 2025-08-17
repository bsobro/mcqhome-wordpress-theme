/**
 * Assessment Delivery System JavaScript
 * MCQHome Theme
 */

(function ($) {
  "use strict";

  // Assessment state
  let assessmentState = {
    currentQuestion: 0,
    answers: {},
    timeRemaining: 0,
    timerInterval: null,
    autoSaveTimeout: null,
    isSubmitting: false,
    startTime: Date.now(),
    securityManager: null,
  };

  // Initialize assessment when document is ready
  $(document).ready(function () {
    initializeAssessment();
  });

  /**
   * Initialize the assessment system
   */
  function initializeAssessment() {
    // Load assessment data from global variable
    if (typeof window.assessmentData === "undefined") {
      console.error("Assessment data not found");
      return;
    }

    const data = window.assessmentData;

    // Initialize state
    assessmentState.currentQuestion = data.currentQuestion || 0;
    assessmentState.answers = data.answersData || {};
    assessmentState.timeRemaining = data.timeLimit || 0;

    // Initialize security manager
    if (typeof window.AssessmentSecurity !== "undefined") {
      assessmentState.securityManager = new window.AssessmentSecurity({
        setId: data.setId,
        userId: data.userId,
        timeLimit: data.timeLimit,
        ajaxUrl: data.ajaxUrl,
        nonce: data.nonce,
        preventNavigation: true,
        assessmentUrl: window.location.href,
      });
      console.log("Security manager initialized");
    }

    // Initialize timer if time limit is set
    if (assessmentState.timeRemaining > 0) {
      initializeTimer();
    }

    // Initialize navigation
    initializeNavigation();

    // Initialize auto-save
    initializeAutoSave();

    // Initialize keyboard shortcuts
    initializeKeyboardShortcuts();

    // Initialize visibility change handler
    initializeVisibilityHandler();

    // Initialize mobile-specific features
    initializeMobileFeatures();

    // Initialize navigation panel integration
    initializeNavigationPanelIntegration();

    // Update UI
    updateProgressIndicator();
    updateQuestionNavigation();

    // Expose assessment interface for security manager
    window.assessmentInterface = {
      autoSubmit: function (reason) {
        console.log("Auto-submitting assessment:", reason);
        submitAssessment(true);
      },
      getCurrentAnswers: function () {
        return assessmentState.answers;
      },
      getTimeTaken: function () {
        return Math.floor((Date.now() - assessmentState.startTime) / 1000);
      },
    };

    console.log("Assessment initialized successfully");
  }

  /**
   * Initialize timer functionality
   */
  function initializeTimer() {
    const timerDisplay = $("#timer-display");

    if (timerDisplay.length === 0) {
      return;
    }

    // Start timer
    assessmentState.timerInterval = setInterval(function () {
      assessmentState.timeRemaining--;

      const minutes = Math.floor(assessmentState.timeRemaining / 60);
      const seconds = assessmentState.timeRemaining % 60;
      const timeString = `${minutes.toString().padStart(2, "0")}:${seconds
        .toString()
        .padStart(2, "0")}`;

      timerDisplay.text(timeString);

      // Warning when 5 minutes remaining
      if (assessmentState.timeRemaining === 300) {
        timerDisplay.addClass("timer-warning");
        showNotification("5 minutes remaining!", "warning");
      }

      // Warning when 1 minute remaining
      if (assessmentState.timeRemaining === 60) {
        showNotification("1 minute remaining!", "error");
      }

      // Auto-submit when time is up
      if (assessmentState.timeRemaining <= 0) {
        clearInterval(assessmentState.timerInterval);
        timerDisplay.text("00:00");
        showNotification("Time is up! Submitting assessment...", "error");
        setTimeout(function () {
          submitAssessment(true); // Auto-submit
        }, 2000);
      }
    }, 1000);
  }

  /**
   * Initialize navigation functionality
   */
  function initializeNavigation() {
    // Question navigation buttons with enhanced touch support
    $(".question-nav-btn").on("click touchend", function (e) {
      e.preventDefault();
      const questionIndex = parseInt($(this).data("question"));
      navigateToQuestion(questionIndex);
    });

    // Previous/Next buttons with touch support
    $(".prev-btn").on("click touchend", function (e) {
      e.preventDefault();
      const currentIndex = assessmentState.currentQuestion;
      if (currentIndex > 0) {
        navigateToQuestion(currentIndex - 1);
      }
    });

    $(".next-btn").on("click touchend", function (e) {
      e.preventDefault();
      const currentIndex = assessmentState.currentQuestion;
      const totalQuestions = window.assessmentData.totalQuestions;
      if (currentIndex < totalQuestions - 1) {
        navigateToQuestion(currentIndex + 1);
      }
    });

    // Submit button with touch support
    $(".submit-btn").on("click touchend", function (e) {
      e.preventDefault();
      submitAssessment();
    });

    // Answer selection with enhanced mobile support
    $('input[type="radio"]').on("change", function () {
      const questionIndex = parseInt(
        $(this).attr("name").replace("question_", "")
      );
      const selectedAnswer = $(this).val();
      saveAnswer(questionIndex, selectedAnswer);
    });

    // Enhanced option label clicking for mobile
    $(".option-label").on("click touchend", function (e) {
      const radio = $(this).find('input[type="radio"]');
      if (radio.length && !radio.prop("checked")) {
        radio.prop("checked", true).trigger("change");
      }
    });
  }

  /**
   * Initialize auto-save functionality
   */
  function initializeAutoSave() {
    // Auto-save every 30 seconds
    setInterval(function () {
      if (Object.keys(assessmentState.answers).length > 0) {
        autoSaveProgress();
      }
    }, 30000);

    // Save on page unload
    $(window).on("beforeunload", function () {
      if (
        Object.keys(assessmentState.answers).length > 0 &&
        !assessmentState.isSubmitting
      ) {
        autoSaveProgress(false); // Synchronous save
        return "You have unsaved progress. Are you sure you want to leave?";
      }
    });
  }

  /**
   * Initialize keyboard shortcuts
   */
  function initializeKeyboardShortcuts() {
    $(document).on("keydown", function (e) {
      // Prevent shortcuts during submission
      if (assessmentState.isSubmitting) {
        return;
      }

      const currentIndex = assessmentState.currentQuestion;
      const totalQuestions = window.assessmentData.totalQuestions;

      switch (e.key) {
        case "ArrowLeft":
          if (e.ctrlKey && currentIndex > 0) {
            e.preventDefault();
            navigateToQuestion(currentIndex - 1);
          }
          break;

        case "ArrowRight":
          if (e.ctrlKey && currentIndex < totalQuestions - 1) {
            e.preventDefault();
            navigateToQuestion(currentIndex + 1);
          }
          break;

        case "1":
        case "2":
        case "3":
        case "4":
          if (e.altKey) {
            e.preventDefault();
            const optionMap = { 1: "A", 2: "B", 3: "C", 4: "D" };
            const option = optionMap[e.key];
            selectOption(currentIndex, option);
          }
          break;

        case "Enter":
          if (e.ctrlKey) {
            e.preventDefault();
            submitAssessment();
          }
          break;
      }
    });
  }

  /**
   * Initialize visibility change handler
   */
  function initializeVisibilityHandler() {
    document.addEventListener("visibilitychange", function () {
      if (document.hidden) {
        // Page is hidden - save progress
        autoSaveProgress();
      }
    });
  }

  /**
   * Initialize mobile-specific features
   */
  function initializeMobileFeatures() {
    // Check if device is mobile
    const isMobile =
      window.innerWidth <= 768 ||
      /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
        navigator.userAgent
      );

    if (!isMobile) return;

    // Initialize touch gestures for question navigation
    initializeTouchGestures();

    // Initialize collapsible navigation panel
    initializeCollapsibleNavPanel();

    // Initialize smooth scrolling
    initializeSmoothScrolling();

    // Initialize mobile-specific keyboard handling
    initializeMobileKeyboard();

    // Prevent zoom on double tap for form elements
    preventZoomOnDoubleTap();

    // Initialize orientation change handler
    initializeOrientationHandler();

    // Initialize mobile notifications
    initializeMobileNotifications();

    // Initialize haptic feedback
    initializeHapticFeedback();

    // Initialize mobile progress tracking
    initializeMobileProgressTracking();

    // Initialize mobile accessibility features
    initializeMobileAccessibility();

    // Initialize mobile performance optimizations
    initializeMobilePerformanceOptimizations();

    console.log("Mobile features initialized");
  }

  /**
   * Initialize enhanced touch gestures for navigation
   */
  function initializeTouchGestures() {
    let startX = 0;
    let startY = 0;
    let endX = 0;
    let endY = 0;
    let startTime = 0;
    let isScrolling = false;
    let touchStartElement = null;

    const questionContainer = document.querySelector(".question-display-area");
    if (!questionContainer) return;

    questionContainer.addEventListener(
      "touchstart",
      function (e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        startTime = Date.now();
        isScrolling = false;
        touchStartElement = e.target;

        // Add touch feedback
        createTouchFeedback(e.touches[0].clientX, e.touches[0].clientY);
      },
      { passive: true }
    );

    questionContainer.addEventListener(
      "touchmove",
      function (e) {
        const currentX = e.touches[0].clientX;
        const currentY = e.touches[0].clientY;
        const deltaX = Math.abs(currentX - startX);
        const deltaY = Math.abs(currentY - startY);

        // Determine if user is scrolling vertically
        if (deltaY > deltaX && deltaY > 15) {
          isScrolling = true;
        }

        // Prevent swipe if touch started on interactive elements
        if (
          touchStartElement &&
          (touchStartElement.matches(
            "input, button, .option-label, .question-number"
          ) ||
            touchStartElement.closest(
              "input, button, .option-label, .question-number"
            ))
        ) {
          isScrolling = true;
        }
      },
      { passive: true }
    );

    questionContainer.addEventListener(
      "touchend",
      function (e) {
        endX = e.changedTouches[0].clientX;
        endY = e.changedTouches[0].clientY;
        const endTime = Date.now();

        // Only handle swipes if not scrolling and gesture was quick enough
        if (
          !isScrolling &&
          endTime - startTime < 600 &&
          endTime - startTime > 100
        ) {
          handleSwipeGesture();
        }
      },
      { passive: true }
    );

    function handleSwipeGesture() {
      const deltaX = endX - startX;
      const deltaY = endY - startY;
      const minSwipeDistance = 100; // Increased for better accuracy
      const maxVerticalDeviation = 120;
      const swipeVelocity = Math.abs(deltaX) / (Date.now() - startTime);

      // Only handle horizontal swipes that are longer than vertical and fast enough
      if (
        Math.abs(deltaX) > Math.abs(deltaY) &&
        Math.abs(deltaX) > minSwipeDistance &&
        Math.abs(deltaY) < maxVerticalDeviation &&
        swipeVelocity > 0.3
      ) {
        const currentIndex = assessmentState.currentQuestion;
        const totalQuestions = window.assessmentData.totalQuestions;

        // Only allow swipes in Next-Next format
        if (window.assessmentData.displayFormat === "next_next") {
          if (deltaX > 0 && currentIndex > 0) {
            // Swipe right - go to previous question
            showSwipeIndicator("← Previous Question", "success");
            if (window.mobileHapticFeedback) {
              window.mobileHapticFeedback("light");
            }
            navigateToQuestion(currentIndex - 1);
          } else if (deltaX < 0 && currentIndex < totalQuestions - 1) {
            // Swipe left - go to next question
            showSwipeIndicator("Next Question →", "success");
            if (window.mobileHapticFeedback) {
              window.mobileHapticFeedback("light");
            }
            navigateToQuestion(currentIndex + 1);
          } else {
            // Invalid swipe - show feedback
            if (deltaX > 0 && currentIndex === 0) {
              showSwipeIndicator("← Already at first question", "warning");
            } else if (deltaX < 0 && currentIndex === totalQuestions - 1) {
              showSwipeIndicator("Already at last question →", "warning");
            }
            if (window.mobileHapticFeedback) {
              window.mobileHapticFeedback("error");
            }
          }
        } else {
          // Single page format - show info about navigation
          showSwipeIndicator(
            "Use navigation panel to jump to questions",
            "info"
          );
        }
      }
    }

    /**
     * Create touch feedback effect
     */
    function createTouchFeedback(x, y) {
      const feedback = document.createElement("div");
      feedback.className = "touch-feedback";
      feedback.style.left = x - 20 + "px";
      feedback.style.top = y - 20 + "px";
      document.body.appendChild(feedback);

      setTimeout(() => {
        if (feedback.parentNode) {
          feedback.parentNode.removeChild(feedback);
        }
      }, 600);
    }

    /**
     * Show enhanced swipe indicator
     */
    function showSwipeIndicator(message, type = "info") {
      let indicator = document.querySelector(".swipe-indicator");
      if (!indicator) {
        indicator = document.createElement("div");
        indicator.className = "swipe-indicator";
        document.body.appendChild(indicator);
      }

      // Add type-specific styling
      indicator.className = `swipe-indicator ${type}`;
      indicator.textContent = message;
      indicator.classList.add("show");

      // Auto-hide after delay
      setTimeout(
        () => {
          indicator.classList.remove("show");
        },
        type === "warning" ? 2000 : 1500
      );
    }
  }

  /**
   * Initialize enhanced collapsible navigation panel for mobile
   */
  function initializeCollapsibleNavPanel() {
    const navPanel = $(".question-nav-panel");
    const navHeader = navPanel.find("h3");

    if (navHeader.length === 0) return;

    // Add collapsible class for styling
    navPanel.addClass("collapsible");

    // Enhanced click handler with better touch support
    navHeader.on("click touchend", function (e) {
      e.preventDefault();
      e.stopPropagation();

      const wasCollapsed = navPanel.hasClass("collapsed");
      navPanel.toggleClass("collapsed");

      // Update icon
      const icon = navHeader.find("::after");

      // Provide haptic feedback
      if (window.mobileHapticFeedback) {
        window.mobileHapticFeedback("light");
      }

      // Save state to localStorage
      const isCollapsed = navPanel.hasClass("collapsed");
      localStorage.setItem("mcq_nav_collapsed", isCollapsed);

      // Announce change for screen readers
      if (window.announceMobileChange) {
        window.announceMobileChange(
          isCollapsed
            ? "Navigation panel collapsed"
            : "Navigation panel expanded"
        );
      }
    });

    // Add swipe gesture support for collapse/expand
    let startY = 0;
    let endY = 0;

    navPanel.on("touchstart", function (e) {
      startY = e.originalEvent.touches[0].clientY;
    });

    navPanel.on("touchend", function (e) {
      endY = e.originalEvent.changedTouches[0].clientY;
      const deltaY = endY - startY;
      const minSwipeDistance = 50;

      if (Math.abs(deltaY) > minSwipeDistance) {
        if (deltaY > 0 && navPanel.hasClass("collapsed")) {
          // Swipe down - expand
          navPanel.removeClass("collapsed");
          localStorage.setItem("mcq_nav_collapsed", false);
        } else if (deltaY < 0 && !navPanel.hasClass("collapsed")) {
          // Swipe up - collapse
          navPanel.addClass("collapsed");
          localStorage.setItem("mcq_nav_collapsed", true);
        }
      }
    });

    // Restore previous state
    const wasCollapsed = localStorage.getItem("mcq_nav_collapsed") === "true";
    if (wasCollapsed) {
      navPanel.addClass("collapsed");
    }

    // Auto-collapse on question selection for better UX
    navPanel.on("click touchend", ".question-number", function () {
      if (window.innerWidth <= 640) {
        setTimeout(() => {
          navPanel.addClass("collapsed");
          localStorage.setItem("mcq_nav_collapsed", true);
        }, 300);
      }
    });
  }

  /**
   * Initialize smooth scrolling for mobile
   */
  function initializeSmoothScrolling() {
    // Smooth scroll to active question in single page format
    if (window.assessmentData.displayFormat === "single_page") {
      $(document).on("click", ".question-nav-btn", function () {
        const questionIndex = parseInt($(this).data("question"));
        const targetQuestion = $(
          `.question-card[data-question="${questionIndex}"]`
        );

        if (targetQuestion.length) {
          $("html, body").animate(
            {
              scrollTop: targetQuestion.offset().top - 80,
            },
            300,
            "easeInOutQuad"
          );
        }
      });
    }
  }

  /**
   * Initialize mobile keyboard handling
   */
  function initializeMobileKeyboard() {
    // Handle virtual keyboard appearance
    let initialViewportHeight = window.innerHeight;
    let keyboardTimeout;

    // Use visual viewport API if available
    if (window.visualViewport) {
      window.visualViewport.addEventListener("resize", handleViewportChange);
    } else {
      // Fallback for older browsers
      window.addEventListener("resize", handleViewportChange);
    }

    function handleViewportChange() {
      clearTimeout(keyboardTimeout);
      keyboardTimeout = setTimeout(() => {
        const currentHeight = window.visualViewport
          ? window.visualViewport.height
          : window.innerHeight;
        const heightDifference = initialViewportHeight - currentHeight;

        // If height decreased significantly, keyboard is likely open
        if (heightDifference > 150) {
          document.body.classList.add("keyboard-open");
          handleKeyboardOpen();
        } else {
          document.body.classList.remove("keyboard-open");
          handleKeyboardClose();
        }
      }, 100);
    }

    function handleKeyboardOpen() {
      // Scroll to focused element
      const focusedElement = document.activeElement;
      if (
        focusedElement &&
        (focusedElement.tagName === "INPUT" ||
          focusedElement.tagName === "TEXTAREA")
      ) {
        setTimeout(() => {
          // Calculate safe area
          const keyboardHeight =
            initialViewportHeight -
            (window.visualViewport?.height || window.innerHeight);
          const elementRect = focusedElement.getBoundingClientRect();
          const viewportHeight =
            window.visualViewport?.height || window.innerHeight;

          // Check if element is hidden by keyboard
          if (elementRect.bottom > viewportHeight - 50) {
            focusedElement.scrollIntoView({
              behavior: "smooth",
              block: "center",
            });
          }
        }, 300);
      }

      // Hide navigation panel when keyboard is open
      const navPanel = document.querySelector(".question-nav-panel");
      if (navPanel && !navPanel.classList.contains("collapsed")) {
        navPanel.classList.add("keyboard-hidden");
      }

      // Adjust question navigation position
      const questionNav = document.querySelector(".question-navigation");
      if (questionNav) {
        questionNav.style.position = "fixed";
        questionNav.style.bottom = "0";
        questionNav.style.left = "0";
        questionNav.style.right = "0";
        questionNav.style.zIndex = "1000";
      }
    }

    function handleKeyboardClose() {
      // Restore navigation panel
      const navPanel = document.querySelector(".question-nav-panel");
      if (navPanel) {
        navPanel.classList.remove("keyboard-hidden");
      }

      // Reset question navigation position
      const questionNav = document.querySelector(".question-navigation");
      if (questionNav) {
        questionNav.style.position = "";
        questionNav.style.bottom = "";
        questionNav.style.left = "";
        questionNav.style.right = "";
        questionNav.style.zIndex = "";
      }
    }

    // Handle input focus for better mobile experience
    document.addEventListener("focusin", function (e) {
      if (e.target.matches("input[type='radio']")) {
        // Add visual focus indicator for radio buttons
        e.target.closest(".option-label")?.classList.add("focused");
      }
    });

    document.addEventListener("focusout", function (e) {
      if (e.target.matches("input[type='radio']")) {
        // Remove visual focus indicator
        e.target.closest(".option-label")?.classList.remove("focused");
      }
    });
  }

  /**
   * Prevent zoom on double tap for form elements
   */
  function preventZoomOnDoubleTap() {
    let lastTouchEnd = 0;

    document.addEventListener(
      "touchend",
      function (e) {
        const now = new Date().getTime();
        if (now - lastTouchEnd <= 300) {
          e.preventDefault();
        }
        lastTouchEnd = now;
      },
      false
    );

    // Add touch-action CSS to prevent zoom
    $('input[type="radio"], button, .option-label').css(
      "touch-action",
      "manipulation"
    );
  }

  /**
   * Initialize orientation change handler
   */
  function initializeOrientationHandler() {
    window.addEventListener("orientationchange", function () {
      // Delay to allow orientation change to complete
      setTimeout(function () {
        // Recalculate layout
        updateProgressIndicator();
        updateQuestionNavigation();

        // Scroll to current question if needed
        const currentQuestionElement = $(
          ".question-slide.active, .question-card"
        ).eq(assessmentState.currentQuestion);
        if (
          currentQuestionElement.length &&
          window.assessmentData.displayFormat === "single_page"
        ) {
          $("html, body").animate(
            {
              scrollTop: currentQuestionElement.offset().top - 80,
            },
            300
          );
        }

        // Handle landscape orientation on small screens
        handleLandscapeOrientation();
      }, 500);
    });

    // Initial orientation check
    handleLandscapeOrientation();
  }

  /**
   * Handle landscape orientation on small screens
   */
  function handleLandscapeOrientation() {
    const isLandscape = window.innerHeight < window.innerWidth;
    const isSmallScreen = window.innerHeight < 500;

    if (isLandscape && isSmallScreen) {
      // Show orientation message for very small landscape screens
      showOrientationMessage();
    } else {
      hideOrientationMessage();
    }
  }

  /**
   * Show orientation message
   */
  function showOrientationMessage() {
    let orientationMsg = document.querySelector(".orientation-message");
    if (!orientationMsg) {
      orientationMsg = document.createElement("div");
      orientationMsg.className = "orientation-message";
      orientationMsg.innerHTML = `
        <div>
          <h3>Please rotate your device</h3>
          <p>For the best experience, please use portrait mode or a larger screen.</p>
        </div>
      `;
      document.body.appendChild(orientationMsg);
    }
    orientationMsg.style.display = "flex";
  }

  /**
   * Hide orientation message
   */
  function hideOrientationMessage() {
    const orientationMsg = document.querySelector(".orientation-message");
    if (orientationMsg) {
      orientationMsg.style.display = "none";
    }
  }

  /**
   * Initialize mobile notifications
   */
  function initializeMobileNotifications() {
    // Create mobile notification container
    if (!document.querySelector(".mobile-notification-container")) {
      const container = document.createElement("div");
      container.className = "mobile-notification-container";
      document.body.appendChild(container);
    }
  }

  /**
   * Show mobile notification
   */
  function showMobileNotification(message, type = "info", duration = 3000) {
    const container = document.querySelector(".mobile-notification-container");
    if (!container) return;

    const notification = document.createElement("div");
    notification.className = `mobile-notification ${type}`;
    notification.textContent = message;

    container.appendChild(notification);

    // Show notification
    setTimeout(() => {
      notification.classList.add("show");
    }, 100);

    // Hide and remove notification
    setTimeout(() => {
      notification.classList.remove("show");
      setTimeout(() => {
        if (notification.parentNode) {
          notification.parentNode.removeChild(notification);
        }
      }, 300);
    }, duration);
  }

  /**
   * Initialize haptic feedback
   */
  function initializeHapticFeedback() {
    // Store haptic feedback function
    window.mobileHapticFeedback = function (type = "light") {
      if ("vibrate" in navigator) {
        switch (type) {
          case "light":
            navigator.vibrate(50);
            break;
          case "medium":
            navigator.vibrate(100);
            break;
          case "heavy":
            navigator.vibrate([100, 50, 100]);
            break;
          case "success":
            navigator.vibrate([50, 25, 50]);
            break;
          case "error":
            navigator.vibrate([100, 50, 100, 50, 100]);
            break;
        }
      }
    };
  }

  /**
   * Initialize enhanced mobile progress tracking
   */
  function initializeMobileProgressTracking() {
    // Enhanced progress indicators for mobile
    const progressContainer = document.querySelector(".progress-indicator");
    if (
      progressContainer &&
      !progressContainer.querySelector(".mobile-progress-details")
    ) {
      const detailsElement = document.createElement("div");
      detailsElement.className = "mobile-progress-details";
      progressContainer.appendChild(detailsElement);
    }

    // Create mobile progress bar if not exists
    if (!document.querySelector(".mobile-progress-bar")) {
      const progressBar = document.createElement("div");
      progressBar.className = "mobile-progress-bar";
      progressBar.innerHTML = `
        <div class="mobile-progress-fill"></div>
        <div class="mobile-progress-text">
          <span class="current-position">1</span> / <span class="total-questions">${window.assessmentData.totalQuestions}</span>
        </div>
      `;

      const assessmentBody = document.querySelector(".assessment-body");
      if (assessmentBody) {
        assessmentBody.insertBefore(progressBar, assessmentBody.firstChild);
      }
    }

    // Enhanced mobile progress details update
    window.updateMobileProgressDetails = function () {
      const detailsElement = document.querySelector(".mobile-progress-details");
      const progressBar = document.querySelector(".mobile-progress-bar");

      const totalQuestions = window.assessmentData.totalQuestions;
      const answeredCount = Object.keys(assessmentState.answers).length;
      const currentIndex = assessmentState.currentQuestion;
      const progressPercentage = ((currentIndex + 1) / totalQuestions) * 100;

      // Update details element
      if (detailsElement) {
        detailsElement.innerHTML = `
          <div class="mobile-progress-stats">
            <span class="current-question">Question ${
              currentIndex + 1
            } of ${totalQuestions}</span>
            <span class="answered-count">${answeredCount} answered</span>
            <span class="progress-percentage">${Math.round(
              progressPercentage
            )}% complete</span>
          </div>
        `;
      }

      // Update progress bar
      if (progressBar) {
        const progressFill = progressBar.querySelector(".mobile-progress-fill");
        const currentPosition = progressBar.querySelector(".current-position");

        if (progressFill) {
          progressFill.style.width = progressPercentage + "%";
        }

        if (currentPosition) {
          currentPosition.textContent = currentIndex + 1;
        }
      }

      // Update navigation panel progress summary
      if (
        window.questionNavigationPanel &&
        window.questionNavigationPanel.updateProgressSummaryAnimated
      ) {
        window.questionNavigationPanel.updateProgressSummaryAnimated();
      }
    };

    // Call initial update
    if (window.updateMobileProgressDetails) {
      window.updateMobileProgressDetails();
    }

    // Update progress on question change
    document.addEventListener("questionNavigate", function () {
      if (window.updateMobileProgressDetails) {
        window.updateMobileProgressDetails();
      }
    });
  }

  /**
   * Initialize mobile accessibility features
   */
  function initializeMobileAccessibility() {
    // Add screen reader announcements for mobile
    window.announceMobileChange = function (message) {
      const announcement = document.createElement("div");
      announcement.setAttribute("aria-live", "polite");
      announcement.setAttribute("aria-atomic", "true");
      announcement.className = "sr-only";
      announcement.textContent = message;

      document.body.appendChild(announcement);

      setTimeout(() => {
        document.body.removeChild(announcement);
      }, 1000);
    };

    // Enhanced focus management for mobile
    document.addEventListener("focusin", function (e) {
      if (e.target.matches("input[type='radio'], button")) {
        // Ensure focused element is visible
        setTimeout(() => {
          e.target.scrollIntoView({
            behavior: "smooth",
            block: "center",
          });
        }, 100);
      }
    });

    // Add touch indicators for screen readers
    const touchElements = document.querySelectorAll(
      ".option-label, .question-number, button"
    );
    touchElements.forEach((element) => {
      if (!element.getAttribute("role")) {
        element.setAttribute("role", "button");
      }
      if (!element.getAttribute("tabindex")) {
        element.setAttribute("tabindex", "0");
      }
    });
  }

  /**
   * Initialize mobile performance optimizations
   */
  function initializeMobilePerformanceOptimizations() {
    // Debounce scroll events
    let scrollTimeout;
    window.addEventListener(
      "scroll",
      function () {
        if (scrollTimeout) {
          clearTimeout(scrollTimeout);
        }
        scrollTimeout = setTimeout(function () {
          // Handle scroll-based updates
          updateVisibleQuestions();
        }, 100);
      },
      { passive: true }
    );

    // Optimize touch events
    document.addEventListener(
      "touchstart",
      function () {
        // Prepare for touch interaction
      },
      { passive: true }
    );

    // Lazy load images in questions
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

      // Observe images with data-src attribute
      document.querySelectorAll("img[data-src]").forEach((img) => {
        imageObserver.observe(img);
      });
    }
  }

  /**
   * Update visible questions for performance
   */
  function updateVisibleQuestions() {
    if (window.assessmentData.displayFormat !== "single_page") return;

    const questions = document.querySelectorAll(".question-card");
    const viewportHeight = window.innerHeight;
    const scrollTop = window.pageYOffset;

    questions.forEach((question, index) => {
      const rect = question.getBoundingClientRect();
      const isVisible = rect.top < viewportHeight && rect.bottom > 0;

      if (isVisible && index !== assessmentState.currentQuestion) {
        // Update current question if it's the most visible one
        const visibilityRatio =
          Math.min(rect.bottom, viewportHeight) - Math.max(rect.top, 0);
        if (visibilityRatio > viewportHeight * 0.5) {
          assessmentState.currentQuestion = index;
          updateQuestionNavigation();
        }
      }
    });
  }

  /**
   * Navigate to a specific question
   */
  window.navigateToQuestion = function (questionIndex) {
    const totalQuestions = window.assessmentData.totalQuestions;

    if (questionIndex < 0 || questionIndex >= totalQuestions) {
      return;
    }

    const displayFormat = window.assessmentData.displayFormat;
    const isMobile = window.innerWidth <= 768;

    // Get question text for accessibility announcement
    let questionText = "";
    let sectionName = "";

    if (displayFormat === "single_page") {
      const questionElement = document.querySelector(
        `.question-card[data-question="${questionIndex}"] .question-text`
      );
      if (questionElement) {
        questionText = questionElement.textContent || questionElement.innerText;
      }
    }

    if (displayFormat === "next_next") {
      // For Next-Next format, we need to update the current question display
      updateCurrentQuestionDisplay(questionIndex);

      // Auto-collapse navigation panel on mobile after selection
      if (isMobile) {
        const navPanel = $(".question-nav-panel");
        if (!navPanel.hasClass("collapsed")) {
          navPanel.addClass("collapsed");
        }
      }
    } else {
      // Single page format - smooth scroll to question
      const targetQuestion = $(
        `.question-card[data-question="${questionIndex}"]`
      );
      if (targetQuestion.length) {
        const offset = isMobile ? 80 : 100;
        const duration = isMobile ? 300 : 500;

        $("html, body").animate(
          {
            scrollTop: targetQuestion.offset().top - offset,
          },
          duration,
          "easeInOutQuad"
        );
      }
    }

    // Update state
    assessmentState.currentQuestion = questionIndex;

    // Update UI
    updateQuestionNavigation();
    updateProgressIndicator();
    updateNavigationControls();

    // Trigger accessibility events
    const questionEvent = new CustomEvent("mcq-question-changed", {
      detail: {
        questionNumber: questionIndex,
        questionText: questionText,
        sectionName: sectionName,
      },
    });
    document.dispatchEvent(questionEvent);

    // Provide haptic feedback on mobile (if supported)
    if (isMobile && "vibrate" in navigator) {
      navigator.vibrate(50);
    }
  };

  /**
   * Update current question display for Next-Next format
   */
  function updateCurrentQuestionDisplay(questionIndex) {
    // This function will be called by the server-side controller
    // For now, we'll make an AJAX request to get the question content
    const data = {
      action: "mcqhome_get_question_content",
      nonce: window.assessmentData.nonce,
      set_id: window.assessmentData.setId,
      question_index: questionIndex,
    };

    $.ajax({
      url: window.assessmentData.ajaxUrl,
      type: "POST",
      data: data,
      success: function (response) {
        if (response.success) {
          $(".current-question-container").html(response.data.html);
          // Re-bind event handlers for the new content
          bindQuestionEventHandlers();
        }
      },
      error: function () {
        console.error("Failed to load question content");
      },
    });
  }

  /**
   * Bind event handlers for question content
   */
  function bindQuestionEventHandlers() {
    // Re-bind radio button change handlers
    $('.current-question-container input[type="radio"]')
      .off("change")
      .on("change", function () {
        const questionIndex = parseInt(
          $(this).attr("name").replace("question_", "")
        );
        const selectedAnswer = $(this).val();
        saveAnswer(questionIndex, selectedAnswer);
      });

    // Re-bind option label handlers
    $(".current-question-container .option-label")
      .off("click touchend")
      .on("click touchend", function (e) {
        const radio = $(this).find('input[type="radio"]');
        if (radio.length && !radio.prop("checked")) {
          radio.prop("checked", true).trigger("change");
        }
      });
  }

  /**
   * Update navigation controls (Previous/Next buttons)
   */
  function updateNavigationControls() {
    const currentIndex = assessmentState.currentQuestion;
    const totalQuestions = window.assessmentData.totalQuestions;

    // Update Previous button
    const prevBtn = $(".btn-previous");
    if (currentIndex === 0) {
      prevBtn.addClass("invisible").prop("disabled", true);
    } else {
      prevBtn.removeClass("invisible").prop("disabled", false);
    }

    // Update Next/Submit button
    const nextBtn = $(".btn-next");
    const submitBtn = $(".btn-submit");

    if (currentIndex === totalQuestions - 1) {
      nextBtn.addClass("hidden");
      submitBtn.removeClass("hidden");
    } else {
      nextBtn.removeClass("hidden");
      submitBtn.addClass("hidden");
    }
  }

  /**
   * Navigate to previous question
   */
  window.navigateToPrevious = function () {
    const currentIndex = assessmentState.currentQuestion;
    if (currentIndex > 0) {
      navigateToQuestion(currentIndex - 1);
    }
  };

  /**
   * Navigate to next question
   */
  window.navigateToNext = function () {
    const currentIndex = assessmentState.currentQuestion;
    const totalQuestions = window.assessmentData.totalQuestions;
    if (currentIndex < totalQuestions - 1) {
      navigateToQuestion(currentIndex + 1);
    }
  };

  /**
   * Skip current question
   */
  window.skipQuestion = function () {
    const currentIndex = assessmentState.currentQuestion;

    // Update navigation panel
    if (window.questionNavigationPanel) {
      window.questionNavigationPanel.markQuestionSkipped(currentIndex);
    }

    // Send status update to server
    updateQuestionStatusOnServer(currentIndex, "skipped");

    // Log skip action
    console.log(`Question ${currentIndex + 1} skipped`);

    // Navigate to next question
    navigateToNext();
  };

  /**
   * Save answer for a question
   */
  window.saveAnswer = function (questionIndex, selectedAnswer) {
    const previousAnswer = assessmentState.answers[questionIndex];

    // Update state
    assessmentState.answers[questionIndex] = selectedAnswer;

    // Get option text for accessibility announcement
    const optionElement = document.querySelector(
      `input[name="question_${questionIndex}"][value="${selectedAnswer}"]`
    );
    const optionText = optionElement
      ? optionElement.closest(".option-label").querySelector(".option-text")
          .textContent
      : "";

    // Log activity with security manager
    if (assessmentState.securityManager) {
      assessmentState.securityManager.logActivity("answer_selected", {
        question: questionIndex,
        answer: selectedAnswer,
        timestamp: Date.now(),
      });
    }

    // Update navigation panel
    if (window.questionNavigationPanel) {
      window.questionNavigationPanel.markQuestionAnswered(
        questionIndex,
        selectedAnswer
      );
    }

    // Update UI
    updateOptionSelection(questionIndex, selectedAnswer);
    updateQuestionNavigation();
    updateProgressIndicator();

    // Trigger accessibility events
    const answerEvent = new CustomEvent("mcq-answer-changed", {
      detail: {
        questionNumber: questionIndex,
        answer: selectedAnswer,
        optionText: optionText,
        previousAnswer: previousAnswer,
      },
    });
    document.dispatchEvent(answerEvent);

    // Schedule auto-save
    scheduleAutoSave();

    // Send status update to server
    updateQuestionStatusOnServer(questionIndex, "attempted", selectedAnswer);

    console.log(
      `Answer saved: Question ${questionIndex + 1} = ${selectedAnswer}`
    );
  };

  /**
   * Select an option programmatically
   */
  function selectOption(questionIndex, option) {
    const radioButton = $(
      `input[name="question_${questionIndex}"][value="${option}"]`
    );
    if (radioButton.length) {
      radioButton.prop("checked", true).trigger("change");
    }
  }

  /**
   * Update option selection UI
   */
  function updateOptionSelection(questionIndex, selectedAnswer) {
    // Update radio button
    $(`input[name="question_${questionIndex}"]`).prop("checked", false);
    $(
      `input[name="question_${questionIndex}"][value="${selectedAnswer}"]`
    ).prop("checked", true);

    // Update option labels
    $(
      `.question-slide[data-question="${questionIndex}"] .option-label, .question-card[data-question="${questionIndex}"] .option-label`
    ).removeClass("selected");
    $(`input[name="question_${questionIndex}"][value="${selectedAnswer}"]`)
      .closest(".option-label")
      .addClass("selected");
  }

  /**
   * Update question navigation UI
   */
  function updateQuestionNavigation() {
    // Update navigation panel if it exists
    if (window.questionNavigationPanel) {
      window.questionNavigationPanel.updateCurrentIndicator();
      window.questionNavigationPanel.updateProgressSummary();
    }

    // Fallback for legacy navigation buttons
    $(".question-nav-btn, .question-number").each(function () {
      const questionIndex = parseInt($(this).data("question"));
      const isAnswered = assessmentState.answers.hasOwnProperty(questionIndex);
      const isCurrent = questionIndex === assessmentState.currentQuestion;

      $(this).removeClass(
        "bg-blue-500 bg-green-500 bg-gray-200 text-white text-gray-700 ring-2 ring-blue-300 current attempted unanswered"
      );

      if (isCurrent) {
        $(this).addClass("bg-blue-500 text-white ring-2 ring-blue-300 current");
      } else if (isAnswered) {
        $(this).addClass("bg-green-500 text-white attempted");
      } else {
        $(this).addClass("bg-gray-200 text-gray-700 unanswered");
      }
    });
  }

  /**
   * Update progress indicator
   */
  function updateProgressIndicator() {
    const totalQuestions = window.assessmentData.totalQuestions;
    const answeredCount = Object.keys(assessmentState.answers).length;
    const progressPercentage = (answeredCount / totalQuestions) * 100;
    const skippedCount = 0; // Add skipped count if available

    // Update progress bar
    $(".progress-fill").css("width", progressPercentage + "%");

    // Update progress text
    $("#progress-text").text(`${answeredCount} of ${totalQuestions} completed`);

    // Update ARIA attributes for progress bar
    const progressBar = document.querySelector(".progress-bar");
    if (progressBar) {
      progressBar.setAttribute("aria-valuenow", answeredCount.toString());
      progressBar.setAttribute("aria-valuemin", "0");
      progressBar.setAttribute("aria-valuemax", totalQuestions.toString());
      progressBar.setAttribute(
        "aria-valuetext",
        `${answeredCount} of ${totalQuestions} questions answered, ${Math.round(
          progressPercentage
        )}% complete`
      );
    }

    // Trigger accessibility progress event
    const progressEvent = new CustomEvent("mcq-progress-updated", {
      detail: {
        attempted: answeredCount,
        total: totalQuestions,
        skipped: skippedCount,
        percentage: progressPercentage,
      },
    });
    document.dispatchEvent(progressEvent);
  }

  /**
   * Schedule auto-save
   */
  function scheduleAutoSave() {
    if (assessmentState.autoSaveTimeout) {
      clearTimeout(assessmentState.autoSaveTimeout);
    }

    assessmentState.autoSaveTimeout = setTimeout(function () {
      autoSaveProgress();
    }, 2000); // Save 2 seconds after last change
  }

  /**
   * Auto-save progress
   */
  function autoSaveProgress(async = true) {
    const progressData = {
      currentQuestion: assessmentState.currentQuestion,
      answers: assessmentState.answers,
      skipped: [], // Add skipped questions if needed
      progressPercentage: calculateProgressPercentage(),
    };

    // Use secure progress saving if security manager is available
    if (assessmentState.securityManager && async) {
      assessmentState.securityManager
        .saveSecureProgress(progressData)
        .then(function (response) {
          showAutoSaveIndicator("success");
        })
        .catch(function (error) {
          console.error("Secure auto-save failed:", error);
          // Fallback to regular save
          fallbackAutoSave(progressData, async);
        });
    } else {
      // Fallback or synchronous save
      fallbackAutoSave(progressData, async);
    }
  }

  /**
   * Fallback auto-save method
   */
  function fallbackAutoSave(progressData, async = true) {
    const data = {
      action: "mcqhome_save_progress",
      nonce: window.assessmentData.nonce,
      mcq_set_id: window.assessmentData.setId,
      current_question: progressData.currentQuestion,
      answers: progressData.answers,
      skipped: progressData.skipped,
      progress_percentage: progressData.progressPercentage,
    };

    const ajaxOptions = {
      url: window.assessmentData.ajaxUrl,
      type: "POST",
      data: data,
      success: function (response) {
        if (response.success) {
          showAutoSaveIndicator("success");
        } else {
          showAutoSaveIndicator("error");
          console.error("Auto-save failed:", response.data);
        }
      },
      error: function () {
        showAutoSaveIndicator("error");
        console.error("Auto-save request failed");
      },
    };

    if (!async) {
      ajaxOptions.async = false;
    }

    $.ajax(ajaxOptions);
  }

  /**
   * Calculate progress percentage
   */
  function calculateProgressPercentage() {
    const totalQuestions = window.assessmentData.totalQuestions || 1;
    const answeredQuestions = Object.keys(assessmentState.answers).length;
    return Math.round((answeredQuestions / totalQuestions) * 100);
  }

  /**
   * Show auto-save indicator
   */
  function showAutoSaveIndicator(type) {
    let indicator = $(".auto-save-indicator");

    if (indicator.length === 0) {
      indicator = $('<div class="auto-save-indicator"></div>').appendTo("body");
    }

    indicator.removeClass("show error");

    if (type === "success") {
      indicator.text("Progress saved").removeClass("error");
    } else {
      indicator.text("Save failed").addClass("error");
    }

    indicator.addClass("show");

    setTimeout(function () {
      indicator.removeClass("show");
    }, 2000);
  }

  /**
   * Submit assessment
   */
  window.submitAssessment = function (autoSubmit = false) {
    if (assessmentState.isSubmitting) {
      return;
    }

    const totalQuestions = window.assessmentData.totalQuestions;
    const answeredCount = Object.keys(assessmentState.answers).length;

    // Confirmation for incomplete assessment
    if (!autoSubmit && answeredCount < totalQuestions) {
      const unansweredCount = totalQuestions - answeredCount;
      const message = `You have ${unansweredCount} unanswered question(s). Are you sure you want to submit?`;

      if (!confirm(message)) {
        return;
      }
    }

    // Set submitting state
    assessmentState.isSubmitting = true;

    // Disable all form elements
    $("input, button").prop("disabled", true);
    $(".submit-btn").addClass("loading").text("Submitting...");

    // Stop timer
    if (assessmentState.timerInterval) {
      clearInterval(assessmentState.timerInterval);
    }

    // Calculate time taken
    const timeTaken = Math.floor(
      (Date.now() - assessmentState.startTime) / 1000
    );

    // Use secure submission if security manager is available
    if (assessmentState.securityManager) {
      assessmentState.securityManager
        .submitSecureAssessment(assessmentState.answers, {
          timeTaken: timeTaken,
        })
        .then(function (response) {
          showNotification("Assessment submitted successfully!", "success");

          // Redirect to results page
          setTimeout(function () {
            window.location.href = response.redirect_url || "/dashboard/";
          }, 2000);
        })
        .catch(function (error) {
          console.error("Secure submission failed:", error);
          // Fallback to regular submission
          fallbackSubmission(timeTaken, autoSubmit);
        });
    } else {
      // Fallback submission
      fallbackSubmission(timeTaken, autoSubmit);
    }
  };

  /**
   * Fallback submission method
   */
  function fallbackSubmission(timeTaken, autoSubmit) {
    const submitData = {
      action: "mcqhome_submit_assessment",
      nonce: window.assessmentData.nonce,
      mcq_set_id: window.assessmentData.setId,
      answers: assessmentState.answers,
      time_taken: timeTaken,
      auto_submit: autoSubmit,
    };

    $.ajax({
      url: window.assessmentData.ajaxUrl,
      type: "POST",
      data: submitData,
      success: function (response) {
        if (response.success) {
          showNotification("Assessment submitted successfully!", "success");

          // Redirect to results page
          setTimeout(function () {
            window.location.href = response.data.redirect_url || "/dashboard/";
          }, 1500);
        } else {
          handleSubmissionError(response.data);
        }
      },
      error: function () {
        handleSubmissionError("Network error occurred. Please try again.");
      },
    });
  }

  /**
   * Handle submission error
   */
  function handleSubmissionError(error) {
    assessmentState.isSubmitting = false;

    // Re-enable form elements
    $("input, button").prop("disabled", false);
    $(".submit-btn").removeClass("loading").text("Submit Assessment");

    // Show error message
    showNotification("Submission failed: " + error, "error");

    // Restart timer if it was running
    if (
      window.assessmentData.timeLimit > 0 &&
      assessmentState.timeRemaining > 0
    ) {
      initializeTimer();
    }
  }

  /**
   * Show notification
   */
  function showNotification(message, type = "info") {
    let notification = $(".assessment-notification");

    if (notification.length === 0) {
      notification = $(`
                <div class="assessment-notification fixed top-4 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-lg text-white font-medium z-50 opacity-0 transition-all duration-300">
                </div>
            `).appendTo("body");
    }

    // Set message and type
    notification
      .text(message)
      .removeClass("bg-blue-500 bg-green-500 bg-yellow-500 bg-red-500");

    switch (type) {
      case "success":
        notification.addClass("bg-green-500");
        break;
      case "warning":
        notification.addClass("bg-yellow-500");
        break;
      case "error":
        notification.addClass("bg-red-500");
        break;
      default:
        notification.addClass("bg-blue-500");
    }

    // Show notification
    notification.css("opacity", "1");

    // Hide after delay
    setTimeout(function () {
      notification.css("opacity", "0");
    }, 4000);
  }

  /**
   * Utility function to format time
   */
  function formatTime(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    if (hours > 0) {
      return `${hours}:${minutes.toString().padStart(2, "0")}:${secs
        .toString()
        .padStart(2, "0")}`;
    } else {
      return `${minutes}:${secs.toString().padStart(2, "0")}`;
    }
  }

  /**
   * Update question status on server
   */
  function updateQuestionStatusOnServer(questionNumber, status, answer = null) {
    const data = {
      action: "mcqhome_update_question_status",
      nonce: window.assessmentData.nonce,
      set_id: window.assessmentData.setId,
      question_number: questionNumber,
      status: status,
    };

    if (answer) {
      data.answer = answer;
    }

    $.ajax({
      url: window.assessmentData.ajaxUrl,
      type: "POST",
      data: data,
      success: function (response) {
        if (response.success) {
          console.log(
            `Question ${questionNumber + 1} status updated: ${status}`
          );
        } else {
          console.error("Failed to update question status:", response.data);
        }
      },
      error: function () {
        console.error("Failed to update question status on server");
      },
    });
  }

  /**
   * Initialize navigation panel integration
   */
  function initializeNavigationPanelIntegration() {
    // Listen for navigation panel events
    document.addEventListener("questionNavigationChange", function (e) {
      const { questionNumber, previousQuestion } = e.detail;

      // Update assessment state
      assessmentState.currentQuestion = questionNumber;

      // Update UI
      updateQuestionNavigation();
      updateNavigationControls();

      console.log(
        `Navigation: Question ${previousQuestion + 1} → ${questionNumber + 1}`
      );
    });

    // Initialize navigation panel if container exists
    const navContainer = document.querySelector(".question-navigation-panel");
    if (navContainer && typeof window.QuestionNavigationPanel !== "undefined") {
      window.questionNavigationPanel = new window.QuestionNavigationPanel(
        window.assessmentData.config || {},
        window.assessmentData.progress || {}
      );
    }
  }

  /**
   * Debug function
   */
  window.getAssessmentState = function () {
    return assessmentState;
  };
})(jQuery);
