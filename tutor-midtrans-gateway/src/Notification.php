<?php
namespace TutorMidtransGateway;

/**
 * Midtrans IPN Notification Handler
 */
class Notification {

    /**
     * Constructor
     */
    public function __construct() {
        error_log('Tutor Midtrans Gateway: Notification constructor called');

        add_action('init', array($this, 'handle_notification'));

        error_log('Tutor Midtrans Gateway: Notification constructor completed');
    }

    /**
     * Handle Midtrans notification
     */
    public function handle_notification() {
        if (!isset($_POST['order_id']) || !isset($_POST['status_code']) || !isset($_POST['gross_amount'])) {
            return;
        }

        // Verify this is a Midtrans notification
        if (!isset($_POST['signature_key'])) {
            return;
        }

        $order_id = sanitize_text_field($_POST['order_id']);
        $status_code = sanitize_text_field($_POST['status_code']);
        $gross_amount = sanitize_text_field($_POST['gross_amount']);
        $signature_key = sanitize_text_field($_POST['signature_key']);

        // Verify signature
        if (!SnapClient::verify_signature($order_id, $status_code, $gross_amount, $signature_key)) {
            error_log('Midtrans signature verification failed for order: ' . $order_id);
            http_response_code(400);
            exit;
        }

        // Parse order ID to get actual course ID
        $order_parts = explode('_', $order_id);
        if (count($order_parts) < 4 || $order_parts[0] !== 'tutor' || $order_parts[1] !== 'order') {
            error_log('Invalid order ID format: ' . $order_id);
            http_response_code(400);
            exit;
        }

        $course_id = intval($order_parts[2]);
        $timestamp = intval($order_parts[3]);

        // Get stored order data
        $order_data = get_transient('tutor_midtrans_order_' . $order_id);
        if (!$order_data) {
            error_log('Order data not found for: ' . $order_id);
            http_response_code(400);
            exit;
        }

        // Verify amount
        if (intval($gross_amount) !== intval($order_data['amount'])) {
            error_log('Amount mismatch for order: ' . $order_id);
            http_response_code(400);
            exit;
        }

        // Process status
        $this->process_payment_status($status_code, $course_id, $order_data['user_id'], $order_id);

        // Clean up transient
        delete_transient('tutor_midtrans_order_' . $order_id);

        // Return success
        http_response_code(200);
        echo 'OK';
        exit;
    }

    /**
     * Process payment status
     *
     * @param string $status_code
     * @param int $course_id
     * @param int $user_id
     * @param string $order_id
     */
    private function process_payment_status($status_code, $course_id, $user_id, $order_id) {
        $tutor_utils = tutor_utils();

        switch ($status_code) {
            case '200':
            case '201':
                // Payment successful
                $this->handle_successful_payment($course_id, $user_id, $order_id);
                break;

            case '202':
                // Payment pending
                $this->handle_pending_payment($course_id, $user_id, $order_id);
                break;

            case '400':
            case '401':
            case '402':
            case '403':
            case '404':
            case '405':
            case '406':
            case '407':
            case '408':
            case '409':
            case '410':
            case '411':
            case '412':
            case '413':
            case '414':
            case '415':
            case '416':
            case '417':
            case '418':
            case '419':
            case '420':
            case '421':
            case '422':
            case '423':
            case '424':
            case '425':
            case '426':
            case '427':
            case '428':
            case '429':
            case '430':
            case '431':
            case '432':
            case '433':
            case '434':
            case '435':
            case '436':
            case '437':
            case '438':
            case '439':
            case '440':
            case '441':
            case '442':
            case '443':
            case '444':
            case '445':
            case '446':
            case '447':
            case '448':
            case '449':
            case '450':
            case '451':
            case '452':
            case '453':
            case '454':
            case '455':
            case '456':
            case '457':
            case '458':
            case '459':
            case '460':
            case '461':
            case '462':
            case '463':
            case '464':
            case '465':
            case '466':
            case '467':
            case '468':
            case '469':
            case '470':
            case '471':
            case '472':
            case '473':
            case '474':
            case '475':
            case '476':
            case '477':
            case '478':
            case '479':
            case '480':
            case '481':
            case '482':
            case '483':
            case '484':
            case '485':
            case '486':
            case '487':
            case '488':
            case '489':
            case '490':
            case '491':
            case '492':
            case '493':
            case '494':
            case '495':
            case '496':
            case '497':
            case '498':
            case '499':
            case '500':
            case '501':
            case '502':
            case '503':
            case '504':
            case '505':
            case '506':
            case '507':
            case '508':
            case '509':
            case '510':
            case '511':
                // Payment failed
                $this->handle_failed_payment($course_id, $user_id, $order_id, $status_code);
                break;

            default:
                // Unknown status
                error_log('Unknown Midtrans status code: ' . $status_code . ' for order: ' . $order_id);
                break;
        }
    }

    /**
     * Handle successful payment
     *
     * @param int $course_id
     * @param int $user_id
     * @param string $order_id
     */
    private function handle_successful_payment($course_id, $user_id, $order_id) {
        try {
            // Check if user already enrolled
            if ($this->is_user_enrolled($course_id, $user_id)) {
                return;
            }

            // Enroll user to course
            $enrollment_id = $this->enroll_user_to_course($course_id, $user_id);
            
            if ($enrollment_id) {
                // Log successful enrollment
                error_log('User ' . $user_id . ' successfully enrolled to course ' . $course_id . ' via Midtrans');
                
                // Send enrollment email
                $this->send_enrollment_email($user_id, $course_id);
            }

        } catch (Exception $e) {
            error_log('Error processing successful payment: ' . $e->getMessage());
        }
    }

    /**
     * Handle pending payment
     *
     * @param int $course_id
     * @param int $user_id
     * @param string $order_id
     */
    private function handle_pending_payment($course_id, $user_id, $order_id) {
        // For pending payments, we don't enroll the user yet
        // Just log the status
        error_log('Payment pending for order: ' . $order_id . ' - User: ' . $user_id . ' - Course: ' . $course_id);
    }

    /**
     * Handle failed payment
     *
     * @param int $course_id
     * @param int $user_id
     * @param string $order_id
     * @param string $status_code
     */
    private function handle_failed_payment($course_id, $user_id, $order_id, $status_code) {
        // Log failed payment
        error_log('Payment failed for order: ' . $order_id . ' - Status: ' . $status_code . ' - User: ' . $user_id . ' - Course: ' . $course_id);
        
        // Send failure notification email
        $this->send_failure_email($user_id, $course_id, $status_code);
    }

    /**
     * Check if user is already enrolled
     *
     * @param int $course_id
     * @param int $user_id
     * @return bool
     */
    private function is_user_enrolled($course_id, $user_id) {
        return tutor_utils()->is_course_enrolled_by_user($course_id, $user_id);
    }

    /**
     * Enroll user to course
     *
     * @param int $course_id
     * @param int $user_id
     * @return int|false
     */
    private function enroll_user_to_course($course_id, $user_id) {
        $tutor_utils = tutor_utils();
        
        // Create enrollment
        $enrollment_data = array(
            'user_id' => $user_id,
            'course_id' => $course_id,
            'status' => 'completed',
            'enrolled_date' => current_time('mysql'),
            'completion_date' => current_time('mysql'),
            'payment_method' => 'midtrans_snap'
        );

        return $tutor_utils->do_enroll($course_id, $enrollment_data);
    }

    /**
     * Send enrollment email
     *
     * @param int $user_id
     * @param int $course_id
     */
    private function send_enrollment_email($user_id, $course_id) {
        $user = get_userdata($user_id);
        $course = get_post($course_id);
        
        if (!$user || !$course) {
            return;
        }

        $subject = sprintf(__('Welcome to %s!', 'tutor-midtrans'), $course->post_title);
        $message = sprintf(
            __('Hello %s,<br><br>You have been successfully enrolled to <strong>%s</strong>.<br><br>You can now access your course from your dashboard.<br><br>Best regards,<br>%s', 'tutor-midtrans'),
            $user->display_name,
            $course->post_title,
            get_bloginfo('name')
        );

        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($user->user_email, $subject, $message, $headers);
    }

    /**
     * Send failure email
     *
     * @param int $user_id
     * @param int $course_id
     * @param string $status_code
     */
    private function send_failure_email($user_id, $course_id, $status_code) {
        $user = get_userdata($user_id);
        $course = get_post($course_id);
        
        if (!$user || !$course) {
            return;
        }

        $subject = sprintf(__('Payment Failed for %s', 'tutor-midtrans'), $course->post_title);
        $message = sprintf(
            __('Hello %s,<br><br>Your payment for <strong>%s</strong> has failed.<br><br>Please try again or contact support if the problem persists.<br><br>Best regards,<br>%s', 'tutor-midtrans'),
            $user->display_name,
            $course->post_title,
            get_bloginfo('name')
        );

        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($user->user_email, $subject, $message, $headers);
    }
}
