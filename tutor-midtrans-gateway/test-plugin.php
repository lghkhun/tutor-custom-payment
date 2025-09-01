<?php
/**
 * Simple test script to check plugin compatibility
 */

// Define WordPress constants for testing
define('ABSPATH', __DIR__ . '/');
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Mock WordPress functions
if (!function_exists('add_action')) {
    function add_action($hook, $callback) {
        echo "Registered action: $hook\n";
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback) {
        echo "Registered filter: $hook\n";
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action) {
        return true; // Mock as valid
    }
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action) {
        return 'test_nonce';
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '') {
        return 'http://localhost/wp-admin/' . $path;
    }
}

if (!function_exists('wp_localize_script')) {
    function wp_localize_script($handle, $name, $data) {
        echo "Localized script: $handle with $name\n";
    }
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) {
        echo "Enqueued script: $handle\n";
    }
}

if (!function_exists('wp_enqueue_scripts')) {
    function wp_enqueue_scripts() {
        // Mock hook
    }
}

if (!function_exists('wp_head')) {
    function wp_head() {
        // Mock hook
    }
}

if (!function_exists('get_option')) {
    function get_option($key, $default = null) {
        return $default;
    }
}

if (!function_exists('add_option')) {
    function add_option($key, $value) {
        echo "Added option: $key\n";
    }
}

if (!function_exists('update_option')) {
    function update_option($key, $value) {
        echo "Updated option: $key\n";
    }
}

if (!function_exists('delete_option')) {
    function delete_option($key) {
        echo "Deleted option: $key\n";
    }
}

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $callback) {
        echo "Registered activation hook for: $file\n";
    }
}

if (!function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($file, $callback) {
        echo "Registered deactivation hook for: $file\n";
    }
}

if (!function_exists('register_uninstall_hook')) {
    function register_uninstall_hook($file, $callback) {
        echo "Registered uninstall hook for: $file\n";
    }
}

if (!function_exists('__')) {
    function __($text, $domain = '') {
        return $text;
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text);
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text);
    }
}

if (!function_exists('esc_js')) {
    function esc_js($text) {
        return $text;
    }
}

if (!function_exists('wp_die')) {
    function wp_die($message) {
        die($message);
    }
}

if (!function_exists('wp_remote_post')) {
    function wp_remote_post($url, $args) {
        return array('response' => array('code' => 200), 'body' => '{"token":"test_token","redirect_url":"test_url"}');
    }
}

if (!function_exists('wp_remote_retrieve_response_code')) {
    function wp_remote_retrieve_response_code($response) {
        return 200;
    }
}

if (!function_exists('wp_remote_retrieve_body')) {
    function wp_remote_retrieve_body($response) {
        return '{"token":"test_token","redirect_url":"test_url"}';
    }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) {
        return false;
    }
}

if (!function_exists('json_decode')) {
    function json_decode($json, $assoc = false) {
        return json_decode($json, $assoc);
    }
}

if (!function_exists('json_last_error')) {
    function json_last_error() {
        return JSON_ERROR_NONE;
    }
}

if (!function_exists('set_transient')) {
    function set_transient($key, $value, $expiration = 0) {
        echo "Set transient: $key\n";
    }
}

if (!function_exists('get_transient')) {
    function get_transient($key) {
        return null;
    }
}

if (!function_exists('delete_transient')) {
    function delete_transient($key) {
        echo "Deleted transient: $key\n";
    }
}

if (!function_exists('wp_get_current_user')) {
    function wp_get_current_user() {
        return (object) array('ID' => 1, 'user_email' => 'test@example.com', 'first_name' => 'Test', 'last_name' => 'User', 'display_name' => 'Test User');
    }
}

if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() {
        return true;
    }
}

if (!function_exists('get_post')) {
    function get_post($id) {
        return (object) array('ID' => $id, 'post_type' => 'courses', 'post_title' => 'Test Course');
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return true;
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return $str;
    }
}

if (!function_exists('wp_parse_args')) {
    function wp_parse_args($args, $defaults) {
        return array_merge($defaults, $args);
    }
}

if (!function_exists('current_time')) {
    function current_time($type) {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('get_userdata')) {
    function get_userdata($id) {
        return (object) array('ID' => $id, 'user_email' => 'test@example.com', 'display_name' => 'Test User');
    }
}

if (!function_exists('wp_mail')) {
    function wp_mail($to, $subject, $message, $headers = '') {
        echo "Sent email to: $to\n";
    }
}

if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show) {
        return 'Test Site';
    }
}

if (!function_exists('http_response_code')) {
    function http_response_code($code = null) {
        if ($code !== null) {
            echo "HTTP Response Code: $code\n";
        }
    }
}

if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data = null) {
        echo "JSON Success: " . json_encode($data) . "\n";
        exit;
    }
}

if (!function_exists('wp_send_json_error')) {
    function wp_send_json_error($data = null) {
        echo "JSON Error: " . json_encode($data) . "\n";
        exit;
    }
}

if (!function_exists('error_log')) {
    function error_log($message) {
        echo "[ERROR] $message\n";
    }
}

// Define plugin constants
define('TUTOR_MIDTRANS_VERSION', '1.0.0');
define('TUTOR_MIDTRANS_PLUGIN_FILE', __FILE__);
define('TUTOR_MIDTRANS_PLUGIN_DIR', __DIR__ . '/');
define('TUTOR_MIDTRANS_PLUGIN_URL', 'http://localhost/wp-content/plugins/tutor-midtrans-gateway/');
define('TUTOR_MIDTRANS_PLUGIN_BASENAME', 'tutor-midtrans-gateway/tutor-midtrans-gateway.php');

// Test different scenarios
echo "=== Testing Plugin Loading ===\n";

// Scenario 1: No Tutor LMS
echo "\n1. Testing without Tutor LMS:\n";
if (!class_exists('TUTOR_LMS')) {
    echo "TUTOR_LMS class not found - this should show admin notice\n";
}

// Scenario 2: With mock Tutor LMS but no Tutor_Payment_Base
echo "\n2. Testing with TUTOR_LMS but no Tutor_Payment_Base:\n";
class TUTOR_LMS {}
if (class_exists('TUTOR_LMS')) {
    echo "TUTOR_LMS class found\n";
}
if (!class_exists('Tutor_Payment_Base')) {
    echo "Tutor_Payment_Base class not found - this should show admin notice\n";
}

// Scenario 3: With mock Tutor_Payment_Base but no tutor_utils
echo "\n3. Testing with Tutor_Payment_Base but no tutor_utils:\n";
class Tutor_Payment_Base {}
if (class_exists('Tutor_Payment_Base')) {
    echo "Tutor_Payment_Base class found\n";
}
if (!function_exists('tutor_utils')) {
    echo "tutor_utils function not found - this should show admin notice\n";
}

// Scenario 4: With all dependencies (mock)
echo "\n4. Testing with all dependencies (mock):\n";
function tutor_utils() {
    return (object) array(
        'get_course_price' => function($id) { return 100000; },
        'is_course_enrolled_by_user' => function($course_id, $user_id) { return false; },
        'do_enroll' => function($course_id, $enrollment_data) { return 123; }
    );
}

if (function_exists('tutor_utils')) {
    echo "tutor_utils function found\n";
}

// Now include the main plugin file
echo "\n5. Loading main plugin file:\n";
include_once 'tutor-midtrans-gateway.php';

// Trigger plugins_loaded
echo "\n6. Triggering plugins_loaded action:\n";
do_action('plugins_loaded');

echo "\n=== Test Complete ===\n";
?>