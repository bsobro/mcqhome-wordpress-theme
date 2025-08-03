<?php
/**
 * Template for displaying single MCQ sets
 *
 * @package MCQHome
 * @since 1.0.0
 */

get_header(); ?>

<div class="container mx-auto px-4 py-8">
    <?php while (have_posts()) : the_post(); ?>
        <div class="single-mcq-set-container max-w-4xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="breadcrumb mb-6 text-sm">
                <ol class="flex items-center space-x-2 text-gray-600">
                    <li><a href="<?php echo home_url(); ?>" class="hover:text-blue-600"><?php _e('Home', 'mcqhome'); ?></a></li>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    <li><a href="<?php echo get_permalink(get_page_by_path('browse')); ?>" class="hover:text-blue-600"><?php _e('Browse MCQs', 'mcqhome'); ?></a></li>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    <li class="text-gray-900"><?php the_title(); ?></li>
                </ol>
            </nav>

            <!-- MCQ Set Header -->
            <div class="mcq-set-header bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div class="flex-1">
                        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3"><?php the_title(); ?></h1>
                        
                        <!-- Meta Information -->
                        <div class="mcq-set-meta flex flex-wrap items-center gap-4 text-sm text-gray-600 mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-user mr-2"></i>
                                <span><?php _e('By', 'mcqhome'); ?> <a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>" class="text-blue-600 hover:text-blue-800"><?php the_author(); ?></a></span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-calendar mr-2"></i>
                                <span><?php echo get_the_date(); ?></span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-question-circle mr-2"></i>
                                <span><?php echo mcqhome_get_mcq_set_question_count(get_the_ID()); ?> <?php _e('questions', 'mcqhome'); ?></span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-eye mr-2"></i>
                                <span><?php echo mcqhome_get_post_views(get_the_ID()); ?> <?php _e('views', 'mcqhome'); ?></span>
                            </div>
                        </div>

                        <!-- Description -->
                        <?php if (get_the_content()) : ?>
                            <div class="mcq-set-description text-gray-700 mb-4">
                                <?php the_content(); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Taxonomies -->
                        <div class="mcq-set-taxonomies flex flex-wrap gap-2 mb-4">
                            <?php
                            // Display subjects
                            $subjects = get_the_terms(get_the_ID(), 'mcq_subject');
                            if ($subjects && !is_wp_error($subjects)) :
                                foreach ($subjects as $subject) :
                            ?>
                                    <a href="<?php echo get_term_link($subject); ?>" 
                                       class="inline-block bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full hover:bg-blue-200 transition-colors">
                                        <i class="fas fa-book mr-1"></i><?php echo esc_html($subject->name); ?>
                                    </a>
                            <?php
                                endforeach;
                            endif;

                            // Display topics
                            $topics = get_the_terms(get_the_ID(), 'mcq_topic');
                            if ($topics && !is_wp_error($topics)) :
                                foreach ($topics as $topic) :
                            ?>
                                    <a href="<?php echo get_term_link($topic); ?>" 
                                       class="inline-block bg-purple-100 text-purple-800 text-xs px-3 py-1 rounded-full hover:bg-purple-200 transition-colors">
                                        <i class="fas fa-tag mr-1"></i><?php echo esc_html($topic->name); ?>
                                    </a>
                            <?php
                                endforeach;
                            endif;

                            // Display difficulty
                            $difficulties = get_the_terms(get_the_ID(), 'mcq_difficulty');
                            if ($difficulties && !is_wp_error($difficulties)) :
                                foreach ($difficulties as $difficulty) :
                                    $color_class = '';
                                    switch (strtolower($difficulty->slug)) {
                                        case 'easy':
                                            $color_class = 'bg-green-100 text-green-800 hover:bg-green-200';
                                            break;
                                        case 'medium':
                                            $color_class = 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200';
                                            break;
                                        case 'hard':
                                            $color_class = 'bg-red-100 text-red-800 hover:bg-red-200';
                                            break;
                                        default:
                                            $color_class = 'bg-gray-100 text-gray-800 hover:bg-gray-200';
                                    }
                            ?>
                                    <a href="<?php echo get_term_link($difficulty); ?>" 
                                       class="inline-block <?php echo $color_class; ?> text-xs px-3 py-1 rounded-full transition-colors">
                                        <i class="fas fa-signal mr-1"></i><?php echo esc_html($difficulty->name); ?>
                                    </a>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <?php if (is_user_logged_in()) : ?>
                            <a href="<?php echo add_query_arg('set_id', get_the_ID(), home_url('/take-assessment/')); ?>" 
                               class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors flex items-center justify-center">
                                <i class="fas fa-play mr-2"></i>
                                <?php _e('Start Assessment', 'mcqhome'); ?>
                            </a>
                        <?php else : ?>
                            <a href="<?php echo wp_login_url(get_permalink()); ?>" 
                               class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors flex items-center justify-center">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                <?php _e('Login to Start', 'mcqhome'); ?>
                            </a>
                        <?php endif; ?>
                        
                        <button class="bookmark-btn bg-gray-200 text-gray-700 px-4 py-3 rounded-md hover:bg-gray-300 transition-colors" 
                                data-mcq-set-id="<?php echo get_the_ID(); ?>">
                            <i class="fas fa-bookmark"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- MCQ Set Stats -->
            <div class="mcq-set-stats bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php _e('Assessment Details', 'mcqhome'); ?></h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php
                    $total_marks = get_post_meta(get_the_ID(), '_mcq_set_total_marks', true);
                    $passing_marks = get_post_meta(get_the_ID(), '_mcq_set_passing_marks', true);
                    $time_limit = get_post_meta(get_the_ID(), '_mcq_set_time_limit', true);
                    $negative_marking = get_post_meta(get_the_ID(), '_mcq_set_negative_marking', true);
                    ?>
                    
                    <div class="stat-item text-center">
                        <div class="stat-value text-2xl font-bold text-blue-600"><?php echo mcqhome_get_mcq_set_question_count(get_the_ID()); ?></div>
                        <div class="stat-label text-sm text-gray-600"><?php _e('Questions', 'mcqhome'); ?></div>
                    </div>
                    
                    <div class="stat-item text-center">
                        <div class="stat-value text-2xl font-bold text-green-600"><?php echo $total_marks ?: 'N/A'; ?></div>
                        <div class="stat-label text-sm text-gray-600"><?php _e('Total Marks', 'mcqhome'); ?></div>
                    </div>
                    
                    <div class="stat-item text-center">
                        <div class="stat-value text-2xl font-bold text-purple-600"><?php echo $passing_marks ?: 'N/A'; ?></div>
                        <div class="stat-label text-sm text-gray-600"><?php _e('Passing Marks', 'mcqhome'); ?></div>
                    </div>
                    
                    <div class="stat-item text-center">
                        <div class="stat-value text-2xl font-bold text-orange-600">
                            <?php echo $time_limit ? $time_limit . 'min' : __('No Limit', 'mcqhome'); ?>
                        </div>
                        <div class="stat-label text-sm text-gray-600"><?php _e('Time Limit', 'mcqhome'); ?></div>
                    </div>
                </div>
                
                <?php if ($negative_marking > 0) : ?>
                    <div class="negative-marking-notice bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2 mt-1"></i>
                            <div class="text-yellow-800">
                                <strong><?php _e('Negative Marking:', 'mcqhome'); ?></strong>
                                <?php printf(__('%s marks will be deducted for each incorrect answer.', 'mcqhome'), $negative_marking); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Questions Preview (if user has permission) -->
            <?php
            $mcq_ids = get_post_meta(get_the_ID(), '_mcq_set_questions', true);
            if (!empty($mcq_ids) && (current_user_can('edit_post', get_the_ID()) || current_user_can('manage_options'))) :
            ?>
                <div class="questions-preview bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php _e('Questions Preview', 'mcqhome'); ?></h3>
                    <div class="space-y-4">
                        <?php
                        $preview_count = min(3, count($mcq_ids)); // Show first 3 questions
                        for ($i = 0; $i < $preview_count; $i++) :
                            $mcq = get_post($mcq_ids[$i]);
                            if ($mcq) :
                                $question_text = get_post_meta($mcq->ID, '_mcq_question_text', true);
                        ?>
                                <div class="question-preview bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <div class="question-number bg-blue-100 text-blue-800 w-8 h-8 rounded-full flex items-center justify-center font-semibold text-sm mr-4 mt-1">
                                            <?php echo $i + 1; ?>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900 mb-2"><?php echo esc_html($mcq->post_title); ?></h4>
                                            <p class="text-gray-600 text-sm">
                                                <?php echo wp_trim_words(strip_tags($question_text), 15); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            endif;
                        endfor;
                        
                        if (count($mcq_ids) > 3) :
                        ?>
                            <div class="text-center text-gray-600 text-sm">
                                <?php printf(__('... and %d more questions', 'mcqhome'), count($mcq_ids) - 3); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Comments Section -->
            <?php if (comments_open() || get_comments_number()) : ?>
                <div class="comments-section bg-white rounded-lg shadow-md p-6">
                    <?php comments_template(); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</div>

<!-- JavaScript for MCQ Set Interaction -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookmarkBtn = document.querySelector('.bookmark-btn');
    
    // Bookmark functionality
    if (bookmarkBtn) {
        bookmarkBtn.addEventListener('click', function() {
            const mcqSetId = this.dataset.mcqSetId;
            mcqhome_toggle_mcq_set_bookmark(mcqSetId, this);
        });
    }
});

// Helper function for MCQ set bookmarking
function mcqhome_toggle_mcq_set_bookmark(mcqSetId, button) {
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'mcqhome_toggle_bookmark',
            nonce: '<?php echo wp_create_nonce('mcqhome_nonce'); ?>',
            mcq_id: mcqSetId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.toggle('text-yellow-500');
            button.innerHTML = data.data.bookmarked 
                ? '<i class="fas fa-bookmark text-yellow-500"></i>'
                : '<i class="fas fa-bookmark"></i>';
        }
    });
}
</script>

<?php get_footer(); ?>