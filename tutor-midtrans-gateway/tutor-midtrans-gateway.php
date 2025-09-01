<?php
/**
 * Plugin Name: Tutor LMS Midtrans Snap Gateway
 * Plugin URI: https://github.com/your-username/tutor-midtrans-gateway
 * Description: Custom payment gateway for Tutor LMS using Midtrans Snap API
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yoursite.com
 * Text Domain: tutor-midtrans
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package TutorMidtransGateway
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TUTOR_MIDTRANS_VERSION', '1.0.0');
define('TUTOR_MIDTRANS_PLUGIN_FILE', __FILE__);
define('TUTOR_MIDTRANS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TUTOR_MIDTRANS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TUTOR_MIDTRANS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'TutorMidtransGateway\\';
    $base_dir = TUTOR_MIDTRANS_PLUGIN_DIR . 'src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize plugin
add_action('plugins_loaded', function() {
    if (!class_exists('TUTOR_LMS')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . 
                 __('Tutor LMS Midtrans Gateway requires Tutor LMS plugin to be installed and activated.', 'tutor-midtrans') . 
                 '</p></div>';
        });
        return;
    }

    // Initialize the gateway
    new \TutorMidtransGateway\Gateway();
    
    // Initialize settings
    new \TutorMidtransGateway\Settings();
    
    // Initialize assets
    new \TutorMidtransGateway\Assets();
    
    // Initialize notification handler
    new \TutorMidtransGateway\Notification();
});

// Activation hook
register_activation_hook(__FILE__, function() {
    // Set default options
    $default_options = array(
        'enabled' => 'no',
        'environment' => 'sandbox',
        'checkout_style' => 'redirect',
        'payment_channels' => array('credit_card', 'gopay', 'shopeepay', 'bca_va', 'bni_va', 'bri_va', 'permata_va', 'echannel'),
        'sandbox_server_key' => '',
        'sandbox_client_key' => '',
        'production_server_key' => '',
        'production_client_key' => ''
    );
    
    add_option('tutor_midtrans_settings', $default_options);
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Clean up if needed
});

// Uninstall hook
register_uninstall_hook(__FILE__, function() {
    delete_option('tutor_midtrans_settings');
});
