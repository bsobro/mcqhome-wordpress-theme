<?php
/**
 * User Registration System with Role Selection
 * 
 * This file handles the custom registration functionality for MCQHome theme
 * with role-based registration (Student, Teacher, Institution)
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MCQHome_Registration {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_mcqhome_register', array($this, 'handle_ajax_registration'));
        add_action('wp_ajax_nopriv_mcqhome_register', array($this, 'handle_ajax_registration'));
        add_shortcode('mcqhome_registration', array($this, 'registration_shortcode'));
    }
    
    public function init() {
        // Add custom registration page if it doesn't exist
        $this->create_registration_page();
    }
    
    public function enqueue_scripts() {
        if (is_page('register') || is_page('registration')) {
            wp_enqueue_script('jquery');
            wp_enqueue_script(
                'mcqhome-registration',
                get_template_directory_uri() . '/assets/js/registration.js',
                array('jquery'),
                '1.0.0',
                true
            );
            
            wp_localize_script('mcqhome-registration', 'mcqhome_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mcqhome_registration_nonce'),
                'messages' => array(
                    'processing' => __('Creating Account...', 'mcqhome'),
                    'success' => __('Account created successfully! Please check your email for verification.', 'mcqhome'),
                    'error' => __('Registration failed. Please try again.', 'mcqhome'),
                    'validation_error' => __('Please fill in all required fields correctly.', 'mcqhome')
                )
            ));
        }
    }
    
    public function create_registration_page() {
        // Check if registration page exists
        $page = get_page_by_path('register');
        
        if (!$page) {
            // Create registration page
            $page_data = array(
                'post_title' => 'Register',
                'post_content' => '[mcqhome_registration]',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_slug' => 'register'
            );
            
            wp_insert_post($page_data);
        }
    }
    
    public function registration_shortcode($atts) {
        ob_start();
        $this->render_registration_form();
        return ob_get_clean();
    }
    
    public function render_registration_form() {
        ?>
        <div id="mcqhome-registration-container" class="max-w-4xl mx-auto px-4 py-8">
            <!-- Messages -->
            <div id="registration-messages" class="mb-6"></div>

            <!-- Step 1: Role Selection -->
            <div id="step-role-selection" class="registration-step active">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2"><?php _e('Choose Your Role', 'mcqhome'); ?></h2>
                        <p class="text-gray-600"><?php _e('Select how you want to use MCQHome', 'mcqhome'); ?></p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <!-- Student Role -->
                        <div class="role-card p-6 border-2 border-gray-200 rounded-lg hover:border-blue-500 cursor-pointer" data-role="student">
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

                        <!-- Teacher Role -->
                        <div class="role-card p-6 border-2 border-gray-200 rounded-lg hover:border-green-500 cursor-pointer" data-role="teacher">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php _e('Teacher', 'mcqhome'); ?></h3>
                                <p class="text-sm text-gray-600"><?php _e('Create and manage MCQs', 'mcqhome'); ?></p>
                            </div>
                        </div>

                        <!-- Institution Role -->
                        <div class="role-card p-6 border-2 border-gray-200 rounded-lg hover:border-purple-500 cursor-pointer" data-role="institution">
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
            </div>

            <!-- Step 2: Registration Form -->
            <div id="step-registration-form" class="registration-step" style="display: none;">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <!-- Back Button -->
                    <button id="back-to-roles" class="text-blue-600 hover:text-blue-800 mb-6 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        <?php _e('Back to Role Selection', 'mcqhome'); ?>
                    </button>

                    <!-- Form Header -->
                    <div class="text-center mb-8">
                        <h2 id="form-title" class="text-2xl font-bold text-gray-900 mb-2"><?php _e('Create Your Account', 'mcqhome'); ?></h2>
                        <p id="form-subtitle" class="text-gray-600"><?php _e('Join MCQHome and start your journey', 'mcqhome'); ?></p>
                    </div>

                    <!-- Registration Form -->
                    <form id="mcqhome-registration-form" class="space-y-6">
                        <?php wp_nonce_field('mcqhome_registration_nonce', 'registration_nonce'); ?>
                        <input type="hidden" id="selected-role" name="user_role" value="">

                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    <?php _e('First Name', 'mcqhome'); ?> <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="first_name" name="first_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    <?php _e('Last Name', 'mcqhome'); ?> <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="last_name" name="last_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php _e('Email Address', 'mcqhome'); ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                    <?php _e('Password', 'mcqhome'); ?> <span class="text-red-500">*</span>
                                </label>
                                <input type="password" id="password" name="password" required minlength="8" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1"><?php _e('Minimum 8 characters', 'mcqhome'); ?></p>
                            </div>

                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">
                                    <?php _e('Confirm Password', 'mcqhome'); ?> <span class="text-red-500">*</span>
                                </label>
                                <input type="password" id="confirm_password" name="confirm_password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        <!-- Role-specific fields -->
                        <div id="role-specific-fields">
                            <!-- Student Fields -->
                            <div id="student-fields" class="role-fields space-y-4" style="display: none;">
                                <h3 class="text-lg font-semibold text-blue-700"><?php _e('Student Information', 'mcqhome'); ?></h3>

                                <div>
                                    <label for="education_level" class="block text-sm font-medium text-gray-700 mb-1">
                                        <?php _e('Education Level', 'mcqhome'); ?>
                                    </label>
                                    <select id="education_level" name="education_level" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value=""><?php _e('Select Level', 'mcqhome'); ?></option>
                                        <option value="high_school"><?php _e('High School', 'mcqhome'); ?></option>
                                        <option value="undergraduate"><?php _e('Undergraduate', 'mcqhome'); ?></option>
                                        <option value="graduate"><?php _e('Graduate', 'mcqhome'); ?></option>
                                        <option value="professional"><?php _e('Professional', 'mcqhome'); ?></option>
                                    </select>
                                </div>

                                <div>
                                    <label for="interests" class="block text-sm font-medium text-gray-700 mb-1">
                                        <?php _e('Interests', 'mcqhome'); ?>
                                    </label>
                                    <input type="text" id="interests" name="interests" placeholder="<?php _e('e.g., Mathematics, Science, Literature', 'mcqhome'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>

                            <!-- Teacher Fields -->
                            <div id="teacher-fields" class="role-fields space-y-4" style="display: none;">
                                <h3 class="text-lg font-semibold text-green-700"><?php _e('Teacher Information', 'mcqhome'); ?></h3>

                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-green-700">
                                                <strong><?php _e('Note:', 'mcqhome'); ?></strong>
                                                <?php _e('You will be automatically assigned to MCQ Academy (our default institution) during registration. You can change your institution later when invited by another institution from their dashboard.', 'mcqhome'); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="specialization" class="block text-sm font-medium text-gray-700 mb-1">
                                        <?php _e('Specialization', 'mcqhome'); ?>
                                    </label>
                                    <input type="text" id="specialization" name="specialization" placeholder="<?php _e('e.g., Mathematics, Physics, Chemistry', 'mcqhome'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>
                            </div>

                            <!-- Institution Fields -->
                            <div id="institution-fields" class="role-fields space-y-4" style="display: none;">
                                <h3 class="text-lg font-semibold text-purple-700"><?php _e('Institution Information', 'mcqhome'); ?></h3>

                                <div>
                                    <label for="institution_name" class="block text-sm font-medium text-gray-700 mb-1">
                                        <?php _e('Institution Name', 'mcqhome'); ?> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="institution_name" name="institution_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label for="institution_type" class="block text-sm font-medium text-gray-700 mb-1">
                                        <?php _e('Institution Type', 'mcqhome'); ?>
                                    </label>
                                    <select id="institution_type" name="institution_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                        <option value=""><?php _e('Select Type', 'mcqhome'); ?></option>
                                        <option value="school"><?php _e('School', 'mcqhome'); ?></option>
                                        <option value="college"><?php _e('College', 'mcqhome'); ?></option>
                                        <option value="university"><?php _e('University', 'mcqhome'); ?></option>
                                        <option value="coaching"><?php _e('Coaching Center', 'mcqhome'); ?></option>
                                        <option value="online"><?php _e('Online Platform', 'mcqhome'); ?></option>
                                        <option value="other"><?php _e('Other', 'mcqhome'); ?></option>
                                    </select>
                                </div>

                                <div>
                                    <label for="institution_website" class="block text-sm font-medium text-gray-700 mb-1">
                                        <?php _e('Website', 'mcqhome'); ?>
                                    </label>
                                    <input type="url" id="institution_website" name="institution_website" placeholder="https://example.com" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <!-- Bio Field (for all roles) -->
                        <div>
                            <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php _e('Bio', 'mcqhome'); ?>
                            </label>
                            <textarea id="bio" name="bio" rows="3" placeholder="<?php _e('Tell us a bit about yourself...', 'mcqhome'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="flex items-start">
                            <input type="checkbox" id="terms_accepted" name="terms_accepted" required class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="terms_accepted" class="ml-2 text-sm text-gray-700">
                                <?php _e('I agree to the', 'mcqhome'); ?> <a href="#" class="text-blue-600 hover:underline"><?php _e('Terms and Conditions', 'mcqhome'); ?></a>
                                <?php _e('and', 'mcqhome'); ?> <a href="#" class="text-blue-600 hover:underline"><?php _e('Privacy Policy', 'mcqhome'); ?></a>
                                <span class="text-red-500">*</span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors font-medium">
                                <?php _e('Create Account', 'mcqhome'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <style>
            .role-card {
                transition: all 0.3s ease;
                cursor: pointer;
                user-select: none;
            }

            .role-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            }

            .role-card:active {
                transform: scale(0.98);
            }

            .role-card.selected {
                border-color: #3b82f6 !important;
                background-color: #eff6ff !important;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }

            .role-card.selected h3 {
                color: #1d4ed8;
            }

            .role-card.selected p {
                color: #1e40af;
            }

            .registration-step {
                display: none;
            }

            .registration-step.active {
                display: block;
            }

            .role-fields {
                display: none;
            }

            .role-fields.active {
                display: block;
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
        </style>
        <?php
    }
    
    public function handle_ajax_registration() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['registration_nonce'], 'mcqhome_registration_nonce')) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => __('Security check failed. Please try again.', 'mcqhome')
            )));
        }
        
        // Sanitize and validate input
        $user_role = sanitize_text_field($_POST['user_role']);
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Basic validation
        if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => __('Please fill in all required fields.', 'mcqhome')
            )));
        }
        
        if (!is_email($email)) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => __('Please enter a valid email address.', 'mcqhome')
            )));
        }
        
        if (strlen($password) < 8) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => __('Password must be at least 8 characters long.', 'mcqhome')
            )));
        }
        
        if ($password !== $confirm_password) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => __('Passwords do not match.', 'mcqhome')
            )));
        }
        
        if (email_exists($email)) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => __('An account with this email already exists.', 'mcqhome')
            )));
        }
        
        // Role-specific validation
        if ($user_role === 'institution' && empty($_POST['institution_name'])) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => __('Institution name is required.', 'mcqhome')
            )));
        }
        
        // Create user
        $username = $this->generate_username($first_name, $last_name, $email);
        
        $user_data = array(
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => $password,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $first_name . ' ' . $last_name,
            'role' => $user_role
        );
        
        $user_id = wp_insert_user($user_data);
        
        if (is_wp_error($user_id)) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => $user_id->get_error_message()
            )));
        }
        
        // Save additional user meta based on role
        $this->save_user_meta($user_id, $user_role, $_POST);
        
        // Handle institution creation for institution role
        if ($user_role === 'institution') {
            $this->create_institution_profile($user_id, $_POST);
        }
        
        // Handle teacher assignment to MCQ Academy
        if ($user_role === 'teacher') {
            $this->assign_teacher_to_mcq_academy($user_id);
        }
        
        wp_die(json_encode(array(
            'success' => true,
            'message' => sprintf(__('Account created successfully! Welcome to MCQHome as a %s.', 'mcqhome'), $user_role)
        )));
    }
    
    private function generate_username($first_name, $last_name, $email) {
        $username = strtolower($first_name . $last_name);
        $username = preg_replace('/[^a-z0-9]/', '', $username);
        
        if (username_exists($username)) {
            $username = strtolower($first_name . $last_name . rand(100, 999));
        }
        
        if (username_exists($username)) {
            $username = sanitize_user(current(explode('@', $email)));
            if (username_exists($username)) {
                $username .= rand(100, 999);
            }
        }
        
        return $username;
    }
    
    private function save_user_meta($user_id, $user_role, $post_data) {
        // Save common meta
        if (!empty($post_data['bio'])) {
            update_user_meta($user_id, 'description', sanitize_textarea_field($post_data['bio']));
        }
        
        // Save role-specific meta
        switch ($user_role) {
            case 'student':
                if (!empty($post_data['education_level'])) {
                    update_user_meta($user_id, 'education_level', sanitize_text_field($post_data['education_level']));
                }
                if (!empty($post_data['interests'])) {
                    update_user_meta($user_id, 'interests', sanitize_text_field($post_data['interests']));
                }
                break;
                
            case 'teacher':
                if (!empty($post_data['specialization'])) {
                    update_user_meta($user_id, 'specialization', sanitize_text_field($post_data['specialization']));
                }
                break;
                
            case 'institution':
                if (!empty($post_data['institution_type'])) {
                    update_user_meta($user_id, 'institution_type', sanitize_text_field($post_data['institution_type']));
                }
                if (!empty($post_data['institution_website'])) {
                    update_user_meta($user_id, 'institution_website', esc_url_raw($post_data['institution_website']));
                }
                break;
        }
    }
    
    private function create_institution_profile($user_id, $post_data) {
        // Create institution post
        $institution_data = array(
            'post_title' => sanitize_text_field($post_data['institution_name']),
            'post_content' => !empty($post_data['bio']) ? sanitize_textarea_field($post_data['bio']) : '',
            'post_status' => 'publish',
            'post_type' => 'institution',
            'post_author' => $user_id
        );
        
        $institution_id = wp_insert_post($institution_data);
        
        if (!is_wp_error($institution_id)) {
            // Link user to institution
            update_user_meta($user_id, 'institution_id', $institution_id);
            
            // Save institution meta
            if (!empty($post_data['institution_type'])) {
                update_post_meta($institution_id, 'institution_type', sanitize_text_field($post_data['institution_type']));
            }
            if (!empty($post_data['institution_website'])) {
                update_post_meta($institution_id, 'website', esc_url_raw($post_data['institution_website']));
            }
        }
    }
    
    private function assign_teacher_to_mcq_academy($user_id) {
        // Get or create MCQ Academy
        $mcq_academy = get_posts(array(
            'post_type' => 'institution',
            'title' => 'MCQ Academy',
            'post_status' => 'publish',
            'numberposts' => 1
        ));
        
        if (empty($mcq_academy)) {
            // Create MCQ Academy if it doesn't exist
            $academy_data = array(
                'post_title' => 'MCQ Academy',
                'post_content' => 'The default institution for independent teachers on MCQHome.',
                'post_status' => 'publish',
                'post_type' => 'institution',
                'post_author' => 1 // Admin user
            );
            
            $academy_id = wp_insert_post($academy_data);
        } else {
            $academy_id = $mcq_academy[0]->ID;
        }
        
        if ($academy_id && !is_wp_error($academy_id)) {
            update_user_meta($user_id, 'institution_id', $academy_id);
        }
    }
}

// Initialize the registration system
new MCQHome_Registration();