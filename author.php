<?php
/**
 * Template for displaying teacher profile pages
 *
 * @package MCQHome
 * @since 1.0.0
 */

get_header(); 

// Get the author information
$author_id = get_queried_object_id();
$author = get_userdata($author_id);
$user_role = mcqhome_get_user_role($author_id);

// Only show profile for teachers
if ($user_role !== 'teacher') {
    wp_redirect(home_url());
    exit;
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="teacher-profile bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Teacher Header -->
        <div class="teacher-header bg-gradient-to-r from-green-600 to-green-800 text-white p-8">
            <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                <div class="teacher-avatar">
                    <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-white shadow-lg">
                        <?php echo get_avatar($author_id, 96, '', '', ['class' => 'w-full h-full object-cover']); ?>
                    </div>
                </div>
                
                <div class="teacher-info flex-1">
                    <h1 class="text-3xl font-bold mb-2"><?php echo esc_html($author->display_name); ?></h1>
                    <?php 
                    $specialization = get_user_meta($author_id, 'specialization', true);
                    $qualification = get_user_meta($author_id, 'qualification', true);
                    $experience = get_user_meta($author_id, 'experience', true);
                    ?>
                    
                    <div class="teacher-meta flex flex-wrap gap-4 text-green-100">
                        <?php if ($specialization) : ?>
                            <span class="flex items-center gap-1">
                                <i class="fas fa-graduation-cap"></i>
                                <?php echo esc_html($specialization); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($qualification) : ?>
                            <span class="flex items-center gap-1">
                                <i class="fas fa-certificate"></i>
                                <?php echo esc_html($qualification); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($experience) : ?>
                            <span class="flex items-center gap-1">
                                <i class="fas fa-clock"></i>
                                <?php echo esc_html($experience); ?> <?php _e('years experience', 'mcqhome'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="teacher-actions">
                    <?php if (is_user_logged_in() && mcqhome_get_user_role() === 'student') : ?>
                        <?php
                        $current_user_id = get_current_user_id();
                        $is_following = mcqhome_is_following($current_user_id, $author_id, 'user');
                        
                        if ($is_following) :
                        ?>
                            <button class="unfollow-teacher-btn bg-gray-500 text-white px-6 py-2 rounded-full font-semibold hover:bg-gray-600 transition-colors" 
                                    data-teacher-id="<?php echo $author_id; ?>">
                                <i class="fas fa-check mr-2"></i>
                                <?php _e('Following', 'mcqhome'); ?>
                            </button>
                        <?php else : ?>
                            <button class="follow-teacher-btn bg-white text-green-600 px-6 py-2 rounded-full font-semibold hover:bg-green-50 transition-colors" 
                                    data-teacher-id="<?php echo $author_id; ?>">
                                <i class="fas fa-plus mr-2"></i>
                                <?php _e('Follow', 'mcqhome'); ?>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Teacher Stats -->
        <div class="teacher-stats bg-gray-50 p-6 border-b">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php
                $stats = mcqhome_get_teacher_stats($author_id);
                ?>
                <div class="stat-item text-center">
                    <div class="stat-number text-2xl font-bold text-green-600"><?php echo $stats['mcq_sets']; ?></div>
                    <div class="stat-label text-gray-600"><?php _e('MCQ Sets', 'mcqhome'); ?></div>
                </div>
                <div class="stat-item text-center">
                    <div class="stat-number text-2xl font-bold text-blue-600"><?php echo $stats['total_mcqs']; ?></div>
                    <div class="stat-label text-gray-600"><?php _e('Total MCQs', 'mcqhome'); ?></div>
                </div>
                <div class="stat-item text-center">
                    <div class="stat-number text-2xl font-bold text-purple-600"><?php echo $stats['students']; ?></div>
                    <div class="stat-label text-gray-600"><?php _e('Students', 'mcqhome'); ?></div>
                </div>
                <div class="stat-item text-center">
                    <div class="stat-number text-2xl font-bold text-orange-600"><?php echo $stats['followers']; ?></div>
                    <div class="stat-label text-gray-600"><?php _e('Followers', 'mcqhome'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Teacher Content -->
        <div class="teacher-content p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <!-- About Section -->
                    <div class="about-section mb-8">
                        <h2 class="text-2xl font-bold mb-4"><?php _e('About', 'mcqhome'); ?></h2>
                        <div class="prose max-w-none">
                            <?php 
                            $bio = get_user_meta($author_id, 'description', true);
                            if ($bio) {
                                echo wpautop(esc_html($bio));
                            } else {
                                echo '<p class="text-gray-600">' . __('No bio available.', 'mcqhome') . '</p>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Institution Associations -->
                    <div class="institutions-section mb-8">
                        <h2 class="text-2xl font-bold mb-4"><?php _e('Associated Institutions', 'mcqhome'); ?></h2>
                        <div class="institutions-grid grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php
                            $institutions = mcqhome_get_teacher_institutions($author_id);
                            if ($institutions) :
                                foreach ($institutions as $institution) :
                            ?>
                                <div class="institution-card bg-white border rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-center gap-3">
                                        <div class="institution-avatar">
                                            <?php if (has_post_thumbnail($institution->ID)) : ?>
                                                <div class="w-12 h-12 rounded-full overflow-hidden">
                                                    <?php echo get_the_post_thumbnail($institution->ID, 'thumbnail', ['class' => 'w-full h-full object-cover']); ?>
                                                </div>
                                            <?php else : ?>
                                                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <i class="fas fa-university text-blue-600"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="institution-info">
                                            <h3 class="font-semibold">
                                                <a href="<?php echo get_permalink($institution->ID); ?>" class="text-blue-600 hover:text-blue-800">
                                                    <?php echo esc_html($institution->post_title); ?>
                                                </a>
                                            </h3>
                                            <p class="text-sm text-gray-600">
                                                <?php echo esc_html(get_post_meta($institution->ID, '_institution_type', true)); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                endforeach;
                            else :
                            ?>
                                <p class="text-gray-600 col-span-2"><?php _e('No institutional associations found.', 'mcqhome'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Recent MCQ Sets -->
                    <div class="mcq-sets-section">
                        <h2 class="text-2xl font-bold mb-4"><?php _e('Recent MCQ Sets', 'mcqhome'); ?></h2>
                        <div class="mcq-sets-grid grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php
                            $mcq_sets = new WP_Query([
                                'post_type' => 'mcq_set',
                                'author' => $author_id,
                                'posts_per_page' => 6,
                                'post_status' => 'publish'
                            ]);
                            
                            if ($mcq_sets->have_posts()) :
                                while ($mcq_sets->have_posts()) : $mcq_sets->the_post();
                            ?>
                                <div class="mcq-set-card bg-white border rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <h3 class="font-semibold mb-2">
                                        <a href="<?php the_permalink(); ?>" class="text-blue-600 hover:text-blue-800">
                                            <?php the_title(); ?>
                                        </a>
                                    </h3>
                                    <div class="mcq-set-meta text-sm text-gray-600 mb-2">
                                        <span><?php echo mcqhome_get_mcq_set_question_count(get_the_ID()); ?> <?php _e('questions', 'mcqhome'); ?></span>
                                        <span class="mx-2">•</span>
                                        <span><?php echo get_the_date(); ?></span>
                                    </div>
                                    <p class="text-sm text-gray-700"><?php echo wp_trim_words(get_the_excerpt(), 15); ?></p>
                                    
                                    <!-- Rating Display -->
                                    <div class="rating-display mt-2">
                                        <?php 
                                        $rating = mcqhome_get_mcq_set_rating(get_the_ID());
                                        if ($rating > 0) :
                                        ?>
                                            <div class="flex items-center gap-1">
                                                <div class="stars flex">
                                                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                        <i class="fas fa-star <?php echo $i <= $rating ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <span class="text-sm text-gray-600">(<?php echo number_format($rating, 1); ?>)</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php 
                                endwhile;
                                wp_reset_postdata();
                            else :
                            ?>
                                <p class="text-gray-600 col-span-2"><?php _e('No MCQ sets found.', 'mcqhome'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Contact Information -->
                    <div class="contact-info bg-gray-50 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold mb-4"><?php _e('Contact Information', 'mcqhome'); ?></h3>
                        <?php
                        $contact_email = get_user_meta($author_id, 'contact_email', true);
                        $social_links = get_user_meta($author_id, 'social_links', true);
                        ?>
                        
                        <?php if ($contact_email) : ?>
                            <div class="contact-item flex items-center gap-2 mb-2">
                                <i class="fas fa-envelope text-gray-500"></i>
                                <a href="mailto:<?php echo esc_attr($contact_email); ?>" class="text-blue-600 hover:text-blue-800">
                                    <?php echo esc_html($contact_email); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($social_links && is_array($social_links)) : ?>
                            <?php foreach ($social_links as $platform => $url) : ?>
                                <?php if ($url) : ?>
                                    <div class="contact-item flex items-center gap-2 mb-2">
                                        <i class="fab fa-<?php echo esc_attr($platform); ?> text-gray-500"></i>
                                        <a href="<?php echo esc_url($url); ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                                            <?php echo ucfirst($platform); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Teaching Areas -->
                    <div class="teaching-areas bg-gray-50 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold mb-4"><?php _e('Teaching Areas', 'mcqhome'); ?></h3>
                        <?php
                        $subjects = mcqhome_get_teacher_subjects($author_id);
                        if ($subjects) :
                        ?>
                            <div class="subjects-list">
                                <?php foreach ($subjects as $subject) : ?>
                                    <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full mr-2 mb-2">
                                        <?php echo esc_html($subject->name); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <p class="text-gray-600"><?php _e('No teaching areas specified.', 'mcqhome'); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="recent-activity bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4"><?php _e('Recent Activity', 'mcqhome'); ?></h3>
                        <?php
                        $recent_posts = new WP_Query([
                            'post_type' => ['mcq', 'mcq_set'],
                            'author' => $author_id,
                            'posts_per_page' => 5,
                            'post_status' => 'publish'
                        ]);
                        
                        if ($recent_posts->have_posts()) :
                        ?>
                            <div class="activity-list space-y-3">
                                <?php while ($recent_posts->have_posts()) : $recent_posts->the_post(); ?>
                                    <div class="activity-item">
                                        <div class="text-sm text-gray-600 mb-1">
                                            <?php echo get_the_date(); ?>
                                        </div>
                                        <div class="text-sm">
                                            <span class="text-gray-500">
                                                <?php echo get_post_type() === 'mcq' ? __('Created MCQ:', 'mcqhome') : __('Created Set:', 'mcqhome'); ?>
                                            </span>
                                            <a href="<?php the_permalink(); ?>" class="text-blue-600 hover:text-blue-800">
                                                <?php echo wp_trim_words(get_the_title(), 6); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php 
                            wp_reset_postdata();
                        else :
                        ?>
                            <p class="text-gray-600"><?php _e('No recent activity.', 'mcqhome'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>