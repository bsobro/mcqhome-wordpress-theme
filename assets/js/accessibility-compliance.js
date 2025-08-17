/**
 * Accessibility Compliance System
 * MCQHome Theme - WCAG 2.1 AA Implementation
 *
 * Provides comprehensive accessibility features for assessment interfaces
 */

(function ($) {
  "use strict";

  /**
   * Accessibility Manager Class
   */
  class AccessibilityManager {
    constructor() {
      this.announcer = null;
      this.focusManager = null;
      this.keyboardNavigation = null;
      this.screenReaderSupport = null;
      this.highContrastMode = false;
      this.reducedMotion = false;
      this.fontSize = "normal";

      this.init();
    }

    /**
     * Initialize accessibility features
     */
    init() {
      this.createLiveRegions();
      this.initializeFocusManagement();
      this.initializeKeyboardNavigation();
      this.initializeScreenReaderSupport();
      this.initializeARIALabels();
      this.initializeAccessibilityControls();
      this.detectUserPreferences();
      this.bindEvents();

      console.log("Accessibility features initialized");
    }

    /**
     * Create ARIA live regions for announcements
     */
    createLiveRegions() {
      // Create polite announcer for general updates
      this.announcer = document.createElement("div");
      this.announcer.setAttribute("aria-live", "polite");
      this.announcer.setAttribute("aria-atomic", "true");
      this.announcer.setAttribute("id", "accessibility-announcer");
      this.announcer.className = "sr-only";
      document.body.appendChild(this.announcer);

      // Create assertive announcer for urgent updates
      this.urgentAnnouncer = document.createElement("div");
      this.urgentAnnouncer.setAttribute("aria-live", "assertive");
      this.urgentAnnouncer.setAttribute("aria-atomic", "true");
      this.urgentAnnouncer.setAttribute("id", "accessibility-urgent-announcer");
      this.urgentAnnouncer.className = "sr-only";
      document.body.appendChild(this.urgentAnnouncer);

      // Create status region for progress updates
      this.statusRegion = document.createElement("div");
      this.statusRegion.setAttribute("role", "status");
      this.statusRegion.setAttribute("aria-live", "polite");
      this.statusRegion.setAttribute("id", "accessibility-status");
      this.statusRegion.className = "sr-only";
      document.body.appendChild(this.statusRegion);
    }

    /**
     * Initialize focus management
     */
    initializeFocusManagement() {
      this.focusManager = {
        focusHistory: [],
        trapStack: [],

        // Store focus for restoration
        storeFocus: function () {
          this.focusHistory.push(document.activeElement);
        },

        // Restore previous focus
        restoreFocus: function () {
          if (this.focusHistory.length > 0) {
            const element = this.focusHistory.pop();
            if (element && element.focus) {
              element.focus();
            }
          }
        },

        // Trap focus within container
        trapFocus: function (container) {
          const focusableElements = container.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
          );

          if (focusableElements.length === 0) return;

          const firstElement = focusableElements[0];
          const lastElement = focusableElements[focusableElements.length - 1];

          const trapHandler = function (e) {
            if (e.key === "Tab") {
              if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                  e.preventDefault();
                  lastElement.focus();
                }
              } else {
                if (document.activeElement === lastElement) {
                  e.preventDefault();
                  firstElement.focus();
                }
              }
            }
          };

          container.addEventListener("keydown", trapHandler);
          this.trapStack.push({ container, handler: trapHandler });

          // Focus first element
          firstElement.focus();
        },

        // Release focus trap
        releaseFocusTrap: function () {
          if (this.trapStack.length > 0) {
            const { container, handler } = this.trapStack.pop();
            container.removeEventListener("keydown", handler);
          }
        },
      };
    }

    /**
     * Initialize comprehensive keyboard navigation
     */
    initializeKeyboardNavigation() {
      this.keyboardNavigation = {
        // Enhanced question navigation
        initQuestionNavigation: () => {
          document.addEventListener("keydown", (e) => {
            // Skip if user is typing in input field
            if (e.target.matches("input, textarea, select")) return;

            const currentIndex = window.assessmentState?.currentQuestion || 0;
            const totalQuestions = window.assessmentData?.totalQuestions || 0;

            switch (e.key) {
              case "ArrowLeft":
                if (e.ctrlKey && currentIndex > 0) {
                  e.preventDefault();
                  this.navigateToQuestion(currentIndex - 1);
                  this.announce(`Moved to question ${currentIndex}`);
                }
                break;

              case "ArrowRight":
                if (e.ctrlKey && currentIndex < totalQuestions - 1) {
                  e.preventDefault();
                  this.navigateToQuestion(currentIndex + 1);
                  this.announce(`Moved to question ${currentIndex + 2}`);
                }
                break;

              case "Home":
                if (e.ctrlKey) {
                  e.preventDefault();
                  this.navigateToQuestion(0);
                  this.announce("Moved to first question");
                }
                break;

              case "End":
                if (e.ctrlKey) {
                  e.preventDefault();
                  this.navigateToQuestion(totalQuestions - 1);
                  this.announce("Moved to last question");
                }
                break;

              case "1":
              case "2":
              case "3":
              case "4":
                if (e.altKey) {
                  e.preventDefault();
                  const optionMap = { 1: "A", 2: "B", 3: "C", 4: "D" };
                  this.selectOption(currentIndex, optionMap[e.key]);
                  this.announce(`Selected option ${optionMap[e.key]}`);
                }
                break;

              case "Enter":
                if (e.ctrlKey) {
                  e.preventDefault();
                  this.submitAssessment();
                }
                break;

              case "Escape":
                // Clear current selection or show help
                this.handleEscapeKey();
                break;

              case "F1":
                e.preventDefault();
                this.showKeyboardHelp();
                break;
            }
          });
        },

        // Navigation panel keyboard support
        initNavigationPanelKeyboard: () => {
          const navPanel = document.querySelector(".question-navigation-panel");
          if (!navPanel) return;

          navPanel.addEventListener("keydown", (e) => {
            if (!e.target.classList.contains("question-number")) return;

            const currentBtn = e.target;
            const questionNumber = parseInt(currentBtn.dataset.question);

            switch (e.key) {
              case "ArrowRight":
              case "ArrowDown":
                e.preventDefault();
                this.focusNextQuestionButton(questionNumber);
                break;

              case "ArrowLeft":
              case "ArrowUp":
                e.preventDefault();
                this.focusPreviousQuestionButton(questionNumber);
                break;

              case "Home":
                e.preventDefault();
                this.focusQuestionButton(0);
                break;

              case "End":
                e.preventDefault();
                const totalQuestions =
                  window.assessmentData?.totalQuestions || 0;
                this.focusQuestionButton(totalQuestions - 1);
                break;
            }
          });
        },
      };

      this.keyboardNavigation.initQuestionNavigation();
      this.keyboardNavigation.initNavigationPanelKeyboard();
    }

    /**
     * Initialize screen reader support
     */
    initializeScreenReaderSupport() {
      this.screenReaderSupport = {
        // Announce question changes
        announceQuestionChange: (questionNumber, questionText) => {
          const announcement = `Question ${
            questionNumber + 1
          }. ${this.stripHTML(questionText)}`;
          this.announce(announcement);
        },

        // Announce answer selection
        announceAnswerSelection: (option, optionText) => {
          const announcement = `Selected option ${option}: ${this.stripHTML(
            optionText
          )}`;
          this.announce(announcement);
        },

        // Announce progress updates
        announceProgress: (attempted, total, skipped = 0) => {
          let announcement = `Progress: ${attempted} of ${total} questions answered`;
          if (skipped > 0) {
            announcement += `, ${skipped} skipped`;
          }
          this.announceStatus(announcement);
        },

        // Announce time remaining
        announceTimeRemaining: (minutes, seconds) => {
          if (minutes === 5 && seconds === 0) {
            this.announceUrgent("5 minutes remaining");
          } else if (minutes === 1 && seconds === 0) {
            this.announceUrgent("1 minute remaining");
          } else if (minutes === 0 && seconds === 30) {
            this.announceUrgent("30 seconds remaining");
          }
        },

        // Announce section changes
        announceSectionChange: (
          sectionName,
          questionNumber,
          totalInSection
        ) => {
          const announcement = `Entering section: ${sectionName}. Question ${
            questionNumber + 1
          } of ${totalInSection} in this section.`;
          this.announce(announcement);
        },
      };
    }

    /**
     * Initialize comprehensive ARIA labels and attributes
     */
    initializeARIALabels() {
      // Assessment container
      const assessmentContainer = document.querySelector(
        ".mcq-assessment-container"
      );
      if (assessmentContainer) {
        assessmentContainer.setAttribute("role", "main");
        assessmentContainer.setAttribute("aria-label", "Assessment Interface");
      }

      // Assessment header
      const assessmentHeader = document.querySelector(".assessment-header");
      if (assessmentHeader) {
        assessmentHeader.setAttribute("role", "banner");
        assessmentHeader.setAttribute("aria-label", "Assessment Information");
      }

      // Timer display
      const timerDisplay = document.querySelector("#timer-display");
      if (timerDisplay) {
        timerDisplay.setAttribute("role", "timer");
        timerDisplay.setAttribute("aria-live", "polite");
        timerDisplay.setAttribute("aria-label", "Time remaining");
        timerDisplay.setAttribute("aria-atomic", "true");
      }

      // Question navigation panel
      const navPanel = document.querySelector(".question-navigation-panel");
      if (navPanel) {
        navPanel.setAttribute("role", "navigation");
        navPanel.setAttribute("aria-label", "Question Navigation");

        // Navigation header
        const navHeader = navPanel.querySelector("h3");
        if (navHeader) {
          navHeader.setAttribute("id", "nav-panel-heading");
          navPanel.setAttribute("aria-labelledby", "nav-panel-heading");
        }
      }

      // Question numbers
      document.querySelectorAll(".question-number").forEach((btn, index) => {
        btn.setAttribute("role", "button");
        btn.setAttribute("aria-label", `Go to question ${index + 1}`);
        btn.setAttribute("tabindex", "0");
      });

      // Question display area
      const questionArea = document.querySelector(".question-display-area");
      if (questionArea) {
        questionArea.setAttribute("role", "region");
        questionArea.setAttribute("aria-label", "Question Content");
      }

      // Answer options
      this.initializeAnswerOptionsARIA();

      // Navigation buttons
      this.initializeNavigationButtonsARIA();

      // Progress indicators
      this.initializeProgressARIA();
    }

    /**
     * Initialize answer options ARIA attributes
     */
    initializeAnswerOptionsARIA() {
      document
        .querySelectorAll(".answer-options")
        .forEach((optionsContainer, questionIndex) => {
          optionsContainer.setAttribute("role", "radiogroup");
          optionsContainer.setAttribute(
            "aria-label",
            `Answer options for question ${questionIndex + 1}`
          );

          const options = optionsContainer.querySelectorAll(".option-label");
          options.forEach((option, optionIndex) => {
            const radio = option.querySelector('input[type="radio"]');
            const optionText = option.querySelector(".option-text");
            const optionLetter = option.querySelector(".option-letter");

            if (radio && optionText && optionLetter) {
              const optionId = `question-${questionIndex}-option-${optionIndex}`;
              const labelId = `question-${questionIndex}-option-${optionIndex}-label`;

              radio.setAttribute("id", optionId);
              radio.setAttribute("aria-describedby", labelId);

              option.setAttribute("id", labelId);
              option.setAttribute(
                "aria-label",
                `Option ${optionLetter.textContent}: ${this.stripHTML(
                  optionText.textContent
                )}`
              );

              // Add keyboard support for option selection
              option.addEventListener("keydown", (e) => {
                if (e.key === "Enter" || e.key === " ") {
                  e.preventDefault();
                  radio.checked = true;
                  radio.dispatchEvent(new Event("change", { bubbles: true }));
                  this.screenReaderSupport.announceAnswerSelection(
                    optionLetter.textContent,
                    optionText.textContent
                  );
                }
              });
            }
          });
        });
    }

    /**
     * Initialize navigation buttons ARIA attributes
     */
    initializeNavigationButtonsARIA() {
      // Previous button
      const prevBtn = document.querySelector(".prev-btn");
      if (prevBtn) {
        prevBtn.setAttribute("aria-label", "Go to previous question");
        prevBtn.setAttribute("aria-keyshortcuts", "Ctrl+ArrowLeft");
      }

      // Next button
      const nextBtn = document.querySelector(".next-btn");
      if (nextBtn) {
        nextBtn.setAttribute("aria-label", "Go to next question");
        nextBtn.setAttribute("aria-keyshortcuts", "Ctrl+ArrowRight");
      }

      // Submit button
      const submitBtn = document.querySelector(".submit-btn");
      if (submitBtn) {
        submitBtn.setAttribute("aria-label", "Submit assessment");
        submitBtn.setAttribute("aria-keyshortcuts", "Ctrl+Enter");
        submitBtn.setAttribute("aria-describedby", "submit-warning");

        // Add submit warning
        const submitWarning = document.createElement("div");
        submitWarning.id = "submit-warning";
        submitWarning.className = "sr-only";
        submitWarning.textContent =
          "Warning: Submitting will end the assessment and cannot be undone.";
        submitBtn.parentNode.insertBefore(submitWarning, submitBtn);
      }
    }

    /**
     * Initialize progress indicators ARIA attributes
     */
    initializeProgressARIA() {
      // Progress bar
      const progressBar = document.querySelector(".progress-bar");
      if (progressBar) {
        progressBar.setAttribute("role", "progressbar");
        progressBar.setAttribute("aria-label", "Assessment progress");

        const progressFill = progressBar.querySelector(".progress-fill");
        if (progressFill) {
          this.updateProgressARIA();
        }
      }

      // Progress summary
      const progressSummary = document.querySelector(".progress-summary");
      if (progressSummary) {
        progressSummary.setAttribute("role", "status");
        progressSummary.setAttribute("aria-live", "polite");
        progressSummary.setAttribute("aria-label", "Progress summary");
      }
    }

    /**
     * Initialize accessibility controls
     */
    initializeAccessibilityControls() {
      // Create accessibility toolbar
      const toolbar = document.createElement("div");
      toolbar.className = "accessibility-toolbar";
      toolbar.setAttribute("role", "toolbar");
      toolbar.setAttribute("aria-label", "Accessibility Controls");

      toolbar.innerHTML = `
                <button class="accessibility-btn" id="toggle-high-contrast" 
                        aria-label="Toggle high contrast mode" 
                        aria-pressed="false">
                    <span class="btn-icon">🎨</span>
                    <span class="btn-text">High Contrast</span>
                </button>
                
                <button class="accessibility-btn" id="toggle-reduced-motion" 
                        aria-label="Toggle reduced motion" 
                        aria-pressed="false">
                    <span class="btn-icon">🎬</span>
                    <span class="btn-text">Reduce Motion</span>
                </button>
                
                <div class="font-size-controls" role="group" aria-label="Font size controls">
                    <button class="accessibility-btn" id="decrease-font-size" 
                            aria-label="Decrease font size">
                        <span class="btn-icon">A-</span>
                    </button>
                    <button class="accessibility-btn" id="increase-font-size" 
                            aria-label="Increase font size">
                        <span class="btn-icon">A+</span>
                    </button>
                </div>
                
                <button class="accessibility-btn" id="show-keyboard-help" 
                        aria-label="Show keyboard shortcuts help">
                    <span class="btn-icon">⌨️</span>
                    <span class="btn-text">Keyboard Help</span>
                </button>
                
                <button class="accessibility-btn" id="skip-to-content" 
                        aria-label="Skip to main content">
                    <span class="btn-text">Skip to Content</span>
                </button>
            `;

      // Insert toolbar at the beginning of the page
      document.body.insertBefore(toolbar, document.body.firstChild);

      // Bind toolbar events
      this.bindAccessibilityToolbarEvents();
    }

    /**
     * Bind accessibility toolbar events
     */
    bindAccessibilityToolbarEvents() {
      // High contrast toggle
      document
        .getElementById("toggle-high-contrast")
        .addEventListener("click", () => {
          this.toggleHighContrast();
        });

      // Reduced motion toggle
      document
        .getElementById("toggle-reduced-motion")
        .addEventListener("click", () => {
          this.toggleReducedMotion();
        });

      // Font size controls
      document
        .getElementById("decrease-font-size")
        .addEventListener("click", () => {
          this.adjustFontSize("decrease");
        });

      document
        .getElementById("increase-font-size")
        .addEventListener("click", () => {
          this.adjustFontSize("increase");
        });

      // Keyboard help
      document
        .getElementById("show-keyboard-help")
        .addEventListener("click", () => {
          this.showKeyboardHelp();
        });

      // Skip to content
      document
        .getElementById("skip-to-content")
        .addEventListener("click", () => {
          this.skipToContent();
        });
    }

    /**
     * Detect user preferences from system settings
     */
    detectUserPreferences() {
      // Detect reduced motion preference
      if (
        window.matchMedia &&
        window.matchMedia("(prefers-reduced-motion: reduce)").matches
      ) {
        this.reducedMotion = true;
        document.body.classList.add("reduced-motion");
        const btn = document.getElementById("toggle-reduced-motion");
        if (btn) btn.setAttribute("aria-pressed", "true");
      }

      // Detect high contrast preference
      if (
        window.matchMedia &&
        window.matchMedia("(prefers-contrast: high)").matches
      ) {
        this.highContrastMode = true;
        document.body.classList.add("high-contrast");
        const btn = document.getElementById("toggle-high-contrast");
        if (btn) btn.setAttribute("aria-pressed", "true");
      }

      // Listen for preference changes
      if (window.matchMedia) {
        window
          .matchMedia("(prefers-reduced-motion: reduce)")
          .addEventListener("change", (e) => {
            this.reducedMotion = e.matches;
            document.body.classList.toggle("reduced-motion", e.matches);
          });

        window
          .matchMedia("(prefers-contrast: high)")
          .addEventListener("change", (e) => {
            this.highContrastMode = e.matches;
            document.body.classList.toggle("high-contrast", e.matches);
          });
      }
    }

    /**
     * Bind global accessibility events
     */
    bindEvents() {
      // Listen for assessment events to provide announcements
      document.addEventListener("mcq-answer-changed", (e) => {
        const { questionNumber, answer, optionText } = e.detail;
        this.screenReaderSupport.announceAnswerSelection(answer, optionText);
      });

      document.addEventListener("mcq-question-changed", (e) => {
        const { questionNumber, questionText, sectionName } = e.detail;
        this.screenReaderSupport.announceQuestionChange(
          questionNumber,
          questionText
        );

        if (sectionName) {
          this.screenReaderSupport.announceSectionChange(
            sectionName,
            questionNumber,
            0
          );
        }
      });

      document.addEventListener("mcq-progress-updated", (e) => {
        const { attempted, total, skipped } = e.detail;
        this.screenReaderSupport.announceProgress(attempted, total, skipped);
        this.updateProgressARIA();
      });

      // Listen for timer updates
      document.addEventListener("mcq-timer-update", (e) => {
        const { minutes, seconds } = e.detail;
        this.screenReaderSupport.announceTimeRemaining(minutes, seconds);
      });

      // Handle focus management for modals/dialogs
      document.addEventListener("modal-opened", (e) => {
        this.focusManager.storeFocus();
        this.focusManager.trapFocus(e.detail.modal);
      });

      document.addEventListener("modal-closed", () => {
        this.focusManager.releaseFocusTrap();
        this.focusManager.restoreFocus();
      });
    }

    /**
     * Toggle high contrast mode
     */
    toggleHighContrast() {
      this.highContrastMode = !this.highContrastMode;
      document.body.classList.toggle("high-contrast", this.highContrastMode);

      const btn = document.getElementById("toggle-high-contrast");
      if (btn) {
        btn.setAttribute("aria-pressed", this.highContrastMode.toString());
      }

      this.announce(
        this.highContrastMode
          ? "High contrast mode enabled"
          : "High contrast mode disabled"
      );

      // Save preference
      localStorage.setItem(
        "accessibility-high-contrast",
        this.highContrastMode.toString()
      );
    }

    /**
     * Toggle reduced motion
     */
    toggleReducedMotion() {
      this.reducedMotion = !this.reducedMotion;
      document.body.classList.toggle("reduced-motion", this.reducedMotion);

      const btn = document.getElementById("toggle-reduced-motion");
      if (btn) {
        btn.setAttribute("aria-pressed", this.reducedMotion.toString());
      }

      this.announce(
        this.reducedMotion
          ? "Reduced motion enabled"
          : "Reduced motion disabled"
      );

      // Save preference
      localStorage.setItem(
        "accessibility-reduced-motion",
        this.reducedMotion.toString()
      );
    }

    /**
     * Adjust font size
     */
    adjustFontSize(direction) {
      const sizes = ["small", "normal", "large", "extra-large"];
      const currentIndex = sizes.indexOf(this.fontSize);

      let newIndex;
      if (direction === "increase" && currentIndex < sizes.length - 1) {
        newIndex = currentIndex + 1;
      } else if (direction === "decrease" && currentIndex > 0) {
        newIndex = currentIndex - 1;
      } else {
        return; // No change needed
      }

      // Remove current font size class
      document.body.classList.remove(`font-size-${this.fontSize}`);

      // Add new font size class
      this.fontSize = sizes[newIndex];
      document.body.classList.add(`font-size-${this.fontSize}`);

      this.announce(`Font size changed to ${this.fontSize}`);

      // Save preference
      localStorage.setItem("accessibility-font-size", this.fontSize);
    }

    /**
     * Show keyboard shortcuts help
     */
    showKeyboardHelp() {
      const helpModal = document.createElement("div");
      helpModal.className = "keyboard-help-modal";
      helpModal.setAttribute("role", "dialog");
      helpModal.setAttribute("aria-labelledby", "keyboard-help-title");
      helpModal.setAttribute("aria-modal", "true");

      helpModal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 id="keyboard-help-title">Keyboard Shortcuts</h2>
                        <button class="close-btn" aria-label="Close help dialog">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="shortcut-group">
                            <h3>Navigation</h3>
                            <dl>
                                <dt>Ctrl + Left Arrow</dt>
                                <dd>Go to previous question</dd>
                                <dt>Ctrl + Right Arrow</dt>
                                <dd>Go to next question</dd>
                                <dt>Ctrl + Home</dt>
                                <dd>Go to first question</dd>
                                <dt>Ctrl + End</dt>
                                <dd>Go to last question</dd>
                            </dl>
                        </div>
                        
                        <div class="shortcut-group">
                            <h3>Answer Selection</h3>
                            <dl>
                                <dt>Alt + 1</dt>
                                <dd>Select option A</dd>
                                <dt>Alt + 2</dt>
                                <dd>Select option B</dd>
                                <dt>Alt + 3</dt>
                                <dd>Select option C</dd>
                                <dt>Alt + 4</dt>
                                <dd>Select option D</dd>
                            </dl>
                        </div>
                        
                        <div class="shortcut-group">
                            <h3>Assessment Control</h3>
                            <dl>
                                <dt>Ctrl + Enter</dt>
                                <dd>Submit assessment</dd>
                                <dt>Escape</dt>
                                <dd>Clear current selection</dd>
                                <dt>F1</dt>
                                <dd>Show this help</dd>
                            </dl>
                        </div>
                        
                        <div class="shortcut-group">
                            <h3>Question Navigation Panel</h3>
                            <dl>
                                <dt>Arrow Keys</dt>
                                <dd>Navigate between question numbers</dd>
                                <dt>Enter/Space</dt>
                                <dd>Go to selected question</dd>
                                <dt>Home/End</dt>
                                <dd>Go to first/last question number</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            `;

      document.body.appendChild(helpModal);

      // Focus management
      this.focusManager.storeFocus();
      this.focusManager.trapFocus(helpModal);

      // Close button handler
      const closeBtn = helpModal.querySelector(".close-btn");
      closeBtn.addEventListener("click", () => {
        this.closeKeyboardHelp(helpModal);
      });

      // Escape key handler
      const escapeHandler = (e) => {
        if (e.key === "Escape") {
          this.closeKeyboardHelp(helpModal);
          document.removeEventListener("keydown", escapeHandler);
        }
      };
      document.addEventListener("keydown", escapeHandler);

      // Announce modal opening
      this.announce("Keyboard shortcuts help dialog opened");
    }

    /**
     * Close keyboard help modal
     */
    closeKeyboardHelp(modal) {
      this.focusManager.releaseFocusTrap();
      this.focusManager.restoreFocus();
      document.body.removeChild(modal);
      this.announce("Keyboard shortcuts help dialog closed");
    }

    /**
     * Skip to main content
     */
    skipToContent() {
      const mainContent = document.querySelector(
        '.question-display-area, main, [role="main"]'
      );
      if (mainContent) {
        mainContent.focus();
        mainContent.scrollIntoView({ behavior: "smooth", block: "start" });
        this.announce("Skipped to main content");
      }
    }

    /**
     * Handle escape key press
     */
    handleEscapeKey() {
      // Clear current answer selection
      const currentQuestion = window.assessmentState?.currentQuestion || 0;
      const currentRadio = document.querySelector(
        `input[name="question_${currentQuestion}"]:checked`
      );

      if (currentRadio) {
        currentRadio.checked = false;
        this.announce("Answer selection cleared");

        // Trigger change event
        currentRadio.dispatchEvent(new Event("change", { bubbles: true }));
      }
    }

    /**
     * Navigate to question (accessibility-enhanced)
     */
    navigateToQuestion(questionNumber) {
      if (typeof window.navigateToQuestion === "function") {
        window.navigateToQuestion(questionNumber);
      }
    }

    /**
     * Select option (accessibility-enhanced)
     */
    selectOption(questionNumber, option) {
      const radio = document.querySelector(
        `input[name="question_${questionNumber}"][value="${option}"]`
      );
      if (radio) {
        radio.checked = true;
        radio.dispatchEvent(new Event("change", { bubbles: true }));
      }
    }

    /**
     * Submit assessment (accessibility-enhanced)
     */
    submitAssessment() {
      if (typeof window.submitAssessment === "function") {
        // Confirm submission
        const confirmed = confirm(
          "Are you sure you want to submit your assessment? This action cannot be undone."
        );
        if (confirmed) {
          window.submitAssessment();
          this.announce("Assessment submitted");
        }
      }
    }

    /**
     * Focus navigation buttons
     */
    focusNextQuestionButton(currentNumber) {
      const totalQuestions = window.assessmentData?.totalQuestions || 0;
      const nextNumber = Math.min(currentNumber + 1, totalQuestions - 1);
      this.focusQuestionButton(nextNumber);
    }

    focusPreviousQuestionButton(currentNumber) {
      const prevNumber = Math.max(currentNumber - 1, 0);
      this.focusQuestionButton(prevNumber);
    }

    focusQuestionButton(questionNumber) {
      const btn = document.querySelector(`[data-question="${questionNumber}"]`);
      if (btn) {
        btn.focus();
        this.announce(`Focused on question ${questionNumber + 1}`);
      }
    }

    /**
     * Update progress ARIA attributes
     */
    updateProgressARIA() {
      const progressBar = document.querySelector(".progress-bar");
      if (!progressBar) return;

      const attempted = Object.keys(
        window.assessmentState?.answers || {}
      ).length;
      const total = window.assessmentData?.totalQuestions || 0;
      const percentage = total > 0 ? Math.round((attempted / total) * 100) : 0;

      progressBar.setAttribute("aria-valuenow", attempted.toString());
      progressBar.setAttribute("aria-valuemin", "0");
      progressBar.setAttribute("aria-valuemax", total.toString());
      progressBar.setAttribute(
        "aria-valuetext",
        `${attempted} of ${total} questions answered, ${percentage}% complete`
      );
    }

    /**
     * Announce message to screen readers (polite)
     */
    announce(message) {
      if (this.announcer) {
        this.announcer.textContent = message;
      }
    }

    /**
     * Announce urgent message to screen readers (assertive)
     */
    announceUrgent(message) {
      if (this.urgentAnnouncer) {
        this.urgentAnnouncer.textContent = message;
      }
    }

    /**
     * Announce status update
     */
    announceStatus(message) {
      if (this.statusRegion) {
        this.statusRegion.textContent = message;
      }
    }

    /**
     * Strip HTML tags from text
     */
    stripHTML(html) {
      const temp = document.createElement("div");
      temp.innerHTML = html;
      return temp.textContent || temp.innerText || "";
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
     * Load saved accessibility preferences
     */
    loadSavedPreferences() {
      // Load high contrast preference
      const highContrast = localStorage.getItem("accessibility-high-contrast");
      if (highContrast === "true") {
        this.toggleHighContrast();
      }

      // Load reduced motion preference
      const reducedMotion = localStorage.getItem(
        "accessibility-reduced-motion"
      );
      if (reducedMotion === "true") {
        this.toggleReducedMotion();
      }

      // Load font size preference
      const fontSize = localStorage.getItem("accessibility-font-size");
      if (
        fontSize &&
        ["small", "normal", "large", "extra-large"].includes(fontSize)
      ) {
        this.fontSize = fontSize;
        document.body.classList.add(`font-size-${fontSize}`);
      }
    }

    /**
     * Destroy accessibility manager
     */
    destroy() {
      // Remove live regions
      if (this.announcer) document.body.removeChild(this.announcer);
      if (this.urgentAnnouncer) document.body.removeChild(this.urgentAnnouncer);
      if (this.statusRegion) document.body.removeChild(this.statusRegion);

      // Remove toolbar
      const toolbar = document.querySelector(".accessibility-toolbar");
      if (toolbar) document.body.removeChild(toolbar);

      // Release focus traps
      while (this.focusManager.trapStack.length > 0) {
        this.focusManager.releaseFocusTrap();
      }
    }
  }

  // Initialize accessibility manager when document is ready
  $(document).ready(function () {
    // Only initialize on assessment pages
    if (document.querySelector(".mcq-assessment-container")) {
      window.accessibilityManager = new AccessibilityManager();

      // Load saved preferences
      window.accessibilityManager.loadSavedPreferences();

      console.log("Assessment accessibility features initialized");
    }
  });

  // Expose for external use
  window.AccessibilityManager = AccessibilityManager;
})(jQuery);
