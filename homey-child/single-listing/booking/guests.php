<?php
global $homey_local;
$booking_hide_fields = homey_option('booking_hide_fields');
$prefilled = homey_get_dates_for_booking();
$guest_val = $prefilled['guest'];
$adult_guest_val = $prefilled['adult_guest'];
$child_guest_val = $prefilled['child_guest'];
if($guest_val > 0) {
	$guest_val = $guest_val;
	$adult_guest_val = $adult_guest_val;
	$child_guest_val = $child_guest_val;
} else {
	$guest_val = 0;
	$adult_guest_val = 0;
	$child_guest_val = 0;
}

if($adult_guest_val > 0) {
	$adult_guest_val = $adult_guest_val;
} else {
	$adult_guest_val = 0;
}

if($child_guest_val > 0) {
	$child_guest_val = $child_guest_val;
} else {
	$child_guest_val = 0;
}

if( empty( $adult_guest_val ) ) {
	$adult_guest_val = $guest_val;
}

if( $guest_val == 0 ) {
	$guest_val = '';
}

if($booking_hide_fields['guests'] != 1) {
?>
<div class="search-guests single-guests-js">
	<input name="guests" value="<?php echo esc_attr($guest_val); ?>" readonly type="text" class="form-control" autocomplete="off" placeholder="<?php echo esc_attr(homey_option('srh_guests_label')); ?>">
	<input type="hidden" name="adult_guest" value="<?php echo esc_attr($adult_guest_val); ?>">
	<input type="hidden" name="child_guest" value="<?php echo esc_attr($child_guest_val); ?>">
	<div class="search-guests-wrap single-form-guests-js clearfix">
	
		<div class="adults-calculator">
			<span class="quantity-calculator homey_adult"><?php echo esc_attr($adult_guest_val); ?></span>
			<span class="calculator-label"><?php echo esc_attr(homey_option('srh_adults_label')); ?></span>
			<button class="adult_plus btn btn-secondary-outlined" type="button"><i class="homey-icon homey-icon-add" aria-hidden="true"></i></button>
			<button class="adult_minus btn btn-secondary-outlined" type="button"><i class="homey-icon homey-icon-subtract" aria-hidden="true"></i></button>
		</div>

		<?php if($booking_hide_fields['children'] != 1) { ?>
		<div class="children-calculator">
			<span class="quantity-calculator homey_child"><?php echo esc_attr( $child_guest_val )?></span>
			<span class="calculator-label"><?php echo esc_attr(homey_option('srh_child_label')); ?></span>
			<button class="child_plus btn btn-secondary-outlined" type="button"><i class="homey-icon homey-icon-add" aria-hidden="true"></i></button>
			<button class="child_minus btn btn-secondary-outlined" type="button"><i class="homey-icon homey-icon-subtract" aria-hidden="true"></i></button>
		</div>
		<?php } ?>
		<div class="guest-apply-btn">
			<button class="btn apply_guests btn-primary" type="button"><?php echo esc_html__(esc_attr($homey_local['sr_apply_label']),'homey'); ?></button>
		</div><!-- guest-apply-btn -->
	</div><!-- search-guests -->
</div>
<?php } ?>