<?php
global $post, $current_user, $homey_prefix, $homey_local, $listing_id;
wp_get_current_user();

$listing_id = $post->ID;
$price_per_night = get_post_meta($listing_id, $homey_prefix . 'night_price', true);
$instant_booking = get_post_meta($listing_id, $homey_prefix . 'instant_booking', true);
$offsite_payment = homey_option('off-site-payment');

$prefilled = homey_get_dates_for_booking();

$key = '';
$userID = $current_user->ID;
$fav_option = 'homey_favorites-' . $userID;
$fav_option = get_option($fav_option);

if (!empty($fav_option)) {
    $key = array_search($post->ID, $fav_option);
}

$price_separator = homey_option('currency_separator');

if ($key != false || $key != '') {
    $favorite = $homey_local['remove_favorite'];
    $heart = 'homey-icon-love-it-full-01';
} else {
    $favorite = $homey_local['add_favorite'];
    $heart = 'homey-icon-love-it'; //homey-icon-love-it //fa-heart-o
}

$listing_price = homey_get_price();

$no_login_needed_for_booking = homey_option('no_login_needed_for_booking');
$multiroom_booking = get_post_meta($listing_id, 'homey_multiroom_booking', true);
$accomodation = homey_get_listing_data('accomodation');
?>
<div id="homey_remove_on_mobile" class="sidebar-booking-module hidden-sm hidden-xs">
    <div class="block">
        <div class="sidebar-booking-module-header">
            <div class="block-body-sidebar">

                <?php
                if($multiroom_booking === 'per_day_multi'){
                    echo '<span class="item-price multi_room">' . esc_html__("Select Dates and Rooms", 'homey') . '</span>';
                } else if (!empty($listing_price)) { ?>

                    <span class="item-price">
					<?php
                    echo homey_formatted_price($listing_price, false, true); ?><sub><?php echo esc_attr($price_separator); ?><?php echo homey_get_price_label(); ?></sub>
					</span>

                <?php } else {
                    echo '<span class="item-price free">' . esc_html__('Free', 'homey') . '</span>';
                } ?>

            </div><!-- block-body-sidebar -->
        </div><!-- sidebar-booking-module-header -->
        <div class="sidebar-booking-module-body">
            <div class="homey_notification block-body-sidebar">

                <?php
                if (homey_affiliate_booking_link()) { ?>

                    <a href="<?php echo homey_affiliate_booking_link(); ?>" target="_blank"
                       class="btn btn-full-width btn-primary"><?php echo esc_html__('Book Now', 'homey'); ?></a>

                    <?php
                } else { ?>
                    <div id="single-listing-date-range" class="search-date-range">
                        <div class="search-date-range-arrive">
                            <input name="arrive" value="<?php echo esc_attr($prefilled['arrive']); ?>" readonly
                                   type="text" class="form-control check_in_date" autocomplete="off"
                                   placeholder="<?php echo esc_attr(homey_option('srh_arrive_label')); ?>">
                        </div>
                        <div class="search-date-range-depart">
                            <input name="depart" value="<?php echo esc_attr($prefilled['depart']); ?>" readonly
                                   type="text" class="form-control check_out_date" autocomplete="off"
                                   placeholder="<?php echo esc_attr(homey_option('srh_depart_label')); ?>">
                        </div>

                        <div id="single-booking-search-calendar"
                             class="search-calendar single-listing-booking-calendar-js clearfix" style="display: none;">
                            <?php
                            $booking_type = homey_booking_type_by_id($listing_id);
                            if ($booking_type == 'per_day_date') {
                                homeyAvailabilityCalendarDayDate();
                            } else {
                                homeyAvailabilityCalendar();
                            }
                            ?>

                            <div class="calendar-navigation custom-actions">
                                <button class="listing-cal-prev btn btn-action pull-left disabled"><i
                                            class="homey-icon homey-icon-arrow-left-1" aria-hidden="true"></i></button>
                                <button class="listing-cal-next btn btn-action pull-right"><i
                                            class="homey-icon homey-icon-arrow-right-1" aria-hidden="true"></i></button>
                            </div><!-- calendar-navigation -->
                        </div>
                    </div>

                    <?php
                    if($multiroom_booking != 'per_day_multi'){
                        get_template_part('single-listing/booking/guests');
                        get_template_part('single-listing/booking/extra-prices');
                    } else {
                         if (!empty($accomodation)) : ?>
                            <div class="multi_room_select">
                                <select name="rooms[]"
                                        class="selectpicker listing_multirooms"
                                        multiple
                                        data-live-search="true"
                                        data-actions-box="true"
                                        title="<?php esc_html_e('Select Rooms', 'homey'); ?>">

                                    <?php foreach ($accomodation as $acc) : 
                                        $room_name   = $acc['acc_bedroom_name'] ?? '';
                                        $night_price = $acc['night_price'] ?? 0;
                                        $room_id     = $acc['room_id'] ?? 0;
                                    ?>

                                        <option value="<?php echo esc_attr($room_id); ?>"
                                            data-content="
                                                <div style='display:flex; justify-content:space-between; width:100%;'>
                                                    <span><?php echo esc_html($room_name); ?></span>
                                                    <strong><?php echo esc_html(homey_formatted_price($night_price)); ?></strong>
                                                </div>
                                            ">
                                            <?php echo esc_html($room_name . ' ' . homey_formatted_price($night_price)); ?>
                                        </option>

                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; 
                    }
                    ?>

                    <?php if ($offsite_payment == 0) { ?>
                        <div class="search-message">
                            <textarea name="guest_message" class="form-control" rows="3"
                                      placeholder="<?php echo esc_html__('Introduce yourself to the host', 'homey'); ?>"></textarea>
                        </div>
                    <?php } ?>

                    <div class="homey_preloader">
                        <?php get_template_part('template-parts/spinner'); ?>
                    </div>
                    <div id="homey_booking_cost" class="payment-list"></div>

                    <input type="hidden" name="listing_id" id="listing_id" value="<?php echo intval($listing_id); ?>">
                    <input type="hidden" name="multi_listing" id="multi_listing" value="<?php echo $multiroom_booking; ?>">

                    <input type="hidden" name="reservation-security" id="reservation-security"
                           value="<?php echo wp_create_nonce('reservation-security-nonce'); ?>"/>

                    <?php if ($instant_booking && $offsite_payment == 0) { ?>
                         <?php if (!is_user_logged_in() && $no_login_needed_for_booking == 'yes') { ?>
                            <div class="new_reser_request_user_email ">
                                <input id="new_reser_request_user_email" name="new_reser_request_user_email"
                                    required="required"
                                    value="<?php echo esc_attr($prefilled['new_reser_request_user_email']); ?>"
                                    type="email" class="form-control new_reser_request_user_email"
                                    placeholder="<?php echo esc_html__('Your email', 'homey'); ?>">
                            </div>
                         <?php } ?> 
                        <button id="instance_reservation" type="button"
                                class="btn btn-full-width btn-primary"><?php echo esc_html__('Instant Booking', 'homey'); ?></button>
                               
                    <?php } else { ?>
                        <?php if (!is_user_logged_in() && $no_login_needed_for_booking == 'yes') { ?>
                            <div class="new_reser_request_user_email ">
                                <input id="new_reser_request_user_email" name="new_reser_request_user_email"
                                       required="required"
                                       value="<?php echo esc_attr($prefilled['new_reser_request_user_email']); ?>"
                                       type="email" class="form-control new_reser_request_user_email"
                                       placeholder="<?php echo esc_html__('Your email', 'homey'); ?>">
                            </div>
                        <?php } ?> 
                        <button id="request_for_reservation" type="button"
                                class="btn btn-full-width btn-primary"><?php echo esc_html__('Request to Book', 'homey'); ?></button>
                        <div class="text-center text-small"><i
                                    class="homey-icon homey-icon-information-circle"></i> <?php echo esc_html__("You won't be charged yet", 'homey'); ?>
                        </div>
                    <?php } ?>
                <?php } ?>

            </div><!-- block-body-sidebar -->
        </div><!-- sidebar-booking-module-body -->

    </div><!-- block -->
</div><!-- sidebar-booking-module -->
<div class="sidebar-booking-module-footer">
    <div class="block-body-sidebar">

        <?php if (homey_option('detail_favorite') != 0) { ?>
            <button type="button" data-listid="<?php echo intval($post->ID); ?>"
                    class="add_fav btn btn-full-width btn-grey-outlined"><i class="homey-icon <?php echo esc_attr($heart); ?>"
                                                                            aria-hidden="true"></i> <?php echo esc_attr($favorite); ?>
            </button>
        <?php } ?>

        <?php if (homey_option('detail_contact_form') != 0 && homey_option('hide-host-contact') != 1) { ?>
            <button type="button" data-toggle="modal" data-target="#modal-contact-host"
                    class="btn btn-full-width btn-grey-outlined"><?php echo esc_attr($homey_local['pr_cont_host']); ?></button>
        <?php } ?>

        <?php if (homey_option('print_button') != 0) { ?>
            <button type="button" id="homey-print" class="homey-print btn btn-full-width btn-blank"
                    data-listing-id="<?php echo intval($listing_id); ?>">
                <i class="homey-icon homey-icon-print-text" aria-hidden="true"></i> <?php echo esc_attr($homey_local['print_label']); ?>
            </button>
        <?php } ?>
    </div><!-- block-body-sidebar -->

    <?php
    if (homey_option('detail_share') != 0) {
        get_template_part('single-listing/share');
    }
    ?>
</div><!-- sidebar-booking-module-footer -->
