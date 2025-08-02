/**
 * Dashboard JavaScript for MCQHome Theme
 */

(function ($) {
  "use strict";

  // Dashboard object
  const MCQHomeDashboard = {
    init: function () {
      this.bindEvents();
      this.initComponents();
      this.initResponsiveFeatures();
    },

    bindEvents: function () {
      // Follow/Unfollow buttons
      $(document).on(
        "click",
        ".follow-institution-btn",
        this.followInstitution
      );
      $(document).on(
        "click",
        ".unfollow-institution-btn",
        this.unfollowInstitution
      );
      $(document).on("click", ".follow-teacher-btn", this.followTeacher);
      $(document).on("click", ".unfollow-teacher-btn", this.unfollowTeacher);

      // Enrollment buttons
      $(document).on("click", ".enroll-mcq-set-btn", this.enrollMCQSet);

      // Load more buttons
      $(document).on("click", ".load-more-btn", this.loadMoreContent);

      // Refresh stats
      $(document).on("click", ".refresh-stats-btn", this.refreshStats);

      // Tab switching
      $(document).on("click", ".dashboard-tab", this.switchTab);

      // Notification handling
      $(document).on(
        "click",
        ".mark-notification-read-btn",
        this.markNotificationRead
      );
      $(document).on(
        "click",
        ".load-more-notifications-btn",
        this.loadMoreNotifications
      );

      // Activity feed handling
      $(document).on("click", ".load-more-activity-btn", this.loadMoreActivity);
    },

    initComponents: function () {
      // Initialize tooltips if available
      if (typeof tippy !== "undefined") {
        tippy("[data-tippy-content]");
      }

      // Initialize progress bars animation
      this.animateProgressBars();

      // Auto-refresh stats every 5 minutes
      setInterval(this.refreshStats, 300000);

      // Auto-refresh notifications every 2 minutes
      setInterval(this.refreshNotifications, 120000);

      // Load initial notifications count
      this.refreshNotifications();
    },

    initResponsiveFeatures: function () {
      // Handle mobile navigation for dashboard tabs
      this.initMobileTabNavigation();

      // Handle responsive card layouts
      this.handleResponsiveCards();

      // Handle touch interactions
      this.initTouchInteractions();

      // Handle orientation changes
      this.handleOrientationChange();
    },

    initMobileTabNavigation: function () {
      const tabContainer = document.querySelector(".dashboard-tabs");
      if (!tabContainer) return;

      // Add scroll indicators for mobile tab navigation
      const scrollIndicator = document.createElement("div");
      scrollIndicator.className = "tab-scroll-indicator hidden";
      scrollIndicator.innerHTML = "→";
      tabContainer.parentNode.appendChild(scrollIndicator);

      // Show/hide scroll indicator based on scroll position
      tabContainer.addEventListener("scroll", function () {
        const isScrollable =
          tabContainer.scrollWidth > tabContainer.clientWidth;
        const isAtEnd =
          tabContainer.scrollLeft >=
          tabContainer.scrollWidth - tabContainer.clientWidth - 10;

        if (isScrollable && !isAtEnd) {
          scrollIndicator.classList.remove("hidden");
        } else {
          scrollIndicator.classList.add("hidden");
        }
      });

      // Initial check
      if (tabContainer.scrollWidth > tabContainer.clientWidth) {
        scrollIndicator.classList.remove("hidden");
      }
    },

    handleResponsiveCards: function () {
      // Adjust card layouts based on screen size
      const adjustCardLayouts = () => {
        const cards = document.querySelectorAll(".dashboard-card");
        const isMobile = window.innerWidth < 768;

        cards.forEach((card) => {
          if (isMobile) {
            card.classList.add("mobile-card");
          } else {
            card.classList.remove("mobile-card");
          }
        });
      };

      // Initial adjustment
      adjustCardLayouts();

      // Adjust on resize
      window.addEventListener("resize", adjustCardLayouts);
    },

    initTouchInteractions: function () {
      // Improve touch interactions for mobile devices
      if ("ontouchstart" in window) {
        // Add touch-friendly classes
        document.body.classList.add("touch-device");

        // Handle touch feedback for buttons
        const buttons = document.querySelectorAll(
          ".btn-primary, .btn-secondary, .btn-success"
        );
        buttons.forEach((button) => {
          button.addEventListener("touchstart", function () {
            this.classList.add("touch-active");
          });

          button.addEventListener("touchend", function () {
            setTimeout(() => {
              this.classList.remove("touch-active");
            }, 150);
          });
        });
      }
    },

    handleOrientationChange: function () {
      // Handle orientation changes on mobile devices
      window.addEventListener("orientationchange", function () {
        setTimeout(() => {
          // Recalculate layouts after orientation change
          MCQHomeDashboard.handleResponsiveCards();

          // Refresh any charts or visualizations
          const charts = document.querySelectorAll(".performance-chart");
          charts.forEach((chart) => {
            // Trigger resize event for charts
            if (chart.chart && typeof chart.chart.resize === "function") {
              chart.chart.resize();
            }
          });
        }, 100);
      });
    },

    followInstitution: function (e) {
      e.preventDefault();

      const $btn = $(this);
      const institutionId = $btn.data("institution-id");

      if (!institutionId) {
        MCQHomeDashboard.showNotification("error", "Institution ID not found.");
        return;
      }

      $btn.prop("disabled", true).text("Following...");

      $.ajax({
        url: mcqhome_ajax.ajax_url,
        type: "POST",
        data: {
          action: "mcqhome_follow_institution",
          institution_id: institutionId,
          nonce: mcqhome_ajax.nonce,
        },
        success: function (response) {
          if (response.success) {
            $btn
              .removeClass("follow-institution-btn btn-primary")
              .addClass("unfollow-institution-btn btn-secondary")
              .text("Unfollow")
              .prop("disabled", false);

            MCQHomeDashboard.showNotification("success", response.data.message);
            MCQHomeDashboard.updateFollowingCount(1);
          } else {
            MCQHomeDashboard.showNotification("error", response.data);
            $btn.prop("disabled", false).text("Follow");
          }
        },
        error: function () {
          MCQHomeDashboard.showNotification(
            "error",
            "An error occurred. Please try again."
          );
          $btn.prop("disabled", false).text("Follow");
        },
      });
    },

    unfollowInstitution: function (e) {
      e.preventDefault();

      const $btn = $(this);
      const institutionId = $btn.data("institution-id");

      if (!institutionId) {
        MCQHomeDashboard.showNotification("error", "Institution ID not found.");
        return;
      }

      $btn.prop("disabled", true).text("Unfollowing...");

      $.ajax({
        url: mcqhome_ajax.ajax_url,
        type: "POST",
        data: {
          action: "mcqhome_unfollow_institution",
          institution_id: institutionId,
          nonce: mcqhome_ajax.nonce,
        },
        success: function (response) {
          if (response.success) {
            $btn
              .removeClass("unfollow-institution-btn btn-secondary")
              .addClass("follow-institution-btn btn-primary")
              .text("Follow")
              .prop("disabled", false);

            MCQHomeDashboard.showNotification("success", response.data.message);
            MCQHomeDashboard.updateFollowingCount(-1);
          } else {
            MCQHomeDashboard.showNotification("error", response.data);
            $btn.prop("disabled", false).text("Unfollow");
          }
        },
        error: function () {
          MCQHomeDashboard.showNotification(
            "error",
            "An error occurred. Please try again."
          );
          $btn.prop("disabled", false).text("Unfollow");
        },
      });
    },

    followTeacher: function (e) {
      e.preventDefault();

      const $btn = $(this);
      const teacherId = $btn.data("teacher-id");

      if (!teacherId) {
        MCQHomeDashboard.showNotification("error", "Teacher ID not found.");
        return;
      }

      $btn.prop("disabled", true).text("Following...");

      $.ajax({
        url: mcqhome_ajax.ajax_url,
        type: "POST",
        data: {
          action: "mcqhome_follow_teacher",
          teacher_id: teacherId,
          nonce: mcqhome_ajax.nonce,
        },
        success: function (response) {
          if (response.success) {
            $btn
              .removeClass("follow-teacher-btn btn-primary")
              .addClass("unfollow-teacher-btn btn-secondary")
              .text("Unfollow")
              .prop("disabled", false);

            MCQHomeDashboard.showNotification("success", response.data.message);
            MCQHomeDashboard.updateFollowingCount(1);
          } else {
            MCQHomeDashboard.showNotification("error", response.data);
            $btn.prop("disabled", false).text("Follow");
          }
        },
        error: function () {
          MCQHomeDashboard.showNotification(
            "error",
            "An error occurred. Please try again."
          );
          $btn.prop("disabled", false).text("Follow");
        },
      });
    },

    unfollowTeacher: function (e) {
      e.preventDefault();

      const $btn = $(this);
      const teacherId = $btn.data("teacher-id");

      if (!teacherId) {
        MCQHomeDashboard.showNotification("error", "Teacher ID not found.");
        return;
      }

      $btn.prop("disabled", true).text("Unfollowing...");

      $.ajax({
        url: mcqhome_ajax.ajax_url,
        type: "POST",
        data: {
          action: "mcqhome_unfollow_teacher",
          teacher_id: teacherId,
          nonce: mcqhome_ajax.nonce,
        },
        success: function (response) {
          if (response.success) {
            $btn
              .removeClass("unfollow-teacher-btn btn-secondary")
              .addClass("follow-teacher-btn btn-primary")
              .text("Follow")
              .prop("disabled", false);

            MCQHomeDashboard.showNotification("success", response.data.message);
            MCQHomeDashboard.updateFollowingCount(-1);
          } else {
            MCQHomeDashboard.showNotification("error", response.data);
            $btn.prop("disabled", false).text("Unfollow");
          }
        },
        error: function () {
          MCQHomeDashboard.showNotification(
            "error",
            "An error occurred. Please try again."
          );
          $btn.prop("disabled", false).text("Unfollow");
        },
      });
    },

    enrollMCQSet: function (e) {
      e.preventDefault();

      const $btn = $(this);
      const mcqSetId = $btn.data("mcq-set-id");

      if (!mcqSetId) {
        MCQHomeDashboard.showNotification("error", "MCQ Set ID not found.");
        return;
      }

      $btn.prop("disabled", true).text("Enrolling...");

      $.ajax({
        url: mcqhome_ajax.ajax_url,
        type: "POST",
        data: {
          action: "mcqhome_enroll_mcq_set",
          mcq_set_id: mcqSetId,
          nonce: mcqhome_ajax.nonce,
        },
        success: function (response) {
          if (response.success) {
            $btn
              .removeClass("enroll-mcq-set-btn btn-primary")
              .addClass("btn-success")
              .text("Enrolled")
              .prop("disabled", true);

            MCQHomeDashboard.showNotification("success", response.data.message);
            MCQHomeDashboard.updateEnrolledCount(1);

            // Redirect to the MCQ set after a short delay
            setTimeout(function () {
              window.location.href = $btn.data("redirect-url") || "#";
            }, 1500);
          } else {
            MCQHomeDashboard.showNotification("error", response.data);
            $btn.prop("disabled", false).text("Enroll");
          }
        },
        error: function () {
          MCQHomeDashboard.showNotification(
            "error",
            "An error occurred. Please try again."
          );
          $btn.prop("disabled", false).text("Enroll");
        },
      });
    },

    loadMoreContent: function (e) {
      e.preventDefault();

      const $btn = $(this);
      const contentType = $btn.data("content-type");
      const currentPage = parseInt($btn.data("page")) || 1;
      const nextPage = currentPage + 1;
      const $container = $btn
        .closest(".dashboard-section")
        .find(".content-container");

      $btn.prop("disabled", true).text("Loading...");

      $.ajax({
        url: mcqhome_ajax.ajax_url,
        type: "POST",
        data: {
          action: "mcqhome_load_more_content",
          content_type: contentType,
          page: nextPage,
          per_page: 5,
          nonce: mcqhome_ajax.nonce,
        },
        success: function (response) {
          if (response.success && response.data.html) {
            $container.append(response.data.html);
            $btn.data("page", nextPage);
            $btn.prop("disabled", false).text("Load More");

            // Hide button if no more content
            if (response.data.html.trim() === "") {
              $btn.hide();
            }
          } else {
            $btn.hide();
          }
        },
        error: function () {
          MCQHomeDashboard.showNotification(
            "error",
            "Failed to load more content."
          );
          $btn.prop("disabled", false).text("Load More");
        },
      });
    },

    refreshStats: function () {
      const $statsContainer = $(".dashboard-stats");

      if ($statsContainer.length === 0) return;

      $.ajax({
        url: mcqhome_ajax.ajax_url,
        type: "POST",
        data: {
          action: "mcqhome_get_dashboard_stats",
          nonce: mcqhome_ajax.nonce,
        },
        success: function (response) {
          if (response.success) {
            const stats = response.data;

            // Update stat values
            $(".stat-enrolled-courses").text(stats.enrolled_courses);
            $(".stat-completed-mcqs").text(stats.completed_mcqs);
            $(".stat-average-score").text(stats.average_score + "%");
            $(".stat-following-count").text(stats.following_count);

            // Animate the updated values
            MCQHomeDashboard.animateStatValues();
          }
        },
      });
    },

    switchTab: function (e) {
      e.preventDefault();

      const $tab = $(this);
      const targetTab = $tab.data("tab");

      // Update active tab
      $(".dashboard-tab").removeClass("active");
      $tab.addClass("active");

      // Show/hide content
      $(".dashboard-tab-content").hide();
      $("#dashboard-tab-" + targetTab).show();

      // Update URL without page reload
      if (history.pushState) {
        const newUrl = window.location.pathname + "?tab=" + targetTab;
        history.pushState(null, null, newUrl);
      }
    },

    updateFollowingCount: function (change) {
      const $followingCount = $(".stat-following-count");
      const currentCount = parseInt($followingCount.text()) || 0;
      const newCount = Math.max(0, currentCount + change);

      $followingCount.text(newCount);
      this.animateStatValue($followingCount);
    },

    updateEnrolledCount: function (change) {
      const $enrolledCount = $(".stat-enrolled-courses");
      const currentCount = parseInt($enrolledCount.text()) || 0;
      const newCount = Math.max(0, currentCount + change);

      $enrolledCount.text(newCount);
      this.animateStatValue($enrolledCount);
    },

    animateProgressBars: function () {
      $(".progress-bar").each(function () {
        const $bar = $(this);
        const targetWidth =
          $bar.data("width") || $bar.attr("style").match(/width:\s*(\d+)%/);

        if (targetWidth) {
          const width =
            typeof targetWidth === "string" ? targetWidth[1] : targetWidth;
          $bar.css("width", "0%").animate(
            {
              width: width + "%",
            },
            1000
          );
        }
      });
    },

    animateStatValues: function () {
      $(".dashboard-stat-value").each(function () {
        MCQHomeDashboard.animateStatValue($(this));
      });
    },

    animateStatValue: function ($element) {
      $element.addClass("stat-updated");
      setTimeout(function () {
        $element.removeClass("stat-updated");
      }, 500);
    },

    markNotificationRead: function (e) {
      e.preventDefault();

      const $btn = $(this);
      const notificationId = $btn.data("notification-id");
      const $notificationItem = $btn.closest(".notification-item");

      $btn.prop("disabled", true).text("Marking...");

      $.ajax({
        url: mcqhome_ajax.ajax_url,
        type: "POST",
        data: {
          action: "mcqhome_mark_notification_read",
          notification_id: notificationId,
          nonce: mcqhome_ajax.nonce,
        },
        success: function (response) {
          if (response.success) {
            $notificationItem
              .removeClass("bg-blue-50 border-blue-200")
              .addClass("bg-white border-gray-200");
            $btn.remove();

            // Update notification count
            const $notificationCount = $(".notification-count");
            const currentCount = parseInt($notificationCount.text()) || 0;
            const newCount = Math.max(0, currentCount - 1);

            if (newCount === 0) {
              $notificationCount.addClass("hidden");
            } else {
              $notificationCount.text(newCount);
            }

            MCQHomeDashboard.showNotification("success", response.data.message);
          } else {
            MCQHomeDashboard.showNotification("error", response.data);
            $btn.prop("disabled", false).text("Mark as read");
          }
        },
        error: function () {
          MCQHomeDashboard.showNotification(
            "error",
            "Failed to mark notification as read."
          );
          $btn.prop("disabled", false).text("Mark as read");
        },
      });
    },

    loadMoreNotifications: function (e) {
      e.preventDefault();

      const $btn = $(this);
      const userId = $btn.data("user-id");
      const currentPage = parseInt($btn.data("page")) || 1;
      const nextPage = currentPage + 1;
      const $container = $("#notifications-container");

      $btn.prop("disabled", true).text("Loading...");

      $.ajax({
        url: mcqhome_ajax.ajax_url,
        type: "POST",
        data: {
          action: "mcqhome_get_notifications",
          limit: 5,
          page: nextPage,
          nonce: mcqhome_ajax.nonce,
        },
        success: function (response) {
          if (response.success && response.data.notifications.length > 0) {
            // Render notifications
            response.data.notifications.forEach(function (notification) {
              $container.append(
                MCQHomeDashboard.renderNotificationItem(notification)
              );
            });

            $btn.data("page", nextPage);
            $btn.prop("disabled", false).text("Load more notifications");

            if (response.data.notifications.length < 5) {
              $btn.hide();
            }
          } else {
            $btn.hide();
          }
        },
        error: function () {
          MCQHomeDashboard.showNotification(
            "error",
            "Failed to load more notifications."
          );
          $btn.prop("disabled", false).text("Load more notifications");
        },
      });
    },

    loadMoreActivity: function (e) {
      e.preventDefault();

      const $btn = $(this);
      const userId = $btn.data("user-id");
      const currentPage = parseInt($btn.data("page")) || 1;
      const nextPage = currentPage + 1;
      const $container = $("#activity-feed-container");

      $btn.prop("disabled", true).text("Loading...");

      $.ajax({
        url: mcqhome_ajax.ajax_url,
        type: "POST",
        data: {
          action: "mcqhome_get_activity_feed",
          limit: 10,
          page: nextPage,
          nonce: mcqhome_ajax.nonce,
        },
        success: function (response) {
          if (response.success && response.data.activities.length > 0) {
            // Render activities
            response.data.activities.forEach(function (activity) {
              $container.append(MCQHomeDashboard.renderActivityItem(activity));
            });

            $btn.data("page", nextPage);
            $btn.prop("disabled", false).text("Load more activity");

            if (response.data.activities.length < 10) {
              $btn.hide();
            }
          } else {
            $btn.hide();
          }
        },
        error: function () {
          MCQHomeDashboard.showNotification(
            "error",
            "Failed to load more activity."
          );
          $btn.prop("disabled", false).text("Load more activity");
        },
      });
    },

    refreshNotifications: function () {
      $.ajax({
        url: mcqhome_ajax.ajax_url,
        type: "POST",
        data: {
          action: "mcqhome_get_notifications",
          limit: 1,
          unread_only: true,
          nonce: mcqhome_ajax.nonce,
        },
        success: function (response) {
          if (response.success) {
            const unreadCount = response.data.unread_count || 0;
            const $notificationCount = $(".notification-count");

            if (unreadCount > 0) {
              $notificationCount.text(unreadCount).removeClass("hidden");
            } else {
              $notificationCount.addClass("hidden");
            }
          }
        },
      });
    },

    renderNotificationItem: function (notification) {
      // This would be a more complex function to render notification HTML
      // For now, return a simple placeholder
      return (
        '<div class="notification-item">Notification: ' +
        notification.title +
        "</div>"
      );
    },

    renderActivityItem: function (activity) {
      // This would be a more complex function to render activity HTML
      // For now, return a simple placeholder
      return (
        '<div class="activity-item">Activity: ' +
        activity.activity_type +
        "</div>"
      );
    },

    showNotification: function (type, message) {
      // Create notification element
      const $notification = $(
        '<div class="mcqhome-notification mcqhome-notification-' +
          type +
          '">' +
          message +
          "</div>"
      );

      // Add to page
      if ($(".mcqhome-notifications").length === 0) {
        $("body").append('<div class="mcqhome-notifications"></div>');
      }

      $(".mcqhome-notifications").append($notification);

      // Show notification
      setTimeout(function () {
        $notification.addClass("show");
      }, 100);

      // Hide notification after 5 seconds (longer on mobile)
      const hideDelay = window.innerWidth < 768 ? 7000 : 5000;
      setTimeout(function () {
        $notification.removeClass("show");
        setTimeout(function () {
          $notification.remove();
        }, 300);
      }, hideDelay);
    },

    // Responsive utility functions
    isMobile: function () {
      return window.innerWidth < 768;
    },

    isTablet: function () {
      return window.innerWidth >= 768 && window.innerWidth < 1024;
    },

    isDesktop: function () {
      return window.innerWidth >= 1024;
    },

    // Handle responsive table/list views
    handleResponsiveTable: function (selector) {
      const tables = document.querySelectorAll(selector);

      tables.forEach((table) => {
        if (this.isMobile()) {
          // Convert table to card layout on mobile
          table.classList.add("mobile-table");
        } else {
          table.classList.remove("mobile-table");
        }
      });
    },

    // Optimize images for different screen sizes
    optimizeImages: function () {
      const images = document.querySelectorAll("img[data-src-mobile]");

      images.forEach((img) => {
        if (this.isMobile() && img.dataset.srcMobile) {
          img.src = img.dataset.srcMobile;
        } else if (img.dataset.srcDesktop) {
          img.src = img.dataset.srcDesktop;
        }
      });
    },
  };

  // Global functions for inline onclick handlers
  window.mcqhome_follow_institution = function (institutionId) {
    $(
      '.follow-institution-btn[data-institution-id="' + institutionId + '"]'
    ).trigger("click");
  };

  window.mcqhome_unfollow_institution = function (institutionId) {
    $(
      '.unfollow-institution-btn[data-institution-id="' + institutionId + '"]'
    ).trigger("click");
  };

  window.mcqhome_follow_teacher = function (teacherId) {
    $('.follow-teacher-btn[data-teacher-id="' + teacherId + '"]').trigger(
      "click"
    );
  };

  window.mcqhome_unfollow_teacher = function (teacherId) {
    $('.unfollow-teacher-btn[data-teacher-id="' + teacherId + '"]').trigger(
      "click"
    );
  };

  // Initialize when document is ready
  $(document).ready(function () {
    MCQHomeDashboard.init();
  });
})(jQuery);
