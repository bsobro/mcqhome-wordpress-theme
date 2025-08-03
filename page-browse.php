<?php

/**
 * Template for displaying browse MCQs page
 *
 * @package MCQHome
 * @since 1.0.0
 */

get_header(); ?>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="page-header mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php _e('Browse MCQs', 'mcqhome'); ?></h1>
        <p class="text-gray-600"><?php _e('Discover MCQ sets and questions from various institutions and teachers.', 'mcqhome'); ?></p>
    </div>

    <!-- Hierarchical Category Navigation -->
    <div class="category-navigation bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4"><?php _e('Browse by Category', 'mcqhome'); ?></h2>

        <!-- Subject Categories with Topics -->
        <div class="subjects-section mb-6">
            <h3 class="text-lg font-medium mb-3"><?php _e('Subjects & Topics', 'mcqhome'); ?></h3>

            <?php
            $subjects = get_terms([
                'taxonomy' => 'mcq_subject',
                'hide_empty' => true,
                'orderby' => 'count',
                'order' => 'DESC',
                'number' => 8 // Show top 8 subjects
            ]);

            if ($subjects && !is_wp_error($subjects)) :
            ?>
                <div class="subjects-hierarchy">
                    <?php foreach ($subjects as $subject) : ?>
                        <div class="subject-group mb-4 p-4 bg-gray-50 rounded-lg">
                            <div class="subject-header flex items-center justify-between mb-3">
                                <a href="<?php echo get_term_link($subject); ?>"
                                    class="subject-title flex items-center text-lg font-medium text-blue-700 hover:text-blue-900">
                                    <i class="fas fa-book mr-2"></i>
                                    <?php echo esc_html($subject->name); ?>
                                    <span class="ml-2 text-sm text-gray-500">(<?php echo $subject->count; ?>)</span>
                                </a>
                                <button class="toggle-topics text-gray-400 hover:text-gray-600" data-subject="<?php echo $subject->term_id; ?>">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>

                            <?php
                            // Get topics for this subject
                            $topics = get_terms([
                                'taxonomy' => 'mcq_topic',
                                'hide_empty' => true,
                                'meta_query' => [
                                    [
                                        'key' => 'parent_subject',
                                        'value' => $subject->term_id,
                                        'compare' => '='
                                    ]
                                ],
                                'orderby' => 'name',
                                'order' => 'ASC',
                                'number' => 6 // Show top 6 topics per subject
                            ]);

                            if ($topics && !is_wp_error($topics)) :
                            ?>
                                <div class="topics-list hidden" id="topics-<?php echo $subject->term_id; ?>">
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                        <?php foreach ($topics as $topic) : ?>
                                            <a href="<?php echo get_term_link($topic); ?>"
                                                class="topic-link bg-white hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-md p-2 text-sm transition-colors">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-gray-700"><?php echo esc_html($topic->name); ?></span>
                                                    <span class="text-xs text-gray-500"><?php echo $topic->count; ?></span>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>

                                    <?php
                                    // Check if there are more topics
                                    $total_topics = wp_count_terms([
                                        'taxonomy' => 'mcq_topic',
                                        'hide_empty' => true,
                                        'meta_query' => [
                                            [
                                                'key' => 'parent_subject',
                                                'value' => $subject->term_id,
                                                'compare' => '='
                                            ]
                                        ]
                                    ]);

                                    if ($total_topics > 6) :
                                    ?>
                                        <div class="mt-2 text-center">
                                            <a href="<?php echo get_term_link($subject); ?>"
                                                class="text-blue-600 hover:text-blue-800 text-sm">
                                                <?php printf(__('View all %d topics', 'mcqhome'), $total_topics); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- View All Subjects Link -->
                <?php
                $total_subjects = wp_count_terms([
                    'taxonomy' => 'mcq_subject',
                    'hide_empty' => true
                ]);

                if ($total_subjects > 8) :
                ?>
                    <div class="text-center mt-4">
                        <a href="#" class="view-all-subjects text-blue-600 hover:text-blue-800 font-medium">
                            <?php printf(__('View all %d subjects', 'mcqhome'), $total_subjects); ?>
                        </a>
                    </div>
                <?php endif; ?>

            <?php else : ?>
                <p class="text-gray-600"><?php _e('No subjects found.', 'mcqhome'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Quick Access Categories -->
        <div class="quick-categories">
            <h3 class="text-lg font-medium mb-3"><?php _e('Quick Access', 'mcqhome'); ?></h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <!-- Difficulty Levels -->
                <?php
                $difficulties = get_terms([
                    'taxonomy' => 'mcq_difficulty',
                    'hide_empty' => true,
                    'orderby' => 'name',
                    'order' => 'ASC'
                ]);

                if ($difficulties && !is_wp_error($difficulties)) :
                    foreach ($difficulties as $difficulty) :
                        $color_class = '';
                        $icon_class = '';
                        switch (strtolower($difficulty->slug)) {
                            case 'easy':
                                $color_class = 'bg-green-50 hover:bg-green-100 border-green-200 text-green-800';
                                $icon_class = 'text-green-600';
                                break;
                            case 'medium':
                                $color_class = 'bg-yellow-50 hover:bg-yellow-100 border-yellow-200 text-yellow-800';
                                $icon_class = 'text-yellow-600';
                                break;
                            case 'hard':
                                $color_class = 'bg-red-50 hover:bg-red-100 border-red-200 text-red-800';
                                $icon_class = 'text-red-600';
                                break;
                            default:
                                $color_class = 'bg-gray-50 hover:bg-gray-100 border-gray-200 text-gray-800';
                                $icon_class = 'text-gray-600';
                        }
                ?>
                        <a href="<?php echo get_term_link($difficulty); ?>"
                            class="difficulty-card <?php echo $color_class; ?> border rounded-lg p-3 text-center transition-colors">
                            <div class="<?php echo $icon_class; ?> text-xl mb-2">
                                <i class="fas fa-signal"></i>
                            </div>
                            <div class="text-sm font-medium"><?php echo esc_html($difficulty->name); ?></div>
                            <div class="text-xs opacity-75"><?php echo $difficulty->count; ?> <?php _e('items', 'mcqhome'); ?></div>
                        </a>
                <?php
                    endforeach;
                endif;
                ?>

                <!-- Popular Categories -->
                <a href="<?php echo add_query_arg('sort', 'popular', get_permalink()); ?>"
                    class="category-card bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-800 rounded-lg p-3 text-center transition-colors">
                    <div class="text-purple-600 text-xl mb-2">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div class="text-sm font-medium"><?php _e('Popular', 'mcqhome'); ?></div>
                    <div class="text-xs opacity-75"><?php _e('Trending', 'mcqhome'); ?></div>
                </a>

                <a href="<?php echo add_query_arg('price', 'free', get_permalink()); ?>"
                    class="category-card bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 rounded-lg p-3 text-center transition-colors">
                    <div class="text-emerald-600 text-xl mb-2">
                        <i class="fas fa-gift"></i>
                    </div>
                    <div class="text-sm font-medium"><?php _e('Free', 'mcqhome'); ?></div>
                    <div class="text-xs opacity-75"><?php _e('No cost', 'mcqhome'); ?></div>
                </a>

                <a href="<?php echo add_query_arg('sort', 'date', get_permalink()); ?>"
                    class="category-card bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-800 rounded-lg p-3 text-center transition-colors">
                    <div class="text-blue-600 text-xl mb-2">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="text-sm font-medium"><?php _e('Latest', 'mcqhome'); ?></div>
                    <div class="text-xs opacity-75"><?php _e('New content', 'mcqhome'); ?></div>
                </a>
            </div>
        </div>
    </div>

    <!-- Advanced Search and Filters -->
    <div class="search-filters bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="filter-header flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900"><?php _e('Search & Filter Content', 'mcqhome'); ?></h3>
            <button type="button" class="toggle-advanced-filters text-blue-600 hover:text-blue-800 text-sm">
                <span class="toggle-text"><?php _e('Advanced Filters', 'mcqhome'); ?></span>
                <i class="fas fa-chevron-down ml-1"></i>
            </button>
        </div>

        <form method="GET" class="mcq-search-form">
            <!-- Basic Search Row -->
            <div class="basic-filters grid grid-cols-1 md:grid-cols-6 gap-4 mb-4">
                <!-- Search Input -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Search', 'mcqhome'); ?>
                    </label>
                    <div class="relative">
                        <input type="text"
                            id="search"
                            name="search"
                            value="<?php echo esc_attr(get_query_var('search', '')); ?>"
                            placeholder="<?php _e('Search questions, topics, institutions...', 'mcqhome'); ?>"
                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Subject Filter -->
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Subject', 'mcqhome'); ?>
                    </label>
                    <select id="subject"
                        name="subject"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value=""><?php _e('All Subjects', 'mcqhome'); ?></option>
                        <?php
                        $all_subjects = get_terms([
                            'taxonomy' => 'mcq_subject',
                            'hide_empty' => true,
                            'orderby' => 'name',
                            'order' => 'ASC'
                        ]);

                        if ($all_subjects && !is_wp_error($all_subjects)) :
                            foreach ($all_subjects as $subject) :
                        ?>
                                <option value="<?php echo $subject->slug; ?>" <?php selected(get_query_var('subject'), $subject->slug); ?>>
                                    <?php echo esc_html($subject->name); ?> (<?php echo $subject->count; ?>)
                                </option>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </select>
                </div>

                <!-- Topic Filter -->
                <div>
                    <label for="topic" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Topic', 'mcqhome'); ?>
                    </label>
                    <select id="topic"
                        name="topic"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value=""><?php _e('All Topics', 'mcqhome'); ?></option>
                        <?php
                        $all_topics = get_terms([
                            'taxonomy' => 'mcq_topic',
                            'hide_empty' => true,
                            'orderby' => 'name',
                            'order' => 'ASC'
                        ]);

                        if ($all_topics && !is_wp_error($all_topics)) :
                            foreach ($all_topics as $topic) :
                        ?>
                                <option value="<?php echo $topic->slug; ?>" <?php selected(get_query_var('topic'), $topic->slug); ?>>
                                    <?php echo esc_html($topic->name); ?> (<?php echo $topic->count; ?>)
                                </option>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </select>
                </div>

                <!-- Difficulty Filter -->
                <div>
                    <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Difficulty', 'mcqhome'); ?>
                    </label>
                    <select id="difficulty"
                        name="difficulty"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value=""><?php _e('All Levels', 'mcqhome'); ?></option>
                        <?php
                        $all_difficulties = get_terms([
                            'taxonomy' => 'mcq_difficulty',
                            'hide_empty' => true,
                            'orderby' => 'name',
                            'order' => 'ASC'
                        ]);

                        if ($all_difficulties && !is_wp_error($all_difficulties)) :
                            foreach ($all_difficulties as $difficulty) :
                        ?>
                                <option value="<?php echo $difficulty->slug; ?>" <?php selected(get_query_var('difficulty'), $difficulty->slug); ?>>
                                    <?php echo esc_html($difficulty->name); ?> (<?php echo $difficulty->count; ?>)
                                </option>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </select>
                </div>

                <!-- Sort Options -->
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Sort By', 'mcqhome'); ?>
                    </label>
                    <select id="sort"
                        name="sort"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="date" <?php selected(get_query_var('sort'), 'date'); ?>><?php _e('Latest', 'mcqhome'); ?></option>
                        <option value="title" <?php selected(get_query_var('sort'), 'title'); ?>><?php _e('Title A-Z', 'mcqhome'); ?></option>
                        <option value="popular" <?php selected(get_query_var('sort'), 'popular'); ?>><?php _e('Most Popular', 'mcqhome'); ?></option>
                        <option value="rating" <?php selected(get_query_var('sort'), 'rating'); ?>><?php _e('Highest Rated', 'mcqhome'); ?></option>
                        <option value="questions_count" <?php selected(get_query_var('sort'), 'questions_count'); ?>><?php _e('Most Questions', 'mcqhome'); ?></option>
                    </select>
                </div>
            </div>

            <!-- Advanced Filters (Initially Hidden) -->
            <div class="advanced-filters hidden">
                <div class="border-t pt-4">
                    <h4 class="text-md font-medium text-gray-800 mb-3"><?php _e('Advanced Filters', 'mcqhome'); ?></h4>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <!-- Price Filter -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php _e('Price', 'mcqhome'); ?>
                            </label>
                            <select id="price"
                                name="price"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value=""><?php _e('All', 'mcqhome'); ?></option>
                                <option value="free" <?php selected(get_query_var('price'), 'free'); ?>><?php _e('Free', 'mcqhome'); ?></option>
                                <option value="paid" <?php selected(get_query_var('price'), 'paid'); ?>><?php _e('Paid', 'mcqhome'); ?></option>
                            </select>
                        </div>

                        <!-- Institution Filter -->
                        <div>
                            <label for="institution" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php _e('Institution', 'mcqhome'); ?>
                            </label>
                            <select id="institution"
                                name="institution"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value=""><?php _e('All Institutions', 'mcqhome'); ?></option>
                                <?php
                                $institutions = get_posts([
                                    'post_type' => 'institution',
                                    'posts_per_page' => -1,
                                    'post_status' => 'publish',
                                    'orderby' => 'title',
                                    'order' => 'ASC'
                                ]);

                                foreach ($institutions as $institution) :
                                ?>
                                    <option value="<?php echo $institution->ID; ?>" <?php selected(get_query_var('institution'), $institution->ID); ?>>
                                        <?php echo esc_html($institution->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Teacher Filter -->
                        <div>
                            <label for="teacher" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php _e('Teacher', 'mcqhome'); ?>
                            </label>
                            <select id="teacher"
                                name="teacher"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value=""><?php _e('All Teachers', 'mcqhome'); ?></option>
                                <?php
                                $teachers = get_users([
                                    'role' => 'teacher',
                                    'orderby' => 'display_name',
                                    'order' => 'ASC',
                                    'number' => 100 // Limit to prevent performance issues
                                ]);

                                foreach ($teachers as $teacher) :
                                ?>
                                    <option value="<?php echo $teacher->ID; ?>" <?php selected(get_query_var('teacher'), $teacher->ID); ?>>
                                        <?php echo esc_html($teacher->display_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Content Type -->
                        <div>
                            <label for="content_type" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php _e('Content Type', 'mcqhome'); ?>
                            </label>
                            <select id="content_type"
                                name="content_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="" <?php selected(get_query_var('content_type', ''), ''); ?>><?php _e('All Types', 'mcqhome'); ?></option>
                                <option value="mcq_set" <?php selected(get_query_var('content_type'), 'mcq_set'); ?>><?php _e('MCQ Sets', 'mcqhome'); ?></option>
                                <option value="mcq" <?php selected(get_query_var('content_type'), 'mcq'); ?>><?php _e('Individual MCQs', 'mcqhome'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Rating and Question Count Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Minimum Rating -->
                        <div>
                            <label for="min_rating" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php _e('Minimum Rating', 'mcqhome'); ?>
                            </label>
                            <select id="min_rating"
                                name="min_rating"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value=""><?php _e('Any Rating', 'mcqhome'); ?></option>
                                <option value="4" <?php selected(get_query_var('min_rating'), '4'); ?>><?php _e('4+ Stars', 'mcqhome'); ?></option>
                                <option value="3" <?php selected(get_query_var('min_rating'), '3'); ?>><?php _e('3+ Stars', 'mcqhome'); ?></option>
                                <option value="2" <?php selected(get_query_var('min_rating'), '2'); ?>><?php _e('2+ Stars', 'mcqhome'); ?></option>
                            </select>
                        </div>

                        <!-- Question Count Range (for MCQ Sets) -->
                        <div>
                            <label for="min_questions" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php _e('Min Questions', 'mcqhome'); ?>
                            </label>
                            <select id="min_questions"
                                name="min_questions"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value=""><?php _e('Any Amount', 'mcqhome'); ?></option>
                                <option value="5" <?php selected(get_query_var('min_questions'), '5'); ?>><?php _e('5+ Questions', 'mcqhome'); ?></option>
                                <option value="10" <?php selected(get_query_var('min_questions'), '10'); ?>><?php _e('10+ Questions', 'mcqhome'); ?></option>
                                <option value="20" <?php selected(get_query_var('min_questions'), '20'); ?>><?php _e('20+ Questions', 'mcqhome'); ?></option>
                                <option value="50" <?php selected(get_query_var('min_questions'), '50'); ?>><?php _e('50+ Questions', 'mcqhome'); ?></option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div>
                            <label for="date_range" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php _e('Published', 'mcqhome'); ?>
                            </label>
                            <select id="date_range"
                                name="date_range"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value=""><?php _e('Any Time', 'mcqhome'); ?></option>
                                <option value="week" <?php selected(get_query_var('date_range'), 'week'); ?>><?php _e('Past Week', 'mcqhome'); ?></option>
                                <option value="month" <?php selected(get_query_var('date_range'), 'month'); ?>><?php _e('Past Month', 'mcqhome'); ?></option>
                                <option value="year" <?php selected(get_query_var('date_range'), 'year'); ?>><?php _e('Past Year', 'mcqhome'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center justify-between gap-4 mt-6">
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors flex items-center">
                        <i class="fas fa-search mr-2"></i>
                        <?php _e('Search', 'mcqhome'); ?>
                    </button>
                    <a href="<?php echo get_permalink(); ?>" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400 transition-colors">
                        <?php _e('Clear All', 'mcqhome'); ?>
                    </a>
                </div>

                <!-- Quick Filter Tags -->
                <div class="flex flex-wrap gap-2">
                    <?php
                    $active_filters = [];
                    if (get_query_var('search')) $active_filters[] = __('Search', 'mcqhome');
                    if (get_query_var('subject')) $active_filters[] = __('Subject', 'mcqhome');
                    if (get_query_var('topic')) $active_filters[] = __('Topic', 'mcqhome');
                    if (get_query_var('difficulty')) $active_filters[] = __('Difficulty', 'mcqhome');
                    if (get_query_var('price')) $active_filters[] = __('Price', 'mcqhome');
                    if (get_query_var('institution')) $active_filters[] = __('Institution', 'mcqhome');
                    if (get_query_var('teacher')) $active_filters[] = __('Teacher', 'mcqhome');

                    if (!empty($active_filters)) :
                    ?>
                        <span class="text-sm text-gray-600"><?php _e('Active filters:', 'mcqhome'); ?></span>
                        <?php foreach ($active_filters as $filter) : ?>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?php echo esc_html($filter); ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Results -->
    <div class="browse-results">
        <?php
        // Build comprehensive query arguments
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;
        $search = get_query_var('search');
        $subject = get_query_var('subject');
        $topic = get_query_var('topic');
        $difficulty = get_query_var('difficulty');
        $price = get_query_var('price');
        $institution = get_query_var('institution');
        $teacher = get_query_var('teacher');
        $content_type = get_query_var('content_type');
        $min_rating = get_query_var('min_rating');
        $min_questions = get_query_var('min_questions');
        $date_range = get_query_var('date_range');
        $sort = get_query_var('sort', 'date');

        // Default to MCQ sets if no content type specified
        $post_types = $content_type ? [$content_type] : ['mcq_set'];

        $args = [
            'post_type' => $post_types,
            'posts_per_page' => 12,
            'paged' => $paged,
            'post_status' => 'publish'
        ];

        // Add search
        if ($search) {
            $args['s'] = $search;
        }

        // Add author filter
        if ($teacher) {
            $args['author'] = $teacher;
        }

        // Add taxonomy queries
        $tax_query = [];
        if ($subject) {
            $tax_query[] = [
                'taxonomy' => 'mcq_subject',
                'field' => 'slug',
                'terms' => $subject
            ];
        }

        if ($topic) {
            $tax_query[] = [
                'taxonomy' => 'mcq_topic',
                'field' => 'slug',
                'terms' => $topic
            ];
        }

        if ($difficulty) {
            $tax_query[] = [
                'taxonomy' => 'mcq_difficulty',
                'field' => 'slug',
                'terms' => $difficulty
            ];
        }

        if (!empty($tax_query)) {
            $tax_query['relation'] = 'AND';
            $args['tax_query'] = $tax_query;
        }

        // Add meta queries
        $meta_query = [];
        if ($price) {
            $meta_query[] = [
                'key' => '_pricing_type',
                'value' => $price,
                'compare' => '='
            ];
        }

        if ($institution) {
            $meta_query[] = [
                'key' => '_institution_id',
                'value' => $institution,
                'compare' => '='
            ];
        }

        if ($min_rating) {
            $meta_query[] = [
                'key' => '_average_rating',
                'value' => $min_rating,
                'compare' => '>=',
                'type' => 'DECIMAL'
            ];
        }

        if ($min_questions && in_array('mcq_set', $post_types)) {
            $meta_query[] = [
                'key' => '_question_count',
                'value' => $min_questions,
                'compare' => '>=',
                'type' => 'NUMERIC'
            ];
        }

        if (!empty($meta_query)) {
            $meta_query['relation'] = 'AND';
            $args['meta_query'] = $meta_query;
        }

        // Add date range filter
        if ($date_range) {
            $date_query = [];
            switch ($date_range) {
                case 'week':
                    $date_query = [
                        'after' => '1 week ago'
                    ];
                    break;
                case 'month':
                    $date_query = [
                        'after' => '1 month ago'
                    ];
                    break;
                case 'year':
                    $date_query = [
                        'after' => '1 year ago'
                    ];
                    break;
            }

            if (!empty($date_query)) {
                $args['date_query'] = [$date_query];
            }
        }

        // Add sorting
        switch ($sort) {
            case 'title':
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            case 'popular':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_view_count';
                $args['order'] = 'DESC';
                break;
            case 'rating':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_average_rating';
                $args['order'] = 'DESC';
                break;
            case 'questions_count':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_question_count';
                $args['order'] = 'DESC';
                break;
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
        }

        $browse_query = new WP_Query($args);

        if ($browse_query->have_posts()) :
        ?>
            <!-- Results Header -->
            <div class="results-header flex justify-between items-center mb-6">
                <div class="results-count">
                    <span class="text-gray-600">
                        <?php printf(
                            __('Showing %d of %d results', 'mcqhome'),
                            min($browse_query->post_count, $browse_query->found_posts),
                            $browse_query->found_posts
                        ); ?>
                    </span>
                </div>

                <div class="view-toggle">
                    <button class="view-grid-btn bg-blue-600 text-white px-3 py-2 rounded-l-md">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="view-list-btn bg-gray-300 text-gray-700 px-3 py-2 rounded-r-md">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>

            <!-- Results Grid -->
            <div class="results-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php while ($browse_query->have_posts()) : $browse_query->the_post(); ?>
                    <div class="content-card bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <!-- Card Header -->
                        <div class="card-header bg-gradient-to-r from-blue-500 to-purple-600 text-white p-4">
                            <h3 class="text-lg font-semibold mb-2">
                                <a href="<?php the_permalink(); ?>" class="hover:text-blue-200">
                                    <?php the_title(); ?>
                                </a>
                            </h3>
                            <div class="card-meta text-blue-100 text-sm">
                                <span><?php echo get_the_author(); ?></span>
                                <span class="mx-2">•</span>
                                <span><?php echo get_the_date(); ?></span>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="card-content p-4">
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                            </p>

                            <!-- Content Stats -->
                            <?php if (get_post_type() === 'mcq_set') : ?>
                                <div class="content-stats flex justify-between items-center mb-4 text-sm">
                                    <span class="text-gray-600">
                                        <i class="fas fa-question-circle mr-1"></i>
                                        <?php echo mcqhome_get_mcq_set_question_count(get_the_ID()); ?> <?php _e('questions', 'mcqhome'); ?>
                                    </span>

                                    <?php
                                    $rating = mcqhome_get_mcq_set_rating(get_the_ID());
                                    if ($rating > 0) :
                                    ?>
                                        <div class="rating flex items-center">
                                            <div class="stars flex mr-1">
                                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                    <i class="fas fa-star text-xs <?php echo $i <= $rating ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="text-gray-600"><?php echo number_format($rating, 1); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Tags -->
                            <div class="content-tags mb-4">
                                <?php
                                $subjects = get_the_terms(get_the_ID(), 'mcq_subject');
                                $difficulties = get_the_terms(get_the_ID(), 'mcq_difficulty');

                                if ($subjects && !is_wp_error($subjects)) :
                                    foreach (array_slice($subjects, 0, 2) as $subject) :
                                ?>
                                        <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mr-2 mb-1">
                                            <?php echo esc_html($subject->name); ?>
                                        </span>
                                    <?php
                                    endforeach;
                                endif;

                                if ($difficulties && !is_wp_error($difficulties)) :
                                    foreach ($difficulties as $difficulty) :
                                        $color_class = '';
                                        switch (strtolower($difficulty->slug)) {
                                            case 'easy':
                                                $color_class = 'bg-green-100 text-green-800';
                                                break;
                                            case 'medium':
                                                $color_class = 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'hard':
                                                $color_class = 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                $color_class = 'bg-gray-100 text-gray-800';
                                        }
                                    ?>
                                        <span class="inline-block <?php echo $color_class; ?> text-xs px-2 py-1 rounded-full mr-2 mb-1">
                                            <?php echo esc_html($difficulty->name); ?>
                                        </span>
                                <?php
                                    endforeach;
                                endif;
                                ?>
                            </div>

                            <!-- Actions -->
                            <div class="card-actions flex justify-between items-center">
                                <a href="<?php the_permalink(); ?>"
                                    class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition-colors">
                                    <?php echo get_post_type() === 'mcq_set' ? __('Take Assessment', 'mcqhome') : __('View Question', 'mcqhome'); ?>
                                </a>

                                <?php
                                $pricing_type = get_post_meta(get_the_ID(), '_pricing_type', true);
                                if ($pricing_type === 'paid') :
                                    $price = get_post_meta(get_the_ID(), '_price', true);
                                ?>
                                    <span class="text-green-600 font-semibold text-sm">
                                        $<?php echo number_format($price, 2); ?>
                                    </span>
                                <?php else : ?>
                                    <span class="text-green-600 font-semibold text-sm">
                                        <?php _e('Free', 'mcqhome'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper">
                <?php
                echo paginate_links([
                    'total' => $browse_query->max_num_pages,
                    'current' => $paged,
                    'format' => '?paged=%#%',
                    'prev_text' => '<i class="fas fa-chevron-left"></i> ' . __('Previous', 'mcqhome'),
                    'next_text' => __('Next', 'mcqhome') . ' <i class="fas fa-chevron-right"></i>',
                    'class' => 'flex justify-center space-x-2'
                ]);
                ?>
            </div>

        <?php
            wp_reset_postdata();
        else :
        ?>
            <div class="no-results text-center py-12">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-search text-6xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2"><?php _e('No results found', 'mcqhome'); ?></h3>
                <p class="text-gray-600 mb-4"><?php _e('Try adjusting your search criteria or browse by category.', 'mcqhome'); ?></p>
                <a href="<?php echo get_permalink(); ?>" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    <?php _e('Clear Filters', 'mcqhome'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>