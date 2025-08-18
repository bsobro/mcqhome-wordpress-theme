<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 *
 * @package MCQHome
 * @since 1.0.0
 */

get_header(); ?>

<main id="primary" class="site-main">
    <div class="container mx-auto px-4 py-8">
        <?php if (have_posts()) : ?>
            <div class="posts-grid grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-lg shadow-md overflow-hidden'); ?>>
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="post-thumbnail">
                                <?php the_post_thumbnail('medium', ['class' => 'w-full h-48 object-cover']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <header class="entry-header mb-4">
                                <?php the_title('<h2 class="entry-title text-xl font-semibold mb-2"><a href="' . esc_url(get_permalink()) . '" class="text-gray-900 hover:text-blue-600">', '</a></h2>'); ?>
                            </header>

                            <div class="entry-content text-gray-600">
                                <?php the_excerpt(); ?>
                            </div>

                            <footer class="entry-footer mt-4">
                                <a href="<?php echo esc_url(get_permalink()); ?>" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                                    <?php _e('Read More', 'mcqhome'); ?>
                                </a>
                            </footer>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php the_posts_navigation([
                'prev_text' => __('Previous Posts', 'mcqhome'),
                'next_text' => __('Next Posts', 'mcqhome'),
                'class' => 'mt-8'
            ]); ?>

        <?php else : ?>
            <div class="no-posts text-center py-12">
                <h1 class="text-2xl font-semibold mb-4"><?php _e('Nothing Found', 'mcqhome'); ?></h1>
                <p class="text-gray-600"><?php _e('It looks like nothing was found at this location. Maybe try a search?', 'mcqhome'); ?></p>
                <?php get_search_form(); ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
get_sidebar();
get_footer();