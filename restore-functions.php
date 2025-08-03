<?php
/**
 * Script to gradually restore functions.php functionality
 * Run this after the minimal version works
 */

// Load WordPress
require_once dirname(__FILE__) . '/wp-load.php';

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

echo '<h1>MCQHome Functions Restoration</h1>';

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;

echo "<p>Current step: $step</p>";

switch ($step) {
    case 1:
        echo '<h2>Step 1: Add basic includes</h2>';
        $content = file_get_contents('functions-minimal.php');
        $content .= "\n\n// Include template functions\n";
        $content .= "require_once MCQHOME_THEME_DIR . '/inc/template-functions.php';\n";
        $content .= "require_once MCQHOME_THEME_DIR . '/inc/customizer.php';\n";
        
        file_put_contents('functions.php', $content);
        echo '<p style="color: green;">✓ Added basic includes</p>';
        echo '<p><a href="?step=2">Next Step →</a></p>';
        break;
        
    case 2:
        echo '<h2>Step 2: Add post types</h2>';
        $content = file_get_contents('functions.php');
        $content .= "\n\n// Include post types\n";
        $content .= "if (file_exists(MCQHOME_THEME_DIR . '/inc/post-types.php')) {\n";
        $content .= "    require_once MCQHOME_THEME_DIR . '/inc/post-types.php';\n";
        $content .= "}\n";
        
        file_put_contents('functions.php', $content);
        echo '<p style="color: green;">✓ Added post types</p>';
        echo '<p><a href="?step=3">Next Step →</a></p>';
        break;
        
    case 3:
        echo '<h2>Step 3: Add user roles</h2>';
        $content = file_get_contents('functions.php');
        $content .= "\n\n// Include user roles\n";
        $content .= "if (file_exists(MCQHOME_THEME_DIR . '/inc/user-roles.php')) {\n";
        $content .= "    require_once MCQHOME_THEME_DIR . '/inc/user-roles.php';\n";
        $content .= "}\n";
        
        file_put_contents('functions.php', $content);
        echo '<p style="color: green;">✓ Added user roles</p>';
        echo '<p><a href="?step=4">Next Step →</a></p>';
        break;
        
    case 4:
        echo '<h2>Step 4: Add database setup</h2>';
        $content = file_get_contents('functions.php');
        $content .= "\n\n// Include database setup\n";
        $content .= "if (file_exists(MCQHOME_THEME_DIR . '/inc/database-setup.php')) {\n";
        $content .= "    require_once MCQHOME_THEME_DIR . '/inc/database-setup.php';\n";
        $content .= "}\n";
        
        file_put_contents('functions.php', $content);
        echo '<p style="color: green;">✓ Added database setup</p>';
        echo '<p><a href="?step=5">Next Step →</a></p>';
        break;
        
    case 5:
        echo '<h2>Step 5: Restore full functionality</h2>';
        copy('functions-backup.php', 'functions.php');
        echo '<p style="color: green;">✓ Restored full functions.php</p>';
        echo '<p>Full functionality restored. Test the theme now.</p>';
        break;
        
    default:
        echo '<p>Invalid step</p>';
}

echo '<p><a href="' . admin_url() . '">← Back to Admin</a></p>';
?>