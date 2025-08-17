/**
 * MCQHome Registration JavaScript
 * Handles the frontend registration functionality with role selection
 */

jQuery(document).ready(function ($) {
  console.log("MCQHome Registration script loaded");

  // Global variables
  let selectedRole = null;

  // DOM elements
  const $roleCards = $(".role-card");
  const $roleSelectionStep = $("#step-role-selection");
  const $registrationFormStep = $("#step-registration-form");
  const $backToRolesBtn = $("#back-to-roles");
  const $registrationForm = $("#mcqhome-registration-form");
  const $messagesDiv = $("#registration-messages");
  const $formTitle = $("#form-title");
  const $formSubtitle = $("#form-subtitle");
  const $selectedRoleInput = $("#selected-role");

  // Role-specific field containers
  const $roleSpecificFields = $("#role-specific-fields");
  const $studentFields = $("#student-fields");
  const $teacherFields = $("#teacher-fields");
  const $institutionFields = $("#institution-fields");

  // Role configuration
  const roleConfig = {
    student: {
      title: "Student Registration",
      subtitle: "Start your learning journey with MCQHome",
      color: "blue",
    },
    teacher: {
      title: "Teacher Registration",
      subtitle: "Join as a teacher and create amazing MCQs",
      color: "green",
    },
    institution: {
      title: "Institution Registration",
      subtitle: "Register your institution and manage your team",
      color: "purple",
    },
  };

  // Utility functions
  function showMessage(message, type = "info") {
    const messageClass = type === "error" ? "message-error" : "message-success";
    $messagesDiv.html(
      '<div class="message ' + messageClass + '">' + message + "</div>"
    );

    // Auto-hide success messages after 5 seconds
    if (type === "success") {
      setTimeout(function () {
        $messagesDiv.html("");
      }, 5000);
    }
  }

  function clearMessages() {
    $messagesDiv.html("");
  }

  function resetFormCompletely() {
    // Reset form data
    $registrationForm[0].reset();
    selectedRole = null;
    $selectedRoleInput.val("");

    // Clear validation states
    $registrationForm.find("input").each(function () {
      this.setCustomValidity("");
    });

    // Hide role-specific fields
    $(".role-fields").removeClass("active").hide();
    $roleSpecificFields.hide();

    // Reset role cards
    $roleCards.removeClass("selected");
    $roleCards.css("transform", "translateY(0)");

    // Reset form titles
    $formTitle.text("Create Your Account");
    $formSubtitle.text("Join MCQHome and start your journey");

    // Clear messages
    clearMessages();

    // Show role selection step
    showStep("role-selection");
  }

  function showStep(step) {
    console.log("Showing step:", step);

    if (step === "role-selection") {
      $roleSelectionStep.addClass("active").show();
      $registrationFormStep.removeClass("active").hide();
    } else if (step === "registration-form") {
      $roleSelectionStep.removeClass("active").hide();
      $registrationFormStep.addClass("active").show();
    }
  }

  function selectRole(role) {
    console.log("Role selected:", role);
    selectedRole = role;

    // Clear any previous messages
    clearMessages();

    // Update visual selection
    $roleCards.removeClass("selected");
    $('[data-role="' + role + '"]').addClass("selected");

    // Set hidden input value
    $selectedRoleInput.val(role);

    // Update form title and subtitle
    if (roleConfig[role]) {
      $formTitle.text(roleConfig[role].title);
      $formSubtitle.text(roleConfig[role].subtitle);
    }

    // Show/hide role-specific fields
    $(".role-fields").removeClass("active").hide();
    $("#" + role + "-fields")
      .addClass("active")
      .show();
    $roleSpecificFields.show();

    // Show registration form after a short delay for smooth transition
    setTimeout(function () {
      showStep("registration-form");
    }, 300);
  }

  function validateForm() {
    const firstName = $("#first_name").val().trim();
    const lastName = $("#last_name").val().trim();
    const email = $("#email").val().trim();
    const password = $("#password").val();
    const confirmPassword = $("#confirm_password").val();
    const termsAccepted = $("#terms_accepted").is(":checked");

    // Basic validation
    if (!firstName) {
      showMessage("First name is required.", "error");
      return false;
    }

    if (!lastName) {
      showMessage("Last name is required.", "error");
      return false;
    }

    if (!email || !isValidEmail(email)) {
      showMessage("Please enter a valid email address.", "error");
      return false;
    }

    if (!password || password.length < 8) {
      showMessage("Password must be at least 8 characters long.", "error");
      return false;
    }

    if (password !== confirmPassword) {
      showMessage("Passwords do not match.", "error");
      return false;
    }

    if (!termsAccepted) {
      showMessage("You must accept the terms and conditions.", "error");
      return false;
    }

    // Role-specific validation
    if (selectedRole === "institution") {
      const institutionName = $("#institution_name").val().trim();
      if (!institutionName) {
        showMessage("Institution name is required.", "error");
        return false;
      }
    }

    return true;
  }

  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  function handleFormSubmit(e) {
    e.preventDefault();

    if (!selectedRole) {
      showMessage("Please select a role first.", "error");
      showStep("role-selection");
      return;
    }

    if (!validateForm()) {
      return;
    }

    // Clear any previous messages
    clearMessages();

    // Show loading state
    const $submitButton = $registrationForm.find('button[type="submit"]');
    const originalText = $submitButton.text();
    $submitButton.text(mcqhome_ajax.messages.processing).prop("disabled", true);

    // Add visual loading indicator
    $submitButton.addClass("opacity-75 cursor-not-allowed");

    // Prepare form data
    const formData = $registrationForm.serialize();

    // Add action for AJAX
    const ajaxData = formData + "&action=mcqhome_register";

    // Submit via AJAX
    $.ajax({
      url: mcqhome_ajax.ajax_url,
      type: "POST",
      data: ajaxData,
      dataType: "json",
      success: function (response) {
        if (response.success) {
          showMessage(response.message, "success");

          // Reset everything after showing success message
          setTimeout(function () {
            resetFormCompletely();
          }, 3000);
        } else {
          showMessage(response.message || mcqhome_ajax.messages.error, "error");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
        console.error("Response:", xhr.responseText);

        let errorMessage = mcqhome_ajax.messages.error;

        // Try to parse error response for more specific error
        try {
          const errorResponse = JSON.parse(xhr.responseText);
          if (errorResponse.message) {
            errorMessage = errorResponse.message;
          }
        } catch (e) {
          // Use default error message
        }

        showMessage(errorMessage, "error");
      },
      complete: function () {
        // Always reset button state regardless of success or failure
        $submitButton.text(originalText).prop("disabled", false);
        $submitButton.removeClass("opacity-75 cursor-not-allowed");
      },
    });
  }

  // Event listeners

  // Bind role card clicks
  $roleCards.on("click", function (e) {
    e.preventDefault();
    const role = $(this).data("role");
    console.log("Role card clicked:", role);
    selectRole(role);
  });

  // Add keyboard support for role cards
  $roleCards.on("keydown", function (e) {
    if (e.key === "Enter" || e.key === " ") {
      e.preventDefault();
      const role = $(this).data("role");
      selectRole(role);
    }
  });

  // Make cards focusable
  $roleCards.attr("tabindex", "0");

  // Bind back button
  $backToRolesBtn.on("click", function (e) {
    e.preventDefault();
    console.log("Back to roles clicked");

    // Reset form but keep the selected role for better UX
    $registrationForm[0].reset();

    // Clear validation states
    $registrationForm.find("input").each(function () {
      this.setCustomValidity("");
    });

    clearMessages();
    showStep("role-selection");
  });

  // Bind form submission
  $registrationForm.on("submit", handleFormSubmit);

  // Real-time password confirmation validation
  const $passwordField = $("#password");
  const $confirmPasswordField = $("#confirm_password");

  $confirmPasswordField.on("input", function () {
    const password = $passwordField.val();
    const confirmPassword = $(this).val();

    if (confirmPassword && password && confirmPassword !== password) {
      this.setCustomValidity("Passwords do not match");
    } else {
      this.setCustomValidity("");
    }
  });

  $passwordField.on("input", function () {
    const password = $(this).val();
    const confirmPassword = $confirmPasswordField.val();

    if (confirmPassword && password !== confirmPassword) {
      $confirmPasswordField[0].setCustomValidity("Passwords do not match");
    } else {
      $confirmPasswordField[0].setCustomValidity("");
    }
  });

  // Add hover effects for better accessibility
  $roleCards.on("mouseenter", function () {
    if (!$(this).hasClass("selected")) {
      $(this).css("transform", "translateY(-2px)");
    }
  });

  $roleCards.on("mouseleave", function () {
    if (!$(this).hasClass("selected")) {
      $(this).css("transform", "translateY(0)");
    }
  });

  console.log("MCQHome Registration script initialized successfully");
});
