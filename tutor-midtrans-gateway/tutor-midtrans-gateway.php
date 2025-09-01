<?php
/**
 * Plugin Name: Tutor LMS Midtrans Snap Gateway
 * Plugin URI: https://github.com/your-username/tutor-midtrans-gateway
 * Description: Custom payment gateway for Tutor LMS using Midtrans Snap API
 * Version: 1.0.0
 * Author: LGH Khun
 * Author URI: https://lghkhun.com
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

error_log('Tutor Midtrans Gateway: Plugin file loaded');

// Define plugin constants with conflict check
if (!defined('TUTOR_MIDTRANS_VERSION')) {
    define('TUTOR_MIDTRANS_VERSION', '1.0.0');
}
if (!defined('TUTOR_MIDTRANS_PLUGIN_FILE')) {
    define('TUTOR_MIDTRANS_PLUGIN_FILE', __FILE__);
}
if (!defined('TUTOR_MIDTRANS_PLUGIN_DIR')) {
    define('TUTOR_MIDTRANS_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('TUTOR_MIDTRANS_PLUGIN_URL')) {
    define('TUTOR_MIDTRANS_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('TUTOR_MIDTRANS_PLUGIN_BASENAME')) {
    define('TUTOR_MIDTRANS_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

error_log('Tutor Midtrans Gateway: Constants defined');

// Autoloader with conflict check
$midtrans_autoloader = function ($class) {
    $prefix = 'TutorMidtransGateway\\';
    $base_dir = TUTOR_MIDTRANS_PLUGIN_DIR . 'src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        error_log('Tutor Midtrans Gateway: Loading class file: ' . $file);
        require_once $file;
    } else {
        error_log('Tutor Midtrans Gateway: Class file not found: ' . $file);
    }
};

// Check if autoloader already registered
$registered_autoloaders = spl_autoload_functions();
$autoloader_already_registered = false;

if ($registered_autoloaders) {
    foreach ($registered_autoloaders as $autoloader) {
        if (is_array($autoloader) && isset($autoloader[0]) && is_object($autoloader[0])) {
            // Check if it's our autoloader by comparing closure
            if ($autoloader[0] instanceof Closure) {
                $autoloader_already_registered = true;
                break;
            }
        }
    }
}

if (!$autoloader_already_registered) {
    spl_autoload_register($midtrans_autoloader);
    error_log('Tutor Midtrans Gateway: Autoloader registered');
} else {
    error_log('Tutor Midtrans Gateway: Autoloader already registered, skipping');
}

error_log('Tutor Midtrans Gateway: Autoloader registered');

// Initialize plugin
add_action('plugins_loaded', function() {
    error_log('Tutor Midtrans Gateway: plugins_loaded action triggered');

    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        error_log('Tutor Midtrans Gateway: PHP version too low: ' . PHP_VERSION);
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' .
                 __('Tutor LMS Midtrans Gateway requires PHP 7.4 or higher. Current version: ', 'tutor-midtrans') . PHP_VERSION .
                 '</p></div>';
        });
        return;
    }

    error_log('Tutor Midtrans Gateway: PHP version check passed');

    // Comprehensive Tutor LMS dependency check
    if (!class_exists('TUTOR_LMS')) {
        error_log('Tutor Midtrans Gateway: TUTOR_LMS class not found');
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' .
                 __('Tutor LMS Midtrans Gateway requires Tutor LMS plugin to be installed and activated.', 'tutor-midtrans') .
                 '</p></div>';
        });
        return;
    }

    error_log('Tutor Midtrans Gateway: TUTOR_LMS class found');

    // Check for Tutor_Payment_Base class
    if (!class_exists('Tutor_Payment_Base')) {
        error_log('Tutor Midtrans Gateway: Tutor_Payment_Base class not found');
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' .
                 __('Tutor LMS Midtrans Gateway requires a compatible version of Tutor LMS with payment gateway support.', 'tutor-midtrans') .
                 '</p></div>';
        });
        return;
    }

    error_log('Tutor Midtrans Gateway: Tutor_Payment_Base class found');

    // Check for tutor_utils function
    if (!function_exists('tutor_utils')) {
        error_log('Tutor Midtrans Gateway: tutor_utils function not found');
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' .
                 __('Tutor LMS Midtrans Gateway requires Tutor LMS utilities to be available.', 'tutor-midtrans') .
                 '</p></div>';
        });
        return;
    }

    error_log('Tutor Midtrans Gateway: tutor_utils function found');
    error_log('Tutor Midtrans Gateway: All dependencies found, initializing plugin');

    try {
        // Check if classes are already instantiated to prevent conflicts
        global $tutor_midtrans_instances;

        if (!isset($tutor_midtrans_instances)) {
            $tutor_midtrans_instances = array();
        }

        // Initialize the gateway
        if (!isset($tutor_midtrans_instances['gateway'])) {
            error_log('Tutor Midtrans Gateway: Attempting to instantiate Gateway class');
            $tutor_midtrans_instances['gateway'] = new \TutorMidtransGateway\Gateway();
            error_log('Tutor Midtrans Gateway: Gateway initialized successfully');
        } else {
            error_log('Tutor Midtrans Gateway: Gateway already instantiated, skipping');
        }

        // Initialize settings
        if (!isset($tutor_midtrans_instances['settings'])) {
            error_log('Tutor Midtrans Gateway: Attempting to instantiate Settings class');
            $tutor_midtrans_instances['settings'] = new \TutorMidtransGateway\Settings();
            error_log('Tutor Midtrans Gateway: Settings initialized successfully');
        } else {
            error_log('Tutor Midtrans Gateway: Settings already instantiated, skipping');
        }

        // Initialize assets
        if (!isset($tutor_midtrans_instances['assets'])) {
            error_log('Tutor Midtrans Gateway: Attempting to instantiate Assets class');
            $tutor_midtrans_instances['assets'] = new \TutorMidtransGateway\Assets();
            error_log('Tutor Midtrans Gateway: Assets initialized successfully');
        } else {
            error_log('Tutor Midtrans Gateway: Assets already instantiated, skipping');
        }

        // Initialize notification handler
        if (!isset($tutor_midtrans_instances['notification'])) {
            error_log('Tutor Midtrans Gateway: Attempting to instantiate Notification class');
            $tutor_midtrans_instances['notification'] = new \TutorMidtransGateway\Notification();
            error_log('Tutor Midtrans Gateway: Notification handler initialized successfully');
        } else {
            error_log('Tutor Midtrans Gateway: Notification already instantiated, skipping');
        }

    } catch (Exception $e) {
        error_log('Tutor Midtrans Gateway initialization error: ' . $e->getMessage());
        error_log('Tutor Midtrans Gateway stack trace: ' . $e->getTraceAsString());
        add_action('admin_notices', function() use ($e) {
            echo '<div class="notice notice-error"><p>' .
                 __('Tutor LMS Midtrans Gateway failed to initialize: ', 'tutor-midtrans') . esc_html($e->getMessage()) .
                 '</p></div>';
        });
    } catch (Error $e) {
        error_log('Tutor Midtrans Gateway fatal error: ' . $e->getMessage());
        error_log('Tutor Midtrans Gateway error file: ' . $e->getFile() . ' line: ' . $e->getLine());
        add_action('admin_notices', function() use ($e) {
            echo '<div class="notice notice-error"><p>' .
                 __('Tutor LMS Midtrans Gateway encountered a fatal error: ', 'tutor-midtrans') . esc_html($e->getMessage()) .
                 '</p></div>';
        });
    }
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
