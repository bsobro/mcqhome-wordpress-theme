/**
 * MCQ Set Builder JavaScript
 * Enhanced tabbed interface functionality for MCQ Set configuration
 */

(function ($) {
  "use strict";

  // Global variables
  let sectionIndex = 0;
  let questionIndex = 0;
  let mediaUploader;
  let hasUnsavedChanges = false;

  $(document).ready(function () {
    initializeMCQSetBuilder();
  });

  function initializeMCQSetBuilder() {
    // Initialize tab navigation
    initTabNavigation();

    // Initialize thumbnail upload
    initThumbnailUpload();

    // Initialize pricing toggle
    initPricingToggle();

    // Initialize display format configuration
    initDisplayFormatConfiguration();

    // Initialize sections management
    initSectionsManagement();

    // Initialize question builder
    initQuestionBuilder();

    // Initialize sortable functionality
    initSortable();

    // Initialize form change tracking
    initChangeTracking();

    // Initialize existing questions loader
    loadExistingQuestions();

    // Initialize bulk operations
    initBulkOperations();

    // Set initial section index
    sectionIndex = $(".mcq-set-section-item").length;
    questionIndex = $(".mcq-set-question-item").length;
  }

  /**
   * Tab Navigation
   */
  function initTabNavigation() {
    $(".mcq-set-tab-link").on("click", function (e) {
      e.preventDefault();

      const targetTab = $(this).attr("href");

      // Update active tab link
      $(".mcq-set-tab-link").removeClass("active");
      $(this).addClass("active");

      // Update active tab content
      $(".mcq-set-tab-content").removeClass("active");
      $(targetTab).addClass("active");

      // Trigger tab change event
      $(document).trigger("mcqSetTabChanged", [targetTab]);
    });
  }

  /**
   * Thumbnail Upload
   */
  function initThumbnailUpload() {
    // Upload thumbnail
    $(document).on("click", ".mcq-set-upload-thumbnail", function (e) {
      e.preventDefault();

      if (mediaUploader) {
        mediaUploader.open();
        return;
      }

      mediaUploader = wp.media({
        title: mcqSetBuilderL10n.selectMedia,
        button: {
          text: mcqSetBuilderL10n.useMedia,
        },
        multiple: false,
        library: {
          type: "image",
        },
      });

      mediaUploader.on("select", function () {
        const attachment = mediaUploader
          .state()
          .get("selection")
          .first()
          .toJSON();

        const previewHtml = `
                    <div class="mcq-set-thumbnail-preview" style="display: block;">
                        <img src="${
                          attachment.sizes.medium
                            ? attachment.sizes.medium.url
                            : attachment.url
                        }" 
                             alt="${mcqSetBuilderL10n.thumbnailPreview}" 
                             style="max-width: 200px; height: auto;">
                        <button type="button" class="button mcq-set-remove-thumbnail">${
                          mcqSetBuilderL10n.remove
                        }</button>
                    </div>
                `;

        $(".mcq-set-thumbnail-upload").html(previewHtml);
        $(".mcq-set-thumbnail-id").val(attachment.id);

        markAsChanged();
      });

      mediaUploader.open();
    });

    // Remove thumbnail
    $(document).on("click", ".mcq-set-remove-thumbnail", function (e) {
      e.preventDefault();

      const uploadHtml = `
                <button type="button" class="button mcq-set-upload-thumbnail">
                    ${mcqSetBuilderL10n.uploadThumbnail}
                </button>
            `;

      $(".mcq-set-thumbnail-upload").html(uploadHtml);
      $(".mcq-set-thumbnail-id").val("");

      markAsChanged();
    });
  }

  /**
   * Pricing Toggle
   */
  function initPricingToggle() {
    $(".mcq-set-pricing-radio").on("change", function () {
      const pricingType = $(this).val();
      const priceField = $(".mcq-set-price-field");

      if (pricingType === "paid") {
        priceField.slideDown(200);
      } else {
        priceField.slideUp(200);
        $("#mcq_set_price").val("");
      }

      markAsChanged();
    });
  }

  /**
   * Display Format Configuration
   */
  function initDisplayFormatConfiguration() {
    // Handle format selection changes
    $(".mcq-format-radio").on("change", function () {
      const selectedFormat = $(this).val();

      // Update visual feedback
      $(".mcq-format-option-card").removeClass("selected");
      $(this).closest(".mcq-format-option-card").addClass("selected");

      // Show format-specific information
      showFormatInfo(selectedFormat);

      markAsChanged();
    });

    // Initialize with current selection
    const currentFormat = $(".mcq-format-radio:checked").val();
    if (currentFormat) {
      $(".mcq-format-radio:checked")
        .closest(".mcq-format-option-card")
        .addClass("selected");
      showFormatInfo(currentFormat);
    }
  }

  function showFormatInfo(format) {
    // Remove any existing info messages
    $(".format-info-message").remove();

    let infoMessage = "";
    let messageClass = "info";

    if (format === "next_next") {
      infoMessage =
        mcqSetBuilderL10n.nextNextFormatInfo ||
        "Students will see one question at a time with navigation controls. This format provides better focus and is recommended for most assessments.";
      messageClass = "success";
    } else if (format === "single_page") {
      infoMessage =
        mcqSetBuilderL10n.singlePageFormatInfo ||
        "All questions will be displayed on one scrollable page. This format works best for shorter assessments (under 10 questions).";
      messageClass = "warning";
    }

    if (infoMessage) {
      const messageHtml = `
        <div class="format-info-message mcq-set-message ${messageClass}">
          <strong>Selected Format:</strong> ${infoMessage}
        </div>
      `;

      $(".format-recommendation").before(messageHtml);

      // Auto-hide after 5 seconds
      setTimeout(() => {
        $(".format-info-message").fadeOut(300, function () {
          $(this).remove();
        });
      }, 5000);
    }
  }

  /**
   * Sections Management
   */
  function initSectionsManagement() {
    // Toggle sections enabled/disabled
    $(".mcq-set-sections-enabled").on("change", function () {
      const sectionsManager = $(".mcq-set-sections-manager");
      const sectionAssignment = $(".mcq-set-section-assignment");

      if ($(this).is(":checked")) {
        sectionsManager.slideDown(300);
        sectionAssignment.slideDown(300);
      } else {
        sectionsManager.slideUp(300);
        sectionAssignment.slideUp(300);
      }

      markAsChanged();
    });

    // Add new section
    $(".mcq-set-add-section").on("click", function () {
      addNewSection();
    });

    // Remove section
    $(document).on("click", ".mcq-set-remove-section", function () {
      $(this)
        .closest(".mcq-set-section-item")
        .fadeOut(300, function () {
          $(this).remove();
          updateSectionOrder();
          updateSectionSelects();
          updateBulkSectionDropdown();
          markAsChanged();
        });
    });

    // Section name change
    $(document).on("input", ".mcq-set-section-name", function () {
      updateSectionSelects();
      updateBulkSectionDropdown();
      markAsChanged();
    });
  }

  function addNewSection() {
    const sectionId = "section_" + Date.now();
    const sectionHtml = `
            <div class="mcq-set-section-item" data-section-index="${sectionIndex}">
                <div class="mcq-set-section-header">
                    <span class="mcq-set-section-handle">⋮⋮</span>
                    <input type="text" 
                           name="mcq_set_sections[${sectionIndex}][name]" 
                           placeholder="${mcqSetBuilderL10n.sectionName}" 
                           class="mcq-set-section-name mcq-form-field">
                    <button type="button" class="button mcq-set-remove-section">${
                      mcqSetBuilderL10n.remove
                    }</button>
                </div>
                <div class="mcq-set-section-description">
                    <textarea name="mcq_set_sections[${sectionIndex}][description]" 
                              placeholder="${
                                mcqSetBuilderL10n.sectionDescription
                              }" 
                              class="mcq-set-section-desc mcq-form-field"></textarea>
                </div>
                <input type="hidden" name="mcq_set_sections[${sectionIndex}][id]" value="${sectionId}">
                <input type="hidden" name="mcq_set_sections[${sectionIndex}][order]" value="${
      sectionIndex + 1
    }" class="section-order">
            </div>
        `;

    $(".mcq-set-sections-list").append(sectionHtml);
    sectionIndex++;

    updateSectionOrder();
    updateSectionSelects();
    updateBulkSectionDropdown();
    markAsChanged();

    // Focus on the new section name field
    $(".mcq-set-section-item").last().find(".mcq-set-section-name").focus();
  }

  function updateSectionOrder() {
    $(".mcq-set-section-item").each(function (index) {
      $(this)
        .find(".section-order")
        .val(index + 1);
    });
  }

  function updateSectionSelects() {
    const sectionSelect = $(".mcq-section-select");
    const currentValue = sectionSelect.val();

    // Clear existing options except "No Section"
    sectionSelect.find("option:not(:first)").remove();

    // Add sections to select
    $(".mcq-set-section-item").each(function () {
      const sectionId = $(this).find('input[name*="[id]"]').val();
      const sectionName = $(this).find(".mcq-set-section-name").val();

      if (sectionName.trim()) {
        sectionSelect.append(
          `<option value="${sectionId}">${sectionName}</option>`
        );
      }
    });

    // Restore previous value if it still exists
    if (
      currentValue &&
      sectionSelect.find(`option[value="${currentValue}"]`).length
    ) {
      sectionSelect.val(currentValue);
    }
  }

  /**
   * Question Builder
   */
  function initQuestionBuilder() {
    // Question tab switching
    $(".mcq-set-tab-btn").on("click", function () {
      const targetTab = $(this).data("tab");

      $(".mcq-set-tab-btn").removeClass("active");
      $(this).addClass("active");

      $(".mcq-set-question-tab-content").hide();
      $(`#mcq-${targetTab}-tab`).show();
    });

    // Add question
    $(".mcq-set-save-question").on("click", function () {
      saveNewQuestion(false);
    });

    // Add question and create another
    $(".mcq-set-save-and-add-another").on("click", function () {
      saveNewQuestion(true);
    });

    // Handle correct answer selection visual feedback
    $(document).on(
      "change",
      'input[name="mcq_new_correct_answer"]',
      function () {
        // Remove selected class from all option fields
        $(".mcq-set-option-field").removeClass("selected");

        // Add selected class to the chosen option's field
        $(this).closest(".mcq-set-option-field").addClass("selected");
      }
    );

    // Inline editing event handlers
    $(document).on("click", ".mcq-save-inline-edit", function () {
      const questionItem = $(this).closest(".mcq-set-question-item");
      saveInlineEdit(questionItem);
    });

    $(document).on("click", ".mcq-cancel-edit", function () {
      const questionItem = $(this).closest(".mcq-set-question-item");
      cancelInlineEdit(questionItem);
    });

    // Bulk operations event handlers
    $(document).on("change", ".mcq-select-all-questions", function () {
      const isChecked = $(this).is(":checked");
      $(".mcq-question-checkbox").prop("checked", isChecked);
    });

    $(document).on("change", ".mcq-bulk-action", function () {
      const action = $(this).val();
      const sectionSelect = $(".mcq-bulk-section");

      if (action === "assign_section") {
        sectionSelect.show();
      } else {
        sectionSelect.hide();
      }
    });

    $(document).on("click", ".mcq-apply-bulk", function () {
      applyBulkActionWithAjax();
    });

    // Individual question selection
    $(document).on("change", ".mcq-question-checkbox", function () {
      const totalCheckboxes = $(".mcq-question-checkbox").length;
      const checkedCheckboxes = $(".mcq-question-checkbox:checked").length;

      $(".mcq-select-all-questions").prop(
        "checked",
        totalCheckboxes === checkedCheckboxes
      );
    });

    // Remove question with confirmation
    $(document).on("click", ".mcq-set-remove-question", function () {
      const questionItem = $(this).closest(".mcq-set-question-item");
      const questionTitle = questionItem
        .find(".mcq-set-question-content h5")
        .text();

      if (
        confirm(
          mcqSetBuilderL10n.confirmDeleteQuestion.replace("%s", questionTitle)
        )
      ) {
        const questionId = questionItem.data("question-id");

        // Show loading state
        questionItem.addClass("mcq-set-loading");

        // If it's a saved question, delete from database
        if (questionId) {
          deleteQuestionFromDatabase(questionId, questionItem);
        } else {
          // Just remove from DOM if it's not saved yet
          removeQuestionFromDOM(questionItem);
        }
      }
    });

    // Edit question - inline editing
    $(document).on("click", ".mcq-set-edit-question", function () {
      const questionItem = $(this).closest(".mcq-set-question-item");
      const questionId = questionItem.data("question-id");

      if (questionItem.hasClass("mcq-editing")) {
        return; // Already editing
      }

      // Load question data for editing
      loadQuestionForEditing(questionId, questionItem);
    });
  }

  function saveNewQuestion(addAnother = false) {
    // Get form data
    const questionData = {
      question_text: getEditorContent("mcq_new_question_text"),
      option_a: getEditorContent("mcq_new_option_a"),
      option_b: getEditorContent("mcq_new_option_b"),
      option_c: getEditorContent("mcq_new_option_c"),
      option_d: getEditorContent("mcq_new_option_d"),
      correct_answer: $('input[name="mcq_new_correct_answer"]:checked').val(),
      explanation: getEditorContent("mcq_new_explanation"),
      section_id: $("#mcq_new_section").val(),
    };

    // Enhanced validation for rich content
    const questionTextClean = stripHtmlTags(questionData.question_text).trim();
    const optionAClean = stripHtmlTags(questionData.option_a).trim();
    const optionBClean = stripHtmlTags(questionData.option_b).trim();
    const optionCClean = stripHtmlTags(questionData.option_c).trim();
    const optionDClean = stripHtmlTags(questionData.option_d).trim();
    const explanationClean = stripHtmlTags(questionData.explanation).trim();

    if (!questionTextClean) {
      showMessage(mcqSetBuilderL10n.errorNoQuestion, "error");
      focusEditor("mcq_new_question_text");
      return;
    }

    if (!optionAClean || !optionBClean || !optionCClean || !optionDClean) {
      showMessage(mcqSetBuilderL10n.errorEmptyOptions, "error");
      // Focus on first empty option
      if (!optionAClean) focusEditor("mcq_new_option_a");
      else if (!optionBClean) focusEditor("mcq_new_option_b");
      else if (!optionCClean) focusEditor("mcq_new_option_c");
      else if (!optionDClean) focusEditor("mcq_new_option_d");
      return;
    }

    if (!questionData.correct_answer) {
      showMessage(mcqSetBuilderL10n.errorNoCorrectAnswer, "error");
      $('input[name="mcq_new_correct_answer"]').first().focus();
      return;
    }

    if (!explanationClean) {
      showMessage(mcqSetBuilderL10n.errorNoExplanation, "error");
      focusEditor("mcq_new_explanation");
      return;
    }

    // Show loading state with better UX
    $(".mcq-set-new-question-form").addClass("mcq-set-loading");
    $(".mcq-set-save-question, .mcq-set-save-and-add-another").prop(
      "disabled",
      true
    );

    // AJAX call to create MCQ
    $.ajax({
      url: mcqSetBuilderL10n.ajaxUrl,
      type: "POST",
      data: {
        action: "mcqhome_create_mcq_inline",
        nonce: mcqSetBuilderL10n.nonce,
        ...questionData,
      },
      success: function (response) {
        if (response.success) {
          // Add question to the list
          addQuestionToList(response.data);

          // Clear form if adding another
          if (addAnother) {
            clearQuestionForm();
            showMessage(
              mcqSetBuilderL10n.questionAdded +
                " " +
                mcqSetBuilderL10n.addAnotherPrompt,
              "success"
            );
            // Focus on question editor for next question
            setTimeout(() => focusEditor("mcq_new_question_text"), 500);
          } else {
            showMessage(mcqSetBuilderL10n.questionAdded, "success");
          }

          markAsChanged();
          updateQuestionCounter();
        } else {
          showMessage(response.data || mcqSetBuilderL10n.errorGeneric, "error");
        }
      },
      error: function (xhr, status, error) {
        console.error("MCQ creation error:", error);
        showMessage(mcqSetBuilderL10n.errorGeneric, "error");
      },
      complete: function () {
        $(".mcq-set-new-question-form").removeClass("mcq-set-loading");
        $(".mcq-set-save-question, .mcq-set-save-and-add-another").prop(
          "disabled",
          false
        );
      },
    });
  }

  function getEditorContent(editorId) {
    if (typeof tinyMCE !== "undefined" && tinyMCE.get(editorId)) {
      return tinyMCE.get(editorId).getContent();
    }
    return $(`#${editorId}`).val() || "";
  }

  function setEditorContent(editorId, content) {
    if (typeof tinyMCE !== "undefined" && tinyMCE.get(editorId)) {
      tinyMCE.get(editorId).setContent(content);
    } else {
      $(`#${editorId}`).val(content);
    }
  }

  function focusEditor(editorId) {
    if (typeof tinyMCE !== "undefined" && tinyMCE.get(editorId)) {
      tinyMCE.get(editorId).focus();
    } else {
      $(`#${editorId}`).focus();
    }
  }

  function stripHtmlTags(html) {
    const tmp = document.createElement("div");
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || "";
  }

  function updateQuestionCounter() {
    const questionCount = $(".mcq-set-question-item").length;
    const counterText =
      questionCount === 1
        ? mcqSetBuilderL10n.oneQuestion
        : mcqSetBuilderL10n.multipleQuestions.replace("%d", questionCount);

    // Update counter in tab or header if it exists
    let $counter = $(".mcq-set-question-counter");
    if ($counter.length === 0) {
      $counter = $('<span class="mcq-set-question-counter"></span>');
      $(".mcq-set-tab-link[href='#tab-questions']")
        .append(" ")
        .append($counter);
    }
    $counter.text(`(${questionCount})`);
  }

  function clearQuestionForm() {
    // Clear editors with a slight delay to ensure TinyMCE is ready
    setTimeout(() => {
      setEditorContent("mcq_new_question_text", "");
      setEditorContent("mcq_new_option_a", "");
      setEditorContent("mcq_new_option_b", "");
      setEditorContent("mcq_new_option_c", "");
      setEditorContent("mcq_new_option_d", "");
      setEditorContent("mcq_new_explanation", "");
    }, 100);

    // Clear radio buttons
    $('input[name="mcq_new_correct_answer"]').prop("checked", false);

    // Reset section selection to current default
    $("#mcq_new_section").val("");

    // Remove any error states
    $(".mcq-set-question-field, .mcq-set-option-field").removeClass("error");

    // Update correct answer indicators
    $(".mcq-set-option-field").removeClass("selected");
  }

  function addQuestionToList(questionData) {
    const sectionName = questionData.section_id
      ? $(`.mcq-set-section-item input[value="${questionData.section_id}"]`)
          .closest(".mcq-set-section-item")
          .find(".mcq-set-section-name")
          .val()
      : "";

    const sectionBadge = sectionName
      ? `<span class="mcq-set-question-section">${mcqSetBuilderL10n.section}: ${sectionName}</span>`
      : "";

    const questionHtml = `
            <div class="mcq-set-question-item" data-question-id="${
              questionData.id
            }">
                <div class="mcq-set-question-select">
                    <input type="checkbox" class="mcq-question-checkbox" value="${
                      questionData.id
                    }">
                </div>
                <div class="mcq-set-question-handle">⋮⋮</div>
                <div class="mcq-set-question-content">
                    <h5>${questionData.title}</h5>
                    <p>${questionData.excerpt}</p>
                    ${sectionBadge}
                </div>
                <div class="mcq-set-question-actions">
                    <button type="button" class="button button-small mcq-set-edit-question">${
                      mcqSetBuilderL10n.edit
                    }</button>
                    <button type="button" class="button button-small mcq-set-remove-question">${
                      mcqSetBuilderL10n.remove
                    }</button>
                </div>
                <input type="hidden" name="mcq_set_questions[${questionIndex}][mcq_id]" value="${
      questionData.id
    }">
                <input type="hidden" name="mcq_set_questions[${questionIndex}][section_id]" value="${
      questionData.section_id || ""
    }">
                <input type="hidden" name="mcq_set_questions[${questionIndex}][order]" value="${
      questionIndex + 1
    }">
            </div>
        `;

    $(".mcq-set-questions-list").append(questionHtml);
    questionIndex++;

    updateQuestionOrder();
    updateNoQuestionsMessage();
  }

  function updateQuestionOrder() {
    $(".mcq-set-question-item").each(function (index) {
      $(this)
        .find('input[name*="[order]"]')
        .val(index + 1);
    });
  }

  function updateNoQuestionsMessage() {
    const questionsList = $(".mcq-set-questions-list");
    const hasQuestions =
      questionsList.find(".mcq-set-question-item").length > 0;

    if (hasQuestions) {
      questionsList.find(".mcq-set-no-questions").remove();
    } else {
      if (!questionsList.find(".mcq-set-no-questions").length) {
        questionsList.append(
          `<p class="mcq-set-no-questions">${mcqSetBuilderL10n.noQuestions}</p>`
        );
      }
    }
  }

  /**
   * Load Existing Questions
   */
  function loadExistingQuestions(page = 1, search = "") {
    const existingList = $(".mcq-set-existing-list");

    // Show loading state
    if (page === 1) {
      existingList.html(
        `<p class="description">${mcqSetBuilderL10n.loadingQuestions}</p>`
      );
    }

    $.ajax({
      url: mcqSetBuilderL10n.ajaxUrl,
      type: "POST",
      data: {
        action: "mcqhome_get_available_mcqs",
        nonce: mcqSetBuilderL10n.nonce,
        page: page,
        search: search,
      },
      success: function (response) {
        if (response.success) {
          displayExistingQuestions(response.data);
        } else {
          existingList.html(
            `<div class="mcq-existing-error">
              <p class="description">${mcqSetBuilderL10n.errorLoadingQuestions}</p>
              <button type="button" class="button mcq-retry-load">${mcqSetBuilderL10n.retry}</button>
            </div>`
          );

          // Retry functionality
          $(".mcq-retry-load").on("click", function () {
            loadExistingQuestions(page, search);
          });
        }
      },
      error: function (xhr, status, error) {
        console.error("Error loading questions:", error);
        existingList.html(
          `<div class="mcq-existing-error">
            <p class="description">${mcqSetBuilderL10n.errorLoadingQuestions}</p>
            <button type="button" class="button mcq-retry-load">${mcqSetBuilderL10n.retry}</button>
          </div>`
        );

        // Retry functionality
        $(".mcq-retry-load").on("click", function () {
          loadExistingQuestions(page, search);
        });
      },
    });
  }

  function displayExistingQuestions(questions) {
    const existingList = $(".mcq-set-existing-list");

    if (questions.mcqs.length === 0) {
      existingList.html(`
        <div class="mcq-existing-empty">
          <p class="description">${mcqSetBuilderL10n.noExistingQuestions}</p>
          <p class="description">${mcqSetBuilderL10n.createFirstQuestion}</p>
        </div>
      `);
      return;
    }

    let html = '<div class="mcq-existing-search">';
    html += `<input type="text" placeholder="${mcqSetBuilderL10n.searchQuestions}" class="mcq-search-input mcq-form-field">`;
    html += `<button type="button" class="button mcq-search-btn">${mcqSetBuilderL10n.search}</button>`;
    html += "</div>";

    html += '<div class="mcq-existing-questions-grid">';

    questions.mcqs.forEach(function (question) {
      html += `
        <div class="mcq-existing-question-item" data-question-id="${question.id}">
          <label class="mcq-existing-question-label">
            <input type="checkbox" value="${question.id}" class="mcq-existing-question-checkbox">
            <div class="mcq-existing-question-content">
              <h6>${question.title}</h6>
              <p>${question.excerpt}</p>
              <span class="mcq-question-date">${question.date}</span>
            </div>
          </label>
        </div>
      `;
    });

    html += "</div>";

    // Add pagination if needed
    if (questions.pages > 1) {
      html += '<div class="mcq-existing-pagination">';
      for (let i = 1; i <= questions.pages; i++) {
        const activeClass = i === questions.current_page ? " active" : "";
        html += `<button type="button" class="button mcq-page-btn${activeClass}" data-page="${i}">${i}</button>`;
      }
      html += "</div>";
    }

    html += `<div class="mcq-existing-actions">`;
    html += `<button type="button" class="button button-secondary mcq-select-all">${mcqSetBuilderL10n.selectAll}</button>`;
    html += `<button type="button" class="button button-secondary mcq-deselect-all">${mcqSetBuilderL10n.deselectAll}</button>`;
    html += `<button type="button" class="button button-primary mcq-add-selected-questions">${mcqSetBuilderL10n.addSelectedQuestions}</button>`;
    html += `</div>`;

    existingList.html(html);

    // Bind events
    bindExistingQuestionsEvents();
  }

  function bindExistingQuestionsEvents() {
    // Search functionality
    $(".mcq-search-btn").on("click", function () {
      const searchTerm = $(".mcq-search-input").val();
      loadExistingQuestions(1, searchTerm);
    });

    $(".mcq-search-input").on("keypress", function (e) {
      if (e.which === 13) {
        const searchTerm = $(this).val();
        loadExistingQuestions(1, searchTerm);
      }
    });

    // Pagination
    $(".mcq-page-btn").on("click", function () {
      const page = $(this).data("page");
      const searchTerm = $(".mcq-search-input").val();
      loadExistingQuestions(page, searchTerm);
    });

    // Select/Deselect all
    $(".mcq-select-all").on("click", function () {
      $(".mcq-existing-question-checkbox:not(:disabled)").prop("checked", true);
    });

    $(".mcq-deselect-all").on("click", function () {
      $(".mcq-existing-question-checkbox").prop("checked", false);
    });

    // Handle adding selected questions
    $(".mcq-add-selected-questions").on("click", function () {
      const selectedQuestions = $(".mcq-existing-question-checkbox:checked");

      if (selectedQuestions.length === 0) {
        showMessage(mcqSetBuilderL10n.selectQuestionsFirst, "warning");
        return;
      }

      let addedCount = 0;
      selectedQuestions.each(function () {
        const questionId = $(this).val();
        const questionItem = $(this).closest(".mcq-existing-question-item");
        const title = questionItem.find("h6").text();
        const excerpt = questionItem.find("p").text();

        // Check if question is already added
        if ($(`input[name*="[mcq_id]"][value="${questionId}"]`).length === 0) {
          addQuestionToList({
            id: questionId,
            title: title,
            excerpt: excerpt,
            section_id: "",
          });
          addedCount++;
        }

        // Disable the checkbox
        $(this).prop("disabled", true);
        questionItem.addClass("mcq-question-added");
      });

      if (addedCount > 0) {
        const message =
          addedCount === 1
            ? mcqSetBuilderL10n.questionAdded
            : mcqSetBuilderL10n.questionsAdded.replace("%d", addedCount);
        showMessage(message, "success");
        markAsChanged();
        updateQuestionCounter();
      } else {
        showMessage(mcqSetBuilderL10n.questionsAlreadyAdded, "warning");
      }
    });
  }

  /**
   * Sortable Functionality
   */
  function initSortable() {
    // Make sections sortable
    if ($.fn.sortable) {
      $(".mcq-set-sections-list").sortable({
        handle: ".mcq-set-section-handle",
        placeholder: "mcq-section-placeholder",
        update: function () {
          updateSectionOrder();
          markAsChanged();
        },
      });

      // Make questions sortable with enhanced functionality
      $(".mcq-set-questions-list").sortable({
        handle: ".mcq-set-question-handle",
        placeholder: "mcq-question-placeholder",
        cursor: "move",
        opacity: 0.8,
        tolerance: "pointer",
        start: function (event, ui) {
          ui.placeholder.height(ui.item.height());
          ui.item.addClass("mcq-question-dragging");
        },
        stop: function (event, ui) {
          ui.item.removeClass("mcq-question-dragging");
        },
        update: function () {
          updateQuestionOrder();
          saveQuestionOrder();
          markAsChanged();
          showMessage(mcqSetBuilderL10n.questionsReordered, "success");
        },
      });
    }
  }

  /**
   * Change Tracking
   */
  function initChangeTracking() {
    // Track form changes
    $(document).on(
      "input change",
      '.mcq-form-field, input[type="radio"], input[type="checkbox"]',
      function () {
        markAsChanged();
      }
    );

    // Warn before leaving with unsaved changes
    $(window).on("beforeunload", function () {
      if (hasUnsavedChanges) {
        return mcqSetBuilderL10n.unsavedChanges;
      }
    });

    // Clear unsaved changes flag on form submit
    $("form").on("submit", function () {
      hasUnsavedChanges = false;
    });
  }

  function markAsChanged() {
    hasUnsavedChanges = true;
  }

  /**
   * Question Management Functions
   */
  function loadQuestionForEditing(questionId, questionItem) {
    // Show loading state
    questionItem.addClass("mcq-set-loading");

    $.ajax({
      url: mcqSetBuilderL10n.ajaxUrl,
      type: "POST",
      data: {
        action: "mcqhome_get_mcq_for_editing",
        nonce: mcqSetBuilderL10n.nonce,
        question_id: questionId,
      },
      success: function (response) {
        if (response.success) {
          showInlineEditor(questionItem, response.data);
        } else {
          showMessage(
            response.data || mcqSetBuilderL10n.errorLoadingQuestion,
            "error"
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("Error loading question:", error);
        showMessage(mcqSetBuilderL10n.errorLoadingQuestion, "error");
      },
      complete: function () {
        questionItem.removeClass("mcq-set-loading");
      },
    });
  }

  function showInlineEditor(questionItem, questionData) {
    questionItem.addClass("mcq-editing");

    const currentContent = questionItem.find(".mcq-set-question-content");
    const questionId = questionItem.data("question-id");
    const sectionId = questionItem.find('input[name*="[section_id]"]').val();

    // Create inline editor HTML
    const editorHtml = `
      <div class="mcq-inline-editor">
        <div class="mcq-inline-editor-header">
          <h4>${mcqSetBuilderL10n.editingQuestion}</h4>
          <button type="button" class="button mcq-cancel-edit">${
            mcqSetBuilderL10n.cancel
          }</button>
        </div>
        
        <div class="mcq-inline-editor-content">
          <div class="mcq-inline-field">
            <label>${mcqSetBuilderL10n.questionText}</label>
            <textarea class="mcq-inline-question" rows="3">${
              questionData.question_text
            }</textarea>
          </div>
          
          <div class="mcq-inline-options">
            <div class="mcq-inline-option">
              <label>A)</label>
              <input type="text" class="mcq-inline-option-a" value="${
                questionData.options.A
              }">
              <input type="radio" name="mcq_inline_correct_${questionId}" value="A" ${
      questionData.correct_answer === "A" ? "checked" : ""
    }>
            </div>
            <div class="mcq-inline-option">
              <label>B)</label>
              <input type="text" class="mcq-inline-option-b" value="${
                questionData.options.B
              }">
              <input type="radio" name="mcq_inline_correct_${questionId}" value="B" ${
      questionData.correct_answer === "B" ? "checked" : ""
    }>
            </div>
            <div class="mcq-inline-option">
              <label>C)</label>
              <input type="text" class="mcq-inline-option-c" value="${
                questionData.options.C
              }">
              <input type="radio" name="mcq_inline_correct_${questionId}" value="C" ${
      questionData.correct_answer === "C" ? "checked" : ""
    }>
            </div>
            <div class="mcq-inline-option">
              <label>D)</label>
              <input type="text" class="mcq-inline-option-d" value="${
                questionData.options.D
              }">
              <input type="radio" name="mcq_inline_correct_${questionId}" value="D" ${
      questionData.correct_answer === "D" ? "checked" : ""
    }>
            </div>
          </div>
          
          <div class="mcq-inline-field">
            <label>${mcqSetBuilderL10n.explanation}</label>
            <textarea class="mcq-inline-explanation" rows="2">${
              questionData.explanation
            }</textarea>
          </div>
          
          <div class="mcq-inline-field">
            <label>${mcqSetBuilderL10n.section}</label>
            <select class="mcq-inline-section">
              <option value="">${mcqSetBuilderL10n.noSection}</option>
            </select>
          </div>
        </div>
        
        <div class="mcq-inline-editor-actions">
          <button type="button" class="button button-primary mcq-save-inline-edit">${
            mcqSetBuilderL10n.saveChanges
          }</button>
          <button type="button" class="button mcq-cancel-edit">${
            mcqSetBuilderL10n.cancel
          }</button>
        </div>
      </div>
    `;

    // Replace content with editor
    currentContent.hide().after(editorHtml);

    // Populate section dropdown
    const sectionSelect = questionItem.find(".mcq-inline-section");
    $(".mcq-set-section-item").each(function () {
      const secId = $(this).find('input[name*="[id]"]').val();
      const secName = $(this).find(".mcq-set-section-name").val();

      if (secName.trim()) {
        const selected = secId === sectionId ? "selected" : "";
        sectionSelect.append(
          `<option value="${secId}" ${selected}>${secName}</option>`
        );
      }
    });
  }

  function saveInlineEdit(questionItem) {
    const questionId = questionItem.data("question-id");
    const editor = questionItem.find(".mcq-inline-editor");

    const questionData = {
      question_id: questionId,
      question_text: editor.find(".mcq-inline-question").val(),
      option_a: editor.find(".mcq-inline-option-a").val(),
      option_b: editor.find(".mcq-inline-option-b").val(),
      option_c: editor.find(".mcq-inline-option-c").val(),
      option_d: editor.find(".mcq-inline-option-d").val(),
      correct_answer: editor
        .find(`input[name="mcq_inline_correct_${questionId}"]:checked`)
        .val(),
      explanation: editor.find(".mcq-inline-explanation").val(),
      section_id: editor.find(".mcq-inline-section").val(),
    };

    // Validate
    if (!questionData.question_text.trim()) {
      showMessage(mcqSetBuilderL10n.errorNoQuestion, "error");
      return;
    }

    if (
      !questionData.option_a.trim() ||
      !questionData.option_b.trim() ||
      !questionData.option_c.trim() ||
      !questionData.option_d.trim()
    ) {
      showMessage(mcqSetBuilderL10n.errorEmptyOptions, "error");
      return;
    }

    if (!questionData.correct_answer) {
      showMessage(mcqSetBuilderL10n.errorNoCorrectAnswer, "error");
      return;
    }

    if (!questionData.explanation.trim()) {
      showMessage(mcqSetBuilderL10n.errorNoExplanation, "error");
      return;
    }

    // Show loading
    questionItem.addClass("mcq-set-loading");

    $.ajax({
      url: mcqSetBuilderL10n.ajaxUrl,
      type: "POST",
      data: {
        action: "mcqhome_update_mcq_inline",
        nonce: mcqSetBuilderL10n.nonce,
        ...questionData,
      },
      success: function (response) {
        if (response.success) {
          updateQuestionDisplay(questionItem, response.data);
          cancelInlineEdit(questionItem);
          showMessage(mcqSetBuilderL10n.questionUpdated, "success");
          markAsChanged();
        } else {
          showMessage(
            response.data || mcqSetBuilderL10n.errorUpdatingQuestion,
            "error"
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("Error updating question:", error);
        showMessage(mcqSetBuilderL10n.errorUpdatingQuestion, "error");
      },
      complete: function () {
        questionItem.removeClass("mcq-set-loading");
      },
    });
  }

  function cancelInlineEdit(questionItem) {
    questionItem.removeClass("mcq-editing");
    questionItem.find(".mcq-inline-editor").remove();
    questionItem.find(".mcq-set-question-content").show();
  }

  function updateQuestionDisplay(questionItem, questionData) {
    const content = questionItem.find(".mcq-set-question-content");
    const sectionName = questionData.section_id
      ? $(`.mcq-set-section-item input[value="${questionData.section_id}"]`)
          .closest(".mcq-set-section-item")
          .find(".mcq-set-section-name")
          .val()
      : "";

    const sectionBadge = sectionName
      ? `<span class="mcq-set-question-section">${mcqSetBuilderL10n.section}: ${sectionName}</span>`
      : "";

    content.find("h5").text(questionData.title);
    content.find("p").text(questionData.excerpt);
    content.find(".mcq-set-question-section").remove();
    if (sectionBadge) {
      content.append(sectionBadge);
    }

    // Update hidden fields
    questionItem
      .find('input[name*="[section_id]"]')
      .val(questionData.section_id || "");
  }

  function deleteQuestionFromDatabase(questionId, questionItem) {
    $.ajax({
      url: mcqSetBuilderL10n.ajaxUrl,
      type: "POST",
      data: {
        action: "mcqhome_delete_mcq_inline",
        nonce: mcqSetBuilderL10n.nonce,
        question_id: questionId,
      },
      success: function (response) {
        if (response.success) {
          removeQuestionFromDOM(questionItem);
          showMessage(mcqSetBuilderL10n.questionDeleted, "success");
        } else {
          showMessage(
            response.data || mcqSetBuilderL10n.errorDeletingQuestion,
            "error"
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("Error deleting question:", error);
        showMessage(mcqSetBuilderL10n.errorDeletingQuestion, "error");
      },
      complete: function () {
        questionItem.removeClass("mcq-set-loading");
      },
    });
  }

  function removeQuestionFromDOM(questionItem) {
    questionItem.fadeOut(300, function () {
      $(this).remove();
      updateQuestionOrder();
      updateNoQuestionsMessage();
      updateQuestionCounter();
      markAsChanged();
    });
  }

  // Bulk operations
  function initBulkOperations() {
    // Add bulk selection controls
    const bulkControlsHtml = `
      <div class="mcq-bulk-operations">
        <div class="mcq-bulk-controls">
          <label class="mcq-bulk-select-all">
            <input type="checkbox" class="mcq-select-all-questions"> ${mcqSetBuilderL10n.selectAll}
          </label>
          <select class="mcq-bulk-action">
            <option value="">${mcqSetBuilderL10n.bulkActions}</option>
            <option value="assign_section">${mcqSetBuilderL10n.assignToSection}</option>
            <option value="remove_section">${mcqSetBuilderL10n.removeFromSection}</option>
            <option value="delete">${mcqSetBuilderL10n.deleteSelected}</option>
          </select>
          <select class="mcq-bulk-section" style="display:none;">
            <option value="">${mcqSetBuilderL10n.selectSection}</option>
          </select>
          <button type="button" class="button mcq-apply-bulk">${mcqSetBuilderL10n.apply}</button>
        </div>
      </div>
    `;

    $(".mcq-set-current-questions h4").after(bulkControlsHtml);

    // Update section dropdown for bulk operations
    updateBulkSectionDropdown();
  }

  function updateBulkSectionDropdown() {
    const bulkSectionSelect = $(".mcq-bulk-section");
    bulkSectionSelect.find("option:not(:first)").remove();

    $(".mcq-set-section-item").each(function () {
      const sectionId = $(this).find('input[name*="[id]"]').val();
      const sectionName = $(this).find(".mcq-set-section-name").val();

      if (sectionName.trim()) {
        bulkSectionSelect.append(
          `<option value="${sectionId}">${sectionName}</option>`
        );
      }
    });
  }

  function applyBulkAction() {
    const selectedQuestions = $(".mcq-question-checkbox:checked");
    const action = $(".mcq-bulk-action").val();

    if (selectedQuestions.length === 0) {
      showMessage(mcqSetBuilderL10n.selectQuestionsFirst, "warning");
      return;
    }

    if (!action) {
      showMessage(mcqSetBuilderL10n.selectActionFirst, "warning");
      return;
    }

    switch (action) {
      case "assign_section":
        const sectionId = $(".mcq-bulk-section").val();
        if (!sectionId) {
          showMessage(mcqSetBuilderL10n.selectSectionFirst, "warning");
          return;
        }
        bulkAssignToSection(selectedQuestions, sectionId);
        break;

      case "remove_section":
        bulkRemoveFromSection(selectedQuestions);
        break;

      case "delete":
        if (
          confirm(
            mcqSetBuilderL10n.confirmBulkDelete.replace(
              "%d",
              selectedQuestions.length
            )
          )
        ) {
          bulkDeleteQuestions(selectedQuestions);
        }
        break;
    }
  }

  function bulkAssignToSection(selectedQuestions, sectionId) {
    const sectionName = $(`.mcq-set-section-item input[value="${sectionId}"]`)
      .closest(".mcq-set-section-item")
      .find(".mcq-set-section-name")
      .val();

    selectedQuestions.each(function () {
      const questionItem = $(this).closest(".mcq-set-question-item");
      const questionId = $(this).val();

      // Update hidden field
      questionItem.find('input[name*="[section_id]"]').val(sectionId);

      // Update display
      questionItem.find(".mcq-set-question-section").remove();
      if (sectionName) {
        const sectionBadge = `<span class="mcq-set-question-section">${mcqSetBuilderL10n.section}: ${sectionName}</span>`;
        questionItem.find(".mcq-set-question-content").append(sectionBadge);
      }
    });

    // Clear selections
    selectedQuestions.prop("checked", false);
    $(".mcq-select-all-questions").prop("checked", false);

    showMessage(
      mcqSetBuilderL10n.questionsAssignedToSection.replace("%s", sectionName),
      "success"
    );
    markAsChanged();
  }

  function bulkRemoveFromSection(selectedQuestions) {
    selectedQuestions.each(function () {
      const questionItem = $(this).closest(".mcq-set-question-item");

      // Update hidden field
      questionItem.find('input[name*="[section_id]"]').val("");

      // Remove section badge
      questionItem.find(".mcq-set-question-section").remove();
    });

    // Clear selections
    selectedQuestions.prop("checked", false);
    $(".mcq-select-all-questions").prop("checked", false);

    showMessage(mcqSetBuilderL10n.questionsRemovedFromSections, "success");
    markAsChanged();
  }

  function bulkDeleteQuestions(selectedQuestions) {
    let deletedCount = 0;
    const totalCount = selectedQuestions.length;

    selectedQuestions.each(function () {
      const questionItem = $(this).closest(".mcq-set-question-item");
      const questionId = $(this).val();

      if (questionId && questionId !== "") {
        // Delete from database
        $.ajax({
          url: mcqSetBuilderL10n.ajaxUrl,
          type: "POST",
          data: {
            action: "mcqhome_delete_mcq_inline",
            nonce: mcqSetBuilderL10n.nonce,
            question_id: questionId,
          },
          success: function (response) {
            if (response.success) {
              removeQuestionFromDOM(questionItem);
              deletedCount++;

              if (deletedCount === totalCount) {
                showMessage(
                  mcqSetBuilderL10n.questionsDeleted.replace(
                    "%d",
                    deletedCount
                  ),
                  "success"
                );
              }
            }
          },
        });
      } else {
        // Just remove from DOM
        removeQuestionFromDOM(questionItem);
        deletedCount++;
      }
    });

    // Clear selections
    $(".mcq-select-all-questions").prop("checked", false);
  }

  /**
   * Save question order via AJAX
   */
  function saveQuestionOrder() {
    const mcqSetId = $("#post_ID").val();
    if (!mcqSetId) return;

    const questionOrder = [];
    $(".mcq-set-question-item").each(function (index) {
      const mcqId = $(this).find('input[name*="[mcq_id]"]').val();
      const sectionId = $(this).find('input[name*="[section_id]"]').val();

      if (mcqId) {
        questionOrder.push({
          mcq_id: mcqId,
          section_id: sectionId || "",
          order: index + 1,
        });
      }
    });

    $.ajax({
      url: mcqSetBuilderL10n.ajaxUrl,
      type: "POST",
      data: {
        action: "mcqhome_update_question_order",
        nonce: mcqSetBuilderL10n.nonce,
        mcq_set_id: mcqSetId,
        question_order: questionOrder,
      },
      success: function (response) {
        if (!response.success) {
          console.error("Failed to save question order:", response.data);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error saving question order:", error);
      },
    });
  }

  /**
   * Enhanced bulk operations with AJAX
   */
  function applyBulkActionWithAjax() {
    const selectedQuestions = $(".mcq-question-checkbox:checked");
    const action = $(".mcq-bulk-action").val();
    const mcqSetId = $("#post_ID").val();

    if (selectedQuestions.length === 0) {
      showMessage(mcqSetBuilderL10n.selectQuestionsFirst, "warning");
      return;
    }

    if (!action) {
      showMessage(mcqSetBuilderL10n.selectActionFirst, "warning");
      return;
    }

    const questionIds = [];
    selectedQuestions.each(function () {
      questionIds.push($(this).val());
    });

    let sectionId = "";
    if (action === "assign_section") {
      sectionId = $(".mcq-bulk-section").val();
      if (!sectionId) {
        showMessage(mcqSetBuilderL10n.selectSectionFirst, "warning");
        return;
      }
    }

    // Confirm deletion
    if (action === "delete") {
      if (
        !confirm(
          mcqSetBuilderL10n.confirmBulkDelete.replace(
            "%d",
            selectedQuestions.length
          )
        )
      ) {
        return;
      }
    }

    // Show loading state
    $(".mcq-bulk-operations").addClass("mcq-set-loading");

    $.ajax({
      url: mcqSetBuilderL10n.ajaxUrl,
      type: "POST",
      data: {
        action: "mcqhome_bulk_question_operations",
        nonce: mcqSetBuilderL10n.nonce,
        mcq_set_id: mcqSetId,
        bulk_action: action,
        question_ids: questionIds,
        section_id: sectionId,
      },
      success: function (response) {
        if (response.success) {
          showMessage(response.data.message, "success");

          // Handle UI updates based on action
          switch (action) {
            case "assign_section":
              updateQuestionsSection(questionIds, sectionId);
              break;
            case "remove_section":
              updateQuestionsSection(questionIds, "");
              break;
            case "delete":
              removeQuestionsFromDOM(questionIds);
              break;
          }

          // Clear selections
          selectedQuestions.prop("checked", false);
          $(".mcq-select-all-questions").prop("checked", false);

          markAsChanged();
        } else {
          showMessage(response.data || mcqSetBuilderL10n.errorGeneric, "error");
        }
      },
      error: function (xhr, status, error) {
        console.error("Bulk operation error:", error);
        showMessage(mcqSetBuilderL10n.errorGeneric, "error");
      },
      complete: function () {
        $(".mcq-bulk-operations").removeClass("mcq-set-loading");
      },
    });
  }

  /**
   * Update questions section display
   */
  function updateQuestionsSection(questionIds, sectionId) {
    const sectionName = sectionId
      ? $(`.mcq-set-section-item input[value="${sectionId}"]`)
          .closest(".mcq-set-section-item")
          .find(".mcq-set-section-name")
          .val()
      : "";

    questionIds.forEach(function (questionId) {
      const questionItem = $(
        `.mcq-set-question-item input[value="${questionId}"]`
      ).closest(".mcq-set-question-item");

      // Update hidden field
      questionItem.find('input[name*="[section_id]"]').val(sectionId);

      // Update display
      questionItem.find(".mcq-set-question-section").remove();
      if (sectionName) {
        const sectionBadge = `<span class="mcq-set-question-section">${mcqSetBuilderL10n.section}: ${sectionName}</span>`;
        questionItem.find(".mcq-set-question-content").append(sectionBadge);
      }
    });
  }

  /**
   * Remove questions from DOM
   */
  function removeQuestionsFromDOM(questionIds) {
    questionIds.forEach(function (questionId) {
      const questionItem = $(
        `.mcq-set-question-item input[value="${questionId}"]`
      ).closest(".mcq-set-question-item");

      questionItem.fadeOut(300, function () {
        $(this).remove();
        updateQuestionOrder();
        updateNoQuestionsMessage();
        updateQuestionCounter();
      });
    });
  }

  /**
   * Utility Functions
   */
  function showMessage(message, type = "info") {
    const messageHtml = `<div class="mcq-set-message ${type}">${message}</div>`;

    // Remove existing messages
    $(".mcq-set-message").remove();

    // Add new message
    $(".mcq-set-tab-content.active .mcq-set-tab-inner").prepend(messageHtml);

    // Auto-hide after 5 seconds
    setTimeout(function () {
      $(".mcq-set-message").fadeOut(300, function () {
        $(this).remove();
      });
    }, 5000);
  }

  // Expose functions globally for external use
  window.MCQSetBuilder = {
    addNewSection: addNewSection,
    updateSectionSelects: updateSectionSelects,
    showMessage: showMessage,
    markAsChanged: markAsChanged,
  };
})(jQuery);
