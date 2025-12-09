<?php
global $post, $homey_local, $homey_prefix;
$search_hide_fields = homey_option('search_hide_fields_exp');
?>
<div data-searh-for="exp" class="search-filter">
	<div data-searh-for="exp" class="search-filter-wrap">

        <?php if($search_hide_fields['search_price'] != 1) { ?>
		<div data-searh-for="exp" class="filters-wrap">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
					<div class="filters">
						<strong><?php echo esc_html__(esc_attr(homey_option('srh_price_exp')), 'homey'); ?></strong>
					</div>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
					<select name="min-price" class="selectpicker" data-live-search="true" data-live-search-style="begins" title="<?php echo esc_html__(esc_attr($homey_local['search_min']),'homey'); ?>">
						<?php homey_adv_searches_min_price(); ?>
					</select>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
					<select name="max-price" class="selectpicker" data-live-search="true" data-live-search-style="begins" title="<?php echo esc_html__(esc_attr($homey_local['search_max']), 'homey'); ?>">
						<?php homey_adv_searches_max_price(); ?>
					</select>
				</div>
			</div>
		</div><!-- .filters-wrap -->
		<?php } ?>


		<?php 
		if($search_hide_fields['search_amenities'] != 1) {
			get_template_part('template-parts/search/amenities-exp');
		}
		?>

		<?php 
		if($search_hide_fields['search_facilities'] != 1) {
			get_template_part('template-parts/search/facilities-exp');
		}
		?>

		<?php
		if(@$search_hide_fields['search_host_languages'] != 1 || 1==1) {
			get_template_part('template-parts/search/host-languages-exp');
		}
		?>

		<div class="search-filter-footer text-right">
			<button type="submit" class="btn btn btn-grey-outlined search-reset-btn"><?php echo esc_attr($homey_local['search_reset']); ?></button>
			<button type="submit" class="btn btn-primary search-apply-filters homey_half_map_exp_search_btn"><?php echo esc_attr($homey_local['search_apply']); ?></button>
		</div><!-- .search-filter-footer -->

	</div><!-- .search-filter-wrap -->
	
</div><!-- search-filter -->