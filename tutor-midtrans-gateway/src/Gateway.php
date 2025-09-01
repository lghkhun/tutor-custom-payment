<?php
namespace TutorMidtransGateway;

use Tutor_Payment_Base;

/**
 * Midtrans Snap Gateway for Tutor LMS
 */
class Gateway extends Tutor_Payment_Base {

    /**
     * Constructor
     */
    public function __construct() {
        error_log('Tutor Midtrans Gateway: Gateway constructor called');

        try {
            parent::__construct();
            error_log('Tutor Midtrans Gateway: Parent constructor called successfully');
        } catch (Exception $e) {
            error_log('Tutor Midtrans Gateway: Parent constructor error: ' . $e->getMessage());
            throw $e;
        } catch (Error $e) {
            error_log('Tutor Midtrans Gateway: Parent constructor fatal error: ' . $e->getMessage());
            throw $e;
        }

        // Register AJAX hooks with unique prefix
        add_action('wp_ajax_tutor_midtrans_create_snap', array($this, 'create_snap'));
        add_action('wp_ajax_nopriv_tutor_midtrans_create_snap', array($this, 'create_snap'));

        // Add gateway to Tutor LMS
        add_filter('tutor_payment_gateways', array($this, 'register_gateway'));

        error_log('Tutor Midtrans Gateway: Gateway constructor completed');
    }

    /**
     * Get gateway name
     *
     * @return string
     */
    public function get_name() {
        return __('Midtrans Snap', 'tutor-midtrans');
    }

    /**
     * Get gateway logo
     *
     * @return string
     */
    public function get_logo() {
        return TUTOR_MIDTRANS_PLUGIN_URL . 'assets/logo.svg';
    }

    /**
     * Get gateway credentials
     *
     * @return array
     */
    public function get_credentials() {
        $settings = get_option('tutor_midtrans_settings', array());
        
        return array(
            'enabled' => isset($settings['enabled']) ? $settings['enabled'] : 'no',
            'environment' => isset($settings['environment']) ? $settings['environment'] : 'sandbox',
            'checkout_style' => isset($settings['checkout_style']) ? $settings['checkout_style'] : 'redirect',
            'payment_channels' => isset($settings['payment_channels']) ? $settings['payment_channels'] : array(),
            'sandbox_server_key' => isset($settings['sandbox_server_key']) ? $settings['sandbox_server_key'] : '',
            'sandbox_client_key' => isset($settings['sandbox_client_key']) ? $settings['sandbox_client_key'] : '',
            'production_server_key' => isset($settings['production_server_key']) ? $settings['production_server_key'] : '',
            'production_client_key' => isset($settings['production_client_key']) ? $settings['production_client_key'] : ''
        );
    }

    /**
     * Payment form HTML
     *
     * @param int $course_id
     * @return string
     */
    public function payment_form($course_id = 0) {
        $credentials = $this->get_credentials();
        
        if ($credentials['enabled'] !== 'yes') {
            return '';
        }

        $button_text = __('Pay with Midtrans', 'tutor-midtrans');
        $button_class = 'tutor-midtrans-pay';
        
        if ($credentials['checkout_style'] === 'popup') {
            $button_class .= ' tutor-midtrans-popup';
        }

        return sprintf(
            '<button type="button" class="%s" data-course-id="%d">%s</button>',
            esc_attr($button_class),
            esc_attr($course_id),
            esc_html($button_text)
        );
    }

    /**
     * Handle payment process
     *
     * @param int $order_id
     * @return void
     */
    public function handle_payment($order_id) {
        // This method is called by Tutor LMS when processing payment
        // The actual payment handling is done via AJAX
    }

    /**
     * Create Midtrans Snap token
     *
     * @return void
     */
    public function create_snap() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'tutor_midtrans_nonce')) {
            wp_send_json_error(__('Security check failed', 'tutor-midtrans'));
        }

        // Check user capability
        if (!is_user_logged_in()) {
            wp_send_json_error(__('User not logged in', 'tutor-midtrans'));
        }

        // Get course ID
        $course_id = intval($_POST['course_id']);
        if (!$course_id) {
            wp_send_json_error(__('Invalid course ID', 'tutor-midtrans'));
        }

        // Get course details
        $course = get_post($course_id);
        if (!$course || $course->post_type !== 'courses') {
            wp_send_json_error(__('Invalid course', 'tutor-midtrans'));
        }

        // Get user details
        $user = wp_get_current_user();
        
        // Generate unique order ID
        $order_id = 'tutor_order_' . $course_id . '_' . time();
        
        // Get course price
        $price = tutor_utils()->get_course_price($course_id);
        if (!$price || $price <= 0) {
            wp_send_json_error(__('Invalid course price', 'tutor-midtrans'));
        }

        // Prepare Midtrans parameters
        $params = array(
            'transaction_details' => array(
                'order_id' => $order_id,
                'gross_amount' => intval($price)
            ),
            'item_details' => array(
                array(
                    'id' => $course_id,
                    'price' => intval($price),
                    'quantity' => 1,
                    'name' => $course->post_title
                )
            ),
            'customer_details' => array(
                'first_name' => $user->first_name ?: $user->display_name,
                'last_name' => $user->last_name ?: '',
                'email' => $user->user_email
            ),
            'enabled_payments' => $this->get_credentials()['payment_channels']
        );

        try {
            // Create Snap token
            $result = SnapClient::create($params);
            
            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }

            // Store order data temporarily with error handling
            try {
                $transient_result = set_transient('tutor_midtrans_order_' . $order_id, array(
                    'course_id' => $course_id,
                    'user_id' => $user->ID,
                    'amount' => $price,
                    'timestamp' => time()
                ), HOUR_IN_SECONDS);

                if (!$transient_result) {
                    error_log('Tutor Midtrans Gateway: Failed to set transient for order: ' . $order_id);
                }
            } catch (Exception $e) {
                error_log('Tutor Midtrans Gateway: Exception setting transient: ' . $e->getMessage());
            }

            wp_send_json_success($result);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Register gateway with Tutor LMS
     *
     * @param array $gateways
     * @return array
     */
    public function register_gateway($gateways) {
        $credentials = $this->get_credentials();
        
        if ($credentials['enabled'] === 'yes') {
            $gateways['midtrans_snap'] = array(
                'name' => $this->get_name(),
                'logo' => $this->get_logo(),
                'description' => __('Pay securely with Midtrans Snap', 'tutor-midtrans')
            );
        }

        return $gateways;
    }
}
