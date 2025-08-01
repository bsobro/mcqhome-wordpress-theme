<?php
/**
 * Template for displaying institutions browse page
 *
 * @package MCQHome
 * @since 1.0.0
 */

get_header(); ?>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="page-header mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php _e('Browse Institutions', 'mcqhome'); ?></h1>
        <p class="text-gray-600"><?php _e('Discover educational institutions and their MCQ content.', 'mcqhome'); ?></p>
    </div>
    
    <!-- Search and Filters -->
    <div class="search-filters bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" class="institution-search-form">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search Input -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Search Institutions', 'mcqhome'); ?>
                    </label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="<?php echo esc_attr(get_query_var('search', '')); ?>"
                           placeholder="<?php _e('Enter institution name...', 'mcqhome'); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- Institution Type Filter -->
                <div>
                    <label for="institution_type" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Type', 'mcqhome'); ?>
                    </label>
                    <select id="institution_type" 
                            name="institution_type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value=""><?php _e('All Types', 'mcqhome'); ?></option>
                        <option value="university" <?php selected(get_query_var('institution_type'), 'university'); ?>><?php _e('University', 'mcqhome'); ?></option>
                        <option value="college" <?php selected(get_query_var('institution_type'), 'college'); ?>><?php _e('College', 'mcqhome'); ?></option>
                        <option value="school" <?php selected(get_query_var('institution_type'), 'school'); ?>><?php _e('School', 'mcqhome'); ?></option>
                        <option value="training_center" <?php selected(get_query_var('institution_type'), 'training_center'); ?>><?php _e('Training Center', 'mcqhome'); ?></option>
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
                        <option value="name" <?php selected(get_query_var('sort'), 'name'); ?>><?php _e('Name', 'mcqhome'); ?></option>
                        <option value="teachers" <?php selected(get_query_var('sort'), 'teachers'); ?>><?php _e('Most Teachers', 'mcqhome'); ?></option>
                        <option value="content" <?php selected(get_query_var('sort'), 'content'); ?>><?php _e('Most Content', 'mcqhome'); ?></option>
                        <option value="followers" <?php selected(get_query_var('sort'), 'followers'); ?>><?php _e('Most Followers', 'mcqhome'); ?></option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4 flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>
                    <?php _e('Search', 'mcqhome'); ?>
                </button>
                <a href="<?php echo get_permalink(); ?>" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400 transition-colors">
                    <?php _e('Clear', 'mcqhome'); ?>
                </a>
            </div>
        </form>
    </div>
    
    <!-- Institutions Grid -->
    <div class="institutions-grid">
        <?php
        // Build query arguments
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;
        $search = get_query_var('search');
        $institution_type = get_query_var('institution_type');
        $sort = get_query_var('sort', 'name');
        
        $args = [
            'post_type' => 'institution',
            'posts_per_page' => 12,
            'paged' => $paged,
            'post_status' => 'publish'
        ];
        
        // Add search
        if ($search) {
            $args['s'] = $search;
        }
        
        // Add meta query for institution type
        if ($institution_type) {
            $args['meta_query'] = [
                [
                    'key' => '_institution_type',
                    'value' => $institution_type,
                    'compare' => '='
                ]
            ];
        }
        
        // Add sorting
        switch ($sort) {
            case 'name':
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            case 'teachers':
            case 'content':
            case 'followers':
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }
        
        $institutions_query = new WP_Query($args);
        
        if ($institutions_query->have_posts()) :
        ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php while ($institutions_query->have_posts()) : $institutions_query->the_post(); ?>
                    <div class="institution-card bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <!-- Institution Image -->
                        <div class="institution-image h-48 bg-gradient-to-br from-blue-500 to-blue-700 relative">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="absolute inset-0">
                                    <?php the_post_thumbnail('medium', ['class' => 'w-full h-full object-cover']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-black bg-opacity-20"></div>
                            <div class="absolute bottom-4 left-4 right-4">
                                <h3 class="text-white text-xl font-bold mb-1">
                                    <a href="<?php the_permalink(); ?>" class="hover:text-blue-200">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                                <?php 
                                $institution_type = get_post_meta(get_the_ID(), '_institution_type', true);
                                $location = get_post_meta(get_the_ID(), '_institution_location', true);
                                ?>
                                <div class="text-blue-100 text-sm">
                                    <?php if ($institution_type) : ?>
                                        <span><?php echo esc_html(ucfirst($institution_type)); ?></span>
                                    <?php endif; ?>
                                    <?php if ($location) : ?>
                                        <span class="mx-2">•</span>
                                        <span><?php echo esc_html($location); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Institution Content -->
                        <div class="p-6">
                            <!-- Description -->
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                            </p>
                            
                            <!-- Stats -->
                            <div class="institution-stats grid grid-cols-3 gap-2 mb-4 text-center">
                                <?php $stats = mcqhome_get_institution_stats(get_the_ID()); ?>
                                <div class="stat-item">
                                    <div class="stat-number text-lg font-bold text-blue-600"><?php echo $stats['teachers']; ?></div>
                                    <div class="stat-label text-xs text-gray-500"><?php _e('Teachers', 'mcqhome'); ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number text-lg font-bold text-green-600"><?php echo $stats['mcq_sets']; ?></div>
                                    <div class="stat-label text-xs text-gray-500"><?php _e('MCQ Sets', 'mcqhome'); ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number text-lg font-bold text-orange-600"><?php echo $stats['followers']; ?></div>
                                    <div class="stat-label text-xs text-gray-500"><?php _e('Followers', 'mcqhome'); ?></div>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex justify-between items-center">
                                <a href="<?php the_permalink(); ?>" 
                                   class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition-colors">
                                    <?php _e('View Profile', 'mcqhome'); ?>
                                </a>
                                
                                <?php if (is_user_logged_in() && mcqhome_get_user_role() === 'student') : ?>
                                    <button class="follow-institution-btn text-blue-600 hover:text-blue-800 text-sm" 
                                            data-institution-id="<?php echo get_the_ID(); ?>">
                                        <i class="fas fa-plus mr-1"></i>
                                        <?php _e('Follow', 'mcqhome'); ?>
                                    </button>
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
                    'total' => $institutions_query->max_num_pages,
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
                    <i class="fas fa-university text-6xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2"><?php _e('No institutions found', 'mcqhome'); ?></h3>
                <p class="text-gray-600 mb-4"><?php _e('Try adjusting your search criteria or browse all institutions.', 'mcqhome'); ?></p>
                <a href="<?php echo get_permalink(); ?>" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    <?php _e('View All Institutions', 'mcqhome'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>