/**
 * Tutor LMS Midtrans Snap Frontend JavaScript
 */
(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initMidtransGateway();
    });

    /**
     * Initialize Midtrans Gateway
     */
    function initMidtransGateway() {
        // Check if Midtrans is available
        if (typeof window.snap === 'undefined') {
            console.error('Midtrans Snap is not loaded');
            return;
        }

        // Bind click events to payment buttons
        $(document).on('click', '.tutor-midtrans-pay', handlePaymentClick);
    }

    /**
     * Handle payment button click
     */
    function handlePaymentClick(e) {
        e.preventDefault();

        const $button = $(this);
        const courseId = $button.data('course-id');
        const checkoutStyle = tutorMidtrans.checkoutStyle;

        if (!courseId) {
            showError('Invalid course ID');
            return;
        }

        // Disable button and show loading
        $button.prop('disabled', true).text(tutorMidtrans.strings.processing);

        // Create Snap token
        createSnapToken(courseId, checkoutStyle)
            .then(function(response) {
                if (response.success && response.data) {
                    const { token, redirect_url } = response.data;
                    
                    if (checkoutStyle === 'popup') {
                        openSnapPopup(token);
                    } else {
                        window.location.href = redirect_url;
                    }
                } else {
                    showError(response.data || tutorMidtrans.strings.error);
                }
            })
            .catch(function(error) {
                showError(error.message || tutorMidtrans.strings.error);
            })
            .finally(function() {
                // Re-enable button
                $button.prop('disabled', false).text('Pay with Midtrans');
            });
    }

    /**
     * Create Snap token via AJAX
     */
    function createSnapToken(courseId, checkoutStyle) {
        return $.ajax({
            url: tutorMidtrans.ajaxUrl,
            type: 'POST',
            data: {
                action: 'tutor_create_midtrans_snap',
                course_id: courseId,
                nonce: tutorMidtrans.nonce
            },
            dataType: 'json'
        });
    }

    /**
     * Open Snap popup
     */
    function openSnapPopup(token) {
        if (typeof window.snap.pay !== 'function') {
            showError('Snap popup function not available');
            return;
        }

        window.snap.pay(token, {
            onSuccess: function(result) {
                handlePaymentSuccess(result);
            },
            onPending: function(result) {
                handlePaymentPending(result);
            },
            onError: function(result) {
                handlePaymentError(result);
            },
            onClose: function() {
                // User closed the popup
                console.log('Snap popup closed');
            }
        });
    }

    /**
     * Handle successful payment
     */
    function handlePaymentSuccess(result) {
        console.log('Payment successful:', result);
        
        // Show success message
        showSuccess(tutorMidtrans.strings.success);
        
        // Redirect to course or dashboard after a short delay
        setTimeout(function() {
            // Try to redirect to the course page
            const courseId = getCurrentCourseId();
            if (courseId) {
                window.location.href = getCourseUrl(courseId);
            } else {
                window.location.href = getDashboardUrl();
            }
        }, 2000);
    }

    /**
     * Handle pending payment
     */
    function handlePaymentPending(result) {
        console.log('Payment pending:', result);
        
        showInfo('Payment is pending. You will be notified once it is completed.');
        
        // Redirect to dashboard
        setTimeout(function() {
            window.location.href = getDashboardUrl();
        }, 3000);
    }

    /**
     * Handle payment error
     */
    function handlePaymentError(result) {
        console.error('Payment error:', result);
        
        showError('Payment failed. Please try again or contact support.');
    }

    /**
     * Get current course ID from page
     */
    function getCurrentCourseId() {
        // Try to get from button data
        const $button = $('.tutor-midtrans-pay').first();
        if ($button.length) {
            return $button.data('course-id');
        }

        // Try to get from URL
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('course_id') || urlParams.get('id');
    }

    /**
     * Get course URL
     */
    function getCourseUrl(courseId) {
        // This should match your WordPress permalink structure
        return window.location.origin + '/courses/' + courseId;
    }

    /**
     * Get dashboard URL
     */
    function getDashboardUrl() {
        // This should match your Tutor LMS dashboard URL
        return window.location.origin + '/dashboard';
    }

    /**
     * Show success message
     */
    function showSuccess(message) {
        showMessage(message, 'success');
    }

    /**
     * Show error message
     */
    function showError(message) {
        showMessage(message, 'error');
    }

    /**
     * Show info message
     */
    function showInfo(message) {
        showMessage(message, 'info');
    }

    /**
     * Show message with type
     */
    function showMessage(message, type) {
        // Remove existing messages
        $('.tutor-midtrans-message').remove();

        // Create message element
        const $message = $('<div class="tutor-midtrans-message tutor-midtrans-' + type + '">' + message + '</div>');
        
        // Add styles
        $message.css({
            'position': 'fixed',
            'top': '20px',
            'right': '20px',
            'padding': '15px 20px',
            'border-radius': '5px',
            'color': '#fff',
            'font-weight': 'bold',
            'z-index': '9999',
            'max-width': '300px',
            'word-wrap': 'break-word'
        });

        // Set background color based on type
        switch (type) {
            case 'success':
                $message.css('background-color', '#28a745');
                break;
            case 'error':
                $message.css('background-color', '#dc3545');
                break;
            case 'info':
                $message.css('background-color', '#17a2b8');
                break;
        }

        // Add to page
        $('body').append($message);

        // Auto remove after 5 seconds
        setTimeout(function() {
            $message.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    /**
     * Utility function to check if element exists
     */
    function elementExists(selector) {
        return $(selector).length > 0;
    }

    /**
     * Utility function to get element
     */
    function getElement(selector) {
        return $(selector);
    }

})(jQuery);
