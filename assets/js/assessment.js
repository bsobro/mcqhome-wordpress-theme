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

    // Update UI
    updateProgressIndicator();
    updateQuestionNavigation();

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

    console.log("Mobile features initialized");
  }

  /**
   * Initialize touch gestures for navigation
   */
  function initializeTouchGestures() {
    let startX = 0;
    let startY = 0;
    let endX = 0;
    let endY = 0;

    const questionContainer = document.querySelector(".question-display-area");
    if (!questionContainer) return;

    questionContainer.addEventListener(
      "touchstart",
      function (e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
      },
      { passive: true }
    );

    questionContainer.addEventListener(
      "touchend",
      function (e) {
        endX = e.changedTouches[0].clientX;
        endY = e.changedTouches[0].clientY;
        handleSwipeGesture();
      },
      { passive: true }
    );

    function handleSwipeGesture() {
      const deltaX = endX - startX;
      const deltaY = endY - startY;
      const minSwipeDistance = 50;

      // Only handle horizontal swipes that are longer than vertical
      if (
        Math.abs(deltaX) > Math.abs(deltaY) &&
        Math.abs(deltaX) > minSwipeDistance
      ) {
        const currentIndex = assessmentState.currentQuestion;
        const totalQuestions = window.assessmentData.totalQuestions;

        if (deltaX > 0 && currentIndex > 0) {
          // Swipe right - go to previous question
          navigateToQuestion(currentIndex - 1);
        } else if (deltaX < 0 && currentIndex < totalQuestions - 1) {
          // Swipe left - go to next question
          navigateToQuestion(currentIndex + 1);
        }
      }
    }
  }

  /**
   * Initialize collapsible navigation panel for mobile
   */
  function initializeCollapsibleNavPanel() {
    const navPanel = $(".question-nav-panel");
    const navHeader = navPanel.find("h3");

    if (navHeader.length === 0) return;

    // Add click handler to toggle collapse
    navHeader.on("click touchend", function (e) {
      e.preventDefault();
      navPanel.toggleClass("collapsed");

      // Save state to localStorage
      const isCollapsed = navPanel.hasClass("collapsed");
      localStorage.setItem("mcq_nav_collapsed", isCollapsed);
    });

    // Restore previous state
    const wasCollapsed = localStorage.getItem("mcq_nav_collapsed") === "true";
    if (wasCollapsed) {
      navPanel.addClass("collapsed");
    }
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

    window.addEventListener("resize", function () {
      const currentHeight = window.innerHeight;
      const heightDifference = initialViewportHeight - currentHeight;

      // If height decreased significantly, keyboard is likely open
      if (heightDifference > 150) {
        document.body.classList.add("keyboard-open");

        // Scroll to focused element
        const focusedElement = document.activeElement;
        if (focusedElement && focusedElement.tagName === "INPUT") {
          setTimeout(() => {
            focusedElement.scrollIntoView({
              behavior: "smooth",
              block: "center",
            });
          }, 300);
        }
      } else {
        document.body.classList.remove("keyboard-open");
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
      }, 500);
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

    if (displayFormat === "next_next") {
      const currentSlide = $(".question-slide.active");
      const targetSlide = $(
        `.question-slide[data-question="${questionIndex}"]`
      );

      if (isMobile) {
        // Enhanced mobile transition
        const direction =
          questionIndex > assessmentState.currentQuestion ? "left" : "right";

        // Add transition classes
        currentSlide.addClass(`slide-out-${direction}`);
        targetSlide.addClass(
          `slide-in-${direction === "left" ? "right" : "left"}`
        );

        setTimeout(() => {
          currentSlide
            .removeClass("active slide-out-left slide-out-right")
            .addClass("hidden");
          targetSlide
            .removeClass("hidden slide-in-left slide-in-right")
            .addClass("active");
        }, 300);
      } else {
        // Standard transition for desktop
        currentSlide.removeClass("active").addClass("hidden");
        targetSlide.removeClass("hidden").addClass("active");
      }

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

    // Provide haptic feedback on mobile (if supported)
    if (isMobile && "vibrate" in navigator) {
      navigator.vibrate(50);
    }
  };

  /**
   * Save answer for a question
   */
  window.saveAnswer = function (questionIndex, selectedAnswer) {
    // Update state
    assessmentState.answers[questionIndex] = selectedAnswer;

    // Update UI
    updateOptionSelection(questionIndex, selectedAnswer);
    updateQuestionNavigation();
    updateProgressIndicator();

    // Schedule auto-save
    scheduleAutoSave();

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
    $(".question-nav-btn").each(function () {
      const questionIndex = parseInt($(this).data("question"));
      const isAnswered = assessmentState.answers.hasOwnProperty(questionIndex);
      const isCurrent = questionIndex === assessmentState.currentQuestion;

      $(this).removeClass(
        "bg-blue-500 bg-green-500 bg-gray-200 text-white text-gray-700 ring-2 ring-blue-300"
      );

      if (isCurrent) {
        $(this).addClass("bg-blue-500 text-white ring-2 ring-blue-300 current");
      } else if (isAnswered) {
        $(this).addClass("bg-green-500 text-white");
      } else {
        $(this).addClass("bg-gray-200 text-gray-700");
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

    // Update progress bar
    $(".progress-fill").css("width", progressPercentage + "%");

    // Update progress text
    $("#progress-text").text(`${answeredCount} of ${totalQuestions} completed`);
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
    const data = {
      action: "mcqhome_save_assessment_progress",
      nonce: window.assessmentData.nonce,
      set_id: window.assessmentData.setId,
      current_question: assessmentState.currentQuestion,
      answers: assessmentState.answers,
      time_taken: Math.floor((Date.now() - assessmentState.startTime) / 1000),
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

    // Submit data
    const submitData = {
      action: "mcqhome_submit_assessment",
      nonce: window.assessmentData.nonce,
      set_id: window.assessmentData.setId,
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
            window.location.href = response.data.redirect_url;
          }, 1500);
        } else {
          handleSubmissionError(response.data);
        }
      },
      error: function () {
        handleSubmissionError("Network error occurred. Please try again.");
      },
    });
  };

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
   * Debug function
   */
  window.getAssessmentState = function () {
    return assessmentState;
  };
})(jQuery);
