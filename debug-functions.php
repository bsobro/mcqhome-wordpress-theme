<?php
/**
 * Debug file to test functions
 */

// Include WordPress
if (file_exists('wp-config.php')) {
    require_once('wp-config.php');
} else {
    echo "WordPress not found\n";
    exit;
}

echo "Testing functions...\n";

// Test if functions exist
if (function_exists('mcqhome_get_institution_stats')) {
    echo "✓ mcqhome_get_institution_stats exists\n";
    
    try {
        $stats = mcqhome_get_institution_stats(1);
        echo "✓ Function executed successfully\n";
        echo "Stats: " . print_r($stats, true) . "\n";
    } catch (Exception $e) {
        echo "✗ Function error: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ mcqhome_get_institution_stats missing\n";
}

if (function_exists('mcqhome_get_teacher_stats')) {
    echo "✓ mcqhome_get_teacher_stats exists\n";
    
    try {
        $stats = mcqhome_get_teacher_stats(1);
        echo "✓ Function executed successfully\n";
        echo "Stats: " . print_r($stats, true) . "\n";
    } catch (Exception $e) {
        echo "✗ Function error: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ mcqhome_get_teacher_stats missing\n";
}

// Test role creation functions
echo "\n=== Testing Role Creation ===\n";

if (function_exists('mcqhome_create_student_role')) {
    echo "✓ mcqhome_create_student_role exists\n";
    
    try {
        mcqhome_create_student_role();
        $role = get_role('student');
        if ($role) {
            echo "✓ Student role created/exists\n";
        } else {
            echo "✗ Student role not found after creation\n";
        }
    } catch (Exception $e) {
        echo "✗ Student role creation error: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ mcqhome_create_student_role missing\n";
}

if (function_exists('mcqhome_create_teacher_role')) {
    echo "✓ mcqhome_create_teacher_role exists\n";
    
    try {
        mcqhome_create_teacher_role();
        $role = get_role('teacher');
        if ($role) {
            echo "✓ Teacher role created/exists\n";
        } else {
            echo "✗ Teacher role not found after creation\n";
        }
    } catch (Exception $e) {
        echo "✗ Teacher role creation error: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ mcqhome_create_teacher_role missing\n";
}

if (function_exists('mcqhome_create_institution_role')) {
    echo "✓ mcqhome_create_institution_role exists\n";
    
    try {
        mcqhome_create_institution_role();
        $role = get_role('institution');
        if ($role) {
            echo "✓ Institution role created/exists\n";
        } else {
            echo "✗ Institution role not found after creation\n";
        }
    } catch (Exception $e) {
        echo "✗ Institution role creation error: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ mcqhome_create_institution_role missing\n";
}

// Test activation function
if (function_exists('mcqhome_activate_user_roles')) {
    echo "✓ mcqhome_activate_user_roles exists\n";
    
    try {
        mcqhome_activate_user_roles();
        echo "✓ Activation function executed successfully\n";
    } catch (Exception $e) {
        echo "✗ Activation function error: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ mcqhome_activate_user_roles missing\n";
}

echo "Test complete.\n";