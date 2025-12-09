<?php
if (!function_exists('homey_generate_ical_export_link')) {
    function homey_generate_ical_export_link($listing_id)
    {
        $homey_ical_id = get_post_meta($listing_id, 'homey_ical_id', true);
        if ($homey_ical_id == '') {
            $homey_ical_id = md5(uniqid(mt_rand(), true));
            update_post_meta($listing_id, 'homey_ical_id', $homey_ical_id);
        }

        $ican_feeds_page_link = homey_get_template_link_2('template/template-ical.php');
        if (!empty($ican_feeds_page_link)) {
            $ican_feeds_page_link = esc_url_raw(add_query_arg('iCal', $homey_ical_id, $ican_feeds_page_link));
            return $ican_feeds_page_link;
        }
        return '';
    }
}

if (!function_exists('homey_get_listing_id_by_ical_id')) {
    function homey_get_listing_id_by_ical_id($ical_id)
    {
        $args = array(
            'post_type' => 'listing',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'homey_ical_id',
                    'value' => $ical_id,
                    'compare' => '=',
                )
            ),
        );

        $listing_id = '';

        $query = new WP_Query($args);
        if ($query->have_posts()):
            while ($query->have_posts()): $query->the_post();
                $listing_id = get_the_ID();
            endwhile;
        endif;
        wp_reset_postdata();

        return $listing_id;
    }
}

if (!function_exists('homey_get_booked_dates_for_icalendar')) {
    function homey_get_booked_dates_for_icalendar($listing_id) {

        $ical_timezone_string  = wp_timezone_string();
        $timezone = new DateTimeZone('UTC');

        $args = array(
            'post_type' => 'homey_reservation',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'reservation_listing_id',
                    'value' => $listing_id,
                    'type' => 'NUMERIC',
                    'compare' => '='
                ),
                array(
                    'key' => 'reservation_status',
                    'value' => 'booked',
                    'compare' => '='
                )
            )
        );

        $return_feeds = '';

        $wpQry = new WP_Query($args);

        if ($wpQry->have_posts()) {
            $return_feeds = '';

            while ($wpQry->have_posts()): $wpQry->the_post();

                $reservation_id = get_the_ID();

                $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);

                $check_in_date = get_post_meta($reservation_id, 'reservation_checkin_date', true);
                $check_out_date = get_post_meta($reservation_id, 'reservation_checkout_date', true);

                $check_in_date_time = date('d-m-Y H:i', strtotime($check_in_date));
                $check_out_date_time = date('d-m-Y H:i', strtotime('+23 hours', strtotime($check_out_date)));

                $check_in_datetime = new DateTime($check_in_date_time, new DateTimeZone($ical_timezone_string));
                $check_in_datetime->setTimezone($timezone);
                $check_in_start = $check_in_datetime->format('Ymd\THis\Z');

                $check_out_datetime = new DateTime($check_out_date_time, new DateTimeZone($ical_timezone_string));
                $check_out_datetime->setTimezone($timezone);
                $check_out_end = $check_out_datetime->format('Ymd\THis\Z');

                $note = esc_html__('Booked dates for reservation id:', 'homey').' '.$reservation_id;
                $return_feeds .= homey_generate_ical_event($check_in_start, $check_out_end, $listing_id, $reservation_id, $note);

            endwhile;
            wp_reset_postdata();
        }

        return $return_feeds;
    }
}

//zahid .k
if (!function_exists('homey_get_unavailable_dates_for_icalendar')) {
    function homey_get_unavailable_dates_for_icalendar($listing_id)
    {
        $unavailable_dates = get_post_meta($listing_id, 'reservation_unavailable', true);
        $check_in_date = $check_out_date = 0;
        $return_feeds = '';
        $reservation_id = '';

        $ical_timezone_string  = wp_timezone_string();
        $timezone = new DateTimeZone('UTC');

        if ($unavailable_dates) {
            if (is_array($unavailable_dates) || is_object($unavailable_dates)) {
                foreach ($unavailable_dates as $datetime_string => $listingID) {
                    $check_in_date = $check_out_date = 0;

                    if ($check_in_date == 0) {
                        $check_in_date = $datetime_string;
                    }
                    if ($check_out_date < $datetime_string) {
                        $check_out_date = $datetime_string;
                    }

                    if ($check_in_date != 0 && $check_out_date != 0) {
                        $check_in_date_time = date('d-m-Y H:i', $check_in_date);
                        $check_out_date_time = date('d-m-Y H:i', strtotime('+23 hours', $check_out_date));

                        $check_in_datetime = new DateTime($check_in_date_time, new DateTimeZone($ical_timezone_string));
                        $check_in_datetime->setTimezone($timezone);
                        $check_in_start = $check_in_datetime->format('Ymd\THis\Z');

                        $check_out_datetime = new DateTime($check_out_date_time, new DateTimeZone($ical_timezone_string));
                        $check_out_datetime->setTimezone($timezone);
                        $check_out_end = $check_out_datetime->format('Ymd\THis\Z');


                        $note = esc_html__('Manually booked for listing id:', 'homey').' '.$listing_id;

                        $return_feeds .= homey_generate_ical_event($check_in_start, $check_out_end, $listing_id, $reservation_id, $note);
                    }
                }
            }        }

        wp_reset_postdata();

        return $return_feeds;
    }
}

if (!function_exists('homey_get_reserved_dates_for_icalendar')) {
    function homey_get_reserved_dates_for_icalendar($listing_id)
    {
        $reservation_dates = get_post_meta($listing_id, 'reservation_dates', true);
        $check_in_date = $check_out_date = 0;
        $return_feeds = '';

        $reservation_id = '';

        $ical_timezone_string  = wp_timezone_string();
        $timezone = new DateTimeZone('UTC');

        if ($reservation_dates) {
            if (is_array($reservation_dates) || is_object($reservation_dates)) {
                foreach ($reservation_dates as $datetime_string => $listingID) {
                    $check_in_date = $check_out_date = 0;

                    if ($check_in_date == 0) {
                        $check_in_date = $datetime_string;
                    }
                    if ($check_out_date < $datetime_string) {
                        $check_out_date = $datetime_string;
                    }

                    if ($check_in_date != 0 && $check_out_date != 0) {
                        $check_in_date_time = date('d-m-Y H:i', $check_in_date);
                        $check_out_date_time = date('d-m-Y H:i', strtotime('+23 hours', $check_out_date));

                        $check_in_datetime = new DateTime($check_in_date_time, new DateTimeZone($ical_timezone_string));
                        $check_in_datetime->setTimezone($timezone);
                        $check_in_start = $check_in_datetime->format('Ymd\THis\Z');

                        $check_out_datetime = new DateTime($check_out_date_time, new DateTimeZone($ical_timezone_string));
                        $check_out_datetime->setTimezone($timezone);
                        $check_out_end = $check_out_datetime->format('Ymd\THis\Z');


                        $note = esc_html__('Reserved booked for listing id:', 'homey').' '.$listing_id;

                        $return_feeds .= homey_generate_ical_event($check_in_start, $check_out_end, $listing_id, $reservation_id, $note);
                    }
                }
            }
        }

        wp_reset_postdata();

        return $return_feeds;
    }
}


if (!function_exists('homey_generate_ical_event')) {
    function homey_generate_ical_event($check_in_start, $check_out_end, $listing_id, $reservation_id = null, $note = '' ) {
        $eol = PHP_EOL;
        $host = $_SERVER['HTTP_HOST'];
        $UID  = md5(uniqid(mt_rand(), true)) . "@" . $host;

        $timezone = new DateTimeZone('UTC');
        $timezone = isset($timezone->timezone) ? $timezone->timezone : 'UTC';

        if( $note == '' && is_int($check_in_start)) {
            $check_in_start = date("Ymd\THis\Z", $check_in_start);
            $check_out_end = date("Ymd\THis\Z", $check_out_end);

            $summary = "BOOKED - ".$host." booking id ".$reservation_id." and Listing ID " . $listing_id;
        } else {
            $summary = $host.' '.$note;
        }

        $ical_event = '';
        $ical_event .= "BEGIN:VEVENT".$eol;

        $ical_event .= "DTSTART;TZID=$timezone:" . $check_in_start . $eol;
        $ical_event .= "DTEND;TZID=$timezone:" . $check_out_end . $eol;

        //This is not standard way
        //$ical_event .= "DTSTART;VALUE=DATE:" . $check_in_start . $eol;
        //$ical_event .= "DTEND;VALUE=DATE:" . $check_out_end . $eol;

        $ical_event .= "DTSTAMP:" . date('Ymd\THis') . $eol;
        $ical_event .= "UID:" . $UID . "\n";
        $ical_event .= "SUMMARY:". $summary . $eol;
        $ical_event .= "STATUS:CONFIRMED".$eol;
        $ical_event .= "END:VEVENT".$eol;

        return $ical_event;

    }
}


add_action('wp_ajax_homey_add_ical_feeds', 'homey_add_ical_feeds');
if (!function_exists('homey_add_ical_feeds')) {
    function homey_add_ical_feeds()
    {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $local = homey_get_localization();
        $store_feeds_array = array();
        $temp_array = array();
        $allowded_html = array();

        $listing_id = intval($_POST['listing_id']);
        $the_post = get_post($listing_id);
        $post_owner = $the_post->post_author;
        $ical_feed_name = $_POST['ical_feed_name'];
        $ical_feed_url = $_POST['ical_feed_url'];

        if (!is_user_logged_in() || $userID === 0) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Login required', 'homey')
                )
            );
            wp_die();
        }

        if (!is_numeric($listing_id)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Something went wrong, please contact site administer', 'homey')
                )
            );
            wp_die();
        }

        if ($userID != $post_owner && !homey_is_admin()) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__("You don't have rights to do this.", 'homey')
                )
            );
            wp_die();
        }

        if (is_array($ical_feed_url) || is_object($ical_feed_url)) {
            foreach ($ical_feed_url as $key => $value) {
                if (!empty($value)) {
                    $temp_array['feed_url'] = esc_url_raw($value);
                    $temp_array['feed_name'] = esc_html($ical_feed_name[$key]);
                    $store_feeds_array[] = $temp_array;
                }
            }
        }

        if (!empty($store_feeds_array)) {
            update_post_meta($listing_id, 'homey_ical_feeds_meta', $store_feeds_array);
            homey_import_icalendar_feeds($listing_id);
        }

        $dashboard_submission = homey_get_template_link('template/dashboard-submission.php');
        $return_url = add_query_arg(
            array(
                'edit_listing' => $listing_id,
                'tab' => 'calendar'
            ),
            $dashboard_submission
        );

        echo json_encode(
            array(
                'success' => true,
                'message' => $local['feeds_imported'],
                'url' => $return_url,
            )
        );
        wp_die();

    }
}

if (!function_exists('homey_import_icalendar_feeds')) {
    function homey_import_icalendar_feeds($listing_id)
    {
        $ical_feeds_meta = get_post_meta($listing_id, 'homey_ical_feeds_meta', true);

        update_post_meta( $listing_id, 'last_ical_update_time', date('Y-m-d H:i:s') );
        if (is_array($ical_feeds_meta) || is_object($ical_feeds_meta)) {
            foreach ($ical_feeds_meta as $key => $value) {
                $feed_name = $value['feed_name'];
                $feed_url = $value['feed_url'];
//                echo $feed_name.' = '.$feed_url.'<br/>';
                homey_insert_icalendar_feeds($listing_id, $feed_name, $feed_url);
            }
        }
        /*echo '<pre>';
        print_r($ical_feeds_meta);*/
    }
}
//homey_import_icalendar_feeds(11263);

if (!function_exists('homey_insert_icalendar_feeds')) {
    function homey_insert_icalendar_feeds($listing_id, $feed_name, $feed_url)
    {

        if (empty($feed_url) || !intval($listing_id) || filter_var($feed_url, FILTER_VALIDATE_URL) === FALSE) {
            return;
        }

        $temp_array = array();
        $events_data_array = array();

        $ical = new ICal($feed_url);

        $events = $ical->events();

        $log_msg = ' here it was for feed URL '.$feed_url.' date => '. date("d-m-Y H:i:s");

        //$ical_timezone = $ical->cal['VCALENDAR']['X-WR-TIMEZONE'];
        if (is_array($events) || is_object($events)) {
            if ($events) {
                foreach ($events as $event) {

                    $start_time_unix = $end_time_unix = '';

                    if (isset($event['DTSTART'])) {
                        $start_time_unix = $ical->iCalDateToUnixTimestamp($event['DTSTART']);
                        $log_msg .= ', DTSTART => '.$event['DTSTART'];
                    }

                    if (isset($event['DTEND'])) {
                        $end_time_unix = $ical->iCalDateToUnixTimestamp($event['DTEND']);
                        $log_msg .= ', DTEND => '.$event['DTEND'];

                    }

                    $feed_name = empty($feed_name) ? 'feed-name-was-null_'. $start_time_unix.'_'.$end_time_unix : $feed_name;
                    if (!empty($start_time_unix) && !empty($end_time_unix) && !empty($feed_name)) {

                        $temp_array['start_time_unix'] = $start_time_unix;
                        $temp_array['end_time_unix'] = $end_time_unix;
                        $temp_array['feed_name'] = $feed_name;

                        $log_msg .= ', feed_name => '.$feed_name;

                        $events_data_array[] = $temp_array;
                    }
                }
            }
        }

        $booked_dates_array = get_post_meta($listing_id, 'reservation_dates', true);

        if (is_array($booked_dates_array) || is_object($booked_dates_array)) {
            $ical_feed_name_txt = isset($events_data_array[0]['feed_name']) ? $events_data_array[0]['feed_name'] : 'No Name Available';
            $events_data_to_unset = array_keys($booked_dates_array, $ical_feed_name_txt);
            if (is_array($events_data_to_unset) || is_object($events_data_to_unset)) {
                foreach ($events_data_to_unset as $key => $timestamp) {
                    unset($booked_dates_array[$timestamp]);
                }
            }
            update_post_meta($listing_id, 'reservation_dates', $booked_dates_array);
        }

        if (is_array($events_data_array) || is_object($events_data_array)) {
            foreach ($events_data_array as $data) {
                $start_time_unix = $data['start_time_unix'];
                $end_time_unix = $data['end_time_unix'];
                $feed_name = $data['feed_name'];
                homey_add_listing_booking_dates($listing_id, $start_time_unix, $end_time_unix, $feed_name);
            }
        }

        /*echo '<pre>';
        print_r($events_data_array);*/
        $log_msg .= ' new feed *** <br>';
//        file_put_contents('log_calender_events_' . date("j.n.Y") . '.log', $log_msg, FILE_APPEND);

    }
}

if (!function_exists('homey_add_listing_booking_dates')) {
    function homey_add_listing_booking_dates($listing_id, $start_time_unix, $end_time_unix, $feed_name)
    {
        $now = time();
        $daysAgo = $now - 3 * 24 * 60 * 60;

        //change date format and remove hours, mins
        $start_date = gmdate("Y-m-d 0:0:0", $start_time_unix);
        $start_date_unix = strtotime($start_date);
        $end_date = gmdate("Y-m-d 0:0:0", $end_time_unix);
        $end_date_unix = strtotime($end_date);

        if ($end_date_unix < $daysAgo) {
            return;
        }

        $booked_dates_array = get_post_meta($listing_id, 'reservation_dates', true);

        if (!is_array($booked_dates_array) || empty($booked_dates_array)) {
            $booked_dates_array = array();
        }


        $start_date_unix = gmdate("Y-m-d\TH:i:s\Z", $start_date_unix);
        $end_date_unix = gmdate("Y-m-d\TH:i:s\Z", $end_date_unix);

        $check_in = new DateTime($start_date_unix);
        $check_in_unix = $check_in->getTimestamp();
        $check_out = new DateTime($end_date_unix);
        $check_out_unix = $check_out->getTimestamp();

        $booked_dates_array[$check_in_unix] = $feed_name;
        $check_in_unix = $check_in->getTimestamp();

        while ($check_in_unix < $check_out_unix) {

            $booked_dates_array[$check_in_unix] = $feed_name;

            $check_in->modify('tomorrow');
            $check_in_unix = $check_in->getTimestamp();
        }
        //Update booked dates meta
        update_post_meta($listing_id, 'reservation_dates', $booked_dates_array);

    }
}


add_action('wp_ajax_homey_remove_ical_feeds', 'homey_remove_ical_feeds');
if (!function_exists('homey_remove_ical_feeds')) {
    function homey_remove_ical_feeds()
    {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();

        $listing_id = intval($_POST['listing_id']);
        $the_post = get_post($listing_id);
        $post_owner = $the_post->post_author;
        $remove_index = $_POST['remove_index'];

        if (!is_user_logged_in() || $userID === 0) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Login required', 'homey')
                )
            );
            wp_die();
        }

        if (!is_numeric($listing_id) || !intval($listing_id)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Something went wrong, please contact site administer', 'homey')
                )
            );
            wp_die();
        }

        if ($userID != $post_owner && !homey_is_admin()) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__("You don't have rights to do this.", 'homey')
                )
            );
            wp_die();
        }

        // Remove feed link
        $homey_ical_feeds_meta = get_post_meta($listing_id, 'homey_ical_feeds_meta', true);
        $feed_for_delete = $homey_ical_feeds_meta[$remove_index]['feed_name'];
        unset($homey_ical_feeds_meta[$remove_index]);
        update_post_meta($listing_id, 'homey_ical_feeds_meta', $homey_ical_feeds_meta);

        //Remove reserved dates
        $reservation_dates = get_post_meta($listing_id, 'reservation_dates', true);
        $array = array();
        if (is_array($reservation_dates) || is_object($reservation_dates)) {
            foreach ($reservation_dates as $key => $value) {
                if ($feed_for_delete == $value) {
                    unset($reservation_dates[$key]);
                }
            }
        }
        update_post_meta($listing_id, 'reservation_dates', $reservation_dates);

        homey_import_icalendar_feeds($listing_id);

        echo json_encode(
            array(
                'success' => true,
                'message' => esc_html__("Removed Successfully.", 'homey')
            )
        );
        wp_die();

    }
}

if(isset($_GET['test_ical_pre'])){
    $homey_ical_feeds_meta = get_post_meta(2753, 'homey_ical_feeds_meta', true);
    echo '<pre>'; print_r($homey_ical_feeds_meta);
    $feed_for_delete = $homey_ical_feeds_meta[$remove_index]['feed_name'];
}

if (!function_exists('homey_generate_ical_dot_ics_url')) {
    function homey_generate_ical_dot_ics_url($listing_id) {

        $allowed_html = array();
        $eol = PHP_EOL;
        $ical_prod_id          = sprintf('-//%s//iCal Event Maker', get_bloginfo('name'));
        $ical_refresh_interval = 'P30M';
        $ical_timezone_string  = wp_timezone_string();

        $slug      = get_post_field('post_name', $listing_id);
        $title     = get_post_field('post_title', $listing_id);
        $title     = sprintf('%s - %s', $title, __('Bookings', 'homey_core', 'homey'));
        $permalink = get_permalink($listing_id);

        $ics_data = "BEGIN:VCALENDAR".$eol;
        $ics_data .= "VERSION:2.0".$eol;
        $ics_data .= "PRODID:".$ical_prod_id . $eol;
        $ics_data .= "X-WR-CALNAME:".$title . $eol;
        $ics_data .= "X-HOMEY-LISTING-URL:".$permalink . $eol;
        $ics_data .= "NAME:iCal:".$title . $eol;
        $ics_data .= "REFRESH-INTERVAL;VALUE=DURATION:".$ical_refresh_interval . $eol;
        $ics_data .= "CALSCALE:GREGORIAN".$eol;
        $ics_data .= "X-WR-TIMEZONE:". $ical_timezone_string . $eol;
        $ics_data .= "METHOD:PUBLISH".$eol;
        $ics_data .= homey_get_booked_dates_for_icalendar($listing_id);
        $ics_data .= homey_get_unavailable_dates_for_icalendar($listing_id);
        $ics_data .= "END:VCALENDAR";
        $base_folder_path = WP_CONTENT_DIR . "/uploads/listings-calendars/";
        $upload_url = $base_folder_path."listing-{$listing_id}.ics";
        $content_upload_url = content_url()."/uploads/listings-calendars/listing-{$listing_id}.ics";

        if (!file_exists($base_folder_path)) {
            mkdir($base_folder_path, 0777, true);
        }

        update_post_meta($listing_id, "icalendar_file_url_with_ics", $content_upload_url);

        file_put_contents($upload_url, $ics_data);

        return $content_upload_url;
    }
}

add_action('wp_ajax_nopriv_homey_run_ical_ajaxly_when_visit_listing', 'homey_run_ical_ajaxly_when_visit_listing');
add_action('wp_ajax_homey_run_ical_ajaxly_when_visit_listing', 'homey_run_ical_ajaxly_when_visit_listing');
if (!function_exists('homey_run_ical_ajaxly_when_visit_listing')) {
    function homey_run_ical_ajaxly_when_visit_listing() {
        $listing_id = isset($_POST['listing_id']) ? $_POST['listing_id'] : 0;
        if($listing_id < 1) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('No listing found.', 'homey')
                )
            );
            wp_die();
        };

        $last_ical_update_time = get_post_meta( $listing_id, 'last_ical_update_time', true );

        if ( $last_ical_update_time ) {
            $last_ical_update_time = strtotime( $last_ical_update_time ); // Convert to timestamp
            $current_time = strtotime(date('Y-m-d H:i:s'));
            $time_difference = $current_time - $last_ical_update_time;
            if( $time_difference > 600){
                homey_import_icalendar_feeds($listing_id);
                echo json_encode(
                    array(
                        'success' => true,
                        'message' => esc_html__('Ical updated on time: .', 'homey')
                    )
                );
                wp_die();
            }
        } else {
            homey_import_icalendar_feeds($listing_id);
            echo json_encode(
                array(
                    'success' => true,
                    'message' => esc_html__('Ical updated on time: .', 'homey')
                )
            );
            wp_die();
        }

        echo json_encode(
            array(
                'success' => true,
                'message' => esc_html__('Ical okay.', 'homey')
            )
        );
        wp_die();
    }
}// function tries to ical when that is on details page.

function ical_test()
{
    $listing_id = 3416;

    $feed_url = "https://outlook.live.com/owa/calendar/00000000-0000-0000-0000-000000000000/4c2ce906-b114-44eb-8751-10c14966d54f/cid-B326C0B75C5C3CD4/calendar.ics";
    $feed_url = "https://lodgify.com/77ebfdc4-5338-4e3d-8dfd-11494926257e.ics";

    $ical = new ICal($feed_url);

    $events = $ical->events();

    $log_msg = ' here it was for feed URL '.$feed_url.' date => '. date("d-m-Y H:i:s");

    //$ical_timezone = $ical->cal['VCALENDAR']['X-WR-TIMEZONE'];
    if (is_array($events) || is_object($events)) {
        if ($events) {
            foreach ($events as $event) {
                $start_time_unix = $end_time_unix = '';

                if (isset($event['DTSTART'])) {
                    $start_time_unix = $ical->iCalDateToUnixTimestamp($event['DTSTART']);
                    $log_msg .= ', DTSTART => '.$event['DTSTART'];
                }

                if (isset($event['DTEND'])) {
                    $end_time_unix = $ical->iCalDateToUnixTimestamp($event['DTEND']);
                    $log_msg .= ', DTEND => '.$event['DTEND'];

                }

                $feed_name = empty($feed_name) ? 'feed-name-was-null' : $feed_name;
                if (!empty($start_time_unix) && !empty($end_time_unix) && !empty($feed_name)) {

                    $temp_array['start_time_unix'] = $start_time_unix;
                    $temp_array['end_time_unix'] = $end_time_unix;
                    $temp_array['feed_name'] = $feed_name;

                    $log_msg .= ', feed_name => '.$feed_name;

                    $events_data_array[] = $temp_array;
                }

                $log_msg .= '<br>';
            }
        }
    }

    $booked_dates_array = get_post_meta($listing_id, 'reservation_dates', true);

    if (is_array($booked_dates_array) || is_object($booked_dates_array)) {
        $ical_feed_name_txt = isset($events_data_array[0]['feed_name']) ? $events_data_array[0]['feed_name'] : 'No Name Available';
        $log_msg .= ' => booked date -> ';
        $events_data_to_unset = array_keys($booked_dates_array, $ical_feed_name_txt);

        if (is_array($events_data_to_unset) || is_object($events_data_to_unset)) {
            foreach ($events_data_to_unset as $key => $timestamp) {
                unset($booked_dates_array[$timestamp]);
            }
        }
    }


    if (is_array($events_data_array) || is_object($events_data_array)) {
        foreach ($events_data_array as $data) {
            $start_time_unix = $data['start_time_unix'];
            $end_time_unix = $data['end_time_unix'];
            $feed_name = $data['feed_name'];
            homey_add_listing_booking_dates($listing_id, $start_time_unix, $end_time_unix, $feed_name);
        }
    }

    /*echo '<pre>';
    print_r($events_data_array);*/
    $log_msg .= ' new feed *** <br>';
//        file_put_contents('log_calender_events_' . date("j.n.Y") . '.log', $log_msg, FILE_APPEND);
}