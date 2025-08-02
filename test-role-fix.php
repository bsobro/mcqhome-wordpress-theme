<?php
/**
 * Test script to verify role creation fixes
 * Run this after the fixes to ensure everything works
 */

// Include WordPress
if (file_exists('wp-config.php')) {
    require_once('wp-config.php');
} else {
    echo "WordPress not found\n";
    exit;
}

echo "=== MCQHome Role Creation Test ===\n\n";

// Test 1: Check if role creation functions exist
echo "1. Checking function existence:\n";
$functions = [
    'mcqhome_create_student_role',
    'mcqhome_create_teacher_role', 
    'mcqhome_create_institution_role',
    'mcqhome_activate_user_roles'
];

foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "   ✓ $func exists\n";
    } else {
        echo "   ✗ $func missing\n";
    }
}

// Test 2: Check current role status
echo "\n2. Current role status:\n";
$roles = ['student', 'teacher', 'institution'];
foreach ($roles as $role_name) {
    $role = get_role($role_name);
    if ($role) {
        echo "   ✓ $role_name role exists\n";
    } else {
        echo "   ✗ $role_name role missing\n";
    }
}

// Test 3: Test safe role creation (should not cause errors)
echo "\n3. Testing safe role creation:\n";
try {
    if (function_exists('mcqhome_create_student_role')) {
        mcqhome_create_student_role();
        echo "   ✓ Student role creation - no errors\n";
    }
    
    if (function_exists('mcqhome_create_teacher_role')) {
        mcqhome_create_teacher_role();
        echo "   ✓ Teacher role creation - no errors\n";
    }
    
    if (function_exists('mcqhome_create_institution_role')) {
        mcqhome_create_institution_role();
        echo "   ✓ Institution role creation - no errors\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error during role creation: " . $e->getMessage() . "\n";
}

// Test 4: Test activation function
echo "\n4. Testing activation function:\n";
try {
    if (function_exists('mcqhome_activate_user_roles')) {
        mcqhome_activate_user_roles();
        echo "   ✓ Activation function - no errors\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error during activation: " . $e->getMessage() . "\n";
}

// Test 5: Final role verification
echo "\n5. Final role verification:\n";
foreach ($roles as $role_name) {
    $role = get_role($role_name);
    if ($role) {
        echo "   ✓ $role_name role confirmed\n";
    } else {
        echo "   ✗ $role_name role still missing\n";
    }
}

echo "\n=== Test Complete ===\n";
echo "If all tests show ✓, the role creation fixes are working correctly.\n";