/**
 * Browse and Discovery System JavaScript
 *
 * @package MCQHome
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  // Initialize browse functionality
  $(document).ready(function () {
    initFollowButtons();
    initViewToggle();
    initSearchFilters();
    initCategoryNavigation();
    initAdvancedFilters();
  });

  /**
   * Initialize follow/unfollow buttons
   */
  function initFollowButtons() {
    $(document).on(
      "click",
      ".follow-institution-btn, .follow-teacher-btn",
      function (e) {
        e.preventDefault();

        const $button = $(this);
        const isInstitution = $button.hasClass("follow-institution-btn");
        const followedId = $button.data(
          isInstitution ? "institution-id" : "teacher-id"
        );
        const followedType = isInstitution ? "institution" : "teacher";

        // Disable button during request
        $button.prop("disabled", true);

        // Show loading state
        const originalText = $button.html();
        $button.html(
          '<i class="fas fa-spinner fa-spin mr-2"></i>' + mcqhome_browse.loading
        );

        $.ajax({
          url: mcqhome_ajax.ajax_url,
          type: "POST",
          data: {
            action: "mcqhome_toggle_follow",
            followed_id: followedId,
            followed_type: followedType,
            nonce: mcqhome_ajax.nonce,
          },
          success: function (response) {
            if (response.success) {
              if (response.data.action === "followed") {
                $button
                  .removeClass("bg-white text-blue-600 hover:bg-blue-50")
                  .addClass("bg-blue-600 text-white hover:bg-blue-700")
                  .html(
                    '<i class="fas fa-check mr-2"></i>' +
                      mcqhome_browse.following
                  );
              } else {
                $button
                  .removeClass("bg-blue-600 text-white hover:bg-blue-700")
                  .addClass("bg-white text-blue-600 hover:bg-blue-50")
                  .html(
                    '<i class="fas fa-plus mr-2"></i>' + mcqhome_browse.follow
                  );
              }

              // Show success message
              showNotification(response.data.message, "success");

              // Update follower count if visible
              updateFollowerCount(
                followedId,
                followedType,
                response.data.action === "followed" ? 1 : -1
              );
            } else {
              showNotification(response.data || mcqhome_browse.error, "error");
              $button.html(originalText);
            }
          },
          error: function () {
            showNotification(mcqhome_browse.error, "error");
            $button.html(originalText);
          },
          complete: function () {
            $button.prop("disabled", false);
          },
        });
      }
    );
  }

  /**
   * Initialize view toggle (grid/list)
   */
  function initViewToggle() {
    $(".view-grid-btn, .view-list-btn").on("click", function () {
      const $button = $(this);
      const isGrid = $button.hasClass("view-grid-btn");

      // Update button states
      $(".view-grid-btn, .view-list-btn")
        .removeClass("bg-blue-600 text-white")
        .addClass("bg-gray-300 text-gray-700");
      $button
        .removeClass("bg-gray-300 text-gray-700")
        .addClass("bg-blue-600 text-white");

      // Update view
      const $resultsGrid = $(".results-grid");
      if (isGrid) {
        $resultsGrid
          .removeClass("grid-cols-1")
          .addClass("grid-cols-1 md:grid-cols-2 lg:grid-cols-3");
        $(".content-card").removeClass("flex").addClass("block");
      } else {
        $resultsGrid
          .removeClass("md:grid-cols-2 lg:grid-cols-3")
          .addClass("grid-cols-1");
        $(".content-card").removeClass("block").addClass("flex");
      }

      // Save preference
      localStorage.setItem("mcqhome_browse_view", isGrid ? "grid" : "list");
    });

    // Load saved preference
    const savedView = localStorage.getItem("mcqhome_browse_view");
    if (savedView === "list") {
      $(".view-list-btn").trigger("click");
    }
  }

  /**
   * Initialize search filters
   */
  function initSearchFilters() {
    // Auto-submit on filter change
    $(".mcq-search-form select, .institution-search-form select").on(
      "change",
      function () {
        // Add a small delay to allow for multiple quick changes
        clearTimeout(window.filterTimeout);
        window.filterTimeout = setTimeout(
          function () {
            $(this).closest("form").submit();
          }.bind(this),
          300
        );
      }
    );

    // Search input with debounce
    $(
      '.mcq-search-form input[name="search"], .institution-search-form input[name="search"]'
    ).on("input", function () {
      const $input = $(this);
      clearTimeout(window.searchTimeout);

      window.searchTimeout = setTimeout(function () {
        // Only auto-submit if there's content or if clearing
        if ($input.val().length >= 3 || $input.val().length === 0) {
          $input.closest("form").submit();
        }
      }, 500);
    });

    // Category quick filters
    $(".subject-card, .difficulty-tag").on("click", function (e) {
      e.preventDefault();

      const href = $(this).attr("href");
      if (href) {
        window.location.href = href;
      }
    });
  }

  /**
   * Update follower count display
   */
  function updateFollowerCount(id, type, change) {
    const selector =
      type === "institution"
        ? `.institution-card[data-id="${id}"] .stat-item:last-child .stat-number, .institution-profile .stat-item:last-child .stat-number`
        : `.teacher-card[data-id="${id}"] .stat-item:last-child .stat-number, .teacher-profile .stat-item:last-child .stat-number`;

    const $counter = $(selector);
    if ($counter.length) {
      const currentCount = parseInt($counter.text()) || 0;
      const newCount = Math.max(0, currentCount + change);
      $counter.text(newCount);
    }
  }

  /**
   * Show notification message
   */
  function showNotification(message, type = "info") {
    // Remove existing notifications
    $(".mcqhome-notification").remove();

    const typeClasses = {
      success: "bg-green-500 text-white",
      error: "bg-red-500 text-white",
      info: "bg-blue-500 text-white",
      warning: "bg-yellow-500 text-black",
    };

    const $notification = $(`
            <div class="mcqhome-notification fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg ${typeClasses[type]} max-w-sm">
                <div class="flex items-center justify-between">
                    <span>${message}</span>
                    <button class="ml-4 text-current opacity-70 hover:opacity-100">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `);

    $("body").append($notification);

    // Auto-hide after 5 seconds
    setTimeout(function () {
      $notification.fadeOut(300, function () {
        $(this).remove();
      });
    }, 5000);

    // Manual close
    $notification.find("button").on("click", function () {
      $notification.fadeOut(300, function () {
        $(this).remove();
      });
    });
  }

  /**
   * Initialize infinite scroll (optional enhancement)
   */
  function initInfiniteScroll() {
    if (!$(".pagination-wrapper").length) return;

    let loading = false;
    let page = 2;
    const $loadMore = $(
      '<button class="load-more-btn bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors mx-auto block mt-8">' +
        mcqhome_browse.loadMore +
        "</button>"
    );

    $(".pagination-wrapper").after($loadMore);
    $(".pagination-wrapper").hide();

    $loadMore.on("click", function () {
      if (loading) return;

      loading = true;
      $loadMore.html(
        '<i class="fas fa-spinner fa-spin mr-2"></i>' + mcqhome_browse.loading
      );

      const currentUrl = new URL(window.location);
      currentUrl.searchParams.set("paged", page);

      $.get(currentUrl.toString())
        .done(function (data) {
          const $newContent = $(data).find(".results-grid .content-card");
          if ($newContent.length) {
            $(".results-grid").append($newContent);
            page++;
            $loadMore.html(mcqhome_browse.loadMore);
          } else {
            $loadMore.html(mcqhome_browse.noMore).prop("disabled", true);
          }
        })
        .fail(function () {
          showNotification(mcqhome_browse.error, "error");
          $loadMore.html(mcqhome_browse.loadMore);
        })
        .always(function () {
          loading = false;
        });
    });
  }

  /**
   * Initialize category navigation features
   */
  function initCategoryNavigation() {
    // Toggle topics visibility
    $(".toggle-topics").on("click", function (e) {
      e.preventDefault();
      const subjectId = $(this).data("subject");
      const $topicsList = $("#topics-" + subjectId);
      const $icon = $(this).find("i");

      $topicsList.toggleClass("hidden");

      if ($topicsList.hasClass("hidden")) {
        $icon.removeClass("fa-chevron-up").addClass("fa-chevron-down");
      } else {
        $icon.removeClass("fa-chevron-down").addClass("fa-chevron-up");
      }
    });

    // View all subjects modal/expansion
    $(".view-all-subjects").on("click", function (e) {
      e.preventDefault();
      // Could implement a modal or expand the subjects list
      // For now, redirect to a subjects archive page
      window.location.href = "/browse/?view=subjects";
    });

    // Subject/topic filtering integration
    $("#subject").on("change", function () {
      const selectedSubject = $(this).val();
      const $topicSelect = $("#topic");

      if (selectedSubject) {
        // Filter topics based on selected subject
        // This would require AJAX to get related topics
        loadRelatedTopics(selectedSubject, $topicSelect);
      } else {
        // Reset topic options to show all
        resetTopicOptions($topicSelect);
      }
    });
  }

  /**
   * Initialize advanced filters functionality
   */
  function initAdvancedFilters() {
    // Toggle advanced filters visibility
    $(".toggle-advanced-filters").on("click", function (e) {
      e.preventDefault();
      const $advancedFilters = $(".advanced-filters");
      const $toggleText = $(this).find(".toggle-text");
      const $icon = $(this).find("i");

      $advancedFilters.toggleClass("hidden");

      if ($advancedFilters.hasClass("hidden")) {
        $toggleText.text(mcqhome_browse.advancedFilters || "Advanced Filters");
        $icon.removeClass("fa-chevron-up").addClass("fa-chevron-down");
      } else {
        $toggleText.text(mcqhome_browse.hideFilters || "Hide Filters");
        $icon.removeClass("fa-chevron-down").addClass("fa-chevron-up");
      }
    });

    // Smart filter dependencies
    $("#content_type").on("change", function () {
      const contentType = $(this).val();
      const $minQuestions = $("#min_questions");

      if (contentType === "mcq") {
        // Hide question count filter for individual MCQs
        $minQuestions.closest("div").hide();
        $minQuestions.val("");
      } else {
        $minQuestions.closest("div").show();
      }
    });

    // Institution-teacher relationship
    $("#institution").on("change", function () {
      const institutionId = $(this).val();
      const $teacherSelect = $("#teacher");

      if (institutionId) {
        loadInstitutionTeachers(institutionId, $teacherSelect);
      } else {
        resetTeacherOptions($teacherSelect);
      }
    });

    // Filter presets
    initFilterPresets();
  }

  /**
   * Load related topics for a subject
   */
  function loadRelatedTopics(subjectSlug, $topicSelect) {
    $.ajax({
      url: mcqhome_ajax.ajax_url,
      type: "POST",
      data: {
        action: "mcqhome_get_subject_topics",
        subject_slug: subjectSlug,
        nonce: mcqhome_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          $topicSelect.empty().append('<option value="">All Topics</option>');
          $.each(response.data, function (index, topic) {
            $topicSelect.append(
              '<option value="' +
                topic.slug +
                '">' +
                topic.name +
                " (" +
                topic.count +
                ")</option>"
            );
          });
        }
      },
      error: function () {
        console.log("Error loading related topics");
      },
    });
  }

  /**
   * Reset topic options to show all
   */
  function resetTopicOptions($topicSelect) {
    // This would reload all topics - could be cached
    $topicSelect.empty().append('<option value="">All Topics</option>');
    // Add logic to reload all topics if needed
  }

  /**
   * Load teachers for an institution
   */
  function loadInstitutionTeachers(institutionId, $teacherSelect) {
    $.ajax({
      url: mcqhome_ajax.ajax_url,
      type: "POST",
      data: {
        action: "mcqhome_get_institution_teachers",
        institution_id: institutionId,
        nonce: mcqhome_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          $teacherSelect
            .empty()
            .append('<option value="">All Teachers</option>');
          $.each(response.data, function (index, teacher) {
            $teacherSelect.append(
              '<option value="' + teacher.id + '">' + teacher.name + "</option>"
            );
          });
        }
      },
      error: function () {
        console.log("Error loading institution teachers");
      },
    });
  }

  /**
   * Reset teacher options
   */
  function resetTeacherOptions($teacherSelect) {
    // Reload all teachers
    $teacherSelect.empty().append('<option value="">All Teachers</option>');
    // Add logic to reload all teachers if needed
  }

  /**
   * Initialize filter presets
   */
  function initFilterPresets() {
    // Add quick filter buttons
    const $filterPresets = $(
      '<div class="filter-presets mt-4 pt-4 border-t border-gray-200">' +
        '<h5 class="text-sm font-medium text-gray-700 mb-2">Quick Filters:</h5>' +
        '<div class="flex flex-wrap gap-2">' +
        '<button type="button" class="preset-btn" data-preset="popular">Popular</button>' +
        '<button type="button" class="preset-btn" data-preset="free">Free Content</button>' +
        '<button type="button" class="preset-btn" data-preset="recent">Recent</button>' +
        '<button type="button" class="preset-btn" data-preset="highly-rated">Highly Rated</button>' +
        "</div>" +
        "</div>"
    );

    $(".advanced-filters").append($filterPresets);

    // Style preset buttons
    $(".preset-btn").addClass(
      "px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full transition-colors"
    );

    // Handle preset clicks
    $(".preset-btn").on("click", function () {
      const preset = $(this).data("preset");
      applyFilterPreset(preset);
    });
  }

  /**
   * Apply filter preset
   */
  function applyFilterPreset(preset) {
    // Clear existing filters first
    $(".mcq-search-form")[0].reset();

    switch (preset) {
      case "popular":
        $("#sort").val("popular");
        break;
      case "free":
        $("#price").val("free");
        break;
      case "recent":
        $("#date_range").val("week");
        $("#sort").val("date");
        break;
      case "highly-rated":
        $("#min_rating").val("4");
        $("#sort").val("rating");
        break;
    }

    // Submit the form
    $(".mcq-search-form").submit();
  }

  // Export functions for external use
  window.MCQHomeBrowse = {
    showNotification: showNotification,
    updateFollowerCount: updateFollowerCount,
    loadRelatedTopics: loadRelatedTopics,
    applyFilterPreset: applyFilterPreset,
  };
})(jQuery);
