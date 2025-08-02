<?php
/**
 * The header for our theme
 *
 * @package MCQHome
 * @since 1.0.0
 */
?>
<!doctype html>
<html <?php language_attributes(); ?> <?php echo mcqhome_get_html_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?> <?php echo mcqhome_get_body_attributes(); ?>>
<?php wp_body_open(); ?>

<?php mcqhome_semantic_skip_links(); ?>

<div id="page" class="site min-h-screen flex flex-col" itemscope itemtype="https://schema.org/WebPage">
    
    <?php mcqhome_semantic_header(); ?>
    
    <div class="site-content flex-1">
        <?php mcqhome_semantic_breadcrumbs(); ?>