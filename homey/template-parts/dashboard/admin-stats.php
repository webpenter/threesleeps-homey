<?php
global $wpdb, $current_user, $userID, $homey_local, $homey_threads, $author;
$reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
$listings_page_link = homey_get_template_link('template/dashboard-listings.php');
$dashboard = homey_get_template_link('template/dashboard.php');
$users_page_link = add_query_arg( 'dpage', 'users', $dashboard );
$total_earnings = $author['total_earnings'];
$total_website_one_week_earnings = homey_get_website_earnings( date('Y-m-01 00:00:00') );

$total_listings = homey_posts_count('listing');
$total_reservation = homey_posts_count('homey_reservation');
$count_users = count_users();
$new_users = homey_get_users_count_from_last_24h();

$new_listings = homey_get_listings_count_from_last_24h('listing');
$new_reservations = homey_get_listings_count_from_last_24h('homey_reservation');

$new_listings_text = esc_html__('New listing', 'homey');
if($new_listings > 1) {
    $new_listings_text = esc_html__('New listings', 'homey');
}

$new_reservations_text = esc_html__('New reservation', 'homey');
if($new_reservations > 1) {
    $new_reservations_text = esc_html__('New reservations', 'homey');
}

$new_users_text = esc_html__('New User', 'homey');
if($new_users > 1) {
    $new_users_text = esc_html__('New Users', 'homey');
}

$wallet_page_link = homey_get_template_link('template/dashboard-wallet.php');
$host_earnings_link = add_query_arg( 'host', $userID, $wallet_page_link );
$guest_deposit_link = add_query_arg( 'guest', $userID, $wallet_page_link );

?>
<div class="block">
    <div class="block-verify">
        <div class="block-col block-col-25 text-left">
            <h3><?php echo esc_attr($homey_local['pr_listing_label']); ?></h3>
            <p class="block-big-text"><?php echo esc_attr($total_listings); ?></p>
            <div><?php echo esc_attr($new_listings); ?> <?php echo esc_attr($new_listings_text); ?></div>
            <a class="btn btn-slim admin-top-banner-btn" href="<?php echo esc_url($listings_page_link); ?>"><?php esc_html_e('Manage', 'homey'); ?></a>
        </div>
        <div class="block-col block-col-25">
            <h3><?php echo esc_attr($homey_local['pr_resv_label']); ?></h3>
            <p class="block-big-text"><?php echo esc_attr($total_reservation); ?></p>
            <div><?php echo esc_attr($new_reservations); ?> <?php echo esc_attr($new_reservations_text); ?></div>
            <a class="btn btn-slim admin-top-banner-btn" href="<?php echo esc_url($reservation_page_link); ?>"><?php esc_html_e('Manage', 'homey'); ?></a>
        </div>
        <div class="block-col block-col-25">
            <h3><?php esc_html_e('Users', 'homey'); ?></h3>
            <p class="block-big-text"><?php echo esc_attr($count_users['total_users']); ?></p>
            <div><?php echo esc_attr($new_users); ?> <?php echo esc_attr($new_users_text); ?></div>
            <a class="btn btn-slim admin-top-banner-btn" href="<?php echo esc_url($users_page_link); ?>"><?php esc_html_e('Manage', 'homey'); ?></a>
        </div>
        <div class="block-col block-col-25">
            <h3><?php echo esc_html__('Earnings', 'homey'); ?></h3>
            <p class="block-big-text"><?php echo homey_formatted_price($total_website_one_week_earnings->total_website_amount); ?></p>
<!--            <div>April, 2019</div>-->
            <a class="btn btn-slim admin-top-banner-btn" href="<?php echo esc_url($host_earnings_link); ?>"><?php esc_html_e('Details', 'homey'); ?></a>
        </div>
    </div>
</div>