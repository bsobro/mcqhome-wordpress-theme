<?php
/**
 * Demo Content Generator for MCQHome Theme
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main demo content generation class
 */
class MCQHome_Demo_Content {
    
    private $demo_institutions = [];
    private $demo_teachers = [];
    private $demo_students = [];
    private $demo_subjects = [];
    private $demo_topics = [];
    private $demo_mcqs = [];
    private $demo_mcq_sets = [];
    
    /**
     * Initialize demo content generation
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_ajax_mcqhome_generate_demo_content', [$this, 'ajax_generate_demo_content']);
        add_action('wp_ajax_mcqhome_cleanup_demo_content', [$this, 'ajax_cleanup_demo_content']);
    }
    
    /**
     * Add admin menu for demo content management
     */
    public function add_admin_menu() {
        add_submenu_page(
            'themes.php',
            __('MCQHome Demo Content', 'mcqhome'),
            __('Demo Content', 'mcqhome'),
            'manage_options',
            'mcqhome-demo-content',
            [$this, 'admin_page']
        );
    }
    
    /**
     * Admin page for demo content management
     */
    public function admin_page() {
        $demo_exists = get_option('mcqhome_demo_content_generated', false);
        ?>
        <div class="wrap">
            <h1><?php _e('MCQHome Demo Content', 'mcqhome'); ?></h1>
            
            <div class="notice notice-info">
                <p><?php _e('Demo content helps you understand how the MCQHome theme works and provides a foundation to build upon.', 'mcqhome'); ?></p>
            </div>
            
            <?php if (!$demo_exists): ?>
                <div class="card">
                    <h2><?php _e('Generate Demo Content', 'mcqhome'); ?></h2>
                    <p><?php _e('This will create comprehensive demo content including:', 'mcqhome'); ?></p>
                    <ul>
                        <li><?php _e('Demo institutions with realistic profiles', 'mcqhome'); ?></li>
                        <li><?php _e('Demo teachers associated with institutions', 'mcqhome'); ?></li>
                        <li><?php _e('Demo students with enrollment history', 'mcqhome'); ?></li>
                        <li><?php _e('Comprehensive category structure (subjects and topics)', 'mcqhome'); ?></li>
                        <li><?php _e('Sample MCQs across different subjects and difficulty levels', 'mcqhome'); ?></li>
                        <li><?php _e('Demo MCQ sets with various configurations', 'mcqhome'); ?></li>
                        <li><?php _e('Sample follow relationships between users', 'mcqhome'); ?></li>
                    </ul>
                    
                    <p>
                        <button type="button" class="button button-primary" id="generate-demo-content">
                            <?php _e('Generate Demo Content', 'mcqhome'); ?>
                        </button>
                        <span class="spinner"></span>
                    </p>
                </div>
            <?php else: ?>
                <div class="card">
                    <h2><?php _e('Demo Content Status', 'mcqhome'); ?></h2>
                    <p class="notice notice-success inline"><?php _e('Demo content has been generated successfully!', 'mcqhome'); ?></p>
                    
                    <h3><?php _e('Demo Content Summary', 'mcqhome'); ?></h3>
                    <?php $this->display_demo_summary(); ?>
                    
                    <h3><?php _e('Cleanup Demo Content', 'mcqhome'); ?></h3>
                    <p><?php _e('Remove all demo content while preserving the theme structure.', 'mcqhome'); ?></p>
                    <p>
                        <button type="button" class="button button-secondary" id="cleanup-demo-content">
                            <?php _e('Remove Demo Content', 'mcqhome'); ?>
                        </button>
                        <span class="spinner"></span>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#generate-demo-content').on('click', function() {
                var $button = $(this);
                var $spinner = $button.next('.spinner');
                
                $button.prop('disabled', true);
                $spinner.addClass('is-active');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'mcqhome_generate_demo_content',
                        nonce: '<?php echo wp_create_nonce('mcqhome_demo_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('<?php _e('An error occurred. Please try again.', 'mcqhome'); ?>');
                    },
                    complete: function() {
                        $button.prop('disabled', false);
                        $spinner.removeClass('is-active');
                    }
                });
            });
            
            $('#cleanup-demo-content').on('click', function() {
                if (!confirm('<?php _e('Are you sure you want to remove all demo content? This action cannot be undone.', 'mcqhome'); ?>')) {
                    return;
                }
                
                var $button = $(this);
                var $spinner = $button.next('.spinner');
                
                $button.prop('disabled', true);
                $spinner.addClass('is-active');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'mcqhome_cleanup_demo_content',
                        nonce: '<?php echo wp_create_nonce('mcqhome_demo_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('<?php _e('An error occurred. Please try again.', 'mcqhome'); ?>');
                    },
                    complete: function() {
                        $button.prop('disabled', false);
                        $spinner.removeClass('is-active');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Display demo content summary
     */
    private function display_demo_summary() {
        $institutions_count = wp_count_posts('institution')->publish ?? 0;
        $mcqs_count = wp_count_posts('mcq')->publish ?? 0;
        $mcq_sets_count = wp_count_posts('mcq_set')->publish ?? 0;
        
        $teachers_count = count(get_users(['role' => 'teacher']));
        $students_count = count(get_users(['role' => 'student']));
        
        $subjects_count = wp_count_terms(['taxonomy' => 'mcq_subject']);
        $topics_count = wp_count_terms(['taxonomy' => 'mcq_topic']);
        
        echo '<ul>';
        echo '<li>' . sprintf(__('Institutions: %d', 'mcqhome'), $institutions_count) . '</li>';
        echo '<li>' . sprintf(__('Teachers: %d', 'mcqhome'), $teachers_count) . '</li>';
        echo '<li>' . sprintf(__('Students: %d', 'mcqhome'), $students_count) . '</li>';
        echo '<li>' . sprintf(__('Subjects: %d', 'mcqhome'), $subjects_count) . '</li>';
        echo '<li>' . sprintf(__('Topics: %d', 'mcqhome'), $topics_count) . '</li>';
        echo '<li>' . sprintf(__('MCQs: %d', 'mcqhome'), $mcqs_count) . '</li>';
        echo '<li>' . sprintf(__('MCQ Sets: %d', 'mcqhome'), $mcq_sets_count) . '</li>';
        echo '</ul>';
    }
    
    /**
     * AJAX handler for generating demo content
     */
    public function ajax_generate_demo_content() {
        if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_demo_nonce') || !current_user_can('manage_options')) {
            wp_die(__('Security check failed', 'mcqhome'));
        }
        
        try {
            $this->generate_all_demo_content();
            wp_send_json_success(__('Demo content generated successfully!', 'mcqhome'));
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    /**
     * AJAX handler for cleaning up demo content
     */
    public function ajax_cleanup_demo_content() {
        if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_demo_nonce') || !current_user_can('manage_options')) {
            wp_die(__('Security check failed', 'mcqhome'));
        }
        
        try {
            $this->cleanup_all_demo_content();
            wp_send_json_success(__('Demo content removed successfully!', 'mcqhome'));
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    /**
     * Generate all demo content
     */
    public function generate_all_demo_content() {
        // Set time limit for large operations
        set_time_limit(300);
        
        // Create MCQ Academy default institution first
        $this->create_mcq_academy_institution();
        
        // Generate demo content in order
        $this->create_demo_subjects_and_topics();
        $this->create_demo_institutions();
        $this->create_demo_teachers();
        $this->create_demo_students();
        $this->create_demo_mcqs();
        $this->create_demo_mcq_sets();
        $this->create_demo_follow_relationships();
        $this->create_demo_enrollments();
        
        // Mark demo content as generated
        update_option('mcqhome_demo_content_generated', true);
        update_option('mcqhome_demo_content_timestamp', current_time('mysql'));
    }
    
    /**
     * Create MCQ Academy default institution
     */
    private function create_mcq_academy_institution() {
        // Check if MCQ Academy already exists
        $existing = get_posts([
            'post_type' => 'institution',
            'meta_key' => '_is_default_institution',
            'meta_value' => '1',
            'post_status' => 'any',
            'numberposts' => 1
        ]);
        
        if (!empty($existing)) {
            return $existing[0]->ID;
        }
        
        $mcq_academy_id = wp_insert_post([
            'post_title' => 'MCQ Academy',
            'post_content' => 'MCQ Academy is the default institution for independent teachers and educators who want to share their knowledge and create quality multiple choice questions for students worldwide.',
            'post_status' => 'publish',
            'post_type' => 'institution',
            'post_author' => 1
        ]);
        
        if ($mcq_academy_id && !is_wp_error($mcq_academy_id)) {
            // Mark as default institution
            update_post_meta($mcq_academy_id, '_is_default_institution', '1');
            update_post_meta($mcq_academy_id, '_institution_type', 'default');
            update_post_meta($mcq_academy_id, '_institution_website', 'https://mcqhome.com');
            update_post_meta($mcq_academy_id, '_institution_description', 'The home for independent educators and quality MCQ content.');
            update_post_meta($mcq_academy_id, '_institution_established', '2024');
            update_post_meta($mcq_academy_id, '_institution_location', 'Global');
            
            // Store for later use
            $this->demo_institutions['mcq_academy'] = $mcq_academy_id;
            
            return $mcq_academy_id;
        }
        
        throw new Exception('Failed to create MCQ Academy institution');
    }    
    
/**
     * Create demo subjects and topics
     */
    private function create_demo_subjects_and_topics() {
        $subjects_data = [
            'Mathematics' => [
                'description' => 'Mathematical concepts and problem solving',
                'topics' => ['Algebra', 'Geometry', 'Calculus', 'Statistics', 'Trigonometry', 'Number Theory']
            ],
            'Science' => [
                'description' => 'Natural sciences and scientific methods',
                'topics' => ['Physics', 'Chemistry', 'Biology', 'Earth Science', 'Environmental Science']
            ],
            'Computer Science' => [
                'description' => 'Computing, programming, and technology',
                'topics' => ['Programming', 'Data Structures', 'Algorithms', 'Database Systems', 'Web Development', 'Machine Learning']
            ],
            'English' => [
                'description' => 'English language and literature',
                'topics' => ['Grammar', 'Literature', 'Writing', 'Reading Comprehension', 'Vocabulary', 'Poetry']
            ],
            'History' => [
                'description' => 'Historical events and civilizations',
                'topics' => ['World History', 'Ancient Civilizations', 'Modern History', 'American History', 'European History']
            ],
            'Business Studies' => [
                'description' => 'Business concepts and management',
                'topics' => ['Marketing', 'Finance', 'Management', 'Economics', 'Entrepreneurship', 'Business Ethics']
            ]
        ];
        
        foreach ($subjects_data as $subject_name => $subject_info) {
            // Create subject
            $subject_term = wp_insert_term($subject_name, 'mcq_subject', [
                'description' => $subject_info['description']
            ]);
            
            if (!is_wp_error($subject_term)) {
                $this->demo_subjects[$subject_name] = $subject_term['term_id'];
                
                // Create topics for this subject
                foreach ($subject_info['topics'] as $topic_name) {
                    $topic_term = wp_insert_term($topic_name, 'mcq_topic', [
                        'description' => sprintf('Topics related to %s in %s', $topic_name, $subject_name),
                        'parent' => 0
                    ]);
                    
                    if (!is_wp_error($topic_term)) {
                        $this->demo_topics[$topic_name] = $topic_term['term_id'];
                    }
                }
            }
        }
    }
    
    /**
     * Create demo institutions
     */
    private function create_demo_institutions() {
        $institutions_data = [
            [
                'name' => 'Tech University',
                'description' => 'A leading institution in technology and engineering education, known for innovative teaching methods and cutting-edge research.',
                'type' => 'university',
                'website' => 'https://techuniversity.edu',
                'established' => '1985',
                'location' => 'Silicon Valley, CA',
                'specialization' => 'Technology, Engineering, Computer Science'
            ],
            [
                'name' => 'Global Business School',
                'description' => 'Premier business education institution offering comprehensive programs in management, finance, and entrepreneurship.',
                'type' => 'business_school',
                'website' => 'https://globalbusiness.edu',
                'established' => '1978',
                'location' => 'New York, NY',
                'specialization' => 'Business, Management, Finance, Marketing'
            ],
            [
                'name' => 'Science Academy',
                'description' => 'Dedicated to excellence in scientific education and research across multiple disciplines.',
                'type' => 'academy',
                'website' => 'https://scienceacademy.edu',
                'established' => '1992',
                'location' => 'Boston, MA',
                'specialization' => 'Physics, Chemistry, Biology, Mathematics'
            ],
            [
                'name' => 'Liberal Arts College',
                'description' => 'A comprehensive liberal arts institution fostering critical thinking and creative expression.',
                'type' => 'college',
                'website' => 'https://liberalarts.edu',
                'established' => '1965',
                'location' => 'Portland, OR',
                'specialization' => 'Literature, History, Philosophy, Arts'
            ],
            [
                'name' => 'Medical Institute',
                'description' => 'Leading medical education and research institution training the next generation of healthcare professionals.',
                'type' => 'medical_institute',
                'website' => 'https://medicalinstitute.edu',
                'established' => '1955',
                'location' => 'Chicago, IL',
                'specialization' => 'Medicine, Healthcare, Biomedical Sciences'
            ]
        ];
        
        foreach ($institutions_data as $institution_data) {
            $institution_id = wp_insert_post([
                'post_title' => $institution_data['name'],
                'post_content' => $institution_data['description'],
                'post_status' => 'publish',
                'post_type' => 'institution',
                'post_author' => 1
            ]);
            
            if ($institution_id && !is_wp_error($institution_id)) {
                // Add institution metadata
                update_post_meta($institution_id, '_institution_type', $institution_data['type']);
                update_post_meta($institution_id, '_institution_website', $institution_data['website']);
                update_post_meta($institution_id, '_institution_established', $institution_data['established']);
                update_post_meta($institution_id, '_institution_location', $institution_data['location']);
                update_post_meta($institution_id, '_institution_specialization', $institution_data['specialization']);
                update_post_meta($institution_id, '_institution_description', $institution_data['description']);
                
                // Store for later use
                $this->demo_institutions[sanitize_title($institution_data['name'])] = $institution_id;
            }
        }
    }
    
    /**
     * Create demo teachers
     */
    private function create_demo_teachers() {
        $teachers_data = [
            [
                'username' => 'prof_johnson',
                'email' => 'johnson@techuniversity.edu',
                'first_name' => 'Dr. Sarah',
                'last_name' => 'Johnson',
                'display_name' => 'Dr. Sarah Johnson',
                'bio' => 'Professor of Computer Science with 15 years of experience in software engineering and machine learning.',
                'specialization' => 'Computer Science, Machine Learning, Software Engineering',
                'institution' => 'tech-university'
            ],
            [
                'username' => 'prof_martinez',
                'email' => 'martinez@globalbusiness.edu',
                'first_name' => 'Prof. Carlos',
                'last_name' => 'Martinez',
                'display_name' => 'Prof. Carlos Martinez',
                'bio' => 'Business strategy expert with extensive experience in international markets and entrepreneurship.',
                'specialization' => 'Business Strategy, International Business, Entrepreneurship',
                'institution' => 'global-business-school'
            ],
            [
                'username' => 'dr_chen',
                'email' => 'chen@scienceacademy.edu',
                'first_name' => 'Dr. Lisa',
                'last_name' => 'Chen',
                'display_name' => 'Dr. Lisa Chen',
                'bio' => 'Physics professor specializing in quantum mechanics and theoretical physics research.',
                'specialization' => 'Physics, Quantum Mechanics, Theoretical Physics',
                'institution' => 'science-academy'
            ],
            [
                'username' => 'prof_williams',
                'email' => 'williams@liberalarts.edu',
                'first_name' => 'Prof. Michael',
                'last_name' => 'Williams',
                'display_name' => 'Prof. Michael Williams',
                'bio' => 'English literature professor with expertise in modern and contemporary literature.',
                'specialization' => 'English Literature, Modern Literature, Creative Writing',
                'institution' => 'liberal-arts-college'
            ],
            [
                'username' => 'dr_patel',
                'email' => 'patel@medicalinstitute.edu',
                'first_name' => 'Dr. Priya',
                'last_name' => 'Patel',
                'display_name' => 'Dr. Priya Patel',
                'bio' => 'Medical doctor and educator specializing in internal medicine and medical education.',
                'specialization' => 'Internal Medicine, Medical Education, Clinical Research',
                'institution' => 'medical-institute'
            ],
            [
                'username' => 'independent_teacher1',
                'email' => 'teacher1@mcqacademy.com',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'display_name' => 'John Smith',
                'bio' => 'Independent mathematics educator with passion for making complex concepts accessible to all students.',
                'specialization' => 'Mathematics, Algebra, Calculus',
                'institution' => 'mcq_academy'
            ],
            [
                'username' => 'independent_teacher2',
                'email' => 'teacher2@mcqacademy.com',
                'first_name' => 'Emma',
                'last_name' => 'Davis',
                'display_name' => 'Emma Davis',
                'bio' => 'Freelance science educator creating engaging content for students worldwide.',
                'specialization' => 'Biology, Chemistry, Environmental Science',
                'institution' => 'mcq_academy'
            ]
        ];
        
        foreach ($teachers_data as $teacher_data) {
            // Create user account
            $user_id = wp_create_user(
                $teacher_data['username'],
                wp_generate_password(12, false),
                $teacher_data['email']
            );
            
            if ($user_id && !is_wp_error($user_id)) {
                // Set user role
                $user = new WP_User($user_id);
                $user->set_role('teacher');
                
                // Update user meta
                wp_update_user([
                    'ID' => $user_id,
                    'first_name' => $teacher_data['first_name'],
                    'last_name' => $teacher_data['last_name'],
                    'display_name' => $teacher_data['display_name']
                ]);
                
                // Add custom meta
                update_user_meta($user_id, 'bio', $teacher_data['bio']);
                update_user_meta($user_id, 'specialization', $teacher_data['specialization']);
                
                // Associate with institution
                if (isset($this->demo_institutions[$teacher_data['institution']])) {
                    update_user_meta($user_id, 'institution_id', $this->demo_institutions[$teacher_data['institution']]);
                }
                
                // Store for later use
                $this->demo_teachers[$teacher_data['username']] = $user_id;
            }
        }
    }
    
    /**
     * Create demo students
     */
    private function create_demo_students() {
        $students_data = [
            [
                'username' => 'student_alice',
                'email' => 'alice@student.com',
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'display_name' => 'Alice Johnson',
                'bio' => 'Computer Science student passionate about web development and artificial intelligence.'
            ],
            [
                'username' => 'student_bob',
                'email' => 'bob@student.com',
                'first_name' => 'Bob',
                'last_name' => 'Smith',
                'display_name' => 'Bob Smith',
                'bio' => 'Business student interested in entrepreneurship and digital marketing.'
            ],
            [
                'username' => 'student_carol',
                'email' => 'carol@student.com',
                'first_name' => 'Carol',
                'last_name' => 'Davis',
                'display_name' => 'Carol Davis',
                'bio' => 'Physics student with a love for theoretical physics and mathematics.'
            ],
            [
                'username' => 'student_david',
                'email' => 'david@student.com',
                'first_name' => 'David',
                'last_name' => 'Wilson',
                'display_name' => 'David Wilson',
                'bio' => 'Literature student exploring modern and classical works.'
            ],
            [
                'username' => 'student_emma',
                'email' => 'emma@student.com',
                'first_name' => 'Emma',
                'last_name' => 'Brown',
                'display_name' => 'Emma Brown',
                'bio' => 'Pre-med student focused on biology and chemistry.'
            ],
            [
                'username' => 'student_frank',
                'email' => 'frank@student.com',
                'first_name' => 'Frank',
                'last_name' => 'Miller',
                'display_name' => 'Frank Miller',
                'bio' => 'Mathematics enthusiast working on advanced calculus and statistics.'
            ]
        ];
        
        foreach ($students_data as $student_data) {
            // Create user account
            $user_id = wp_create_user(
                $student_data['username'],
                wp_generate_password(12, false),
                $student_data['email']
            );
            
            if ($user_id && !is_wp_error($user_id)) {
                // Set user role
                $user = new WP_User($user_id);
                $user->set_role('student');
                
                // Update user meta
                wp_update_user([
                    'ID' => $user_id,
                    'first_name' => $student_data['first_name'],
                    'last_name' => $student_data['last_name'],
                    'display_name' => $student_data['display_name']
                ]);
                
                // Add custom meta
                update_user_meta($user_id, 'bio', $student_data['bio']);
                
                // Store for later use
                $this->demo_students[$student_data['username']] = $user_id;
            }
        }
    }  
  
    /**
     * Create demo MCQs
     */
    private function create_demo_mcqs() {
        $mcqs_data = [
            // Computer Science MCQs
            [
                'title' => 'Basic Programming Concepts',
                'question' => 'What is the primary purpose of a variable in programming?',
                'options' => [
                    'A' => 'To store and manipulate data',
                    'B' => 'To create user interfaces',
                    'C' => 'To connect to databases',
                    'D' => 'To handle network requests'
                ],
                'correct_answer' => 'A',
                'explanation' => 'Variables are fundamental programming constructs used to store and manipulate data. They act as containers that hold values which can be changed during program execution.',
                'subject' => 'Computer Science',
                'topic' => 'Programming',
                'difficulty' => 'easy',
                'author' => 'prof_johnson',
                'marks' => 1
            ],
            [
                'title' => 'Data Structures - Arrays',
                'question' => 'What is the time complexity of accessing an element in an array by index?',
                'options' => [
                    'A' => 'O(n)',
                    'B' => 'O(log n)',
                    'C' => 'O(1)',
                    'D' => 'O(n²)'
                ],
                'correct_answer' => 'C',
                'explanation' => 'Array access by index is O(1) or constant time because arrays store elements in contiguous memory locations, allowing direct calculation of memory address.',
                'subject' => 'Computer Science',
                'topic' => 'Data Structures',
                'difficulty' => 'medium',
                'author' => 'prof_johnson',
                'marks' => 2
            ],
            
            // Mathematics MCQs
            [
                'title' => 'Basic Algebra',
                'question' => 'Solve for x: 2x + 5 = 13',
                'options' => [
                    'A' => 'x = 3',
                    'B' => 'x = 4',
                    'C' => 'x = 5',
                    'D' => 'x = 6'
                ],
                'correct_answer' => 'B',
                'explanation' => 'To solve 2x + 5 = 13: Subtract 5 from both sides: 2x = 8. Divide both sides by 2: x = 4.',
                'subject' => 'Mathematics',
                'topic' => 'Algebra',
                'difficulty' => 'easy',
                'author' => 'independent_teacher1',
                'marks' => 1
            ],
            [
                'title' => 'Calculus - Derivatives',
                'question' => 'What is the derivative of f(x) = x³?',
                'options' => [
                    'A' => '3x²',
                    'B' => 'x²',
                    'C' => '3x',
                    'D' => 'x³/3'
                ],
                'correct_answer' => 'A',
                'explanation' => 'Using the power rule: d/dx(xⁿ) = n·xⁿ⁻¹. For f(x) = x³, the derivative is 3·x³⁻¹ = 3x².',
                'subject' => 'Mathematics',
                'topic' => 'Calculus',
                'difficulty' => 'medium',
                'author' => 'independent_teacher1',
                'marks' => 2
            ],
            
            // Physics MCQs
            [
                'title' => 'Newton\'s Laws',
                'question' => 'According to Newton\'s first law, an object at rest will:',
                'options' => [
                    'A' => 'Always start moving',
                    'B' => 'Stay at rest unless acted upon by a force',
                    'C' => 'Accelerate automatically',
                    'D' => 'Change direction randomly'
                ],
                'correct_answer' => 'B',
                'explanation' => 'Newton\'s first law (law of inertia) states that an object at rest stays at rest and an object in motion stays in motion unless acted upon by an external force.',
                'subject' => 'Science',
                'topic' => 'Physics',
                'difficulty' => 'easy',
                'author' => 'dr_chen',
                'marks' => 1
            ],
            [
                'title' => 'Quantum Mechanics Basics',
                'question' => 'What is the fundamental principle behind Heisenberg\'s uncertainty principle?',
                'options' => [
                    'A' => 'Energy cannot be created or destroyed',
                    'B' => 'You cannot simultaneously know both position and momentum precisely',
                    'C' => 'Light behaves only as a wave',
                    'D' => 'Atoms are indivisible'
                ],
                'correct_answer' => 'B',
                'explanation' => 'Heisenberg\'s uncertainty principle states that there is a fundamental limit to how precisely we can know both the position and momentum of a particle simultaneously.',
                'subject' => 'Science',
                'topic' => 'Physics',
                'difficulty' => 'hard',
                'author' => 'dr_chen',
                'marks' => 3
            ],
            
            // Business MCQs
            [
                'title' => 'Marketing Fundamentals',
                'question' => 'What are the 4 Ps of marketing?',
                'options' => [
                    'A' => 'Product, Price, Place, Promotion',
                    'B' => 'People, Process, Physical, Performance',
                    'C' => 'Planning, Pricing, Positioning, Profit',
                    'D' => 'Purpose, Passion, Persistence, Performance'
                ],
                'correct_answer' => 'A',
                'explanation' => 'The 4 Ps of marketing are Product (what you sell), Price (how much you charge), Place (where you sell), and Promotion (how you communicate).',
                'subject' => 'Business Studies',
                'topic' => 'Marketing',
                'difficulty' => 'easy',
                'author' => 'prof_martinez',
                'marks' => 1
            ],
            [
                'title' => 'Financial Analysis',
                'question' => 'What does ROI stand for in business?',
                'options' => [
                    'A' => 'Rate of Interest',
                    'B' => 'Return on Investment',
                    'C' => 'Risk of Investment',
                    'D' => 'Ratio of Income'
                ],
                'correct_answer' => 'B',
                'explanation' => 'ROI stands for Return on Investment, which is a performance measure used to evaluate the efficiency of an investment or compare efficiency of different investments.',
                'subject' => 'Business Studies',
                'topic' => 'Finance',
                'difficulty' => 'easy',
                'author' => 'prof_martinez',
                'marks' => 1
            ],
            
            // English Literature MCQs
            [
                'title' => 'Shakespeare\'s Works',
                'question' => 'Who wrote "To be or not to be, that is the question"?',
                'options' => [
                    'A' => 'William Shakespeare',
                    'B' => 'Charles Dickens',
                    'C' => 'Jane Austen',
                    'D' => 'Mark Twain'
                ],
                'correct_answer' => 'A',
                'explanation' => 'This famous soliloquy is from Hamlet, Act 3, Scene 1, written by William Shakespeare. It\'s one of the most quoted lines in English literature.',
                'subject' => 'English',
                'topic' => 'Literature',
                'difficulty' => 'easy',
                'author' => 'prof_williams',
                'marks' => 1
            ],
            [
                'title' => 'Grammar Rules',
                'question' => 'Which sentence uses correct subject-verb agreement?',
                'options' => [
                    'A' => 'The group of students are studying',
                    'B' => 'The group of students is studying',
                    'C' => 'The group of students were studying',
                    'D' => 'The group of students have studying'
                ],
                'correct_answer' => 'B',
                'explanation' => 'The subject "group" is singular, so it requires the singular verb "is." The prepositional phrase "of students" doesn\'t affect the verb agreement.',
                'subject' => 'English',
                'topic' => 'Grammar',
                'difficulty' => 'medium',
                'author' => 'prof_williams',
                'marks' => 2
            ],
            
            // Biology MCQs
            [
                'title' => 'Cell Biology',
                'question' => 'What is the powerhouse of the cell?',
                'options' => [
                    'A' => 'Nucleus',
                    'B' => 'Mitochondria',
                    'C' => 'Ribosome',
                    'D' => 'Endoplasmic Reticulum'
                ],
                'correct_answer' => 'B',
                'explanation' => 'Mitochondria are called the powerhouse of the cell because they produce ATP (adenosine triphosphate), which is the main energy currency of cells.',
                'subject' => 'Science',
                'topic' => 'Biology',
                'difficulty' => 'easy',
                'author' => 'independent_teacher2',
                'marks' => 1
            ],
            [
                'title' => 'Genetics',
                'question' => 'What does DNA stand for?',
                'options' => [
                    'A' => 'Deoxyribonucleic Acid',
                    'B' => 'Deoxyribose Nucleic Acid',
                    'C' => 'Diribonucleic Acid',
                    'D' => 'Deoxyribonuclear Acid'
                ],
                'correct_answer' => 'A',
                'explanation' => 'DNA stands for Deoxyribonucleic Acid. It is the hereditary material in humans and almost all other organisms that carries genetic instructions.',
                'subject' => 'Science',
                'topic' => 'Biology',
                'difficulty' => 'easy',
                'author' => 'independent_teacher2',
                'marks' => 1
            ],
            
            // Additional diverse MCQs for better variety
            [
                'title' => 'Advanced Algorithms',
                'question' => 'What is the worst-case time complexity of QuickSort?',
                'options' => [
                    'A' => 'O(n log n)',
                    'B' => 'O(n²)',
                    'C' => 'O(n)',
                    'D' => 'O(log n)'
                ],
                'correct_answer' => 'B',
                'explanation' => 'QuickSort has a worst-case time complexity of O(n²) when the pivot is always the smallest or largest element, leading to unbalanced partitions.',
                'subject' => 'Computer Science',
                'topic' => 'Algorithms',
                'difficulty' => 'hard',
                'author' => 'prof_johnson',
                'marks' => 3
            ],
            [
                'title' => 'Statistics Fundamentals',
                'question' => 'What is the median of the dataset: 3, 7, 8, 5, 12, 14, 21, 13, 18?',
                'options' => [
                    'A' => '12',
                    'B' => '13',
                    'C' => '14',
                    'D' => '11'
                ],
                'correct_answer' => 'A',
                'explanation' => 'To find the median, first sort the data: 3, 5, 7, 8, 12, 13, 14, 18, 21. The median is the middle value, which is 12.',
                'subject' => 'Mathematics',
                'topic' => 'Statistics',
                'difficulty' => 'medium',
                'author' => 'independent_teacher1',
                'marks' => 2
            ],
            [
                'title' => 'Chemistry Basics',
                'question' => 'What is the chemical symbol for Gold?',
                'options' => [
                    'A' => 'Go',
                    'B' => 'Gd',
                    'C' => 'Au',
                    'D' => 'Ag'
                ],
                'correct_answer' => 'C',
                'explanation' => 'The chemical symbol for Gold is Au, derived from the Latin word "aurum" meaning gold.',
                'subject' => 'Science',
                'topic' => 'Chemistry',
                'difficulty' => 'easy',
                'author' => 'independent_teacher2',
                'marks' => 1
            ],
            [
                'title' => 'World History',
                'question' => 'In which year did World War II end?',
                'options' => [
                    'A' => '1944',
                    'B' => '1945',
                    'C' => '1946',
                    'D' => '1947'
                ],
                'correct_answer' => 'B',
                'explanation' => 'World War II ended in 1945 with the surrender of Japan on September 2, 1945, following the atomic bombings and Soviet invasion.',
                'subject' => 'History',
                'topic' => 'World History',
                'difficulty' => 'easy',
                'author' => 'prof_williams',
                'marks' => 1
            ],
            [
                'title' => 'Advanced Business Strategy',
                'question' => 'What is Porter\'s Five Forces model primarily used for?',
                'options' => [
                    'A' => 'Financial analysis',
                    'B' => 'Industry analysis and competitive strategy',
                    'C' => 'Human resource management',
                    'D' => 'Marketing segmentation'
                ],
                'correct_answer' => 'B',
                'explanation' => 'Porter\'s Five Forces is a framework for analyzing the competitive environment of an industry by examining five key forces that shape competition.',
                'subject' => 'Business Studies',
                'topic' => 'Management',
                'difficulty' => 'medium',
                'author' => 'prof_martinez',
                'marks' => 2
            ],
            [
                'title' => 'Environmental Science',
                'question' => 'What is the primary cause of ozone layer depletion?',
                'options' => [
                    'A' => 'Carbon dioxide emissions',
                    'B' => 'Chlorofluorocarbons (CFCs)',
                    'C' => 'Methane gas',
                    'D' => 'Nitrogen oxides'
                ],
                'correct_answer' => 'B',
                'explanation' => 'Chlorofluorocarbons (CFCs) are the primary cause of ozone layer depletion. When CFCs reach the stratosphere, they break down and release chlorine atoms that destroy ozone molecules.',
                'subject' => 'Science',
                'topic' => 'Environmental Science',
                'difficulty' => 'medium',
                'author' => 'independent_teacher2',
                'marks' => 2
            ]
        ];
        
        foreach ($mcqs_data as $mcq_data) {
            // Get author ID
            $author_id = isset($this->demo_teachers[$mcq_data['author']]) ? $this->demo_teachers[$mcq_data['author']] : 1;
            
            // Create MCQ post
            $mcq_id = wp_insert_post([
                'post_title' => $mcq_data['title'],
                'post_content' => '', // Content is stored in meta
                'post_status' => 'publish',
                'post_type' => 'mcq',
                'post_author' => $author_id
            ]);
            
            if ($mcq_id && !is_wp_error($mcq_id)) {
                // Add MCQ metadata
                update_post_meta($mcq_id, '_mcq_question_text', $mcq_data['question']);
                update_post_meta($mcq_id, '_mcq_option_a', $mcq_data['options']['A']);
                update_post_meta($mcq_id, '_mcq_option_b', $mcq_data['options']['B']);
                update_post_meta($mcq_id, '_mcq_option_c', $mcq_data['options']['C']);
                update_post_meta($mcq_id, '_mcq_option_d', $mcq_data['options']['D']);
                update_post_meta($mcq_id, '_mcq_correct_answer', $mcq_data['correct_answer']);
                update_post_meta($mcq_id, '_mcq_explanation', $mcq_data['explanation']);
                update_post_meta($mcq_id, '_mcq_marks', $mcq_data['marks']);
                update_post_meta($mcq_id, '_mcq_negative_marks', 0.25);
                
                // Assign taxonomies
                if (isset($this->demo_subjects[$mcq_data['subject']])) {
                    wp_set_post_terms($mcq_id, [$this->demo_subjects[$mcq_data['subject']]], 'mcq_subject');
                }
                
                if (isset($this->demo_topics[$mcq_data['topic']])) {
                    wp_set_post_terms($mcq_id, [$this->demo_topics[$mcq_data['topic']]], 'mcq_topic');
                }
                
                // Set difficulty
                $difficulty_term = get_term_by('slug', $mcq_data['difficulty'], 'mcq_difficulty');
                if ($difficulty_term) {
                    wp_set_post_terms($mcq_id, [$difficulty_term->term_id], 'mcq_difficulty');
                }
                
                // Store for later use
                $this->demo_mcqs[] = $mcq_id;
            }
        }
    }
    
    /**
     * Create demo MCQ sets
     */
    private function create_demo_mcq_sets() {
        $mcq_sets_data = [
            [
                'title' => 'Computer Science Fundamentals',
                'description' => 'Test your knowledge of basic computer science concepts including programming and data structures.',
                'author' => 'prof_johnson',
                'mcq_subjects' => ['Computer Science'],
                'total_marks' => 10,
                'passing_marks' => 6,
                'time_limit' => 30,
                'display_format' => 'next_next',
                'negative_marking' => 0.25,
                'pricing_type' => 'free',
                'question_count' => 5
            ],
            [
                'title' => 'Mathematics Basics',
                'description' => 'Essential mathematics questions covering algebra and calculus fundamentals.',
                'author' => 'independent_teacher1',
                'mcq_subjects' => ['Mathematics'],
                'total_marks' => 8,
                'passing_marks' => 5,
                'time_limit' => 25,
                'display_format' => 'single_page',
                'negative_marking' => 0.25,
                'pricing_type' => 'free',
                'question_count' => 4
            ],
            [
                'title' => 'Physics Challenge',
                'description' => 'Advanced physics questions including quantum mechanics and classical physics.',
                'author' => 'dr_chen',
                'mcq_subjects' => ['Science'],
                'total_marks' => 15,
                'passing_marks' => 9,
                'time_limit' => 45,
                'display_format' => 'next_next',
                'negative_marking' => 0.5,
                'pricing_type' => 'paid',
                'price' => 9.99,
                'question_count' => 6
            ],
            [
                'title' => 'Business Essentials',
                'description' => 'Core business concepts including marketing and finance fundamentals.',
                'author' => 'prof_martinez',
                'mcq_subjects' => ['Business Studies'],
                'total_marks' => 6,
                'passing_marks' => 4,
                'time_limit' => 20,
                'display_format' => 'single_page',
                'negative_marking' => 0.25,
                'pricing_type' => 'free',
                'question_count' => 3
            ],
            [
                'title' => 'English Literature & Grammar',
                'description' => 'Test your English language skills with literature and grammar questions.',
                'author' => 'prof_williams',
                'mcq_subjects' => ['English'],
                'total_marks' => 8,
                'passing_marks' => 5,
                'time_limit' => 30,
                'display_format' => 'next_next',
                'negative_marking' => 0.25,
                'pricing_type' => 'free',
                'question_count' => 4
            ],
            [
                'title' => 'Biology Fundamentals',
                'description' => 'Basic biology concepts including cell biology and genetics.',
                'author' => 'independent_teacher2',
                'mcq_subjects' => ['Science'],
                'total_marks' => 6,
                'passing_marks' => 4,
                'time_limit' => 20,
                'display_format' => 'single_page',
                'negative_marking' => 0.25,
                'pricing_type' => 'free',
                'question_count' => 3
            ],
            [
                'title' => 'Advanced Computer Science',
                'description' => 'Challenging questions on algorithms, data structures, and advanced programming concepts.',
                'author' => 'prof_johnson',
                'mcq_subjects' => ['Computer Science'],
                'total_marks' => 20,
                'passing_marks' => 14,
                'time_limit' => 60,
                'display_format' => 'next_next',
                'negative_marking' => 0.5,
                'pricing_type' => 'paid',
                'price' => 19.99,
                'question_count' => 8
            ],
            [
                'title' => 'Quick Math Quiz',
                'description' => 'Short mathematics quiz for quick practice and assessment.',
                'author' => 'independent_teacher1',
                'mcq_subjects' => ['Mathematics'],
                'total_marks' => 5,
                'passing_marks' => 3,
                'time_limit' => 10,
                'display_format' => 'single_page',
                'negative_marking' => 0,
                'pricing_type' => 'free',
                'question_count' => 5
            ],
            [
                'title' => 'Science Comprehensive Test',
                'description' => 'Comprehensive science test covering physics, chemistry, and biology.',
                'author' => 'dr_chen',
                'mcq_subjects' => ['Science'],
                'total_marks' => 30,
                'passing_marks' => 18,
                'time_limit' => 90,
                'display_format' => 'next_next',
                'negative_marking' => 0.33,
                'pricing_type' => 'paid',
                'price' => 14.99,
                'question_count' => 10
            ],
            [
                'title' => 'Business Management Essentials',
                'description' => 'Essential business management concepts for aspiring managers.',
                'author' => 'prof_martinez',
                'mcq_subjects' => ['Business Studies'],
                'total_marks' => 12,
                'passing_marks' => 7,
                'time_limit' => 40,
                'display_format' => 'single_page',
                'negative_marking' => 0.25,
                'pricing_type' => 'free',
                'question_count' => 6
            ],
            [
                'title' => 'Mixed Subject Challenge',
                'description' => 'Challenging questions from multiple subjects to test broad knowledge.',
                'author' => 'prof_williams',
                'mcq_subjects' => ['English', 'History'],
                'total_marks' => 25,
                'passing_marks' => 15,
                'time_limit' => 75,
                'display_format' => 'next_next',
                'negative_marking' => 0.5,
                'pricing_type' => 'paid',
                'price' => 12.99,
                'question_count' => 10
            ]
        ];
        
        foreach ($mcq_sets_data as $set_data) {
            // Get author ID
            $author_id = isset($this->demo_teachers[$set_data['author']]) ? $this->demo_teachers[$set_data['author']] : 1;
            
            // Create MCQ Set post
            $set_id = wp_insert_post([
                'post_title' => $set_data['title'],
                'post_content' => $set_data['description'],
                'post_status' => 'publish',
                'post_type' => 'mcq_set',
                'post_author' => $author_id
            ]);
            
            if ($set_id && !is_wp_error($set_id)) {
                // Get relevant MCQs for this set
                $relevant_mcqs = [];
                foreach ($set_data['mcq_subjects'] as $subject) {
                    if (isset($this->demo_subjects[$subject])) {
                        $subject_mcqs = get_posts([
                            'post_type' => 'mcq',
                            'post_status' => 'publish',
                            'numberposts' => $set_data['question_count'],
                            'tax_query' => [
                                [
                                    'taxonomy' => 'mcq_subject',
                                    'field' => 'term_id',
                                    'terms' => $this->demo_subjects[$subject]
                                ]
                            ],
                            'author' => $author_id
                        ]);
                        
                        foreach ($subject_mcqs as $mcq) {
                            $relevant_mcqs[] = $mcq->ID;
                        }
                    }
                }
                
                // If we don't have enough MCQs from the same author, get from any author
                if (count($relevant_mcqs) < $set_data['question_count']) {
                    foreach ($set_data['mcq_subjects'] as $subject) {
                        if (isset($this->demo_subjects[$subject])) {
                            $additional_mcqs = get_posts([
                                'post_type' => 'mcq',
                                'post_status' => 'publish',
                                'numberposts' => $set_data['question_count'] - count($relevant_mcqs),
                                'tax_query' => [
                                    [
                                        'taxonomy' => 'mcq_subject',
                                        'field' => 'term_id',
                                        'terms' => $this->demo_subjects[$subject]
                                    ]
                                ],
                                'exclude' => $relevant_mcqs
                            ]);
                            
                            foreach ($additional_mcqs as $mcq) {
                                $relevant_mcqs[] = $mcq->ID;
                            }
                        }
                    }
                }
                
                // Add MCQ Set metadata
                update_post_meta($set_id, '_mcq_set_questions', $relevant_mcqs);
                update_post_meta($set_id, '_mcq_set_total_marks', $set_data['total_marks']);
                update_post_meta($set_id, '_mcq_set_passing_marks', $set_data['passing_marks']);
                update_post_meta($set_id, '_mcq_set_time_limit', $set_data['time_limit']);
                update_post_meta($set_id, '_mcq_set_display_format', $set_data['display_format']);
                update_post_meta($set_id, '_mcq_set_negative_marking', $set_data['negative_marking']);
                update_post_meta($set_id, '_mcq_set_pricing_type', $set_data['pricing_type']);
                
                if (isset($set_data['price'])) {
                    update_post_meta($set_id, '_mcq_set_price', $set_data['price']);
                }
                
                // Individual question marks (equal distribution)
                $marks_per_question = [];
                $marks_each = $set_data['total_marks'] / count($relevant_mcqs);
                foreach ($relevant_mcqs as $mcq_id) {
                    $marks_per_question[$mcq_id] = $marks_each;
                }
                update_post_meta($set_id, '_mcq_set_individual_marks', $marks_per_question);
                
                // Store for later use
                $this->demo_mcq_sets[] = $set_id;
            }
        }
    }    

    /**
     * Create demo follow relationships
     */
    private function create_demo_follow_relationships() {
        global $wpdb;
        
        // Create follow relationships between students and teachers/institutions
        $follow_relationships = [
            // Students following teachers
            ['student_alice', 'prof_johnson', 'user'],
            ['student_alice', 'dr_chen', 'user'],
            ['student_bob', 'prof_martinez', 'user'],
            ['student_bob', 'independent_teacher1', 'user'],
            ['student_carol', 'dr_chen', 'user'],
            ['student_carol', 'independent_teacher1', 'user'],
            ['student_david', 'prof_williams', 'user'],
            ['student_emma', 'independent_teacher2', 'user'],
            ['student_emma', 'dr_patel', 'user'],
            ['student_frank', 'independent_teacher1', 'user'],
            
            // Students following institutions
            ['student_alice', 'tech-university', 'institution'],
            ['student_alice', 'science-academy', 'institution'],
            ['student_bob', 'global-business-school', 'institution'],
            ['student_carol', 'science-academy', 'institution'],
            ['student_david', 'liberal-arts-college', 'institution'],
            ['student_emma', 'medical-institute', 'institution'],
            ['student_frank', 'mcq_academy', 'institution']
        ];
        
        foreach ($follow_relationships as $relationship) {
            $follower_id = isset($this->demo_students[$relationship[0]]) ? $this->demo_students[$relationship[0]] : null;
            
            if ($relationship[2] === 'user') {
                $followed_id = isset($this->demo_teachers[$relationship[1]]) ? $this->demo_teachers[$relationship[1]] : null;
            } else {
                $followed_id = isset($this->demo_institutions[$relationship[1]]) ? $this->demo_institutions[$relationship[1]] : null;
            }
            
            if ($follower_id && $followed_id) {
                mcqhome_add_user_follow($follower_id, $followed_id, $relationship[2]);
            }
        }
    }
    
    /**
     * Create demo enrollments
     */
    private function create_demo_enrollments() {
        // Enroll students in various MCQ sets
        $enrollments = [
            ['student_alice', 0], // Computer Science Fundamentals
            ['student_alice', 2], // Physics Challenge
            ['student_bob', 1],   // Mathematics Basics
            ['student_bob', 3],   // Business Essentials
            ['student_carol', 0], // Computer Science Fundamentals
            ['student_carol', 2], // Physics Challenge
            ['student_david', 4], // English Literature & Grammar
            ['student_emma', 5],  // Biology Fundamentals
            ['student_frank', 1], // Mathematics Basics
        ];
        
        foreach ($enrollments as $enrollment) {
            $student_id = isset($this->demo_students[$enrollment[0]]) ? $this->demo_students[$enrollment[0]] : null;
            $set_id = isset($this->demo_mcq_sets[$enrollment[1]]) ? $this->demo_mcq_sets[$enrollment[1]] : null;
            
            if ($student_id && $set_id) {
                mcqhome_enroll_user($student_id, $set_id, 'free');
                
                // Create some progress data for enrolled students
                $this->create_demo_progress($student_id, $set_id);
            }
        }
    }
    
    /**
     * Create demo progress data
     */
    private function create_demo_progress($user_id, $mcq_set_id) {
        // Get MCQs in this set
        $mcq_ids = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
        if (empty($mcq_ids)) {
            return;
        }
        
        $total_questions = count($mcq_ids);
        $completed_questions = rand(1, $total_questions);
        $current_question = min($completed_questions + 1, $total_questions);
        
        // Create answers data
        $answers_data = [];
        $completed_list = [];
        
        for ($i = 0; $i < $completed_questions; $i++) {
            $mcq_id = $mcq_ids[$i];
            $correct_answer = get_post_meta($mcq_id, '_mcq_correct_answer', true);
            
            // 70% chance of correct answer for demo purposes
            $is_correct = (rand(1, 100) <= 70);
            $selected_answer = $is_correct ? $correct_answer : ['A', 'B', 'C', 'D'][rand(0, 3)];
            
            $answers_data[$mcq_id] = $selected_answer;
            $completed_list[] = $mcq_id;
            
            // Record individual MCQ attempt
            mcqhome_record_mcq_attempt(
                $user_id,
                $mcq_id,
                $mcq_set_id,
                $selected_answer,
                $correct_answer,
                $is_correct ? 1 : 0,
                $is_correct ? 0 : 0.25
            );
        }
        
        // Update user progress
        mcqhome_update_user_progress($user_id, $mcq_set_id, [
            'current_question' => $current_question,
            'total_questions' => $total_questions,
            'completed_questions' => $completed_list,
            'answers_data' => $answers_data,
            'progress_percentage' => ($completed_questions / $total_questions) * 100
        ]);
        
        // If set is completed, create set attempt record
        if ($completed_questions === $total_questions) {
            $correct_count = 0;
            $total_score = 0;
            
            foreach ($answers_data as $mcq_id => $selected_answer) {
                $correct_answer = get_post_meta($mcq_id, '_mcq_correct_answer', true);
                if ($selected_answer === $correct_answer) {
                    $correct_count++;
                    $total_score += 1; // Assuming 1 mark per question for demo
                } else {
                    $total_score -= 0.25; // Negative marking
                }
            }
            
            $max_score = $total_questions;
            $score_percentage = ($total_score / $max_score) * 100;
            $passing_marks = get_post_meta($mcq_set_id, '_mcq_set_passing_marks', true) ?: ($max_score * 0.6);
            $is_passed = $total_score >= $passing_marks;
            
            mcqhome_update_set_attempt($user_id, $mcq_set_id, [
                'total_questions' => $total_questions,
                'answered_questions' => $total_questions,
                'correct_answers' => $correct_count,
                'total_score' => $total_score,
                'max_score' => $max_score,
                'score_percentage' => $score_percentage,
                'passing_score' => $passing_marks,
                'is_passed' => $is_passed ? 1 : 0,
                'status' => 'completed',
                'completed_at' => current_time('mysql')
            ]);
        }
    }
    
    /**
     * Clean up all demo content
     */
    public function cleanup_all_demo_content() {
        global $wpdb;
        
        // Set time limit for large operations
        set_time_limit(300);
        
        // Delete demo users (except admin)
        $demo_users = get_users([
            'role__in' => ['student', 'teacher', 'institution'],
            'meta_query' => [
                [
                    'key' => 'demo_user',
                    'compare' => 'EXISTS'
                ]
            ]
        ]);
        
        // Mark demo users for identification
        $this->mark_demo_users();
        
        // Get all demo users
        $all_demo_users = array_merge(
            get_users(['role' => 'student']),
            get_users(['role' => 'teacher']),
            get_users(['role' => 'institution'])
        );
        
        foreach ($all_demo_users as $user) {
            // Don't delete admin users
            if (!in_array('administrator', $user->roles)) {
                wp_delete_user($user->ID);
            }
        }
        
        // Delete demo posts
        $demo_post_types = ['mcq', 'mcq_set', 'institution'];
        foreach ($demo_post_types as $post_type) {
            $posts = get_posts([
                'post_type' => $post_type,
                'post_status' => 'any',
                'numberposts' => -1
            ]);
            
            foreach ($posts as $post) {
                wp_delete_post($post->ID, true);
            }
        }
        
        // Delete demo taxonomy terms
        $demo_taxonomies = ['mcq_subject', 'mcq_topic'];
        foreach ($demo_taxonomies as $taxonomy) {
            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => false
            ]);
            
            foreach ($terms as $term) {
                wp_delete_term($term->term_id, $taxonomy);
            }
        }
        
        // Clean up database tables
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}mcq_attempts");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}mcq_set_attempts");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}mcq_user_progress");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}mcq_user_follows");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}mcq_user_enrollments");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}mcq_user_notifications");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}mcq_activity_stream");
        
        // Reset demo content flag
        delete_option('mcqhome_demo_content_generated');
        delete_option('mcqhome_demo_content_timestamp');
    }
    
    /**
     * Mark demo users for identification
     */
    private function mark_demo_users() {
        $demo_usernames = [
            'prof_johnson', 'prof_martinez', 'dr_chen', 'prof_williams', 'dr_patel',
            'independent_teacher1', 'independent_teacher2',
            'student_alice', 'student_bob', 'student_carol', 'student_david', 'student_emma', 'student_frank'
        ];
        
        foreach ($demo_usernames as $username) {
            $user = get_user_by('login', $username);
            if ($user) {
                update_user_meta($user->ID, 'demo_user', '1');
            }
        }
    }
}

// Initialize demo content system
new MCQHome_Demo_Content();

/**
 * Auto-generate demo content on theme activation if option is set
 */
function mcqhome_auto_generate_demo_content() {
    // Only auto-generate if explicitly requested
    if (get_option('mcqhome_auto_demo_content', false)) {
        $demo_generator = new MCQHome_Demo_Content();
        try {
            $demo_generator->generate_all_demo_content();
        } catch (Exception $e) {
            error_log('MCQHome: Failed to auto-generate demo content - ' . $e->getMessage());
        }
        
        // Remove the auto-generation flag
        delete_option('mcqhome_auto_demo_content');
    }
}
add_action('after_switch_theme', 'mcqhome_auto_generate_demo_content');

/**
 * Helper function to check if demo content exists
 */
function mcqhome_has_demo_content() {
    return get_option('mcqhome_demo_content_generated', false);
}

/**
 * Helper function to get demo content statistics
 */
function mcqhome_get_demo_content_stats() {
    if (!mcqhome_has_demo_content()) {
        return false;
    }
    
    return [
        'institutions' => wp_count_posts('institution')->publish ?? 0,
        'mcqs' => wp_count_posts('mcq')->publish ?? 0,
        'mcq_sets' => wp_count_posts('mcq_set')->publish ?? 0,
        'teachers' => count(get_users(['role' => 'teacher'])),
        'students' => count(get_users(['role' => 'student'])),
        'subjects' => wp_count_terms(['taxonomy' => 'mcq_subject']),
        'topics' => wp_count_terms(['taxonomy' => 'mcq_topic']),
        'generated_at' => get_option('mcqhome_demo_content_timestamp')
    ];
}
/*
*
 * Add demo content notice to admin dashboard
 */
function mcqhome_demo_content_admin_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $screen = get_current_screen();
    if ($screen->id !== 'dashboard') {
        return;
    }
    
    if (mcqhome_has_demo_content()) {
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>' . __('MCQHome Demo Content Active', 'mcqhome') . '</strong></p>';
        echo '<p>' . __('Your site is currently using demo content. You can manage or remove it from the', 'mcqhome') . ' ';
        echo '<a href="' . admin_url('themes.php?page=mcqhome-demo-content') . '">' . __('Demo Content page', 'mcqhome') . '</a>.</p>';
        echo '</div>';
    }
}
add_action('admin_notices', 'mcqhome_demo_content_admin_notice');

/**
 * Add demo content widget to dashboard
 */
function mcqhome_add_demo_content_dashboard_widget() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    wp_add_dashboard_widget(
        'mcqhome_demo_content_widget',
        __('MCQHome Demo Content', 'mcqhome'),
        'mcqhome_demo_content_dashboard_widget_content'
    );
}
add_action('wp_dashboard_setup', 'mcqhome_add_demo_content_dashboard_widget');

/**
 * Demo content dashboard widget content
 */
function mcqhome_demo_content_dashboard_widget_content() {
    if (mcqhome_has_demo_content()) {
        $stats = mcqhome_get_demo_content_stats();
        echo '<p>' . __('Demo content is active with:', 'mcqhome') . '</p>';
        echo '<ul>';
        echo '<li>' . sprintf(__('%d Institutions', 'mcqhome'), $stats['institutions']) . '</li>';
        echo '<li>' . sprintf(__('%d Teachers', 'mcqhome'), $stats['teachers']) . '</li>';
        echo '<li>' . sprintf(__('%d Students', 'mcqhome'), $stats['students']) . '</li>';
        echo '<li>' . sprintf(__('%d MCQs', 'mcqhome'), $stats['mcqs']) . '</li>';
        echo '<li>' . sprintf(__('%d MCQ Sets', 'mcqhome'), $stats['mcq_sets']) . '</li>';
        echo '</ul>';
        echo '<p><a href="' . admin_url('themes.php?page=mcqhome-demo-content') . '" class="button">' . __('Manage Demo Content', 'mcqhome') . '</a></p>';
    } else {
        echo '<p>' . __('No demo content is currently active.', 'mcqhome') . '</p>';
        echo '<p>' . __('Demo content helps you understand how the MCQHome theme works and provides sample data to get started.', 'mcqhome') . '</p>';
        echo '<p><a href="' . admin_url('themes.php?page=mcqhome-demo-content') . '" class="button button-primary">' . __('Generate Demo Content', 'mcqhome') . '</a></p>';
    }
}

/**
 * Add sample follow relationships with more variety
 */
function mcqhome_create_additional_follow_relationships() {
    global $wpdb;
    
    // Additional follow relationships for more realistic demo data
    $additional_follows = [
        // Cross-institutional follows
        ['student_alice', 'prof_martinez', 'user'],
        ['student_bob', 'dr_chen', 'user'],
        ['student_carol', 'prof_williams', 'user'],
        ['student_david', 'independent_teacher1', 'user'],
        ['student_emma', 'prof_johnson', 'user'],
        ['student_frank', 'prof_williams', 'user'],
        
        // More institutional follows
        ['student_alice', 'global-business-school', 'institution'],
        ['student_bob', 'science-academy', 'institution'],
        ['student_carol', 'liberal-arts-college', 'institution'],
        ['student_david', 'tech-university', 'institution'],
        ['student_emma', 'science-academy', 'institution'],
        ['student_frank', 'tech-university', 'institution']
    ];
    
    $demo_content = new MCQHome_Demo_Content();
    
    foreach ($additional_follows as $relationship) {
        $follower_id = isset($demo_content->demo_students[$relationship[0]]) ? $demo_content->demo_students[$relationship[0]] : null;
        
        if ($relationship[2] === 'user') {
            $followed_id = isset($demo_content->demo_teachers[$relationship[1]]) ? $demo_content->demo_teachers[$relationship[1]] : null;
        } else {
            $followed_id = isset($demo_content->demo_institutions[$relationship[1]]) ? $demo_content->demo_institutions[$relationship[1]] : null;
        }
        
        if ($follower_id && $followed_id) {
            mcqhome_add_user_follow($follower_id, $followed_id, $relationship[2]);
        }
    }
}

/**
 * Create demo notifications for users
 */
function mcqhome_create_demo_notifications() {
    $demo_content = new MCQHome_Demo_Content();
    
    // Create sample notifications for students
    foreach ($demo_content->demo_students as $username => $student_id) {
        // New content notification
        mcqhome_create_notification(
            $student_id,
            'new_content',
            __('New MCQ Set Available', 'mcqhome'),
            __('A new MCQ set has been published by one of your followed teachers.', 'mcqhome'),
            ['type' => 'mcq_set', 'action_url' => home_url('/browse/')]
        );
        
        // Achievement notification
        mcqhome_create_notification(
            $student_id,
            'achievement',
            __('Great Progress!', 'mcqhome'),
            __('You have completed 5 MCQ sets this week. Keep up the excellent work!', 'mcqhome'),
            ['achievement_type' => 'weekly_completion']
        );
    }
}

/**
 * Initialize demo content with additional features
 */
function mcqhome_initialize_enhanced_demo_content() {
    if (mcqhome_has_demo_content()) {
        mcqhome_create_additional_follow_relationships();
        mcqhome_create_demo_notifications();
    }
}
add_action('init', 'mcqhome_initialize_enhanced_demo_content', 20);