/**
 * Question Navigation Panel System
 * MCQHome Theme - Task 9 Implementation
 *
 * Handles navigation panel with section-aware rendering and color-coded status indicators
 */

(function ($) {
  "use strict";

  /**
   * Question Navigation Panel Class
   */
  class QuestionNavigationPanel {
    constructor(config, progress) {
      this.config = config;
      this.progress = progress;
      this.currentQuestion = progress.current_question || 0;
      this.container = document.querySelector(".question-navigation-panel");

      if (!this.container) {
        console.error("Question navigation panel container not found");
        return;
      }

      this.init();
    }

    /**
     * Initialize the navigation panel
     */
    init() {
      this.render();
      this.bindEvents();
      this.updateCurrentIndicator();

      // Initialize progress tracking
      this.initializeProgressTracking();

      // Initialize mobile features if needed
      if (this.isMobile()) {
        this.initializeMobileFeatures();
      }
    }

    /**
     * Initialize progress tracking features
     */
    initializeProgressTracking() {
      // Update section progress indicators
      this.updateSectionProgress();

      // Set up real-time progress monitoring
      this.setupProgressMonitoring();

      // Initialize progress summary
      this.updateProgressSummaryAnimated();
    }

    /**
     * Set up real-time progress monitoring
     */
    setupProgressMonitoring() {
      // Listen for answer changes from the assessment interface
      document.addEventListener("mcq-answer-changed", (e) => {
        const { questionNumber, answer } = e.detail;
        this.markQuestionAnswered(questionNumber, answer);
      });

      // Listen for question skips
      document.addEventListener("mcq-question-skipped", (e) => {
        const { questionNumber } = e.detail;
        this.markQuestionSkipped(questionNumber);
      });

      // Listen for answer clears
      document.addEventListener("mcq-answer-cleared", (e) => {
        const { questionNumber } = e.detail;
        this.clearQuestionAnswer(questionNumber);
      });

      // Set up periodic progress sync (every 30 seconds)
      this.progressSyncInterval = setInterval(() => {
        this.syncProgressWithServer();
      }, 30000);
    }

    /**
     * Render the complete navigation panel
     */
    render() {
      const html = this.generateHTML();
      this.container.innerHTML = html;
    }

    /**
     * Generate HTML for navigation panel
     */
    generateHTML() {
      let html = '<div class="navigation-header">';
      html +=
        "<h3>" + (mcqhome_l10n?.question_navigation || "Questions") + "</h3>";
      html += this.generateProgressSummary();
      html += "</div>";

      html += '<div class="question-navigation-content">';

      if (
        this.config.sections_enabled &&
        this.config.sections &&
        this.config.sections.length > 0
      ) {
        html += this.generateSectionedNavigation();
      } else {
        html += this.generateSimpleNavigation();
      }

      html += "</div>";
      html += this.generateLegend();

      return html;
    }

    /**
     * Generate progress summary
     */
    generateProgressSummary() {
      const attemptedCount = this.getAttemptedCount();
      const skippedCount = this.getSkippedCount();
      const remainingCount = this.getRemainingCount();

      let html = '<div class="progress-summary">';
      html +=
        '<span class="attempted">' +
        attemptedCount +
        " " +
        (mcqhome_l10n?.attempted || "Attempted") +
        "</span>";

      if (skippedCount > 0) {
        html +=
          '<span class="skipped">' +
          skippedCount +
          " " +
          (mcqhome_l10n?.skipped || "Skipped") +
          "</span>";
      }

      html +=
        '<span class="remaining">' +
        remainingCount +
        " " +
        (mcqhome_l10n?.remaining || "Remaining") +
        "</span>";
      html += "</div>";

      return html;
    }

    /**
     * Generate sectioned navigation
     */
    generateSectionedNavigation() {
      let html = "";
      let globalIndex = 0;

      this.config.sections.forEach((section) => {
        if (
          !this.config.questions[section.id] ||
          this.config.questions[section.id].length === 0
        ) {
          return;
        }

        const sectionQuestions = this.config.questions[section.id];

        html +=
          '<div class="section-nav" data-section="' +
          this.escapeHtml(section.id) +
          '">';
        html +=
          '<h4 class="section-nav-title">' +
          this.escapeHtml(section.name) +
          "</h4>";

        if (section.description) {
          html +=
            '<p class="section-nav-description">' +
            this.escapeHtml(section.description) +
            "</p>";
        }

        html += '<div class="question-numbers">';

        sectionQuestions.forEach((question, localIndex) => {
          const questionNumber = globalIndex + localIndex;
          const status = this.getQuestionStatus(questionNumber);
          const isCurrent = questionNumber === this.currentQuestion;

          html +=
            '<button class="question-number ' +
            status +
            (isCurrent ? " current" : "") +
            '" ';
          html += 'data-question="' + questionNumber + '" ';
          html += 'data-section="' + this.escapeHtml(section.id) + '" ';
          html +=
            'title="' + this.getQuestionTooltip(questionNumber, status) + '">';
          html += questionNumber + 1; // Display 1-based numbering
          html += "</button>";
        });

        html += "</div></div>";
        globalIndex += sectionQuestions.length;
      });

      return html;
    }

    /**
     * Generate simple (non-sectioned) navigation
     */
    generateSimpleNavigation() {
      let html = '<div class="question-numbers simple-grid">';

      for (let i = 0; i < this.config.total_questions; i++) {
        const status = this.getQuestionStatus(i);
        const isCurrent = i === this.currentQuestion;

        html +=
          '<button class="question-number ' +
          status +
          (isCurrent ? " current" : "") +
          '" ';
        html += 'data-question="' + i + '" ';
        html += 'title="' + this.getQuestionTooltip(i, status) + '">';
        html += i + 1; // Display 1-based numbering
        html += "</button>";
      }

      html += "</div>";
      return html;
    }

    /**
     * Generate legend for status indicators
     */
    generateLegend() {
      let html = '<div class="navigation-legend">';

      html += '<div class="legend-item">';
      html += '<div class="legend-color current"></div>';
      html += "<span>" + (mcqhome_l10n?.current || "Current") + "</span>";
      html += "</div>";

      html += '<div class="legend-item">';
      html += '<div class="legend-color attempted"></div>';
      html += "<span>" + (mcqhome_l10n?.answered || "Answered") + "</span>";
      html += "</div>";

      if (this.getSkippedCount() > 0) {
        html += '<div class="legend-item">';
        html += '<div class="legend-color skipped"></div>';
        html += "<span>" + (mcqhome_l10n?.skipped || "Skipped") + "</span>";
        html += "</div>";
      }

      html += '<div class="legend-item">';
      html += '<div class="legend-color unanswered"></div>';
      html +=
        "<span>" + (mcqhome_l10n?.unanswered || "Not Answered") + "</span>";
      html += "</div>";

      html += "</div>";
      return html;
    }

    /**
     * Bind event handlers
     */
    bindEvents() {
      // Question number click handlers
      this.container.addEventListener("click", (e) => {
        if (e.target.classList.contains("question-number")) {
          e.preventDefault();
          const questionNumber = parseInt(e.target.dataset.question);
          this.navigateToQuestion(questionNumber);
        }
      });

      // Touch event handlers for mobile
      if (this.isMobile()) {
        this.container.addEventListener("touchend", (e) => {
          if (e.target.classList.contains("question-number")) {
            e.preventDefault();
            const questionNumber = parseInt(e.target.dataset.question);
            this.navigateToQuestion(questionNumber);
          }
        });
      }

      // Keyboard navigation
      this.container.addEventListener("keydown", (e) => {
        if (e.target.classList.contains("question-number")) {
          this.handleKeyboardNavigation(e);
        }
      });
    }

    /**
     * Handle keyboard navigation within the panel
     */
    handleKeyboardNavigation(e) {
      const currentBtn = e.target;
      const questionNumber = parseInt(currentBtn.dataset.question);

      switch (e.key) {
        case "Enter":
        case " ":
          e.preventDefault();
          this.navigateToQuestion(questionNumber);
          break;

        case "ArrowRight":
        case "ArrowDown":
          e.preventDefault();
          this.focusNextQuestion(questionNumber);
          break;

        case "ArrowLeft":
        case "ArrowUp":
          e.preventDefault();
          this.focusPreviousQuestion(questionNumber);
          break;

        case "Home":
          e.preventDefault();
          this.focusQuestion(0);
          break;

        case "End":
          e.preventDefault();
          this.focusQuestion(this.config.total_questions - 1);
          break;
      }
    }

    /**
     * Focus next question button
     */
    focusNextQuestion(currentNumber) {
      const nextNumber = Math.min(
        currentNumber + 1,
        this.config.total_questions - 1
      );
      this.focusQuestion(nextNumber);
    }

    /**
     * Focus previous question button
     */
    focusPreviousQuestion(currentNumber) {
      const prevNumber = Math.max(currentNumber - 1, 0);
      this.focusQuestion(prevNumber);
    }

    /**
     * Focus specific question button
     */
    focusQuestion(questionNumber) {
      const btn = this.container.querySelector(
        `[data-question="${questionNumber}"]`
      );
      if (btn) {
        btn.focus();
      }
    }

    /**
     * Navigate to specific question
     */
    navigateToQuestion(questionNumber) {
      if (questionNumber < 0 || questionNumber >= this.config.total_questions) {
        return;
      }

      const displayFormat = this.config.display_format;
      const isMobile = this.isMobile();

      if (displayFormat === "next_next") {
        // For Next-Next format, update the current question display
        this.updateCurrentQuestionDisplay(questionNumber);

        // Auto-collapse navigation panel on mobile after selection
        if (isMobile) {
          this.collapseOnMobile();
        }
      } else {
        // Single page format - smooth scroll to question
        this.scrollToQuestion(questionNumber);
      }

      // Update state
      this.currentQuestion = questionNumber;
      this.progress.current_question = questionNumber;

      // Update UI
      this.updateCurrentIndicator();
      this.updateProgressSummary();

      // Trigger custom event
      this.triggerNavigationEvent(questionNumber);

      // Provide haptic feedback on mobile (if supported)
      if (isMobile && "vibrate" in navigator) {
        navigator.vibrate(50);
      }

      // Update global assessment state if available
      if (window.assessmentState) {
        window.assessmentState.currentQuestion = questionNumber;
      }

      // Call global navigation function if available
      if (typeof window.navigateToQuestion === "function") {
        window.navigateToQuestion(questionNumber);
      }
    }

    /**
     * Update current question display for Next-Next format
     */
    updateCurrentQuestionDisplay(questionNumber) {
      // This will trigger an AJAX request to load the question content
      if (typeof window.updateCurrentQuestionDisplay === "function") {
        window.updateCurrentQuestionDisplay(questionNumber);
      } else {
        // Fallback: trigger custom event
        const event = new CustomEvent("questionNavigate", {
          detail: { questionNumber: questionNumber },
        });
        document.dispatchEvent(event);
      }
    }

    /**
     * Scroll to question in single page format
     */
    scrollToQuestion(questionNumber) {
      const targetQuestion = document.querySelector(
        `[data-question="${questionNumber}"], [data-question-number="${questionNumber}"]`
      );

      if (targetQuestion) {
        const offset = this.isMobile() ? 80 : 100;
        const duration = this.isMobile() ? 300 : 500;

        // Use smooth scrolling
        targetQuestion.scrollIntoView({
          behavior: "smooth",
          block: "center",
        });

        // Highlight the question temporarily
        this.highlightQuestion(targetQuestion);
      }
    }

    /**
     * Highlight question temporarily
     */
    highlightQuestion(element) {
      element.classList.add("highlighted");
      setTimeout(() => {
        element.classList.remove("highlighted");
      }, 2000);
    }

    /**
     * Collapse navigation panel on mobile
     */
    collapseOnMobile() {
      if (this.container.classList.contains("collapsible")) {
        this.container.classList.add("collapsed");

        // Save state to localStorage
        localStorage.setItem("mcq_nav_collapsed", "true");
      }
    }

    /**
     * Update status of a specific question with real-time visual feedback
     */
    updateQuestionStatus(questionNumber, status) {
      const btn = this.container.querySelector(
        `[data-question="${questionNumber}"]`
      );
      if (btn) {
        // Add transition effect for visual feedback
        btn.classList.add("status-updating");

        // Remove all status classes
        btn.classList.remove("attempted", "skipped", "unanswered");

        // Add new status class with enhanced animation
        setTimeout(() => {
          btn.classList.add(status);
          btn.classList.remove("status-updating");

          // Add specific status change animation
          if (status === "attempted") {
            btn.classList.add("status-change-answered");
            setTimeout(
              () => btn.classList.remove("status-change-answered"),
              800
            );
          } else if (status === "skipped") {
            btn.classList.add("status-change-skipped");
            setTimeout(
              () => btn.classList.remove("status-change-skipped"),
              800
            );
          }

          // Add pulse effect for status change
          btn.classList.add("status-changed");
          setTimeout(() => {
            btn.classList.remove("status-changed");
          }, 600);
        }, 100);

        // Update tooltip
        btn.title = this.getQuestionTooltip(questionNumber, status);

        // Update progress in the progress object
        if (status === "attempted") {
          if (!this.progress.answers) this.progress.answers = {};
          // Mark as answered (actual answer will be set elsewhere)
          if (!this.progress.answers.hasOwnProperty(questionNumber)) {
            this.progress.answers[questionNumber] = true;
          }
          // Remove from skipped if it was skipped before
          if (this.progress.skipped) {
            const skippedIndex = this.progress.skipped.indexOf(questionNumber);
            if (skippedIndex > -1) {
              this.progress.skipped.splice(skippedIndex, 1);
            }
          }
        } else if (status === "skipped") {
          if (!this.progress.skipped) this.progress.skipped = [];
          if (!this.progress.skipped.includes(questionNumber)) {
            this.progress.skipped.push(questionNumber);
          }
          // Remove from answers if it was answered before
          if (
            this.progress.answers &&
            this.progress.answers.hasOwnProperty(questionNumber)
          ) {
            delete this.progress.answers[questionNumber];
          }
        } else if (status === "unanswered") {
          // Clear both answered and skipped status
          if (
            this.progress.answers &&
            this.progress.answers.hasOwnProperty(questionNumber)
          ) {
            delete this.progress.answers[questionNumber];
          }
          if (this.progress.skipped) {
            const skippedIndex = this.progress.skipped.indexOf(questionNumber);
            if (skippedIndex > -1) {
              this.progress.skipped.splice(skippedIndex, 1);
            }
          }
        }
      }

      // Update progress summary with animation
      this.updateProgressSummaryAnimated();

      // Update section progress indicators with animation
      this.updateSectionProgressAnimated();

      // Update legend if needed
      this.updateLegend();

      // Trigger progress change event
      this.triggerProgressChangeEvent(questionNumber, status);

      // Check for completion milestones
      this.checkProgressMilestones();
    }

    /**
     * Update current question indicator
     */
    updateCurrentIndicator() {
      // Remove current class from all buttons
      this.container
        .querySelectorAll(".question-number.current")
        .forEach((btn) => {
          btn.classList.remove("current");
        });

      // Add current class to active question
      const currentBtn = this.container.querySelector(
        `[data-question="${this.currentQuestion}"]`
      );
      if (currentBtn) {
        currentBtn.classList.add("current");

        // Ensure it's visible (scroll into view if needed)
        this.ensureQuestionVisible(currentBtn);
      }
    }

    /**
     * Ensure question button is visible in scrollable container
     */
    ensureQuestionVisible(button) {
      const container = this.container.querySelector(
        ".question-navigation-content"
      );
      if (!container) return;

      const containerRect = container.getBoundingClientRect();
      const buttonRect = button.getBoundingClientRect();

      if (
        buttonRect.top < containerRect.top ||
        buttonRect.bottom > containerRect.bottom
      ) {
        button.scrollIntoView({
          behavior: "smooth",
          block: "nearest",
        });
      }
    }

    /**
     * Update progress summary with animation
     */
    updateProgressSummary() {
      const summaryElement = this.container.querySelector(".progress-summary");
      if (summaryElement) {
        summaryElement.innerHTML = this.generateProgressSummary().replace(
          /<div[^>]*>|<\/div>/g,
          ""
        );
      }
    }

    /**
     * Update progress summary with animated transitions
     */
    updateProgressSummaryAnimated() {
      const summaryElement = this.container.querySelector(".progress-summary");
      if (summaryElement) {
        // Add updating class for animation
        summaryElement.classList.add("updating");

        // Update content
        const newContent = this.generateProgressSummary().replace(
          /<div[^>]*>|<\/div>/g,
          ""
        );

        // Animate the change
        setTimeout(() => {
          summaryElement.innerHTML = newContent;
          summaryElement.classList.remove("updating");
          summaryElement.classList.add("updated");

          setTimeout(() => {
            summaryElement.classList.remove("updated");
          }, 300);
        }, 150);
      }
    }

    /**
     * Update legend visibility
     */
    updateLegend() {
      const legendElement = this.container.querySelector(".navigation-legend");
      if (legendElement) {
        legendElement.innerHTML = this.generateLegend().replace(
          /<div[^>]*class="navigation-legend"[^>]*>|<\/div>$/g,
          ""
        );
      }
    }

    /**
     * Get question status
     */
    getQuestionStatus(questionNumber) {
      if (
        this.progress.answers &&
        this.progress.answers.hasOwnProperty(questionNumber)
      ) {
        return "attempted";
      } else if (
        this.progress.skipped &&
        this.progress.skipped.includes(questionNumber)
      ) {
        return "skipped";
      } else {
        return "unanswered";
      }
    }

    /**
     * Get question tooltip text
     */
    getQuestionTooltip(questionNumber, status) {
      const questionNum = questionNumber + 1;

      switch (status) {
        case "attempted":
          return `Question ${questionNum} - Answered`;
        case "skipped":
          return `Question ${questionNum} - Skipped`;
        case "current":
          return `Question ${questionNum} - Current`;
        default:
          return `Question ${questionNum} - Not answered`;
      }
    }

    /**
     * Get attempted questions count
     */
    getAttemptedCount() {
      return this.progress.answers
        ? Object.keys(this.progress.answers).length
        : 0;
    }

    /**
     * Get skipped questions count
     */
    getSkippedCount() {
      return this.progress.skipped ? this.progress.skipped.length : 0;
    }

    /**
     * Get remaining questions count
     */
    getRemainingCount() {
      return this.config.total_questions - this.getAttemptedCount();
    }

    /**
     * Initialize mobile-specific features
     */
    initializeMobileFeatures() {
      // Add collapsible functionality
      this.initializeCollapsible();

      // Add touch-friendly enhancements
      this.enhanceTouchTargets();

      // Initialize swipe gestures for navigation
      this.initializeSwipeGestures();
    }

    /**
     * Initialize collapsible navigation panel for mobile
     */
    initializeCollapsible() {
      const header = this.container.querySelector(".navigation-header h3");
      if (!header) return;

      // Add collapsible class and toggle icon
      this.container.classList.add("collapsible");
      header.innerHTML += '<span class="toggle-icon">▼</span>';

      // Add click handler to toggle collapse
      header.addEventListener("click", (e) => {
        e.preventDefault();
        this.toggleCollapse();
      });

      header.addEventListener("touchend", (e) => {
        e.preventDefault();
        this.toggleCollapse();
      });

      // Restore previous state
      const wasCollapsed = localStorage.getItem("mcq_nav_collapsed") === "true";
      if (wasCollapsed) {
        this.container.classList.add("collapsed");
      }
    }

    /**
     * Toggle collapse state
     */
    toggleCollapse() {
      this.container.classList.toggle("collapsed");

      // Update toggle icon
      const icon = this.container.querySelector(".toggle-icon");
      if (icon) {
        icon.textContent = this.container.classList.contains("collapsed")
          ? "▶"
          : "▼";
      }

      // Save state to localStorage
      const isCollapsed = this.container.classList.contains("collapsed");
      localStorage.setItem("mcq_nav_collapsed", isCollapsed.toString());
    }

    /**
     * Enhance touch targets for mobile
     */
    enhanceTouchTargets() {
      // Add touch-action CSS to prevent zoom
      this.container.querySelectorAll(".question-number").forEach((btn) => {
        btn.style.touchAction = "manipulation";
      });
    }

    /**
     * Initialize swipe gestures for navigation panel
     */
    initializeSwipeGestures() {
      let startY = 0;
      let endY = 0;

      this.container.addEventListener(
        "touchstart",
        (e) => {
          startY = e.touches[0].clientY;
        },
        { passive: true }
      );

      this.container.addEventListener(
        "touchend",
        (e) => {
          endY = e.changedTouches[0].clientY;
          this.handleSwipeGesture(startY, endY);
        },
        { passive: true }
      );
    }

    /**
     * Handle swipe gesture
     */
    handleSwipeGesture(startY, endY) {
      const deltaY = endY - startY;
      const minSwipeDistance = 50;

      if (Math.abs(deltaY) > minSwipeDistance) {
        if (deltaY > 0) {
          // Swipe down - expand if collapsed
          if (this.container.classList.contains("collapsed")) {
            this.toggleCollapse();
          }
        } else {
          // Swipe up - collapse if expanded
          if (!this.container.classList.contains("collapsed")) {
            this.toggleCollapse();
          }
        }
      }
    }

    /**
     * Update section progress indicators for sectioned assessments
     */
    updateSectionProgress() {
      if (!this.config.sections_enabled || !this.config.sections) {
        return;
      }

      this.config.sections.forEach((section) => {
        const sectionElement = this.container.querySelector(
          `[data-section="${section.id}"]`
        );
        if (!sectionElement) return;

        const sectionQuestions = this.config.questions[section.id] || [];
        const sectionStartIndex = this.getSectionStartIndex(section.id);

        let attempted = 0;
        let skipped = 0;
        let total = sectionQuestions.length;

        // Count progress in this section
        for (let i = 0; i < total; i++) {
          const globalIndex = sectionStartIndex + i;
          if (
            this.progress.answers &&
            this.progress.answers.hasOwnProperty(globalIndex)
          ) {
            attempted++;
          } else if (
            this.progress.skipped &&
            this.progress.skipped.includes(globalIndex)
          ) {
            skipped++;
          }
        }

        // Update or create section progress indicator
        let progressIndicator = sectionElement.querySelector(
          ".section-progress-indicator"
        );
        if (!progressIndicator) {
          progressIndicator = document.createElement("div");
          progressIndicator.className = "section-progress-indicator";
          const titleElement =
            sectionElement.querySelector(".section-nav-title");
          if (titleElement) {
            titleElement.appendChild(progressIndicator);
          }
        }

        const percentage =
          total > 0 ? Math.round((attempted / total) * 100) : 0;
        const remaining = total - attempted - skipped;

        progressIndicator.innerHTML = `
          <div class="section-progress-bar">
            <div class="section-progress-fill" style="width: ${percentage}%"></div>
          </div>
          <div class="section-progress-text">
            <span class="section-attempted">${attempted}</span>/<span class="section-total">${total}</span>
            ${
              skipped > 0
                ? `<span class="section-skipped">(${skipped} skipped)</span>`
                : ""
            }
          </div>
        `;

        // Add completion status class with animation
        const wasComplete =
          sectionElement.classList.contains("section-complete");
        sectionElement.classList.remove(
          "section-complete",
          "section-partial",
          "section-empty"
        );

        if (attempted === total) {
          sectionElement.classList.add("section-complete");
          // Trigger completion animation if newly completed
          if (!wasComplete) {
            sectionElement.classList.add("section-just-completed");
            setTimeout(() => {
              sectionElement.classList.remove("section-just-completed");
            }, 1000);
          }
        } else if (attempted > 0 || skipped > 0) {
          sectionElement.classList.add("section-partial");
        } else {
          sectionElement.classList.add("section-empty");
        }
      });
    }

    /**
     * Update section progress indicators with enhanced animations
     */
    updateSectionProgressAnimated() {
      if (!this.config.sections_enabled || !this.config.sections) {
        return;
      }

      this.config.sections.forEach((section) => {
        const sectionElement = this.container.querySelector(
          `[data-section="${section.id}"]`
        );
        if (!sectionElement) return;

        const sectionQuestions = this.config.questions[section.id] || [];
        const sectionStartIndex = this.getSectionStartIndex(section.id);

        let attempted = 0;
        let skipped = 0;
        let total = sectionQuestions.length;

        // Count progress in this section
        for (let i = 0; i < total; i++) {
          const globalIndex = sectionStartIndex + i;
          if (
            this.progress.answers &&
            this.progress.answers.hasOwnProperty(globalIndex)
          ) {
            attempted++;
          } else if (
            this.progress.skipped &&
            this.progress.skipped.includes(globalIndex)
          ) {
            skipped++;
          }
        }

        // Update or create section progress indicator
        let progressIndicator = sectionElement.querySelector(
          ".section-progress-indicator"
        );
        if (!progressIndicator) {
          progressIndicator = document.createElement("div");
          progressIndicator.className = "section-progress-indicator";
          const titleElement =
            sectionElement.querySelector(".section-nav-title");
          if (titleElement) {
            titleElement.appendChild(progressIndicator);
          }
        }

        const percentage =
          total > 0 ? Math.round((attempted / total) * 100) : 0;
        const remaining = total - attempted - skipped;

        // Get current progress bar for animation
        const currentProgressFill = progressIndicator.querySelector(
          ".section-progress-fill"
        );
        const currentPercentage = currentProgressFill
          ? parseInt(currentProgressFill.style.width) || 0
          : 0;

        // Update content
        progressIndicator.innerHTML = `
          <div class="section-progress-bar">
            <div class="section-progress-fill progress-updated" style="width: ${percentage}%"></div>
          </div>
          <div class="section-progress-text">
            <span class="section-attempted">${attempted}</span>/<span class="section-total">${total}</span>
            ${
              skipped > 0
                ? `<span class="section-skipped">(${skipped} skipped)</span>`
                : ""
            }
          </div>
        `;

        // Add progress animation if percentage increased
        if (percentage > currentPercentage) {
          const newProgressFill = progressIndicator.querySelector(
            ".section-progress-fill"
          );
          if (newProgressFill) {
            newProgressFill.classList.add("progress-updated");
            setTimeout(() => {
              newProgressFill.classList.remove("progress-updated");
            }, 600);
          }
        }

        // Add completion status class with animation
        const wasComplete =
          sectionElement.classList.contains("section-complete");
        sectionElement.classList.remove(
          "section-complete",
          "section-partial",
          "section-empty"
        );

        if (attempted === total) {
          sectionElement.classList.add("section-complete");
          // Trigger completion animation if newly completed
          if (!wasComplete) {
            sectionElement.classList.add("section-just-completed");
            setTimeout(() => {
              sectionElement.classList.remove("section-just-completed");
            }, 1000);

            // Show completion notification
            this.showSectionCompletionNotification(section.name);
          }
        } else if (attempted > 0 || skipped > 0) {
          sectionElement.classList.add("section-partial");
        } else {
          sectionElement.classList.add("section-empty");
        }
      });
    }

    /**
     * Get section start index for progress calculation
     */
    getSectionStartIndex(targetSectionId) {
      let index = 0;
      for (const sectionId of Object.keys(this.config.questions)) {
        if (sectionId === targetSectionId) {
          break;
        }
        index += this.config.questions[sectionId].length;
      }
      return index;
    }

    /**
     * Trigger progress change event
     */
    triggerProgressChangeEvent(questionNumber, status) {
      const event = new CustomEvent("questionProgressChange", {
        detail: {
          questionNumber: questionNumber,
          status: status,
          progress: this.getProgressStats(),
          sectionProgress: this.getSectionProgressStats(),
        },
      });

      document.dispatchEvent(event);
    }

    /**
     * Get overall progress statistics
     */
    getProgressStats() {
      const attempted = this.getAttemptedCount();
      const skipped = this.getSkippedCount();
      const total = this.config.total_questions;
      const remaining = total - attempted;
      const percentage = total > 0 ? Math.round((attempted / total) * 100) : 0;

      return {
        attempted,
        skipped,
        remaining,
        total,
        percentage,
      };
    }

    /**
     * Get section-wise progress statistics
     */
    getSectionProgressStats() {
      if (!this.config.sections_enabled || !this.config.sections) {
        return null;
      }

      const sectionStats = {};

      this.config.sections.forEach((section) => {
        const sectionQuestions = this.config.questions[section.id] || [];
        const sectionStartIndex = this.getSectionStartIndex(section.id);

        let attempted = 0;
        let skipped = 0;
        const total = sectionQuestions.length;

        for (let i = 0; i < total; i++) {
          const globalIndex = sectionStartIndex + i;
          if (
            this.progress.answers &&
            this.progress.answers.hasOwnProperty(globalIndex)
          ) {
            attempted++;
          } else if (
            this.progress.skipped &&
            this.progress.skipped.includes(globalIndex)
          ) {
            skipped++;
          }
        }

        const percentage =
          total > 0 ? Math.round((attempted / total) * 100) : 0;
        const remaining = total - attempted - skipped;

        sectionStats[section.id] = {
          name: section.name,
          attempted,
          skipped,
          remaining,
          total,
          percentage,
        };
      });

      return sectionStats;
    }

    /**
     * Trigger navigation event
     */
    triggerNavigationEvent(questionNumber) {
      const event = new CustomEvent("questionNavigationChange", {
        detail: {
          questionNumber: questionNumber,
          previousQuestion: this.currentQuestion,
          totalQuestions: this.config.total_questions,
          progress: this.progress,
        },
      });

      document.dispatchEvent(event);
    }

    /**
     * Check if device is mobile
     */
    isMobile() {
      return (
        window.innerWidth <= 768 ||
        /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
          navigator.userAgent
        )
      );
    }

    /**
     * Escape HTML for safe insertion
     */
    escapeHtml(text) {
      const div = document.createElement("div");
      div.textContent = text;
      return div.innerHTML;
    }

    /**
     * Mark question as answered with visual feedback
     */
    markQuestionAnswered(questionNumber, answer) {
      // Update progress data
      if (!this.progress.answers) this.progress.answers = {};
      this.progress.answers[questionNumber] = answer;

      // Update visual status
      this.updateQuestionStatus(questionNumber, "attempted");

      // Save progress to server
      this.saveProgressToServer();
    }

    /**
     * Mark question as skipped with visual feedback
     */
    markQuestionSkipped(questionNumber) {
      // Update progress data
      if (!this.progress.skipped) this.progress.skipped = [];
      if (!this.progress.skipped.includes(questionNumber)) {
        this.progress.skipped.push(questionNumber);
      }

      // Remove from answers if previously answered
      if (this.progress.answers && this.progress.answers.hasOwnProperty(questionNumber)) {
        delete this.progress.answers[questionNumber];
      }

      // Update visual status
      this.updateQuestionStatus(questionNumber, "skipped");

      // Save progress to server
      this.saveProgressToServer();
    }

    /**
     * Clear question answer with visual feedback
     */
    clearQuestionAnswer(questionNumber) {
      // Remove from both answers and skipped
      if (this.progress.answers && this.progress.answers.hasOwnProperty(questionNumber)) {
        delete this.progress.answers[questionNumber];
      }
      if (this.progress.skipped) {
        const skippedIndex = this.progress.skipped.indexOf(questionNumber);
        if (skippedIndex > -1) {
          this.progress.skipped.splice(skippedIndex, 1);
        }
      }

      // Update visual status
      this.updateQuestionStatus(questionNumber, "unanswered");

      // Save progress to server
      this.saveProgressToServer();
    }

    /**
     * Check for progress milestones and provide feedback
     */
    checkProgressMilestones() {
      const stats = this.getProgressStats();
      const { attempted, total, percentage } = stats;

      // Check for milestone achievements
      if (percentage === 25 && !this.milestones?.quarter) {
        this.showProgressMilestone("25% Complete!", "You're making great progress!");
        if (!this.milestones) this.milestones = {};
        this.milestones.quarter = true;
      } else if (percentage === 50 && !this.milestones?.half) {
        this.showProgressMilestone("Halfway There!", "Keep up the excellent work!");
        if (!this.milestones) this.milestones = {};
        this.milestones.half = true;
      } else if (percentage === 75 && !this.milestones?.threeQuarter) {
        this.showProgressMilestone("75% Complete!", "Almost finished!");
        if (!this.milestones) this.milestones = {};
        this.milestones.threeQuarter = true;
      } else if (percentage === 100 && !this.milestones?.complete) {
        this.showProgressMilestone("All Questions Answered!", "Ready to submit your assessment!");
        if (!this.milestones) this.milestones = {};
        this.milestones.complete = true;
      }

      // Highlight unanswered questions if time is running low
      this.highlightUnansweredIfTimeRunningOut();
    }

    /**
     * Show progress milestone notification
     */
    showProgressMilestone(title, message) {
      // Create milestone notification
      const notification = document.createElement("div");
      notification.className = "progress-milestone-notification";
      notification.innerHTML = `
        <div class="milestone-icon">🎉</div>
        <div class="milestone-content">
          <div class="milestone-title">${title}</div>
          <div class="milestone-message">${message}</div>
        </div>
      `;

      // Add to container
      this.container.appendChild(notification);

      // Animate in
      setTimeout(() => {
        notification.classList.add("show");
      }, 100);

      // Remove after delay
      setTimeout(() => {
        notification.classList.remove("show");
        setTimeout(() => {
          if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
          }
        }, 300);
      }, 3000);

      // Trigger haptic feedback on mobile
      if (this.isMobile() && "vibrate" in navigator) {
        navigator.vibrate([100, 50, 100]);
      }
    }

    /**
     * Show section completion notification
     */
    showSectionCompletionNotification(sectionName) {
      const notification = document.createElement("div");
      notification.className = "section-completion-notification";
      notification.innerHTML = `
        <div class="completion-icon">✅</div>
        <div class="completion-content">
          <div class="completion-title">Section Complete!</div>
          <div class="completion-message">${sectionName} section finished</div>
        </div>
      `;

      // Add to container
      this.container.appendChild(notification);

      // Animate in
      setTimeout(() => {
        notification.classList.add("show");
      }, 100);

      // Remove after delay
      setTimeout(() => {
        notification.classList.remove("show");
        setTimeout(() => {
          if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
          }
        }, 300);
      }, 2500);

      // Trigger haptic feedback on mobile
      if (this.isMobile() && "vibrate" in navigator) {
        navigator.vibrate([200, 100, 200]);
      }
    }

    /**
     * Highlight unanswered questions when time is running out
     */
    highlightUnansweredIfTimeRunningOut() {
      // Check if time limit is set and get remaining time
      if (!this.config.time_limit || !window.assessmentTimer) {
        return;
      }

      const remainingMinutes = window.assessmentTimer.getRemainingMinutes?.() || 0;
      const totalQuestions = this.config.total_questions;
      const unansweredCount = this.getRemainingCount();

      // Highlight if less than 5 minutes remaining and unanswered questions exist
      if (remainingMinutes <= 5 && unansweredCount > 0) {
        this.container.querySelectorAll(".question-number.unanswered").forEach(btn => {
          btn.classList.add("time-warning");
        });

        // Show warning notification if not already shown
        if (!this.timeWarningShown) {
          this.showTimeWarningNotification(remainingMinutes, unansweredCount);
          this.timeWarningShown = true;
        }
      } else {
        // Remove warning highlighting
        this.container.querySelectorAll(".question-number.time-warning").forEach(btn => {
          btn.classList.remove("time-warning");
        });
      }
    }

    /**
     * Show time warning notification
     */
    showTimeWarningNotification(remainingMinutes, unansweredCount) {
      const notification = document.createElement("div");
      notification.className = "time-warning-notification";
      notification.innerHTML = `
        <div class="warning-icon">⏰</div>
        <div class="warning-content">
          <div class="warning-title">Time Running Out!</div>
          <div class="warning-message">${remainingMinutes} minutes left, ${unansweredCount} questions remaining</div>
        </div>
      `;

      // Add to container
      this.container.appendChild(notification);

      // Animate in
      setTimeout(() => {
        notification.classList.add("show");
      }, 100);

      // Remove after delay
      setTimeout(() => {
        notification.classList.remove("show");
        setTimeout(() => {
          if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
          }
        }, 300);
      }, 4000);

      // Trigger haptic feedback on mobile
      if (this.isMobile() && "vibrate" in navigator) {
        navigator.vibrate([300, 100, 300, 100, 300]);
      }
    }

    /**
     * Sync progress with server periodically
     */
    syncProgressWithServer() {
      if (!window.mcqhome_ajax || !this.config.mcq_set_id) {
        return;
      }

      const progressData = {
        action: 'mcqhome_save_progress',
        nonce: window.mcqhome_ajax.nonce,
        mcq_set_id: this.config.mcq_set_id,
        current_question: this.currentQuestion,
        answers: this.progress.answers || {},
        skipped: this.progress.skipped || [],
        progress_percentage: this.getProgressStats().percentage
      };

      fetch(window.mcqhome_ajax.ajax_url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(progressData)
      }).catch(error => {
        console.warn('Progress sync failed:', error);
      });
    }

    /**
     * Save progress to server immediately
     */
    saveProgressToServer() {
      // Debounce rapid saves
      if (this.saveTimeout) {
        clearTimeout(this.saveTimeout);
      }

      this.saveTimeout = setTimeout(() => {
        this.syncProgressWithServer();
      }, 1000);
    }

    /**
     * Cleanup method
     */
    destroy() {
      // Clear intervals and timeouts
      if (this.progressSyncInterval) {
        clearInterval(this.progressSyncInterval);
      }
      if (this.saveTimeout) {
        clearTimeout(this.saveTimeout);
      }

      // Remove event listeners
      document.removeEventListener("mcq-answer-changed", this.handleAnswerChanged);
      document.removeEventListener("mcq-question-skipped", this.handleQuestionSkipped);
      document.removeEventListener("mcq-answer-cleared", this.handleAnswerCleared);
    }
    }

    /**
     * Public method to update progress from external sources
     */
    updateProgress(newProgress) {
      this.progress = { ...this.progress, ...newProgress };
      this.updateProgressSummary();
      this.updateCurrentIndicator();
    }

    /**
     * Public method to mark question as answered with real-time feedback
     */
    markQuestionAnswered(questionNumber, answer) {
      if (!this.progress.answers) {
        this.progress.answers = {};
      }

      // Remove from skipped if it was skipped before
      if (
        this.progress.skipped &&
        this.progress.skipped.includes(questionNumber)
      ) {
        this.progress.skipped = this.progress.skipped.filter(
          (q) => q !== questionNumber
        );
      }

      this.progress.answers[questionNumber] = answer;
      this.updateQuestionStatus(questionNumber, "attempted");

      // Provide haptic feedback on mobile
      if (this.isMobile() && "vibrate" in navigator) {
        navigator.vibrate(30);
      }

      // Show success animation
      this.showStatusChangeAnimation(questionNumber, "answered");
    }

    /**
     * Public method to mark question as skipped with visual feedback
     */
    markQuestionSkipped(questionNumber) {
      if (!this.progress.skipped) {
        this.progress.skipped = [];
      }

      // Remove from answers if it was answered before
      if (
        this.progress.answers &&
        this.progress.answers.hasOwnProperty(questionNumber)
      ) {
        delete this.progress.answers[questionNumber];
      }

      if (!this.progress.skipped.includes(questionNumber)) {
        this.progress.skipped.push(questionNumber);
      }

      this.updateQuestionStatus(questionNumber, "skipped");

      // Show skip animation
      this.showStatusChangeAnimation(questionNumber, "skipped");
    }

    /**
     * Public method to clear question answer
     */
    clearQuestionAnswer(questionNumber) {
      // Remove from answers
      if (
        this.progress.answers &&
        this.progress.answers.hasOwnProperty(questionNumber)
      ) {
        delete this.progress.answers[questionNumber];
      }

      // Remove from skipped
      if (
        this.progress.skipped &&
        this.progress.skipped.includes(questionNumber)
      ) {
        this.progress.skipped = this.progress.skipped.filter(
          (q) => q !== questionNumber
        );
      }

      this.updateQuestionStatus(questionNumber, "unanswered");
    }

    /**
     * Show visual animation for status changes
     */
    showStatusChangeAnimation(questionNumber, changeType) {
      const btn = this.container.querySelector(
        `[data-question="${questionNumber}"]`
      );
      if (!btn) return;

      // Add specific animation class
      btn.classList.add(`status-change-${changeType}`);

      setTimeout(() => {
        btn.classList.remove(`status-change-${changeType}`);
      }, 800);

      // Update progress bar animation if in sectioned mode
      if (this.config.sections_enabled) {
        const sectionId = this.getQuestionSectionId(questionNumber);
        if (sectionId) {
          const sectionElement = this.container.querySelector(
            `[data-section="${sectionId}"]`
          );
          if (sectionElement) {
            const progressBar = sectionElement.querySelector(
              ".section-progress-fill"
            );
            if (progressBar) {
              progressBar.classList.add("progress-updated");
              setTimeout(() => {
                progressBar.classList.remove("progress-updated");
              }, 600);
            }
          }
        }
      }
    }

    /**
     * Get section ID for a question number
     */
    getQuestionSectionId(questionNumber) {
      if (!this.config.sections_enabled || !this.config.sections) {
        return "default";
      }

      let currentIndex = 0;
      for (const sectionId of Object.keys(this.config.questions)) {
        const sectionQuestions = this.config.questions[sectionId];
        if (
          questionNumber >= currentIndex &&
          questionNumber < currentIndex + sectionQuestions.length
        ) {
          return sectionId;
        }
        currentIndex += sectionQuestions.length;
      }

      return "default";
    }

    /**
     * Public method to get current state
     */
    getState() {
      return {
        currentQuestion: this.currentQuestion,
        progress: this.progress,
        config: this.config,
      };
    }

    /**
     * Sync progress with server for persistence
     */
    syncProgressWithServer() {
      if (!window.assessmentData || !window.assessmentData.session_data) {
        return;
      }

      const progressData = {
        current_question: this.currentQuestion,
        answers: this.progress.answers || {},
        skipped: this.progress.skipped || [],
        progress_stats: this.getProgressStats(),
      };

      // Send progress to server via AJAX
      if (typeof jQuery !== "undefined") {
        jQuery.ajax({
          url: window.assessmentData.session_data.ajax_url,
          type: "POST",
          data: {
            action: "mcqhome_save_progress",
            nonce: window.assessmentData.session_data.nonce,
            mcq_set_id: window.assessmentData.session_data.mcq_set_id,
            progress_data: JSON.stringify(progressData),
          },
          success: (response) => {
            if (response.success) {
              console.log("Progress synced successfully");
            }
          },
          error: (xhr, status, error) => {
            console.warn("Progress sync failed:", error);
          },
        });
      }
    }

    /**
     * Get real-time progress statistics
     */
    getRealTimeProgressStats() {
      const stats = this.getProgressStats();
      const sectionStats = this.getSectionProgressStats();

      return {
        overall: stats,
        sections: sectionStats,
        timestamp: Date.now(),
      };
    }

    /**
     * Destroy the navigation panel
     */
    destroy() {
      // Clear progress sync interval
      if (this.progressSyncInterval) {
        clearInterval(this.progressSyncInterval);
      }

      // Remove event listeners
      document.removeEventListener(
        "mcq-answer-changed",
        this.boundAnswerHandler
      );
      document.removeEventListener(
        "mcq-question-skipped",
        this.boundSkipHandler
      );
      document.removeEventListener(
        "mcq-answer-cleared",
        this.boundClearHandler
      );

      if (this.container) {
        this.container.innerHTML = "";
        this.container.removeEventListener("click", this.boundClickHandler);
        this.container.removeEventListener("touchend", this.boundTouchHandler);
        this.container.removeEventListener("keydown", this.boundKeyHandler);
      }
    }
  }

  // Export to global scope
  window.QuestionNavigationPanel = QuestionNavigationPanel;

  // Auto-initialize if assessment data is available
  $(document).ready(function () {
    if (
      typeof window.assessmentData !== "undefined" &&
      window.assessmentData.config
    ) {
      window.questionNavigationPanel = new QuestionNavigationPanel(
        window.assessmentData.config,
        window.assessmentData.progress || {}
      );
    }
  });
})(jQuery);
