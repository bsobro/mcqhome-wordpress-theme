<?php
/**
 * The sidebar containing the main widget area
 *
 * @package MCQHome
 * @since 1.0.0
 */

if (!is_active_sidebar('sidebar-1')) {
    return;
}
?>

<aside id="secondary" class="widget-area lg:w-1/3 lg:pl-8">
    <div class="sticky top-8">
        <?php dynamic_sidebar('sidebar-1'); ?>
    </div>
</aside>