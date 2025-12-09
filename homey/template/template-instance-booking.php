<?php
/**
 * Template Name: Instance Booking
 */

$no_login_needed_for_booking = homey_option('no_login_needed_for_booking');

if(isset($_GET['reservation_no_userHash'])){
    if($_GET['reservation_no_userHash'] > 0){
        $userID = (int) $_GET['reservation_no_userHash'];
        $userID = intval(deHashNoUserId($userID));
        echo "<input type='hidden' id='reservation_no_userHash' value='".intval(hashNoUserId($userID))."'>";
    }
}

if ( $no_login_needed_for_booking == 'no' && !is_user_logged_in() ) {
    wp_redirect(  home_url('/') );
    return false;
}
get_header();

global $post, $current_user, $homey_prefix, $homey_local;

$listing_id = isset($_GET['listing_id']) ? $_GET['listing_id'] : '';
$homey_booking_type = homey_booking_type_by_id($listing_id);

if($homey_booking_type == 'per_hour') {
    get_template_part('template-parts/instance-booking/hourly');
} else if($homey_booking_type == 'per_day_date') {
    get_template_part('template-parts/instance-booking/daily');
}else {
    get_template_part('template-parts/instance-booking/nightly');
}

get_footer(); ?>