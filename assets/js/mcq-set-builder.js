/**
 * MCQ Set Builder JavaScript
 *
 * @package MCQHome
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  // Initialize when document is ready
  $(document).ready(function () {
    initMCQSetBuilder();
  });

  /**
   * Initialize MCQ Set Builder functionality
   */
  function initMCQSetBuilder() {
    initMCQSelection();
    initScoringConfiguration();
    initDisplayFormatOptions();
    initPricingOptions();
    initAutoSave();
    initValidation();
  }

  /**
   * Initialize MCQ Selection functionality
   */
  function initMCQSelection() {
    var $container = $("#mcq-selection-container");
    var $items = $container.find(".mcq-item");
    var $searchBox = $("#mcq-search");
    var $filterBtns = $(".mcq-filter-btn");
    var $selectedCount = $(".mcq-selected-count");

    // Update selected count
    function updateSelectedCount() {
      var count = $container.find("input[type=checkbox]:checked").length;
      $selectedCount.text(mcqSetBuilderL10n.selectedCount.replace("%d", count));
    }

    // Filter items based on search and filter
    function filterItems() {
      var searchTerm = $searchBox.val().toLowerCase();
      var activeFilter = $filterBtns.filter(".active").data("filter");

      $items.each(function () {
        var $item = $(this);
        var text = $item.text().toLowerCase();
        var isSelected = $item.find("input[type=checkbox]").is(":checked");
        var matchesSearch = text.indexOf(searchTerm) !== -1;
        var matchesFilter =
          activeFilter === "all" ||
          (activeFilter === "selected" && isSelected) ||
          (activeFilter === "unselected" && !isSelected);

        $item.toggle(matchesSearch && matchesFilter);
      });
    }

    // Search functionality
    $searchBox.on("input", filterItems);

    // Filter button functionality
    $filterBtns.on("click", function () {
      $filterBtns.removeClass("active");
      $(this).addClass("active");
      filterItems();
    });

    // Checkbox change handler
    $container.on("change", "input[type=checkbox]", function () {
      var $item = $(this).closest(".mcq-item");

      updateSelectedCount();
      $item.attr("data-selected", $(this).is(":checked") ? "true" : "false");
      $item.toggleClass("selected", $(this).is(":checked"));

      // Update individual marks section
      updateIndividualMarks();

      // Re-filter if not showing all
      if ($filterBtns.filter(".active").data("filter") !== "all") {
        filterItems();
      }
    });

    // Initial setup
    updateSelectedCount();

    // Mark initially selected items
    $container.find("input[type=checkbox]:checked").each(function () {
      $(this).closest(".mcq-item").addClass("selected");
    });
  }

  /**
   * Initialize Scoring Configuration
   */
  function initScoringConfiguration() {
    var $defaultMarks = $("#mcq_set_marks_per_question");
    var $totalMarks = $("#mcq_set_total_marks");
    var $passingMarks = $("#mcq_set_passing_marks");

    // Update individual marks when default changes
    $defaultMarks.on("input", function () {
      var newDefault = parseFloat($(this).val()) || 1;
      $(".individual-marks-input").each(function () {
        if (!$(this).data("manually-set")) {
          $(this).val(newDefault);
        }
      });
      calculateTotalMarks();
    });

    // Calculate total marks
    function calculateTotalMarks() {
      var total = 0;
      $(".individual-marks-input").each(function () {
        total += parseFloat($(this).val()) || 0;
      });
      $totalMarks.val(total.toFixed(1));
    }

    // Update individual marks section
    window.updateIndividualMarks = function () {
      var selectedMCQs = $("input[name='mcq_set_questions[]']:checked");
      var container = $("#individual-marks-container");
      var defaultMarks = parseFloat($defaultMarks.val()) || 1;

      if (selectedMCQs.length === 0) {
        container.html(
          "<p><em>" + mcqSetBuilderL10n.selectQuestionsFirst + "</em></p>"
        );
        return;
      }

      var html =
        '<table class="widefat"><thead><tr><th>' +
        mcqSetBuilderL10n.question +
        "</th><th>" +
        mcqSetBuilderL10n.marks +
        "</th></tr></thead><tbody>";

      selectedMCQs.each(function () {
        var mcqId = $(this).val();
        var mcqTitle = $(this)
          .closest(".mcq-item")
          .find(".mcq-item-title")
          .text();
        var currentMarks =
          window.mcqSetIndividualMarks && window.mcqSetIndividualMarks[mcqId]
            ? window.mcqSetIndividualMarks[mcqId]
            : defaultMarks;

        html += "<tr>";
        html += "<td>" + escapeHtml(mcqTitle) + "</td>";
        html +=
          '<td><input type="number" name="mcq_set_individual_marks[' +
          mcqId +
          ']" value="' +
          currentMarks +
          '" min="0" step="0.5" class="small-text individual-marks-input"></td>';
        html += "</tr>";
      });

      html += "</tbody></table>";
      container.html(html);

      calculateTotalMarks();
    };

    // Handle individual marks changes
    $(document).on("input", ".individual-marks-input", function () {
      $(this).data("manually-set", true);
      calculateTotalMarks();
    });

    // Auto-update passing marks to be 50% of total by default
    $(document).on("input", "#mcq_set_total_marks", function () {
      var total = parseFloat($(this).val()) || 0;
      var currentPassing = parseFloat($passingMarks.val()) || 0;

      // Only auto-update if passing marks is 0 or hasn't been manually set
      if (currentPassing === 0 || !$passingMarks.data("manually-set")) {
        $passingMarks.val((total * 0.5).toFixed(1));
      }
    });

    // Mark passing marks as manually set when changed
    $passingMarks.on("input", function () {
      $(this).data("manually-set", true);
    });
  }

  /**
   * Initialize Display Format Options
   */
  function initDisplayFormatOptions() {
    var $formatOptions = $("input[name='mcq_set_display_format']");
    var $formatContainers = $(".display-format-option");

    // Handle format selection
    $formatOptions.on("change", function () {
      $formatContainers.removeClass("selected");
      $(this).closest(".display-format-option").addClass("selected");
    });

    // Initial setup
    $formatOptions
      .filter(":checked")
      .closest(".display-format-option")
      .addClass("selected");

    // Make entire container clickable
    $formatContainers.on("click", function (e) {
      if (e.target.type !== "radio") {
        $(this)
          .find("input[type='radio']")
          .prop("checked", true)
          .trigger("change");
      }
    });
  }

  /**
   * Initialize Pricing Options
   */
  function initPricingOptions() {
    var $pricingOptions = $("input[name='mcq_set_pricing_type']");
    var $priceField = $("#price-field");
    var $priceInput = $("#mcq_set_price");

    // Handle pricing type changes
    $pricingOptions.on("change", function () {
      if ($(this).val() === "paid") {
        $priceField.slideDown();
        $priceInput.focus();
      } else {
        $priceField.slideUp();
        $priceInput.val(0);
      }
    });

    // Initial setup
    if ($pricingOptions.filter(":checked").val() === "free") {
      $priceField.hide();
    }
  }

  /**
   * Initialize Auto-save functionality
   */
  function initAutoSave() {
    var autoSaveTimer;
    var $form = $("#post");
    var hasChanges = false;

    // Track changes
    $form.on("change input", "input, select, textarea", function () {
      hasChanges = true;
      clearTimeout(autoSaveTimer);
      autoSaveTimer = setTimeout(performAutoSave, 3000); // Auto-save after 3 seconds of inactivity
    });

    // Perform auto-save
    function performAutoSave() {
      if (!hasChanges) return;

      var postId = $("#post_ID").val();
      if (!postId) return;

      var formData = new FormData($form[0]);
      formData.append("action", "mcqhome_autosave_mcq_set");
      formData.append("nonce", mcqSetBuilderL10n.nonce);

      $.ajax({
        url: mcqSetBuilderL10n.ajaxUrl,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
          if (response.success) {
            showMessage(mcqSetBuilderL10n.autoSaveSuccess, "success");
            hasChanges = false;
          } else {
            showMessage(mcqSetBuilderL10n.autoSaveError, "error");
          }
        },
        error: function () {
          showMessage(mcqSetBuilderL10n.autoSaveError, "error");
        },
      });
    }

    // Save before leaving page
    $(window).on("beforeunload", function () {
      if (hasChanges) {
        return mcqSetBuilderL10n.unsavedChanges;
      }
    });

    // Clear unsaved changes flag on form submit
    $form.on("submit", function () {
      hasChanges = false;
    });
  }

  /**
   * Initialize Validation
   */
  function initValidation() {
    var $form = $("#post");

    $form.on("submit", function (e) {
      var errors = [];

      // Check if at least one question is selected
      var selectedQuestions = $(
        "input[name='mcq_set_questions[]']:checked"
      ).length;
      if (selectedQuestions === 0) {
        errors.push(mcqSetBuilderL10n.errorNoQuestions);
      }

      // Check if total marks is greater than 0
      var totalMarks = parseFloat($("#mcq_set_total_marks").val()) || 0;
      if (totalMarks <= 0) {
        errors.push(mcqSetBuilderL10n.errorNoMarks);
      }

      // Check if passing marks is not greater than total marks
      var passingMarks = parseFloat($("#mcq_set_passing_marks").val()) || 0;
      if (passingMarks > totalMarks) {
        errors.push(mcqSetBuilderL10n.errorPassingMarksHigh);
      }

      // Check pricing
      var pricingType = $("input[name='mcq_set_pricing_type']:checked").val();
      if (pricingType === "paid") {
        var price = parseFloat($("#mcq_set_price").val()) || 0;
        if (price <= 0) {
          errors.push(mcqSetBuilderL10n.errorInvalidPrice);
        }
      }

      // Show errors if any
      if (errors.length > 0) {
        e.preventDefault();
        showMessage(errors.join("<br>"), "error");

        // Scroll to first error
        $("html, body").animate(
          {
            scrollTop: $(".mcq-set-message.error").offset().top - 50,
          },
          500
        );

        return false;
      }
    });
  }

  /**
   * Show message to user
   */
  function showMessage(message, type) {
    var $message = $(
      '<div class="mcq-set-message ' + type + '">' + message + "</div>"
    );

    // Remove existing messages
    $(".mcq-set-message").remove();

    // Add new message at top of form
    $("#post").prepend($message);

    // Auto-hide success messages
    if (type === "success") {
      setTimeout(function () {
        $message.fadeOut();
      }, 3000);
    }
  }

  /**
   * Escape HTML to prevent XSS
   */
  function escapeHtml(text) {
    var map = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    };
    return text.replace(/[&<>"']/g, function (m) {
      return map[m];
    });
  }

  /**
   * Utility function to format numbers
   */
  function formatNumber(num, decimals) {
    decimals = decimals || 1;
    return parseFloat(num).toFixed(decimals);
  }

  // Make functions available globally if needed
  window.MCQSetBuilder = {
    updateIndividualMarks: window.updateIndividualMarks,
    showMessage: showMessage,
  };
})(jQuery);
