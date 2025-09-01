<?php
namespace TutorMidtransGateway;

/**
 * Frontend Assets Manager
 */
class Assets {

    /**
     * Constructor
     */
    public function __construct() {
        error_log('Tutor Midtrans Gateway: Assets constructor called');

        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_head', array($this, 'add_inline_config'));

        error_log('Tutor Midtrans Gateway: Assets constructor completed');
    }

    /**
     * Enqueue frontend scripts
     */
    public function enqueue_scripts() {
        // Only load on course pages
        if (!is_singular('courses') && !is_page()) {
            return;
        }

        $settings = get_option('tutor_midtrans_settings', array());
        
        if (empty($settings['enabled']) || $settings['enabled'] !== 'yes') {
            return;
        }

        // Enqueue Midtrans Snap JS from CDN
        $midtrans_url = SnapClient::get_base_url();
        wp_enqueue_script(
            'midtrans-snap',
            $midtrans_url,
            array(),
            null,
            true
        );

        // Enqueue our custom JS
        wp_enqueue_script(
            'tutor-midtrans',
            TUTOR_MIDTRANS_PLUGIN_URL . 'assets/tutor-midtrans.js',
            array('jquery', 'midtrans-snap'),
            TUTOR_MIDTRANS_VERSION,
            true
        );

        // Localize script with AJAX URL and nonce
        wp_localize_script('tutor-midtrans', 'tutorMidtrans', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tutor_midtrans_nonce'),
            'clientKey' => SnapClient::get_client_key(),
            'checkoutStyle' => isset($settings['checkout_style']) ? $settings['checkout_style'] : 'redirect',
            'strings' => array(
                'processing' => __('Processing payment...', 'tutor-midtrans'),
                'error' => __('An error occurred. Please try again.', 'tutor-midtrans'),
                'success' => __('Payment successful!', 'tutor-midtrans')
            )
        ));
    }

    /**
     * Add inline configuration
     */
    public function add_inline_config() {
        $settings = get_option('tutor_midtrans_settings', array());
        
        if (empty($settings['enabled']) || $settings['enabled'] !== 'yes') {
            return;
        }

        $client_key = SnapClient::get_client_key();
        if (empty($client_key)) {
            return;
        }

        ?>
        <script type="text/javascript">
            window.MidtransConfig = {
                clientKey: '<?php echo esc_js($client_key); ?>',
                environment: '<?php echo esc_js(isset($settings['environment']) ? $settings['environment'] : 'sandbox'); ?>'
            };
        </script>
        <?php
    }
}
