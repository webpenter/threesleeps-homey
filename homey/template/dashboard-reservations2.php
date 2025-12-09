<?php
/**
 * Template Name: Dashboard My Reservations
 */
defined('ABSPATH') or die('No script kiddies please!');

if (!isset($_GET['reservation_no_userHash']) && !is_user_logged_in()) {
    wp_redirect(home_url('/'));
}

global $homey_local, $current_user;
if (isset($_GET['reservation_no_userHash'])) {
    if ($_GET['reservation_no_userHash'] > 0) {
        $userID = (int)$_GET['reservation_no_userHash'];
        $userID = intval(deHashNoUserId($userID));
        $user_data = get_userdata($userID);

        if ($user_data) {
            $user_email = $user_data->user_email;
        }
        echo "<input type='hidden' id='reservation_no_userHash' value='" . intval(hashNoUserId($userID)) . "'>";
    }
} else {
    wp_get_current_user();
    $userID = $current_user->ID;
}

$user_email = $current_user->user_email;
$admin_email = get_bloginfo('admin_email');
$allowed_html = array();

get_header();
?>

<section id="body-area">

    <div class="dashboard-page-title">
        <h1><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h1>
    </div><!-- .dashboard-page-title -->

    <?php
    if (!isset($_GET['reservation_no_userHash'])) {
        get_template_part('template-parts/dashboard/side-menu');
    } ?>

    <?php
    if (isset($_GET['reservation_detail']) && $_GET['reservation_detail'] != "") {
        $resr_id = $_GET['reservation_detail'];
        $is_hourly = get_post_meta($resr_id, 'is_hourly', true);

        if ($is_hourly == 'yes') {
            if (isset($_GET['reservation_no_userHash'])) {
                if ($_GET['reservation_no_userHash'] > 0) {
                    get_template_part('template-parts/no-login-user-booking-hourly');
                }
            } else {
                get_template_part('template-parts/dashboard/reservation/detail-hourly');
            }
        } else {
            if (isset($_GET['reservation_no_userHash'])) {
                if ($_GET['reservation_no_userHash'] > 0) {
                    get_template_part('template-parts/no-login-user-booking');
                }
            } else {
                get_template_part('template-parts/dashboard/reservation/detail');
            }
        }

    } else {
        if (isset($_GET['reservation_no_userHash'])) {
            if ($_GET['reservation_no_userHash'] > 0) {
                get_template_part('template-parts/dashboard/reservation/no-login-list');
            }
        } else {
            get_template_part('template-parts/dashboard/reservation-host/list');
        }
    }
    ?>

</section><!-- #body-area -->


<?php get_footer(); ?>
