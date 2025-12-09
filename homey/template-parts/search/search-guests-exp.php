<?php
global $homey_local;
$search_fields = homey_option('search_hide_fields_exp');

$adults = isset($search_fields['adults']) ? $search_fields['adults'] : 0;
?>
<div class="search-guests-wrap search-guests-wrap-js clearfix">
	<input type="hidden" name="adult_guest" class="search_adult_guest" value="<?php echo isset($_GET['adult_guest']) ? (int) $_GET['adult_guest'] : 0 ?>">
	<input type="hidden" name="child_guest" class="search_child_guest" value="<?php echo isset($_GET['child_guest']) ? (int) $_GET['child_guest'] : 0 ?>">

	<?php if($adults != 1) { ?>
	<div class="adults-calculator">
		<span class="quantity-calculator search_homey_adult"><?php echo isset($_GET['adult_guest']) ? (int) $_GET['adult_guest'] : 0 ?></span>
		<span class="calculator-label"><?php echo esc_attr(homey_option('srh_guests_label_exp')); ?></span>
		<button class="search_adult_plus btn btn-secondary-outlined" type="button"><i class="homey-icon homey-icon-add" aria-hidden="true"></i></button>
		<button class="search_adult_minus btn btn-secondary-outlined" type="button"><i class="homey-icon homey-icon-subtract" aria-hidden="true"></i></button>
	</div>
	<?php } ?>

	<div class="guest-apply-btn">
		<button class="btn btn-primary" type="button"><?php echo esc_html__(esc_attr($homey_local['sr_apply_label']), 'homey'); ?></button>
	</div><!-- guest-apply-btn -->
</div><!-- search-guests -->