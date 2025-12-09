<?php
if(!function_exists('homeySearchCalendarExp')) {
    function homeySearchCalendarExp() {

        $numberOfMonths = 1;
        $timeNow  = current_time( 'timestamp' );
        $now = date('Y-m-d');
        $date = new DateTime();

        $currentMonth = gmdate('m', $timeNow);
        $currentYear  = gmdate('Y', $timeNow);
        $unixMonth = mktime(0, 0 , 0, $currentMonth, 1, $currentYear);

        while( $numberOfMonths <= homey_calendar_months() ) {

            homeySearchGenerateMonthExp( $numberOfMonths, $unixMonth, $currentMonth, $currentYear );

            $date->modify( 'first day of next month' );
            $currentMonth = $date->format( 'm' );
            $currentYear  = $date->format( 'Y' );
            $unixMonth = mktime(0, 0 , 0, $currentMonth, 1, $currentYear);

            $numberOfMonths++;
        }

    }
}

if(!function_exists('homeySearchDaysInMonthExp')) {
    function homeySearchDaysInMonthExp($month = null, $year = null) {

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

if(!function_exists('homeySearchGenerateMonthExp')) {
    function homeySearchGenerateMonthExp( $numberOfMonths, $unixMonth, $currentMonth, $currentYear ) {
        global $wpdb, $post, $wp_locale;
        $post_id = isset($post->ID) ? $post->ID : 0;
        $bookedDays  = get_post_meta($post_id, 'reservation_dates',true  );
        $pending_dates  = get_post_meta($post_id, 'reservation_pending_dates',true  );

        if(empty($bookedDays)) {
            $bookedDays = array();
        }

        if(empty($pending_dates)) {
            $pending_dates = array();
        }


        $daysInMonth = homeySearchDaysInMonthExp($currentMonth, $currentYear);
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
        $style = "";

        $main_class = 'exp-only-calendar';

        if( $numberOfMonths > 1 ) {
            $style = 'style="display:none;"';
        }

        $main_search_calendar_wrap_class = 'single-main-exp-search-calendar-wrap';

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

            if( $timestamp < (time()-24*60*60) ) {
                $dayClass = "day-disabled past-day";
            } else {
                $dayClass = "future-day";
            }

            $dateTimeStamp  = new DateTime($currentYear.'-'.$currentMonth.'-'.$day);
            $dateTimeStamp = $dateTimeStamp->getTimestamp();

            $homey_get_formatted_date = homey_get_formatted_date($currentYear, $currentMonth, $day);

            if ( $day == gmdate('j', current_time('timestamp')) && $currentMonth == gmdate('m', current_time('timestamp')) && $currentYear == gmdate('Y', current_time('timestamp')) ) {

                $monthDays .= '<li data-timestamp="'.esc_attr($dateTimeStamp).'" data-formatted-date="'.$homey_get_formatted_date.'" class="current-month current-day '.esc_attr($dayClass).'"><span class="day-number">'.esc_attr($day).'</span></li>';
            } else {

                $monthDays .= '<li data-timestamp="'.esc_attr($dateTimeStamp).'" data-formatted-date="'.$homey_get_formatted_date.'" class="current-month '.esc_attr($dayClass).'">
	            	<span class="day-number">'.esc_attr($day).'</span>
	            </li>';
            }

        }

        $output = '<div class="exp-search-calenar '.esc_attr($main_search_calendar_wrap_class).' '.esc_attr($main_class).'" data-month = "'.esc_attr($numberOfMonths).'" '.$style.'>';

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

        $output_escaped = $output;
        echo ''.$output_escaped;

    } //homeySearchGenerateMonthExp
} // function_exists

$search_calendar_main = 'search-calendar-main';
?>
<?php
$enable_search_exp = homey_option('enable_search_exp');
if($enable_search_exp == 'on' || $enable_search_exp == 1){ ?>
    <div class="exp-search-calendar search-calendar <?php echo esc_attr($search_calendar_main); ?> clearfix">
        <div class="calendar-arrow"></div>
        <?php homeySearchCalendarExp(); ?>

        <button type="button" style="z-index: 99;" class="btn-link btn-clear-calendar"><?php echo esc_html__('Clear', 'homey'); ?></button>

        <div class="calendar-navigation custom-actions">
            <button class="main-exp-search-cal-prev btn btn-action pull-left disabled"><i class="homey-icon homey-icon-arrow-left-1" aria-hidden="true"></i></button>
            <button class="main-exp-search-cal-next btn btn-action pull-right"><i class="homey-icon homey-icon-arrow-right-1" aria-hidden="true"></i></button>
        </div><!-- calendar-navigation -->
    </div>
    <!-- On mobile: display this button below when  the user selected arrival and depart dates -->
    <button style="display: none;" class="btn btn-primary search-calendar-btn"><?php echo esc_html__('Done', 'homey'); ?></button>
<?php } ?>
