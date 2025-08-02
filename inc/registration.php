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
    <style>
        .role-card {
            transition: all 0.3s ease;
        }
        .role-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .role-card:active {
            transform: scale(0.98);
        }
        .role-card.selected {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        
        .message {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        .message-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        .message-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        #registration-messages {
            transition: all 0.3s ease;
        }
        
        .loading-text svg {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .form-step {
            transition: all 0.3s ease;
        }
        
        .role-fields {
            transition: all 0.3s ease;
        }
    </style>

    <div id="mcqhome-registration-form" class="mcqhome-form-container">
        <!-- Step 1: Role Selection -->
        <div id="step-role-selection" class="registration-step">
            <div class="form-header mb-6">
                <h2 class="text-2xl font-bold text-center mb-2"><?php _e('Choose Your Role', 'mcqhome'); ?></h2>
                <p class="text-gray-600 text-center"><?php _e('Select how you want to use MCQHome', 'mcqhome'); ?></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" id="role-selection-container">
                <div class="role-card cursor-pointer p-6 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:shadow-lg transition-all active:scale-95" data-role="student" style="user-select: none; position: relative; z-index: 10;">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php _e('Student', 'mcqhome'); ?></h3>
                        <p class="text-sm text-gray-600"><?php _e('Take MCQs and track your progress', 'mcqhome'); ?></p>
                    </div>
                </div>

                <div class="role-card cursor-pointer p-6 border-2 border-gray-200 rounded-lg hover:border-green-500 hover:shadow-lg transition-all active:scale-95" data-role="teacher" style="user-select: none; position: relative; z-index: 10;">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php _e('Teacher', 'mcqhome'); ?></h3>
                        <p class="text-sm text-gray-600"><?php _e('Create and manage MCQs', 'mcqhome'); ?></p>
                    </div>
                </div>

                <div class="role-card cursor-pointer p-6 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:shadow-lg transition-all active:scale-95" data-role="institution" style="user-select: none; position: relative; z-index: 10;">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php _e('Institution', 'mcqhome'); ?></h3>
                        <p class="text-sm text-gray-600"><?php _e('Manage teachers and students', 'mcqhome'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Registration Form based on Role -->
        <div id="step-registration-form" class="registration-step" style="display: none;">
            <div class="form-header mb-6">
                <button type="button" id="back-to-roles" class="text-blue-600 hover:text-blue-800 mb-4 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <?php _e('Back to Role Selection', 'mcqhome'); ?>
                </button>
                <h2 class="text-2xl font-bold text-center mb-2">
                    <span id="form-title"><?php _e('Create Your Account', 'mcqhome'); ?></span>
                </h2>
                <p class="text-gray-600 text-center">
                    <span id="form-subtitle"><?php _e('Join MCQHome and start your journey', 'mcqhome'); ?></span>
                </p>
            </div>

            <form id="mcqhome-register-form" class="mcqhome-register-form" method="post">
                <?php wp_nonce_field('mcqhome_register_nonce', 'mcqhome_register_nonce'); ?>
                <input type="hidden" id="selected-role" name="user_role" value="">

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

            <!-- Back Button -->
            <div class="text-center mt-6">
                <button type="button" id="back-to-roles"
                    class="text-blue-600 hover:text-blue-800 font-medium transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <?php _e('Back to Role Selection', 'mcqhome'); ?>
                </button>
            </div>

            <?php if ($atts['show_login_link']): ?>
                <div class="text-center mt-4">
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
                // Registration form controller
                const RegistrationController = {
                    currentStep: 'role-selection',
                    selectedRole: null,
                    
                    init: function() {
                        this.bindEvents();
                        this.showStep('role-selection');
                    },
                    
                    bindEvents: function() {
                        // Role selection
                        const roleCards = document.querySelectorAll('.role-card');
                        roleCards.forEach(card => {
                            card.addEventListener('click', (e) => {
                                const role = card.dataset.role;
                                this.selectRole(role);
                            });
                        });
                        
                        // Back button
                        const backBtn = document.getElementById('back-to-roles');
                        if (backBtn) {
                            backBtn.addEventListener('click', () => this.showStep('role-selection'));
                        }
                        
                        // Form submission
                        const form = document.getElementById('mcqhome-register-form');
                        if (form) {
                            form.addEventListener('submit', (e) => this.handleSubmit(e));
                        }
                    },
                    
                    showStep: function(step) {
                        const roleSelection = document.getElementById('step-role-selection');
                        const registrationForm = document.getElementById('step-registration-form');
                        
                        if (step === 'role-selection') {
                            roleSelection.style.display = 'block';
                            registrationForm.style.display = 'none';
                            this.currentStep = 'role-selection';
                        } else if (step === 'registration-form') {
                            roleSelection.style.display = 'none';
                            registrationForm.style.display = 'block';
                            this.currentStep = 'registration-form';
                        }
                    },
                    
                    selectRole: function(role) {
                        console.log('Selecting role:', role);
                        this.selectedRole = role;
                        
                        // Set hidden input
                        const roleInput = document.getElementById('selected-role');
                        if (roleInput) roleInput.value = role;
                        
                        // Show appropriate fields
                        this.showRoleFields(role);
                        this.updateFormTitles(role);
                        this.showStep('registration-form');
                    },
                    
                    showRoleFields: function(role) {
                        // Hide all role fields
                        document.querySelectorAll('.role-fields').forEach(field => {
                            field.style.display = 'none';
                        });
                        
                        // Show specific role fields
                        const roleFields = document.getElementById(role + '-fields');
                        if (roleFields) {
                            roleFields.style.display = 'block';
                        }
                    },
                    
                    updateFormTitles: function(role) {
                        const title = document.getElementById('form-title');
                        const subtitle = document.getElementById('form-subtitle');
                        
                        if (!title || !subtitle) return;
                        
                        const titles = {
                            student: {
                                title: '<?php _e('Student Registration', 'mcqhome'); ?>',
                                subtitle: '<?php _e('Start your learning journey with MCQHome', 'mcqhome'); ?>'
                            },
                            teacher: {
                                title: '<?php _e('Teacher Registration', 'mcqhome'); ?>',
                                subtitle: '<?php _e('Join as a teacher and create amazing MCQs', 'mcqhome'); ?>'
                            },
                            institution: {
                                title: '<?php _e('Institution Registration', 'mcqhome'); ?>',
                                subtitle: '<?php _e('Register your institution and manage your team', 'mcqhome'); ?>'
                            }
                        };
                        
                        if (titles[role]) {
                            title.textContent = titles[role].title;
                            subtitle.textContent = titles[role].subtitle;
                        }
                    },
                    
                    handleSubmit: function(e) {
                        e.preventDefault();
                        
                        // Password validation
                        const password = document.getElementById('password').value;
                        const confirmPassword = document.getElementById('confirm_password').value;
                        
                        if (password !== confirmPassword) {
                            this.showMessage('<?php _e('Passwords do not match.', 'mcqhome'); ?>', 'error');
                            return;
                        }
                        
                        // Submit form
                        this.submitForm();
                    },
                    
                    submitForm: function() {
                        const form = document.getElementById('mcqhome-register-form');
                        const submitBtn = document.getElementById('register-submit');
                        const messagesDiv = document.getElementById('registration-messages');
                        
                        // Show loading state
                        submitBtn.disabled = true;
                        submitBtn.querySelector('.submit-text').style.display = 'none';
                        submitBtn.querySelector('.loading-text').style.display = 'inline';
                        
                        const formData = new FormData(form);
                        formData.append('action', 'mcqhome_register_user');
                        
                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.showMessage(data.data.message, 'success');
                                if (data.data.redirect) {
                                    setTimeout(() => {
                                        window.location.href = data.data.redirect;
                                    }, 2000);
                                }
                            } else {
                                this.showMessage(data.data || '<?php _e('Registration failed. Please try again.', 'mcqhome'); ?>', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            this.showMessage('<?php _e('An error occurred. Please try again.', 'mcqhome'); ?>', 'error');
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.querySelector('.submit-text').style.display = 'inline';
                            submitBtn.querySelector('.loading-text').style.display = 'none';
                        });
                    },
                    
                    showMessage: function(message, type) {
                        const messagesDiv = document.getElementById('registration-messages');
                        if (!messagesDiv) return;
                        
                        messagesDiv.innerHTML = `<div class="message message-${type}">${message}</div>`;
                        messagesDiv.scrollIntoView({ behavior: 'smooth' });
                    }
                };
                
                // Initialize the registration controller
                RegistrationController.init();
                
                // Legacy global function for backward compatibility
                window.selectRole = function(role) {
                    RegistrationController.selectRole(role);
                };
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
