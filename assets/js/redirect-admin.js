/**
 * Admin JavaScript for MCQ Redirect Management
 *
 * @package MCQHome
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  /**
   * MCQ Redirect Admin functionality
   */
  const MCQRedirectAdmin = {
    /**
     * Initialize the admin interface
     */
    init: function () {
      this.bindEvents();
      this.createLoadingOverlay();
    },

    /**
     * Bind event handlers
     */
    bindEvents: function () {
      // Rebuild mappings button
      $("#rebuild-mappings").on("click", this.handleRebuildMappings.bind(this));

      // Clear orphaned MCQs button
      $("#clear-orphaned").on("click", this.handleClearOrphaned.bind(this));

      // Prevent form submission for AJAX actions
      $("form").on("submit", function (e) {
        const action = $('button[type="submit"]:focus').val();
        if (action === "rebuild_mappings" || action === "clear_orphaned") {
          e.preventDefault();
        }
      });
    },

    /**
     * Create loading overlay
     */
    createLoadingOverlay: function () {
      if ($(".mcq-loading-overlay").length === 0) {
        $("body").append(
          '<div class="mcq-loading-overlay">' +
            '<div class="mcq-loading-spinner"></div>' +
            "</div>"
        );
      }
    },

    /**
     * Show loading overlay
     */
    showLoading: function () {
      $(".mcq-loading-overlay").addClass("active");
    },

    /**
     * Hide loading overlay
     */
    hideLoading: function () {
      $(".mcq-loading-overlay").removeClass("active");
    },

    /**
     * Handle rebuild mappings action
     */
    handleRebuildMappings: function (e) {
      e.preventDefault();

      if (!confirm(mcqRedirectAdmin.confirmRebuild)) {
        return;
      }

      const $button = $(e.target);
      const originalText = $button.text();

      // Update button state
      $button.addClass("loading").text(mcqRedirectAdmin.rebuilding);
      this.showLoading();

      // Perform AJAX request
      $.ajax({
        url: mcqRedirectAdmin.ajaxUrl,
        type: "POST",
        data: {
          action: "rebuild_redirect_mappings",
          nonce: mcqRedirectAdmin.nonce,
        },
        success: function (response) {
          if (response.success) {
            MCQRedirectAdmin.showNotice(response.data.message, "success");
            // Reload page to show updated statistics
            setTimeout(function () {
              window.location.reload();
            }, 1500);
          } else {
            MCQRedirectAdmin.showNotice(
              response.data || mcqRedirectAdmin.error,
              "error"
            );
          }
        },
        error: function () {
          MCQRedirectAdmin.showNotice(mcqRedirectAdmin.error, "error");
        },
        complete: function () {
          $button.removeClass("loading").text(originalText);
          MCQRedirectAdmin.hideLoading();
        },
      });
    },

    /**
     * Handle clear orphaned MCQs action
     */
    handleClearOrphaned: function (e) {
      e.preventDefault();

      if (!confirm(mcqRedirectAdmin.confirmClear)) {
        return;
      }

      const $button = $(e.target);
      const originalText = $button.text();

      // Update button state
      $button.addClass("loading").text(mcqRedirectAdmin.clearing);
      this.showLoading();

      // Perform AJAX request
      $.ajax({
        url: mcqRedirectAdmin.ajaxUrl,
        type: "POST",
        data: {
          action: "clear_orphaned_mcqs",
          nonce: mcqRedirectAdmin.nonce,
        },
        success: function (response) {
          if (response.success) {
            MCQRedirectAdmin.showNotice(response.data.message, "success");
            // Reload page to show updated statistics
            setTimeout(function () {
              window.location.reload();
            }, 1500);
          } else {
            MCQRedirectAdmin.showNotice(
              response.data || mcqRedirectAdmin.error,
              "error"
            );
          }
        },
        error: function () {
          MCQRedirectAdmin.showNotice(mcqRedirectAdmin.error, "error");
        },
        complete: function () {
          $button.removeClass("loading").text(originalText);
          MCQRedirectAdmin.hideLoading();
        },
      });
    },

    /**
     * Show admin notice
     */
    showNotice: function (message, type) {
      type = type || "info";

      // Remove existing notices
      $(".mcq-admin-notice").remove();

      // Create new notice
      const $notice = $(
        '<div class="notice notice-' +
          type +
          ' is-dismissible mcq-admin-notice">' +
          "<p>" +
          message +
          "</p>" +
          '<button type="button" class="notice-dismiss">' +
          '<span class="screen-reader-text">Dismiss this notice.</span>' +
          "</button>" +
          "</div>"
      );

      // Insert notice
      $(".wrap h1").after($notice);

      // Handle dismiss button
      $notice.find(".notice-dismiss").on("click", function () {
        $notice.fadeOut(300, function () {
          $(this).remove();
        });
      });

      // Auto-dismiss success notices
      if (type === "success") {
        setTimeout(function () {
          $notice.fadeOut(300, function () {
            $(this).remove();
          });
        }, 5000);
      }

      // Scroll to notice
      $("html, body").animate(
        {
          scrollTop: $notice.offset().top - 50,
        },
        300
      );
    },

    /**
     * Format numbers with commas
     */
    formatNumber: function (num) {
      return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },

    /**
     * Update statistics display
     */
    updateStatistics: function (stats) {
      if (stats.total_mappings !== undefined) {
        $(".mcq-stat-item:first .mcq-stat-number").text(
          this.formatNumber(stats.total_mappings)
        );
      }

      if (stats.orphaned_count !== undefined) {
        $(".mcq-stat-item:nth-child(2) .mcq-stat-number").text(
          this.formatNumber(stats.orphaned_count)
        );
      }
    },
  };

  /**
   * Initialize when document is ready
   */
  $(document).ready(function () {
    MCQRedirectAdmin.init();
  });

  /**
   * Handle page visibility changes to refresh data
   */
  $(document).on("visibilitychange", function () {
    if (!document.hidden) {
      // Page became visible, refresh statistics if needed
      const lastUpdate = localStorage.getItem("mcq_redirect_last_update");
      const now = Date.now();

      // Refresh if more than 5 minutes have passed
      if (!lastUpdate || now - parseInt(lastUpdate) > 300000) {
        // Could implement auto-refresh here if needed
      }
    }
  });

  /**
   * Keyboard shortcuts
   */
  $(document).on("keydown", function (e) {
    // Ctrl/Cmd + R for rebuild (when focused on admin page)
    if (
      (e.ctrlKey || e.metaKey) &&
      e.key === "r" &&
      $(".mcq-redirect-admin").length > 0
    ) {
      e.preventDefault();
      $("#rebuild-mappings").click();
    }
  });

  /**
   * Enhanced table interactions
   */
  $(document).on("click", ".wp-list-table tbody tr", function (e) {
    // Don't trigger if clicking on a button or link
    if ($(e.target).is("a, button, input")) {
      return;
    }

    // Highlight row
    $(this).toggleClass("selected");
  });

  /**
   * Tooltip functionality for help text
   */
  $(document).on("mouseenter", "[data-tooltip]", function () {
    const tooltip = $(this).data("tooltip");
    if (tooltip) {
      const $tooltip = $('<div class="mcq-tooltip">' + tooltip + "</div>");
      $("body").append($tooltip);

      const offset = $(this).offset();
      $tooltip.css({
        top: offset.top - $tooltip.outerHeight() - 10,
        left:
          offset.left + $(this).outerWidth() / 2 - $tooltip.outerWidth() / 2,
      });
    }
  });

  $(document).on("mouseleave", "[data-tooltip]", function () {
    $(".mcq-tooltip").remove();
  });
})(jQuery);
