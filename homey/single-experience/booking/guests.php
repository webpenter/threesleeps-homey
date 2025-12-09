<?php
global $homey_local;
$booking_hide_fields = homey_option('booking_hide_fields');
$prefilled = homey_get_dates_for_booking();
$guest_val = $prefilled['guest'];
if($guest_val > 0) {
	$guest_val = $guest_val;
	$guest_val2 = $guest_val;
} else {
	$guest_val = '0';
	$guest_val2 = '';
}

if($booking_hide_fields['guests'] != 1) {
?>
<div class="search-guests exp-single-guests-js">
	<input name="exp_guests" value="<?php echo esc_attr($guest_val2); ?>" readonly type="text" class="form-control" autocomplete="off" placeholder="<?php echo esc_attr(homey_option('srh_guests_label')); ?>">
	<input type="hidden" name="exp_adult_guest" value="<?php echo esc_attr($guest_val); ?>">
	<input type="hidden" name="exp_child_guest" value="0">
	<div class="search-guests-wrap exp-single-form-guests-js clearfix">
	
		<div class="adults-calculator">
			<span class="quantity-calculator exp_homey_adult"><?php echo esc_attr($guest_val); ?></span>
			<span class="calculator-label"><?php echo esc_attr(homey_option('srh_guests_label_exp')); ?></span>
			<button class="exp_adult_plus btn btn-secondary-outlined" type="button"><i class="homey-icon homey-icon-add" aria-hidden="true"></i></button>
			<button class="exp_adult_minus btn btn-secondary-outlined" type="button"><i class="homey-icon homey-icon-subtract" aria-hidden="true"></i></button>
		</div>

		<div class="guest-apply-btn">
			<button class="btn apply_exp_guests btn-primary" type="button"><?php echo esc_html__(esc_attr($homey_local['sr_apply_label']),'homey'); ?></button>
		</div><!-- guest-apply-btn -->
	</div><!-- search-guests -->
</div>
<?php } ?>