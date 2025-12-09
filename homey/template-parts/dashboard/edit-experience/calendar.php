<?php
global $homey_local;

$class = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'calendar') {
    $class = 'in active';
}

if(!function_exists('homeyEditExperienceCalendar')) {
    function homeyEditExperienceCalendar() {

        $numberOfMonths = 1;
        $timeNow  = current_time( 'timestamp' );
        $now = date('Y-m-d');
        $date = new DateTime();

        $currentMonth = gmdate('m', $timeNow);
        $currentYear  = gmdate('Y', $timeNow);
        $unixMonth = mktime(0, 0 , 0, $currentMonth, 1, $currentYear);

        while( $numberOfMonths <= homey_calendar_months() ) {

            homeyEditExperienceGenerateMonth( $numberOfMonths, $unixMonth, $currentMonth, $currentYear );

            $date->modify( 'first day of next month' );
            $currentMonth = $date->format( 'm' );
            $currentYear  = $date->format( 'Y' );
            $unixMonth = mktime(0, 0 , 0, $currentMonth, 1, $currentYear);

            $numberOfMonths++;
        }

    }
}

if(!function_exists('homeyEditExperienceDaysInMonth')) {
    function homeyEditExperienceDaysInMonth($month = null, $year = null) {

        $timeNow  = current_time( 'timestamp' );
        if(null == ($year)) {
            $year = gmdate('Y', $timeNow);
        }

        if(null == ($month)){
            $month = gmdate('m', $timeNow);
        }


        $unixMonth = mktime(0, 0 , 0, $month, 1, $year);

        return date('t', $unixMonth);
    }
}

if(!function_exists('homeyEditExperienceGenerateMonth')) {
    function homeyEditExperienceGenerateMonth( $numberOfMonths, $unixMonth, $currentMonth, $currentYear ) {
        global $homey_local, $wpdb, $post, $wp_locale;

        $experience_id = isset($_GET['edit_experience']) ? $_GET['edit_experience'] : '';

        $bookedDays  = get_post_meta($experience_id, 'reservation_dates',true  );
        $pending_dates  = get_post_meta($experience_id, 'reservation_pending_dates',true  );
        $unavailable_dates  = get_post_meta($experience_id, 'reservation_unavailable',true  );

        $reservation_page_link = homey_get_template_link('template/dashboard-reservations-experiences.php');

        if(empty($bookedDays)) {
            $bookedDays = array();
        }

        if(empty($pending_dates)) {
            $pending_dates = array();
        }

        if(empty($unavailable_dates)) {
            $unavailable_dates = array();
        }


        $daysInMonth = homeyEditExperienceDaysInMonth($currentMonth, $currentYear);
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
        $day_status_li = '';

        $style = "";
        if( $numberOfMonths > 1 ) {
            $style = 'style="display:none;"';
        }

        for ( $wCount = 0; $wCount <= 6; $wCount++ ) {
            $weekArray[] = $wp_locale->get_weekday(($wCount + $weekBegins)%7);
        }

        foreach ( $weekArray as $weekDay ) {
            $dayName = (true == $weekDayInitial) ? $wp_locale->get_weekday_initial($weekDay) : $wp_locale->get_weekday_abbrev($weekDay);
            $weekDays .= '<li data-dayName = "'.esc_attr($weekDay).'">'.$dayName.'</li>';
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
                $dayClass = "past-day";
            } else {
                $dayClass = "future-day";
            }

            if( array_key_exists($timestamp, $bookedDays) ) {
                $calendar_day_class = 'exp-booked booked';
                $booked_id = $bookedDays[$timestamp];
                $resv_end=1;
                if($resv_start == 1){
                    $resv_class  = 'reservation_start';
                    $resv_start  = 0;
                }

                $res_all_ids = isset($booked_id['reservation_ids']) && !empty($booked_id['reservation_ids']) ? explode(",", $booked_id['reservation_ids']) : [];
                $day_status = '<span class="day-status">';
                $day_pic = '';

                if(!is_array($res_all_ids)){
                    $day_status = '<span class="day-status"><a>'.$bookedDays[$timestamp]['reservation_ids'].esc_html__(' no of attendee ', 'homey').$bookedDays[$timestamp]['no_of_attendee'].'</a></span>';
                }

                foreach ($res_all_ids as $key => $i){
                    if($i < 1){
                        continue;
                    }
                    if(is_numeric($booked_id)) {
                        $pending_detail_link = add_query_arg( 'reservation_detail', $i, $reservation_page_link );
                        $resv_renter = get_post_meta($i, 'experience_renter', true);
                        $renter_meta = homey_get_author_by_id('24', '24', 'img-circle', $resv_renter);

                        $day_status .= '<a href="'.esc_url($pending_detail_link).'">'.esc_attr($homey_local['pending_id_label']).': '.$i.'</a><br>';
                        $day_pic .= $renter_meta['photo'];
                    } else {
                        $booked_detail_link = '';
                        if(isset($booked_id['reservation_ids'])){
                            $li_reservation_link = 'https://'.$_SERVER['HTTP_HOST'].'/'.$_SERVER['REQUEST_URI'].'&delete_reservation_id='.$i;
                            $day_status_li .= '<li>'.esc_html__('Booking ID:', 'homey').' '.$i.' <a href="'.$li_reservation_link.'"><i class="homey-icon homey-icon-bin-1-interface-essential"></i></a></li>';
                        }
                        $day_pic = '';
                    }
                }

                $day_status .= '</span>';

            } elseif( array_key_exists($timestamp, $pending_dates) ) {
                $calendar_day_class = 'exp-pending pending';
                $pending_id = $pending_dates[$timestamp];

                if(isset($pending_id['reservation_ids'])){
                    $pending_id = $pending_id['reservation_ids'];
                }

                $resv_end=1;
                if($resv_start == 1){
                    $resv_class  = 'reservation_start';
                    $resv_start  = 0;
                }

               $res_all_ids = explode(",", $pending_id);
               $day_status = '<span class="day-status">';
               $day_pic = '';

                foreach ($res_all_ids as $i){
                    $pending_detail_link = add_query_arg( 'reservation_detail', $i, $reservation_page_link );
                    $resv_renter = get_post_meta($i, 'experience_renter', true);
                    $renter_meta = homey_get_author_by_id('24', '24', 'img-circle', $resv_renter);

                    $day_status .= '<a href="'.esc_url($pending_detail_link).'">'.esc_attr($homey_local['pending_id_label']).': '.$i.'</a><br>';
                    $day_pic .= $renter_meta['photo'];
                }

                $day_status .= '</span>';

            } elseif(array_key_exists($timestamp, $unavailable_dates)) {
                $calendar_day_class = 'exp-unavailable unavailable';
                $day_status = '<span class="day-status">'.esc_attr($homey_local['unavail_label']).'</span>';
                $day_pic = '';
            } else {

                if( $timestamp < (time()-24*60*60) ) {
                    $calendar_day_class = 'exp-unavailable unavailable';
                    $day_status = '<span class="day-status"></span>';
                    $resv_class = '';
                } else {
                    $calendar_day_class = 'exp-available available';
                    $resv_start=1;
                    if($resv_end===1){
                        $resv_class=' reservation_end ';
                        $resv_end=0;
                    }
                    $day_status = '<span class="day-status">'.esc_attr($homey_local['avail_label']).'</span>';
                }
                $day_pic = '';

            }

            if($day_status_li != ''){
                $day_status = '<ul class="booked-reservations-list">'.$day_status_li.'</ul>';
            }

            if ( $day == gmdate('j', current_time('timestamp')) && $currentMonth == gmdate('m', current_time('timestamp')) && $currentYear == gmdate('Y', current_time('timestamp')) ) {

                $monthDays .= '<li data-formatted-date="'.esc_attr($currentYear).'-'.esc_attr($currentMonth).'-'.esc_attr($day).'" class="current-month '.esc_attr($resv_class).' '.esc_attr($calendar_day_class).' '.esc_attr($dayClass).'">
                <span class="day-number current-day">'.esc_attr($day).'</span>'.$day_status.'</li>';

            } else {

                $monthDays .= '<li data-formatted-date="'.esc_attr($currentYear).'-'.esc_attr($currentMonth).'-'.esc_attr($day).'" class="current-month '.esc_attr($resv_class).' '.esc_attr($calendar_day_class).' '.esc_attr($dayClass).'">
                    <span class="day-number">'.esc_attr($day).'</span>
                    '.$day_pic.'
                    '.$day_status.'
                </li>';
            }

        }

        $output = '<div class="homey_month_wrap" data-month = "'.esc_attr($numberOfMonths).'" '.$style.'>';

            $output .= '<div class="month clearfix">';

                $output .= '<h4>'.date_i18n("F", mktime(0, 0, 0, $currentMonth, 10)).'<br>';
                    $output .= '<span>'.esc_attr($currentYear).'</span>';
                $output .= '</h4>';


            $output .= '</div>'; // end month

            $output .= '<div class="available-text">'.$homey_local['make_available_text'].'</div>';

            $output .= '<ul class="weekdays clearfix">';
                $output .= $weekDays;
            $output .= '</ul>';

            $output .= '<ul class="days clearfix">';
                $output .= $prevMonthDays;

                $output .= $monthDays;
            $output .= '</ul>';

        $output .= '</div>'; // end homey_month_wrap div

        echo ''.$output;

    } //homeyGenerateMonth
} // function_exists
?>

<div id="calendar-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
            <h3 class="title"><?php echo esc_attr($homey_local['cal_label']); ?></h3>
    </div>
    <div class="block-body">
        <div class="calendar-navigation custom-actions">
            <div class="btn-group">
                <button type="button" class="btn btn-secondary-outlined btn-reserve-period dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php echo esc_attr($homey_local['sync_ical_label']); ?> <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="#" data-toggle="modal" data-target="#modal-calendar-import"><?php echo esc_attr($homey_local['ical_import']); ?></a></li>
                    <li><a href="#" data-toggle="modal" data-target="#modal-calendar-export"><?php echo esc_attr($homey_local['ical_export']); ?></a></li>
                </ul>
            </div>

            <a class="btn btn-secondary-outlined btn-reserve-period" data-toggle="modal" data-target="#modal-calendar"><?php echo esc_attr($homey_local['reserve_period_label']); ?></a>
            <a class="btn btn-action btn-reserve-period-mobile" data-toggle="modal" data-target="#modal-calendar"><i class="homey-icon homey-icon homey-icon-cog-double-2-interface-essential" aria-hidden="true"></i></a>

            <button class="homey-prev-month btn btn-action disabled"><i class="homey-icon homey-icon-arrow-left-1" aria-hidden="true"></i></button>
            <button class="homey-next-month btn btn-action"><i class="homey-icon homey-icon-arrow-right-1" aria-hidden="true"></i></button>
        </div>

        <div id="property-calendar" class="property-calendar experience-calendar">

            <?php echo homeyEditExperienceCalendar(); ?>

        </div>
    </div>
</div>
<?php get_template_part('template-parts/dashboard/edit-experience/modal-calendar'); ?>
<?php get_template_part('template-parts/dashboard/ical-import-exp'); ?>
<?php get_template_part('template-parts/dashboard/ical-export-exp'); ?>
