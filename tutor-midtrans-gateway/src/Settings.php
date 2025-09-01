<?php
namespace TutorMidtransGateway;

/**
 * Settings integration for Tutor LMS
 */
class Settings {

    /**
     * Constructor
     */
    public function __construct() {
        error_log('Tutor Midtrans Gateway: Settings constructor called');

        add_action('tutor_admin_settings_payment_after', array($this, 'add_settings_tab'));
        add_action('tutor_admin_settings_payment_midtrans_snap', array($this, 'render_settings_page'));
        add_action('admin_post_tutor_save_midtrans_settings', array($this, 'save_settings'));

        error_log('Tutor Midtrans Gateway: Settings constructor completed');
    }

    /**
     * Add Midtrans Snap tab to Tutor LMS settings
     */
    public function add_settings_tab() {
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
        $active_class = $current_tab === 'midtrans_snap' ? 'active' : '';
        
        echo '<a href="' . admin_url('admin.php?page=tutor_settings&tab=payment&subtab=midtrans_snap') . '" class="nav-tab ' . esc_attr($active_class) . '">' . 
             esc_html__('Midtrans Snap', 'tutor-midtrans') . '</a>';
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'tutor-midtrans'));
        }

        $settings = get_option('tutor_midtrans_settings', array());
        
        // Set defaults
        $settings = wp_parse_args($settings, array(
            'enabled' => 'no',
            'environment' => 'sandbox',
            'checkout_style' => 'redirect',
            'payment_channels' => array('credit_card', 'gopay', 'shopeepay', 'bca_va', 'bni_va', 'bri_va', 'permata_va', 'echannel'),
            'sandbox_server_key' => '',
            'sandbox_client_key' => '',
            'production_server_key' => '',
            'production_client_key' => ''
        ));

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Midtrans Snap Gateway Settings', 'tutor-midtrans'); ?></h1>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('tutor_midtrans_settings_nonce', 'tutor_midtrans_nonce'); ?>
                <input type="hidden" name="action" value="tutor_save_midtrans_settings">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="enabled"><?php esc_html_e('Enable Gateway', 'tutor-midtrans'); ?></label>
                        </th>
                        <td>
                            <select name="enabled" id="enabled">
                                <option value="no" <?php selected($settings['enabled'], 'no'); ?>>
                                    <?php esc_html_e('No', 'tutor-midtrans'); ?>
                                </option>
                                <option value="yes" <?php selected($settings['enabled'], 'yes'); ?>>
                                    <?php esc_html_e('Yes', 'tutor-midtrans'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Enable or disable the Midtrans Snap payment gateway.', 'tutor-midtrans'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="environment"><?php esc_html_e('Environment', 'tutor-midtrans'); ?></label>
                        </th>
                        <td>
                            <select name="environment" id="environment">
                                <option value="sandbox" <?php selected($settings['environment'], 'sandbox'); ?>>
                                    <?php esc_html_e('Sandbox (Testing)', 'tutor-midtrans'); ?>
                                </option>
                                <option value="production" <?php selected($settings['environment'], 'production'); ?>>
                                    <?php esc_html_e('Production (Live)', 'tutor-midtrans'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Select the Midtrans environment. Use Sandbox for testing and Production for live transactions.', 'tutor-midtrans'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="checkout_style"><?php esc_html_e('Checkout Style', 'tutor-midtrans'); ?></label>
                        </th>
                        <td>
                            <select name="checkout_style" id="checkout_style">
                                <option value="redirect" <?php selected($settings['checkout_style'], 'redirect'); ?>>
                                    <?php esc_html_e('Redirect', 'tutor-midtrans'); ?>
                                </option>
                                <option value="popup" <?php selected($settings['checkout_style'], 'popup'); ?>>
                                    <?php esc_html_e('Popup', 'tutor-midtrans'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Choose how the Midtrans checkout will be displayed.', 'tutor-midtrans'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e('Payment Channels', 'tutor-midtrans'); ?></label>
                        </th>
                        <td>
                            <?php
                            $channels = array(
                                'credit_card' => __('Credit Card', 'tutor-midtrans'),
                                'gopay' => __('GoPay', 'tutor-midtrans'),
                                'shopeepay' => __('ShopeePay', 'tutor-midtrans'),
                                'bca_va' => __('BCA Virtual Account', 'tutor-midtrans'),
                                'bni_va' => __('BNI Virtual Account', 'tutor-midtrans'),
                                'bri_va' => __('BRI Virtual Account', 'tutor-midtrans'),
                                'permata_va' => __('Permata Virtual Account', 'tutor-midtrans'),
                                'echannel' => __('E-Channel', 'tutor-midtrans')
                            );

                            foreach ($channels as $channel => $label) {
                                $checked = in_array($channel, $settings['payment_channels']) ? 'checked' : '';
                                echo '<label style="display: block; margin-bottom: 5px;">';
                                echo '<input type="checkbox" name="payment_channels[]" value="' . esc_attr($channel) . '" ' . $checked . '> ';
                                echo esc_html($label);
                                echo '</label>';
                            }
                            ?>
                            <p class="description">
                                <?php esc_html_e('Select which payment methods to enable for your customers.', 'tutor-midtrans'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="sandbox_server_key"><?php esc_html_e('Sandbox Server Key', 'tutor-midtrans'); ?></label>
                        </th>
                        <td>
                            <input type="password" name="sandbox_server_key" id="sandbox_server_key" 
                                   value="<?php echo esc_attr($settings['sandbox_server_key']); ?>" class="regular-text">
                            <p class="description">
                                <?php esc_html_e('Your Midtrans Sandbox Server Key for testing.', 'tutor-midtrans'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="sandbox_client_key"><?php esc_html_e('Sandbox Client Key', 'tutor-midtrans'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="sandbox_client_key" id="sandbox_client_key" 
                                   value="<?php echo esc_attr($settings['sandbox_client_key']); ?>" class="regular-text">
                            <p class="description">
                                <?php esc_html_e('Your Midtrans Sandbox Client Key for testing.', 'tutor-midtrans'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="production_server_key"><?php esc_html_e('Production Server Key', 'tutor-midtrans'); ?></label>
                        </th>
                        <td>
                            <input type="password" name="production_server_key" id="production_server_key" 
                                   value="<?php echo esc_attr($settings['production_server_key']); ?>" class="regular-text">
                            <p class="description">
                                <?php esc_html_e('Your Midtrans Production Server Key for live transactions.', 'tutor-midtrans'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="production_client_key"><?php esc_html_e('Production Client Key', 'tutor-midtrans'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="production_client_key" id="production_client_key" 
                                   value="<?php echo esc_attr($settings['production_client_key']); ?>" class="regular-text">
                            <p class="description">
                                <?php esc_html_e('Your Midtrans Production Client Key for live transactions.', 'tutor-midtrans'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Save Settings', 'tutor-midtrans')); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Save settings
     */
    public function save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'tutor-midtrans'));
        }

        if (!wp_verify_nonce($_POST['tutor_midtrans_nonce'], 'tutor_midtrans_settings_nonce')) {
            wp_die(__('Security check failed', 'tutor-midtrans'));
        }

        $settings = array(
            'enabled' => sanitize_text_field($_POST['enabled']),
            'environment' => sanitize_text_field($_POST['environment']),
            'checkout_style' => sanitize_text_field($_POST['checkout_style']),
            'payment_channels' => isset($_POST['payment_channels']) ? array_map('sanitize_text_field', $_POST['payment_channels']) : array(),
            'sandbox_server_key' => sanitize_text_field($_POST['sandbox_server_key']),
            'sandbox_client_key' => sanitize_text_field($_POST['sandbox_client_key']),
            'production_server_key' => sanitize_text_field($_POST['production_server_key']),
            'production_client_key' => sanitize_text_field($_POST['production_client_key'])
        );

        update_option('tutor_midtrans_settings', $settings);

        wp_redirect(admin_url('admin.php?page=tutor_settings&tab=payment&subtab=midtrans_snap&updated=true'));
        exit;
    }
}
