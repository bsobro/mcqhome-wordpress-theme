<?php
/**
 * Template for displaying single MCQ questions
 *
 * @package MCQHome
 * @since 1.0.0
 */

get_header(); ?>

<div class="container mx-auto px-4 py-8">
    <?php while (have_posts()) : the_post(); ?>
        <div class="single-mcq-container max-w-4xl mx-auto">
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

            <!-- MCQ Header -->
            <div class="mcq-header bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div class="flex-1">
                        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3"><?php the_title(); ?></h1>
                        
                        <!-- Meta Information -->
                        <div class="mcq-meta flex flex-wrap items-center gap-4 text-sm text-gray-600 mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-user mr-2"></i>
                                <span><?php _e('By', 'mcqhome'); ?> <a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>" class="text-blue-600 hover:text-blue-800"><?php the_author(); ?></a></span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-calendar mr-2"></i>
                                <span><?php echo get_the_date(); ?></span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-eye mr-2"></i>
                                <span><?php echo mcqhome_get_post_views(get_the_ID()); ?> <?php _e('views', 'mcqhome'); ?></span>
                            </div>
                        </div>

                        <!-- Taxonomies -->
                        <div class="mcq-taxonomies flex flex-wrap gap-2 mb-4">
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
                            <button class="practice-mode-btn bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors flex items-center justify-center">
                                <i class="fas fa-play mr-2"></i>
                                <?php _e('Practice Mode', 'mcqhome'); ?>
                            </button>
                        <?php else : ?>
                            <a href="<?php echo wp_login_url(get_permalink()); ?>" 
                               class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors flex items-center justify-center">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                <?php _e('Login to Practice', 'mcqhome'); ?>
                            </a>
                        <?php endif; ?>
                        
                        <button class="bookmark-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors" 
                                data-mcq-id="<?php echo get_the_ID(); ?>">
                            <i class="fas fa-bookmark"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- MCQ Content -->
            <div class="mcq-content bg-white rounded-lg shadow-md p-6 mb-6">
                <?php
                // Get MCQ data
                $question_text = get_post_meta(get_the_ID(), '_mcq_question_text', true);
                $option_a = get_post_meta(get_the_ID(), '_mcq_option_a', true);
                $option_b = get_post_meta(get_the_ID(), '_mcq_option_b', true);
                $option_c = get_post_meta(get_the_ID(), '_mcq_option_c', true);
                $option_d = get_post_meta(get_the_ID(), '_mcq_option_d', true);
                $correct_answer = get_post_meta(get_the_ID(), '_mcq_correct_answer', true);
                $explanation = get_post_meta(get_the_ID(), '_mcq_explanation', true);
                $marks = get_post_meta(get_the_ID(), '_mcq_marks', true) ?: 1;
                $negative_marks = get_post_meta(get_the_ID(), '_mcq_negative_marks', true) ?: 0;
                $time_limit = get_post_meta(get_the_ID(), '_mcq_time_limit', true);
                $hint = get_post_meta(get_the_ID(), '_mcq_hint', true);
                ?>

                <!-- Question -->
                <div class="question-section mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4"><?php _e('Question', 'mcqhome'); ?></h2>
                    <div class="question-content bg-gray-50 rounded-lg p-4 text-gray-800 leading-relaxed">
                        <?php echo wp_kses_post($question_text); ?>
                    </div>
                    
                    <?php if ($time_limit) : ?>
                        <div class="question-meta mt-3 text-sm text-gray-600">
                            <i class="fas fa-clock mr-1"></i>
                            <?php printf(__('Time limit: %d seconds', 'mcqhome'), $time_limit); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Answer Options -->
                <div class="options-section mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php _e('Answer Options', 'mcqhome'); ?></h3>
                    <div class="options-list space-y-3">
                        <?php
                        $options = [
                            'A' => $option_a,
                            'B' => $option_b,
                            'C' => $option_c,
                            'D' => $option_d
                        ];

                        foreach ($options as $key => $value) :
                            if (!empty($value)) :
                        ?>
                                <div class="option-item bg-gray-50 rounded-lg p-4 border-2 border-transparent hover:border-blue-200 transition-colors cursor-pointer"
                                     data-option="<?php echo $key; ?>">
                                    <div class="flex items-start">
                                        <div class="option-letter bg-blue-100 text-blue-800 w-8 h-8 rounded-full flex items-center justify-center font-semibold text-sm mr-4 mt-1">
                                            <?php echo $key; ?>
                                        </div>
                                        <div class="option-text flex-1 text-gray-800">
                                            <?php echo esc_html($value); ?>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </div>
                </div>

                <!-- Hint (if available) -->
                <?php if ($hint && is_user_logged_in()) : ?>
                    <div class="hint-section mb-6">
                        <button class="show-hint-btn text-blue-600 hover:text-blue-800 text-sm flex items-center">
                            <i class="fas fa-lightbulb mr-2"></i>
                            <?php _e('Show Hint', 'mcqhome'); ?>
                        </button>
                        <div class="hint-content hidden mt-3 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                            <div class="flex items-start">
                                <i class="fas fa-lightbulb text-yellow-600 mr-2 mt-1"></i>
                                <div class="text-yellow-800">
                                    <?php echo wp_kses_post($hint); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Answer and Explanation (Hidden by default) -->
                <div class="answer-section hidden" id="answer-section">
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg mb-4">
                        <h4 class="text-lg font-semibold text-green-800 mb-2">
                            <i class="fas fa-check-circle mr-2"></i>
                            <?php _e('Correct Answer', 'mcqhome'); ?>
                        </h4>
                        <div class="text-green-700">
                            <strong><?php _e('Option', 'mcqhome'); ?> <?php echo $correct_answer; ?>:</strong>
                            <?php echo esc_html($options[$correct_answer]); ?>
                        </div>
                    </div>

                    <?php if ($explanation) : ?>
                        <div class="explanation bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                            <h4 class="text-lg font-semibold text-blue-800 mb-2">
                                <i class="fas fa-info-circle mr-2"></i>
                                <?php _e('Explanation', 'mcqhome'); ?>
                            </h4>
                            <div class="text-blue-700 leading-relaxed">
                                <?php echo wp_kses_post($explanation); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons flex flex-wrap gap-3 mt-6 pt-6 border-t border-gray-200">
                    <button class="show-answer-btn bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition-colors">
                        <i class="fas fa-eye mr-2"></i>
                        <?php _e('Show Answer', 'mcqhome'); ?>
                    </button>
                    
                    <button class="reset-btn bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 transition-colors hidden">
                        <i class="fas fa-redo mr-2"></i>
                        <?php _e('Try Again', 'mcqhome'); ?>
                    </button>
                </div>
            </div>

            <!-- MCQ Stats -->
            <div class="mcq-stats bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php _e('Question Statistics', 'mcqhome'); ?></h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="stat-item text-center">
                        <div class="stat-value text-2xl font-bold text-blue-600"><?php echo $marks; ?></div>
                        <div class="stat-label text-sm text-gray-600"><?php _e('Marks', 'mcqhome'); ?></div>
                    </div>
                    
                    <?php if ($negative_marks > 0) : ?>
                        <div class="stat-item text-center">
                            <div class="stat-value text-2xl font-bold text-red-600">-<?php echo $negative_marks; ?></div>
                            <div class="stat-label text-sm text-gray-600"><?php _e('Negative Marks', 'mcqhome'); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="stat-item text-center">
                        <div class="stat-value text-2xl font-bold text-purple-600"><?php echo mcqhome_get_post_views(get_the_ID()); ?></div>
                        <div class="stat-label text-sm text-gray-600"><?php _e('Views', 'mcqhome'); ?></div>
                    </div>
                    
                    <div class="stat-item text-center">
                        <div class="stat-value text-2xl font-bold text-green-600">
                            <?php echo mcqhome_get_mcq_success_rate(get_the_ID()); ?>%
                        </div>
                        <div class="stat-label text-sm text-gray-600"><?php _e('Success Rate', 'mcqhome'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Related MCQs -->
            <?php
            $related_mcqs = mcqhome_get_related_mcqs(get_the_ID(), 4);
            if ($related_mcqs->have_posts()) :
            ?>
                <div class="related-mcqs bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php _e('Related Questions', 'mcqhome'); ?></h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php while ($related_mcqs->have_posts()) : $related_mcqs->the_post(); ?>
                            <div class="related-mcq-item bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                <h4 class="font-medium text-gray-900 mb-2">
                                    <a href="<?php the_permalink(); ?>" class="hover:text-blue-600">
                                        <?php echo wp_trim_words(get_the_title(), 10); ?>
                                    </a>
                                </h4>
                                <div class="text-sm text-gray-600">
                                    <?php
                                    $related_subjects = get_the_terms(get_the_ID(), 'mcq_subject');
                                    if ($related_subjects && !is_wp_error($related_subjects)) :
                                        echo esc_html($related_subjects[0]->name);
                                    endif;
                                    ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php wp_reset_postdata(); ?>
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

<!-- JavaScript for MCQ Interaction -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const options = document.querySelectorAll('.option-item');
    const showAnswerBtn = document.querySelector('.show-answer-btn');
    const resetBtn = document.querySelector('.reset-btn');
    const answerSection = document.getElementById('answer-section');
    const showHintBtn = document.querySelector('.show-hint-btn');
    const hintContent = document.querySelector('.hint-content');
    const bookmarkBtn = document.querySelector('.bookmark-btn');
    
    let selectedOption = null;
    let answered = false;
    
    // Option selection
    options.forEach(option => {
        option.addEventListener('click', function() {
            if (answered) return;
            
            // Remove previous selection
            options.forEach(opt => {
                opt.classList.remove('border-blue-500', 'bg-blue-50');
            });
            
            // Add selection to clicked option
            this.classList.add('border-blue-500', 'bg-blue-50');
            selectedOption = this.dataset.option;
        });
    });
    
    // Show answer
    if (showAnswerBtn) {
        showAnswerBtn.addEventListener('click', function() {
            if (!selectedOption) {
                alert('<?php _e('Please select an answer first.', 'mcqhome'); ?>');
                return;
            }
            
            answered = true;
            const correctAnswer = '<?php echo $correct_answer; ?>';
            
            // Show correct/incorrect styling
            options.forEach(option => {
                const optionKey = option.dataset.option;
                
                if (optionKey === correctAnswer) {
                    option.classList.add('border-green-500', 'bg-green-50');
                    option.classList.remove('border-blue-500', 'bg-blue-50');
                } else if (optionKey === selectedOption && optionKey !== correctAnswer) {
                    option.classList.add('border-red-500', 'bg-red-50');
                    option.classList.remove('border-blue-500', 'bg-blue-50');
                }
            });
            
            // Show answer section
            answerSection.classList.remove('hidden');
            
            // Hide show answer button, show reset button
            this.classList.add('hidden');
            resetBtn.classList.remove('hidden');
            
            // Track view/attempt
            mcqhome_track_mcq_attempt(<?php echo get_the_ID(); ?>, selectedOption, correctAnswer);
        });
    }
    
    // Reset functionality
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            answered = false;
            selectedOption = null;
            
            // Reset option styling
            options.forEach(option => {
                option.classList.remove('border-blue-500', 'bg-blue-50', 'border-green-500', 'bg-green-50', 'border-red-500', 'bg-red-50');
            });
            
            // Hide answer section
            answerSection.classList.add('hidden');
            
            // Show answer button, hide reset button
            showAnswerBtn.classList.remove('hidden');
            this.classList.add('hidden');
        });
    }
    
    // Show hint
    if (showHintBtn && hintContent) {
        showHintBtn.addEventListener('click', function() {
            hintContent.classList.toggle('hidden');
            this.innerHTML = hintContent.classList.contains('hidden') 
                ? '<i class="fas fa-lightbulb mr-2"></i><?php _e('Show Hint', 'mcqhome'); ?>'
                : '<i class="fas fa-lightbulb mr-2"></i><?php _e('Hide Hint', 'mcqhome'); ?>';
        });
    }
    
    // Bookmark functionality
    if (bookmarkBtn) {
        bookmarkBtn.addEventListener('click', function() {
            const mcqId = this.dataset.mcqId;
            mcqhome_toggle_bookmark(mcqId, this);
        });
    }
});

// Helper functions
function mcqhome_track_mcq_attempt(mcqId, selectedAnswer, correctAnswer) {
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'mcqhome_track_mcq_attempt',
            nonce: '<?php echo wp_create_nonce('mcqhome_nonce'); ?>',
            mcq_id: mcqId,
            selected_answer: selectedAnswer,
            correct_answer: correctAnswer
        })
    });
}

function mcqhome_toggle_bookmark(mcqId, button) {
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'mcqhome_toggle_bookmark',
            nonce: '<?php echo wp_create_nonce('mcqhome_nonce'); ?>',
            mcq_id: mcqId
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