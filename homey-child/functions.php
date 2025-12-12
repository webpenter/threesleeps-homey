<?php
require_once( get_stylesheet_directory() . '/inc/register-scripts.php' );
function homey_enqueue_styles() {
    
    // enqueue parent styles
    wp_enqueue_style('homey-parent-theme', get_template_directory_uri() .'/style.css');
    
    // enqueue child styles
    wp_enqueue_style('homey-child-theme', get_stylesheet_directory_uri() .'/style.css', array('homey-parent-theme'));
    
}
add_action('wp_enqueue_scripts', 'homey_enqueue_styles');

require_once('includes/stripe-connect.php'); 

require_once( get_stylesheet_directory() . '/framework/functions/listings.php' );
require_once( get_stylesheet_directory() . '/framework/functions/reservation.php' );
require_once( get_stylesheet_directory() . '/framework/functions/calendar.php' );
require_once( get_stylesheet_directory() . '/framework/functions/icalendar.php' );

/**
 *    ---------------------------------------------------------------------------------------
 *    Meta Boxes
 *    ---------------------------------------------------------------------------------------
 */
require_once(get_stylesheet_directory() . '/framework/metaboxes/homey-meta-boxes.php');

add_filter('cron_schedules', function ($schedules) {
    if (!isset($schedules['every_hour'])) {
        $schedules['every_hour'] = [
            'interval' => 3600, // 1 hour in seconds
            'display'  => __('Every Hour'),
        ];
    }
    return $schedules;
});
if (!wp_next_scheduled('homey_ical_sync_multi')) {
    wp_schedule_event(time(), 'every_hour', 'homey_ical_sync_multi');
}

if(isset($_GET['homey_ical_multi'])) {

   homey_ical_sync_multi_callback();

}

add_action('save_post', function($post_id) {

	if (defined('DOING_AJAX') && DOING_AJAX) return;
    if (!is_admin()) return;

    $booking_type = get_post_meta($post_id, 'homey_booking_type', true);
    $accomodation = get_post_meta($post_id, 'homey_accomodation', true);

    if ($booking_type === 'per_day_multi') {

		if (!empty($accomodation) && is_array($accomodation)) {
			$cleaned  = [];
			$used_ids = [];

			foreach ($accomodation as $key => $room) {
				$room_id = isset($room['room_id']) ? trim((string)$room['room_id']) : '';
				$price   = isset($room['night_price']) ? trim((string)$room['night_price']) : '';

				// Treat empty string or zero as "empty" price – adjust if you want to allow zero
				$price_is_empty = ($price === '' || (float)$price == 0);

				// 1) If BOTH room_id and price are empty -> skip (unset)
				if ($room_id === '' && $price_is_empty) {
					continue;
				}

				// 2) If price exists but room_id missing -> generate new room_id
				if ($room_id === '') {
					$room_id = 'room_' . uniqid('', true);
					$room['room_id'] = $room_id;
				}

				// 3) If room_id duplicates another, regenerate to keep unique
				if (isset($used_ids[$room_id])) {
					$room_id = 'room_' . uniqid('', true);
					$room['room_id'] = $room_id;
				}

				$used_ids[$room_id] = true;
				$cleaned[] = $room;
			}

			// Reindex keys to keep things tidy for Meta Box
			update_post_meta($post_id, 'homey_accomodation', array_values($cleaned));
		}

        update_post_meta($post_id, 'homey_multiroom_booking', 'per_day_multi');
        update_post_meta($post_id, 'homey_booking_type', 'per_day');
    } else {
        update_post_meta($post_id, 'homey_multiroom_booking', '');
    }
});


?>