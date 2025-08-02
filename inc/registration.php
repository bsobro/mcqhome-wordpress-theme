<?php

/**
 * Custom User Registration System
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize registration system
 */
function mcqhome_init_registration()
{
    // Add custom registration form shortcode
    add_shortcode('mcqhome_registration', 'mcqhome_registration_form');

    // Handle registration form submission
    add_action('wp_ajax_nopriv_mcqhome_register_user', 'mcqhome_handle_registration');
    add_action('wp_ajax_mcqhome_register_user', 'mcqhome_handle_registration');

    // Handle email verification
    add_action('wp_ajax_nopriv_mcqhome_verify_email', 'mcqhome_handle_email_verification');
    add_action('wp_ajax_mcqhome_verify_email', 'mcqhome_handle_email_verification');

    // Customize login redirect based on role
    add_filter('login_redirect', 'mcqhome_login_redirect', 10, 3);

    // Disable default WordPress registration
    add_filter('wp_signup_location', 'mcqhome_redirect_signup');
    add_filter('register_url', 'mcqhome_custom_register_url');
}

/**
 * Registration form shortcode
 */
function mcqhome_registration_form($atts)
{
    $atts = shortcode_atts([
        'redirect' => '',
        'show_login_link' => true,
    ], $atts);

    // If user is already logged in, redirect to dashboard
    if (is_user_logged_in()) {
        $user_role = mcqhome_get_user_primary_role();
        $redirect_url = home_url('/dashboard/');
        return '<p>' . sprintf(__('You are already logged in. <a href="%s">Go to Dashboard</a>', 'mcqhome'), $redirect_url) . '</p>';
    }

    ob_start();
?>
    <div id="mcqhome-registration-form" class="mcqhome-form-container">
        <form id="mcqhome-register-form" class="mcqhome-register-form" method="post">
            <?php wp_nonce_field('mcqhome_register_nonce', 'mcqhome_register_nonce'); ?>

            <div class="form-header mb-6">
                <h2 class="text-2xl font-bold text-center mb-2"><?php _e('Create Your Account', 'mcqhome'); ?></h2>
                <p class="text-gray-600 text-center"><?php _e('Join MCQHome and start your learning journey', 'mcqhome'); ?></p>
            </div>

            <!-- Role Selection -->
            <div class="form-group mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <?php _e('Select Your Role', 'mcqhome'); ?> <span class="text-red-500">*</span>
                </label>
                <div class="space-y-2 mb-6">
                    <div class="flex items-center">
                        <input type="radio" id="role-student" name="user_role" value="student" required
                            class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                        <label for="role-student" class="ml-3 block text-sm font-medium text-gray-700">
                            <?php _e('Student', 'mcqhome'); ?> - <?php _e('Take MCQs and track progress', 'mcqhome'); ?>
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="radio" id="role-teacher" name="user_role" value="teacher"
                            class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                        <label for="role-teacher" class="ml-3 block text-sm font-medium text-gray-700">
                            <?php _e('Teacher', 'mcqhome'); ?> - <?php _e('Create and manage MCQs', 'mcqhome'); ?>
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="radio" id="role-institution" name="user_role" value="institution"
                            class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                        <label for="role-institution" class="ml-3 block text-sm font-medium text-gray-700">
                            <?php _e('Institution', 'mcqhome'); ?> - <?php _e('Manage teachers and students', 'mcqhome'); ?>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="basic-info-section">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-group">
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                            <?php _e('First Name', 'mcqhome'); ?> <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="first_name" name="first_name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div class="form-group">
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                            <?php _e('Last Name', 'mcqhome'); ?> <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="last_name" name="last_name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        <?php _e('Email Address', 'mcqhome'); ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-group">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            <?php _e('Password', 'mcqhome'); ?> <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="password" name="password" required minlength="8"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1"><?php _e('Minimum 8 characters', 'mcqhome'); ?></p>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">
                            <?php _e('Confirm Password', 'mcqhome'); ?> <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Role-specific fields (will be shown/hidden based on role selection) -->
            <div id="role-specific-fields" class="role-specific-section" style="display: none;">

                <!-- Teacher-specific fields -->
                <div id="teacher-fields" class="role-fields" style="display: none;">
                    <h3 class="text-lg font-semibold mb-4 text-green-700"><?php _e('Teacher Information', 'mcqhome'); ?></h3>

                    <div class="form-group mb-4">
                        <label for="teacher_specialization" class="block text-sm font-medium text-gray-700 mb-1">
                            <?php _e('Specialization', 'mcqhome'); ?>
                        </label>
                        <input type="text" id="teacher_specialization" name="specialization"
                            placeholder="<?php _e('e.g., Mathematics, Physics, Chemistry', 'mcqhome'); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-4">
                        <p class="text-sm text-green-700">
                            <strong><?php _e('Note:', 'mcqhome'); ?></strong> 
                            <?php _e('You will be automatically assigned to MCQ Academy (our default institution) during registration. You can change your institution later when invited by another institution from their dashboard.', 'mcqhome'); ?>
                        </p>
                    </div>
                </div>

                <!-- Institution-specific fields -->
                <div id="institution-fields" class="role-fields" style="display: none;">
                    <h3 class="text-lg font-semibold mb-4 text-purple-700"><?php _e('Institution Information', 'mcqhome'); ?></h3>

                    <div class="form-group mb-4">
                        <label for="institution_name" class="block text-sm font-medium text-gray-700 mb-1">
                            <?php _e('Institution Name', 'mcqhome'); ?> <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="institution_name" name="institution_name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>

                    <div class="form-group mb-4">
                        <label for="institution_type" class="block text-sm font-medium text-gray-700 mb-1">
                            <?php _e('Institution Type', 'mcqhome'); ?>
                        </label>
                        <select id="institution_type" name="institution_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value=""><?php _e('Select Type', 'mcqhome'); ?></option>
                            <option value="school"><?php _e('School', 'mcqhome'); ?></option>
                            <option value="college"><?php _e('College', 'mcqhome'); ?></option>
                            <option value="university"><?php _e('University', 'mcqhome'); ?></option>
                            <option value="coaching"><?php _e('Coaching Center', 'mcqhome'); ?></option>
                            <option value="online"><?php _e('Online Platform', 'mcqhome'); ?></option>
                            <option value="other"><?php _e('Other', 'mcqhome'); ?></option>
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label for="institution_website" class="block text-sm font-medium text-gray-700 mb-1">
                            <?php _e('Website', 'mcqhome'); ?>
                        </label>
                        <input type="url" id="institution_website" name="institution_website"
                            placeholder="https://example.com"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Student-specific fields -->
                <div id="student-fields" class="role-fields" style="display: none;">
                    <h3 class="text-lg font-semibold mb-4 text-blue-700"><?php _e('Student Information', 'mcqhome'); ?></h3>

                    <div class="form-group mb-4">
                        <label for="education_level" class="block text-sm font-medium text-gray-700 mb-1">
                            <?php _e('Education Level', 'mcqhome'); ?>
                        </label>
                        <select id="education_level" name="education_level"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value=""><?php _e('Select Level', 'mcqhome'); ?></option>
                            <option value="high_school"><?php _e('High School', 'mcqhome'); ?></option>
                            <option value="undergraduate"><?php _e('Undergraduate', 'mcqhome'); ?></option>
                            <option value="graduate"><?php _e('Graduate', 'mcqhome'); ?></option>
                            <option value="professional"><?php _e('Professional', 'mcqhome'); ?></option>
                            <option value="other"><?php _e('Other', 'mcqhome'); ?></option>
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label for="interests" class="block text-sm font-medium text-gray-700 mb-1">
                            <?php _e('Subjects of Interest', 'mcqhome'); ?>
                        </label>
                        <input type="text" id="interests" name="interests"
                            placeholder="<?php _e('e.g., Mathematics, Science, History', 'mcqhome'); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Bio field for all roles -->
            <div class="form-group mb-6">
                <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">
                    <?php _e('Bio', 'mcqhome'); ?>
                </label>
                <textarea id="bio" name="bio" rows="3"
                    placeholder="<?php _e('Tell us a bit about yourself...', 'mcqhome'); ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>

            <!-- Terms and Conditions -->
            <div class="form-group mb-6">
                <label class="flex items-start">
                    <input type="checkbox" id="terms_accepted" name="terms_accepted" required
                        class="mt-1 mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <span class="text-sm text-gray-700">
                        <?php _e('I agree to the', 'mcqhome'); ?>
                        <a href="<?php echo home_url('/terms/'); ?>" target="_blank" class="text-blue-600 hover:underline">
                            <?php _e('Terms of Service', 'mcqhome'); ?>
                        </a>
                        <?php _e('and', 'mcqhome'); ?>
                        <a href="<?php echo home_url('/privacy/'); ?>" target="_blank" class="text-blue-600 hover:underline">
                            <?php _e('Privacy Policy', 'mcqhome'); ?>
                        </a>
                    </span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit" id="register-submit"
                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors font-medium">
                    <span class="submit-text"><?php _e('Create Account', 'mcqhome'); ?></span>
                    <span class="loading-text" style="display: none;">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <?php _e('Creating Account...', 'mcqhome'); ?>
                    </span>
                </button>
            </div>

            <!-- Messages -->
            <div id="registration-messages" class="mt-4"></div>

            <?php if ($atts['show_login_link']): ?>
                <div class="text-center mt-6">
                    <p class="text-gray-600">
                        <?php _e('Already have an account?', 'mcqhome'); ?>
                        <a href="<?php echo wp_login_url(); ?>" class="text-blue-600 hover:underline font-medium">
                            <?php _e('Sign In', 'mcqhome'); ?>
                        </a>
                    </p>
                </div>
            <?php endif; ?>
        </form>
    </div>



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('mcqhome-register-form');
            const roleInputs = document.querySelectorAll('input[name="user_role"]');
            const roleSpecificFields = document.getElementById('role-specific-fields');
            const submitButton = document.getElementById('register-submit');
            const messagesDiv = document.getElementById('registration-messages');

            // Handle role selection
            function handleRoleChange() {
                // Show/hide role-specific fields
                document.querySelectorAll('.role-fields').forEach(field => {
                    field.style.display = 'none';
                });

                const selectedRole = document.querySelector('input[name="user_role"]:checked');
                if (selectedRole) {
                    const roleFields = document.getElementById(selectedRole.value + '-fields');
                    if (roleFields) {
                        roleSpecificFields.style.display = 'block';
                        roleFields.style.display = 'block';

                        // Set required fields based on role
                        if (selectedRole.value === 'institution') {
                            const institutionName = document.getElementById('institution_name');
                            if (institutionName) institutionName.required = true;
                        } else {
                            const institutionName = document.getElementById('institution_name');
                            if (institutionName) institutionName.required = false;
                        }
                    }
                } else {
                    roleSpecificFields.style.display = 'none';
                }
            }

            roleInputs.forEach(function(input) {
                input.addEventListener('change', handleRoleChange);
            });

            // Initialize on page load
            handleRoleChange();

            // Handle form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Validate passwords match
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;

                if (password !== confirmPassword) {
                    showMessage('<?php _e('Passwords do not match.', 'mcqhome'); ?>', 'error');
                    return;
                }

                // Show loading state
                submitButton.disabled = true;
                submitButton.querySelector('.submit-text').style.display = 'none';
                submitButton.querySelector('.loading-text').style.display = 'inline';

                // Prepare form data
                const formData = new FormData(form);
                formData.append('action', 'mcqhome_register_user');

                // Submit form
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage(data.data.message, 'success');
                            if (data.data.redirect) {
                                setTimeout(() => {
                                    window.location.href = data.data.redirect;
                                }, 2000);
                            }
                        } else {
                            showMessage(data.data.message || '<?php _e('Registration failed. Please try again.', 'mcqhome'); ?>', 'error');
                        }
                    })
                    .catch(error => {
                        showMessage('<?php _e('An error occurred. Please try again.', 'mcqhome'); ?>', 'error');
                    })
                    .finally(() => {
                        // Reset loading state
                        submitButton.disabled = false;
                        submitButton.querySelector('.submit-text').style.display = 'inline';
                        submitButton.querySelector('.loading-text').style.display = 'none';
                    });
            });

            function showMessage(message, type) {
                const alertClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
                messagesDiv.innerHTML = `
                <div class="border px-4 py-3 rounded ${alertClass}" role="alert">
                    <span class="block sm:inline">${message}</span>
                </div>
            `;
            }
        });
    </script>
<?php

    return ob_get_clean();
}

/**
 * Handle registration form submission
 */
function mcqhome_handle_registration()
{
    // Verify nonce
    if (!wp_verify_nonce($_POST['mcqhome_register_nonce'], 'mcqhome_register_nonce')) {
        wp_send_json_error(['message' => __('Security check failed.', 'mcqhome')]);
    }

    // Sanitize and validate input
    $user_role = sanitize_text_field($_POST['user_role']);
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    $errors = [];

    if (empty($user_role) || !in_array($user_role, ['student', 'teacher', 'institution'])) {
        $errors[] = __('Please select a valid role.', 'mcqhome');
    }

    if (empty($first_name)) {
        $errors[] = __('First name is required.', 'mcqhome');
    }

    if (empty($last_name)) {
        $errors[] = __('Last name is required.', 'mcqhome');
    }

    if (empty($email) || !is_email($email)) {
        $errors[] = __('Please enter a valid email address.', 'mcqhome');
    }

    if (email_exists($email)) {
        $errors[] = __('An account with this email already exists.', 'mcqhome');
    }

    if (empty($password) || strlen($password) < 8) {
        $errors[] = __('Password must be at least 8 characters long.', 'mcqhome');
    }

    if ($password !== $confirm_password) {
        $errors[] = __('Passwords do not match.', 'mcqhome');
    }

    if (!isset($_POST['terms_accepted'])) {
        $errors[] = __('You must accept the terms and conditions.', 'mcqhome');
    }

    // Role-specific validation
    if ($user_role === 'institution' && empty($_POST['institution_name'])) {
        $errors[] = __('Institution name is required.', 'mcqhome');
    }

    if (!empty($errors)) {
        wp_send_json_error(['message' => implode('<br>', $errors)]);
    }

    // Create user
    $username = mcqhome_generate_username($first_name, $last_name, $email);

    $user_data = [
        'user_login' => $username,
        'user_email' => $email,
        'user_pass' => $password,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'display_name' => $first_name . ' ' . $last_name,
        'role' => $user_role,
    ];

    $user_id = wp_insert_user($user_data);

    if (is_wp_error($user_id)) {
        wp_send_json_error(['message' => $user_id->get_error_message()]);
    }

    // Save additional user meta
    $meta_fields = ['bio', 'specialization', 'education_level', 'interests'];
    foreach ($meta_fields as $field) {
        if (!empty($_POST[$field])) {
            update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
        }
    }

    // Handle role-specific data
    if ($user_role === 'teacher') {
        // Automatically assign teachers to the default institution
        $default_institution_id = mcqhome_get_default_institution_id();
        if ($default_institution_id) {
            update_user_meta($user_id, 'institution_id', $default_institution_id);
        }
    } elseif ($user_role === 'institution') {
        // Create institution post
        $institution_data = [
            'post_title' => sanitize_text_field($_POST['institution_name']),
            'post_type' => 'institution',
            'post_status' => 'publish',
            'post_author' => $user_id,
        ];

        $institution_id = wp_insert_post($institution_data);

        if (!is_wp_error($institution_id)) {
            // Save institution meta
            update_post_meta($institution_id, 'institution_type', sanitize_text_field($_POST['institution_type']));
            update_post_meta($institution_id, 'website', esc_url_raw($_POST['institution_website']));
            update_post_meta($institution_id, 'admin_user_id', $user_id);

            // Associate user with institution
            update_user_meta($user_id, 'institution_id', $institution_id);
        }
    }

    // Send verification email
    $verification_sent = mcqhome_send_verification_email($user_id);

    // Set user as pending verification
    update_user_meta($user_id, 'email_verified', false);
    update_user_meta($user_id, 'verification_key', wp_generate_password(32, false));

    // Trigger action for successful registration
    do_action('mcqhome_user_registered', $user_id, $user_role, $_POST);

    $message = __('Account created successfully! Please check your email to verify your account.', 'mcqhome');
    $redirect_url = home_url('/login/?registered=1');

    wp_send_json_success([
        'message' => $message,
        'redirect' => $redirect_url,
        'user_id' => $user_id,
    ]);
}

/**
 * Generate unique username
 */
function mcqhome_generate_username($first_name, $last_name, $email)
{
    $base_username = strtolower($first_name . $last_name);
    $base_username = preg_replace('/[^a-z0-9]/', '', $base_username);

    if (empty($base_username)) {
        $base_username = strtolower(substr($email, 0, strpos($email, '@')));
        $base_username = preg_replace('/[^a-z0-9]/', '', $base_username);
    }

    $username = $base_username;
    $counter = 1;

    while (username_exists($username)) {
        $username = $base_username . $counter;
        $counter++;
    }

    return $username;
}

/**
 * Send email verification
 */
function mcqhome_send_verification_email($user_id)
{
    $user = get_userdata($user_id);
    if (!$user) {
        return false;
    }

    $verification_key = get_user_meta($user_id, 'verification_key', true);
    $verification_url = add_query_arg([
        'action' => 'verify_email',
        'user_id' => $user_id,
        'key' => $verification_key,
    ], home_url('/'));

    $subject = sprintf(__('[%s] Please verify your email address', 'mcqhome'), get_bloginfo('name'));

    $message = sprintf(
        __('Hi %s,

Welcome to %s! Please click the link below to verify your email address and activate your account:

%s

If you did not create this account, please ignore this email.

Best regards,
The %s Team', 'mcqhome'),
        $user->display_name,
        get_bloginfo('name'),
        $verification_url,
        get_bloginfo('name')
    );

    return wp_mail($user->user_email, $subject, $message);
}

/**
 * Handle email verification
 */
function mcqhome_handle_email_verification()
{
    $user_id = intval($_GET['user_id']);
    $key = sanitize_text_field($_GET['key']);

    if (!$user_id || !$key) {
        wp_die(__('Invalid verification link.', 'mcqhome'));
    }

    $stored_key = get_user_meta($user_id, 'verification_key', true);

    if ($key !== $stored_key) {
        wp_die(__('Invalid verification key.', 'mcqhome'));
    }

    // Verify the user
    update_user_meta($user_id, 'email_verified', true);
    delete_user_meta($user_id, 'verification_key');

    // Auto-login the user
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    // Redirect to dashboard
    $user_role = mcqhome_get_user_primary_role($user_id);
    $redirect_url = home_url('/dashboard/');

    wp_redirect($redirect_url);
    exit;
}

/**
 * Get or create default institution (MCQ Academy)
 */
function mcqhome_get_default_institution_id()
{
    $default_institution = get_posts([
        'post_type' => 'institution',
        'meta_query' => [
            [
                'key' => 'is_default_institution',
                'value' => true,
                'compare' => '='
            ]
        ],
        'posts_per_page' => 1,
    ]);

    if (!empty($default_institution)) {
        return $default_institution[0]->ID;
    }

    // Create default institution
    $institution_data = [
        'post_title' => 'MCQ Academy',
        'post_type' => 'institution',
        'post_status' => 'publish',
        'post_content' => 'Default institution for independent teachers on MCQHome platform.',
    ];

    $institution_id = wp_insert_post($institution_data);

    if (!is_wp_error($institution_id)) {
        update_post_meta($institution_id, 'is_default_institution', true);
        update_post_meta($institution_id, 'institution_type', 'platform');
        return $institution_id;
    }

    return false;
}

/**
 * Custom login redirect based on user role
 */
function mcqhome_login_redirect($redirect_to, $request, $user)
{
    if (isset($user->roles) && is_array($user->roles)) {
        $role = mcqhome_get_user_primary_role($user->ID);

        // Check if email is verified
        $email_verified = get_user_meta($user->ID, 'email_verified', true);
        if (!$email_verified) {
            wp_logout();
            wp_redirect(home_url('/login/?error=email_not_verified'));
            exit;
        }

        // Redirect based on role
        switch ($role) {
            case 'administrator':
                return admin_url();
            case 'institution':
            case 'teacher':
                return home_url('/dashboard/');
            case 'student':
                return home_url('/dashboard/');
            default:
                return home_url();
        }
    }

    return $redirect_to;
}

/**
 * Redirect default WordPress signup to custom registration
 */
function mcqhome_redirect_signup($url)
{
    return home_url('/register/');
}

/**
 * Custom register URL
 */
function mcqhome_custom_register_url($url)
{
    return home_url('/register/');
}

// Initialize registration system
add_action('init', 'mcqhome_init_registration');
