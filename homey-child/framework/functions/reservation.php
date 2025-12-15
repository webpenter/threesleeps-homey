<?php

add_action('wp_ajax_nopriv_homey_add_reservation', 'homey_add_reservation');
add_action('wp_ajax_homey_add_reservation', 'homey_add_reservation');
if (!function_exists('homey_add_reservation')) {
    function homey_add_reservation()
    {
        global $current_user;
        $local = homey_get_localization();
        $no_logged_in_user = false;
        $admin_email = get_option('new_admin_email');
        $admin_email = empty($admin_email) ? get_option( 'admin_email' ) : $admin_email;

        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $no_login_needed_for_booking = homey_option('no_login_needed_for_booking');

        if ($no_login_needed_for_booking != "yes" && !isset($_REQUEST['new_reser_request_user_email'])) {
            //check security
            $nonce = $_REQUEST['security'];
            if (!wp_verify_nonce($nonce, 'reservation-security-nonce')) {

                echo json_encode(
                    array(
                        'success' => false,
                        'message' => $local['security_check_text']
                    )
                );
                wp_die();
            }
        }

        if ($current_user->ID == 0 && $no_login_needed_for_booking == "yes" && isset($_REQUEST['new_reser_request_user_email'])) {
            $email = trim($_REQUEST['new_reser_request_user_email']);

            if (empty($email)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => esc_html__('Enter email address', 'homey')
                    )
                );
                wp_die();
            }

            $user = get_user_by('email', $email);

            if (isset($user->ID)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => esc_html__('This email already registered, please login first, or try with new email.', 'homey')
                    )
                );
                wp_die();

                //add_filter('authenticate', 'for_reservation_nop_auto_login', 3, 10);
                //for_reservation_nop_auto_login($user);
            } else { //create user from email
                $user_login = $email;
                $no_logged_in_user = true;
                $display_name  = $email;
                $nickname   = $email; ;
                $first_name   = "New" ;
                $last_name   = "User" ;
                $description    = "New User";

                $user_email = $email;
                $role = 'homey_renter';
                $user_pass = wp_generate_password(8, false);
                $userdata = compact('user_login', 'user_email', 'user_pass', 'role', 'display_name', 'nickname', 'first_name', 'last_name', 'description');
                $new_user_id = wp_insert_user($userdata);

                if ($new_user_id > 0) {
                    homey_wp_new_user_notification($new_user_id, $user_pass);
                }

                update_user_meta($new_user_id, 'viaphp', 1);

                // log in automatically
                if (!is_user_logged_in()) {
                    $user = get_user_by('email', $email);

                    add_filter('authenticate', 'for_reservation_nop_auto_login', 3, 10);
                    for_reservation_nop_auto_login($user);
                }
            }
        }

        $current_user = wp_get_current_user();
        $userID = $current_user->ID;

        $local = homey_get_localization();
        $allowded_html = array();
        $reservation_meta = array();

        $listing_id = intval($_POST['listing_id']);
        $listing_owner_id = get_post_field('post_author', $listing_id);
        $check_in_date = wp_kses($_POST['check_in_date'], $allowded_html);
        $check_out_date = wp_kses($_POST['check_out_date'], $allowded_html);
        $extra_options = isset($_POST['extra_options']) ? $_POST['extra_options'] : '';
        $guest_message = stripslashes($_POST['guest_message']);
        $guests = intval($_POST['guests']);
        $adult_guest = isset($_POST['adult_guest']) ? intval($_POST['adult_guest']) : 0;
        $child_guest = isset($_POST['child_guest']) ? intval($_POST['child_guest']) : 0;
        $title = $local['reservation_text'];

        $booking_type = homey_booking_type_by_id($listing_id);

        $owner = homey_usermeta($listing_owner_id);
        $owner_email = $owner['email'];

        if (!is_user_logged_in() || $userID === 0) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['login_for_reservation']
                )
            );
            wp_die();
        }

        $booking_hide_fields = homey_option('booking_hide_fields');
        if (empty($guests) && $booking_hide_fields['guests'] != 1) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['choose_guests']
                )
            );
            wp_die();
        }

        if ($userID == $listing_owner_id) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['own_listing_error']
                )
            );
            wp_die();
        }

        if ($booking_type == "per_day_date" && strtotime($check_out_date) < strtotime($check_in_date)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['ins_book_proceed']
                )
            );
            wp_die();
        }

        if ($booking_type != "per_day_date" && strtotime($check_out_date) <= strtotime($check_in_date)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['dates_not_available']
                )
            );
            wp_die();
        }

        $check_availability = check_booking_availability($check_in_date, $check_out_date, $listing_id, $guests);
        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if ($is_available) {

            if ($booking_type == 'per_week') {
                $prices_array = homey_get_weekly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

                $price_per_week = $prices_array['price_per_week'];
                $weeks_total_price = $prices_array['weeks_total_price'];
                $total_weeks_count = $prices_array['total_weeks_count'];

                $reservation_meta['price_per_week'] = $price_per_week;
                $reservation_meta['weeks_total_price'] = $weeks_total_price;
                $reservation_meta['total_weeks_count'] = $total_weeks_count;
                $reservation_meta['reservation_listing_type'] = 'per_week';

            } else if ($booking_type == 'per_month') {
                $prices_array = homey_get_monthly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

                $price_per_month = $prices_array['price_per_month'];
                $months_total_price = $prices_array['months_total_price'];
                $total_months_count = $prices_array['total_months_count'];

                $reservation_meta['price_per_month'] = $price_per_month;
                $reservation_meta['months_total_price'] = $months_total_price;
                $reservation_meta['total_months_count'] = $total_months_count;
                $reservation_meta['reservation_listing_type'] = 'per_month';

            } else if ($booking_type == 'per_day_date') {

                $prices_array = homey_get_day_date_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
                $price_per_night = $prices_array['price_per_day_date'];
                $nights_total_price = $prices_array['nights_total_price'];

                $reservation_meta['price_per_day_date'] = $price_per_night;
                $reservation_meta['price_per_night'] = $price_per_night;
                $reservation_meta['days_total_price'] = $nights_total_price;
                $reservation_meta['reservation_listing_type'] = 'per_day_date';
            } else {

                $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
                $price_per_night = $prices_array['price_per_night'];
                $nights_total_price = $prices_array['nights_total_price'];
                $booking_fee =  $prices_array['booking_fee'];
                $booking_fee_title =  $prices_array['booking_fee_title'];
                $reservation_meta['booking_fee'] = $booking_fee;
                $reservation_meta['booking_fee_title'] = $booking_fee_title;
                $reservation_meta['price_per_night'] = $price_per_night;
                $reservation_meta['nights_total_price'] = $nights_total_price;
                $reservation_meta['reservation_listing_type'] = 'per_night';
            }

            $reservation_meta['no_of_days'] = $prices_array['days_count'] = $booking_type == 'per_day_date' ? $prices_array['days_count'] : $prices_array['days_count'];
            $reservation_meta['additional_guests'] = $prices_array['additional_guests'];

            $upfront_payment = $prices_array['upfront_payment'];
            $balance = $prices_array['balance'];
            $total_price = $prices_array['total_price'];
            $cleaning_fee = $prices_array['cleaning_fee'];
            $city_fee = $prices_array['city_fee'];
            $services_fee = $prices_array['services_fee'];
            $days_count = $prices_array['days_count'];
            $period_days = $prices_array['period_days'];
            $taxes = $prices_array['taxes'];
            $taxes_percent = $prices_array['taxes_percent'];
            $security_deposit = $prices_array['security_deposit'];
            $additional_guests = $prices_array['additional_guests'];
            $additional_guests_price = $prices_array['additional_guests_price'];
            $additional_guests_total_price = $prices_array['additional_guests_total_price'];
            $booking_has_weekend = $prices_array['booking_has_weekend'];
            $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];

            $reservation_meta['check_in_date'] = $check_in_date;
            $reservation_meta['check_out_date'] = $check_out_date;
            $reservation_meta['guests'] = $guests;
            $reservation_meta['adult_guest'] = $adult_guest;
            $reservation_meta['child_guest'] = $child_guest;
            $reservation_meta['listing_id'] = $listing_id;
            $reservation_meta['upfront'] = $upfront_payment;
            $reservation_meta['balance'] = $balance;
            $reservation_meta['total'] = $total_price;

            $reservation_meta['cleaning_fee'] = $cleaning_fee;
            $reservation_meta['city_fee'] = $city_fee;
            $reservation_meta['services_fee'] = $services_fee;
            $reservation_meta['period_days'] = $period_days;
            $reservation_meta['taxes'] = $taxes;
            $reservation_meta['taxes_percent'] = $taxes_percent;
            $reservation_meta['security_deposit'] = $security_deposit;
            $reservation_meta['additional_guests_price'] = $additional_guests_price;
            $reservation_meta['additional_guests_total_price'] = $additional_guests_total_price;
            $reservation_meta['booking_has_weekend'] = $booking_has_weekend;
            $reservation_meta['booking_has_custom_pricing'] = $booking_has_custom_pricing;

            $reservation = array(
                'post_title' => $title,
                'post_status' => 'publish',
                'post_type' => 'homey_reservation',
                'post_author' => $userID
            );
            $reservation_id = wp_insert_post($reservation);

            $reservation_update = array(
                'ID' => $reservation_id,
                'post_title' => $title . ' ' . $reservation_id
            );
            wp_update_post($reservation_update);

            update_post_meta($reservation_id, 'reservation_listing_id', $listing_id);
            update_post_meta($reservation_id, 'listing_owner', $listing_owner_id);
            update_post_meta($reservation_id, 'listing_renter', $userID);
            update_post_meta($reservation_id, 'reservation_checkin_date', $check_in_date);
            update_post_meta($reservation_id, 'reservation_checkout_date', $check_out_date);
            update_post_meta($reservation_id, 'reservation_guests', $guests);
            update_post_meta($reservation_id, 'reservation_adult_guest', $adult_guest);
            update_post_meta($reservation_id, 'reservation_child_guest', $child_guest);
            update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
            update_post_meta($reservation_id, 'reservation_status', 'under_review');
            update_post_meta($reservation_id, 'is_hourly', 'no');
            update_post_meta($reservation_id, 'extra_options', $extra_options);

            update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
            update_post_meta($reservation_id, 'reservation_balance', $balance);
            update_post_meta($reservation_id, 'reservation_total', $total_price);
            update_post_meta($reservation_id, 'no_logged_in_user', $no_logged_in_user);
            
            if ($booking_type == 'per_day_date') {
                $pending_dates_array = homey_get_booking_pending_date_days($listing_id);
            } else {
                $pending_dates_array = homey_get_booking_pending_days($listing_id);
            }

            update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

            echo json_encode(
                array(
                    'success' => true,
                    'message' => $local['request_sent']
                )
            );

            $guest_message = empty($guest_message) ? esc_html__("To send another message, click on view.", "homey") : $guest_message;

            if (!empty(trim($guest_message))) {
                do_action('homey_create_messages_thread', $guest_message, $reservation_id);
            }

            $message_link = homey_thread_link_after_reservation($reservation_id);

            $user_info = get_userdata($userID);
            $renter_email = '';
            if ($user_info) {
                $renter_email = $user_info->user_email;
            }

            $email_args = array(
                'reservation_detail_url' => reservation_detail_link($reservation_id),
                'check_in_date' => $check_in_date,
                'check_out_date' => $check_out_date,
                'guests' => $guests,
                'adult_guest' => $adult_guest,
                'child_guest' => $child_guest,
                'total_price' => $total_price,
                'renter_email' => $renter_email,
                'guest_message' => $guest_message,
                'message_link' => $message_link,
                'booking_fee' => $booking_fee
            );

            if ($owner_email != $admin_email) {
                homey_email_composer($owner_email, 'new_reservation', $email_args);
            }

            homey_email_composer($admin_email, 'new_reservation', $email_args);

            $reservation_page = homey_get_template_link_dash('template/dashboard-reservations2.php');
            $reservation_detail_link = add_query_arg('reservation_detail', $reservation_id, $reservation_page);

            $user_info = get_userdata($userID);
            $renter_email = '';
            if ($user_info) {
                $renter_email = $user_info->user_email;
            }

            $email_args = array(
                'reservation_detail_url' => $reservation_detail_link,
                'check_in_date' => $check_in_date,
                'check_out_date' => $check_out_date,
                'guests' => $guests,
                'adult_guest' => $adult_guest,
                'child_guest' => $child_guest,
                'total_price' => $total_price,
                'renter_email' => $renter_email,
                'guest_message' => $guest_message,
                'message_link' => $message_link,
                'booking_fee' => $booking_fee 

            );

            homey_email_composer($current_user->user_email, 'new_reservation_sent', $email_args);

            wp_die();

        } else { // end $check_availability
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $check_message
                )
            );
            wp_die();
        }

    }
}

if (!function_exists('homey_calculate_booking_cost_ajax_nightly')) {
    function homey_calculate_booking_cost_ajax_nightly($listing_id, $check_in_date, $check_out_date, $guests, $extra_options)
    {

        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

        $nights_total_price_li_html = $prices_array['nights_total_price_li_html'];
        $price_per_night = homey_formatted_price($prices_array['price_per_night'], true);
        $no_of_days = $prices_array['days_count'];

        $nights_total_price = homey_formatted_price($prices_array['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $booking_fee =  $prices_array['booking_fee'];
        $booking_fee_title = $prices_array['booking_fee_title'];
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        $extra_prices_html = $prices_array['extra_prices_html'];
        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        if ($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if ($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $output = '<div class="payment-list-price-detail clearfix">';

        if (isset($prices_array['breakdown_price'])) {
            $output .= '<div style="display:none;">' . $prices_array['breakdown_price'] . '</div>';
        }

        $output .= '<div class="pull-left">';
        $output .= '<div class="payment-list-price-detail-total-price">' . esc_attr($local['cs_total']) . '</div>';
        $output .= '<div class="payment-list-price-detail-note">' . esc_attr($local['cs_tax_fees']) . '</div>';
        $output .= '</div>';

        $output .= '<div class="pull-right text-right">';
        $output .= '<div class="payment-list-price-detail-total-price">' . homey_formatted_price($total_price) . '</div>';
        $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">' . esc_attr($local['cs_view_details']) . '</a>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="collapse collapseExample" id="collapseExample">';
        $output .= '<ul>';

        if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<li class="homey_price_first">' .$nights_total_price_li_html. esc_attr($no_of_days) . ' ' . esc_attr($night_label) . ' (' . esc_attr($local['with_custom_period_and_weekend_label']) . ') <span>' . esc_attr($nights_total_price) . '</span></li>';

        } elseif ($booking_has_weekend == 1) {
            $output .= '<li class="homey_price_first">' .$nights_total_price_li_html. esc_attr($no_of_days) . ' ' . esc_attr($night_label) . ' (' . esc_attr($with_weekend_label) . ') <span>' . $nights_total_price . '</span></li>';

        } elseif ($booking_has_custom_pricing == 1) {
            $output .= '<li class="homey_price_first">' .$nights_total_price_li_html. esc_attr($no_of_days) . ' ' . esc_attr($night_label) . ' (' . esc_attr($local['with_custom_period_label']) . ') <span>' . esc_attr($nights_total_price) . '</span></li>';

        } else {
            $output .= '<li class="homey_price_first">' .$nights_total_price_li_html. ($price_per_night) . ' x ' . esc_attr($no_of_days) . ' ' . esc_attr($night_label) . ' <span>' . $nights_total_price . '</span></li>';
        }

        if (!empty($additional_guests)) {
            $output .= '<li>' . esc_attr($additional_guests) . ' ' . esc_attr($add_guest_label) . ' <span>' . homey_formatted_price($additional_guests_total_price) . '</span></li>';
        }

        if (!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
            $output .= '<li>' . esc_attr($local['cs_cleaning_fee']) . ' <span>' . ($cleaning_fee) . '</span></li>';
        }

        if (!empty($extra_prices_html)) {
            $output .= $extra_prices_html;
        }

        $services_fee = $services_fee > 0 ? $services_fee : 0;
        $sub_total_amnt = $total_price - $prices_array['city_fee'] - $security_deposit - $services_fee - $taxes;
        $output .= '<li class="sub-total">' . esc_html__('Sub Total', 'homey') . '<span>' . homey_formatted_price($sub_total_amnt) . '</span></li>';

        if (!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>' . esc_attr($local['cs_city_fee']) . ' <span>' . ($city_fee) . '</span></li>';
        }

        if (!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>' . esc_attr($local['cs_sec_deposit']) . ' <span>' . homey_formatted_price($security_deposit) . '</span></li>';
        }

        if (!empty($services_fee) && $services_fee != 0) {
            $output .= '<li>' . esc_attr($local['cs_services_fee']) . ' <span>' . homey_formatted_price($services_fee) . '</span></li>';
        }

        if (!empty($booking_fee) && $booking_fee != 0) {
            if(!empty($booking_fee_title)){
                $booking_fee_title = $booking_fee_title;
            }
            else{
                $booking_fee_title = 'Booking Fee';
            }
            $output .= '<li>' . esc_html__($booking_fee_title) . ' <span>' . homey_formatted_price($booking_fee) . '</span></li>';
        }
        
        if (!empty($taxes) && $taxes != 0) {
            $output .= '<li>' . esc_attr($local['cs_taxes']) . ' ' . esc_attr($taxes_percent) . '% <span>' . homey_formatted_price($taxes) . '</span></li>';
        }

        $avg_price = homey_formatted_price(0);
        if (!empty($upfront_payment) && $upfront_payment != 0) {
            $curncy = homey_get_currency(1);

            $avg_price = $curncy . ' ' . $upfront_payment / $no_of_days;

            $avg_price .= ' <sub> /';
            $avg_price .= esc_html__('Average Night', 'homey');
            $avg_price .= '</sub>';

            $output .= '<li class="payment-due">' . esc_attr($local['cs_payment_due']) . ' <span>' . homey_formatted_price($upfront_payment) . '</span></li>';
            $output .= '<input data-avg-price="' . $avg_price . '" type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="' . $upfront_payment . '">';
        }

        $output .= '</ul>';
        $output .= '</div>';

        // This variable has been safely escaped in same file: Line: 1071 - 1128
        $output_escaped = $output;
        print '' . $output_escaped;

        wp_die();

    }
}

if (!function_exists('homey_get_prices')) {
    function homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options = null)
    {
        $prefix = 'homey_';

        $enable_services_fee = homey_option('enable_services_fee');
        $enable_taxes = homey_option('enable_taxes');
        $offsite_payment = homey_option('off-site-payment');
        $reservation_payment_type = homey_option('reservation_payment');
        $booking_percent = homey_option('booking_percent');
        $tax_type = homey_option('tax_type');
        $apply_taxes_on_service_fee = homey_option('apply_taxes_on_service_fee');
        $taxes_percent_global = homey_option('taxes_percent');
        $single_listing_tax = get_post_meta($listing_id, 'homey_tax_rate', true);

        $period_price = get_post_meta($listing_id, 'homey_custom_period', true);
        /*echo '<pre> its period prices > ';
        print_r($period_price);*/

        if (empty($period_price)) {
            $period_price = array();
        }

        $total_extra_services = 0;
        $extra_prices_html = "";
        $taxes_final = 0;
        $taxes_percent = 0;
        $total_price = 0;
        $total_guests_price = 0;
        $upfront_payment = 0;
        $nights_total_price = 0;
        $nights_total_price_li_html = '<ul style="display: none;">';
        $booking_has_weekend = 0;
        $booking_has_custom_pricing = 0;
        $balance = 0;
        $taxable_amount = 0;
        $period_days = 0;
        $security_deposit = '';
        $additional_guests = '';
        $additional_guests_total_price = '';
        $services_fee_final = '';
        $taxes_fee_final = '';
        $prices_array = array();

        $listing_guests = floatval(get_post_meta($listing_id, $prefix . 'guests', true));
        $nightly_price = floatval(get_post_meta($listing_id, $prefix . 'night_price', true));
        $price_per_night = $nightly_price;
        $weekends_price = floatval(get_post_meta($listing_id, $prefix . 'weekends_price', true));
        $weekends_days = get_post_meta($listing_id, $prefix . 'weekends_days', true);
        $priceWeek = floatval(get_post_meta($listing_id, $prefix . 'priceWeek', true)); // 7 Nights
        $priceMonthly = floatval(get_post_meta($listing_id, $prefix . 'priceMonthly', true));  // 30 Nights
        $security_deposit = floatval(get_post_meta($listing_id, $prefix . 'security_deposit', true));

        $cleaning_fee = floatval(get_post_meta($listing_id, $prefix . 'cleaning_fee', true));
        $cleaning_fee_type = get_post_meta($listing_id, $prefix . 'cleaning_fee_type', true);

        $city_fee = floatval(get_post_meta($listing_id, $prefix . 'city_fee', true));
        $city_fee_type = get_post_meta($listing_id, $prefix . 'city_fee_type', true);

        $extra_guests_price = floatval(get_post_meta($listing_id, $prefix . 'additional_guests_price', true));
        $additional_guests_price = $extra_guests_price;

        $allow_additional_guests = get_post_meta($listing_id, $prefix . 'allow_additional_guests', true);

        $check_in = new DateTime($check_in_date);
        $check_in_unix = $check_in->getTimestamp();
        $check_in_unix_first_day = $check_in->getTimestamp();
        $check_out = new DateTime($check_out_date);
        $check_out_unix = $check_out->getTimestamp();

        $time_difference = abs(strtotime($check_in_date) - strtotime($check_out_date));
        $days_count = $time_difference / 86400;
        $days_count = intval($days_count);
        $breakdown_price = '';
        //print_r($check_in_unix);

        if (isset($period_price[$check_in_unix]) && isset($period_price[$check_in_unix]['night_price']) && $period_price[$check_in_unix]['night_price'] != 0) {
            $price_per_night = $period_price[$check_in_unix]['night_price'];

            $booking_has_custom_pricing = 1;
            $period_days = $period_days + 1;
        }

        if ($days_count >= 7 && $priceWeek != 0) {
            $price_per_night = $priceWeek;
        }

        if ($days_count >= 30 && $priceMonthly != 0) {
            $price_per_night = $priceMonthly;
        }

        // Check additional guests price
        if ($allow_additional_guests == 'yes' && $guests > 0 && !empty($guests)) {
            if ($guests > $listing_guests) {
                $additional_guests = $guests - $listing_guests;

                $guests_price_return = homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price);
                $breakdown_price .= ', total_guests_price prev price=' . $total_guests_price . ' + weekend or reg price=' . $guests_price_return . '<br>';

                $total_guests_price = $total_guests_price + $guests_price_return;
            }
        }
        //echo $price_per_night.' only price ';

        // Check for weekend and add weekend price
        $breakdown_price .= ' * This first date * ' . date('d-m-Y', $check_in_unix) . '<br>';



        $weekday = date('N', $check_in_unix_first_day);
        if (homey_check_weekend($weekday, $weekends_days, $weekends_price)) {
            $booking_has_weekend = 1;
        }

        if ($booking_has_weekend != 1 && isset($period_price[$check_in_unix]) && isset($period_price[$check_in_unix]['night_price']) && $period_price[$check_in_unix]['night_price'] != 0) {
            //echo ' iffff ';
            $returnPrice = $period_price[$check_in_unix]['night_price'];
        } else {
            //echo ' elseeee ';

            $returnPrice = homey_cal_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price);
        }


//         echo  ' first night price= '. $returnPrice.'<br>';
        $nights_total_price = $nights_total_price + $returnPrice;
        $html_date_text = date('Y-m-d', $check_in_unix);
        $nights_total_price_li_html .= '<li class="out-loop">Price '.$returnPrice .' for '.$html_date_text.'</li>';

        $check_in->modify('tomorrow');
        $check_in_unix = $check_in->getTimestamp();

        $total_price = $total_price + $returnPrice;
        $current_index = 0;
        while ($check_in_unix < $check_out_unix) {
//             echo ' * This date * '.date('d-m-Y',$check_in_unix).'<br>';
            $current_index++;

            $weekday = date('N', $check_in_unix);
            if (homey_check_weekend($weekday, $weekends_days, $weekends_price)) {
                $booking_has_weekend = 1;
            }

            if (isset($period_price[$check_in_unix]) && isset($period_price[$check_in_unix]['night_price']) && $period_price[$check_in_unix]['night_price'] != 0) {

                $price_per_night = $period_price[$check_in_unix]['night_price'];
                //echo 'cond> <pre>  if( isset('.$period_price[$check_in_unix].') && isset('. $period_price[$check_in_unix]['night_price'] .') && '. $period_price[$check_in_unix]['night_price'] .'!=0 ){';
                //print_r($period_price[$check_in_unix]);
                $breakdown_price .= date('d-m-Y', $check_in_unix) . ' its custom pr ' . $price_per_night . ' custom price <br>';

                $booking_has_custom_pricing = 1;
                $period_days = $period_days + 1;
            } else {
                if ($days_count >= 7 && $priceWeek != 0) {
                    //do the logic
                } else if ($days_count >= 30 && $priceMonthly != 0) {
                    //do the logic
                } else {
                    $price_per_night = $nightly_price; // this creates issue for 7+ and 30+ nights issue
                }
            }

            // To make this per night per additional guest, we added a condition > 1 night, because once it is added
//            if ($current_index > 0 && $allow_additional_guests == 'yes' && $guests > 0 && !empty($guests)) {
            if ($allow_additional_guests == 'yes' && $guests > 0 && !empty($guests)) {
                if ($guests > $listing_guests) {
                    $additional_guests = $guests - $listing_guests;

                    $guests_price_return = homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price);

                    $breakdown_price .= ', prev price=' . $total_guests_price . ' + guest price=' . $guests_price_return . '<br>';

                    $total_guests_price = $total_guests_price + $guests_price_return;
                }
            } // end To make this per night per additional guest, we added a condition > 1 night, because once it is added

            $returnPrice = homey_cal_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price);

//             echo ' the day => price='. $returnPrice.'<br>';

            $nights_total_price = $nights_total_price + $returnPrice;
            $html_date_text = date('Y-m-d', $check_in_unix);
            $nights_total_price_li_html .= '<li class="in-loop">Price '.$returnPrice .' for '.$html_date_text.'</li>';

            $total_price = $total_price + $returnPrice;
            $breakdown_price .= date('d-m-Y', $check_in_unix) . ' < date ' . $total_price . ' < total price <br>';

            $check_in->modify('tomorrow');
            $check_in_unix = $check_in->getTimestamp();

        }

        if ($cleaning_fee_type == 'daily') {
            $cleaning_fee = $cleaning_fee * $days_count;
            $total_price = $total_price + $cleaning_fee;
        } else {
            $total_price = $total_price + $cleaning_fee;
        }


        //Extra prices =======================================
        if ($extra_options != '' && is_array($extra_options)) {

            $extra_prices_output = '';
            $is_first = 0;
            if (is_array($extra_options) || is_object($extra_options)) {
                foreach ($extra_options as $extra_price) {
                    if ($is_first == 0) {
                        $extra_prices_output .= '<li class="sub-total">' . esc_html__('Extra Services', 'homey') . '</li>';
                    }
                    $is_first = 2;

                    $ex_single_price = explode('|', $extra_price);

                    $ex_name = $ex_single_price[0];
                    $ex_price = floatval($ex_single_price[1]);
                    $ex_type = $ex_single_price[2];

                    if ($ex_type == 'single_fee') {
                        $ex_price = $ex_price;

                    } elseif ($ex_type == 'per_night') {
                        $ex_price = $ex_price * $days_count;
                    } elseif ($ex_type == 'per_guest') {
                        $ex_price = $ex_price * $guests;
                    } elseif ($ex_type == 'per_night_per_guest') {
                        $ex_price = $ex_price * $days_count * $guests;
                    }

                    $total_extra_services = $total_extra_services + $ex_price;

                    $extra_prices_output .= '<li>' . esc_attr($ex_name) . '<span>' . homey_formatted_price($ex_price) . '</span></li>';
                }
            }

            $total_price = $total_price + $total_extra_services;
            $extra_prices_html = $extra_prices_output;
        }

        //Calculate taxes based of original price (Excluding city, security deposit etc)
        if ($enable_taxes == 1) {

            if ($tax_type == 'global_tax') {
                $taxes_percent = $taxes_percent_global;
            } else {
                if (!empty($single_listing_tax)) {
                    $taxes_percent = $single_listing_tax;
                }
            }

            $taxable_amount = $total_price + $total_guests_price;
            $taxes_final = homey_calculate_taxes($taxes_percent, $taxable_amount);
            $total_price = $total_price + $taxes_final;
        }

        //Calculate sevices fee based of original price ( guests price + extra prices ) (Excluding cleaning, city fee etc)
        if ($enable_services_fee == 1 && $offsite_payment != 1) {
            $services_fee_type = homey_option('services_fee_type');
            $services_fee = homey_option('services_fee');
            $price_for_services_fee = $total_price + $total_guests_price;
            $services_fee_final = homey_calculate_services_fee($services_fee_type, $services_fee, $price_for_services_fee);
            $total_price = (float) $total_price + (float) $services_fee_final;
        }

        $total_guests_with_additional = (int) $guests + (int) $additional_guests;

        if ($city_fee_type == 'daily') {
            $city_fee = $city_fee * $days_count;
            $total_price = $total_price + $city_fee;
        } elseif ($city_fee_type == 'per_guest') {
            $city_fee = $city_fee * $total_guests_with_additional;
            $total_price = $total_price + $city_fee;
        } elseif ($city_fee_type == 'daily_per_guest') {
            $city_fee = $city_fee * $days_count * $total_guests_with_additional;
            $total_price = $total_price + $city_fee;
        } else {
            $total_price = $total_price + $city_fee;
        }

        if (!empty($security_deposit) && $security_deposit != 0) {
            $total_price = $total_price + $security_deposit;
        }

        if ($total_guests_price != 0) {
            $total_price = $total_price + $total_guests_price;
        }

        $listing_host_id = get_post_field('post_author', $listing_id);
        $host_reservation_payment_type = get_user_meta($listing_host_id, 'host_reservation_payment', true);
        $host_booking_percent = get_user_meta($listing_host_id, 'host_booking_percent', true);

        if ($offsite_payment == 1 && !empty($host_reservation_payment_type)) {

            if ($host_reservation_payment_type == 'percent') {
                if (!empty($host_booking_percent) && $host_booking_percent != 0) {
                    $upfront_payment = round($host_booking_percent * $total_price / 100, 2);
                }

            } elseif ($host_reservation_payment_type == 'full') {
                $upfront_payment = $total_price;

            } elseif ($host_reservation_payment_type == 'only_security') {
                $upfront_payment = $security_deposit;

            } elseif ($host_reservation_payment_type == 'only_services') {
                $upfront_payment = $services_fee_final;

            } elseif ($host_reservation_payment_type == 'services_security') {
                $upfront_payment = $security_deposit + $services_fee_final;
            }

        } else {

            if ($reservation_payment_type == 'percent') {
                if (!empty($booking_percent) && $booking_percent != 0) {
                    $upfront_payment = round($booking_percent * $total_price / 100, 2);
                }

            } elseif ($reservation_payment_type == 'full') {
                $upfront_payment = $total_price;

            } elseif ($reservation_payment_type == 'only_security') {
                $upfront_payment = $security_deposit;

            } elseif ($reservation_payment_type == 'only_services') {
                $upfront_payment = $services_fee_final;

            } elseif ($reservation_payment_type == 'services_security') {
                $upfront_payment = (int)$security_deposit + (int)$services_fee_final;
            }
        }
        $enable_booking_fee = homey_option('enable_booking_fee', 0);
        if($enable_booking_fee){
        $booking_fee_ranges_raw = get_option('homey_options')['booking_fee_ranges'] ?? [];

        $titles = $booking_fee_ranges_raw['title'] ?? [];
        $mins   = $booking_fee_ranges_raw['min_amount'] ?? [];
        $maxs   = $booking_fee_ranges_raw['max_amount'] ?? [];
        $fees   = $booking_fee_ranges_raw['fee_amount'] ?? [];

        $total_rows = max(count($titles), count($mins), count($maxs), count($fees));

        $fee_ranges = [];
        for ($i = 0; $i < $total_rows; $i++) {
            $fee_ranges[] = [
                'title'      => $titles[$i] ?? '',
                'min_amount' => $mins[$i] ?? 0,
                'max_amount' => $maxs[$i] ?? 0,
                'fee_amount' => $fees[$i] ?? 0,
            ];
        }

        $total_price = floatval($total_price ?? 0); 
        $booking_fee = 0;

        foreach ($fee_ranges as $range) {
            $min = floatval($range['min_amount']);
            $max = floatval($range['max_amount']);
            $title = $range['title'];
            $fee = floatval($range['fee_amount']);

            if ($max == 0) {
                $max = PHP_FLOAT_MAX;
            }

            if ($total_price >= $min && $total_price <= $max) {
                $booking_fee = $fee;
                break;
            }
        }

        $total_price += $booking_fee;
    }

        $prices_array['booking_fee'] = $booking_fee;
        $prices_array['booking_fee_title'] = $title;

        $balance = (float)  $total_price - (float) $upfront_payment;
        $nights_total_price_li_html .= '</ul>';

        $prices_array['breakdown_price'] = $breakdown_price;
        $prices_array['price_per_night'] = $price_per_night;
        $prices_array['nights_total_price'] = $nights_total_price;
        $prices_array['nights_total_price_li_html'] = $nights_total_price_li_html;
        $prices_array['total_price'] = $total_price;
        $prices_array['check_in_date'] = $check_in_date;
        $prices_array['check_out_date'] = $check_out_date;
        $prices_array['cleaning_fee'] = $cleaning_fee;
        $prices_array['city_fee'] = $city_fee;
        $prices_array['services_fee'] = $services_fee_final;
        $prices_array['days_count'] = $days_count;
        $prices_array['period_days'] = $period_days;
        $prices_array['taxes'] = $taxes_final;
        $prices_array['taxes_percent'] = $taxes_percent;
        $prices_array['security_deposit'] = $security_deposit;
        $prices_array['additional_guests'] = $additional_guests;
        $prices_array['additional_guests_price'] = $additional_guests_price;
        $prices_array['additional_guests_total_price'] = $total_guests_price;
        $prices_array['booking_has_weekend'] = $booking_has_weekend;
        $prices_array['booking_has_custom_pricing'] = $booking_has_custom_pricing;
        $prices_array['extra_prices_html'] = $extra_prices_html;
        $prices_array['balance'] = $balance;
        $prices_array['upfront_payment'] = $upfront_payment;

        return $prices_array;

    }
}

if (!function_exists('homey_calculate_booking_cost_instance')) {
    function homey_calculate_booking_cost_instance()
    {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $listing_id = intval($_GET['listing_id']);
        $check_in_date = wp_kses($_GET['check_in'], $allowded_html);
        $check_out_date = wp_kses($_GET['check_out'], $allowded_html);
        $guests = intval($_GET['guest']);
        $extra_options = isset($_GET['extra_options']) ? $_GET['extra_options'] : '';

        $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

        $price_per_night = homey_formatted_price($prices_array['price_per_night'], true);
        $no_of_days = $prices_array['days_count'];

        $nights_total_price = homey_formatted_price($prices_array['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $booking_fee = $prices_array['booking_fee'];
        $booking_fee_title = $prices_array['booking_fee_title'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $extra_prices_html = $prices_array['extra_prices_html'];

        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if ($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if ($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $output = '<div class="payment-list-price-detail clearfix">';
        $output .= '<div class="pull-left">';
        $output .= '<div class="payment-list-price-detail-total-price">' . $local['cs_total'] . '</div>';
        $output .= '<div class="payment-list-price-detail-note">' . $local['cs_tax_fees'] . '</div>';
        $output .= '</div>';

        $output .= '<div class="pull-right text-right">';
        $output .= '<div class="payment-list-price-detail-total-price">' . homey_formatted_price($total_price) . '</div>';
        $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">' . $local['cs_view_details'] . '</a>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="collapse collapseExample" id="collapseExample">';
        $output .= '<ul>';

        if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<li>' . $no_of_days . ' ' . $night_label . ' (' . $local['with_custom_period_and_weekend_label'] . ') <span>' . $nights_total_price . '</span></li>';

        } elseif ($booking_has_weekend == 1) {
            $output .= '<li>' . $no_of_days . ' ' . $night_label . ' (' . $with_weekend_label . ') <span>' . $nights_total_price . '</span></li>';

        } elseif ($booking_has_custom_pricing == 1) {
            $output .= '<li>' . $no_of_days . ' ' . $night_label . ' (' . $local['with_custom_period_label'] . ') <span>' . $nights_total_price . '</span></li>';

        } else {
            $output .= '<li>' . $price_per_night . ' x ' . $no_of_days . ' ' . $night_label . ' <span>' . $nights_total_price . '</span></li>';
        }

        if (!empty($additional_guests)) {
            $output .= '<li>' . $additional_guests . ' ' . $add_guest_label . ' <span>' . homey_formatted_price($additional_guests_total_price) . '</span></li>';
        }

        if (!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
            $output .= '<li>' . $local['cs_cleaning_fee'] . ' <span>' . $cleaning_fee . '</span></li>';
        }

        if (!empty($extra_prices_html)) {
            $output .= $extra_prices_html;
        }

        $services_fee = $services_fee > 0 ? $services_fee : 0;
        $sub_total_amnt = $total_price - $prices_array['city_fee'] - $security_deposit - $services_fee - $taxes;
        $output .= '<li class="sub-total">' . esc_html__('Sub Total', 'homey') . '<span>' . homey_formatted_price($sub_total_amnt) . '</span></li>';

        if (!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>' . $local['cs_city_fee'] . ' <span>' . $city_fee . '</span></li>';
        }

        if (!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>' . $local['cs_sec_deposit'] . ' <span>' . homey_formatted_price($security_deposit) . '</span></li>';
        }

        if (!empty($services_fee) && $services_fee != 0) {
            $output .= '<li>' . $local['cs_services_fee'] . ' <span>' . homey_formatted_price($services_fee) . '</span></li>';
        }
          if (!empty($booking_fee) && $booking_fee != 0) {
            if(!empty($booking_fee_title)){
                $booking_fee_title = $booking_fee_title;
            }
            else{
                $booking_fee_title = 'Booking Fee';
            }
            $output .= '<li>' . esc_html__($booking_fee_title) . ' <span>' . homey_formatted_price($booking_fee) . '</span></li>';
        }

        if (!empty($taxes) && $taxes != 0) {
            $output .= '<li>' . $local['cs_taxes'] . ' ' . $taxes_percent . '% <span>' . homey_formatted_price($taxes) . '</span></li>';
        }

        if (!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<li class="payment-due">' . $local['cs_payment_due'] . ' <span>' . homey_formatted_price($upfront_payment) . '</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="' . $upfront_payment . '">';
        }

        if (!empty($balance) && $balance != 0) {
            $output .= '<li><i class="homey-icon homey-icon-information-circle"></i> ' . $local['cs_pay_rest_1'] . ' ' . homey_formatted_price($balance) . ' ' . $local['cs_pay_rest_2'] . '</li>';
        }
        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

if (!function_exists('homey_calculate_booking_cost_ajax_1_5_3')) {
    function homey_calculate_booking_cost_ajax_1_5_3()
    {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $listing_id = intval($_POST['listing_id']);
        $check_in_date = wp_kses($_POST['check_in_date'], $allowded_html);
        $check_out_date = wp_kses($_POST['check_out_date'], $allowded_html);
        $extra_options = isset($_POST['extra_options']) ? $_POST['extra_options'] : '';
        $guests = intval($_POST['guests']);

        $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

        $price_per_night = homey_formatted_price($prices_array['price_per_night'], true);
        $no_of_days = $prices_array['days_count'];

        $nights_total_price = homey_formatted_price($prices_array['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $booking_fee = $prices_array['booking_fee'];
        $booking_fee_title = $prices_array['booking_fee_title'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        $extra_prices_html = $prices_array['extra_prices_html'];
        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        if ($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if ($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }


        $output = '<div class="payment-list-price-detail clearfix">';
        $output .= '<div class="pull-left">';
        $output .= '<div class="payment-list-price-detail-total-price">' . esc_attr($local['cs_total']) . '</div>';
        $output .= '<div class="payment-list-price-detail-note">' . esc_attr($local['cs_tax_fees']) . '</div>';
        $output .= '</div>';

        $output .= '<div class="pull-right text-right">';
        $output .= '<div class="payment-list-price-detail-total-price">' . homey_formatted_price($total_price) . '</div>';
        $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">' . esc_attr($local['cs_view_details']) . '</a>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="collapse collapseExample" id="collapseExample">';
        $output .= '<ul>';

        if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<li class="homey_price_first">' . esc_attr($no_of_days) . ' ' . esc_attr($night_label) . ' (' . esc_attr($local['with_custom_period_and_weekend_label']) . ') <span>' . esc_attr($nights_total_price) . '</span></li>';

        } elseif ($booking_has_weekend == 1) {
            $output .= '<li class="homey_price_first">' . esc_attr($no_of_days) . ' ' . esc_attr($night_label) . ' (' . esc_attr($with_weekend_label) . ') <span>' . $nights_total_price . '</span></li>';

        } elseif ($booking_has_custom_pricing == 1) {
            $output .= '<li class="homey_price_first">' . esc_attr($no_of_days) . ' ' . esc_attr($night_label) . ' (' . esc_attr($local['with_custom_period_label']) . ') <span>' . esc_attr($nights_total_price) . '</span></li>';

        } else {
            $output .= '<li class="homey_price_first">' . ($price_per_night) . ' x ' . esc_attr($no_of_days) . ' ' . esc_attr($night_label) . ' <span>' . $nights_total_price . '</span></li>';
        }

        if (!empty($additional_guests)) {
            $output .= '<li>' . esc_attr($additional_guests) . ' ' . esc_attr($add_guest_label) . ' <span>' . homey_formatted_price($additional_guests_total_price) . '</span></li>';
        }

        if (!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
            $output .= '<li>' . esc_attr($local['cs_cleaning_fee']) . ' <span>' . ($cleaning_fee) . '</span></li>';
        }

        if (!empty($extra_prices_html)) {
            $output .= $extra_prices_html;
        }

        $services_fee = $services_fee > 0 ? $services_fee : 0;
        $sub_total_amnt = $total_price - $prices_array['city_fee'] - $security_deposit - $services_fee - $taxes;
        $output .= '<li class="sub-total">' . esc_html__('Sub Total', 'homey') . '<span>' . homey_formatted_price($sub_total_amnt) . '</span></li>';

        if (!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>' . esc_attr($local['cs_city_fee']) . ' <span>' . ($city_fee) . '</span></li>';
        }


        if (!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>' . esc_attr($local['cs_sec_deposit']) . ' <span>' . homey_formatted_price($security_deposit) . '</span></li>';
        }

        if (!empty($services_fee) && $services_fee != 0) {
            $output .= '<li>' . esc_attr($local['cs_services_fee']) . ' <span>' . homey_formatted_price($services_fee) . '</span></li>';
        }

        if (!empty($booking_fee) && $booking_fee != 0) {
            if(!empty($booking_fee_title)){
                $booking_fee_title = $booking_fee_title;
            }
            else{
                $booking_fee_title = 'Booking Fee';
            }
            $output .= '<li>' . esc_html__($booking_fee_title) . ' <span>' . homey_formatted_price($booking_fee) . '</span></li>';
        }
        if (!empty($taxes) && $taxes != 0) {
            $output .= '<li>' . esc_attr($local['cs_taxes']) . ' ' . esc_attr($taxes_percent) . '% <span>' . homey_formatted_price($taxes) . '</span></li>';
        }

        if (!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<li class="payment-due">' . esc_attr($local['cs_payment_due']) . ' <span>' . homey_formatted_price($upfront_payment) . '</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="' . $upfront_payment . '">';
        }

        $output .= '</ul>';
        $output .= '</div>';

        // This variable has been safely escaped in same file: Line: 1071 - 1128
        $output_escaped = $output;
        print '' . $output_escaped;

        wp_die();

    }
}


if (!function_exists('homey_calculate_reservation_cost_nightly')) {
    function homey_calculate_reservation_cost_nightly($reservation_id, $collapse = false)
    {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if (empty($reservation_id)) {
            return;
        }

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);

        $listing_id = intval(isset($reservation_meta['listing_id']) ? $reservation_meta['listing_id'] : 0);
        $check_in_date = wp_kses(isset($reservation_meta['check_in_date']) ? $reservation_meta['check_in_date'] : '', $allowded_html);
        $check_out_date = wp_kses(isset($reservation_meta['check_out_date']) ? $reservation_meta['check_out_date'] : '', $allowded_html);
        $guests = intval(isset($reservation_meta['guests']) ? $reservation_meta['guests'] : 0);
        $booking_fee = isset($reservation_meta['booking_fee']) ? $reservation_meta['booking_fee'] : 0;
        $booking_fee_title = isset($reservation_meta['booking_fee_title']) ? $reservation_meta['booking_fee_title'] : '';

        $price_per_night = homey_formatted_price(isset($reservation_meta['price_per_night']) ? $reservation_meta['price_per_night'] : 0, true);
        $no_of_days = isset($reservation_meta['no_of_days']) ? $reservation_meta['no_of_days'] : 0;

        $nights_total_price = homey_formatted_price(isset($reservation_meta['nights_total_price']) ? $reservation_meta['nights_total_price'] : 0, false);

        $cleaning_fee = homey_formatted_price(isset($reservation_meta['cleaning_fee']) ? $reservation_meta['cleaning_fee'] : 0);
        $services_fee = isset($reservation_meta['services_fee']) ? $reservation_meta['services_fee'] : 0;

        $taxes = isset($reservation_meta['taxes']) ? $reservation_meta['taxes'] : 0;
        $taxes_percent = isset($reservation_meta['taxes_percent']) ? $reservation_meta['taxes_percent'] : 0;
        $city_fee = homey_formatted_price(isset($reservation_meta['city_fee']) ? $reservation_meta['city_fee'] : 0);
        $security_deposit = isset($reservation_meta['security_deposit']) ? $reservation_meta['security_deposit'] : 0;
        $additional_guests = isset($reservation_meta['additional_guests']) ? $reservation_meta['additional_guests'] : 0;
        $additional_guests_price = isset($reservation_meta['additional_guests_price']) ? $reservation_meta['additional_guests_price'] : 0;
        $additional_guests_total_price = isset($reservation_meta['additional_guests_total_price']) ? $reservation_meta['additional_guests_total_price'] : 0;

        $upfront_payment = isset($reservation_meta['upfront']) ? $reservation_meta['upfront'] : 0;

        $balance = isset($reservation_meta['balance']) ? $reservation_meta['balance'] : 0;
        $total_price = isset($reservation_meta['total']) ? $reservation_meta['total'] : 0;

        $booking_has_weekend = isset($reservation_meta['booking_has_weekend']) ? $reservation_meta['booking_has_weekend'] : 0;
        $booking_has_custom_pricing = isset($reservation_meta['booking_has_custom_pricing']) ? $reservation_meta['booking_has_custom_pricing'] : 0;
        $with_weekend_label = isset($reservation_meta['with_weekend_label'])?$reservation_meta['with_weekend_label']:0;

        if ($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if ($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $invoice_id = isset($_GET['invoice_id']) ? $_GET['invoice_id'] : '';
        $reservation_detail_id = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
        $is_host = false;
        $homey_invoice_buyer = get_post_meta($reservation_id, 'listing_renter', true);

        if (homey_is_host() && $homey_invoice_buyer != get_current_user_id()) {
            $is_host = true;
        }

        $extra_prices = homey_get_extra_prices($extra_options, $no_of_days, $guests);
        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        if (!empty($extra_expenses)) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $total_price = $total_price + $expenses_total_price;
            $balance = $balance + $expenses_total_price;
        }

        if (!empty($extra_discount)) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $total_price = $total_price - $discount_total_price;
            //zahid.k added for discount
            $upfront_payment = $upfront_payment - $discount_total_price;
            //zahid.k added for discount
            $balance = $balance - $discount_total_price;
        }

        if (homey_option('reservation_payment') == 'full') {
            $upfront_payment = $total_price;
            $balance = 0;
        }

        $start_div = '<div class="payment-list">';

        if ($collapse) {
            $output = '<div class="payment-list-price-detail clearfix">';
            $output .= '<div class="pull-left">';
            $output .= '<div class="payment-list-price-detail-total-price">' . $local['cs_total'] . '</div>';
            $output .= '<div class="payment-list-price-detail-note">' . $local['cs_tax_fees'] . '</div>';
            $output .= '</div>';

            $output .= '<div class="pull-right text-right">';
            $output .= '<div class="payment-list-price-detail-total-price">' . homey_formatted_price($total_price) . '</div>';
            $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">' . $local['cs_view_details'] . '</a>';
            $output .= '</div>';
            $output .= '</div>';

            $start_div = '<div class="collapse collapseExample" id="collapseExample">';
        }

        $output .= $start_div;
        $output .= '<ul>';

        $sub_total_stay_and_addional_geusts = (isset($reservation_meta['nights_total_price']) ? $reservation_meta['nights_total_price'] : 0) + $additional_guests_total_price;

        if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<li>' . $no_of_days . ' ' . $night_label . ' (' . $local['with_custom_period_and_weekend_label'] . ') <span>' . $nights_total_price . '</span></li>';

        } elseif ($booking_has_weekend == 1) {
            $output .= '<li>' . $no_of_days . ' ' . $night_label . ' (' . $with_weekend_label . ') <span>' . $nights_total_price . '</span></li>';

        } elseif ($booking_has_custom_pricing == 1) {
            $output .= '<li>' . $no_of_days . ' ' . $night_label . ' (' . $local['with_custom_period_label'] . ') <span>' . $nights_total_price . '</span></li>';

        } else {
            $output .= '<li>' . $price_per_night . ' x ' . $no_of_days . ' ' . $night_label . ' <span>' . $nights_total_price . '</span></li>';
        }

        if (!empty($additional_guests)) {
            $output .= '<li>' . $additional_guests . ' ' . $add_guest_label . ' <span>' . homey_formatted_price($additional_guests_total_price) . '</span></li>';
        }

        if (isset($reservation_meta['cleaning_fee'])) {
            if (!empty($reservation_meta['cleaning_fee']) && $reservation_meta['cleaning_fee'] != 0) {
                $output .= '<li>' . $local['cs_cleaning_fee'] . ' <span>' . $cleaning_fee . '</span></li>';
            }
        }

        $extra_total_price_for_sub_total = 0;
        if (!empty($extra_prices)) {
            $output .= $extra_prices['extra_html'];
            $extra_total_price_for_sub_total = $extra_prices['extra_total_price'];
        }

        //   if (!empty($booking_fee) && $booking_fee != 0) {
        //     if(!empty($booking_fee_title)){
        //         $booking_fee_title = $booking_fee_title;
        //     }
        //     else{
        //         $booking_fee_title = 'Booking Fee';
        //     }
        //     $output .= '<li>' . esc_html__($booking_fee_title) . ' <span>' . homey_formatted_price($booking_fee) . '</span></li>';
        // }

        if (!$is_host) {
            if (is_string($reservation_meta)) {
                $reservation_meta = array('city_fee' => 0);
            }else{
                $reservation_meta['city_fee'] = trim($reservation_meta['city_fee']) != '' ? $reservation_meta['city_fee'] : 0;
            }

            $security_deposit = trim($security_deposit) != '' ? $security_deposit : 0;
            $services_fee = trim($services_fee) != '' ? $services_fee : 0;
            $taxes = trim($taxes) != '' ? $taxes : 0;

            $sub_total_amnt = $total_price - $reservation_meta['city_fee'] - $security_deposit - $services_fee - $taxes;
        }

//        echo $sub_total_amnt .'='. $total_price .'-'. $reservation_meta['city_fee'] .'-'. $security_deposit .'-'. $services_fee .'-'. $taxes;


        if ($is_host) {
//            $sub_total_amnt = $total_price - (float) $reservation_meta['city_fee'] - (float) $security_deposit - (float) $services_fee - (float) $taxes;
            $sub_total_amnt = $total_price - (float) $reservation_meta['city_fee'] - (float) $security_deposit - (float) $taxes; // service amount should not be minus
            $sub_total_amnt = $sub_total_amnt > 0 ? $sub_total_amnt : 1;
            //host fee in % set by admin
            $host_fee_percent = homey_get_host_fee_percent();
            $host_fee = ($host_fee_percent / 100) * $sub_total_amnt;
        }

        $res_nights_total_price = isset($reservation_meta['nights_total_price']) ? $reservation_meta['nights_total_price'] : 0;
        $res_cleaning_fee = isset($reservation_meta['cleaning_fee']) ? $reservation_meta['cleaning_fee'] : 0;

        $sub_total_amnt = $res_nights_total_price + $additional_guests_total_price + $res_cleaning_fee + $extra_total_price_for_sub_total;

        $output .= '<li class="sub-total">' . esc_html__('Sub Total', 'homey') . '<span>' . homey_formatted_price($sub_total_amnt) . '</span></li>';

        if (isset($reservation_meta['city_fee'])) {
            if (!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
                $output .= '<li>' . $local['cs_city_fee'] . ' <span>' . $city_fee . '</span></li>';
            }
        }

        if (!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>' . $local['cs_sec_deposit'] . ' <span>' . homey_formatted_price($security_deposit) . '</span></li>';
        }


        if (!empty($services_fee) && !$is_host) {
            $output .= '<li>' . $local['cs_services_fee'] . ' <span>' . homey_formatted_price($services_fee) . '</span></li>';
        }
        
        if (!empty($booking_fee) && $booking_fee != 0) {
            if(!empty($booking_fee_title)){
                $booking_fee_title = $booking_fee_title;
            }
            else{
                $booking_fee_title = 'Booking Fee';
            }
            $output .= '<li>' . esc_html__($booking_fee_title) . ' <span>' . homey_formatted_price($booking_fee) . '</span></li>';
        }

        if (!empty($host_fee) && $is_host) {
            $output .= '<li>' . esc_html__('Host Fee', 'homey') . ' <span> ' . homey_formatted_price($host_fee) . '</span></li>';
        }

        if (!empty($extra_expenses)) {
            $output .= $extra_expenses['expenses_html'];
        }

        if (!empty($extra_discount)) {
            $output .= $extra_discount['discount_html'];
        }


        if (!empty($taxes) && $taxes != 0) {
            $output .= '<li>' . $local['cs_taxes'] . ' ' . $taxes_percent . '% <span>' . homey_formatted_price($taxes) . '</span></li>';
        }

        if (homey_option('reservation_payment') == 'full') {

            if (!$is_host && !empty($services_fee)) {
                if (!homey_is_admin()) {
                    $upfront_payment = $sub_total_amnt + $services_fee;
                }
            }

            if ($is_host && !empty($host_fee)) {
                $upfront_payment = $sub_total_amnt - $host_fee;
            }

            $output .= '<li class="payment-due">' . $local['inv_total'] . ' <span>' . homey_formatted_price($upfront_payment) . '</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="' . $upfront_payment . '">';

        } else {
            if (!empty($upfront_payment) && $upfront_payment != 0) {
                if (!$is_host && !empty($services_fee)) {
                    if (!homey_is_admin()) {
                        $upfront_payment = $sub_total_amnt + $services_fee;
                    }
                }

                if ($is_host && !empty($host_fee)) {
                    $upfront_payment = $sub_total_amnt - $host_fee;
                }

                $reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
                $paid_or_due = $reservation_status == 'booked' ? $local['paid'] : $local['cs_payment_due'];
                $output .= '<li class="payment-due">' . $paid_or_due . ' <span>' . homey_formatted_price($upfront_payment) . '</span></li>';
                $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="' . $upfront_payment . '">';
            }
        }

        if (!empty($balance) && $balance != 0) {
            $output .= '<li><i class="homey-icon homey-icon-information-circle"></i> ' . $local['cs_pay_rest_1'] . ' ' . homey_formatted_price($balance) . ' ' . $local['cs_pay_rest_2'] . '</li>';
        }


        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

add_action('wp_ajax_homey_confirm_reservation', 'homey_confirm_reservation');
if (!function_exists('homey_confirm_reservation')) {
    function homey_confirm_reservation()
    {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $local = homey_get_localization();
        $no_upfront = homey_option('reservation_payment');

        $date = date('Y-m-d G:i:s', current_time('timestamp', 0));

        $reservation_id = intval($_POST['reservation_id']);

        $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
        $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);
        $is_hourly = get_post_meta($reservation_id, 'is_hourly', true);

        $renter = homey_usermeta($listing_renter);
        $renter_email = $renter['email'];

        if ($listing_owner != $userID && !homey_is_admin()) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => homey_get_reservation_notification('not_owner')
                )
            );
            wp_die();
        }

        // If no upfront option select then book at this step
        if ($no_upfront == 'no_upfront') {

            if ($is_hourly == 'yes') {
                homey_hourly_booking_with_no_upfront($reservation_id);
            } else {
                homey_booking_with_no_upfront($reservation_id);
            }

            echo json_encode(
                array(
                    'success' => true,
                    'message' => homey_get_reservation_notification('booked')
                )
            );

        } else {
            // Set reservation status from under_review to available
            update_post_meta($reservation_id, 'reservation_status', 'available');
            update_post_meta($reservation_id, 'reservation_confirm_date_time', $date);

            echo json_encode(
                array(
                    'success' => true,
                    'message' => homey_get_reservation_notification('available')
                )
            );

            $allowded_html = array();
            $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
            $check_in_date = wp_kses($reservation_meta['check_in_date'], $allowded_html);
            $check_out_date = wp_kses($reservation_meta['check_out_date'], $allowded_html);
            $guests = intval($reservation_meta['guests']);
            $adult_guest = isset($reservation_meta['adult_guest']) ? intval($reservation_meta['adult_guest']) : 0;
            $child_guest = isset($reservation_meta['child_guest']) ? intval($reservation_meta['child_guest']) : 0;
            $upfront_payment = $reservation_meta['upfront'];
            $balance = $reservation_meta['balance'];
            $total_price = $reservation_meta['total'];
            $booking_fee = $reservation_meta['booking_fee'];

            $email_args = array(
                'reservation_detail_url' => reservation_detail_link($reservation_id),
                'check_in_date' => $check_in_date,
                'check_out_date' => $check_out_date,
                'guests' => $guests,
                'adult_guests' => $adult_guest,
                'child_guests' => $child_guest,
                'upfront_payment' => $upfront_payment,
                'balance' => $balance,
                'total_price' => $total_price,
                'booking_fee' => $booking_fee

            );
            homey_email_composer($renter_email, 'confirm_reservation', $email_args);
//            $admin_email = get_option( 'admin_email' );
//            homey_email_composer( $admin_email, 'confirm_reservation', $email_args );
        }

        wp_die();
    }
}


add_action('wp_ajax_homey_reservation_mark_paid', 'homey_reservation_mark_paid');
if (!function_exists('homey_reservation_mark_paid')) {
    function homey_reservation_mark_paid()
    {
        if (homey_is_admin() || homey_is_host()) {
            $reservation_id = intval($_POST['reservation_id']);

            // on mark paid generating invoice, if not in need you can delete or comment the code below
            $time = time();
            $date = date('Y-m-d G:i:s', $time);
            // Emails
            $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
            $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);
            $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);

            //homey_generate_invoice( 'reservation','one_time', $reservation_id, $date, $listing_renter, 0, 0, '', 'Self' );
            // on mark paid generating invoice, if not in need you can delete or comment the code above

            update_post_meta($reservation_id, 'reservation_status', 'booked');
            $admin_email = get_option('new_admin_email');
            $admin_email = empty($admin_email) ? get_option( 'admin_email' ) : $admin_email;

            //Book dates
            $booked_days_array = homey_make_days_booked($listing_id, $reservation_id);
            update_post_meta($listing_id, 'reservation_dates', $booked_days_array);

            //Remove Pending Dates
            $pending_dates_array = homey_remove_booking_pending_days($listing_id, $reservation_id);
            update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

            $renter = homey_usermeta($listing_renter);
            $renter_email = $renter['email'];

            $owner = homey_usermeta($listing_owner);
            $owner_email = $owner['email'];

            // Update status for paid in invoice
            $reservation_invoice_id = is_invoice_paid_for_reservation($reservation_id, 1);
            update_post_meta($reservation_invoice_id, 'invoice_payment_status', 1);
            update_post_meta($reservation_id, 'invoice_payment_status', 1);

            $allowded_html = array();
            $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
            $check_in_date = wp_kses($reservation_meta['check_in_date'], $allowded_html);
            $check_out_date = wp_kses($reservation_meta['check_out_date'], $allowded_html);
            $guests = intval($reservation_meta['guests']);
            $adult_guest = isset($reservation_meta['adult_guest']) ? intval($reservation_meta['adult_guest']) : 0;
            $child_guest = isset($reservation_meta['child_guest']) ? intval($reservation_meta['child_guest']) : 0;
            $upfront_payment = $reservation_meta['upfront'];
            $balance = $reservation_meta['balance'];
            $total_price = $reservation_meta['total'];
            $booking_fee = $reservation_meta['booking_fee'];

            $email_args = array(
                'reservation_detail_url' => reservation_detail_link($reservation_id),
                'check_in_date' => $check_in_date,
                'check_out_date' => $check_out_date,
                'guests' => $guests,
                'adult_guests' => $adult_guest,
                'child_guests' => $child_guest,
                'upfront_payment' => $upfront_payment,
                'balance' => $balance,
                'total_price' => $total_price,
                'booking_fee' => $booking_fee

            );
            homey_email_composer($renter_email, 'booked_reservation', $email_args);
            homey_email_composer($owner_email, 'booked_reservation', $email_args);
            homey_email_composer($admin_email, 'admin_booked_reservation', $email_args);

            echo json_encode(
                array(
                    'success' => true,
                    'url' => homey_get_template_link('template/dashboard-reservations.php')
                )
            );
            wp_die();
        } else {
            echo json_encode(
                array(
                    'success' => false,
                    'msg' => homey_get_template_link('template/dashboard-reservations.php')
                )
            );
            wp_die();
        }

    }
}

//hm_no login needed to make reservation
add_action('wp_ajax_nopriv_hm_no_login_add_reservation', 'hm_no_login_add_reservation');
add_action('wp_ajax_hm_no_login_add_reservation', 'hm_no_login_add_reservation');
if (!function_exists('hm_no_login_add_reservation')) {
    function hm_no_login_add_reservation()
    {
        $local = homey_get_localization();
        $admin_email = get_option('new_admin_email');
        $admin_email = empty($admin_email) ? get_option( 'admin_email' ) : $admin_email;

        $email = trim($_REQUEST['new_reser_request_user_email']);

        if (empty($email)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Enter email address', 'homey')
                )
            );
            wp_die();
        }

        $user = get_user_by('email', $email);

        if (isset($user->ID)) {
            $hm_no_login_user_id = $user->ID;
        } else { //create user from email
            $user_login = $email;

            $display_name  = $email;
            $nickname   = $email; ;
            $first_name   = "New" ;
            $last_name   = "User" ;
            $description    = "New User";

            $user_email = $email;
            $role = 'homey_renter';
            $user_pass = wp_generate_password(8, false);
            $userdata = compact('user_login', 'user_email', 'user_pass', 'role', 'display_name', 'nickname', 'first_name', 'last_name', 'description');
            $hm_no_login_user_id = wp_insert_user($userdata);

            if ($hm_no_login_user_id > 0 && homey_option('hm_no_login_notify', 0) == 1) {
                homey_wp_new_user_notification($hm_no_login_user_id, $user_pass);
            }

            update_user_meta($hm_no_login_user_id, 'viaphp', 1);

        }

        $userID = $hm_no_login_user_id;

        $local = homey_get_localization();
        $allowded_html = array();
        $reservation_meta = array();

        $listing_id = intval($_POST['listing_id']);
        $listing_owner_id = get_post_field('post_author', $listing_id);
        $check_in_date = wp_kses($_POST['check_in_date'], $allowded_html);
        $check_out_date = wp_kses($_POST['check_out_date'], $allowded_html);
        $extra_options = isset($_POST['extra_options']) ? $_POST['extra_options'] : '';
        $guest_message = stripslashes($_POST['guest_message']);
        $guests = intval($_POST['guests']);
        $adult_guest = isset($_POST['adult_guest']) ? intval($_POST['adult_guest']) : 0;
        $child_guest = isset($_POST['child_guest']) ? intval($_POST['child_guest']) : 0;
        $title = $local['reservation_text'];

        $booking_type = homey_booking_type_by_id($listing_id);

        $owner = homey_usermeta($listing_owner_id);
        $owner_email = $owner['email'];

        $booking_hide_fields = homey_option('booking_hide_fields');
        if (empty($guests) && $booking_hide_fields['guests'] != 1) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['choose_guests']
                )
            );
            wp_die();
        }

        if ($booking_type == "per_day_date" && strtotime($check_out_date) < strtotime($check_in_date)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['ins_book_proceed']
                )
            );
            wp_die();
        }

        if ($booking_type != "per_day_date" && strtotime($check_out_date) <= strtotime($check_in_date)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['dates_not_available']
                )
            );
            wp_die();
        }

        $check_availability = check_booking_availability($check_in_date, $check_out_date, $listing_id, $guests);
        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if ($is_available) {

            if ($booking_type == 'per_week') {
                $prices_array = homey_get_weekly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

                $price_per_week = $prices_array['price_per_week'];
                $weeks_total_price = $prices_array['weeks_total_price'];
                $total_weeks_count = $prices_array['total_weeks_count'];

                $reservation_meta['price_per_week'] = $price_per_week;
                $reservation_meta['weeks_total_price'] = $weeks_total_price;
                $reservation_meta['total_weeks_count'] = $total_weeks_count;
                $reservation_meta['reservation_listing_type'] = 'per_week';

            } else if ($booking_type == 'per_month') {
                $prices_array = homey_get_monthly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

                $price_per_month = $prices_array['price_per_month'];
                $months_total_price = $prices_array['months_total_price'];
                $total_months_count = $prices_array['total_months_count'];

                $reservation_meta['price_per_month'] = $price_per_month;
                $reservation_meta['months_total_price'] = $months_total_price;
                $reservation_meta['total_months_count'] = $total_months_count;
                $reservation_meta['reservation_listing_type'] = 'per_month';

            } else if ($booking_type == 'per_day_date') {

                $prices_array = homey_get_day_date_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
                $price_per_night = $prices_array['price_per_day_date'];
                $nights_total_price = $prices_array['nights_total_price'];

                $reservation_meta['price_per_day_date'] = $price_per_night;
                $reservation_meta['price_per_night'] = $price_per_night;
                $reservation_meta['days_total_price'] = $nights_total_price;
                $reservation_meta['reservation_listing_type'] = 'per_day_date';
            } else {

                $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
                $price_per_night = $prices_array['price_per_night'];
                $nights_total_price = $prices_array['nights_total_price'];
                $booking_fee = $prices_array['booking_fee'];
                $booking_fee_title = $prices_array['booking_fee_title'];

                $reservation_meta['booking_fee'] = $booking_fee;
                $reservation_meta['booking_fee_title'] = $booking_fee_title;
                $reservation_meta['price_per_night'] = $price_per_night;
                $reservation_meta['nights_total_price'] = $nights_total_price;
                $reservation_meta['reservation_listing_type'] = 'per_night';
            }

            $reservation_meta['no_of_days'] = $prices_array['days_count'] = $booking_type == 'per_day_date' ? $prices_array['days_count'] : $prices_array['days_count'];
            $reservation_meta['additional_guests'] = $prices_array['additional_guests'];

            $upfront_payment = $prices_array['upfront_payment'];
            $balance = $prices_array['balance'];
            $total_price = $prices_array['total_price'];
            $cleaning_fee = $prices_array['cleaning_fee'];
            $city_fee = $prices_array['city_fee'];
            $services_fee = $prices_array['services_fee'];
            $days_count = $prices_array['days_count'];
            $period_days = $prices_array['period_days'];
            $taxes = $prices_array['taxes'];
            $taxes_percent = $prices_array['taxes_percent'];
            $security_deposit = $prices_array['security_deposit'];
            $additional_guests = $prices_array['additional_guests'];
            $additional_guests_price = $prices_array['additional_guests_price'];
            $additional_guests_total_price = $prices_array['additional_guests_total_price'];
            $booking_has_weekend = $prices_array['booking_has_weekend'];
            $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];

            $reservation_meta['check_in_date'] = $check_in_date;
            $reservation_meta['check_out_date'] = $check_out_date;
            $reservation_meta['guests'] = $guests;
            $reservation_meta['adult_guest'] = $adult_guest;
            $reservation_meta['child_guest'] = $child_guest;
            $reservation_meta['listing_id'] = $listing_id;
            $reservation_meta['upfront'] = $upfront_payment;
            $reservation_meta['balance'] = $balance;
            $reservation_meta['total'] = $total_price;

            $reservation_meta['cleaning_fee'] = $cleaning_fee;
            $reservation_meta['city_fee'] = $city_fee;
            $reservation_meta['services_fee'] = $services_fee;
            $reservation_meta['period_days'] = $period_days;
            $reservation_meta['taxes'] = $taxes;
            $reservation_meta['taxes_percent'] = $taxes_percent;
            $reservation_meta['security_deposit'] = $security_deposit;
            $reservation_meta['additional_guests_price'] = $additional_guests_price;
            $reservation_meta['additional_guests_total_price'] = $additional_guests_total_price;
            $reservation_meta['booking_has_weekend'] = $booking_has_weekend;
            $reservation_meta['booking_has_custom_pricing'] = $booking_has_custom_pricing;

            $reservation = array(
                'post_title' => $title,
                'post_status' => 'publish',
                'post_type' => 'homey_reservation',
                'post_author' => $userID
            );
            $reservation_id = wp_insert_post($reservation);

            $reservation_update = array(
                'ID' => $reservation_id,
                'post_title' => $title . ' ' . $reservation_id
            );
            wp_update_post($reservation_update);

            update_post_meta($reservation_id, 'reservation_listing_id', $listing_id);
            update_post_meta($reservation_id, 'listing_owner', $listing_owner_id);
            update_post_meta($reservation_id, 'listing_renter', $userID);
            update_post_meta($reservation_id, 'reservation_checkin_date', $check_in_date);
            update_post_meta($reservation_id, 'reservation_checkout_date', $check_out_date);
            update_post_meta($reservation_id, 'reservation_guests', $guests);
            update_post_meta($reservation_id, 'reservation_adult_guest', $adult_guest);
            update_post_meta($reservation_id, 'reservation_child_guest', $child_guest);
            update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
            update_post_meta($reservation_id, 'reservation_status', 'under_review');
            update_post_meta($reservation_id, 'is_hourly', 'no');
            update_post_meta($reservation_id, 'extra_options', $extra_options);

            update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
            update_post_meta($reservation_id, 'reservation_balance', $balance);
            update_post_meta($reservation_id, 'reservation_total', $total_price);

            if ($booking_type == 'per_day_date') {
                $pending_dates_array = homey_get_booking_pending_date_days($listing_id);
            } else {
                $pending_dates_array = homey_get_booking_pending_days($listing_id);
            }

            update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

            echo json_encode(
                array(
                    'success' => true,
                    'message' => $local['request_sent']
                )
            );

            $guest_message = empty($guest_message) ? esc_html__("To send another message, click on view.", "homey") : $guest_message;

            if (!empty(trim($guest_message))) {
                do_action('homey_create_messages_thread', $guest_message, $reservation_id);
            }

            $message_link = homey_thread_link_after_reservation($reservation_id);

            $user_info = get_userdata($userID);
            $renter_email = '';
            if ($user_info) {
                $renter_email = $user_info->user_email;
            }

            $email_args = array(
                'reservation_detail_url' => reservation_detail_link($reservation_id),
                'check_in_date' => $check_in_date,
                'check_out_date' => $check_out_date,
                'guests' => $guests,
                'adult_guest' => $adult_guest,
                'child_guest' => $child_guest,
                'total_price' => $total_price,
                'renter_email' => $renter_email,
                'guest_message' => $guest_message,
                'message_link' => $message_link,
                'booking_fee' => $booking_fee
            );

            if ($owner_email != $admin_email) {
                homey_email_composer($owner_email, 'hm_no_login_new_reservation', $email_args);
            }

            homey_email_composer($admin_email, 'hm_no_login_new_reservation', $email_args);

            $user_info = get_userdata($userID);
            $renter_email = '';
            if ($user_info) {
                $renter_email = $user_info->user_email;
            }
            $reservation_page = homey_get_template_link_dash('template/dashboard-reservations2.php');
            $reservation_detail_link = add_query_arg('reservation_detail', $reservation_id, $reservation_page);
            $email_args = array(
                'reservation_detail_url' => $reservation_detail_link,
                'check_in_date' => $check_in_date,
                'check_out_date' => $check_out_date,
                'guests' => $guests,
                'adult_guest' => $adult_guest,
                'child_guest' => $child_guest,
                'total_price' => $total_price,
                'renter_email' => $renter_email,
                'guest_message' => $guest_message,
                'message_link' => $message_link,
                'booking_fee' => $booking_fee

            );

            homey_email_composer($email, 'hm_no_login_new_reservation_sent', $email_args);


            wp_die();

        } else { // end $check_availability
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $check_message
                )
            );
            wp_die();
        }

    }
}



?>
