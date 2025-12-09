<?php

//add_action('cron_job_update_custom_period_when_expire', 'update_custom_period_when_expire');
function update_custom_period_when_expire()
{
    return 1;
    $args = array(
        'numberposts' => -1,
        'post_type' => 'listing'
    );

    $arr = array();

    $latest_listings = get_posts($args);
    $listing_ids = wp_list_pluck($latest_listings, 'ID');

    $current_period_meta_array = array();

    // echo "<pre>";
    // print_r( $ids );
    // exit;

    $all_dates = array();
    foreach ($listing_ids as $key => $id) {

        // $ids[] = $list->ID;
        $current_period_meta = array();

        $current_period_meta = get_post_meta($id, 'homey_custom_period', true);

        if (!is_null($current_period_meta) && $current_period_meta !== '') {
            // echo $id . ' ';
            //  echo $key . "  " . $id . "<br>";

            // $current_period_meta_array[] = array_key_last( $current_period_meta );
            $current_period_meta_array['periods'] = $current_period_meta;

            // print_r( $current_period_meta );

            $expiry_date = array();
            $start_date = array();
            $sample = array();
            $start_date_check = false;
            $n = 0;
            foreach ($current_period_meta as $timestamp => $period) {

                $_date = new DateTime("@" . $timestamp);
                $_date->modify('tomorrow');
                $tomorrrow_date = $_date->getTimestamp();

                // echo date('Y-m-d', $timestamp) .'->'. date('Y-m-d', $tomorrrow_date) . '<br>';

                // if( isset( $current_period_meta[$tomorrrow_date] ) && $current_period_meta[$tomorrrow_date] !== '' ) {

                if ($start_date_check || $n == 0) {
                    $start_date['start'][] = $timestamp;
                    $start_date_check = false;
                }

                if ($period['night_price'] !== $current_period_meta[$tomorrrow_date]['night_price']) {
                    // echo $period['night_price'] . '<br>';
                    // print_r( $current_period_meta[$tomorrrow_date] );
                    $expiry_date['end'][] = $timestamp;
                    $sample['sample_periods'][] = $period;
                    // exit;
                    $start_date_check = true;
                }

                $n++;
                // }
            }
            $all_dates[$id] = array_merge($start_date, $expiry_date, $sample);
            // print_r( $start_date );
        }
    }

    // exit;
    // $date =  new DateTime("@".$current_period_meta_array[0]);

    $periods_list = array();

    foreach ($all_dates as $id => $value) {

        $current_period_meta_array = get_post_meta($id, 'homey_custom_period', true);
        if (empty($current_period_meta_array)) {
            $current_period_meta_array = array();
        }

        for ($i = 0; $i < count($value['start']); $i++) {

            $start = $value['start'][$i];
            $end = $value['end'][$i];

            $_date = new DateTime("@" . $start);
            $now = date_timestamp_get(date_create(date('Y-m-d')));

            // check if period expired
            // if( $end < $now ) {
            //     while ( $start <= $end ) {

            //         $periods_list[$id][$i][date('Y-m-d', $start)] = $value['sample_periods'][$i];

            //         $_date->modify('+1 day');
            //         $start = $_date->getTimestamp();
            //     }
            // }

            // update if period expired

            $start_date = new DateTime("@" . $value['start'][$i]);
            $start_date->modify('+1 year');
            $start_date_modified = $start_date->getTimestamp();

            $end_date = new DateTime("@" . $end);
            $end_date->modify('+1 year');
            $end_date_modified = $end_date->getTimestamp();

            $updated = $value['sample_periods'][$i]['updated'] ?? '0';

            if ($updated != 1) {
                if ($end < $now) {
                    while ($start_date_modified <= $end_date_modified) {

                        $value['sample_periods'][$i]['updated'] = '1';
                        // $periods_list[$id][$i][date('Y-m-d', $start_date_modified)] = $value['sample_periods'][$i];
                        $current_period_meta_array[$start_date_modified] = $value['sample_periods'][$i];

                        $start_date->modify('+1 day');
                        $start_date_modified = $start_date->getTimestamp();
                    }
                }
            }
        }
        // $periods_list[] = $current_period_meta_array;
        //update_post_meta($id, 'homey_custom_period', $current_period_meta_array);
    }

    // echo "<pre>";
    // print_r( $periods_list );
    // print_r( $current_period_meta_array[1] );
    // exit;


} // end function update_custom_period_when_expire

// update_custom_period_when_expire();

if (!function_exists('rewnew_custom_price_period_if_expired')) {
    function rewnew_custom_price_period_if_expired($listing_id = 0)
    {
        //get all custom periods of current year
        global $wpdb;
        $prefix = $wpdb->prefix;

        if ($listing_id > 0) {
            $custom_period_query = 'SELECT * FROM ' . $prefix . 'postmeta WHERE post_id = $listing_id and meta_key = "homey_custom_period"';
        } else {
            $custom_period_query = 'SELECT * FROM ' . $prefix . 'postmeta WHERE meta_key = "homey_custom_period"';
        }

        $custom_periods = $wpdb->get_results($custom_period_query);

        $i = 0;
        $night_price = '';
        $weekend_price = '';
        $guest_price = '';

        foreach ($custom_periods as $key => $custom_period) {
            $custom_period_with_new_year = array();
            $period_array_new_year = '';
            $period_array = unserialize($custom_period->meta_value);
            $listing_id = $custom_period->post_id;

            echo ' This is list id#' . $listing_id . ' > ';

            if (empty($period_array)) {
                continue;
            }

            if (is_array($period_array)) {
                ksort($period_array);
            }

            //check every date if any expired
            foreach ($period_array as $timestamp => $data) {
                $is_consecutive_day = 0;
                $from_date = new DateTime("@" . $timestamp);
                $to_date = new DateTime("@" . $timestamp);
                $tomorrrow_date = new DateTime("@" . $timestamp);

                $tomorrrow_date->modify('tomorrow');
                $tomorrrow_date = $tomorrrow_date->getTimestamp();


                if ($i == 0) {
                    $i = 1;

                    $night_price = $data['night_price'];
                    $weekend_price = $data['weekend_price'];
                    $guest_price = $data['guest_price'];

                    $from_date_unix = $from_date->getTimestamp();
                    // start date of custom period
                }

                if (!array_key_exists($tomorrrow_date, $period_array)) {
                    $is_consecutive_day = 1;

                } else {

                    if ($period_array[$tomorrrow_date]['night_price'] != $night_price ||
                        $period_array[$tomorrrow_date]['weekend_price'] != $weekend_price ||
                        $period_array[$tomorrrow_date]['guest_price'] != $guest_price) {
                        $is_consecutive_day = 1;
                    }
                }

                if ($is_consecutive_day == 1) {
                    if ($i == 1) {
                        $to_date_unix = $from_date->getTimestamp();

                        // end date of custom period, check here
                        $strtotime = strtotime(date('Y-m-d H:i:s'));
                        $currentYear = date('Y');
                        $yearFromEndDate = date('Y', $from_date_unix);
                        //echo $strtotime .' > '. $from_date->getTimestamp().' zahidum,,, ';
                        //if(1==1 || $strtotime > $from_date->getTimestamp()){
                        if ($yearFromEndDate == $currentYear && $strtotime > $from_date->getTimestamp()) {
                            //echo ' yes';
                            $period_array_new_year = get_all_days_of_start_end_date(date('d-m-Y', $from_date_unix), date('d-m-Y', $to_date_unix), $data);

                            if (is_array($period_array_new_year)) {
                                //echo '<pre>'; print_r($period_array); echo ' dosra <pre>'; print_r($period_array_new_year);
                                $period_array_new_year = $period_array + $period_array_new_year;
                                //echo '<pre> phr sy '; print_r($period_array);
                            }
                        } else {
                            //echo 'no';
                        }
                    }
                    $i = 0;
                    $night_price = $data['night_price'];
                    $weekend_price = $data['weekend_price'];
                    $guest_price = $data['guest_price'];
                }
            } // End foreach

            //echo ' llll '; print_r($period_array); exit;
            if($period_array_new_year != ''){
                update_post_meta($listing_id, 'homey_custom_period', $period_array_new_year);
            }
        }

    }
}

function get_all_days_of_start_end_date($start_date, $end_date, $period_meta)
{

    $start_date = new DateTime($start_date);
    $start_date = $start_date->modify('+1 year');
    $start_date_unix = $start_date->getTimestamp();

    $end_date = new DateTime($end_date);
    $end_date = $end_date->modify('+1 year');
    $end_date_unix = $end_date->getTimestamp();

    $current_period_meta_array[$start_date_unix] = $period_meta;

    $start_date->modify('tomorrow');
    $start_date_unix = $start_date->getTimestamp();

    while ($start_date_unix <= $end_date_unix) {
        $current_period_meta_array[$start_date_unix] = $period_meta;
        $start_date->modify('tomorrow');
        $start_date_unix = $start_date->getTimestamp();
    }

    return $current_period_meta_array;
}

if (@$_GET['custom_test']) {
    rewnew_custom_price_period_if_expired();
}


if (!wp_next_scheduled('wpen_cron_rewnew_custom_price_period_if_expired')) {
    wp_schedule_event(time(), 'daily', 'wpen_cron_rewnew_custom_price_period_if_expired');
}
add_action('wpen_cron_rewnew_custom_price_period_if_expired', 'rewnew_custom_price_period_if_expired');
?>