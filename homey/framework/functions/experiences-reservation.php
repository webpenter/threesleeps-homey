<?php
add_action( 'wp_ajax_nopriv_homey_add_exp_reservation', 'homey_add_exp_reservation' );
add_action( 'wp_ajax_homey_add_exp_reservation', 'homey_add_exp_reservation' );
if( !function_exists('homey_add_exp_reservation') ) {
    function homey_add_exp_reservation() {
        global $current_user;

        $admin_email = get_option( 'new_admin_email' );

        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;



        $no_login_needed_for_exp_booking = homey_option('no_login_needed_for_exp_booking');

        if($current_user->ID == 0 && $no_login_needed_for_exp_booking == "yes" && isset($_REQUEST['new_reser_exp_request_user_email'])) {
            $email = trim($_REQUEST['new_reser_exp_request_user_email']);

            if(empty($email)){
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
                $user_email = $email;
                $user_pass = wp_generate_password(8, false);
                $userdata = compact('user_login', 'user_email', 'user_pass');
                $new_user_id = wp_insert_user($userdata);

                if($new_user_id > 0){
                    homey_wp_new_user_notification( $new_user_id, $user_pass );
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
        $userID       = $current_user->ID;

        $local = homey_get_localization();
        $allowded_html = array();
        $reservation_meta = array();

        $experience_id = intval($_POST['experience_id']);
        $experience_owner_id  =  get_post_field( 'post_author', $experience_id );
        $check_in_date     =  wp_kses ( $_POST['check_in_date'], $allowded_html );

        $extra_options    =  $_POST['extra_options'];
        $guest_message = stripslashes ( $_POST['guest_message'] );
        $guests   =  intval($_POST['guests']);
        $title    = $local['reservation_text'];

        $owner = homey_usermeta($experience_owner_id);
        $owner_email = $owner['email'];

        if ( !is_user_logged_in() || $userID === 0 ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['login_for_reservation']
                )
            );
            wp_die();
        }

        if(1==2 && $userID == $experience_owner_id) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['own_experience_error']
                )
            );
            wp_die();
        }

        $check_availability = check_exp_booking_availability( $check_in_date, $experience_id, $guests );
        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if($is_available) {
            // reservation meta information
            $prices_array = homey_get_exp_prices($check_in_date, $experience_id, $guests, $extra_options);
            $price_per_person = $prices_array['price_per_person'];
            $persons_total_price = $prices_array['total_price'];
            $cleaning_fee = $prices_array['cleaning_fee'];
            $upfront_payment = $prices_array['upfront_payment'];
            $balance = $prices_array['balance'];
            $total_price = $prices_array['total_price'];
            $city_fee = $prices_array['city_fee'];
            $services_fee = $prices_array['services_fee'];
            $taxes = $prices_array['taxes'];
            $taxes_percent = $prices_array['taxes_percent'];
            $security_deposit = $prices_array['security_deposit'];
            $additional_guests_price = $prices_array['additional_guests_price'];
            $additional_guests_total_price = $prices_array['additional_guests_total_price'];
            $booking_has_weekend = $prices_array['booking_has_weekend'];

            $reservation_meta['check_in_date'] = $check_in_date;
            $reservation_meta['price_per_person'] = $price_per_person;
            $reservation_meta['guests_total_price'] = $persons_total_price;
            $reservation_meta['reservation_experience_type'] = 'per_person';
            $reservation_meta['guests'] = $guests;
            $reservation_meta['experience_id'] = $experience_id;
            $reservation_meta['upfront'] = $upfront_payment;
            $reservation_meta['balance'] = $balance;
            $reservation_meta['total'] = $total_price;
            $reservation_meta['no_of_persons'] = $prices_array['persons_count'];
            $reservation_meta['additional_guests'] = $prices_array['additional_guests'];
            $reservation_meta['cleaning_fee'] = $cleaning_fee;
            $reservation_meta['city_fee'] = $city_fee;
            $reservation_meta['services_fee'] = $services_fee;
            $reservation_meta['taxes'] = $taxes;
            $reservation_meta['taxes_percent'] = $taxes_percent;
            $reservation_meta['security_deposit'] = $security_deposit;
            $reservation_meta['additional_guests_price'] = $additional_guests_price;
            $reservation_meta['additional_guests_total_price'] = $additional_guests_total_price;
            $reservation_meta['booking_has_weekend'] = $booking_has_weekend;
            // reservation meta information

            $reservation = array(
                'post_title'    => $title,
                'post_status'   => 'publish',
                'post_type'     => 'homey_e_reservation' ,
                'post_author'   => $userID
            );
            $reservation_id =  wp_insert_post($reservation );

            $reservation_update = array(
                'ID'         => $reservation_id,
                'post_title' => $title.' '.$reservation_id
            );
            wp_update_post( $reservation_update );

            update_post_meta($reservation_id, 'reservation_experience_id', $experience_id);
            update_post_meta($reservation_id, 'experience_owner', $experience_owner_id);
            update_post_meta($reservation_id, 'experience_renter', $userID);
            update_post_meta($reservation_id, 'reservation_checkin_date', $check_in_date);
            update_post_meta($reservation_id, 'reservation_guests', $guests);
            update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
            update_post_meta($reservation_id, 'reservation_status', 'under_review');
            update_post_meta($reservation_id, 'extra_options', $extra_options);

            update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
            update_post_meta($reservation_id, 'reservation_balance', $balance);
            update_post_meta($reservation_id, 'reservation_total', $total_price);

            $pending_dates_array = homey_get_exp_booking_pending_persons($experience_id);

            update_post_meta($experience_id, 'reservation_pending_dates', $pending_dates_array);

            echo json_encode(
                array(
                    'success' => true,
                    'message' => $local['request_sent']
                )
            );

            $message_link = homey_thread_link_after_reservation($reservation_id);
            $email_args = array(
                'reservation_detail_url' => exp_reservation_detail_link($reservation_id),
                'guest_message' => $guest_message,
                'message_link' => $message_link
            );

            if(!empty(trim($guest_message)) ){
                do_action('homey_create_messages_thread', $guest_message, $reservation_id);
            }

            homey_email_composer( $owner_email, 'new_reservation', $email_args );
            homey_email_composer( $admin_email, 'admin_booked_reservation', $email_args );

            if(isset($current_user->user_email)){
                $reservation_page = homey_get_template_link_dash('template/dashboard-reservations2-experiences.php');
                $reservation_detail_link = add_query_arg( 'reservation_detail', $reservation_id, $reservation_page );
                $email_args = array( 'reservation_detail_url' => $reservation_detail_link );

                $email_args = array(
                    'reservation_detail_url' => $reservation_detail_link,
                    'guest_message' => $guest_message,
                    'message_link' => $message_link
                );

                homey_email_composer( $current_user->user_email, 'new_reservation_sent', $email_args );
            }

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

add_action( 'wp_ajax_homey_exp_booking_paypal_payment', 'homey_exp_booking_paypal_payment' );
if( !function_exists('homey_exp_booking_paypal_payment') ) {
    function homey_exp_booking_paypal_payment() {
        global $current_user;
        $allowded_html = array();
        $blogInfo = esc_url( home_url('/') );
        wp_get_current_user();
        $userID =   $current_user->ID;
        $local = homey_get_localization();
        $reservation_id = intval($_POST['reservation_id']);

        //check security
        $nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $nonce, 'checkout-security-nonce' ) ) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['security_check_text']
                )
            );
            wp_die();
        }

        if(empty($reservation_id)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['something_went_wrong']
                )
            );
            wp_die();
        }

        $reservation = get_post($reservation_id);

        if( $reservation->post_author != $userID ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['belong_to']
                )
            );
            wp_die();
        }

        $reservation_status = get_post_meta($reservation_id, 'reservation_status', true);

        if( $reservation_status != 'available') {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['something_went_wrong']
                )
            );
            wp_die();
        }

        $currency = homey_option('payment_currency');
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);

        $experience_id     = intval($reservation_meta['experience_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);

        $is_paypal_live         =  homey_option('paypal_api');
        $host                   =  'https://api.sandbox.paypal.com';
        $upfront_payment          =  floatval( $reservation_meta['upfront'] );
        $submission_curency     =  esc_html( $currency );
        $payment_description    =  esc_html__('Reservation payment on ','homey').$blogInfo;

        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        $reservation_payment_type = homey_option('exp_reservation_payment');

        if( ! empty($extra_expenses) && $reservation_payment_type == 'full' ) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $upfront_payment = $upfront_payment + $expenses_total_price;
        }

        if( ! empty($extra_discount) && $reservation_payment_type == 'full'  ) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $upfront_payment = $upfront_payment - $discount_total_price;
        }

        $total_price =  number_format( $upfront_payment, 2, '.','' );

        // Check if payal live
        if( $is_paypal_live =='live'){
            $host='https://api.paypal.com';
        }

        $url             =   $host.'/v1/oauth2/token';
        $postArgs        =   'grant_type=client_credentials';

        // Get Access token
        $paypal_token    =   homey_get_paypal_access_token( $url, $postArgs );
        $url             =   $host.'/v1/payments/payment';

        $payment_page_link = homey_get_template_link_2('template/dashboard-exp-payment.php');
        $reservation_page_link = homey_get_template_link('template/dashboard-reservations-experiences.php');

        $cancel_link = add_query_arg( array('reservation_id' => $reservation_id), $payment_page_link );
        $return_link = add_query_arg( 'reservation_detail', $reservation_id, $reservation_page_link );

        $payment = array(
            'intent' => 'sale',
            "redirect_urls" => array(
                "return_url" => $return_link,
                "cancel_url" => $cancel_link
            ),
            'payer' => array("payment_method" => "paypal"),
        );

        /* Prepare basic payment details
        *--------------------------------------*/
        $payment['transactions'][0] = array(
            'amount' => array(
                'total' => $total_price,
                'currency' => $submission_curency,
                'details' => array(
                    'subtotal' => $total_price,
                    'tax' => '0.00',
                    'shipping' => '0.00'
                )
            ),
            'description' => $payment_description
        );


        /* Prepare individual items
        *--------------------------------------*/
        $payment['transactions'][0]['item_list']['items'][] = array(
            'quantity' => '1',
            'name' => esc_html__( 'Reservation ID','homey').' '.$reservation_id.' '.esc_html__( 'Experience ID','homey').' '.$experience_id,
            'price' => $total_price,
            'currency' => $submission_curency,
            'sku' => 'Paid Reservation',
        );

        /* Convert PHP array into json format
        *--------------------------------------*/
        $jsonEncode = json_encode($payment);
        $json_response = homey_execute_paypal_request( $url, $jsonEncode, $paypal_token );

        //print_r($json_response);
        foreach ($json_response['links'] as $link) {
            if($link['rel'] == 'execute'){
                $payment_execute_url = $link['href'];
            } else  if($link['rel'] == 'approval_url'){
                $payment_approval_url = $link['href'];
            }
        }

        // Save data in database for further use on processor page
        $output['payment_execute_url'] = $payment_execute_url;
        $output['paypal_token']        = $paypal_token;
        $output['reservation_id']      = $reservation_id;

        $output['experience_id']          = '';
        $output['check_in_date']       = '';
        $output['check_out_date']      = '';
        $output['guests']              = '';
        $output['extra_options']       = '';
        $output['renter_message']      = '';
        $output['is_instance_booking'] = 0;
        $output['is_hourly'] = 0;

        $save_output[$userID]   =   $output;
        update_option('homey_paypal_transfer',$save_output);

        //Add host earning history
        homey_add_exp_earning($reservation_id);

        echo json_encode(
            array(
                'success' => true,
                'message' => 'success',
                'payment_execute_url' => $payment_approval_url
            )
        );

        wp_die();
    }
}

add_action( 'wp_ajax_homey_instance_exp_booking_paypal_payment', 'homey_instance_exp_booking_paypal_payment' );
if( !function_exists('homey_instance_exp_booking_paypal_payment') ) {
    function homey_instance_exp_booking_paypal_payment() {
        global $current_user;
        $allowded_html = array();
        $blogInfo = esc_url( home_url('/') );
        wp_get_current_user();
        $userID =   $current_user->ID;
        $local = homey_get_localization();

        //check security
//        $nonce = $_REQUEST['security'];
//        if ( ! wp_verify_nonce( $nonce, 'checkout-security-nonce' ) ) {
//
//            echo json_encode(
//                array(
//                    'success' => false,
//                    'message' => $local['security_check_text']
//                )
//            );
//            wp_die();
//        }

        $currency = homey_option('payment_currency');

        $experience_id     = intval($_POST['experience_id']);
        $check_in_date  = wp_kses ($_POST['check_in'], $allowded_html);
        $renter_message = wp_kses ($_POST['renter_message'], $allowded_html);
        $guests         = intval($_POST['guests']);
        $extra_options  = $_POST['extra_options'];

        $reservor_name  = wp_kses ($_POST['reservor_name'], $allowded_html);
        $reservor_phone  = wp_kses ($_POST['reservor_phone'], $allowded_html);

        $check_availability = check_exp_booking_availability($check_in_date, $experience_id, $guests);
        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if(!$is_available) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $check_message,
                    'payment_execute_url' => ''
                )
            );
            wp_die();


        } else {

            $prices_array = homey_get_exp_prices($check_in_date, $experience_id, $guests, $extra_options);

            $is_paypal_live         =  homey_option('paypal_api');
            $host                   =  'https://api.sandbox.paypal.com';
            $upfront_payment          =  floatval( $prices_array['total_price'] );

            $submission_curency     =  esc_html( $currency );
            $payment_description    =  esc_html__('Experience Reservation payment on ','homey').$blogInfo;

            $total_price =  number_format( $upfront_payment, 2, '.','' );

            // Check if payal live
            if( $is_paypal_live =='live'){
                $host='https://api.paypal.com';
            }

            $url             =   $host.'/v1/oauth2/token';
            $postArgs        =   'grant_type=client_credentials';

            // Get Access token
            $paypal_token    =   homey_get_paypal_access_token( $url, $postArgs );
            $url             =   $host.'/v1/payments/payment';

            $instance_payment_page_link = homey_get_template_link_2('template/template-instance-exp-booking.php');
            $reservation_page_link = homey_get_template_link('template/dashboard-reservations-experiences.php');

            $cancel_link = add_query_arg(
                array(
                    'check_in' => $check_in_date,
                    'guest' => $guests,
                    //'extra_options' => $extra_options,
                    'experience_id' => $experience_id,
                ), $instance_payment_page_link );

            $return_link = add_query_arg( 'reservation_detail', '', $reservation_page_link );

            $payment = array(
                'intent' => 'sale',
                "redirect_urls" => array(
                    "return_url" => $return_link,
                    "cancel_url" => $cancel_link
                ),
                'payer' => array("payment_method" => "paypal"),
            );

            /* Prepare basic payment details
            *--------------------------------------*/
            $payment['transactions'][0] = array(
                'amount' => array(
                    'total' => $total_price,
                    'currency' => $submission_curency,
                    'details' => array(
                        'subtotal' => $total_price,
                        'tax' => '0.00',
                        'shipping' => '0.00'
                    )
                ),
                'description' => $payment_description
            );


            /* Prepare individual items
            *--------------------------------------*/
            $payment['transactions'][0]['item_list']['items'][] = array(
                'quantity' => '1',
                'name' => esc_html__( 'Experience ID','homey').' '.$experience_id,
                'price' => $total_price,
                'currency' => $submission_curency,
                'sku' => 'Paid Reservation',
            );

            /* Convert PHP array into json format
            *--------------------------------------*/
            $jsonEncode = json_encode($payment);
            $json_response = homey_execute_paypal_request( $url, $jsonEncode, $paypal_token );

            //print_r($json_response);
            foreach ($json_response['links'] as $link) {
                if($link['rel'] == 'execute'){
                    $payment_execute_url = $link['href'];
                } else  if($link['rel'] == 'approval_url'){
                    $payment_approval_url = $link['href'];
                }
            }

            // Save data in database for further use on processor page
            $output['payment_execute_url'] = $payment_execute_url;
            $output['paypal_token']        = $paypal_token;
            $output['reservation_id']      = '';
            $output['experience_id']          = $experience_id;
            $output['check_in_date']       = $check_in_date;
            $output['guests']              = $guests;
            $output['extra_options']      = $extra_options;
            $output['renter_message']      = $renter_message;
            $output['is_instance_booking'] = 1;
            $output['is_hourly'] = 0;

            $save_output[$userID]   =   $output;
            update_option('homey_paypal_transfer',$save_output);
            //Add host earning history

            echo json_encode(
                array(
                    'success' => true,
                    'message' => $local['processing_text'],
                    'payment_execute_url' => $payment_approval_url
                )
            );
            wp_die();
        }
    }
}

if( !function_exists('homey_add_instance_exp_booking') ) {
    function homey_add_instance_exp_booking($experience_id, $check_in_date, $guests, $renter_message, $extra_options, $user_id = null, $adult_guests=0, $child_guest=0) {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;

        if(!empty($user_id)) {
            $userID = $user_id;
        }

        $local = homey_get_localization();
        $allowded_html = array();
        $reservation_meta = array();

        $experience_owner_id  =  get_post_field( 'post_author', $experience_id );
        $title = $local['reservation_text'];

        $prices_array = homey_get_exp_prices($check_in_date, $experience_id, $guests, $extra_options );
        $price_per_person = $prices_array['price_per_person'];
        $persons_total_price = $prices_array['total_price'];

        // reservation meta information
        $price_per_person = $prices_array['price_per_person'];
        $persons_total_price = $prices_array['total_price'];
        $cleaning_fee = $prices_array['cleaning_fee'];
        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];
        $city_fee = $prices_array['city_fee'];
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];
        $booking_has_weekend = $prices_array['booking_has_weekend'];

        $reservation_meta['check_in_date'] = $check_in_date;
        $reservation_meta['price_per_person'] = $price_per_person;
        $reservation_meta['guests_total_price'] = $persons_total_price;
        $reservation_meta['reservation_experience_type'] = 'per_person';
        $reservation_meta['guests'] = $guests;
        $reservation_meta['adult_guests'] = $adult_guests;
        $reservation_meta['child_guest'] = $child_guest;
        $reservation_meta['experience_id'] = $experience_id;
        $reservation_meta['upfront'] = $upfront_payment;
        $reservation_meta['balance'] = $balance;
        $reservation_meta['total'] = $total_price;
        $reservation_meta['no_of_persons'] = $prices_array['persons_count'];
        $reservation_meta['additional_guests'] = $prices_array['additional_guests'];
        $reservation_meta['cleaning_fee'] = $cleaning_fee;
        $reservation_meta['city_fee'] = $city_fee;
        $reservation_meta['services_fee'] = $services_fee;
        $reservation_meta['taxes'] = $taxes;
        $reservation_meta['taxes_percent'] = $taxes_percent;
        $reservation_meta['security_deposit'] = $security_deposit;
        $reservation_meta['additional_guests_price'] = $additional_guests_price;
        $reservation_meta['additional_guests_total_price'] = $additional_guests_total_price;
        $reservation_meta['booking_has_weekend'] = $booking_has_weekend;
        // reservation meta information

        $reservation = array(
            'post_title'    => $title,
            'post_status'   => 'publish',
            'post_type'     => 'homey_e_reservation' ,
            'post_author'   => $userID
        );
        $reservation_id =  wp_insert_post($reservation, true );

        $reservation_update = array(
            'ID'         => $reservation_id,
            'post_title' => $title.' '.$reservation_id
        );

        wp_update_post( $reservation_update );

        update_post_meta($reservation_id, 'reservation_experience_id', $experience_id);
        update_post_meta($reservation_id, 'experience_owner', $experience_owner_id);
        update_post_meta($reservation_id, 'experience_renter', $userID);
        update_post_meta($reservation_id, 'reservation_checkin_date', $check_in_date);
        update_post_meta($reservation_id, 'reservation_guests', $guests);
        update_post_meta($reservation_id, 'reservation_adult_guests', $adult_guests);
        update_post_meta($reservation_id, 'reservation_child_guest', $child_guest);
        update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
        update_post_meta($reservation_id, 'reservation_status', 'booked');
        update_post_meta($reservation_id, 'extra_options', $extra_options);
        update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
        update_post_meta($reservation_id, 'reservation_balance', $balance);
        update_post_meta($reservation_id, 'reservation_total', $total_price);

        //Book dates
        $booked_persons_array = homey_make_exp_guests_booked($experience_id, $reservation_id);
        update_post_meta($experience_id, 'reservation_dates', $booked_persons_array);

        do_action('homey_create_messages_thread', $renter_message, $reservation_id, $user_id);
        homey_add_exp_earning($reservation_id);
        return $reservation_id;

    }
}

add_action('wp_ajax_nopriv_check_exp_availability_on_date_change', 'check_exp_availability_on_date_change');
add_action('wp_ajax_check_exp_availability_on_date_change', 'check_exp_availability_on_date_change');

if (!function_exists('check_exp_availability_on_date_change')) {
    function check_exp_availability_on_date_change()
    {
        $local = homey_get_localization();
        $allowded_html = array();
        $booking_proceed = true;

        $experience_id = intval($_POST['experience_id']);
        $exp_guests = intval($_POST['exp_guests']);
        $check_in_date = wp_kses($_POST['check_in_date'], $allowded_html);

        $reservation_booked_array = get_post_meta($experience_id, 'reservation_dates', true);
        if (empty($reservation_booked_array)) {
            $reservation_booked_array = homey_get_booked_persons($experience_id);
        }

        $reservation_pending_array = get_post_meta($experience_id, 'reservation_pending_dates', true);
        if (empty($reservation_pending_array)) {
            $reservation_pending_array = homey_get_exp_booking_pending_persons($experience_id);
        }

        $reservation_unavailable_array = get_post_meta($experience_id, 'reservation_unavailable', true);
        if (empty($reservation_unavailable_array)) {
            $reservation_unavailable_array = array();
        }

        $check_in = new DateTime($check_in_date);
        $check_in_unix = $check_in->getTimestamp();

        $total_no_of_attendee = get_post_meta($experience_id, 'homey_total_guests_plus_additional_guests', true );

        $remaining_no_of_attendee = remainingAttendeeSlots($total_no_of_attendee, $check_in_unix, $reservation_booked_array, $reservation_pending_array);

        if ( (array_key_exists($check_in_unix, $reservation_booked_array) && ($remaining_no_of_attendee < 1 || $remaining_no_of_attendee < $exp_guests) ) || (array_key_exists($check_in_unix, $reservation_pending_array) && ($remaining_no_of_attendee < 1 || $remaining_no_of_attendee < $exp_guests)  ) || array_key_exists($check_in_unix, $reservation_unavailable_array)) {

            $msg = $local['dates_not_available'];
            if($remaining_no_of_attendee > 0){
                $msg = $local['dates_not_available']. esc_html__(', remaining slots are', 'homey').' ' . $remaining_no_of_attendee.' of '. $total_no_of_attendee;
            }

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $msg
                )
            );
            wp_die();

        }

        echo json_encode(
            array(
                'success' => true,
                'message' => $local['dates_available']
            )
        );
        wp_die();
    }
} // check_exp_availability_on_date_change()

if( !function_exists('homey_calculate_booking_cost_admin') ) {
    function homey_calculate_booking_cost_admin($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);

        $experience_id     = intval($reservation_meta['experience_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);

        $prices_array = homey_get_exp_prices($check_in_date, $experience_id, $guests, $extra_options);

        $price_per_person = homey_formatted_price($prices_array['price_per_person'], true);
        $no_of_persons = $prices_array['persons_count'];

        $persons_total_price = homey_formatted_price($prices_array['persons_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_persons > 1) {
            $person_label = esc_html__('Persons', 'homey');
        } else {
            $person_label = esc_html__('Person', 'homey');
        }

        if($additional_guests > 1) {
            $add_guest_label = esc_html__('Guests', 'homey');
        } else {
            $add_guest_label = esc_html__('Guest', 'homey');
        }

        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<tr>
                    <td class="manage-column">'.$no_of_persons.' '.$person_label.' ('.$local['with_custom_period_and_weekend_label'].')</td> 
                    <td>'.$persons_total_price.'</td>
                    </tr>';

        } elseif($booking_has_weekend == 1) {
            $output .= '<tr>
                <td class="manage-column">'.$no_of_persons.' '.$person_label.' ('.$with_weekend_label.') </td>
                <td>'.$persons_total_price.'</td>
                </tr>';

        } elseif($booking_has_custom_pricing == 1) {
            $output .= '<tr>
                <td class="manage-column">'.$no_of_persons.' '.$person_label.' ('.$local['with_custom_period_label'].') </td> 
                <td>'.$persons_total_price.'</td>
                </tr>';

        } else {
            $output .= '<tr>
                <td class="manage-column">'.$price_per_person.' x '.$no_of_persons.' '.$person_label.' </td>
                <td>'.$persons_total_price.'</td>
                </tr>';
        }

        if(!empty($additional_guests)) {
            $output .= '<tr><td class="manage-column">'.$additional_guests.' '.$add_guest_label.'</td> <td>'.homey_formatted_price($additional_guests_total_price).'</td></tr>';
        }

        $output .= '<tr><td class="manage-column">'.$local['cs_cleaning_fee'].'</td> <td>'.$cleaning_fee.'</td></tr>';
        $output .= '<tr><td class="manage-column">'.$local['cs_city_fee'].'</td> <td>'.$city_fee.'</td></tr>';

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<tr><td class="manage-column">'.$local['cs_sec_deposit'].'</td> <td>'.homey_formatted_price($security_deposit).'</td></tr>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_services_fee'].'</td> <td>'.homey_formatted_price($services_fee).'</td></tr>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_taxes'].' '.$taxes_percent.'%</td> <td>'.homey_formatted_price($taxes).'</td></tr>';
        }


        $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_total'].'</strong></td> <td><strong>'.homey_formatted_price($total_price).'</strong></td></tr>';


        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_payment_due'].'</strong></td> <td><strong>'.homey_formatted_price($upfront_payment).'</strong></td></tr>';
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<tr><td class="manage-column"><i class="homey-icon homey-icon-information-circle"></i> '.$local['cs_pay_rest_1'].' <strong>'.homey_formatted_price($balance).'</strong> '.$local['cs_pay_rest_2'].'</td></tr>';
        }



        return $output;
    }
}

// Reservation cost

if( !function_exists('homey_calculate_exp_reservation_cost') ) {
    function homey_calculate_exp_reservation_cost($reservation_id, $collapse = false) {

        if(empty($reservation_id)) {
            return;
        }

        return homey_calculate_exp_reservation_cost_guestly($reservation_id, $collapse);

    }
}

if( !function_exists('homey_calculate_exp_reservation_cost_guestly') ) {
    function homey_calculate_exp_reservation_cost_guestly($reservation_id, $collapse = false) { // homey_calculate_exp_reservation_cost_personly => homey_calculate_exp_reservation_cost_guestly
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);

        $extra_options = get_post_meta($reservation_id, 'extra_options', true);
        $guests         = intval(isset($reservation_meta['guests']) ? $reservation_meta['guests'] : 0 );
        $price_per_person = homey_formatted_price(isset($reservation_meta['price_per_person'])?$reservation_meta['price_per_person']:0, true);
        $guests_total_price = homey_formatted_price(isset($reservation_meta['price_per_person'])? $reservation_meta['price_per_person'] * $reservation_meta['guests'] :0, false);
        $cleaning_fee = homey_formatted_price(isset($reservation_meta['cleaning_fee'])?$reservation_meta['cleaning_fee']:0);
        $services_fee = isset($reservation_meta['services_fee'])?$reservation_meta['services_fee']:0;
        $taxes = isset($reservation_meta['taxes'])?$reservation_meta['taxes']:0;
        $taxes_percent = isset($reservation_meta['taxes_percent'])?$reservation_meta['taxes_percent']:0;
        $city_fee = homey_formatted_price(isset($reservation_meta['city_fee'])?$reservation_meta['city_fee']:0);
        $security_deposit = isset($reservation_meta['security_deposit'])?$reservation_meta['security_deposit']:0;
        $additional_guests = isset($reservation_meta['additional_guests'])?$reservation_meta['additional_guests']:0;
        $additional_guests_price = isset($reservation_meta['additional_guests_price'])?$reservation_meta['additional_guests_price']:0;
        $additional_guests_total_price = isset($reservation_meta['additional_guests_total_price'])?$reservation_meta['additional_guests_total_price']:0;

        $upfront_payment = isset($reservation_meta['upfront'])?$reservation_meta['upfront']:0;

        $balance = isset($reservation_meta['balance'])?$reservation_meta['balance']:0;
        $total_price = isset($reservation_meta['total'])?$reservation_meta['total']:0;
        $booking_has_weekend = isset($reservation_meta['booking_has_weekend'])?$reservation_meta['booking_has_weekend']:0;
        $booking_has_custom_pricing = isset($reservation_meta['booking_has_custom_pricing'])?$reservation_meta['booking_has_custom_pricing']:0;
        $with_weekend_label = $local['with_weekend_label'];

        if($guests > 1) {
            $guest_label = esc_html__('Guests', 'homey');
        } else {
            $guest_label = esc_html__('Guest', 'homey');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $invoice_id = isset($_GET['invoice_id']) ? $_GET['invoice_id'] : '';
        $reservation_detail_id = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
        $is_host = false;
        $homey_invoice_buyer = get_post_meta($reservation_id, 'experience_renter', true);

        if( homey_is_host() && $homey_invoice_buyer != get_current_user_id() ) {
            $is_host = true;
        }

        $extra_prices = homey_get_exp_extra_prices($extra_options, $guests);
        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        if($is_host && !empty($services_fee)) {
            $total_price = $total_price - $services_fee;
        }

        if(!empty($extra_expenses)) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $total_price = $total_price + $expenses_total_price;
            $balance = $balance + $expenses_total_price;
        }

        if(!empty($extra_discount)) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $total_price = $total_price - $discount_total_price;
            //zahid.k added for discount
            $upfront_payment = $upfront_payment - $discount_total_price;
            //zahid.k added for discount
            $balance = $balance - $discount_total_price;
        }

        if(homey_option('exp_reservation_payment') == 'full') {
            $upfront_payment = $total_price;
            $balance = 0;
        }

        $start_div = '<div class="payment-list">';

        if($collapse) {
            $output = '<div class="payment-list-price-detail clearfix">';
            $output .= '<div class="pull-left">';
            $output .= '<div class="payment-list-price-detail-total-price">ffff '.$local['cs_total'].'</div>';
            $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
            $output .= '</div>';

            $output .= '<div class="pull-right text-right">';
            $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
            $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
            $output .= '</div>';
            $output .= '</div>';

            $start_div  = '<div class="collapse collapseExample" id="collapseExample">';
        }

        $output .= $start_div;
        $output .= '<ul>';

        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<li>'.$guests.' '.$guest_label.' ('.$local['with_custom_period_and_weekend_label'].') <span>'.$guests_total_price.'</span></li>';

        } elseif($booking_has_weekend == 1) {
            $output .= '<li>'.$guests.' '.$guest_label.' ('.$with_weekend_label.') <span>'.$guests_total_price.'</span></li>';

        } elseif($booking_has_custom_pricing == 1) {
            $output .= '<li>'.$guests.' '.$guest_label.' ('.$local['with_custom_period_label'].') <span>'.$guests_total_price.'</span></li>';

        } else {
            $output .= '<li>'.$price_per_person.' x '.$guests.' '.$guest_label.' <span>'.$guests_total_price.'</span></li>';
        }

        if(!empty($additional_guests)) {
            $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(isset($reservation_meta['cleaning_fee'])){
            if(!empty($reservation_meta['cleaning_fee']) && $reservation_meta['cleaning_fee'] != 0) {
                $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
            }
        }

        if(!empty($extra_prices)) {
            $output .= $extra_prices['extra_html'];
        }

        $services_fee = $services_fee > 0 ? $services_fee: 0;

        $city_fee = isset($reservation_meta['city_fee']) ? $reservation_meta['city_fee'] > 0 ? $reservation_meta['city_fee'] : 0 : 0;
        $sub_total_amnt = $total_price - $city_fee -  $security_deposit - $services_fee - $taxes;

        $output .= '<li class="sub-total">'. esc_html__('Sub Total', 'homey'). '<span>'. homey_formatted_price($sub_total_amnt) .'</span></li>';

        if(isset($reservation_meta['city_fee'])){
            if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
                $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
            }
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }


        if(!empty($services_fee) && !$is_host) {
            $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($extra_expenses)) {
            $output .= $extra_expenses['expenses_html'];
        }

        if(!empty($extra_discount)) {
            $output .= $extra_discount['discount_html'];
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(homey_option('exp_reservation_payment') == 'full') {

            if($is_host && !empty($services_fee)) {
                $upfront_payment = $upfront_payment - $services_fee;
            }
            $output .= '<li class="payment-due">'.$local['inv_total'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';

        } else {
            if(!empty($upfront_payment) && $upfront_payment != 0) {
                if($is_host && !empty($services_fee)) {
                    $upfront_payment = $upfront_payment - $services_fee;
                }
                $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
                $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
            }
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<li><i class="homey-icon homey-icon-information-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
        }

        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}
// end Reservation cost

if(!function_exists('homey_get_exp_extra_prices')) {
    function homey_get_exp_extra_prices($extra_options, $guests) {
        $total_extra_services = 0;
        $extra_prices_output = '';
        $output_array = array();

        if(!empty($extra_options)) {
            $is_first = 0;
            foreach ($extra_options as $extra_price) {
                $single_price = explode('|', $extra_price);
                if($is_first == 0){
                    $extra_prices_output .= '<li class="sub-total">'.esc_html__('Extra Services', 'homey').'</li>';
                }

                $name = $single_price[0];
                $price = doubleval($single_price[1]);
                $type = $single_price[2];

                if($type == 'single_fee') {
                    $price = $price;

                } elseif($type == 'per_person') {
                    $price = $price * $guests;
                }

                $total_extra_services = $total_extra_services + $price;

                $extra_prices_output .= '<li>'.esc_attr($name).' <span>'.homey_formatted_price($price).'</span></li>';
            }

            $output_array['extra_total_price'] = $total_extra_services;
            $output_array['extra_html'] = $extra_prices_output;

            return $output_array;

        }
    }
}

add_action( 'wp_ajax_nopriv_homey_calculate_exp_booking_cost', 'homey_calculate_exp_booking_cost' );
add_action( 'wp_ajax_homey_calculate_exp_booking_cost', 'homey_calculate_exp_booking_cost' );

if( !function_exists('homey_calculate_exp_booking_cost') ) {
    function homey_calculate_exp_booking_cost() {
        $allowded_html = array();

        $experience_id      = intval($_POST['experience_id']);
        $check_in_date      = wp_kses ( $_POST['check_in_date'], $allowded_html );
        $extra_options      = isset($_POST['extra_options']) ? $_POST['extra_options'] : '';
        $guests             = intval($_POST['guests']);

        calculate_exp_booking_cost_ajax_personly($check_in_date, $experience_id, $guests, $extra_options);

        wp_die();

    }
}

if( !function_exists('calculate_exp_booking_cost_ajax_personly') ) {
    function calculate_exp_booking_cost_ajax_personly($check_in_date, $experience_id, $guests, $extra_options) {

        $local = homey_get_localization();

        $prices_array = homey_get_exp_prices( $check_in_date, $experience_id, $guests, $extra_options );
//        echo '<pre>'; print_r($prices_array);
        
        $price_per_person = homey_formatted_price($prices_array['price_per_person'], true);
        $persons_total_price = homey_formatted_price($prices_array['persons_total_price'], true);

        $exp_total_price = homey_formatted_price($prices_array['total_price'], false);
        $no_of_persons = $prices_array['persons_count'];

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
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

//        dd($prices_array);

        $output = '<div class="payment-list-price-detail clearfix">';
    
        $output .= '<div class="pull-right text-right">';
        $output .= '<div class="payment-list-price-detail-total-price">'. esc_html__('Total', 'homey'). ' <span>'.$exp_total_price.'</span></div>';
        $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.esc_attr($local['cs_view_details']).'</a>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="collapse collapseExample" id="collapseExample">';
        $output .= '<ul>';

        if($no_of_persons > 1) {
            $person_label = esc_html__('Persons', 'homey');
        } else {
            $person_label = esc_html__('Person', 'homey');
        }

        if($additional_guests > 1) {
            $add_guest_label = esc_html__('Guests', 'homey');
        } else {
            $add_guest_label = esc_html__('Guest', 'homey');
        }

        if(!empty($additional_guests)) {
            $output .= '<li>'.esc_attr($additional_guests).' '.esc_attr($add_guest_label).' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        $output .= '<li class="homey_price_first">'.($price_per_person).' x '.esc_attr($no_of_persons).' '.esc_attr($person_label).'<span>'.$persons_total_price.'</span>';

        if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
            $output .= '<li>'.esc_attr($local['cs_cleaning_fee']).' <span>'.($cleaning_fee).'</span></li>';
        }

        if(!empty($extra_prices_html)) {
            $output .= $extra_prices_html;
        }

        $services_fee = $services_fee > 0 ? $services_fee: 0;
        $sub_total_amnt = $prices_array['total_price'] - $prices_array['city_fee'] -  $security_deposit - $services_fee - $taxes;
        //echo $sub_total_amnt .'='. $prices_array['total_price'] .'-'. $prices_array['city_fee'] .'-'.  $security_deposit .'-'. $services_fee .'-'. $taxes;

        $output .= '<li class="sub-total">'. esc_html__('Sub Total', 'homey'). '<span>'. homey_formatted_price($sub_total_amnt) .'</span></li>';

        if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>'.esc_attr($local['cs_city_fee']).' <span>'.($city_fee).'</span></li>';
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.esc_attr($local['cs_sec_deposit']).' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<li>'.esc_attr($local['cs_services_fee']).' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.esc_attr($local['cs_taxes']).' '.esc_attr($taxes_percent).'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<li class="payment-due">'.esc_attr($local['cs_payment_due']).' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<li><i class="homey-icon homey-icon-information-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
        }
        $output .= '</ul>';
        $output .= '</div>';

        $output_escaped = $output;
        print ''.$output_escaped;

        wp_die();

    }
}

if(!function_exists('homey_get_exp_prices')) {
    function homey_get_exp_prices($check_in_date, $experience_id, $guests, $extra_options = null) {
        $prefix = 'homey_';

        $enable_services_fee = homey_option('enable_exp_services_fee');
        $enable_taxes = homey_option('enable_taxes');
        $offsite_payment = homey_option('off-site-payment');
        $reservation_payment_type = homey_option('exp_reservation_payment');
        $booking_percent = homey_option('exp_booking_percent');
        $tax_type = homey_option('tax_type');
        $apply_taxes_on_service_fee  =   homey_option('apply_taxes_on_service_fee');
        $taxes_percent_global  =   homey_option('taxes_percent');
        $single_experience_tax = get_post_meta($experience_id, 'homey_tax_rate', true);

        $total_extra_services = 0;
        $extra_prices_html = "";
        $taxes_final = 0;
        $taxes_percent = 0;
        $total_price = 0;
        $total_guests_price = 0;
        $upfront_payment = 0;
        $persons_total_price = 0;
        $booking_has_weekend = 0;
        $booking_has_custom_pricing = 0;
        $balance = 0;
        $taxable_amount = 0;
        $period_persons = 0;
        $security_deposit = '';
        $additional_guests = '';
        $additional_guests_total_price = '';
        $services_fee_final = '';
        $taxes_fee_final = '';
        $prices_array = array();

        $experience_guests       = floatval( get_post_meta($experience_id, $prefix.'guests', true) );
        $personly_price           = floatval( get_post_meta($experience_id, $prefix.'night_price', true));
        $price_per_person         = $personly_price;
        $weekends_price          = floatval( get_post_meta($experience_id, $prefix.'weekends_price', true) );
        $weekends_days        = get_post_meta($experience_id, $prefix.'weekends_days', true);
        $security_deposit        = floatval( get_post_meta($experience_id, $prefix.'security_deposit', true) );

        $cleaning_fee            = floatval( get_post_meta($experience_id, $prefix.'cleaning_fee', true) );
        $cleaning_fee_type       = get_post_meta($experience_id, $prefix.'cleaning_fee_type', true);

        $city_fee                = floatval( get_post_meta($experience_id, $prefix.'city_fee', true) );
        $city_fee_type           = get_post_meta($experience_id, $prefix.'city_fee_type', true);

        $extra_guests_price      = floatval( get_post_meta($experience_id, $prefix.'additional_guests_price', true) );
        $additional_guests_price = $extra_guests_price;

        $allow_additional_guests = get_post_meta($experience_id, $prefix.'allow_additional_guests', true);

        $check_in        =  new DateTime($check_in_date);
        $check_in_unix   =  $check_in->getTimestamp();
        $check_in_unix_first_person   =  $check_in->getTimestamp();

        $check_out       =  new DateTime($check_in_date);
        $check_out_unix  =  $check_out->getTimestamp();

        $persons_count = $guests;

        //print_r($check_in_unix);
        // Check additional guests price
        if( $allow_additional_guests == 'yes' && $guests > 0 && !empty($guests) ) {
            if( $guests > $experience_guests) {
                $additional_guests = $guests - $experience_guests;

                $guests_price_return = homey_calculate_exp_guests_price($additional_guests, $additional_guests_price);

                // echo ', prev price='.$total_guests_price .' + weekend or reg price='. $guests_price_return.'<br>';
                $total_guests_price = $total_guests_price + $guests_price_return;
            }
        }
        //echo $price_per_person.' only price ';

        // Check for weekend and add weekend price
        // echo ' * This first date * '.date('d-m-Y',$check_in_unix).'<br>';

        $returnPrice = homey_get_exp_weekend_price( $check_in_unix, $weekends_price, $price_per_person, $weekends_days );
        $total_price = $total_price + $returnPrice;

        $check_in->modify('tomorrow');
        $check_in_unix =   $check_in->getTimestamp();

        $weekperson = date('N', $check_in_unix_first_person);
        if(homey_check_exp_weekend($weekperson, $weekends_days, $weekends_price)) {
            $booking_has_weekend = 1;
        }

//             echo ' * This date * '.date('d-m-Y',$check_in_unix).'<br>';

        $weekperson = date('N', $check_in_unix);
        if(homey_check_exp_weekend($weekperson, $weekends_days, $weekends_price)) {
            $booking_has_weekend = 1;
        }

        if( $allow_additional_guests == 'yes' && $guests > 0 && !empty($guests) ) {
            if( $guests > $experience_guests) {
                $additional_guests = $guests - $experience_guests;

                $guests_price_return = homey_calculate_exp_guests_price($additional_guests, $additional_guests_price);

                //echo ', prev price='.$total_guests_price .' + guest price='. $guests_price_return.'<br>';
                $total_guests_price = $total_guests_price + $guests_price_return;
            }
        }

        $returnPrice = homey_cal_exp_weekend_price($check_in_unix, $weekends_price, $price_per_person, $weekends_days);

//             echo ' the person => price='. $returnPrice.'<br>';
        if($guests < 1 ){
            $guests = 1;
        }

        $persons_total_price = $returnPrice * $guests;

        $total_price = $persons_total_price;

        $check_in->modify('tomorrow');
        $check_in_unix =   $check_in->getTimestamp();


        if( $cleaning_fee_type == 'daily' ) {
            $cleaning_fee = $cleaning_fee * $persons_count;
            $total_price = $total_price + $cleaning_fee;
        } else {
            $total_price = $total_price + $cleaning_fee;
        }

        //Extra prices =======================================
        if($extra_options != '') {

            $extra_prices_output = '';
            $is_first = 0;
            foreach ($extra_options as $extra_price) {
                if($is_first == 0){
                    $extra_prices_output .= '<li class="sub-total">'.esc_html__('Extra Services', 'homey').'</li>';
                } $is_first = 2;

                $ex_single_price = explode('|', $extra_price);

                $ex_name = $ex_single_price[0];
                $ex_price = floatval($ex_single_price[1]);
                $ex_type = $ex_single_price[2];

                if($ex_type == 'single_fee') {
                    $ex_price = $ex_price;

                } elseif($ex_type == 'per_person') {
                    $ex_price = $ex_price*$persons_count;
                } elseif($ex_type == 'per_guest') {
                    $ex_price = $ex_price*$guests;
                } elseif($ex_type == 'per_person_per_guest') {
                    $ex_price = $ex_price* $persons_count*$guests;
                }

                $total_extra_services = $total_extra_services + $ex_price;

                $extra_prices_output .= '<li>'.esc_attr($ex_name).'<span>'.homey_formatted_price($ex_price).'</span></li>';
            }

            $total_price = $total_price + $total_extra_services;
            $extra_prices_html = $extra_prices_output;
        }

        //Calculate taxes based of original price (Excluding city, security deposit etc)
        if($enable_taxes == 1) {

            if($tax_type == 'global_tax') {
                $taxes_percent = $taxes_percent_global;
            } else {
                if(!empty($single_experience_tax)) {
                    $taxes_percent = $single_experience_tax;
                }
            }

            $taxable_amount = $total_price + $total_guests_price;
            $taxes_final = homey_calculate_exp_taxes($taxes_percent, $taxable_amount);
            $total_price = $total_price + $taxes_final;
        }

        //Calculate sevices fee based of original price (Excluding cleaning, city, sevices fee etc)
        if($enable_services_fee == 1 && $offsite_payment != 1) {
            $services_fee_type  = homey_option('services_exp_fee_type');
            $services_fee  =   homey_option('exp_services_fee');
            $price_for_services_fee = $total_price + $total_guests_price;
            $services_fee_final = homey_calculate_services_fee($services_fee_type, $services_fee, $price_for_services_fee);
            $total_price = $total_price + $services_fee_final;
        }

        if( $city_fee_type == 'daily' ) {
            $city_fee = $city_fee * $persons_count;
            $total_price = $total_price + $city_fee;
        } else {
            $total_price = $total_price + $city_fee;
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $total_price = $total_price + $security_deposit;
        }

        if($total_guests_price !=0) {
            $total_price = $total_price + $total_guests_price;
        }

        $host_reservation_payment_type = homey_option('exp_reservation_payment');

        if($offsite_payment == 1 && !empty($host_reservation_payment_type)) {

            if($host_reservation_payment_type == 'percent') {
                if(!empty($host_booking_percent) && $host_booking_percent != 0) {
                    $upfront_payment = round($host_booking_percent*$total_price/100,2);
                }

            } elseif($host_reservation_payment_type == 'full') {
                $upfront_payment = $total_price;

            } elseif($host_reservation_payment_type == 'only_security') {
                $upfront_payment = $security_deposit;

            } elseif($host_reservation_payment_type == 'only_services') {
                $upfront_payment = $services_fee_final;

            } elseif($host_reservation_payment_type == 'services_security') {
                $upfront_payment = $security_deposit+$services_fee_final;
            }

        } else {

            if($reservation_payment_type == 'percent') {
                if(!empty($booking_percent) && $booking_percent != 0) {
                    $upfront_payment = round($booking_percent*$total_price/100,2);
                }

            } elseif($reservation_payment_type == 'full') {
                $upfront_payment = $total_price;

            } elseif($reservation_payment_type == 'only_security') {
                $upfront_payment = $security_deposit;

            } elseif($reservation_payment_type == 'only_services') {
                $upfront_payment = $services_fee_final;

            } elseif($reservation_payment_type == 'services_security') {
                $upfront_payment = $security_deposit+$services_fee_final;
            }
        }

        $balance = $total_price - $upfront_payment;

        $prices_array['price_per_person'] = $price_per_person;
        $prices_array['persons_total_price'] = $persons_total_price;
        $prices_array['total_price']     = $total_price;
        $prices_array['check_in_date']   = $check_in_date;
        $prices_array['cleaning_fee']    = $cleaning_fee;
        $prices_array['city_fee']        = $city_fee;
        $prices_array['services_fee']    = $services_fee_final;
        $prices_array['persons_count']   = $persons_count;
        $prices_array['taxes']           = $taxes_final;
        $prices_array['taxes_percent']   = $taxes_percent;
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

add_action('wp_ajax_nopriv_homey_instant_exp_booking', 'homey_instant_exp_booking');
add_action('wp_ajax_homey_instant_exp_booking', 'homey_instant_exp_booking');
if(!function_exists('homey_instant_exp_booking')) {
    function homey_instant_exp_booking() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();
        $instace_page_link = homey_get_template_link_2('template/template-instance-exp-booking.php');

        $no_login_needed_for_exp_booking = homey_option('no_login_needed_for_exp_booking');
        $email = wp_kses(@$_POST['new_reser_exp_request_user_email'], $allowded_html);

        if ( $no_login_needed_for_exp_booking == 'no' && ( !is_user_logged_in() || $userID === 0 ) ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['login_for_reservation']
                )
            );
            wp_die();
        }

        if ( empty($instace_page_link) ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['instance_booking_page']
                )
            );
            wp_die();
        }

        //check security
        $nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $nonce, 'reservation-security-nonce' ) ) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['security_check_text']
                )
            );
            wp_die();
        }

        $experience_id = intval($_POST['experience_id']);
        $experience_owner_id  =  get_post_field( 'post_author', $experience_id );
        $check_in_date     =  wp_kses ( $_POST['check_in_date'], $allowded_html );
        $guests   =  intval($_POST['guests']);
        $guest_message   =  wp_kses ( $_POST['guest_message'], $allowded_html );
        $extra_options  = $_POST['extra_options'];

        if($no_login_needed_for_exp_booking == 'no' && $userID == $experience_owner_id) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['own_experience_error']
                )
            );
            wp_die();
        }

        $booking_hide_fields = homey_option('booking_hide_fields');
        if ( (empty($guests) || $guests === 0) && $booking_hide_fields['guests'] != 1 ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['choose_guests']
                )
            );
            wp_die();
        }

        if ($current_user->ID == 0 && $no_login_needed_for_exp_booking == "yes" && isset($_REQUEST['new_reser_exp_request_user_email'])) {
            $user = get_user_by('email', $email);

            if (empty(trim($email))) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => esc_html__('Enter email address', 'homey')
                    )
                );
                wp_die();
            }

            if (isset($user->ID)) {
                $hm_no_login_user_id = $user->ID;
            } else { //create user from email
                $user_login = $email;
                $user_email = $email;

                $display_name  = $email;
                $nickname   = $email; ;
                $first_name   = "New" ;
                $last_name   = "User" ;
                $description    = "New User";

                $role = 'homey_renter';
                $user_pass = wp_generate_password(8, false);
                $userdata = compact('user_login', 'user_email', 'user_pass', 'role', 'display_name', 'nickname', 'first_name', 'last_name', 'description');
                $hm_no_login_user_id = wp_insert_user($userdata);

                if ($hm_no_login_user_id > 0 && homey_option('hm_no_login_notify', 0) == 1) {
                    homey_wp_new_user_notification($hm_no_login_user_id, $user_pass);
                }

                update_user_meta($hm_no_login_user_id, 'viaphp', 1);
            }
        }// creating user if no login is needed is on

        $instance_page = add_query_arg( array(
            'check_in' => $check_in_date,
            'guest' => $guests,
            'guest_message' => $guest_message,
            'extra_options' => $extra_options,
            'experience_id' => $experience_id,
            'reservation_no_userHash' => hashNoUserId($hm_no_login_user_id),

        ), $instace_page_link );

        echo json_encode(
            array(
                'success' => true,
                'message' => __('Submitting, Please wait...', 'homey'),
                'instance_url' =>  $instance_page
            )
        );
        wp_die();
    }
}

if(!function_exists('check_exp_booking_availability')) {
    function check_exp_booking_availability($check_in_date, $experience_id, $guests, $adult_guest=0, $child_guest=0) {
        $return_array = array();
        $local = homey_get_localization();
        $booking_proceed = true;

        $booking_hide_fields = homey_option('booking_hide_fields');

        $homey_allow_additional_guests = get_post_meta($experience_id, 'homey_allow_additional_guests', true);
        $allowed_guests = get_post_meta($experience_id, 'homey_guests', true);

        if(!empty($allowed_guests)) {
            if( ($homey_allow_additional_guests != 'yes') && ($guests > $allowed_guests)) {
                $return_array['success'] = false;
                $return_array['message'] = $local['guest_allowed'].' '.$allowed_guests;
                return $return_array;
            }
        }

        if(empty($check_in_date) && empty($guests)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['fill_all_fields'];
            return $return_array;
        }

        if(empty($check_in_date)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['choose_checkin'];
            return $return_array;
        }
        
        if(empty($guests) && $booking_hide_fields['guests'] != 1) {
            $return_array['success'] = false;
            $return_array['message'] = $local['choose_guests'];
            
            return $return_array;
        }

        if(!$booking_proceed) {
            $return_array['success'] = false;
            $return_array['message'] = $local['ins_book_proceed'];
            return $return_array;
        }

        $reservation_booked_array = get_post_meta($experience_id, 'reservation_dates', true);
        if(empty($reservation_booked_array)) {
            $reservation_booked_array = homey_get_booked_persons($experience_id);
        }

        $reservation_pending_array = get_post_meta($experience_id, 'reservation_pending_dates', true);
        if(empty($reservation_pending_array)) {
            $reservation_pending_array = homey_get_exp_booking_pending_persons($experience_id);
        }

        $reservation_unavailable_array = get_post_meta($experience_id, 'reservation_unavailable', true);
        if(empty($reservation_unavailable_array)) {
            $reservation_unavailable_array = array();
        }

        $check_in      = new DateTime($check_in_date);
        $check_in_unix = $check_in->getTimestamp();

        $total_no_of_attendee = get_post_meta($experience_id, 'homey_total_guests_plus_additional_guests', true );
        $remaining_no_of_attendee = remainingAttendeeSlots($total_no_of_attendee, $check_in_unix, $reservation_booked_array, $reservation_pending_array);

        if ( (array_key_exists($check_in_unix, $reservation_booked_array) && ($remaining_no_of_attendee < 1 || $remaining_no_of_attendee < $guests) )
            || (array_key_exists($check_in_unix, $reservation_pending_array) && ($remaining_no_of_attendee < 1 || $remaining_no_of_attendee < $guests)  )
            || array_key_exists($check_in_unix, $reservation_unavailable_array)
        ) {

            $return_array['success'] = false;

            $msg = $local['dates_not_available'];
            if($remaining_no_of_attendee > 0){
                $msg = $local['dates_not_available']. esc_html__(', remaining slots are', 'homey').' ' . $remaining_no_of_attendee.' of '. $total_no_of_attendee;
            }

            $return_array['message'] = $msg;

            if(homey_is_instance_page()) {
                $return_array['message'] = $local['ins_unavailable'];
            }

            return $return_array; //dates are not available
        }
       
        //dates are available
        $return_array['success'] = true;
        $return_array['message'] = $local['dates_available'];
        
        return $return_array;

    }
}

if( !function_exists('calculate_exp_booking_cost_instance') ) {
    function calculate_exp_booking_cost_instance() {
        $local = homey_get_localization();
        $allowded_html = array();

        $experience_id     = intval($_GET['experience_id']);
        $check_in_date  = wp_kses ( $_GET['check_in'], $allowded_html );
        $guests         = intval($_GET['guest']);
        $extra_options  = isset($_GET['extra_options']) ? $_GET['extra_options'] : '';

        $prices_array = homey_get_exp_prices( $check_in_date, $experience_id, $guests, $extra_options );
//         dd($prices_array, 0);
        $price_per_person = homey_formatted_price($prices_array['price_per_person'], true);
        $no_of_persons = $prices_array['persons_count'];

        $persons_total_price = homey_formatted_price($prices_array['persons_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
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

        if($no_of_persons > 1) {
            $person_label = esc_html__('Persons', 'homey');
        } else {
            $person_label = esc_html__('Person', 'homey');
        }

        if($additional_guests > 1) {
            $add_guest_label = esc_html__('Guests', 'homey');
        } else {
            $add_guest_label = esc_html__('Guest', 'homey');
        }

        $output = '<div class="payment-list-price-detail clearfix">';
        $output .= '<div class="pull-left">';
        $output .= '<div class="payment-list-price-detail-total-price">'.$local['cs_total'].'</div>';
        $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
        $output .= '</div>';

        $output .= '<div class="pull-right text-right">';
        $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
        $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="collapse collapseExample" id="collapseExample">';
        $output .= '<ul>';

        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<li>'.$no_of_persons.' '.$person_label.' ('.$local['with_custom_period_and_weekend_label'].') <span>'.$persons_total_price.'</span></li>';

        } elseif($booking_has_weekend == 1) {
            $output .= '<li>'.$no_of_persons.' '.$person_label.' ('.$with_weekend_label.') <span>'.$persons_total_price.'</span></li>';

        } elseif($booking_has_custom_pricing == 1) {
            $output .= '<li>'.$no_of_persons.' '.$person_label.' ('.$local['with_custom_period_label'].') <span>'.$persons_total_price.'</span></li>';

        } else {
            $output .= '<li>'.$price_per_person.' x '.$no_of_persons.' '.$person_label.' <span>'.$persons_total_price.'</span></li>';
        }

        if(!empty($additional_guests)) {
            $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
            $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
        }

        if(!empty($extra_prices_html)) {
            $output .= $extra_prices_html;
        }

        $services_fee = $services_fee > 0 ? $services_fee: 0;
        $sub_total_amnt = $prices_array['total_price'] - $prices_array['city_fee'] -  $security_deposit - $services_fee - $taxes;
        $output .= '<li class="sub-total">'. esc_html__('Sub Total', 'homey'). '<span>'. homey_formatted_price($sub_total_amnt) .'</span></li>';

        if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<li><i class="homey-icon homey-icon-information-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
        }

        $output .= '</ul>';
        $output .= '</div>';

        $output_escaped = $output;
        print ''.$output_escaped;

       // wp_die();
    }
}

/* -----------------------------------------------------------------------------------------------------------
*  Stripe Form
-------------------------------------------------------------------------------------------------------------*/

if( !function_exists('homey_stripe_payment_exp') ) {
    function homey_stripe_payment_exp( $reservation_id ) {

        $allowded_html = array();

        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $user_email = $current_user->user_email;
        $reservation_payment_type = homey_option('exp_reservation_payment');

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);


        $experience_id     = intval($reservation_meta['experience_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);

        $prices_array = homey_get_exp_prices($check_in_date, $experience_id, $guests, $extra_options);
        $upfront_payment = floatval( $reservation_meta['upfront'] );

        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        if( ! empty($extra_expenses) && $reservation_payment_type == 'full' ) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $upfront_payment = $upfront_payment + $expenses_total_price;
        }

        if( ! empty($extra_discount) && $reservation_payment_type == 'full' ) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $upfront_payment = $upfront_payment - $discount_total_price;
        }

        $minimum_currency_amount = get_minimum_currency();
        if($upfront_payment < $minimum_currency_amount){
            echo $minimum_amount_error = esc_html__( "You can't pay using Stripe because minimum amount limit is 0.5",'homey');
            return $minimum_amount_error;
        }

        $description = esc_html__( 'Reservation ID','homey').' '.$reservation_id;

        require_once( HOMEY_PLUGIN_PATH . '/classes/class-stripe.php' );

        $stripe_payments = new Homey_Stripe($userID);

        print '<div class="stripe-wrapper" id="homey_stripe_simple"> ';
        $metadata=array(
            'reservation_id_for_stripe' =>  $reservation_id,
            'userID'                    =>  $userID,
            'is_hourly'                 =>  0,
            'payment_type'              =>  'reservation_fee',
            'extra_options'             =>  ($extra_options == '') ? 0 : 1,
            'message'                   =>  esc_html__( 'Reservation Payment','homey')
        );

        if($upfront_payment > 0){
            $stripe_payments->homey_stripe_form($upfront_payment, $metadata, $description);
        }else{
            $message_text = esc_html__('Your amount in your wallet is: ', 'homey');
            $upfront_payment_with_symbol = homey_option("currency_symbol").' '.$upfront_payment;
            echo '<h3>'.$message_text.' '.$upfront_payment_with_symbol.'</h3>';
        }

        print'
        </div>';



    }
}

if( !function_exists('homey_stripe_payment_instance_exp') ) {
    function homey_stripe_payment_instance_exp($experience_id, $check_in, $guests, $renter_message = '') {

        $allowded_html = array();
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $user_email = $current_user->user_email;

        $experience_id     = intval($experience_id);
        $check_in_date  = wp_kses ($check_in, $allowded_html);
        $renter_message = $renter_message;
        $guests         = intval($guests);

        $extra_options = isset($_GET['extra_options']) ? $_GET['extra_options'] : '';

        update_user_meta($userID, 'extra_prices', $extra_options);

        if(!empty($extra_options)) {
            $extra_prices = 1;
        } else {
            $extra_prices = 0;
        }

        $check_availability = check_exp_booking_availability($check_in_date, $experience_id, $guests);

        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if(!$is_available) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $check_message,
                    'payment_execute_url' => ''
                )
            );
            wp_die();

        } else {

            $prices_array = homey_get_exp_prices($check_in_date, $experience_id, $guests, $extra_options);
            $upfront_payment  =  floatval( $prices_array['upfront_payment'] );

            $upfront_payment = round($upfront_payment, 2);

        }

        $minimum_currency_amount = get_minimum_currency();
        if($upfront_payment < $minimum_currency_amount){
            echo $minimum_amount_error = esc_html__( "You can't pay using Stripe because minimum amount limit is 0.5",'homey');
            return $minimum_amount_error;
        }

        require_once( HOMEY_PLUGIN_PATH . '/classes/class-stripe.php' );

        $description = esc_html__( 'Instant Reservation, Experience ID','homey').' '.$experience_id;

        $stripe_payments = new Homey_Stripe($userID);

        print '<div class="stripe-wrapper" id="homey_stripe_simple"> ';
        $metadata=array(
            'experience_id'    =>  $experience_id,
            'reservation_id_for_stripe' =>  0,
            'userID'              =>  $userID,
            'is_experience'       =>  1,
            'is_instance_booking' =>  1,
            'check_in_date'       =>  $check_in_date,
            'guests'              =>  $guests,
            'extra_options'       =>  $extra_prices,
            'guest_message'       =>  $renter_message,
            'payment_type'        =>  'reservation_fee',
            'message'             =>  esc_html__( 'Reservation Payment','homey')
        );

        $stripe_payments->homey_stripe_form($upfront_payment, $metadata, $description);
        print'
        </div>';

    }
}

if (!function_exists("homey_make_exp_guests_booked")) {
    function homey_make_exp_guests_booked($experience_id, $resID ) {
        $now = time();
        $personsAgo = $now-3*24*60*60;

        $check_in_date  = get_post_meta( $resID, 'reservation_checkin_date', true );
        $no_of_attendee = get_post_meta( $resID, 'reservation_guests', true );

        $reservation_dates_array = get_post_meta($experience_id, 'reservation_dates', true );

        if( !is_array($reservation_dates_array) || empty($reservation_dates_array) ) {
            $reservation_dates_array  = array();
        }

        $unix_time_start = strtotime ($check_in_date);

        if ($unix_time_start > $personsAgo) {
            $check_in       =   new DateTime($check_in_date);
            $check_in_unix =   $check_in->getTimestamp();

            if(isset($reservation_dates_array[$check_in_unix]['reservation_ids'])){
                $resID = $reservation_dates_array[$check_in_unix]['reservation_ids']. ',' .$resID;
                $no_of_attendee = $reservation_dates_array[$check_in_unix]['no_of_attendee'] + $no_of_attendee;
            }

            $reservation_dates_array[$check_in_unix]['reservation_ids'] = $resID;
            $reservation_dates_array[$check_in_unix]['no_of_attendee'] = $no_of_attendee;
        }

        return $reservation_dates_array;
    }
}

if (!function_exists("homey_get_exp_booking_pending_persons")) {
    function homey_get_exp_booking_pending_persons($experience_id) {
        $now = time();
        $personsAgo = $now-3*24*60*60;

        $args = array(
            'post_type'        => 'homey_e_reservation',
            'post_status'      => 'any',
            'posts_per_page'   => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key'       => 'reservation_experience_id',
                    'value'     => $experience_id,
                    'type'      => 'NUMERIC',
                    'compare'   => '='
                ),
                array(
                    'key'       => 'reservation_status',
                    'value'     => 'declined',
                    'type'      => 'CHAR',
                    'compare'   => '!='
                ),
                array(
                    'key'       => 'reservation_status',
                    'value'     => 'cancelled',
                    'type'      => 'CHAR',
                    'compare'   => '!='
                )
            )
        );

        $pending_dates_array = get_post_meta($experience_id, 'reservation_pending_dates', true );

        if( !is_array($pending_dates_array) || empty($pending_dates_array) ) {
            $pending_dates_array  = array();
        }

        $wpQry = new WP_Query($args);

        if ($wpQry->have_posts()) {

            while ($wpQry->have_posts()): $wpQry->the_post();

                $resID = get_the_ID();

                $no_of_attendee = get_post_meta( $resID, 'reservation_guests', true );

                $check_in_date  = get_post_meta( $resID, 'reservation_checkin_date', true );
                $check_in       =   new DateTime($check_in_date);

                $check_in_unix  =   $check_in->getTimestamp();

                if(isset($pending_dates_array[$check_in_unix]['reservation_ids'])){
                    $resID = $pending_dates_array[$check_in_unix]['reservation_ids']. ',' .$resID;
                    $no_of_attendee = $pending_dates_array[$check_in_unix]['no_of_attendee'] + $no_of_attendee;
                }

                $pending_dates_array[$check_in_unix]['reservation_ids'] = $resID;
                $pending_dates_array[$check_in_unix]['no_of_attendee'] = $no_of_attendee;

//                echo '<pre>'. $resID; print_r($check_in_date); exit;
            endwhile;
            wp_reset_postdata();
        }

        return $pending_dates_array;

    }
}

add_action( 'wp_ajax_homey_confirm_exp_reservation', 'homey_confirm_exp_reservation' );
if(!function_exists('homey_confirm_exp_reservation')) {
    function homey_confirm_exp_reservation() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $no_upfront = homey_option('exp_reservation_payment');

        $date = date( 'Y-m-d G:i:s', current_time( 'timestamp', 0 ));

        $reservation_id = intval($_POST['reservation_id']);

        $experience_owner = get_post_meta($reservation_id, 'experience_owner', true);
        $experience_renter = get_post_meta($reservation_id, 'experience_renter', true);

        $renter = homey_usermeta($experience_renter);
        $renter_email = $renter['email'];

        if( !homey_is_admin() && $experience_owner != $userID ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => homey_get_reservation_notification('not_owner')
                )
            );
            wp_die();
        }

        // If no upfront option select then book at this step
        if($no_upfront == 'no_upfront') {

            homey_booking_with_no_upfront($reservation_id);

            echo json_encode(
                array(
                    'success' => true,
                    'message' => homey_get_reservation_notification('booked')
                )
            );

        } else {
            // Set reservation status from under_review to available
            update_post_meta($reservation_id, 'reservation_status', 'available');
            update_post_meta($reservation_id, 'reservation_confirm_date_time', $date );

            echo json_encode(
                array(
                    'success' => true,
                    'message' => homey_get_reservation_notification('available')
                )
            );

            $email_args = array('reservation_detail_url' => exp_reservation_detail_link($reservation_id) );
            homey_email_composer( $renter_email, 'confirm_reservation', $email_args );
        }

        wp_die();
    }
}


if(!function_exists('homey_get_exp_reservation_action')) {
    function homey_get_exp_reservation_action($status, $upfront_payment, $payment_link, $ID, $class) {
        $action = '';
        $local = homey_get_localization();

        $offsite_payment = homey_option('off-site-payment');

        if(homey_experience_guest($ID)) {

            if($status == 'under_review') {
                $action = '<span class="btn btn-success-outlined '.esc_attr($class).'"><i class="homey-icon homey-icon-check-circle-1"></i>'.esc_html__('Submitted', 'homey').'</span>';

                $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="collapse" id="cancel-exp-reservation-btn" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Cancel', 'homey').'</button>';

            } elseif ($status == 'available') {

                if( homey_is_woocommerce() ) {

                    $action = '<a href="#" data-reservation_id="'.intval($ID).'" class="homey-woo-exp-reservation-pay btn btn-success '.esc_attr($class).'">'.esc_html__('Pay Now', 'homey').' '.$upfront_payment.'</a>';

                } else {
                    $action = '<a href="'.esc_url($payment_link).'" class="btn btn-success '.esc_attr($class).'">'.esc_html__('Pay Now', 'homey').' '.$upfront_payment.'</a>';
                }

                $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="collapse" id="cancel-exp-reservation-btn" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Cancel', 'homey').'</button>';

            } elseif ($status == 'booked') {
                $action = '<span class="btn btn-success-outlined '.esc_attr($class).'"><i class="homey-icon homey-icon-check-circle-1"></i> '.esc_html__('Booked', 'homey').'</span>';

                $action .= '<button class="btn btn-grey-light btn-full-width" data-toggle="collapse" id="cancel-exp-reservation-btn" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Cancel', 'homey').'</button>';

            } elseif($status == 'waiting_host_payment_verification') {
                $action = '<span class="btn btn-warning-outlined '.esc_attr($class).'"> '.esc_html__('Waiting Approval', 'homey').'</span>';
            }

        } else {

            if($status == 'under_review') {

                if($offsite_payment == 1) {
                    $action = '<button data-reservation_id="'.intval($ID).'" class="confirm-offsite-exp-reservation btn btn-success '.esc_attr($class).'">'.esc_html__('Confirm Availability', 'homey').'</button>';
                } else {
                    $action = '<button data-reservation_id="'.intval($ID).'" class="confirm-exp-reservation btn btn-success '.esc_attr($class).'">'.esc_html__('Confirm Availability', 'homey').'</button>';
                }

                $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="collapse" id="decline-exp-reservation-btn" data-target="#decline-exp-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Decline', 'homey').'</button>';

            } elseif ($status == 'available') {
                $action = '<span class="btn btn-success-outlined '.esc_attr($class).'"><i class="homey-icon homey-icon-check-circle-1"></i>'.esc_html__('Available', 'homey').'</span>';

                $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="collapse" id="decline-exp-reservation-btn" data-target="#decline-exp-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Decline', 'homey').'</button>';


            } elseif ($status == 'booked') {
                $action = '<span class="btn btn-success-outlined '.esc_attr($class).'"><i class="homey-icon homey-icon-check-circle-1"></i> '.esc_html__('Booked', 'homey').'</span>';

                $action .= '<button class="btn btn-grey-light btn-full-width" data-toggle="collapse" id="cancel-exp-reservation-btn" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Cancel', 'homey').'</button>';

            } elseif($status == 'waiting_host_payment_verification') {
                $action = '<a href="#" data-id="'.intval($ID).'" class="mark-as-paid-exp btn btn-success '.esc_attr($class).'">'.esc_html__('Payment Received? Mark as Paid', 'homey').'</a>';
            }

        }

        if ($status == 'declined') {
            $action = '<span class="btn btn-danger-outlined '.esc_attr($class).'"><i class="homey-icon homey-icon-check-circle-1"></i> '.esc_html__('Declined', 'homey').'</span>';
        }

        if( !homey_experience_guest($ID) && $status == 'under_review') {
            $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="modal" data-target="#modal-exp-extra-expenses">'.esc_html__('Extra Expenses', 'homey').'</button>';

            $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="modal" data-target="#modal-exp-discount" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Discount', 'homey').'</button>';
        }

        return $action;

    }
}

if(!function_exists('homey_exp_reservation_action')) {
    function homey_exp_reservation_action($status, $upfront_payment, $payment_link, $ID, $class) {
        echo homey_get_exp_reservation_action($status, $upfront_payment, $payment_link, $ID, $class);
    }
}


if(!function_exists('homey_get_e_reservation_label')) {
    function homey_get_e_reservation_label($status, $reservation_id = null) {
        $status_label = '<span class="label label-warning">'.esc_html__(ucfirst($status), 'homey').'</span>';
        $local = homey_get_localization();

        if(homey_experience_guest($reservation_id)) {

            if($status == 'under_review') {
                $status_label = '<span class="label label-warning">'.$local['under_review_label'].'</span>';
            } elseif($status == 'available') {
                $status_label = '<span class="label label-secondary">'.$local['res_avail_label'].'</span>';
            }

        } else {
            if($status == 'under_review') {
                $status_label = '<span class="label label-secondary">'.$local['new_label'].'</span>';

            } elseif($status == 'available') {
                $status_label = '<span class="label label-secondary">'.$local['payment_process_label'].'</span>';
            }
        }

        if($status == 'booked') {
            $status_label = '<span class="label label-success">'.$local['res_booked_label'].'</span>';

        } elseif ($status == 'declined') {
            $status_label = '<span class="label label-danger">'.$local['res_declined_label'].'</span>';

        } elseif ($status == 'cancelled') {
            $status_label = '<span class="label label-grey">'.$local['res_cancelled_label'].'</span>';
        }

        return $status_label;

    }
}

if(!function_exists('homey_e_reservation_label')) {
    function homey_e_reservation_label($status, $reservation_id = null) {
        echo homey_get_e_reservation_label($status, $reservation_id);
    }
}

if(!function_exists('homey_calculate_exp_guests_price')) {
    function homey_calculate_exp_guests_price($additional_guests, $additional_guests_price) {
        return  $additional_guests_price * $additional_guests;
    }
}

if(!function_exists('homey_cal_exp_weekend_price') ) {
    function homey_cal_exp_weekend_price($check_in_unix, $weekends_price, $price_per_person, $weekends_days){
        $weekperson = date('N', $check_in_unix);

        if($weekends_days == 'sat_sun' && ($weekperson ==6 || $weekperson==7)) {
            $return_price = homey_get_exp_weekend_price($check_in_unix, $weekends_price, $price_per_person, $weekends_days);

        } elseif($weekends_days == 'fri_sat' && ($weekperson ==5 || $weekperson==6)) {
            $return_price = homey_get_exp_weekend_price($check_in_unix, $weekends_price, $price_per_person, $weekends_days);

        } elseif($weekends_days == 'thurs_fri_sat' && ($weekperson ==4 || $weekperson ==5 || $weekperson==6)) {
            $return_price = homey_get_exp_weekend_price($check_in_unix, $weekends_price, $price_per_person, $weekends_days);

        } elseif($weekends_days == 'fri_sat_sun' && ($weekperson ==5 || $weekperson ==6 || $weekperson==7)) {
            $return_price = homey_get_exp_weekend_price($check_in_unix, $weekends_price, $price_per_person, $weekends_days);

        } else {
            $return_price = $price_per_person;
        }

        return $return_price;

    }
}

if(!function_exists('homey_get_exp_weekend_price')) {
    function homey_get_exp_weekend_price($check_in_unix, $weekends_price, $price_per_person, $weekends_days) {
        if( isset($period_price[$check_in_unix]) && isset( $period_price[$check_in_unix]['weekend_price'] ) &&  $period_price[$check_in_unix]['weekend_price']!=0 ) {

            $return_price = $period_price[$check_in_unix]['weekend_price'];

        } elseif(!empty($weekends_price) && $weekends_price != 0) {
            $return_price = $weekends_price;
        } else {
            $return_price = $price_per_person;
        }

        return $return_price;
    }
}

if(!function_exists('homey_check_exp_weekend')) {
    function homey_check_exp_weekend($weekperson, $weekends_days, $weekends_price) {

        if(empty($weekends_price) && $weekends_price == 0 ) {
            return false;

        } else {

            if($weekends_days == 'sat_sun' && ($weekperson ==6 || $weekperson==7)) {
                return true;

            } elseif($weekends_days == 'fri_sat' && ($weekperson ==5 || $weekperson==6)) {
                return true;

            } elseif($weekends_days == 'thurs_fri_sat' && ($weekperson ==4 || $weekperson ==5 || $weekperson==6)) {
                return true;

            } elseif($weekends_days == 'fri_sat_sun' && ($weekperson ==5 || $weekperson ==6 || $weekperson==7)) {
                return true;

            } else {
                return false;
            }
        }

        return false;

    }
}


if(!function_exists('homey_calculate_exp_taxes')) {
    function homey_calculate_exp_taxes($taxes_percent, $total_price) {

        if( empty($taxes_percent) || $taxes_percent == 0 ) {
            $taxes = 0;
        } else {
            $taxes = round($taxes_percent*$total_price/100,2);
        }
        return $taxes;

    }
}

if(!function_exists('homey_get_exp_weekend_price')) {
    function homey_get_exp_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price) {
        if( isset($period_price[$check_in_unix]) && isset( $period_price[$check_in_unix]['weekend_price'] ) &&  $period_price[$check_in_unix]['weekend_price']!=0 ){

            $return_price = $period_price[$check_in_unix]['weekend_price'];

        } elseif(!empty($weekends_price) && $weekends_price != 0) {
            $return_price = $weekends_price;
        } else {
            $return_price = $price_per_night;
        }

        return $return_price;
    }
}

if (!function_exists("homey_get_booked_persons")) {
    function homey_get_booked_persons($experience_id) {
        $now = time();
        $daysAgo = $now-3*24*60*60;

        $args = array(
            'post_type'        => 'homey_e_reservation',
            'post_status'      => 'any',
            'posts_per_page'   => -1,
            'meta_query' => array(
                array(
                    'key'       => 'reservation_experience_id',
                    'value'     => $experience_id,
                    'type'      => 'NUMERIC',
                    'compare'   => '='
                ),
                array(
                    'key'       =>  'reservation_status',
                    'value'     =>  'booked',
                    'compare'   =>  '='
                )
            )
        );

        $booked_dates_array = get_post_meta($experience_id, 'reservation_dates', true );

        if( !is_array($booked_dates_array) || empty($booked_dates_array) ) {
            $booked_dates_array  = array();
        }

        $wpQry = new WP_Query($args);

        if ($wpQry->have_posts()) {

            while ($wpQry->have_posts()): $wpQry->the_post();

                $resID = get_the_ID();

                $check_in_date  = get_post_meta( $resID, 'reservation_checkin_date', true );

                $unix_time_start = strtotime ($check_in_date);

                if ($unix_time_start > $daysAgo) {
                    $check_in       =   new DateTime($check_in_date);
                    $check_in_unix  =   $check_in->getTimestamp();

                    $booked_dates_array[$check_in_unix] = $resID;
                }
            endwhile;
            wp_reset_postdata();
        }

        return $booked_dates_array;

    }
}

add_action( 'wp_ajax_homey_make_exp_date_unavaiable', 'homey_make_exp_date_unavaiable' );
if(!function_exists('homey_make_exp_date_unavaiable')) {
    function homey_make_exp_date_unavaiable() {
        global $current_user;
        $now = time();
        $daysAgo = $now-3*24*60*60;

        $current_user   = wp_get_current_user();
        $userID         = $current_user->ID;
        $local          = homey_get_localization();

        $experience_id     = intval($_POST['experience_id']);
        $the_post       = get_post($experience_id);
        $post_owner     = $the_post->post_author;
        $selected_date = $_POST['selected_date'];

        if ( !is_user_logged_in() || $userID === 0 ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Login required', 'homey')
                )
            );
            wp_die();
        }

        if(!is_numeric($experience_id)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Something went wrong, please contact site administer', 'homey')
                )
            );
            wp_die();
        }

        if( ($userID != $post_owner) && !homey_is_admin())  {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__("You don't have rights to do this.", 'homey')
                )
            );
            wp_die();
        }

        $check_in_date  = $selected_date;

        $reservation_unavailable_array = get_post_meta($experience_id, 'reservation_unavailable', true );

        if( !is_array($reservation_unavailable_array) || empty($reservation_unavailable_array) ) {
            $reservation_unavailable_array  = array();
        }

        $unix_time_start = strtotime ($check_in_date);

        if ($unix_time_start > $daysAgo) {
            $check_in       =   new DateTime($check_in_date);
            $check_in_unix =   $check_in->getTimestamp();

            if( array_key_exists($check_in_unix, $reservation_unavailable_array)) {
                unset($reservation_unavailable_array[$check_in_unix]);
                echo json_encode(
                    array(
                        'success' => true,
                        'message' => 'made_available'
                    )
                );
            } else {
                $reservation_unavailable_array[$check_in_unix] = $experience_id;
                echo json_encode(
                    array(
                        'success' => true,
                        'message' => 'made_unavailable'
                    )
                );
            }
        }

        //return $reservation_unavailable_array;

        //$unavailable_days_array = homey_get_unavailable_dates($experience_id, $selected_date);
        update_post_meta($experience_id, 'reservation_unavailable', $reservation_unavailable_array);

        /*echo json_encode(
            array(
                'success' => true,
                'message' => ''
            )
         );*/
        wp_die();
    }
}

add_action( 'wp_ajax_nopriv_homey_reserve_exp_period_host', 'homey_reserve_exp_period_host' );
add_action( 'wp_ajax_homey_reserve_exp_period_host', 'homey_reserve_exp_period_host' );
if( !function_exists('homey_reserve_exp_period_host') ) {
    function homey_reserve_exp_period_host() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();
        $reservation_meta = array();

        $time = time();
        $date = date( 'Y-m-d H:i:s', $time );

        $experience_id = intval($_POST['experience_id']);
        $experience_owner_id  =  get_post_field( 'post_author', $experience_id );

        $check_in_date     =  date('d-m-Y', custom_strtotime(wp_kses ( $_POST['check_in_date'], $allowded_html )));
        $check_out_date    =  date('d-m-Y', custom_strtotime(wp_kses ( $_POST['check_out_date'], $allowded_html )));

        $period_note   =  wp_kses ( $_POST['period_note'], $allowded_html );
        $title = $local['reservation_text'];
        $guests = get_post_meta($experience_id, 'homey_total_guests_plus_additional_guests', true );
        $guests = $guests > 0 ? $guests : 1;

        $owner = homey_usermeta($experience_owner_id);
        $owner_email = $owner['email'];

        if ( !is_user_logged_in() || $userID === 0 ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['login_for_reservation']
                )
            );
            wp_die();
        }

        //check security
        $nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $nonce, 'period-security-nonce' ) ) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['security_check_text']
                )
            );
            wp_die();
        }

        if( $experience_owner_id != $userID ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['experience_owner_text']
                )
            );
            wp_die();
        }

        $check_in = new DateTime($check_in_date);
        $check_in_unix = $check_in->getTimestamp();

        $check_out = new DateTime($check_out_date);
        $check_out_unix = $check_out->getTimestamp();

        while($check_in_unix <= $check_out_unix){ // this is experiences to book one by one.
            $check_in_date = date('d-m-Y', $check_in_unix);

            $check_availability = check_exp_booking_availability($check_in_date, $experience_id, $guests);
            $is_available = $check_availability['success'];

            if($is_available) {
                $reservation_meta['renter_msg'] = $period_note;
                $prices_array = homey_get_exp_prices($check_in_date, $experience_id, 1);
                $price_per_person = $prices_array['price_per_person'];
                $persons_total_price = $prices_array['total_price'];
                $cleaning_fee = $prices_array['cleaning_fee'];
                $upfront_payment = $prices_array['upfront_payment'];
                $balance = $prices_array['balance'];
                $total_price = $prices_array['total_price'];
                $city_fee = $prices_array['city_fee'];
                $services_fee = $prices_array['services_fee'];
                $taxes = $prices_array['taxes'];
                $taxes_percent = $prices_array['taxes_percent'];
                $security_deposit = $prices_array['security_deposit'];
                $additional_guests_price = $prices_array['additional_guests_price'];
                $additional_guests_total_price = $prices_array['additional_guests_total_price'];
                $booking_has_weekend = $prices_array['booking_has_weekend'];

                $reservation_meta['check_in_date'] = $check_in_date;
                $reservation_meta['price_per_person'] = $price_per_person;
                $reservation_meta['guests_total_price'] = $persons_total_price;
                $reservation_meta['reservation_experience_type'] = 'per_person';
                $reservation_meta['guests'] = $guests;
                $reservation_meta['experience_id'] = $experience_id;
                $reservation_meta['upfront'] = $upfront_payment;
                $reservation_meta['balance'] = $balance;
                $reservation_meta['total'] = $total_price;
                $reservation_meta['no_of_persons'] = $prices_array['persons_count'];
                $reservation_meta['additional_guests'] = $prices_array['additional_guests'];
                $reservation_meta['cleaning_fee'] = $cleaning_fee;
                $reservation_meta['city_fee'] = $city_fee;
                $reservation_meta['services_fee'] = $services_fee;
                $reservation_meta['taxes'] = $taxes;
                $reservation_meta['taxes_percent'] = $taxes_percent;
                $reservation_meta['security_deposit'] = $security_deposit;
                $reservation_meta['additional_guests_price'] = $additional_guests_price;
                $reservation_meta['additional_guests_total_price'] = $additional_guests_total_price;
                $reservation_meta['booking_has_weekend'] = $booking_has_weekend;
                // reservation meta information

                $reservation = array(
                    'post_title'    => $title,
                    'post_status'   => 'publish',
                    'post_type'     => 'homey_e_reservation' ,
                    'post_author'   => $userID
                );

                $reservation_id =  wp_insert_post($reservation );
                $reservation_update = array(
                    'ID'         => $reservation_id,
                    'post_title' => $title.' '.$reservation_id
                );

                wp_update_post( $reservation_update );

                update_post_meta($reservation_id, 'reservation_experience_id', $experience_id);
                update_post_meta($reservation_id, 'experience_owner', $experience_owner_id);
                update_post_meta($reservation_id, 'experience_renter', $userID);
                update_post_meta($reservation_id, 'reservation_checkin_date', $check_in_date);
                update_post_meta($reservation_id, 'reservation_guests', $guests);
                update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
                update_post_meta($reservation_id, 'reservation_status', 'reserved');
                update_post_meta($reservation_id, 'extra_options', '');

                update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
                update_post_meta($reservation_id, 'reservation_balance', $balance);
                update_post_meta($reservation_id, 'reservation_total', $total_price);

                $booked_persons_array = homey_make_exp_guests_booked($experience_id, $reservation_id );
                update_post_meta($experience_id, 'reservation_dates', $booked_persons_array);

                $invoiceID = homey_exp_generate_invoice( 'reservation','one_time', $reservation_id, $date, $userID, 0, 0, '', 'Self' );
                update_post_meta( $invoiceID, 'invoice_payment_status', 1 );
            }

            $check_in = $check_in->modify('+1 day');;
            $check_in_unix = $check_in->getTimestamp();
        }

        echo json_encode(
            array(
                'success' => true,
                'message' => esc_html__('Reservation dates udpated, redirecting...', 'homey')
            )
        );
        wp_die();

    }
}


add_action( 'wp_ajax_nopriv_homey_exp_instance_step_1', 'homey_exp_instance_step_1' );
add_action( 'wp_ajax_homey_exp_instance_step_1', 'homey_exp_instance_step_1' );
if( !function_exists('homey_exp_instance_step_1') ) {
    function homey_exp_instance_step_1() {
        $local = homey_get_localization();
        $allowded_html = array();

        if(isset($_POST['reservation_no_userHash'])){
            if($_POST['reservation_no_userHash'] > 0){
                $userID = intval(deHashNoUserId($_POST['reservation_no_userHash']));
            }
        }else {
            global $current_user;
            $current_user = wp_get_current_user();
            $userID = $current_user->ID;
        }

        if ($userID < 1) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Something went wrong, please contact to the administrator.', 'homey')
                )
            );
            wp_die();
        }

        $first_name     =  wp_kses ( $_POST['first_name'], $allowded_html );
        $last_name    =  wp_kses ( $_POST['last_name'], $allowded_html );
        $phone    =  wp_kses ( $_POST['phone'], $allowded_html );

        if ($userID < 1) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Something went wrong, please contact to the administrator.', 'homey')
                )
            );
            wp_die();
        }

        if(empty($first_name)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['first_name_req']
                )
            );
            wp_die();
        }

        if(empty($last_name)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['last_name_req']
                )
            );
            wp_die();
        }

        if(empty($phone)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['phone_req']
                )
            );
            wp_die();
        }

        update_user_meta( $userID, 'first_name', $first_name);
        update_user_meta( $userID, 'last_name', $last_name);
        update_user_meta( $userID, 'phone', $phone);

        echo json_encode(
            array(
                'success' => true,
                'message' => ''
            )
        );
        wp_die();
    }
}

add_action( 'wp_ajax_homey_exp_reservation_mark_paid', 'homey_exp_reservation_mark_paid' );
if(!function_exists('homey_exp_reservation_mark_paid')) {
    function homey_exp_reservation_mark_paid() {
        if(homey_is_admin() || homey_is_host()){
            $reservation_id = intval($_POST['reservation_id']);

            // on mark paid generating invoice, if not in need you can delete or comment the code below
            $time = time();
            $date = date( 'Y-m-d G:i:s', $time );

            //homey_generate_invoice( 'reservation','one_time', $reservation_id, $date, $experience_renter, 0, 0, '', 'Self' );
            // on mark paid generating invoice, if not in need you can delete or comment the code above

            update_post_meta($reservation_id, 'reservation_status', 'booked');
            $admin_email = get_option( 'new_admin_email' );

            // Emails
            $experience_owner = get_post_meta($reservation_id, 'experience_owner', true);
            $experience_renter = get_post_meta($reservation_id, 'experience_renter', true);
            $experience_id = get_post_meta($reservation_id, 'reservation_experience_id', true);

            //Book dates
            $booked_days_array = homey_make_days_booked($experience_id, $reservation_id);
            update_post_meta($experience_id, 'reservation_dates', $booked_days_array);

            //Remove Pending Dates
            $pending_dates_array = homey_remove_booking_pending_days($experience_id, $reservation_id);
            update_post_meta($experience_id, 'reservation_pending_dates', $pending_dates_array);

            $renter = homey_usermeta($experience_renter);
            $renter_email = $renter['email'];

            $owner = homey_usermeta($experience_owner);
            $owner_email = $owner['email'];

            // Update status for paid in invoice
            $reservation_invoice_id = is_invoice_paid_for_reservation($reservation_id, 1);
            update_post_meta($reservation_invoice_id, 'invoice_payment_status', 1);
            update_post_meta($reservation_id, 'invoice_payment_status', 1);

            $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );
            homey_email_composer( $renter_email, 'booked_reservation', $email_args );
            homey_email_composer( $owner_email, 'booked_reservation', $email_args );
            homey_email_composer( $admin_email, 'admin_booked_reservation', $email_args );

            echo json_encode(
                array(
                    'success' => true,
                    'url' => homey_get_template_link('template/dashboard-reservations-experiences.php')
                )
            );
            wp_die();
        }else{
            echo json_encode(
                array(
                    'success' => false,
                    'msg' => homey_get_template_link('template/dashboard-reservations-experiences.php')
                )
            );
            wp_die();
        }

    }
}


add_action('wp_ajax_homey_exp_decline_reservation', 'homey_exp_decline_reservation');
if (!function_exists('homey_exp_decline_reservation')) {
    function homey_exp_decline_reservation()
    {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $local = homey_get_localization();

        $reservation_id = intval($_POST['reservation_id']);
        $experience_id = get_post_meta($reservation_id, 'reservation_experience_id', true);
        $reason = sanitize_text_field($_POST['reason']);

        $experience_owner = get_post_meta($reservation_id, 'experience_owner', true);
        $experience_renter = get_post_meta($reservation_id, 'experience_renter', true);

        $renter = homey_usermeta($experience_renter);
        $renter_email = $renter['email'];

        if ($experience_owner != $userID && !homey_is_admin()) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['experience_owner_text']
                )
            );
            wp_die();
        }

        // Set reservation status from under_review to available
        update_post_meta($reservation_id, 'reservation_status', 'declined');
        update_post_meta($reservation_id, 'res_decline_reason', $reason);

        //Remove Pending Dates
        $pending_dates_array = homey_remove_booking_pending_days($experience_id, $reservation_id, true);
        update_post_meta($experience_id, 'reservation_pending_dates', $pending_dates_array);

        echo json_encode(
            array(
                'success' => true,
                'message' => esc_html__('success', 'homey')
            )
        );

        $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id));
        homey_email_composer($renter_email, 'declined_reservation', $email_args);
//        $admin_email = get_option( 'admin_email' );
//        homey_email_composer( $admin_email, 'declined_reservation', $email_args );
        wp_die();
    }
}

add_action('wp_ajax_homey_exp_cancelled_reservation', 'homey_exp_cancelled_reservation');
if (!function_exists('homey_exp_cancelled_reservation')) {
    function homey_exp_cancelled_reservation()
    {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $local = homey_get_localization();

        $reservation_id = intval($_POST['reservation_id']);
        $experience_id = get_post_meta($reservation_id, 'reservation_experience_id', true);
        $reason = sanitize_text_field($_POST['reason']);
        $host_cancel = sanitize_text_field($_POST['host_cancel']);

        $experience_owner = get_post_meta($reservation_id, 'experience_owner', true);
        $experience_renter = get_post_meta($reservation_id, 'experience_renter', true);

        //cancellation date is expired check
        $num_hours_before_cancel = homey_option('num_0f_hours_before_checkin_remove_resrv');
        $cancel_before_date = strtotime(date('d-m-Y')) + $num_hours_before_cancel * 60 * 60;
        $check_in_date = strtotime(date('d-m-Y', custom_strtotime(get_post_meta($reservation_id, "reservation_checkin_date", true))));

        if ($cancel_before_date <= $check_in_date) {
            /* echo json_encode(
                array(
                    'success' => false,
                    'message' => isset($local['cancelation_date_expired']) ? $local['cancelation_date_expired'] : 'Cancellation date is expired.'
                )
            );
            wp_die();*/
        }
        //cancellation date is expired check


        if (($experience_renter != $userID) && ($experience_owner != $userID) && !homey_is_admin()) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['experience_renter_text']
                )
            );
            wp_die();
        }

        if (empty($reason)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['reason_text_req']
                )
            );
            wp_die();
        }

        // Set reservation status from under_review to available
        update_post_meta($reservation_id, 'reservation_status', 'cancelled');
        update_post_meta($reservation_id, 'res_cancel_reason', $reason);

        //Remove Pending Dates
        $pending_dates_array = homey_remove_booking_pending_days($experience_id, $reservation_id);
        update_post_meta($experience_id, 'reservation_pending_dates', $pending_dates_array);

        //Remove Booked Dates
        $booked_dates_array = homey_remove_booking_booked_days($experience_id, $reservation_id);
        update_post_meta($experience_id, 'reservation_dates', $booked_dates_array);

        if ($host_cancel == 'cancelled_by_host') {
            $renter = homey_usermeta($experience_renter);
            $to_email = $renter['email'];
        } else {
            $owner = homey_usermeta($experience_owner);
            $to_email = $owner['email'];
        }


        $host_earning = homey_get_earning_by_reservation_id($reservation_id);
        if (!empty($host_earning)) {
            $host_id = $host_earning->user_id;
            $deduct_amount = $host_earning->net_earnings;
            homey_adjust_host_available_balance_2($host_id, $deduct_amount);
        }


        echo json_encode(
            array(
                'success' => true,
                'message' => esc_html__('success', 'homey')
            )
        );

        $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id));

        homey_email_composer($to_email, 'cancelled_reservation', $email_args);
//        $admin_email = get_option( 'admin_email' );
//        homey_email_composer( $admin_email, 'cancelled_reservation', $email_args );
        wp_die();
    }
}

//hm-no_login
add_action( 'wp_ajax_nopriv_hm_no_login_exp_instance_step_1', 'hm_no_login_exp_instance_step_1' );
add_action( 'wp_ajax_hm_no_login_exp_instance_step_1', 'hm_no_login_exp_instance_step_1' );
if( !function_exists('hm_no_login_exp_instance_step_1') ) {
    function hm_no_login_exp_instance_step_1() {
        $local = homey_get_localization();
        $allowded_html = array();

        $first_name     =  wp_kses ( $_POST['first_name'], $allowded_html );
        $last_name    =  wp_kses ( $_POST['last_name'], $allowded_html );
        $email    =  wp_kses ( @$_POST['email'], $allowded_html );
        $phone    =  wp_kses ( $_POST['phone'], $allowded_html );

        $no_login_needed_for_exp_booking = homey_option('no_login_needed_for_exp_booking');

        if($no_login_needed_for_exp_booking == "yes" && isset($_REQUEST['email'])) {
            $user = get_user_by('email', $email);

            if(empty(trim($email))){
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => esc_html__('Enter email address', 'homey')
                    )
                );
                wp_die();
            }

            if (isset($user->ID)) {
                $hm_no_login_user_id = $user->ID;
            } else { //create user from email
                $user_login = $email;
                $user_email = $email;
                $user_pass = wp_generate_password(8, false);
                $userdata = compact('user_login', 'user_email', 'user_pass');
                $hm_no_login_user_id = wp_insert_user($userdata);

                if($hm_no_login_user_id > 0){
                    homey_wp_new_user_notification( $hm_no_login_user_id, $user_pass );
                }

                update_user_meta($hm_no_login_user_id, 'viaphp', 1);
            }
        }

        if(empty($first_name)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['first_name_req']
                )
            );
            wp_die();
        }

        if(empty($last_name)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['last_name_req']
                )
            );
            wp_die();
        }

        if(empty($phone)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['phone_req']
                )
            );
            wp_die();
        }


        update_user_meta( $hm_no_login_user_id, 'first_name', $first_name);
        update_user_meta( $hm_no_login_user_id, 'last_name', $last_name);
        update_user_meta( $hm_no_login_user_id, 'phone', $phone);

        echo json_encode(
            array(
                'success' => true,
                'message' => ''
            )
        );
        wp_die();
    }
}
