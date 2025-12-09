<?php
global $post, $current_user, $homey_prefix, $homey_local, $experience_id;
wp_get_current_user();

$experience_id = $post->ID;
$price_per_night = get_post_meta($experience_id, $homey_prefix.'night_price', true);
$instant_booking = get_post_meta($experience_id, $homey_prefix.'instant_booking', true);

$offsite_payment = homey_option('off-site-payment');

$prefilled = homey_get_dates_for_booking();

$key = '';
$userID      =   $current_user->ID;
$fav_option = 'homey_favorites-'.$userID;
$fav_option = get_option( $fav_option );
if( !empty($fav_option) ) {
    $key = array_search($post->ID, $fav_option);
}

$price_separator = homey_option('currency_separator');

if( $key != false || $key != '' ) {
    $favorite = $homey_local['remove_favorite'];
    $heart = 'homey-icon-love-it-full-01';
} else {
    $favorite = $homey_local['add_favorite'];
    $heart = 'homey-icon-love-it';
}
$experience_price = homey_exp_get_price();

$no_login_needed_for_exp_booking = homey_option('no_login_needed_for_exp_booking');
?>
<div id="homey_remove_on_mobile" class="sidebar-booking-module hidden-sm hidden-xs">
	<div class="block">
		<div class="sidebar-booking-module-header">
			<div class="block-body-sidebar">

					<?php
					if(!empty($experience_price)) { ?>

					<span class="item-price">
					<?php
					echo homey_formatted_price($experience_price, false, true); ?><sub><?php echo homey_exp_get_price_label();?></sub>
					</span>

					<?php } else {
						echo '<span class="item-price free">'.esc_html__('Free', 'homey').'</span>';
					}?>

			</div><!-- block-body-sidebar -->
		</div><!-- sidebar-booking-module-header -->
		<div class="sidebar-booking-module-body">
			<div class="homey_notification block-body-sidebar">

				<?php
				if( homey_affiliate_booking_link() ) { ?>

					<a href="<?php echo homey_affiliate_booking_link(); ?>" target="_blank" class="btn btn-full-width btn-primary"><?php echo esc_html__('Book Now', 'homey'); ?></a>

				<?php
				} else { ?>
					<div id="single-experience-date-range" class="search-date-range">
						<div class="search-date-range-arrive" style="width: 100%;">
							<input name="arrive" value="<?php echo esc_attr($prefilled['arrive']); ?>" readonly type="text" class="form-control check_in_date" autocomplete="off" placeholder="<?php echo esc_attr(homey_option('srh_arrive_label_exp')); ?>">
						</div>

                        <?php if('currently-no-need' == 'starting-experiences-feature') { ?>
                            <div class="search-date-range-depart">
                                <input name="depart" value="<?php echo esc_attr($prefilled['depart']); ?>" readonly type="text" class="form-control check_out_date" autocomplete="off" placeholder="<?php echo esc_attr(homey_option('srh_depart_label')); ?>">
                            </div>
						<?php } ?>

						<div id="single-booking-search-calendar" class="search-calendar search-calendar-single clearfix single-experience-booking-calendar-js clearfix" style="display: none;">
							<?php
							$booking_type = homey_booking_type_by_id($experience_id);
							if($booking_type == 'per_day_date'){ // for now it is same, but we will change.
                                expAvailabilityCalendar();
							}else{
								expAvailabilityCalendar();
							}?>

							<div class="calendar-navigation custom-actions">
		                        <button class="experience-cal-prev btn btn-action pull-left disabled"><i class="homey-icon homey-icon-arrow-left-1" aria-hidden="true"></i></button>
		                        <button class="experience-cal-next btn btn-action pull-right"><i class="homey-icon homey-icon-arrow-right-1" aria-hidden="true"></i></button>
		                    </div><!-- calendar-navigation -->
						</div>
					</div>

					<?php get_template_part('single-experience/booking/guests'); ?>

					<?php get_template_part('single-experience/booking/extra-prices'); ?>

					<?php if( $offsite_payment == 0 ) { ?>
					<div class="search-message">
						<textarea name="exp_guest_message" class="form-control" rows="3" placeholder="<?php echo esc_html__('Introduce yourself to the host', 'homey'); ?>"></textarea>
					</div>
					<?php } ?>

					<div class="homey_preloader">
						<?php get_template_part('template-parts/spinner'); ?>
					</div>
					<div id="homey_booking_cost" class="payment-list"></div>

					<input type="hidden" name="experience_id" id="experience_id" value="<?php echo intval($experience_id); ?>">
					<input type="hidden" name="reservation-security" id="reservation-security" value="<?php echo wp_create_nonce('reservation-security-nonce'); ?>"/>

                    <?php if(!is_user_logged_in() && $no_login_needed_for_exp_booking == 'yes'){ ?>
                        <div class="new_reser_request_user_email ">
                            <input id="new_reser_exp_request_user_email" name="new_reser_request_user_email" required="required" value="<?php echo esc_attr($prefilled['new_reser_request_user_email']); ?>" type="email" class="form-control new_reser_request_user_email" placeholder="<?php echo esc_html__('Your email', 'homey'); ?>">
                        </div>
                    <?php } ?>

					<?php if($instant_booking && $offsite_payment == 0 ) { ?>
						<button id="instance_exp_reservation" type="button" class="btn btn-full-width btn-primary"><?php echo esc_html__('Instant Booking', 'homey'); ?></button>
					<?php } else { ?>
						<button id="request_for_exp_reservation" type="button" class="btn btn-full-width btn-primary"><?php echo esc_html__('Request to Book', 'homey'); ?></button>
					<?php } ?>
                    <div class="text-center text-small"><i class="homey-icon homey-icon-information-circle"></i> <span id="available_slots_by_date_text"><?php echo esc_html__("Select date to check available slots.", 'homey'); ?></span></div>
                <?php } ?>

			</div><!-- block-body-sidebar -->
		</div><!-- sidebar-booking-module-body -->

	</div><!-- block -->
</div><!-- sidebar-booking-module -->
<div class="sidebar-booking-module-footer">
	<div class="block-body-sidebar">

		<?php if(homey_option('experience_detail_favorite') != 0) { ?>
		<button type="button" data-exp-id="<?php echo intval($post->ID); ?>" class="add_exp_fav btn btn-full-width btn-grey-outlined"><i class="homey-icon <?php echo esc_attr($heart); ?>" aria-hidden="true"></i> <?php echo esc_attr($favorite); ?></button>
		<?php } ?>

		<?php if(homey_option('experience_detail_contact_form') != 0 ) { ?>
		<button type="button" data-toggle="modal" data-target="#modal-contact-host" class="btn btn-full-width btn-grey-outlined"><?php echo esc_attr($homey_local['pr_cont_host']); ?></button>
		<?php } ?>

		<?php if(homey_option('experience_print_button') != 0) { ?>
		<button type="button" id="homey-print-experience" class="homey-print-experience btn btn-full-width btn-blank" data-experience-id="<?php echo intval($experience_id);?>">
			<i class="homey-icon homey-icon-print-text" aria-hidden="true"></i> <?php echo esc_attr($homey_local['print_label']); ?>
		</button>
		<?php } ?>
	</div><!-- block-body-sidebar -->

	<?php
	if(homey_option('experience_detail_share') != 0) {
		get_template_part('single-experience/share');
	}
	?>
</div><!-- sidebar-booking-module-footer -->