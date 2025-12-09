<?php
global $post, $homey_prefix, $homey_local, $hide_labels;
$exp_prefix = 'experience_';
$min_book_days  = homey_get_experience_data('min_book_days');
$max_book_days  = homey_get_experience_data('max_book_days');

if($min_book_days > 1) {
    $min_book_days_label = homey_option('experience_sn_nights_label');
} else {
    $min_book_days_label = homey_option('experience_sn_night_label');
}
?>
<div id="availability-section" class="availability-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title"><?php echo esc_attr(homey_option($exp_prefix.'sn_availability_label')); ?></h3>
                </div><!-- block-left -->
            </div><!-- block-body -->
            <div class="block-availability-calendars">
                <div class="single-experience-calendar search-calendar clearfix">

                    <div class="calendar-arrow"></div>

                    <?php expAvailabilityCalendar(0); ?>

                    <div class="availability-notes">
                        <ul class="list-inline">
                            <li class="day-available">
                                <span><?php echo esc_attr(homey_option($exp_prefix.'sn_avail_label')); ?></span>
                            </li>
                            <li class="day-pending">
                                <span><?php echo esc_attr(homey_option($exp_prefix.'sn_pending_label')); ?></span>
                            </li>
                            <li class="day-booked">
                                <span><?php echo esc_attr(homey_option($exp_prefix.'sn_booked_label')); ?></span>
                            </li>
                        </ul>
                    </div>

                    <div class="calendar-navigation custom-actions">
                        <button class="experience-cal-prev btn btn-action pull-left disabled"><i class="homey-icon homey-icon-arrow-left-1" aria-hidden="true"></i></button>
                        <button class="experience-cal-next btn btn-action pull-right"><i class="homey-icon homey-icon-arrow-right-1" aria-hidden="true"></i></button>
                    </div><!-- calendar-navigation -->

                </div>
            </div><!-- block-availability-calendars -->
        </div><!-- block-section -->
    </div><!-- block -->
</div>
