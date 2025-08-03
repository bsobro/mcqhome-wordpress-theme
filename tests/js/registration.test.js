/**
 * JavaScript tests for registration functionality
 */

describe("Registration JavaScript Functionality", () => {
  let registrationModule;

  beforeEach(() => {
    // Mock registration data
    global.mcqhome_registration = {
      ajax_url: "/wp-admin/admin-ajax.php",
      nonce: "test-nonce",
      strings: {
        selectRole: "Please select a role",
        fillRequired: "Please fill in all required fields",
        emailInvalid: "Please enter a valid email address",
        passwordMismatch: "Passwords do not match",
        registrationSuccess: "Registration successful!",
        registrationError: "Registration failed. Please try again.",
      },
    };

    // Create registration form HTML
    document.body.innerHTML = `
      <form id="mcqhome-registration-form">
        <div class="role-selection">
          <label>
            <input type="radio" name="user_role" value="student"> Student
          </label>
          <label>
            <input type="radio" name="user_role" value="teacher"> Teacher
          </label>
          <label>
            <input type="radio" name="user_role" value="institution"> Institution
          </label>
        </div>
        
        <div class="form-fields">
          <input type="text" name="user_login" placeholder="Username" required>
          <input type="email" name="user_email" placeholder="Email" required>
          <input type="password" name="user_pass" placeholder="Password" required>
          <input type="password" name="user_pass_confirm" placeholder="Confirm Password" required>
          <input type="text" name="first_name" placeholder="First Name" required>
          <input type="text" name="last_name" placeholder="Last Name" required>
        </div>
        
        <div class="role-specific-fields" style="display: none;">
          <div class="teacher-fields" style="display: none;">
            <select name="institution_id">
              <option value="">Select Institution</option>
              <option value="1">Test University</option>
              <option value="2">MCQ Academy</option>
            </select>
            <input type="text" name="specialization" placeholder="Specialization">
          </div>
          
          <div class="institution-fields" style="display: none;">
            <input type="text" name="institution_name" placeholder="Institution Name">
            <select name="institution_type">
              <option value="">Select Type</option>
              <option value="university">University</option>
              <option value="school">School</option>
              <option value="training">Training Center</option>
            </select>
          </div>
        </div>
        
        <div class="form-messages">
          <div class="success-message" style="display: none;"></div>
          <div class="error-message" style="display: none;"></div>
        </div>
        
        <button type="submit" id="registration-submit">Register</button>
      </form>
    `;

    // Mock registration module
    registrationModule = {
      form: null,
      selectedRole: null,

      init: function () {
        this.form = document.getElementById("mcqhome-registration-form");
        this.bindEvents();
      },

      bindEvents: function () {
        // Role selection
        document
          .querySelectorAll('input[name="user_role"]')
          .forEach((radio) => {
            radio.addEventListener("change", (e) => {
              this.handleRoleChange(e.target.value);
            });
          });

        // Form submission
        if (this.form) {
          this.form.addEventListener("submit", (e) => {
            e.preventDefault();
            this.handleSubmit();
          });
        }

        // Real-time validation
        document.querySelectorAll("input[required]").forEach((input) => {
          input.addEventListener("blur", () => {
            this.validateField(input);
          });
        });

        // Password confirmation
        const passwordConfirm = document.querySelector(
          'input[name="user_pass_confirm"]'
        );
        if (passwordConfirm) {
          passwordConfirm.addEventListener("input", () => {
            this.validatePasswordMatch();
          });
        }
      },

      handleRoleChange: function (role) {
        this.selectedRole = role;
        this.showRoleSpecificFields(role);
        this.updateRequiredFields(role);
      },

      showRoleSpecificFields: function (role) {
        const roleSpecificContainer = document.querySelector(
          ".role-specific-fields"
        );
        const teacherFields = document.querySelector(".teacher-fields");
        const institutionFields = document.querySelector(".institution-fields");

        // Hide all role-specific fields first
        if (roleSpecificContainer) roleSpecificContainer.style.display = "none";
        if (teacherFields) teacherFields.style.display = "none";
        if (institutionFields) institutionFields.style.display = "none";

        // Show relevant fields based on role
        if (role === "teacher") {
          if (roleSpecificContainer)
            roleSpecificContainer.style.display = "block";
          if (teacherFields) teacherFields.style.display = "block";
        } else if (role === "institution") {
          if (roleSpecificContainer)
            roleSpecificContainer.style.display = "block";
          if (institutionFields) institutionFields.style.display = "block";
        }
      },

      updateRequiredFields: function (role) {
        // Remove existing required attributes from role-specific fields
        document
          .querySelectorAll(
            ".role-specific-fields input, .role-specific-fields select"
          )
          .forEach((field) => {
            field.removeAttribute("required");
          });

        // Add required attributes based on role
        if (role === "teacher") {
          const institutionSelect = document.querySelector(
            'select[name="institution_id"]'
          );
          if (institutionSelect)
            institutionSelect.setAttribute("required", "required");
        } else if (role === "institution") {
          const institutionName = document.querySelector(
            'input[name="institution_name"]'
          );
          const institutionType = document.querySelector(
            'select[name="institution_type"]'
          );
          if (institutionName)
            institutionName.setAttribute("required", "required");
          if (institutionType)
            institutionType.setAttribute("required", "required");
        }
      },

      validateField: function (field) {
        const value = field.value.trim();
        let isValid = true;
        let message = "";

        // Check if required field is empty
        if (field.hasAttribute("required") && !value) {
          isValid = false;
          message = "This field is required";
        }

        // Email validation
        if (field.type === "email" && value) {
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(value)) {
            isValid = false;
            message = "Please enter a valid email address";
          }
        }

        // Username validation
        if (field.name === "user_login" && value) {
          const usernameRegex = /^[a-zA-Z0-9_-]+$/;
          if (!usernameRegex.test(value) || value.length < 3) {
            isValid = false;
            message =
              "Username must be at least 3 characters and contain only letters, numbers, hyphens, and underscores";
          }
        }

        // Password strength validation
        if (field.name === "user_pass" && value) {
          if (value.length < 8) {
            isValid = false;
            message = "Password must be at least 8 characters long";
          }
        }

        this.showFieldValidation(field, isValid, message);
        return isValid;
      },

      validatePasswordMatch: function () {
        const password = document.querySelector('input[name="user_pass"]');
        const confirmPassword = document.querySelector(
          'input[name="user_pass_confirm"]'
        );

        if (password && confirmPassword) {
          const isMatch = password.value === confirmPassword.value;
          const message = isMatch ? "" : "Passwords do not match";

          this.showFieldValidation(confirmPassword, isMatch, message);
          return isMatch;
        }

        return true;
      },

      showFieldValidation: function (field, isValid, message) {
        // Remove existing validation classes and messages
        field.classList.remove("field-valid", "field-invalid");

        const existingMessage = field.parentNode.querySelector(
          ".field-validation-message"
        );
        if (existingMessage) {
          existingMessage.remove();
        }

        // Add validation class and message
        if (message) {
          field.classList.add(isValid ? "field-valid" : "field-invalid");

          const messageElement = document.createElement("div");
          messageElement.className = `field-validation-message ${
            isValid ? "success" : "error"
          }`;
          messageElement.textContent = message;
          field.parentNode.appendChild(messageElement);
        }
      },

      validateForm: function () {
        let isValid = true;
        const errors = [];

        // Check if role is selected
        if (!this.selectedRole) {
          isValid = false;
          errors.push(global.mcqhome_registration.strings.selectRole);
        }

        // Validate all required fields
        const requiredFields = this.form.querySelectorAll(
          "input[required], select[required]"
        );
        requiredFields.forEach((field) => {
          if (!this.validateField(field)) {
            isValid = false;
          }
        });

        // Validate password match
        if (!this.validatePasswordMatch()) {
          isValid = false;
          errors.push(global.mcqhome_registration.strings.passwordMismatch);
        }

        return { isValid, errors };
      },

      handleSubmit: function () {
        const validation = this.validateForm();

        if (!validation.isValid) {
          this.showMessage("error", validation.errors.join("<br>"));
          return;
        }

        // Collect form data
        const formData = new FormData(this.form);
        formData.append("action", "mcqhome_register_user");
        formData.append("nonce", global.mcqhome_registration.nonce);

        // Show loading state
        const submitButton = document.getElementById("registration-submit");
        const originalText = submitButton.textContent;
        submitButton.textContent = "Registering...";
        submitButton.disabled = true;

        // Submit registration
        this.submitRegistration(formData)
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              this.showMessage(
                "success",
                data.data.message ||
                  global.mcqhome_registration.strings.registrationSuccess
              );

              // Redirect after successful registration
              setTimeout(() => {
                window.location.href = data.data.redirect_url || "/dashboard/";
              }, 2000);
            } else {
              this.showMessage(
                "error",
                data.data.message ||
                  global.mcqhome_registration.strings.registrationError
              );
            }
          })
          .catch((error) => {
            console.error("Registration error:", error);
            this.showMessage(
              "error",
              global.mcqhome_registration.strings.registrationError
            );
          })
          .finally(() => {
            // Restore button state
            submitButton.textContent = originalText;
            submitButton.disabled = false;
          });
      },

      submitRegistration: function (formData) {
        return global.fetch(global.mcqhome_registration.ajax_url, {
          method: "POST",
          body: formData,
        });
      },

      showMessage: function (type, message) {
        const successElement = document.querySelector(".success-message");
        const errorElement = document.querySelector(".error-message");

        // Hide both messages first
        if (successElement) successElement.style.display = "none";
        if (errorElement) errorElement.style.display = "none";

        // Show appropriate message
        if (type === "success" && successElement) {
          successElement.innerHTML = message;
          successElement.style.display = "block";
        } else if (type === "error" && errorElement) {
          errorElement.innerHTML = message;
          errorElement.style.display = "block";
        }

        // Auto-hide success messages
        if (type === "success") {
          setTimeout(() => {
            if (successElement) successElement.style.display = "none";
          }, 5000);
        }
      },
    };
  });

  describe("Registration Form Initialization", () => {
    test("should initialize registration module correctly", () => {
      registrationModule.init();

      expect(registrationModule.form).toBeTruthy();
      expect(registrationModule.selectedRole).toBeNull();
    });

    test("should bind event listeners", () => {
      registrationModule.init();

      const roleRadios = document.querySelectorAll('input[name="user_role"]');
      const form = document.getElementById("mcqhome-registration-form");

      expect(roleRadios.length).toBe(3);
      expect(form).toBeTruthy();
    });
  });

  describe("Role Selection", () => {
    beforeEach(() => {
      registrationModule.init();
    });

    test("should handle role selection", () => {
      const studentRadio = document.querySelector('input[value="student"]');

      studentRadio.checked = true;
      studentRadio.dispatchEvent(new Event("change"));

      expect(registrationModule.selectedRole).toBe("student");
    });

    test("should show teacher-specific fields when teacher is selected", () => {
      registrationModule.handleRoleChange("teacher");

      const roleSpecificFields = document.querySelector(
        ".role-specific-fields"
      );
      const teacherFields = document.querySelector(".teacher-fields");

      expect(roleSpecificFields.style.display).toBe("block");
      expect(teacherFields.style.display).toBe("block");
    });

    test("should show institution-specific fields when institution is selected", () => {
      registrationModule.handleRoleChange("institution");

      const roleSpecificFields = document.querySelector(
        ".role-specific-fields"
      );
      const institutionFields = document.querySelector(".institution-fields");

      expect(roleSpecificFields.style.display).toBe("block");
      expect(institutionFields.style.display).toBe("block");
    });

    test("should hide role-specific fields for student", () => {
      registrationModule.handleRoleChange("student");

      const roleSpecificFields = document.querySelector(
        ".role-specific-fields"
      );

      expect(roleSpecificFields.style.display).toBe("none");
    });

    test("should update required fields based on role", () => {
      registrationModule.handleRoleChange("teacher");

      const institutionSelect = document.querySelector(
        'select[name="institution_id"]'
      );
      expect(institutionSelect.hasAttribute("required")).toBe(true);

      registrationModule.handleRoleChange("institution");

      const institutionName = document.querySelector(
        'input[name="institution_name"]'
      );
      const institutionType = document.querySelector(
        'select[name="institution_type"]'
      );

      expect(institutionName.hasAttribute("required")).toBe(true);
      expect(institutionType.hasAttribute("required")).toBe(true);
    });
  });

  describe("Field Validation", () => {
    beforeEach(() => {
      registrationModule.init();
    });

    test("should validate required fields", () => {
      const usernameField = document.querySelector('input[name="user_login"]');
      usernameField.value = "";

      const isValid = registrationModule.validateField(usernameField);

      expect(isValid).toBe(false);
      expect(usernameField.classList.contains("field-invalid")).toBe(true);
    });

    test("should validate email format", () => {
      const emailField = document.querySelector('input[name="user_email"]');

      // Invalid email
      emailField.value = "invalid-email";
      let isValid = registrationModule.validateField(emailField);
      expect(isValid).toBe(false);

      // Valid email
      emailField.value = "test@example.com";
      isValid = registrationModule.validateField(emailField);
      expect(isValid).toBe(true);
    });

    test("should validate username format", () => {
      const usernameField = document.querySelector('input[name="user_login"]');

      // Too short
      usernameField.value = "ab";
      let isValid = registrationModule.validateField(usernameField);
      expect(isValid).toBe(false);

      // Invalid characters
      usernameField.value = "user@name";
      isValid = registrationModule.validateField(usernameField);
      expect(isValid).toBe(false);

      // Valid username
      usernameField.value = "valid_username123";
      isValid = registrationModule.validateField(usernameField);
      expect(isValid).toBe(true);
    });

    test("should validate password strength", () => {
      const passwordField = document.querySelector('input[name="user_pass"]');

      // Too short
      passwordField.value = "123";
      let isValid = registrationModule.validateField(passwordField);
      expect(isValid).toBe(false);

      // Valid password
      passwordField.value = "strongpassword123";
      isValid = registrationModule.validateField(passwordField);
      expect(isValid).toBe(true);
    });

    test("should validate password confirmation", () => {
      const passwordField = document.querySelector('input[name="user_pass"]');
      const confirmField = document.querySelector(
        'input[name="user_pass_confirm"]'
      );

      passwordField.value = "password123";
      confirmField.value = "different123";

      const isMatch = registrationModule.validatePasswordMatch();

      expect(isMatch).toBe(false);
      expect(confirmField.classList.contains("field-invalid")).toBe(true);
    });
  });

  describe("Form Submission", () => {
    beforeEach(() => {
      registrationModule.init();
      global.fetch.mockClear();
    });

    test("should prevent submission if validation fails", () => {
      // Don't select a role
      registrationModule.selectedRole = null;

      registrationModule.handleSubmit();

      expect(global.fetch).not.toHaveBeenCalled();

      const errorMessage = document.querySelector(".error-message");
      expect(errorMessage.style.display).toBe("block");
    });

    test("should submit form data when validation passes", () => {
      // Set up valid form data
      registrationModule.selectedRole = "student";
      document.querySelector('input[name="user_login"]').value = "testuser";
      document.querySelector('input[name="user_email"]').value =
        "test@example.com";
      document.querySelector('input[name="user_pass"]').value = "password123";
      document.querySelector('input[name="user_pass_confirm"]').value =
        "password123";
      document.querySelector('input[name="first_name"]').value = "Test";
      document.querySelector('input[name="last_name"]').value = "User";

      // Mock successful response
      global.fetch.mockResolvedValue({
        json: () =>
          Promise.resolve({
            success: true,
            data: { message: "Registration successful!" },
          }),
      });

      registrationModule.handleSubmit();

      expect(global.fetch).toHaveBeenCalledWith(
        global.mcqhome_registration.ajax_url,
        expect.objectContaining({
          method: "POST",
          body: expect.any(FormData),
        })
      );
    });

    test("should handle registration success", async () => {
      registrationModule.selectedRole = "student";

      // Fill required fields
      document.querySelector('input[name="user_login"]').value = "testuser";
      document.querySelector('input[name="user_email"]').value =
        "test@example.com";
      document.querySelector('input[name="user_pass"]').value = "password123";
      document.querySelector('input[name="user_pass_confirm"]').value =
        "password123";
      document.querySelector('input[name="first_name"]').value = "Test";
      document.querySelector('input[name="last_name"]').value = "User";

      global.fetch.mockResolvedValue({
        json: () =>
          Promise.resolve({
            success: true,
            data: { message: "Registration successful!" },
          }),
      });

      await registrationModule.handleSubmit();

      // Wait for async operations
      await new Promise((resolve) => setTimeout(resolve, 0));

      const successMessage = document.querySelector(".success-message");
      expect(successMessage.style.display).toBe("block");
      expect(successMessage.innerHTML).toBe("Registration successful!");
    });

    test("should handle registration error", async () => {
      registrationModule.selectedRole = "student";

      // Fill required fields
      document.querySelector('input[name="user_login"]').value = "testuser";
      document.querySelector('input[name="user_email"]').value =
        "test@example.com";
      document.querySelector('input[name="user_pass"]').value = "password123";
      document.querySelector('input[name="user_pass_confirm"]').value =
        "password123";
      document.querySelector('input[name="first_name"]').value = "Test";
      document.querySelector('input[name="last_name"]').value = "User";

      global.fetch.mockResolvedValue({
        json: () =>
          Promise.resolve({
            success: false,
            data: { message: "Username already exists" },
          }),
      });

      await registrationModule.handleSubmit();

      // Wait for async operations
      await new Promise((resolve) => setTimeout(resolve, 0));

      const errorMessage = document.querySelector(".error-message");
      expect(errorMessage.style.display).toBe("block");
      expect(errorMessage.innerHTML).toBe("Username already exists");
    });

    test("should disable submit button during submission", () => {
      registrationModule.selectedRole = "student";

      // Fill required fields
      document.querySelector('input[name="user_login"]').value = "testuser";
      document.querySelector('input[name="user_email"]').value =
        "test@example.com";
      document.querySelector('input[name="user_pass"]').value = "password123";
      document.querySelector('input[name="user_pass_confirm"]').value =
        "password123";
      document.querySelector('input[name="first_name"]').value = "Test";
      document.querySelector('input[name="last_name"]').value = "User";

      global.fetch.mockImplementation(() => new Promise(() => {})); // Never resolves

      registrationModule.handleSubmit();

      const submitButton = document.getElementById("registration-submit");
      expect(submitButton.disabled).toBe(true);
      expect(submitButton.textContent).toBe("Registering...");
    });
  });

  describe("Message Display", () => {
    beforeEach(() => {
      registrationModule.init();
    });

    test("should show success message", () => {
      registrationModule.showMessage("success", "Test success message");

      const successMessage = document.querySelector(".success-message");
      const errorMessage = document.querySelector(".error-message");

      expect(successMessage.style.display).toBe("block");
      expect(successMessage.innerHTML).toBe("Test success message");
      expect(errorMessage.style.display).toBe("none");
    });

    test("should show error message", () => {
      registrationModule.showMessage("error", "Test error message");

      const successMessage = document.querySelector(".success-message");
      const errorMessage = document.querySelector(".error-message");

      expect(errorMessage.style.display).toBe("block");
      expect(errorMessage.innerHTML).toBe("Test error message");
      expect(successMessage.style.display).toBe("none");
    });

    test("should auto-hide success messages", (done) => {
      jest.useFakeTimers();

      registrationModule.showMessage("success", "Auto-hide test");

      const successMessage = document.querySelector(".success-message");
      expect(successMessage.style.display).toBe("block");

      // Fast-forward time
      jest.advanceTimersByTime(5000);

      setTimeout(() => {
        expect(successMessage.style.display).toBe("none");
        jest.useRealTimers();
        done();
      }, 0);
    });
  });
});
