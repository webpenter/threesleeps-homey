<?php
if(!function_exists('homeyAvailabilityCalendar')) {
	function homeyAvailabilityCalendar($show_cross_btn = 1, $listing_id = -1) {

		$numberOfMonths = 1;
		$timeNow  = current_time( 'timestamp' );
	    $now = date('Y-m-d');
	    $date = new DateTime();
	    
	    $currentMonth = gmdate('m', $timeNow);
	    $currentYear  = gmdate('Y', $timeNow);             
	    $unixMonth = mktime(0, 0 , 0, $currentMonth, 1, $currentYear);

        if($show_cross_btn == 1) {
            echo "<div style='position: absolute; right: 10px; top: 10px; z-index:999' class='pull-right' id='calendar-cross-btn'><i id='calendar-cross-btn-i' class='homey-icon homey-icon-close'></i></div>";
        }

        while( $numberOfMonths <= homey_calendar_months() ) {
         	
         	homeyGenerateMonth( $numberOfMonths, $unixMonth, $currentMonth, $currentYear, $listing_id );
          
            $date->modify( 'first day of next month' );
            $currentMonth = $date->format( 'm' );
            $currentYear  = $date->format( 'Y' );
            $unixMonth = mktime(0, 0 , 0, $currentMonth, 1, $currentYear);

            $numberOfMonths++;
        }
	    
	}
}

if(!function_exists('homeyDaysInMonth')) {
	function homeyDaysInMonth($month = null, $year = null) {
	     
	    $timeNow  = current_time( 'timestamp' );    
	    if(null == ($year)) {
	        $year = gmdate('Y', $timeNow);
	    }

	    if(null == ($month)){
	        $month = gmdate('m', $timeNow);
	    }

	    $unixMonth = mktime(0, 0 , 0, $month, 1, $year);
	         
	    return date('t', $unixMonth);;
	}
}

if(!function_exists('homeyGenerateMonth')) {
	function homeyGenerateMonth( $numberOfMonths, $unixMonth, $currentMonth, $currentYear, $listing_id = -1 ) {
		global $wpdb, $post, $wp_locale;

        if($listing_id == -1) {
            $listing_id = $post->ID;
        }
		$bookedDays  = get_post_meta($listing_id, 'reservation_dates',true  );
		$pending_dates  = get_post_meta($listing_id, 'reservation_pending_dates',true  );
		$unavailable_dates  = get_post_meta($listing_id, 'reservation_unavailable',true  );

        $booking_type = homey_booking_type_by_id($listing_id);

        if(empty($bookedDays)) {
			$bookedDays = array();
		}

		if(empty($pending_dates)) {
			$pending_dates = array(); 
		}

		if(empty($unavailable_dates)) {
			$unavailable_dates = array(); 
		}

		$daysInMonth = homeyDaysInMonth($currentMonth, $currentYear);
        $weekBegins = intval(homey_option('weekBegins', 1));
        $weekArray = array();
        $weekDays = '';
        $monthDays = '';
        $weekDayInitial = true;
        $prevMonthDays = '';
        $calendar_day_class = '';
        $resv_class = '';
        //$resv_start = '';
        $resv_start=1;
        $resv_end = '';


		if( $numberOfMonths % 2 == 1 ) {
			$main_class = 'left-calendar';
		} else {
			$main_class = 'right-calendar';
		}

		$style = "";
        if( $numberOfMonths > 2 ) {
            $style = 'style="display:none;"';
        }

		for ( $wCount = 0; $wCount <= 6; $wCount++ ) {
			$weekArray[] = $wp_locale->get_weekday(($wCount + $weekBegins)%7);
		}

		foreach ( $weekArray as $weekDay ) {
			$dayName = (true == $weekDayInitial) ? $wp_locale->get_weekday_initial($weekDay) : $wp_locale->get_weekday_abbrev($weekDay);
			$weekDays .= '<li data-start-week="'.$weekBegins.'" data-dayName = "'.esc_attr($weekDay).'">'.esc_attr($dayName).'</li>';
		}


		$weekMod = calendar_week_mod(date('w', $unixMonth) - $weekBegins); // Get number of days since the start of the week.
		if( $weekMod != 0 ) {
			for( $wm = 1; $wm <= $weekMod; $wm++ ) {
				$prevMonthDays .= '<li class="prev-month"></li>';
			}
		}

		for ( $day = 1; $day <= $daysInMonth; ++$day ) {
			$timestamp = strtotime( $day.'-'.$currentMonth.'-'.$currentYear);

			$dayClass = '';
			$resv_class='';


            // to check if the date is passed
            if( $timestamp < (time()-24*60*60) ) {
                $dayClass = "day-disabled past-day";
            }

            if(array_key_exists($timestamp, $unavailable_dates) && array_key_exists($timestamp, $bookedDays)) {
                unset($unavailable_dates[$timestamp]);
            }

            if(array_key_exists($timestamp, $unavailable_dates)) {
                $calendar_day_class = 'day-unavailable homey-not-available-for-booking';

                $resv_end=0;
                if($resv_start == 1 && $booking_type != 'per_month'){
                    $resv_class  = '';
                    $resv_start  = 0;
                }

            } elseif( array_key_exists($timestamp, $pending_dates) && !array_key_exists($timestamp, $bookedDays) ) {
            	$calendar_day_class = 'day-pending homey-not-available-for-booking';
            	$resv_end=1;
                if($resv_start == 1  && $booking_type != 'per_month'){
                    $resv_class  = 'reservation_start';
                    $resv_start  = 0;
                }

            }  elseif( array_key_exists($timestamp, $bookedDays) ) {
                $calendar_day_class = 'day-booked homey-not-available-for-booking';
                $resv_end=1;
                if($resv_start == 1  && $booking_type != 'per_month'){
                    $resv_class  = 'reservation_start';
                    $resv_start  = 0;
                }

            } elseif(array_key_exists($timestamp, $unavailable_dates)) {
            	$calendar_day_class = 'day-unavailable homey-not-available-for-booking';
            } else {
                if( $timestamp < (time()-24*60*60) ) {
                    $dayClass = "day-disabled past-day";
                } else {
                    $dayClass = "future-day";
                    $calendar_day_class = 'day-available';
                }

				$resv_start=1;
                if($resv_end===1){
                    $resv_class=' reservation_end ';
                    $resv_end=0;
                }
            }

            $dateTimeStamp  = new DateTime($currentYear.'-'.$currentMonth.'-'.$day);
        	$dateTimeStamp = $dateTimeStamp->getTimestamp();

        	$homey_get_formatted_date = homey_get_formatted_date($currentYear, $currentMonth, $day);

            if ( $day == gmdate('j', current_time('timestamp')) && $currentMonth == gmdate('m', current_time('timestamp')) && $currentYear == gmdate('Y', current_time('timestamp')) ) {

            	$monthDays .= '<li data-timestamp="'.esc_attr($dateTimeStamp).'" data-formatted-date="'.$homey_get_formatted_date.'" class="current-month current-day '.esc_attr($resv_class).' '.esc_attr($calendar_day_class).' '.esc_attr($dayClass).'"><span class="day-number">'.esc_attr($day).'</span></li>';
            } else {

	            $monthDays .= '<li data-timestamp="'.esc_attr($dateTimeStamp).'" data-formatted-date="'.$homey_get_formatted_date.'" class="current-month '.esc_attr($resv_class).' '.esc_attr($calendar_day_class).' '.esc_attr($dayClass).'">
	            	<span class="day-number">'.esc_attr($day).'</span>
	            </li>';
	        }

		}

        $output = '<div class="single-listing-calendar-wrap '.esc_attr($main_class).'" data-month = "'.esc_attr($numberOfMonths).'" '.$style.'>';

        	$output .= '<div class="month clearfix">';
            	$output .= '<h4>'.date_i18n("F", mktime(0, 0, 0, $currentMonth, 10)).' <span>'.esc_attr($currentYear).'</span></h4>';
            $output .= '</div>';


            $output .= '<ul class="weekdays clearfix">';
                $output .= $weekDays;
            $output .= '</ul>';

            $output .= '<ul class="days clearfix">';
            	$output .= $prevMonthDays;

            	$output .= $monthDays;

            $output .= '</ul>';


        $output .= '</div>'; // end main div

        echo ''.$output;

	} //homeyGenerateMonth
} // function_exists

//experiences calendar
if(!function_exists('expAvailabilityCalendar')) {
    function expAvailabilityCalendar($show_cross_btn = 1, $experience_id = -1) {

        $numberOfMonths = 1;
        $timeNow  = current_time( 'timestamp' );
        $now = date('Y-m-d');
        $date = new DateTime();

        $currentMonth = gmdate('m', $timeNow);
        $currentYear  = gmdate('Y', $timeNow);
        $unixMonth = mktime(0, 0 , 0, $currentMonth, 1, $currentYear);

        if($show_cross_btn == 1){
            echo "<div style='position: absolute; right: 10px; top: 10px;' class='pull-right' id='calendar-cross-btn'><i id='calendar-cross-btn-i' class='homey-icon homey-icon-close'></i></div>";
        }

        while( $numberOfMonths <= homey_calendar_months() ) {

            expGenerateMonth( $numberOfMonths, $unixMonth, $currentMonth, $currentYear, $experience_id );

            $date->modify( 'first day of next month' );
            $currentMonth = $date->format( 'm' );
            $currentYear  = $date->format( 'Y' );
            $unixMonth = mktime(0, 0 , 0, $currentMonth, 1, $currentYear);

            $numberOfMonths++;
        }

    }
}

if(!function_exists('expDaysInMonth')) {
    function expDaysInMonth($month = null, $year = null) {

        $timeNow  = current_time( 'timestamp' );
        if(null == ($year)) {
            $year = gmdate('Y', $timeNow);
        }

        if(null == ($month)){
            $month = gmdate('m', $timeNow);
        }

        $unixMonth = mktime(0, 0 , 0, $month, 1, $year);

        return date('t', $unixMonth);;
    }
}

if(!function_exists('expGenerateMonth')) {
    function expGenerateMonth( $numberOfMonths, $unixMonth, $currentMonth, $currentYear, $experience_id = -1 ) {
        global $wpdb, $post, $wp_locale;
        if($experience_id == -1) {
            $experience_id = $post->ID;
        }

        $bookedDays  = get_post_meta($experience_id, 'reservation_dates',true  );
        $pending_dates  = get_post_meta($experience_id, 'reservation_pending_dates',true  );
        $unavailable_dates  = get_post_meta($experience_id, 'reservation_unavailable',true  );

//echo '<pre>all meta';print_r(get_post_meta(($experience_id));
        if(empty($bookedDays)) {
            $bookedDays = array();
        }

        if(empty($pending_dates)) {
            $pending_dates = array();
        }

        if(empty($unavailable_dates)) {
            $unavailable_dates = array();
        }


        $daysInMonth = expDaysInMonth($currentMonth, $currentYear);
        $weekBegins = intval(homey_option('weekBegins', 1));
        $weekArray = array();
        $weekDays = '';
        $monthDays = '';
        $weekDayInitial = true;
        $prevMonthDays = '';
        $calendar_day_class = '';
        $resv_class = '';
        $resv_start = '';
        $resv_end = '';
        $total_no_of_attendee = get_post_meta($experience_id, 'homey_total_guests_plus_additional_guests', true );

        $main_class = 'experience-calendar';

        $style = "";
        if( $numberOfMonths > 1 ) {
            $style = 'style="display:none;"';
        }

        for ( $wCount = 0; $wCount <= 6; $wCount++ ) {
            $weekArray[] = $wp_locale->get_weekday(($wCount + $weekBegins)%7);
        }

        foreach ( $weekArray as $weekDay ) {
            $dayName = (true == $weekDayInitial) ? $wp_locale->get_weekday_initial($weekDay) : $wp_locale->get_weekday_abbrev($weekDay);
            $weekDays .= '<li data-dayName = "'.esc_attr($weekDay).'">'.esc_attr($dayName).'</li>';
        }

        $weekMod = calendar_week_mod(date('w', $unixMonth) - $weekBegins); // Get number of days since the start of the week.
        if( $weekMod != 0 ) {
            for( $wm = 1; $wm <= $weekMod; $wm++ ) {
                $prevMonthDays .= '<li class="prev-month"></li>';
            }
        }

        for ( $day = 1; $day <= $daysInMonth; ++$day ) {
            $timestamp = strtotime( $day.'-'.$currentMonth.'-'.$currentYear);

            $dayClass = '';
            $resv_class='';

            // to check if the date is passed
            if( $timestamp < (time()-24*60*60) ) {
                $dayClass = "day-disabled past-day";
            }

            $remaining_no_of_attendee = $total_no_of_attendee;

            if( $timestamp < (time()-24*60*60) ) {
                $dayClass = "day-disabled past-day";
            } else {
                // check if any slot is open?
                if( array_key_exists($timestamp, $bookedDays) || array_key_exists($timestamp, $pending_dates) ) {
                    $remaining_no_of_attendee = remainingAttendeeSlots($total_no_of_attendee, $timestamp, $bookedDays, $pending_dates);
                }

                $dayClass = "future-day";
            }

            if( array_key_exists($timestamp, $bookedDays) && $remaining_no_of_attendee < 1 ) {
                $calendar_day_class = 'day-booked homey-not-available-for-booking';
                $resv_end=1;
                if($resv_start == 1){
                    $resv_class  = '';
                    $resv_start  = 0;
                }

            } elseif( array_key_exists($timestamp, $pending_dates) && $remaining_no_of_attendee < 1) {
                $calendar_day_class = 'day-pending homey-not-available-for-booking';
                $resv_end=1;
                if($resv_start == 1){
                    $resv_class  = '';
                    $resv_start  = 0;
                }

            } elseif( array_key_exists($timestamp, $unavailable_dates) ) {
                $calendar_day_class = 'orphan-date day-pending homey-not-available-for-booking';
                $resv_end=1;
                if($resv_start == 1){
                    $resv_class  = '';
                    $resv_start  = 0;
                }

            } else {
                $calendar_day_class = 'day-available';
                $resv_start=1;
                if($resv_end===1){
                    $resv_class='';
                    $resv_end=0;
                }
            }

            $dateTimeStamp  = new DateTime($currentYear.'-'.$currentMonth.'-'.$day);
            $dateTimeStamp = $dateTimeStamp->getTimestamp();

            $homey_get_formatted_date = homey_get_formatted_date($currentYear, $currentMonth, $day);

            if ( $day == gmdate('j', current_time('timestamp')) && $currentMonth == gmdate('m', current_time('timestamp')) && $currentYear == gmdate('Y', current_time('timestamp')) ) {

                $monthDays .= '<li data-available-slots-info="'.esc_html__('Available number of slots:', 'homey')." ".$remaining_no_of_attendee.'" data-timestamp="'.esc_attr($dateTimeStamp).'" data-formatted-date="'.$homey_get_formatted_date.'" class="current-month current-day exp-calendar '.esc_attr($resv_class).' '.esc_attr($calendar_day_class).' '.esc_attr($dayClass).'"><span class="day-number">'.esc_attr($day).'</span></li>';
            } else {

                $monthDays .= '<li data-available-slots-info="'.esc_html__('Available number of slots:', 'homey')." ".$remaining_no_of_attendee.'" data-timestamp="'.esc_attr($dateTimeStamp).'" data-formatted-date="'.$homey_get_formatted_date.'" class="current-month exp-calendar '.esc_attr($resv_class).' '.esc_attr($calendar_day_class).' '.esc_attr($dayClass).'">
	            	<span class="day-number">'.esc_attr($day).'</span>
	            </li>';
            }

        }

        $output = '<div class="single-experience-calendar-wrap '.esc_attr($main_class).'" data-month = "'.esc_attr($numberOfMonths).'" '.$style.'>';

        $output .= '<div class="month clearfix">';
        $output .= '<h4>'.date_i18n("F", mktime(0, 0, 0, $currentMonth, 10)).' <span>'.esc_attr($currentYear).'</span></h4>';
        $output .= '</div>';


        $output .= '<ul class="weekdays clearfix">';
        $output .= $weekDays;
        $output .= '</ul>';

        $output .= '<ul class="days clearfix">';
        $output .= $prevMonthDays;

        $output .= $monthDays;

        $output .= '</ul>';


        $output .= '</div>'; // end main div

        echo ''.$output;

    } //expGenerateMonth
} // function_exists

// end experiences calendar
