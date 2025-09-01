<?php
namespace TutorMidtransGateway;

/**
 * Midtrans Snap API Client
 */
class SnapClient {

    /**
     * Create Snap token
     *
     * @param array $params
     * @return array|WP_Error
     */
    public static function create($params) {
        $settings = get_option('tutor_midtrans_settings', array());
        
        if (empty($settings['enabled']) || $settings['enabled'] !== 'yes') {
            return new \WP_Error('gateway_disabled', __('Gateway is disabled', 'tutor-midtrans'));
        }

        $environment = isset($settings['environment']) ? $settings['environment'] : 'sandbox';
        
        // Get appropriate keys based on environment
        if ($environment === 'sandbox') {
            $server_key = $settings['sandbox_server_key'];
            $base_url = 'https://app.sandbox.midtrans.com/snap/v1/transactions';
        } else {
            $server_key = $settings['production_server_key'];
            $base_url = 'https://app.midtrans.com/snap/v1/transactions';
        }

        if (empty($server_key)) {
            return new \WP_Error('missing_key', __('Server key is missing', 'tutor-midtrans'));
        }

        // Prepare request
        $request_args = array(
            'method' => 'POST',
            'timeout' => 30,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($server_key . ':')
            ),
            'body' => json_encode($params),
            'cookies' => array()
        );

        // Make request to Midtrans
        $response = wp_remote_post($base_url, $request_args);

        if (is_wp_error($response)) {
            return new \WP_Error('request_failed', $response->get_error_message());
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code !== 200) {
            return new \WP_Error('api_error', sprintf(
                __('Midtrans API error: %s (Code: %d)', 'tutor-midtrans'),
                $response_body,
                $response_code
            ));
        }

        $response_data = json_decode($response_body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new \WP_Error('invalid_response', __('Invalid response from Midtrans', 'tutor-midtrans'));
        }

        if (!isset($response_data['token']) || !isset($response_data['redirect_url'])) {
            return new \WP_Error('missing_data', __('Missing token or redirect URL from Midtrans', 'tutor-midtrans'));
        }

        return array(
            'token' => $response_data['token'],
            'redirect_url' => $response_data['redirect_url']
        );
    }

    /**
     * Verify signature for notification
     *
     * @param string $order_id
     * @param string $status_code
     * @param string $gross_amount
     * @param string $signature_key
     * @return bool
     */
    public static function verify_signature($order_id, $status_code, $gross_amount, $signature_key) {
        $settings = get_option('tutor_midtrans_settings', array());
        $environment = isset($settings['environment']) ? $settings['environment'] : 'sandbox';
        
        if ($environment === 'sandbox') {
            $server_key = $settings['sandbox_server_key'];
        } else {
            $server_key = $settings['production_server_key'];
        }

        if (empty($server_key)) {
            return false;
        }

        $expected_signature = hash('sha512', $order_id . $status_code . $gross_amount . $server_key);
        
        return hash_equals($expected_signature, $signature_key);
    }

    /**
     * Get environment-specific client key
     *
     * @return string
     */
    public static function get_client_key() {
        $settings = get_option('tutor_midtrans_settings', array());
        $environment = isset($settings['environment']) ? $settings['environment'] : 'sandbox';
        
        if ($environment === 'sandbox') {
            return isset($settings['sandbox_client_key']) ? $settings['sandbox_client_key'] : '';
        } else {
            return isset($settings['production_client_key']) ? $settings['production_client_key'] : '';
        }
    }

    /**
     * Get environment-specific base URL
     *
     * @return string
     */
    public static function get_base_url() {
        $settings = get_option('tutor_midtrans_settings', array());
        $environment = isset($settings['environment']) ? $settings['environment'] : 'sandbox';
        
        if ($environment === 'sandbox') {
            return 'https://app.sandbox.midtrans.com/snap/snap.js';
        } else {
            return 'https://app.midtrans.com/snap/snap.js';
        }
    }
}
