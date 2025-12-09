<?php
/**
 * Template Name: iCalendar Feeds Experience
 */
if( !isset($_GET['iCal'])){
    wp_die('Oooops.. Something went wrong');
}

$allowed_html = array();
$eol = PHP_EOL;
$ical_prod_id          = sprintf('-//%s//iCal Event Maker', get_bloginfo('name'));
$ical_refresh_interval = 'P30M';
$ical_timezone_string  = wp_timezone_string();

$iCal_id = sanitize_text_field(wp_kses($_GET['iCal'], $allowed_html));
$experience_id = homey_get_experience_id_by_ical_id_exp($iCal_id);

$slug      = get_post_field('post_name', $experience_id);
$title     = get_post_field('post_title', $experience_id);
$title     = sprintf('%s - %s', $title, __('Bookings', 'homey_core', 'homey'));
$permalink = get_permalink($experience_id);

/**
 * Standard iCal head data
 * Includes calendar name, refresh interval (30m by default), calendar scale
 *
 * X-WR-CALNAME: https://docs.microsoft.com/en-us/openspecs/exchange_server_protocols/ms-oxcical/1da58449-b97e-46bd-b018-a1ce576f3e6d
 */

$ics_data = "BEGIN:VCALENDAR".$eol;
$ics_data .= "VERSION:2.0".$eol;
$ics_data .= "PRODID:".$ical_prod_id . $eol;
$ics_data .= "X-WR-CALNAME:".$title . $eol;
$ics_data .= "X-HOMEY-LISTING-URL:".$permalink . $eol;
$ics_data .= "NAME:iCal:".$title . $eol;
$ics_data .= "REFRESH-INTERVAL;VALUE=DURATION:".$ical_refresh_interval . $eol;
$ics_data .= "CALSCALE:GREGORIAN".$eol;
$ics_data .= "X-WR-TIMEZONE". $ical_timezone_string . $eol;
$ics_data .= "METHOD:PUBLISH".$eol;
$ics_data .= homey_get_booked_dates_for_icalendar_exp($experience_id);
$ics_data .= homey_get_unavailable_dates_for_icalendar_exp($experience_id);
$ics_data .= "END:VCALENDAR";

header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: inline; filename=icalendar-exp.ics');
print ''.$ics_data;
exit();