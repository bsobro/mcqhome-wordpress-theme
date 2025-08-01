/**
 * MCQ Builder JavaScript
 * Enhanced WYSIWYG MCQ creation interface with real-time preview
 *
 * @package MCQHome
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  // MCQ Builder Class
  class MCQBuilder {
    constructor() {
      this.init();
    }

    init() {
      this.bindEvents();
      this.initializePreview();
      this.setupAutoSave();
      this.initializeCharacterCounting();
    }

    bindEvents() {
      // Real-time preview updates
      $(document).on(
        "input change",
        '#mcq_question_text, input[name^="mcq_option_"], input[name="mcq_correct_answer"]',
        () => {
          this.updatePreview();
        }
      );

      // TinyMCE editor changes for question text
      if (typeof tinymce !== "undefined") {
        $(document).on("tinymce-editor-init", (event, editor) => {
          if (editor.id === "mcq_question_text") {
            editor.on("input change keyup", () => {
              this.updatePreview();
            });
          }
          if (editor.id === "mcq_explanation") {
            editor.on("input change keyup", () => {
              this.updateExplanationPreview();
            });
          }
        });
      }

      // Correct answer selection
      $(document).on("change", 'input[name="mcq_correct_answer"]', (e) => {
        this.updateCorrectAnswerIndicators();
        this.updatePreview();
      });

      // Option input changes
      $(document).on("input", 'input[name^="mcq_option_"]', (e) => {
        this.updateCharacterCount($(e.target));
        this.updatePreview();
      });

      // Auto-save trigger
      $(document).on("input change", ".mcq-form-field", () => {
        this.triggerAutoSave();
      });

      // Media upload handling
      this.initializeMediaUpload();
    }

    initializePreview() {
      this.createPreviewContainer();
      this.updatePreview();
    }

    createPreviewContainer() {
      if ($("#mcq-live-preview").length === 0) {
        const previewHtml = `
                    <div id="mcq-live-preview" class="mcq-preview-container">
                        <h4 class="mcq-preview-title">
                            <span class="dashicons dashicons-visibility"></span>
                            ${mcqBuilderL10n.livePreview}
                        </h4>
                        <div class="mcq-preview-content">
                            <div id="mcq-preview-question" class="mcq-preview-question"></div>
                            <div id="mcq-preview-options" class="mcq-preview-options"></div>
                            <div id="mcq-preview-explanation" class="mcq-preview-explanation" style="display: none;">
                                <h5>${mcqBuilderL10n.explanation}</h5>
                                <div id="mcq-preview-explanation-content"></div>
                            </div>
                        </div>
                        <div class="mcq-preview-toggle">
                            <button type="button" id="toggle-explanation-preview" class="button button-secondary">
                                ${mcqBuilderL10n.showExplanation}
                            </button>
                        </div>
                    </div>
                `;

        // Insert after the answer options meta box
        $("#mcq_answer_options").after(previewHtml);

        // Toggle explanation preview
        $("#toggle-explanation-preview").on("click", () => {
          const $explanation = $("#mcq-preview-explanation");
          const $button = $("#toggle-explanation-preview");

          if ($explanation.is(":visible")) {
            $explanation.slideUp();
            $button.text(mcqBuilderL10n.showExplanation);
          } else {
            $explanation.slideDown();
            $button.text(mcqBuilderL10n.hideExplanation);
            this.updateExplanationPreview();
          }
        });
      }
    }

    updatePreview() {
      const questionText = this.getQuestionText();
      const options = this.getOptions();
      const correctAnswer = this.getCorrectAnswer();

      // Update question preview
      $("#mcq-preview-question").html(
        questionText || `<em>${mcqBuilderL10n.questionPlaceholder}</em>`
      );

      // Update options preview
      let optionsHtml = "";
      ["A", "B", "C", "D"].forEach((letter) => {
        const optionText =
          options[letter] ||
          mcqBuilderL10n.optionPlaceholder.replace("%s", letter);
        const isCorrect = correctAnswer === letter;
        const correctClass = isCorrect ? "mcq-preview-option-correct" : "";

        optionsHtml += `
                    <div class="mcq-preview-option ${correctClass}" data-option="${letter}">
                        <label class="mcq-preview-option-label">
                            <input type="radio" name="preview_answer" value="${letter}" ${
          isCorrect ? "checked" : ""
        } disabled>
                            <span class="mcq-preview-option-text">
                                <strong>${letter}.</strong> ${optionText}
                            </span>
                        </label>
                    </div>
                `;
      });

      $("#mcq-preview-options").html(optionsHtml);
    }

    updateExplanationPreview() {
      const explanation = this.getExplanationText();
      $("#mcq-preview-explanation-content").html(
        explanation || `<em>${mcqBuilderL10n.explanationPlaceholder}</em>`
      );
    }

    getQuestionText() {
      if (typeof tinymce !== "undefined" && tinymce.get("mcq_question_text")) {
        return tinymce.get("mcq_question_text").getContent();
      }
      return $("#mcq_question_text").val();
    }

    getExplanationText() {
      if (typeof tinymce !== "undefined" && tinymce.get("mcq_explanation")) {
        return tinymce.get("mcq_explanation").getContent();
      }
      return $("#mcq_explanation").val();
    }

    getOptions() {
      return {
        A: $('input[name="mcq_option_a"]').val(),
        B: $('input[name="mcq_option_b"]').val(),
        C: $('input[name="mcq_option_c"]').val(),
        D: $('input[name="mcq_option_d"]').val(),
      };
    }

    getCorrectAnswer() {
      return $('input[name="mcq_correct_answer"]:checked').val();
    }

    updateCorrectAnswerIndicators() {
      // Remove all existing indicators
      $(".mcq-correct-indicator").remove();

      // Add indicator to selected option
      const selectedValue = $('input[name="mcq_correct_answer"]:checked').val();
      if (selectedValue) {
        const $selectedRow = $(
          `input[name="mcq_correct_answer"][value="${selectedValue}"]`
        ).closest(".mcq-option-row");
        $selectedRow.append(
          `<span class="mcq-correct-indicator">✓ ${mcqBuilderL10n.correctAnswer}</span>`
        );
      }
    }

    initializeCharacterCounting() {
      // Add character counters to option inputs
      $('input[name^="mcq_option_"]').each((index, element) => {
        const $input = $(element);
        const maxLength = 200; // Set reasonable limit

        // Add counter display
        const counterId = `char-count-${$input.attr("name")}`;
        $input.after(
          `<div class="mcq-char-counter" id="${counterId}">0/${maxLength}</div>`
        );

        // Update counter
        this.updateCharacterCount($input);
      });
    }

    updateCharacterCount($input) {
      const maxLength = 200;
      const currentLength = $input.val().length;
      const counterId = `char-count-${$input.attr("name")}`;
      const $counter = $(`#${counterId}`);

      $counter.text(`${currentLength}/${maxLength}`);

      // Add warning class if approaching limit
      if (currentLength > maxLength * 0.8) {
        $counter.addClass("mcq-char-warning");
      } else {
        $counter.removeClass("mcq-char-warning");
      }

      // Add error class if over limit
      if (currentLength > maxLength) {
        $counter.addClass("mcq-char-error");
        $input.addClass("mcq-input-error");
      } else {
        $counter.removeClass("mcq-char-error");
        $input.removeClass("mcq-input-error");
      }
    }

    setupAutoSave() {
      this.autoSaveTimer = null;
      this.autoSaveInterval = 30000; // 30 seconds
    }

    triggerAutoSave() {
      clearTimeout(this.autoSaveTimer);
      this.autoSaveTimer = setTimeout(() => {
        this.performAutoSave();
      }, this.autoSaveInterval);
    }

    performAutoSave() {
      const postId = $("#post_ID").val();
      if (!postId) return;

      const data = {
        action: "mcqhome_autosave_mcq",
        post_id: postId,
        nonce: mcqBuilderL10n.nonce,
        question_text: this.getQuestionText(),
        options: this.getOptions(),
        correct_answer: this.getCorrectAnswer(),
        explanation: this.getExplanationText(),
      };

      $.ajax({
        url: mcqBuilderL10n.ajaxUrl,
        type: "POST",
        data: data,
        success: (response) => {
          if (response.success) {
            this.showAutoSaveNotification(mcqBuilderL10n.autoSaveSuccess);
          }
        },
        error: () => {
          this.showAutoSaveNotification(mcqBuilderL10n.autoSaveError, "error");
        },
      });
    }

    showAutoSaveNotification(message, type = "success") {
      const notificationClass =
        type === "error" ? "notice-error" : "notice-success";
      const $notification = $(`
                <div class="notice ${notificationClass} is-dismissible mcq-autosave-notice">
                    <p>${message}</p>
                </div>
            `);

      $(".wrap h1").after($notification);

      // Auto-remove after 3 seconds
      setTimeout(() => {
        $notification.fadeOut(() => {
          $notification.remove();
        });
      }, 3000);
    }

    initializeMediaUpload() {
      // Enhanced media upload for question text
      if (typeof wp !== "undefined" && wp.media) {
        $(document).on("click", ".mcq-media-upload-btn", (e) => {
          e.preventDefault();

          const mediaUploader = wp.media({
            title: mcqBuilderL10n.selectMedia,
            button: {
              text: mcqBuilderL10n.useMedia,
            },
            multiple: false,
            library: {
              type: ["image", "video", "audio"],
            },
          });

          mediaUploader.on("select", () => {
            const attachment = mediaUploader
              .state()
              .get("selection")
              .first()
              .toJSON();
            this.insertMediaIntoQuestion(attachment);
          });

          mediaUploader.open();
        });

        // Initialize drag and drop for media upload
        this.initializeDragAndDrop();
      }

      // Quick add taxonomy terms
      $(document).on("click", ".mcq-quick-add-btn", (e) => {
        e.preventDefault();
        this.showQuickAddDialog($(e.target).data("taxonomy"));
      });
    }

    initializeDragAndDrop() {
      const $questionEditor = $("#mcq_question_text_ifr, #mcq_question_text");
      const $explanationEditor = $("#mcq_explanation_ifr, #mcq_explanation");

      // Create drop zones
      this.createDropZone($questionEditor, "question");
      this.createDropZone($explanationEditor, "explanation");
    }

    createDropZone($element, type) {
      $element.on("dragover", (e) => {
        e.preventDefault();
        e.stopPropagation();
        $element.addClass("mcq-drag-over");
      });

      $element.on("dragleave", (e) => {
        e.preventDefault();
        e.stopPropagation();
        $element.removeClass("mcq-drag-over");
      });

      $element.on("drop", (e) => {
        e.preventDefault();
        e.stopPropagation();
        $element.removeClass("mcq-drag-over");

        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
          this.handleFileUpload(files[0], type);
        }
      });
    }

    handleFileUpload(file, type) {
      // Check file type
      const allowedTypes = [
        "image/jpeg",
        "image/png",
        "image/gif",
        "image/webp",
        "video/mp4",
        "audio/mp3",
        "audio/wav",
      ];
      if (!allowedTypes.includes(file.type)) {
        alert(mcqBuilderL10n.invalidFileType);
        return;
      }

      // Check file size (10MB limit)
      if (file.size > 10 * 1024 * 1024) {
        alert(mcqBuilderL10n.fileTooLarge);
        return;
      }

      // Create form data
      const formData = new FormData();
      formData.append("file", file);
      formData.append("action", "mcqhome_upload_media");
      formData.append("nonce", mcqBuilderL10n.nonce);

      // Show upload progress
      this.showUploadProgress();

      // Upload file
      $.ajax({
        url: mcqBuilderL10n.ajaxUrl,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: (response) => {
          this.hideUploadProgress();
          if (response.success) {
            this.insertMediaIntoEditor(response.data, type);
          } else {
            alert(response.data || mcqBuilderL10n.uploadError);
          }
        },
        error: () => {
          this.hideUploadProgress();
          alert(mcqBuilderL10n.uploadError);
        },
      });
    }

    showUploadProgress() {
      if ($("#mcq-upload-progress").length === 0) {
        $("body").append(`
                    <div id="mcq-upload-progress" class="mcq-upload-overlay">
                        <div class="mcq-upload-progress">
                            <div class="mcq-upload-spinner"></div>
                            <p>${mcqBuilderL10n.uploading}</p>
                        </div>
                    </div>
                `);
      }
    }

    hideUploadProgress() {
      $("#mcq-upload-progress").remove();
    }

    insertMediaIntoEditor(attachment, type) {
      let mediaHtml = "";

      if (attachment.type === "image") {
        mediaHtml = `<img src="${attachment.url}" alt="${
          attachment.alt || ""
        }" style="max-width: 100%; height: auto;">`;
      } else if (attachment.type === "video") {
        mediaHtml = `<video controls style="max-width: 100%;"><source src="${attachment.url}" type="${attachment.mime}"></video>`;
      } else if (attachment.type === "audio") {
        mediaHtml = `<audio controls><source src="${attachment.url}" type="${attachment.mime}"></audio>`;
      }

      const editorId =
        type === "question" ? "mcq_question_text" : "mcq_explanation";

      // Insert into TinyMCE editor
      if (typeof tinymce !== "undefined" && tinymce.get(editorId)) {
        tinymce.get(editorId).insertContent(mediaHtml);
      } else {
        // Fallback for textarea
        const $textarea = $(`#${editorId}`);
        const currentContent = $textarea.val();
        $textarea.val(currentContent + "\n" + mediaHtml);
      }

      this.updatePreview();
    }

    showQuickAddDialog(taxonomy) {
      const taxonomyLabels = {
        mcq_subject: mcqBuilderL10n.addSubject,
        mcq_topic: mcqBuilderL10n.addTopic,
      };

      const label = taxonomyLabels[taxonomy] || mcqBuilderL10n.addTerm;

      const dialogHtml = `
                <div id="mcq-quick-add-dialog" class="mcq-dialog-overlay">
                    <div class="mcq-dialog">
                        <h3>${label}</h3>
                        <div class="mcq-dialog-content">
                            <label for="new-term-name">${mcqBuilderL10n.termName}</label>
                            <input type="text" id="new-term-name" class="widefat" placeholder="${mcqBuilderL10n.enterTermName}">
                            <label for="new-term-description">${mcqBuilderL10n.description} (${mcqBuilderL10n.optional})</label>
                            <textarea id="new-term-description" class="widefat" rows="3" placeholder="${mcqBuilderL10n.enterDescription}"></textarea>
                        </div>
                        <div class="mcq-dialog-actions">
                            <button type="button" class="button button-primary" id="save-new-term" data-taxonomy="${taxonomy}">${mcqBuilderL10n.addTerm}</button>
                            <button type="button" class="button" id="cancel-add-term">${mcqBuilderL10n.cancel}</button>
                        </div>
                    </div>
                </div>
            `;

      $("body").append(dialogHtml);

      // Handle dialog actions
      $("#save-new-term").on("click", (e) => {
        this.saveNewTerm($(e.target).data("taxonomy"));
      });

      $("#cancel-add-term").on("click", () => {
        $("#mcq-quick-add-dialog").remove();
      });

      // Focus on name input
      $("#new-term-name").focus();
    }

    saveNewTerm(taxonomy) {
      const name = $("#new-term-name").val().trim();
      const description = $("#new-term-description").val().trim();

      if (!name) {
        alert(mcqBuilderL10n.termNameRequired);
        return;
      }

      const data = {
        action: "mcqhome_add_taxonomy_term",
        taxonomy: taxonomy,
        name: name,
        description: description,
        nonce: mcqBuilderL10n.nonce,
      };

      $.ajax({
        url: mcqBuilderL10n.ajaxUrl,
        type: "POST",
        data: data,
        success: (response) => {
          if (response.success) {
            // Add new option to select
            const selectId =
              taxonomy === "mcq_subject"
                ? "mcq_subject_select"
                : "mcq_topic_select";
            $(`#${selectId}`).append(
              `<option value="${response.data.term_id}" selected>${response.data.name}</option>`
            );

            // Close dialog
            $("#mcq-quick-add-dialog").remove();

            // Show success message
            this.showAutoSaveNotification(
              mcqBuilderL10n.termAdded.replace("%s", response.data.name)
            );
          } else {
            alert(response.data || mcqBuilderL10n.termAddError);
          }
        },
        error: () => {
          alert(mcqBuilderL10n.termAddError);
        },
      });
    }

    insertMediaIntoQuestion(attachment) {
      let mediaHtml = "";

      if (attachment.type === "image") {
        mediaHtml = `<img src="${attachment.url}" alt="${attachment.alt}" style="max-width: 100%; height: auto;">`;
      } else if (attachment.type === "video") {
        mediaHtml = `<video controls style="max-width: 100%;"><source src="${attachment.url}" type="${attachment.mime}"></video>`;
      } else if (attachment.type === "audio") {
        mediaHtml = `<audio controls><source src="${attachment.url}" type="${attachment.mime}"></audio>`;
      }

      // Insert into TinyMCE editor
      if (typeof tinymce !== "undefined" && tinymce.get("mcq_question_text")) {
        tinymce.get("mcq_question_text").insertContent(mediaHtml);
      } else {
        // Fallback for textarea
        const $textarea = $("#mcq_question_text");
        const currentContent = $textarea.val();
        $textarea.val(currentContent + "\n" + mediaHtml);
      }

      this.updatePreview();
    }

    // Validation methods
    validateMCQ() {
      const errors = [];

      // Check question text
      const questionText = this.getQuestionText().trim();
      if (!questionText || questionText === "") {
        errors.push(mcqBuilderL10n.errorNoQuestion);
      }

      // Check options
      const options = this.getOptions();
      let emptyOptions = 0;
      Object.values(options).forEach((option) => {
        if (!option || option.trim() === "") {
          emptyOptions++;
        }
      });

      if (emptyOptions > 0) {
        errors.push(
          mcqBuilderL10n.errorEmptyOptions.replace("%d", emptyOptions)
        );
      }

      // Check correct answer
      const correctAnswer = this.getCorrectAnswer();
      if (!correctAnswer) {
        errors.push(mcqBuilderL10n.errorNoCorrectAnswer);
      }

      // Check explanation
      const explanation = this.getExplanationText().trim();
      if (!explanation || explanation === "") {
        errors.push(mcqBuilderL10n.errorNoExplanation);
      }

      return errors;
    }

    showValidationErrors(errors) {
      // Remove existing error notices
      $(".mcq-validation-error").remove();

      if (errors.length > 0) {
        let errorHtml =
          '<div class="notice notice-error mcq-validation-error"><ul>';
        errors.forEach((error) => {
          errorHtml += `<li>${error}</li>`;
        });
        errorHtml += "</ul></div>";

        $(".wrap h1").after(errorHtml);

        // Scroll to top to show errors
        $("html, body").animate({ scrollTop: 0 }, 500);

        return false;
      }

      return true;
    }
  }

  // Initialize MCQ Builder when document is ready
  $(document).ready(() => {
    // Only initialize on MCQ edit pages
    if ($("body").hasClass("post-type-mcq")) {
      new MCQBuilder();
    }
  });

  // Form submission validation
  $(document).on("submit", "#post", (e) => {
    if ($("body").hasClass("post-type-mcq")) {
      const builder = new MCQBuilder();
      const errors = builder.validateMCQ();

      if (!builder.showValidationErrors(errors)) {
        e.preventDefault();
        return false;
      }
    }
  });
})(jQuery);
