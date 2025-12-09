<?php
/**
 * Template Name: Listing Availability
 * Description: Handles listing availability status
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the current listing ID
$listing_id = get_the_ID();

// Check if this is a new listing
$is_new_listing = get_post_meta($listing_id, 'is_new_listing', true);

if ($is_new_listing === '') {
    // If it's a new listing, set it as unavailable by default
    update_post_meta($listing_id, 'is_new_listing', '1');
    update_post_meta($listing_id, 'listing_availability', 'unavailable');
    
    // Add a notice for the host
    $notice = esc_html__('Your listing is currently unavailable. Please add availability dates to make it available for booking.', 'homey');
    update_post_meta($listing_id, 'availability_notice', $notice);
}

// Get the current availability status
$availability_status = get_post_meta($listing_id, 'listing_availability', true);
$availability_notice = get_post_meta($listing_id, 'availability_notice', true);

// Display the availability status and notice
if ($availability_status === 'unavailable') {
    ?>
    <div class="alert alert-warning">
        <i class="fa fa-exclamation-circle"></i>
        <?php echo esc_html($availability_notice); ?>
    </div>
    <?php
}

// Function to update availability when host adds dates
function homey_update_listing_availability($listing_id) {
    // Check if availability dates are set
    $availability_dates = get_post_meta($listing_id, 'reservation_dates', true);
    
    if (!empty($availability_dates)) {
        update_post_meta($listing_id, 'listing_availability', 'available');
        delete_post_meta($listing_id, 'availability_notice');
    }
}

// Hook to update availability when host adds dates
add_action('homey_after_save_availability', 'homey_update_listing_availability', 10, 1); 