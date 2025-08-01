<?php
/**
 * Template for displaying single institution pages
 *
 * @package MCQHome
 * @since 1.0.0
 */

get_header(); ?>

<div class="container mx-auto px-4 py-8">
    <?php while (have_posts()) : the_post(); ?>
        <div class="institution-profile bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Institution Header -->
            <div class="institution-header bg-gradient-to-r from-blue-600 to-blue-800 text-white p-8">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                    <div class="institution-avatar">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-white shadow-lg">
                                <?php the_post_thumbnail('thumbnail', ['class' => 'w-full h-full object-cover']); ?>
                            </div>
                        <?php else : ?>
                            <div class="w-24 h-24 rounded-full bg-white bg-opacity-20 flex items-center justify-center border-4 border-white">
                                <i class="fas fa-university text-3xl text-white"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="institution-info flex-1">
                        <h1 class="text-3xl font-bold mb-2"><?php the_title(); ?></h1>
                        <?php 
                        $institution_type = get_post_meta(get_the_ID(), '_institution_type', true);
                        $location = get_post_meta(get_the_ID(), '_institution_location', true);
                        $established = get_post_meta(get_the_ID(), '_institution_established', true);
                        ?>
                        
                        <div class="institution-meta flex flex-wrap gap-4 text-blue-100">
                            <?php if ($institution_type) : ?>
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-tag"></i>
                                    <?php echo esc_html($institution_type); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($location) : ?>
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo esc_html($location); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($established) : ?>
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo esc_html($established); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="institution-actions">
                        <?php if (is_user_logged_in() && mcqhome_get_user_role() === 'student') : ?>
                            <?php
                            $user_id = get_current_user_id();
                            $institution_id = get_the_ID();
                            $is_following = mcqhome_is_following($user_id, $institution_id, 'institution');
                            
                            if ($is_following) :
                            ?>
                                <button class="unfollow-institution-btn bg-gray-500 text-white px-6 py-2 rounded-full font-semibold hover:bg-gray-600 transition-colors" 
                                        data-institution-id="<?php echo $institution_id; ?>">
                                    <i class="fas fa-check mr-2"></i>
                                    <?php _e('Following', 'mcqhome'); ?>
                                </button>
                            <?php else : ?>
                                <button class="follow-institution-btn bg-white text-blue-600 px-6 py-2 rounded-full font-semibold hover:bg-blue-50 transition-colors" 
                                        data-institution-id="<?php echo $institution_id; ?>">
                                    <i class="fas fa-plus mr-2"></i>
                                    <?php _e('Follow', 'mcqhome'); ?>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Institution Stats -->
            <div class="institution-stats bg-gray-50 p-6 border-b">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php
                    $stats = mcqhome_get_institution_stats(get_the_ID());
                    ?>
                    <div class="stat-item text-center">
                        <div class="stat-number text-2xl font-bold text-blue-600"><?php echo $stats['teachers']; ?></div>
                        <div class="stat-label text-gray-600"><?php _e('Teachers', 'mcqhome'); ?></div>
                    </div>
                    <div class="stat-item text-center">
                        <div class="stat-number text-2xl font-bold text-green-600"><?php echo $stats['mcq_sets']; ?></div>
                        <div class="stat-label text-gray-600"><?php _e('MCQ Sets', 'mcqhome'); ?></div>
                    </div>
                    <div class="stat-item text-center">
                        <div class="stat-number text-2xl font-bold text-purple-600"><?php echo $stats['total_mcqs']; ?></div>
                        <div class="stat-label text-gray-600"><?php _e('Total MCQs', 'mcqhome'); ?></div>
                    </div>
                    <div class="stat-item text-center">
                        <div class="stat-number text-2xl font-bold text-orange-600"><?php echo $stats['followers']; ?></div>
                        <div class="stat-label text-gray-600"><?php _e('Followers', 'mcqhome'); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Institution Content -->
            <div class="institution-content p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2">
                        <!-- About Section -->
                        <div class="about-section mb-8">
                            <h2 class="text-2xl font-bold mb-4"><?php _e('About', 'mcqhome'); ?></h2>
                            <div class="prose max-w-none">
                                <?php the_content(); ?>
                            </div>
                        </div>
                        
                        <!-- Teachers Section -->
                        <div class="teachers-section mb-8">
                            <h2 class="text-2xl font-bold mb-4"><?php _e('Our Teachers', 'mcqhome'); ?></h2>
                            <div class="teachers-grid grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php
                                $teachers = mcqhome_get_institution_teachers(get_the_ID());
                                if ($teachers) :
                                    foreach ($teachers as $teacher) :
                                ?>
                                    <div class="teacher-card bg-white border rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <div class="flex items-center gap-3">
                                            <div class="teacher-avatar">
                                                <?php echo get_avatar($teacher->ID, 48, '', '', ['class' => 'rounded-full']); ?>
                                            </div>
                                            <div class="teacher-info">
                                                <h3 class="font-semibold">
                                                    <a href="<?php echo get_author_posts_url($teacher->ID); ?>" class="text-blue-600 hover:text-blue-800">
                                                        <?php echo esc_html($teacher->display_name); ?>
                                                    </a>
                                                </h3>
                                                <p class="text-sm text-gray-600">
                                                    <?php echo esc_html(get_user_meta($teacher->ID, 'specialization', true)); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php 
                                    endforeach;
                                else :
                                ?>
                                    <p class="text-gray-600 col-span-2"><?php _e('No teachers found for this institution.', 'mcqhome'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Recent MCQ Sets -->
                        <div class="mcq-sets-section">
                            <h2 class="text-2xl font-bold mb-4"><?php _e('Recent MCQ Sets', 'mcqhome'); ?></h2>
                            <div class="mcq-sets-grid grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php
                                $mcq_sets = mcqhome_get_institution_mcq_sets(get_the_ID(), 6);
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
                                            <span><?php echo get_the_author(); ?></span>
                                        </div>
                                        <p class="text-sm text-gray-700"><?php echo wp_trim_words(get_the_excerpt(), 15); ?></p>
                                    </div>
                                <?php 
                                    endwhile;
                                    wp_reset_postdata();
                                else :
                                ?>
                                    <p class="text-gray-600 col-span-2"><?php _e('No MCQ sets found for this institution.', 'mcqhome'); ?></p>
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
                            $contact_email = get_post_meta(get_the_ID(), '_institution_email', true);
                            $contact_phone = get_post_meta(get_the_ID(), '_institution_phone', true);
                            $website = get_post_meta(get_the_ID(), '_institution_website', true);
                            ?>
                            
                            <?php if ($contact_email) : ?>
                                <div class="contact-item flex items-center gap-2 mb-2">
                                    <i class="fas fa-envelope text-gray-500"></i>
                                    <a href="mailto:<?php echo esc_attr($contact_email); ?>" class="text-blue-600 hover:text-blue-800">
                                        <?php echo esc_html($contact_email); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($contact_phone) : ?>
                                <div class="contact-item flex items-center gap-2 mb-2">
                                    <i class="fas fa-phone text-gray-500"></i>
                                    <a href="tel:<?php echo esc_attr($contact_phone); ?>" class="text-blue-600 hover:text-blue-800">
                                        <?php echo esc_html($contact_phone); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($website) : ?>
                                <div class="contact-item flex items-center gap-2 mb-2">
                                    <i class="fas fa-globe text-gray-500"></i>
                                    <a href="<?php echo esc_url($website); ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                                        <?php _e('Visit Website', 'mcqhome'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Categories -->
                        <div class="categories-info bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4"><?php _e('Subject Areas', 'mcqhome'); ?></h3>
                            <?php
                            $subjects = mcqhome_get_institution_subjects(get_the_ID());
                            if ($subjects) :
                            ?>
                                <div class="subjects-list">
                                    <?php foreach ($subjects as $subject) : ?>
                                        <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mr-2 mb-2">
                                            <?php echo esc_html($subject->name); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <p class="text-gray-600"><?php _e('No subject areas specified.', 'mcqhome'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>