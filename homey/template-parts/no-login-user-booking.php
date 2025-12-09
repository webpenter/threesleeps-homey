<?php
global $current_user, $homey_local, $homey_prefix, $reservationID, $owner_info, $renter_info, $renter_id, $owner_id, $hide_labels;

$userID =   deHashNoUserId($_GET['reservation_no_userHash']);

$args = array(
    'author'         => $userID,
    'post_type'      => 'homey_reservation',
    'posts_per_page' => 1,
    'orderby'        => 'date',
    'order'          => 'DESC',
);

$query = new WP_Query($args);

if ($query->have_posts()) {
    $query->the_post();
    $reservationID = get_the_ID();
    wp_reset_postdata();
} else {
    $reservationID = 0; // Return 0 if no posts found
}

$messages_page = homey_get_template_link_2('template/dashboard-messages.php');
$booking_hide_fields = homey_option('booking_hide_fields');
$booking_detail_hide_fields = homey_option('booking_detail_hide_fields');

$reservation_status = $notification = $status_label = $notification = '';
$upfront_payment = $check_in = $check_out = $guests = $pets = $renter_msg = '';
$smoke = $pets = $party = $children = $additional_rules = '';

$payment_link = $cancellation_policy = '';
if(!empty($reservationID)) {
    if(homey_is_renter()) {
        $back_to_list = homey_get_template_link('template/dashboard-reservations.php');
    } else {
        if(!homey_listing_guest($reservationID)) {
            $back_to_list = homey_get_template_link_2('template/dashboard-reservations.php');
        } else {
            $back_to_list = homey_get_template_link_2('template/dashboard-reservations2.php');
        }
    }

    $post = get_post($reservationID);
    $current_date = date( 'Y-m-d', current_time( 'timestamp', 0 ));
    $current_date_unix = strtotime($current_date );

    $reservation_status = get_post_meta($reservationID, 'reservation_status', true);
    $total_price = get_post_meta($reservationID, 'reservation_total', true);
    $upfront_payment = get_post_meta($reservationID, 'reservation_upfront', true);
    $upfront_payment = homey_formatted_price($upfront_payment);
    $payment_link = homey_get_template_link_2('template/dashboard-payment.php');

    $check_in = get_post_meta($reservationID, 'reservation_checkin_date', true);
    $check_out = get_post_meta($reservationID, 'reservation_checkout_date', true);
    $guests = get_post_meta($reservationID, 'reservation_guests', true);
    $adult_guest = get_post_meta($reservationID, 'reservation_adult_guest', true);
    $child_guest = get_post_meta($reservationID, 'reservation_child_guest', true);
    $listing_id = get_post_meta($reservationID, 'reservation_listing_id', true);
    $pets   = get_post_meta($listing_id, $homey_prefix.'pets', true);
    $res_meta   = get_post_meta($reservationID, 'reservation_meta', true);

    $booking_type = homey_booking_type_by_id($listing_id);

    $extra_expenses = homey_get_extra_expenses($reservationID);
    $extra_discount = homey_get_extra_discount($reservationID);

    if(!empty($extra_expenses)) {
        $expenses_total_price = $extra_expenses['expenses_total_price'];
        $total_price = $total_price + $expenses_total_price;
    }

    if(!empty($extra_discount)) {
        $discount_total_price = $extra_discount['discount_total_price'];
        $total_price = $total_price - $discount_total_price;
    }

    if(homey_option('reservation_payment') == 'full') {
        $upfront_payment = homey_formatted_price($total_price);
    }

    $renter_msg = isset($res_meta['renter_msg']) ? $res_meta['renter_msg'] : '';

    $renter_id = get_post_meta($reservationID, 'listing_renter', true);
    $renter_info = homey_get_author_by_id('60', '60', 'reserve-detail-avatar img-circle', $renter_id);

    $renter_nickname  = get_user_meta($renter_id, 'nickname', true);
    $renter_name_while_booking  = get_user_meta($renter_id, 'first_name', true);
    $renter_name_while_booking .= ' '.get_user_meta($renter_id, 'last_name', true);
    if(trim(empty($renter_name_while_booking))){
        $renterwhile_booking = get_userdata($renter_id);
        $renter_name_while_booking = $renterwhile_booking->display_name;
    }

    if(empty(trim($renter_name_while_booking))){
        $renter_name_while_booking = explode('@', $renter_nickname);
        $renter_name_while_booking = isset($renter_name_while_booking[0]) ? $renter_name_while_booking[0] : esc_html__('No Information', 'homey');
    }

    $renter_phone = get_user_meta($renter_id, 'phone', true);

    $owner_id = get_post_meta($reservationID, 'listing_owner', true);
    $owner_info = homey_get_author_by_id('60', '60', 'reserve-detail-avatar img-circle', $owner_id);

    $payment_link = add_query_arg( array(
        'reservation_id' => $reservationID,
    ), $payment_link );

    $chcek_reservation_thread = homey_chcek_reservation_thread($reservationID);

    if($chcek_reservation_thread != '') {
        $messages_page_link = add_query_arg( array(
            'thread_id' => $chcek_reservation_thread
        ), $messages_page );
    } else {
        $messages_page_link = add_query_arg( array(
            'reservation_id' => $reservationID,
            'message' => 'new',
        ), $messages_page );
    }

    $guests_label = homey_option('cmn_guest_label');
    if($guests > 1) {
        $guests_label = homey_option('cmn_guests_label');
    }

    $smoke            = homey_get_listing_data('smoke', $listing_id);
    $pets             = homey_get_listing_data('pets', $listing_id);
    $party            = homey_get_listing_data('party', $listing_id);
    $children         = homey_get_listing_data('children', $listing_id);
    $additional_rules = homey_get_listing_data('additional_rules', $listing_id);
    $cancellation_policy = get_post_meta($listing_id, $homey_prefix.'cancellation_policy', true);


    if($smoke != 1) {
        $smoke_allow = 'homey-icon homey-icon-arrow-right-1';
        $smoke_text = esc_html__(homey_option('sn_text_no'), 'homey');
    } else {
        $smoke_allow = 'homey-icon homey-icon-arrow-right-1';
        $smoke_text = esc_html__(homey_option('sn_text_yes'), 'homey');
    }

    if($pets != 1) {
        $pets_allow = 'homey-icon homey-icon-arrow-right-1';
        $pets_text = esc_html__(homey_option('sn_text_no'), 'homey');
    } else {
        $pets_allow = 'homey-icon homey-icon-arrow-right-1';
        $pets_text = esc_html__(homey_option('sn_text_yes'), 'homey');
    }

    if($party != 1) {
        $party_allow = 'homey-icon homey-icon-arrow-right-1';
        $party_text = esc_html__(homey_option('sn_text_no'), 'homey');
    } else {
        $party_allow = 'homey-icon homey-icon-arrow-right-1';
        $party_text = esc_html__(homey_option('sn_text_yes'), 'homey');
    }

    if($children != 1) {
        $children_allow = 'homey-icon homey-icon-arrow-right-1';
        $children_text = esc_html__(homey_option('sn_text_no'), 'homey');
    } else {
        $children_allow = 'homey-icon homey-icon-arrow-right-1';
        $children_text = esc_html__(homey_option('sn_text_yes'), 'homey');
    }

    if(!empty($cancellation_policy)){
        $cancellation_policy   = get_the_content( '', '',  $cancellation_policy ); // Where $cancellation_policy is the ID
    }else{
        $cancellation_policy = '';
    }

}

if( 1==2 && !homey_give_access($reservationID) ) {
    echo('Are you kidding?');

} else {
    ?>
    <div class="user-dashboard-right dashboard-with-sidebar">
        <div class="dashboard-content-area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="dashboard-area">
                            <input type="hidden" id="resrv_id" value="<?php echo intval($reservationID); ?>">
                            <?php homey_reservation_notification($reservation_status, $reservationID); ?>

                            <div class="block">
                                <div class="block-head">
                                    <div class="block-left">
                                        <h2 class="title"><?php echo esc_attr($homey_local['reservation_label']); ?>
                                            <?php $wc_order_id = get_wc_order_id(get_the_ID()); $wc_order_id_txt = $wc_order_id > 0 ? ', wc#'.$wc_order_id.' ' : ' '; ?>
                                            <?php echo '#'.$reservationID.$wc_order_id_txt.' '.homey_get_reservation_label($reservation_status, $reservationID); ?></h2>
                                    </div><!-- block-left -->
                                </div><!-- block-head -->

                                <?php
                                $res_no_of_days = isset($res_meta['no_of_days']) ? $res_meta['no_of_days'] : 0;

                                if($res_no_of_days > 1) {
                                    $night_label = ($booking_type == 'per_day_date') ? homey_option('glc_day_dates_label') : homey_option('glc_day_nights_label');
                                } else {
                                    $night_label = ($booking_type == 'per_day_date') ? homey_option('glc_day_date_label') : homey_option('glc_day_night_label');
                                }

                                $no_of_weeks = isset($res_meta['total_weeks_count']) ? $res_meta['total_weeks_count'] : 0;
                                $no_of_months = isset($res_meta['total_months_count']) ? $res_meta['total_months_count'] : 0;

                                if($no_of_weeks > 1) {
                                    $week_label = homey_option('glc_weeks_label');
                                } else {
                                    $week_label = homey_option('glc_week_label');
                                }

                                if($no_of_months > 1) {
                                    $month_label = homey_option('glc_months_label');
                                } else {
                                    $month_label = homey_option('glc_month_label');
                                }

                                ?>

                                <div class="block-section">
                                    <div class="block-body">
                                        <div class="block-left">
                                            <ul class="detail-list">
                                                <li><strong><?php echo esc_attr($homey_local['date_label']); ?>:</strong></li>
                                                <li><?php echo translate_month_names(esc_attr( get_the_date( get_option( 'date_format' ), $reservationID )));?>
                                                    <br>
                                                    <?php echo esc_attr( get_the_date( homey_time_format(), $reservationID ));?> </li>
                                            </ul>
                                        </div><!-- block-left -->
                                        <div class="block-right">
                                            <?php if(!empty($renter_info['photo'])) {
                                                echo '<a href="'.esc_url($renter_info['link']).'" target="_blank">'.$renter_info['photo'].'</a>';
                                            }?>
                                            <ul class="detail-list">
                                                <li><strong><?php esc_html_e('From', 'homey'); ?>:</strong>
                                                    <a href="<?php echo esc_url($renter_info['link']); ?>" target="_blank">
                                                        <?php echo esc_html__(esc_attr($renter_info['name']), 'homey'); ?>
                                                    </a>
                                                </li>
                                                <?php if($booking_detail_hide_fields['renter_information_on_detail'] == 0){ ?>
                                                <li><strong><?php esc_html_e('Renter Detail', 'homey'); ?>:&nbsp;</strong><?php echo esc_attr($renter_name_while_booking).' <a title="'.esc_html__('Click to call', 'homey').'" href="tel:'.$renter_phone.'">'. $renter_phone.'</a></li>'; ?>
                                                    <?php } ?>
                                                <li><strong><?php esc_html_e('Listing Name', 'homey'); ?>:&nbsp;</strong><?php echo get_the_title($listing_id); ?></li>
                                            </ul>
                                        </div><!-- block-right -->
                                    </div><!-- block-body -->
                                </div><!-- block-section -->

                                <div class="block-section">
                                    <div class="block-body">
                                        <div class="block-left">
                                            <h2 class="title"><?php esc_html_e('Details', 'homey'); ?></h2>
                                        </div><!-- block-left -->
                                        <div class="block-right">
                                            <ul class="detail-list detail-list-2-cols">
                                                <li>
                                                    <?php echo esc_html__(esc_attr($homey_local['check_In']), 'homey'); ?>:
                                                    <strong><?php echo homey_format_date_simple($check_in); ?></strong>
                                                </li>
                                                <li>
                                                    <?php echo esc_html__(esc_attr($homey_local['check_Out']), 'homey'); ?>:
                                                    <strong><?php echo homey_format_date_simple($check_out); ?></strong>
                                                </li>

                                                <?php
                                                if( $booking_type == 'per_week' ) { ?>

                                                    <li>
                                                        <?php echo esc_html__(esc_attr($week_label), 'homey'); ?>:
                                                        <strong><?php echo esc_attr($no_of_weeks); ?>

                                                            <?php
                                                            if( $res_no_of_days > 0 ) {
                                                                echo esc_html__('and', 'homey').' '.esc_attr($res_no_of_days).' '.esc_attr($night_label);
                                                            }?>

                                                        </strong>
                                                    </li>

                                                <?php } else if( $booking_type == 'per_month' ) { ?>

                                                    <li>
                                                        <?php echo esc_html__(esc_attr($month_label), 'homey'); ?>:
                                                        <strong><?php echo esc_attr($no_of_months); ?>

                                                            <?php
                                                            if( $res_no_of_days > 0 ) {
                                                                echo esc_html__('and', 'homey').' '.esc_attr($res_no_of_days).' '.esc_attr($night_label);
                                                            }?>

                                                        </strong>
                                                    </li>

                                                <?php } else if( $booking_type == 'per_day_date' ) { ?>
                                                    <li>
                                                        <?php echo esc_html__(esc_attr($night_label), 'homey'); ?>:
                                                        <strong><?php echo esc_attr($res_no_of_days); ?></strong>
                                                    </li>
                                                <?php } else { ?>
                                                    <li>
                                                        <?php echo esc_html__(esc_attr($night_label), 'homey'); ?>:
                                                        <strong><?php echo esc_attr($res_no_of_days); ?></strong>
                                                    </li>
                                                <?php } ?>

                                                <?php if($booking_hide_fields['guests'] != 1) {?>
                                                    <li>
                                                        <?php echo esc_html__(esc_attr($guests_label), 'homey'); ?>:
                                                        <strong><?php echo esc_attr($guests); echo ' (';echo esc_html__('Adults:', 'homey').' '.esc_attr($adult_guest); echo esc_html__(', ', 'homey'); echo esc_html__('Childrens:', 'homey').' '.esc_attr($child_guest).')'; ?></strong>
                                                    </li>
                                                <?php } ?>

                                                <?php if(!empty($res_meta['additional_guests'])) { ?>
                                                    <li>
                                                        <?php echo esc_html__(esc_attr($homey_local['addinal_guest_text']), 'homey'); ?>:
                                                        <strong><?php echo esc_attr($res_meta['additional_guests']); ?></strong>
                                                    </li>
                                                <?php } ?>

                                            </ul>
                                            <!--cancel pollicy and rules-->
                                            <ul class="rules_list detail-list">
                                                <?php if(@$hide_labels['sn_smoking_allowed'] != 1) { ?>
                                                    <li>
                                                        <i class="<?php echo esc_attr($smoke_allow); ?>" aria-hidden="true"></i>
                                                        <?php echo esc_attr(homey_option('sn_smoking_allowed')); ?>:
                                                        <strong><?php echo esc_attr($smoke_text); ?></strong>
                                                    </li>
                                                <?php } ?>

                                                <?php if(@$hide_labels['sn_pets_allowed'] != 1) { ?>
                                                    <li>
                                                        <i class="<?php echo esc_attr($pets_allow); ?>" aria-hidden="true"></i>
                                                        <?php echo esc_attr(homey_option('sn_pets_allowed')); ?>:
                                                        <strong><?php echo esc_attr($pets_text); ?></strong>
                                                    </li>
                                                <?php } ?>

                                                <?php if(@$hide_labels['sn_party_allowed'] != 1) { ?>
                                                    <li>
                                                        <i class="<?php echo esc_attr($party_allow); ?>" aria-hidden="true"></i>
                                                        <?php echo esc_attr(homey_option('sn_party_allowed')); ?>:
                                                        <strong><?php echo esc_attr($party_text); ?></strong>
                                                    </li>
                                                <?php } ?>

                                                <?php if(@$hide_labels['sn_children_allowed'] != 1) { ?>
                                                    <li>
                                                        <i class="<?php echo esc_attr($children_allow); ?>" aria-hidden="true"></i>
                                                        <?php echo esc_attr(homey_option('sn_children_allowed')); ?>:
                                                        <strong><?php echo esc_attr($children_text); ?></strong>
                                                    </li>
                                                <?php } ?>
                                            </ul>

                                            <?php if($cancellation_policy != ''){ ?>
                                                <h3><?php echo esc_attr($homey_local['cancel_policy']); ?></h3>
                                                <p><?php echo $cancellation_policy; ?></p>
                                            <?php } ?>
                                            <?php if(!empty($additional_rules) && @$hide_labels['sn_add_rules_info'] != 1) { ?>
                                                <div><strong><?php echo esc_attr(homey_option('sn_add_rules_info')); ?></strong></div>
                                                <div><?php echo ''.($additional_rules); ?></div>
                                            <?php } ?>
                                            <!--cancel pollicy and rules-->

                                        </div><!-- block-right -->
                                    </div><!-- block-body -->
                                </div><!-- block-section -->

                                <?php if(!empty($renter_msg)) { ?>
                                    <div class="block-section">
                                        <div class="block-body">
                                            <div class="block-left">
                                                <h2 class="title"><?php esc_html_e('Notes', 'homey'); ?></h2>
                                            </div><!-- block-left -->
                                            <div class="block-right">
                                                <p><?php echo esc_attr($renter_msg); ?></p>
                                            </div><!-- block-right -->
                                        </div><!-- block-body -->
                                    </div><!-- block-section -->
                                <?php } ?>

                                <div class="block-section" data-reservation-payment-detail-section="1">
                                    <div class="block-body">
                                        <div class="block-left">
                                            <h2 class="title"><?php echo esc_html__(esc_attr($homey_local['payment_label']), 'homey'); ?></h2>
                                        </div><!-- block-left -->
                                        <div class="block-right">
                                            <?php
                                            if($booking_type == 'per_day_date'){
                                                echo homey_calculate_reservation_cost_day_date($reservationID);
                                            }else{
                                                echo homey_calculate_reservation_cost($reservationID);
                                            }
                                            ?>
                                        </div><!-- block-right -->
                                    </div><!-- block-body -->
                                </div><!-- block-section -->
                            </div><!-- .block -->
                            <div class="payment-buttons">
                                <?php homey_reservation_action($reservation_status, $upfront_payment, $payment_link, $reservationID, 'btn-half-width'); ?>
                            </div>
                        </div><!-- .dashboard-area -->
                    </div><!-- col-lg-12 col-md-12 col-sm-12 -->
                </div>
            </div><!-- .container-fluid -->
        </div><!-- .dashboard-content-area -->
        <aside class="dashboard-sidebar">
            <?php get_template_part('template-parts/dashboard/reservation/payment-sidebar', '', array("booking_type", $booking_type)); ?>
        </aside><!-- .dashboard-sidebar -->
    </div><!-- .user-dashboard-right -->
    <?php get_template_part('template-parts/dashboard/reservation/message'); ?>
<?php } ?>
