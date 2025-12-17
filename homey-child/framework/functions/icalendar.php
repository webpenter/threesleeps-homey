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

if (!function_exists('homey_generate_ical_multi_export_link')) {
    function homey_generate_ical_multi_export_link($listing_id, $room_id = '') {
        $homey_ical_id = get_post_meta($listing_id, 'homey_ical_id', true);

        if (!empty($room_id)) {
            // Enlace único por habitación
            $homey_ical_id = $homey_ical_id . '_room_' . $room_id;
        }

        if ($homey_ical_id == '') {
            $homey_ical_id = md5(uniqid(mt_rand(), true));
            update_post_meta($listing_id, 'homey_ical_id', $homey_ical_id);
        }

        $ican_feeds_page_link = homey_get_template_link_2('template/template-ical-multi.php');
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
            'post_type'   => 'listing',
            'post_status' => 'publish',
            'meta_query'  => array(
                array(
                    'key'     => 'homey_ical_id',
                    'value'   => $ical_id,
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

if (!function_exists('homey_get_booked_dates_for_icalendar_multi')) {
    function homey_get_booked_dates_for_icalendar_multi($listing_id, $target_room_id = null) {

        $ical_timezone_string = wp_timezone_string();
        $utc_timezone = new DateTimeZone('UTC');

        $args = array(
            'post_type'      => 'homey_reservation',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => 'reservation_listing_id',
                    'value'   => $listing_id,
                    'type'    => 'NUMERIC',
                    'compare' => '='
                ),
                array(
                    'key'     => 'reservation_status',
                    'value'   => 'booked',
                    'compare' => '='
                ),
            ),
        );

        $ical_output = '';

        $reservations = new WP_Query($args);

        if ($reservations->have_posts()) {
            while ($reservations->have_posts()) : $reservations->the_post();

                $reservation_id = get_the_ID();
                $room_ids = get_post_meta($reservation_id, 'reservation_selected_rooms_id', true);

                // Filtra por habitación si se pide explícitamente
                if (!empty($target_room_id) && (!is_array($room_ids) || !in_array($target_room_id, $room_ids))) {
                    continue;
                }

                $check_in_date  = get_post_meta($reservation_id, 'reservation_checkin_date', true);
                $check_out_date = get_post_meta($reservation_id, 'reservation_checkout_date', true);

                if (empty($check_in_date) || empty($check_out_date)) {
                    continue;
                }

                // A UTC con Z
                $check_in_datetime = new DateTime($check_in_date, new DateTimeZone($ical_timezone_string));
                $check_in_datetime->setTimezone($utc_timezone);
                $check_in_start = $check_in_datetime->format('Ymd\THis\Z');

                // Fin de día local 23:59:59 para checkout
                $check_out_datetime = new DateTime($check_out_date . ' 23:59:59', new DateTimeZone($ical_timezone_string));
                $check_out_datetime->setTimezone($utc_timezone);
                $check_out_end = $check_out_datetime->format('Ymd\THis\Z');

                $note = sprintf(
                    esc_html__('Booked room #%s in listing #%s (Reservation ID: %s)', 'homey'),
                    $target_room_id ?? implode(',', (array) $room_ids),
                    $listing_id,
                    $reservation_id
                );

                $ical_output .= homey_generate_ical_event($check_in_start, $check_out_end, $listing_id, $reservation_id, $note);

            endwhile;

            wp_reset_postdata();
        }

        return $ical_output;
    }
}

if (!function_exists('homey_get_booked_dates_for_icalendar')) {
    function homey_get_booked_dates_for_icalendar($listing_id) {

        $ical_timezone_string  = wp_timezone_string();
        $utc = new DateTimeZone('UTC');

        $args = array(
            'post_type'      => 'homey_reservation',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => 'reservation_listing_id',
                    'value'   => $listing_id,
                    'type'    => 'NUMERIC',
                    'compare' => '='
                ),
                array(
                    'key'     => 'reservation_status',
                    'value'   => 'booked',
                    'compare' => '='
                )
            )
        );

        $return_feeds = '';

        $wpQry = new WP_Query($args);

        if ($wpQry->have_posts()) {
            while ($wpQry->have_posts()): $wpQry->the_post();

                $reservation_id = get_the_ID();

                $check_in_date  = get_post_meta($reservation_id, 'reservation_checkin_date',  true);
                $check_out_date = get_post_meta($reservation_id, 'reservation_checkout_date', true);

                if (!$check_in_date || !$check_out_date) { continue; }

                // A UTC con Z
                $check_in_datetime = new DateTime($check_in_date, new DateTimeZone($ical_timezone_string));
                $check_in_datetime->setTimezone($utc);
                $check_in_start = $check_in_datetime->format('Ymd\THis\Z');

                // checkout fin de día local
                $check_out_datetime = new DateTime($check_out_date.' 23:59:59', new DateTimeZone($ical_timezone_string));
                $check_out_datetime->setTimezone($utc);
                $check_out_end = $check_out_datetime->format('Ymd\THis\Z');

                $note = esc_html__('Booked dates for reservation id:', 'homey').' '.$reservation_id;
                $return_feeds .= homey_generate_ical_event($check_in_start, $check_out_end, $listing_id, $reservation_id, $note);

            endwhile;
            wp_reset_postdata();
        }

        return $return_feeds;
    }
}

if (!function_exists('homey_get_unavailable_dates_for_icalendar')) {
    function homey_get_unavailable_dates_for_icalendar($listing_id)
    {
        $unavailable_dates = get_post_meta($listing_id, 'reservation_unavailable', true);
        $return_feeds = '';
        $reservation_id = '';

        $ical_timezone_string  = wp_timezone_string();
        $utc = new DateTimeZone('UTC');

        if ($unavailable_dates && (is_array($unavailable_dates) || is_object($unavailable_dates))) {
            foreach ($unavailable_dates as $datetime_string => $listingID) {

                $check_in_date_time  = date('d-m-Y H:i', $datetime_string);
                $check_out_date_time = date('d-m-Y H:i', strtotime('+23 hours', $datetime_string));

                $check_in_datetime = new DateTime($check_in_date_time, new DateTimeZone($ical_timezone_string));
                $check_in_datetime->setTimezone($utc);
                $check_in_start = $check_in_datetime->format('Ymd\THis\Z');

                $check_out_datetime = new DateTime($check_out_date_time, new DateTimeZone($ical_timezone_string));
                $check_out_datetime->setTimezone($utc);
                $check_out_end = $check_out_datetime->format('Ymd\THis\Z');

                $note = esc_html__('Manually booked for listing id:', 'homey').' '.$listing_id;
                $return_feeds .= homey_generate_ical_event($check_in_start, $check_out_end, $listing_id, $reservation_id, $note);
            }
        }

        wp_reset_postdata();

        return $return_feeds;
    }
}

if (!function_exists('homey_get_reserved_dates_for_icalendar')) {
    function homey_get_reserved_dates_for_icalendar($listing_id)
    {
        $reservation_dates = get_post_meta($listing_id, 'reservation_dates', true);
        $return_feeds = '';

        $reservation_id = '';
        $utc = new DateTimeZone('UTC');

        if ($reservation_dates && (is_array($reservation_dates) || is_object($reservation_dates))) {
            foreach ($reservation_dates as $datetime_string => $listingID) {

                $check_in_date_time  = gmdate('Y-m-d H:i:s', $datetime_string);
                $check_out_date_time = gmdate('Y-m-d H:i:s', $datetime_string + 23*3600);

                $check_in_datetime = new DateTime($check_in_date_time, new DateTimeZone('UTC'));
                $check_out_datetime = new DateTime($check_out_date_time, new DateTimeZone('UTC'));

                $check_in_start = $check_in_datetime->format('Ymd\THis\Z');
                $check_out_end  = $check_out_datetime->format('Ymd\THis\Z');

                $note = esc_html__('Reserved booked for listing id:', 'homey').' '.$listing_id;

                $return_feeds .= homey_generate_ical_event($check_in_start, $check_out_end, $listing_id, $reservation_id, $note);
            }
        }

        wp_reset_postdata();

        return $return_feeds;
    }
}

if (!function_exists('homey_generate_ical_event')) {
    function homey_generate_ical_event($check_in_start, $check_out_end, $listing_id, $reservation_id = null, $note = '' ) {

        // Forzar CRLF
        $eol  = "\r\n";
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $UID  = md5(uniqid(mt_rand(), true)) . "@" . $host;

        // Acepta timestamps o strings ya formateados
        if (is_int($check_in_start)) {
            $check_in_start = gmdate("Ymd\THis\Z", $check_in_start);
        }
        if (is_int($check_out_end)) {
            $check_out_end  = gmdate("Ymd\THis\Z", $check_out_end);
        }

        if ($note == '') {
            $summary = "BOOKED - ".$host." booking id ".$reservation_id." and Listing ID " . $listing_id;
        } else {
            $summary = $host.' '.$note;
        }

        $ical_event  = '';
        $ical_event .= "BEGIN:VEVENT".$eol;

        // UTC con Z, sin TZID ni VALUE=DATE
        $ical_event .= "DTSTART:" . $check_in_start . $eol;
        $ical_event .= "DTEND:"   . $check_out_end  . $eol;

        $ical_event .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . $eol;
        $ical_event .= "UID:" . $UID . $eol;
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

        $listing_id = intval($_POST['listing_id']);
        $the_post   = get_post($listing_id);
        $post_owner = $the_post->post_author;
        $ical_feed_name = $_POST['ical_feed_name'];
        $ical_feed_url  = $_POST['ical_feed_url'];

        if (!is_user_logged_in() || $userID === 0) {
            echo json_encode(array('success' => false, 'message' => esc_html__('Login required', 'homey')));
            wp_die();
        }

        if (!is_numeric($listing_id)) {
            echo json_encode(array('success' => false, 'message' => esc_html__('Something went wrong, please contact site administer', 'homey')));
            wp_die();
        }

        if ($userID != $post_owner && !homey_is_admin()) {
            echo json_encode(array('success' => false, 'message' => esc_html__("You don't have rights to do this.", 'homey')));
            wp_die();
        }

        if (is_array($ical_feed_url) || is_object($ical_feed_url)) {
            foreach ($ical_feed_url as $key => $value) {
                if (!empty($value)) {
                    $temp_array['feed_url']  = esc_url_raw($value);
                    $temp_array['feed_name'] = isset($ical_feed_name[$key]) ? esc_html($ical_feed_name[$key]) : '';
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
                'tab'          => 'calendar'
            ),
            $dashboard_submission
        );

        echo json_encode(array(
            'success' => true,
            'message' => $local['feeds_imported'],
            'url'     => $return_url,
        ));
        wp_die();
    }
}

// --------- MULTIROOM: importar feeds por habitación ---------

if (!function_exists('homey_import_icalendar_multi_feeds')) {
    function homey_import_icalendar_multi_feeds($listing_id, $room_id = null, $feed_url = '', $feed_name = '') {

        update_post_meta($listing_id, 'last_ical_update_time', date('Y-m-d H:i:s'));

        if (!empty($feed_url) && !empty($feed_name) && !empty($room_id)) {
            homey_insert_icalendar_multi_feeds($listing_id, $feed_name, $feed_url, $room_id);
            return;
        }

        $all_feeds = get_post_meta($listing_id, 'homey_ical_feeds_meta', true);
        if (!is_array($all_feeds)) {
            return;
        }

        if ($room_id !== null && isset($all_feeds[$room_id])) {
            foreach ($all_feeds[$room_id] as $f) {
                if (!empty($f['feed_url'])) {
                    homey_insert_icalendar_multi_feeds($listing_id, $f['feed_name'] ?? '', $f['feed_url'], $room_id);
                }
            }
        } else {
            foreach ($all_feeds as $r_id => $feeds) {
                if (!is_array($feeds)) continue;
                foreach ($feeds as $f) {
                    if (!empty($f['feed_url'])) {
                        homey_insert_icalendar_multi_feeds($listing_id, $f['feed_name'] ?? '', $f['feed_url'], $r_id);
                    }
                }
            }
        }
    }
}

if (!function_exists('homey_insert_icalendar_multi_feeds')) {
    function homey_insert_icalendar_multi_feeds($listing_id, $feed_name, $feed_url, $room_id = null) {

        if (empty($feed_url) || empty($listing_id) || empty($room_id) || filter_var($feed_url, FILTER_VALIDATE_URL) === false) {
            return;
        }

        $events_data_array = array();
        $ical   = new ICal($feed_url);
        $events = $ical->events();

        if (!empty($events)) {
            foreach ($events as $event) {
                $start_time_unix = $end_time_unix = 0;

                if (isset($event['DTSTART'])) {
                    $start_time_unix = $ical->iCalDateToUnixTimestamp($event['DTSTART']);
                }
                if (isset($event['DTEND'])) {
                    $end_time_unix = $ical->iCalDateToUnixTimestamp($event['DTEND']);
                }

                $feed_name_local = !empty($feed_name) ? $feed_name : 'feed-' . $start_time_unix . '-' . $end_time_unix;

                if ($start_time_unix && $end_time_unix) {
                    $events_data_array[] = array(
                        'start_time_unix' => $start_time_unix,
                        'end_time_unix'   => $end_time_unix,
                        'feed_name'       => $feed_name_local
                    );
                }
            }
        }

        $reservation_dates_rooms = get_post_meta($listing_id, 'reservation_dates_rooms', true);
        if (!is_array($reservation_dates_rooms)) {
            $reservation_dates_rooms = array();
        }

        $room_feeds_meta = get_post_meta($listing_id, 'homey_ical_feeds_meta', true);
        $active_feed_names = array();
        if (isset($room_feeds_meta[$room_id]) && is_array($room_feeds_meta[$room_id])) {
            foreach ($room_feeds_meta[$room_id] as $feed_meta) {
                if (!empty($feed_meta['feed_name'])) {
                    $active_feed_names[] = $feed_meta['feed_name'];
                }
            }
        }

        // Limpieza: eliminar entradas obsoletas del mismo room
        foreach ($reservation_dates_rooms as $ts => &$entries) {
            foreach ($entries as $key => $entry) {
                if (is_array($entry) && isset($entry['room_id'])) {
                    if ($entry['room_id'] == $room_id && !in_array($entry['feed_name'], $active_feed_names)) {
                        unset($entries[$key]);
                    }
                    if ($entry['room_id'] == $room_id && $entry['feed_name'] == $feed_name) {
                        unset($entries[$key]);
                    }
                }
            }
            if (empty($entries)) {
                unset($reservation_dates_rooms[$ts]);
            } else {
                $entries = array_values($entries);
            }
        }
        unset($entries);

        // Añadir eventos nuevos
        if (!empty($events_data_array)) {
            foreach ($events_data_array as $data) {
                $start_time = $data['start_time_unix'];
                $end_time   = $data['end_time_unix'];
                $feed_name  = $data['feed_name'];

                $current = strtotime(gmdate('Y-m-d', $start_time)); // día UTC
                $end_day = strtotime(gmdate('Y-m-d', $end_time));

                while ($current < $end_day) {
                    if (!isset($reservation_dates_rooms[$current])) {
                        $reservation_dates_rooms[$current] = array();
                    }

                    $already = false;
                    foreach ($reservation_dates_rooms[$current] as $e) {
                        if ($e['room_id'] == $room_id && $e['feed_name'] == $feed_name) {
                            $already = true; break;
                        }
                    }

                    if (!$already) {
                        $reservation_dates_rooms[$current][] = array(
                            'room_id'   => $room_id,
                            'feed_name' => $feed_name
                        );
                    }

                    $current = strtotime('+1 day', $current);
                }
            }
        }

        update_post_meta($listing_id, 'reservation_dates_rooms', $reservation_dates_rooms);
    }
}

if (!function_exists('homey_import_icalendar_feeds')) {
    function homey_import_icalendar_feeds($listing_id)
    {
        $ical_feeds_meta = get_post_meta($listing_id, 'homey_ical_feeds_meta', true);
        update_post_meta( $listing_id, 'last_ical_update_time', date('Y-m-d H:i:s') );

        if (is_array($ical_feeds_meta) || is_object($ical_feeds_meta)) {
            foreach ($ical_feeds_meta as $key => $value) {
                $feed_name = isset($value['feed_name']) ? $value['feed_name'] : '';
                $feed_url  = $value['feed_url'];
                homey_insert_icalendar_feeds($listing_id, $feed_name, $feed_url);
            }
        }
    }
}

if (!function_exists('homey_insert_icalendar_feeds')) {
    function homey_insert_icalendar_feeds($listing_id, $feed_name, $feed_url)
    {
        if (empty($feed_url) || !intval($listing_id) || filter_var($feed_url, FILTER_VALIDATE_URL) === FALSE) {
            return;
        }

        $events_data_array = array();

        $ical   = new ICal($feed_url);
        $events = $ical->events();

        if (is_array($events) || is_object($events)) {
            if ($events) {
                foreach ($events as $event) {

                    $start_time_unix = $end_time_unix = 0;

                    if (isset($event['DTSTART'])) {
                        $start_time_unix = $ical->iCalDateToUnixTimestamp($event['DTSTART']);
                    }

                    if (isset($event['DTEND'])) {
                        $end_time_unix = $ical->iCalDateToUnixTimestamp($event['DTEND']);
                    }

                    $feed_name_local = !empty($feed_name) ? $feed_name : 'feed-name-was-null_'. $start_time_unix.'_'.$end_time_unix;

                    if (!empty($start_time_unix) && !empty($end_time_unix) && !empty($feed_name_local)) {
                        $events_data_array[] = array(
                            'start_time_unix' => $start_time_unix,
                            'end_time_unix'   => $end_time_unix,
                            'feed_name'       => $feed_name_local
                        );
                    }
                }
            }
        }

        $booked_dates_array = get_post_meta($listing_id, 'reservation_dates', true);

        if (is_array($booked_dates_array) || is_object($booked_dates_array)) {
            $ical_feed_name_txt   = isset($events_data_array[0]['feed_name']) ? $events_data_array[0]['feed_name'] : 'No Name Available';
            $events_data_to_unset = array_keys($booked_dates_array, $ical_feed_name_txt);
            if (is_array($events_data_to_unset) || is_object($events_data_to_unset)) {
                foreach ($events_data_to_unset as $key => $timestamp) {
                    unset($booked_dates_array[$timestamp]);
                }
            }
            update_post_meta($listing_id, 'reservation_dates', $booked_dates_array);
        }

        if (!empty($events_data_array)) {
            foreach ($events_data_array as $data) {
                homey_add_listing_booking_dates($listing_id, $data['start_time_unix'], $data['end_time_unix'], $data['feed_name']);
            }
        }
    }
}

if (!function_exists('homey_add_listing_booking_dates')) {
    function homey_add_listing_booking_dates($listing_id, $start_time_unix, $end_time_unix, $feed_name)
    {
        $now     = time();
        $daysAgo = $now - 3 * DAY_IN_SECONDS;

        // Normaliza a medianoche UTC
        $start_day = strtotime(gmdate("Y-m-d 00:00:00", $start_time_unix));
        $end_day   = strtotime(gmdate("Y-m-d 00:00:00", $end_time_unix));

        if ($end_day < $daysAgo) {
            return;
        }

        $booked_dates_array = get_post_meta($listing_id, 'reservation_dates', true);
        if (!is_array($booked_dates_array) || empty($booked_dates_array)) {
            $booked_dates_array = array();
        }

        $current = $start_day;
        while ($current < $end_day) {
            $booked_dates_array[$current] = $feed_name;
            $current += DAY_IN_SECONDS;
        }

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

        $listing_id = intval($_POST['listing_id']);
        $the_post   = get_post($listing_id);
        $post_owner = $the_post->post_author;
        $remove_index = $_POST['remove_index'];

        if (!is_user_logged_in() || $userID === 0) {
            echo json_encode(array('success' => false, 'message' => esc_html__('Login required', 'homey')));
            wp_die();
        }

        if (!is_numeric($listing_id) || !intval($listing_id)) {
            echo json_encode(array('success' => false, 'message' => esc_html__('Something went wrong, please contact site administer', 'homey')));
            wp_die();
        }

        if ($userID != $post_owner && !homey_is_admin()) {
            echo json_encode(array('success' => false, 'message' => esc_html__("You don't have rights to do this.", 'homey')));
            wp_die();
        }

        // Quitar feed
        $homey_ical_feeds_meta = get_post_meta($listing_id, 'homey_ical_feeds_meta', true);
        $feed_for_delete = $homey_ical_feeds_meta[$remove_index]['feed_name'] ?? '';
        unset($homey_ical_feeds_meta[$remove_index]);
        update_post_meta($listing_id, 'homey_ical_feeds_meta', $homey_ical_feeds_meta);

        // Quitar reservas asociadas
        $reservation_dates = get_post_meta($listing_id, 'reservation_dates', true);
        if (is_array($reservation_dates) || is_object($reservation_dates)) {
            foreach ($reservation_dates as $key => $value) {
                if ($feed_for_delete == $value) {
                    unset($reservation_dates[$key]);
                }
            }
        }
        update_post_meta($listing_id, 'reservation_dates', $reservation_dates);

        homey_import_icalendar_feeds($listing_id);

        echo json_encode(array('success' => true, 'message' => esc_html__("Removed Successfully.", 'homey')));
        wp_die();
    }
}

if(isset($_GET['test_ical_pre'])){
    $homey_ical_feeds_meta = get_post_meta(2753, 'homey_ical_feeds_meta', true);
    echo '<pre>'; print_r($homey_ical_feeds_meta);
    // $feed_for_delete = $homey_ical_feeds_meta[$remove_index]['feed_name']; // $remove_index no definido aquí
}

// --------- EXPORT MULTIROOM .ics ---------

if (!function_exists('homey_generate_ical_multi_dot_ics_url')) {
    function homey_generate_ical_multi_dot_ics_url($listing_id, $room_id = null) {
        $eol = "\r\n";
        $ical_prod_id          = sprintf('-//%s//iCal Event Maker', get_bloginfo('name'));
        $ical_refresh_interval = 'PT30M';
        $ical_timezone_string  = wp_timezone_string();

        $title = get_post_field('post_title', $listing_id);

        if ($room_id !== null) {
            $title     = sprintf('%s - Room %s - %s', $title, $room_id, __('Bookings', 'homey_core'));
            $file_name = "listing-{$listing_id}-room-{$room_id}.ics";
        } else {
            $title     = sprintf('%s - %s', $title, __('Bookings', 'homey_core'));
            $file_name = "listing-{$listing_id}.ics";
        }

        $permalink = get_permalink($listing_id);

        $ics_data  = "BEGIN:VCALENDAR".$eol;
        $ics_data .= "VERSION:2.0".$eol;
        $ics_data .= "PRODID:".$ical_prod_id.$eol;
        $ics_data .= "X-WR-CALNAME:".$title.$eol;
        $ics_data .= "X-HOMEY-LISTING-URL:".$permalink.$eol;
        $ics_data .= "NAME:iCal:".$title.$eol;
        $ics_data .= "REFRESH-INTERVAL;VALUE=DURATION:".$ical_refresh_interval.$eol;
        $ics_data .= "CALSCALE:GREGORIAN".$eol;
        $ics_data .= "X-WR-TIMEZONE:".$ical_timezone_string.$eol;
        $ics_data .= "METHOD:PUBLISH".$eol;

        // Sólo reservas de esa habitación (o todas si null)
        $ics_data .= homey_get_booked_dates_for_icalendar_multi($listing_id, $room_id);

        $ics_data .= "END:VCALENDAR".$eol;

        $base_folder_path   = WP_CONTENT_DIR . "/uploads/listings-calendars/";
        $upload_path        = $base_folder_path . $file_name;
        $content_upload_url = content_url() . "/uploads/listings-calendars/" . $file_name;

        if (!file_exists($base_folder_path)) {
            @mkdir($base_folder_path, 0777, true);
        }

        if ($room_id !== null) {
            update_post_meta($listing_id, "icalendar_file_url_room_{$room_id}", $content_upload_url);
        } else {
            update_post_meta($listing_id, "icalendar_file_url_with_ics", $content_upload_url);
        }

        file_put_contents($upload_path, $ics_data);

        return $content_upload_url;
    }
}

// --------- EXPORT SINGLE-ROOM .ics ---------

if (!function_exists('homey_generate_ical_dot_ics_url')) {
    function homey_generate_ical_dot_ics_url($listing_id) {

        $eol = "\r\n";
        $ical_prod_id          = sprintf('-//%s//iCal Event Maker', get_bloginfo('name'));
        $ical_refresh_interval = 'PT30M';
        $ical_timezone_string  = wp_timezone_string();

        $title     = get_post_field('post_title', $listing_id);
        $title     = sprintf('%s - %s', $title, __('Bookings', 'homey_core'));
        $permalink = get_permalink($listing_id);

        $ics_data  = "BEGIN:VCALENDAR".$eol;
        $ics_data .= "VERSION:2.0".$eol;
        $ics_data .= "PRODID:".$ical_prod_id . $eol;
        $ics_data .= "X-WR-CALNAME:".$title . $eol;
        $ics_data .= "X-HOMEY-LISTING-URL:".$permalink . $eol;
        $ics_data .= "NAME:iCal:".$title . $eol;
        $ics_data .= "REFRESH-INTERVAL;VALUE=DURATION:".$ical_refresh_interval . $eol;
        $ics_data .= "CALSCALE:GREGORIAN".$eol;
        $ics_data .= "X-WR-TIMEZONE:".$ical_timezone_string . $eol;
        $ics_data .= "METHOD:PUBLISH".$eol;

        $ics_data .= homey_get_booked_dates_for_icalendar($listing_id);
        $ics_data .= homey_get_unavailable_dates_for_icalendar($listing_id);

        $ics_data .= "END:VCALENDAR".$eol;

        $base_folder_path   = WP_CONTENT_DIR . "/uploads/listings-calendars/";
        $upload_url         = $base_folder_path."listing-{$listing_id}.ics";
        $content_upload_url = content_url()."/uploads/listings-calendars/listing-{$listing_id}.ics";

        if (!file_exists($base_folder_path)) {
            mkdir($base_folder_path, 0777, true);
        }

        update_post_meta($listing_id, "icalendar_file_url_with_ics", $content_upload_url);

        file_put_contents($upload_url, $ics_data);

        return $content_upload_url;
    }
}

// --------- AJAX actualización bajo demanda ---------

add_action('wp_ajax_nopriv_homey_run_ical_ajaxly_when_visit_listing', 'homey_run_ical_ajaxly_when_visit_listing');
add_action('wp_ajax_homey_run_ical_ajaxly_when_visit_listing', 'homey_run_ical_ajaxly_when_visit_listing');
if (!function_exists('homey_run_ical_ajaxly_when_visit_listing')) {
    function homey_run_ical_ajaxly_when_visit_listing() {
        $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        if($listing_id < 1) {
            echo json_encode(array('success' => false, 'message' => esc_html__('No listing found.', 'homey')));
            wp_die();
        }

        $last_ical_update_time = get_post_meta( $listing_id, 'last_ical_update_time', true );

        if ($last_ical_update_time) {
            $last_ical_update_time = strtotime($last_ical_update_time);
            $current_time = time();
            $time_difference = $current_time - $last_ical_update_time;
            if ($time_difference > 600) {
                homey_import_icalendar_feeds($listing_id);
                echo json_encode(array('success' => true, 'message' => esc_html__('Ical updated on time: .', 'homey')));
                wp_die();
            }
        } else {
            homey_import_icalendar_feeds($listing_id);
            echo json_encode(array('success' => true, 'message' => esc_html__('Ical updated on time: .', 'homey')));
            wp_die();
        }

        echo json_encode(array('success' => true, 'message' => esc_html__('Ical okay.', 'homey')));
        wp_die();
    }
}
// Fin del archivo
