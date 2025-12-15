<?php
/**
 * Template Name: iCalendar Feeds multi
 */

if (!isset($_GET['iCal'])) { 
    wp_die('Oooops.. Something went wrong');
}

$ical_id_raw = sanitize_text_field($_GET['iCal']);

// Extract room_id if exists
if (strpos($ical_id_raw, '_room_') !== false) {
    list($ical_id, $room_id) = explode('_room_', $ical_id_raw);
} else {
    $ical_id = $ical_id_raw;
    $room_id = null;
}

// Get listing by iCal ID
$listing_id = homey_get_listing_id_by_ical_id($ical_id);

$slug      = get_post_field('post_name', $listing_id);
$title     = get_post_field('post_title', $listing_id);
$title     = sprintf('%s - %s', $title, __('Bookings', 'homey_core', 'homey'));
$permalink = get_permalink($listing_id);

$eol = "\r\n";
$ical_prod_id = sprintf('-//%s//iCal Event Maker', get_bloginfo('name'));
$ical_refresh_interval = 'PT30M';
$ical_timezone_string = wp_timezone_string();
$timezone = new DateTimeZone('UTC');

$ics_data = "BEGIN:VCALENDAR".$eol;
$ics_data .= "VERSION:2.0".$eol;
$ics_data .= "PRODID:".$ical_prod_id . $eol;
$ics_data .= "X-WR-CALNAME:".$title . $eol;
$ics_data .= "X-HOMEY-LISTING-URL:".$permalink . $eol;
$ics_data .= "NAME:iCal:".$title . $eol;
$ics_data .= "REFRESH-INTERVAL;VALUE=DURATION:".$ical_refresh_interval . $eol;
$ics_data .= "CALSCALE:GREGORIAN".$eol;
$ics_data .= "X-WR-TIMEZONE:". $timezone->getName() . $eol;
$ics_data .= "METHOD:PUBLISH".$eol;

// ✅ Use the multi-room function if room_id exists
if (!empty($room_id)) {
    $ics_data .= homey_get_booked_dates_for_icalendar_multi($listing_id, $room_id);
} else {
    $ics_data .= homey_get_booked_dates_for_icalendar($listing_id);
}

$ics_data .= "END:VCALENDAR";

header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: inline; filename=icalendar.ics');
echo $ics_data;
exit();
