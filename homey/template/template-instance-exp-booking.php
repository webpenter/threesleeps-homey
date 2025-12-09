<?php
/**
 * Template Name: Instant Experience Booking
 */

$no_login_needed_for_exp_booking = homey_option('no_login_needed_for_exp_booking');

if(isset($_GET['reservation_no_userHash'])){
    if($_GET['reservation_no_userHash'] > 0){
        $userID = (int) $_GET['reservation_no_userHash'];
        $userID = intval(deHashNoUserId($userID));
        echo "<input type='hidden' id='reservation_no_userHash' value='".intval(hashNoUserId($userID))."'>";
    }
}

if ( $no_login_needed_for_exp_booking == 'no' && !is_user_logged_in() ) {
    wp_redirect(  home_url('/') );
    return false;
}
get_header();

global $post, $current_user, $homey_prefix, $homey_local;

$experience_id = isset($_GET['experience_id']) ? $_GET['experience_id'] : '';

get_template_part('template-parts/instance-booking/experiences/slots');

get_footer();