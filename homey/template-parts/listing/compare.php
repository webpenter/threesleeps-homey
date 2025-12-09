<?php 
global $homey_local;
$compared = isset($_COOKIE['homey_compare_listings']) ? $_COOKIE['homey_compare_listings'] : '';
$ids = explode(',', $compared);

if(!isset($_COOKIE['homey_compare_listings'])){
    $ids = isset($_GET['ids']) ? explode(',', $_GET['ids']) : '';
}

?>
<div id="compare-property-panel" class="compare-property-panel compare-property-panel-vertical compare-property-panel-right">
	<button class="compare-property-label" style="display: none;">
		<span class="compare-count"></span>
		<i class="homey-icon homey-icon-compare-listings" aria-hidden="true"></i>
	</button>
	<h2 class="title"><?php echo esc_html__('Compare listings', 'homey'); ?></h2>

	<div class="compare-wrap">
	<?php 
	if(!empty($ids[0])) {
	foreach($ids as $id ) { ?>
		<div class="compare-item remove-<?php echo intval($id); ?>">
			<a href="javascript:void(0);" class="remove-compare remove-icon" data-listing_id="<?php echo intval($id); ?>"><i class="homey-icon homey-icon-bin-1-interface-essential" aria-hidden="true"></i></a>
			<img class="img-responsive" src="<?php echo get_the_post_thumbnail_url($id, 'homey-listing-thumb'); ?>" width="450" height="300" alt="<?php esc_attr_e('Thumb', 'homey'); ?>">
		</div>
	<?php } 
	}?>
	</div>

	<a class="compare-btn btn btn-primary btn-full-width" href="javascript:void(0);" ><?php echo esc_attr($homey_local['compare_label']); ?></a>
    <?php get_template_part('template-parts/experience/compare'); ?>
	<button class="btn btn-grey-outlined btn-full-width close-compare-panel"><?php echo esc_html__('Close', 'homey'); ?></button>
</div>