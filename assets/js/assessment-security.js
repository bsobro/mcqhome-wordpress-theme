/**
 * Assessment Security and Anti-Cheating System
 *
 * @package MCQHome
 * @since 1.0.0
 */

class AssessmentSecurity {
  constructor(config) {
    this.config = config;
    this.activityLog = [];
    this.sessionStartTime = Date.now();
    this.lastActivityTime = Date.now();
    this.tabSwitchCount = 0;
    this.copyPasteCount = 0;
    this.suspiciousActivityCount = 0;

    this.init();
  }

  init() {
    this.setupEventListeners();
    this.startSessionMonitoring();
    this.setupPeriodicValidation();
    this.preventCheatingMethods();
  }

  setupEventListeners() {
    // Tab/Window focus monitoring
    window.addEventListener("blur", () => this.logActivity("window_blur"));
    window.addEventListener("focus", () => this.logActivity("window_focus"));
    document.addEventListener("visibilitychange", () => {
      if (document.hidden) {
        this.logActivity("tab_blur");
        this.tabSwitchCount++;
      } else {
        this.logActivity("tab_focus");
      }
    });

    // Copy/Paste detection
    document.addEventListener("copy", () => {
      this.logActivity("copy_detected");
      this.copyPasteCount++;
    });

    document.addEventListener("paste", () => {
      this.logActivity("paste_detected");
      this.copyPasteCount++;
    });

    // Right-click prevention
    document.addEventListener("contextmenu", (e) => {
      e.preventDefault();
      this.logActivity("right_click_attempt");
      return false;
    });

    // Keyboard shortcuts prevention
    document.addEventListener("keydown", (e) => {
      // Prevent F12, Ctrl+Shift+I, Ctrl+U, Ctrl+S, etc.
      if (
        e.key === "F12" ||
        (e.ctrlKey && e.shiftKey && e.key === "I") ||
        (e.ctrlKey && e.key === "u") ||
        (e.ctrlKey && e.key === "s") ||
        (e.ctrlKey && e.shiftKey && e.key === "C")
      ) {
        e.preventDefault();
        this.logActivity("blocked_shortcut", {
          key: e.key,
          ctrl: e.ctrlKey,
          shift: e.shiftKey,
        });
        return false;
      }
    });

    // Mouse selection prevention for questions
    document.addEventListener("selectstart", (e) => {
      if (e.target.closest(".question-text, .option-text")) {
        e.preventDefault();
        return false;
      }
    });

    // Answer change monitoring
    document.addEventListener("change", (e) => {
      if (e.target.type === "radio" && e.target.name.startsWith("question_")) {
        const questionNumber = e.target.name.replace("question_", "");
        this.logActivity("answer_changed", {
          question: questionNumber,
          answer: e.target.value,
          timestamp: Date.now(),
        });
      }
    });

    // Page unload warning
    window.addEventListener("beforeunload", (e) => {
      if (this.config.preventNavigation) {
        e.preventDefault();
        e.returnValue =
          "Are you sure you want to leave? Your progress may be lost.";
        this.logActivity("navigation_attempt");
        return e.returnValue;
      }
    });
  }

  startSessionMonitoring() {
    // Monitor session every 30 seconds
    setInterval(() => {
      this.validateSession();
      this.checkSuspiciousActivity();
      this.updateLastActivity();
    }, 30000);

    // Monitor time limit if set
    if (this.config.timeLimit > 0) {
      this.startTimeLimitMonitoring();
    }
  }

  startTimeLimitMonitoring() {
    const checkInterval = setInterval(() => {
      const elapsed = (Date.now() - this.sessionStartTime) / 1000;
      const remaining = this.config.timeLimit - elapsed;

      if (remaining <= 0) {
        clearInterval(checkInterval);
        this.handleTimeExpired();
      } else if (remaining <= 300) {
        // 5 minutes warning
        this.showTimeWarning(remaining);
      }
    }, 1000);
  }

  setupPeriodicValidation() {
    // Validate session with server every 2 minutes
    setInterval(() => {
      this.validateSessionWithServer();
    }, 120000);
  }

  preventCheatingMethods() {
    // Disable text selection on question content
    const style = document.createElement("style");
    style.textContent = `
            .question-text, .option-text {
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                -webkit-touch-callout: none;
                -webkit-tap-highlight-color: transparent;
            }
        `;
    document.head.appendChild(style);

    // Disable drag and drop
    document.addEventListener("dragstart", (e) => {
      if (e.target.closest(".assessment-container")) {
        e.preventDefault();
        return false;
      }
    });

    // Monitor for developer tools
    this.detectDevTools();
  }

  detectDevTools() {
    let devtools = {
      open: false,
      orientation: null,
    };

    const threshold = 160;

    setInterval(() => {
      if (
        window.outerHeight - window.innerHeight > threshold ||
        window.outerWidth - window.innerWidth > threshold
      ) {
        if (!devtools.open) {
          devtools.open = true;
          this.logActivity("devtools_opened");
          this.handleSuspiciousActivity("devtools_detected");
        }
      } else {
        if (devtools.open) {
          devtools.open = false;
          this.logActivity("devtools_closed");
        }
      }
    }, 500);
  }

  logActivity(type, data = {}) {
    const activity = {
      type: type,
      timestamp: Date.now(),
      data: data,
    };

    this.activityLog.push(activity);
    this.lastActivityTime = Date.now();

    // Send to server for critical activities
    const criticalActivities = [
      "tab_blur",
      "tab_focus",
      "window_blur",
      "window_focus",
      "copy_detected",
      "paste_detected",
      "devtools_opened",
      "answer_changed",
      "navigation_attempt",
    ];

    if (criticalActivities.includes(type)) {
      this.sendActivityToServer(activity);
    }

    // Keep log size manageable
    if (this.activityLog.length > 1000) {
      this.activityLog = this.activityLog.slice(-500);
    }
  }

  sendActivityToServer(activity) {
    if (!this.config.ajaxUrl || !this.config.nonce) {
      return;
    }

    const formData = new FormData();
    formData.append("action", "mcqhome_log_activity");
    formData.append("nonce", this.config.nonce);
    formData.append("mcq_set_id", this.config.setId);
    formData.append("activity_type", activity.type);
    formData.append("activity_data", JSON.stringify(activity.data));

    fetch(this.config.ajaxUrl, {
      method: "POST",
      body: formData,
    }).catch((error) => {
      console.error("Failed to log activity:", error);
    });
  }

  validateSession() {
    // Check if session is still valid
    const sessionDuration = (Date.now() - this.sessionStartTime) / 1000;
    const maxSessionDuration = 24 * 60 * 60; // 24 hours

    if (sessionDuration > maxSessionDuration) {
      this.handleSessionExpired();
      return false;
    }

    return true;
  }

  validateSessionWithServer(currentSection = null) {
    if (!this.config.ajaxUrl || !this.config.nonce) {
      return;
    }

    const formData = new FormData();
    formData.append("action", "mcqhome_validate_session");
    formData.append("nonce", this.config.nonce);
    formData.append("mcq_set_id", this.config.setId);

    if (currentSection) {
      formData.append("current_section", currentSection);
    }

    fetch(this.config.ajaxUrl, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (!data.success) {
          this.handleSessionError(data.data.message);
        } else {
          // Update time remaining if provided
          if (data.data.time_remaining !== undefined) {
            this.updateTimeRemaining(data.data.time_remaining);
          }

          // Handle section-specific validation results
          if (data.data.sections_enabled && currentSection) {
            this.handleSectionValidation(data.data, currentSection);
          }
        }
      })
      .catch((error) => {
        console.error("Session validation failed:", error);
      });
  }

  checkSuspiciousActivity() {
    const recentActivities = this.activityLog.filter(
      (activity) => Date.now() - activity.timestamp < 60000 // Last minute
    );

    // Check for excessive tab switching
    const tabSwitches = recentActivities.filter(
      (activity) =>
        activity.type === "tab_blur" || activity.type === "tab_focus"
    ).length;

    if (tabSwitches > 10) {
      this.handleSuspiciousActivity("excessive_tab_switching", {
        count: tabSwitches,
      });
    }

    // Check for rapid answer changes
    const answerChanges = recentActivities.filter(
      (activity) => activity.type === "answer_changed"
    ).length;

    if (answerChanges > 20) {
      this.handleSuspiciousActivity("rapid_answer_changes", {
        count: answerChanges,
      });
    }

    // Check for copy/paste activity
    const copyPasteActivities = recentActivities.filter(
      (activity) =>
        activity.type === "copy_detected" || activity.type === "paste_detected"
    ).length;

    if (copyPasteActivities > 5) {
      this.handleSuspiciousActivity("excessive_copy_paste", {
        count: copyPasteActivities,
      });
    }
  }

  handleSuspiciousActivity(type, data = {}) {
    this.suspiciousActivityCount++;
    this.logActivity("suspicious_activity_detected", {
      type,
      data,
      count: this.suspiciousActivityCount,
    });

    // Show warning for first few instances
    if (this.suspiciousActivityCount <= 3) {
      this.showSecurityWarning(type);
    }

    // Take action for repeated suspicious activity
    if (this.suspiciousActivityCount >= 5) {
      this.handleSecurityViolation();
    }
  }

  showSecurityWarning(type) {
    let message =
      "Suspicious activity detected. Please focus on the assessment.";

    switch (type) {
      case "excessive_tab_switching":
        message = "Please avoid switching between tabs during the assessment.";
        break;
      case "devtools_detected":
        message = "Developer tools detected. Please close them to continue.";
        break;
      case "excessive_copy_paste":
        message = "Copy/paste activities are being monitored.";
        break;
    }

    this.showNotification(message, "warning");
  }

  handleSecurityViolation() {
    this.showNotification(
      "Multiple security violations detected. Your assessment may be terminated.",
      "error"
    );

    // Could implement automatic submission here
    // this.forceSubmitAssessment();
  }

  handleTimeExpired() {
    this.logActivity("time_expired");
    this.showNotification(
      "Time limit reached. Submitting assessment automatically.",
      "info"
    );

    // Auto-submit the assessment
    if (
      window.assessmentInterface &&
      typeof window.assessmentInterface.autoSubmit === "function"
    ) {
      window.assessmentInterface.autoSubmit("time_expired");
    }
  }

  handleSessionExpired() {
    this.logActivity("session_expired");
    this.showNotification(
      "Session expired. Redirecting to assessment page.",
      "error"
    );

    setTimeout(() => {
      window.location.href = this.config.assessmentUrl || "/";
    }, 3000);
  }

  handleSessionError(message) {
    this.logActivity("session_error", { message });
    this.showNotification(message, "error");

    // Redirect after a delay
    setTimeout(() => {
      window.location.href = this.config.assessmentUrl || "/";
    }, 5000);
  }

  showTimeWarning(remainingSeconds) {
    const minutes = Math.floor(remainingSeconds / 60);
    const seconds = Math.floor(remainingSeconds % 60);

    if (remainingSeconds <= 60) {
      this.showNotification(
        `Time remaining: ${seconds} seconds`,
        "warning",
        false // Don't auto-hide
      );
    } else if (remainingSeconds <= 300 && remainingSeconds % 60 === 0) {
      this.showNotification(`Time remaining: ${minutes} minutes`, "warning");
    }
  }

  showNotification(message, type = "info", autoHide = true) {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll(
      ".security-notification"
    );
    existingNotifications.forEach((notification) => notification.remove());

    // Create notification element
    const notification = document.createElement("div");
    notification.className = `security-notification security-notification--${type}`;
    notification.innerHTML = `
            <div class="security-notification__content">
                <span class="security-notification__message">${message}</span>
                <button class="security-notification__close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;

    // Add styles
    notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            animation: slideInRight 0.3s ease-out;
        `;

    // Set colors based on type
    const colors = {
      info: { bg: "#e3f2fd", border: "#2196f3", text: "#1565c0" },
      warning: { bg: "#fff3e0", border: "#ff9800", text: "#ef6c00" },
      error: { bg: "#ffebee", border: "#f44336", text: "#c62828" },
    };

    const color = colors[type] || colors.info;
    notification.style.backgroundColor = color.bg;
    notification.style.borderLeft = `4px solid ${color.border}`;
    notification.style.color = color.text;

    // Add to page
    document.body.appendChild(notification);

    // Auto-hide after 5 seconds if enabled
    if (autoHide) {
      setTimeout(() => {
        if (notification.parentElement) {
          notification.remove();
        }
      }, 5000);
    }
  }

  updateLastActivity() {
    this.lastActivityTime = Date.now();
  }

  getActivitySummary() {
    return {
      totalActivities: this.activityLog.length,
      tabSwitchCount: this.tabSwitchCount,
      copyPasteCount: this.copyPasteCount,
      suspiciousActivityCount: this.suspiciousActivityCount,
      sessionDuration: Date.now() - this.sessionStartTime,
      lastActivity: this.lastActivityTime,
    };
  }

  // Method to be called when saving progress
  getSecurityData() {
    return {
      activitySummary: this.getActivitySummary(),
      recentActivities: this.activityLog.slice(-50), // Last 50 activities
      sessionToken: this.generateSessionToken(),
    };
  }

  generateSessionToken() {
    // Simple client-side token for additional validation
    const data = `${this.config.userId}_${this.config.setId}_${this.sessionStartTime}`;
    return btoa(data);
  }

  // Handle section-specific validation results
  handleSectionValidation(validationData, currentSection) {
    // Update section progress if provided
    if (validationData.section_progress) {
      this.updateSectionProgress(validationData.section_progress);
    }

    // Log section validation
    this.logActivity("section_validated", {
      section: currentSection,
      display_format: validationData.display_format,
      sections_enabled: validationData.sections_enabled,
    });
  }

  // Update section progress display
  updateSectionProgress(sectionProgress) {
    // This would integrate with the question navigation panel
    if (window.questionNavigationPanel) {
      window.questionNavigationPanel.updateSectionProgress(sectionProgress);
    }
  }

  // Update time remaining display
  updateTimeRemaining(timeRemaining) {
    if (timeRemaining !== null && timeRemaining >= 0) {
      const minutes = Math.floor(timeRemaining / 60);
      const seconds = timeRemaining % 60;

      // Update time display if element exists
      const timeDisplay = document.querySelector(".assessment-time-remaining");
      if (timeDisplay) {
        timeDisplay.textContent = `${minutes}:${seconds
          .toString()
          .padStart(2, "0")}`;

        // Add warning class if time is running low
        if (timeRemaining <= 300) {
          // 5 minutes
          timeDisplay.classList.add("time-warning");
        }
        if (timeRemaining <= 60) {
          // 1 minute
          timeDisplay.classList.add("time-critical");
        }
      }
    }
  }

  // Enhanced activity logging for sectioned assessments
  logSectionActivity(sectionId, activityType, data = {}) {
    const sectionActivity = {
      type: `section_${activityType}`,
      timestamp: Date.now(),
      data: {
        section_id: sectionId,
        ...data,
      },
    };

    this.activityLog.push(sectionActivity);
    this.sendActivityToServer(sectionActivity);
  }

  // Track section entry/exit
  onSectionEnter(sectionId) {
    this.logSectionActivity(sectionId, "entered", {
      previous_section: this.currentSection,
      entry_time: Date.now(),
    });
    this.currentSection = sectionId;
  }

  onSectionExit(sectionId) {
    this.logSectionActivity(sectionId, "exited", {
      exit_time: Date.now(),
      time_spent: Date.now() - (this.sectionStartTime || Date.now()),
    });
  }

  // Enhanced cheating detection for different formats
  detectFormatSpecificCheating() {
    const displayFormat = this.config.displayFormat || "next_next";

    if (displayFormat === "next_next") {
      this.detectNextNextCheating();
    } else if (displayFormat === "single_page") {
      this.detectSinglePageCheating();
    }
  }

  detectNextNextCheating() {
    // Check for rapid question navigation
    const recentNavigation = this.activityLog.filter(
      (activity) =>
        activity.type === "question_navigation" &&
        Date.now() - activity.timestamp < 60000 // Last minute
    );

    if (recentNavigation.length > 15) {
      this.handleSuspiciousActivity("rapid_navigation_next_next", {
        count: recentNavigation.length,
        format: "next_next",
      });
    }

    // Check for answers without sufficient time on question
    const quickAnswers = this.activityLog.filter(
      (activity) =>
        activity.type === "answer_changed" &&
        activity.data.time_on_question &&
        activity.data.time_on_question < 3000 && // Less than 3 seconds
        Date.now() - activity.timestamp < 300000 // Last 5 minutes
    );

    if (quickAnswers.length > 5) {
      this.handleSuspiciousActivity("rapid_answers_next_next", {
        count: quickAnswers.length,
        format: "next_next",
      });
    }
  }

  detectSinglePageCheating() {
    // Check for excessive scrolling
    const recentScrolling = this.activityLog.filter(
      (activity) =>
        activity.type === "page_scroll" &&
        Date.now() - activity.timestamp < 120000 // Last 2 minutes
    );

    if (recentScrolling.length > 50) {
      this.handleSuspiciousActivity("excessive_scrolling_single_page", {
        count: recentScrolling.length,
        format: "single_page",
      });
    }

    // Check for bulk answer selection
    const recentAnswers = this.activityLog.filter(
      (activity) =>
        activity.type === "answer_changed" &&
        Date.now() - activity.timestamp < 30000 // Last 30 seconds
    );

    if (recentAnswers.length > 8) {
      this.handleSuspiciousActivity("bulk_answers_single_page", {
        count: recentAnswers.length,
        time_span: 30,
        format: "single_page",
      });
    }
  }

  // Enhanced periodic validation with section awareness
  setupPeriodicValidation() {
    // Validate session with server every 2 minutes
    setInterval(() => {
      const currentSection = this.getCurrentSection();
      this.validateSessionWithServer(currentSection);
      this.detectFormatSpecificCheating();
    }, 120000);
  }

  // Get current section from assessment interface
  getCurrentSection() {
    // This would integrate with the assessment interface to get current section
    if (
      window.assessmentInterface &&
      typeof window.assessmentInterface.getCurrentSection === "function"
    ) {
      return window.assessmentInterface.getCurrentSection();
    }
    return this.currentSection || null;
  }

  // Enhanced progress saving with security data and section awareness
  saveSecureProgress(progressData) {
    if (!this.config.ajaxUrl || !this.config.nonce) {
      return Promise.reject("Security configuration missing");
    }

    const securityData = this.getSecurityData();

    const formData = new FormData();
    formData.append("action", "mcqhome_save_secure_progress");
    formData.append("nonce", this.config.nonce);
    formData.append("mcq_set_id", this.config.setId);
    formData.append("current_question", progressData.currentQuestion);
    formData.append("answers", JSON.stringify(progressData.answers));
    formData.append("skipped", JSON.stringify(progressData.skipped));
    formData.append("progress_percentage", progressData.progressPercentage);
    formData.append("security_data", JSON.stringify(securityData));

    // Add section-specific data if available
    if (progressData.currentSection) {
      formData.append("current_section", progressData.currentSection);
    }
    if (progressData.sectionProgress) {
      formData.append(
        "section_progress",
        JSON.stringify(progressData.sectionProgress)
      );
    }
    if (progressData.sectionTimes) {
      formData.append(
        "section_times",
        JSON.stringify(progressData.sectionTimes)
      );
    }

    return fetch(this.config.ajaxUrl, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (!data.success) {
          throw new Error(data.data.message || "Failed to save progress");
        }
        return data.data;
      });
  }

  // Enhanced assessment submission with security validation
  submitSecureAssessment(answers, timeData) {
    if (!this.config.ajaxUrl || !this.config.nonce) {
      return Promise.reject("Security configuration missing");
    }

    const securityData = this.getSecurityData();

    const formData = new FormData();
    formData.append("action", "mcqhome_submit_assessment");
    formData.append("nonce", this.config.nonce);
    formData.append("mcq_set_id", this.config.setId);
    formData.append("answers", JSON.stringify(answers));
    formData.append("time_taken", timeData.timeTaken);
    formData.append("client_time", new Date().toISOString());
    formData.append("security_data", JSON.stringify(securityData));

    return fetch(this.config.ajaxUrl, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (!data.success) {
          throw new Error(data.data.message || "Failed to submit assessment");
        }
        return data.data;
      });
  }
}

// Add CSS animations
const style = document.createElement("style");
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .security-notification__content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .security-notification__close {
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        padding: 0;
        margin-left: 10px;
        opacity: 0.7;
    }

    .security-notification__close:hover {
        opacity: 1;
    }
`;
document.head.appendChild(style);

// Export for use in other scripts
window.AssessmentSecurity = AssessmentSecurity;
