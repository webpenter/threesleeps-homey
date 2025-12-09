<?php 
global $homey_local;
$compared = isset($_COOKIE['homey_compare_experiences']) ? $_COOKIE['homey_compare_experiences'] : '';
$ids = explode(',', $compared);
?>
<div id="compare-exp-property" class="">
	<h2 class="title"><?php echo esc_html__('Compare experiences', 'homey'); ?></h2>

	<div class="compare-exp-wrap">
	<?php 
	if(!empty($ids[0])) {
	foreach($ids as $id ) { ?>
		<div class="compare-item remove-<?php echo intval($id); ?>">
			<a href="javascript:void(0);" class="remove-compare-exp remove-icon" data-experience_id="<?php echo intval($id); ?>"><i class="homey-icon homey-icon-bin-1-interface-essential" aria-hidden="true"></i></a>
			<img class="img-responsive" src="<?php echo get_the_post_thumbnail_url($id, 'homey-listing-thumb'); ?>" width="450" height="300" alt="<?php esc_attr_e('Thumb', 'homey'); ?>">
		</div>
	<?php } 
	}?>
	</div>

	<a class="compare-exp-btn btn btn-primary btn-full-width" href="javascript:void(0);" ><?php echo esc_attr($homey_local['compare_exp_label']); ?></a>
</div>