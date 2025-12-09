<?php
global $post, $homey_local, $homey_prefix;
$advanced_filter = (int) homey_option('advanced_filter_exp');
$search_width = homey_option('search_width_exp');
$sticky_search = homey_option('sticky_search');

$location_search = isset($_GET['location_search']) ? $_GET['location_search'] : '';
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$country = isset($_GET['search_country']) ? $_GET['search_country'] : '';
$state = isset($_GET['search_state']) ? $_GET['search_state'] : '';
$city = isset($_GET['search_city']) ? $_GET['search_city'] : '';
$area = isset($_GET['search_area']) ? $_GET['search_area'] : '';

$arrive = isset($_GET['arrive']) ? $_GET['arrive'] : '';
$guest = isset($_GET['guest']) ? $_GET['guest'] : '';

$lat = isset($_GET['lat']) ? $_GET['lat'] : '';
$lng = isset($_GET['lng']) ? $_GET['lng'] : '';

$class = '';
if($advanced_filter != 1) {
	$class = 'without-filters';
}

$experience_type_pre = '';
if(isset($_GET['experience_type'])) {
	$experience_type_pre = $_GET['experience_type'];
}

$experience_country_pre = '';
if(isset($_GET['country'])) {
    $experience_country_pre = $_GET['country'];
}

$experience_state_pre = '';
if(isset($_GET['state'])) {
    $experience_state_pre = $_GET['state'];
}

$experience_city_pre = '';
if(isset($_GET['city'])) {
    $experience_city_pre = $_GET['city'];
}

$experience_area_pre = '';
if(isset($_GET['area'])) {
	$experience_area_pre = $_GET['area'];
}

$location_field = homey_option('location_field_exp');
if($location_field == 'geo_location') {
    $location_classes = "search-destination search-destination-js";
} elseif($location_field == 'keyword') {
    $location_classes = "search-destination search-destination-js";
} else {
    $location_classes = "search-destination with-select search-destination-js";
}

$radius_class = '';
if( homey_option('enable_radius_exp') && homey_option('show_radius_exp') != 0) {
    $radius_class = 'search-destination-geolocation search-destination-js';
}

$layout_order = homey_option('search_visible_fields_exp');
$layout_order = $layout_order['enabled'];

$total_fields = count($layout_order);
$total_fields = $total_fields - 1;
?>
<div id="homey-main-search" class="side-search-experiences main-search <?php echo esc_attr($class);?>" data-sticky="<?php echo esc_attr( $sticky_search ); ?>">
	<div class="<?php echo esc_attr($search_width); ?>">
		<form class="clearfix" action="<?php echo homey_get_search_result_exp_page(); ?>" method="GET">
			<div id="search-desktop" class="search-wrap hidden-sm hidden-xs">
				
				<?php
                if ($layout_order) { 
                    foreach ($layout_order as $key=>$value) {

                        switch($key) { 
                            case 'location':
                                ?>
                                <div class="<?php echo esc_attr($location_classes).' '.esc_attr($radius_class); ?>">
									<?php if($location_field == 'geo_location') { ?>
		                            <label class="animated-label"><?php echo esc_attr(homey_option('srh_whr_to_go_exp')); ?></label>
		                            <input type="text" name="location_search" autocomplete="off" id="location_search_banner" value="<?php echo esc_attr($location_search); ?>" class="form-control input-search" placeholder="<?php echo esc_attr(homey_option('srh_whr_to_go_exp')); ?>">
		                            <input type="hidden" name="search_city" data-value="<?php echo esc_attr($city); ?>" value="<?php echo esc_attr($city); ?>"> 
		                            <input type="hidden" name="search_area" data-value="<?php echo esc_attr($area); ?>" value="<?php echo esc_attr($area); ?>"> 
		                            <input type="hidden" name="search_country" data-value="<?php echo sanitize_text_field($country); ?>" value="<?php echo esc_attr($country); ?>">
		                            <input type="hidden" name="search_state" data-value="<?php echo sanitize_text_field($state); ?>" value="<?php echo esc_attr($state); ?>">
		                            <input type="hidden" name="lat" value="<?php echo esc_attr($lat); ?>">
                            		<input type="hidden" name="lng" value="<?php echo esc_attr($lng); ?>">

		                            <button type="reset" class="btn clear-input-btn"><i class="homey-icon homey-icon-close" aria-hidden="true"></i></button>

		                            <?php } elseif($location_field == 'keyword') { ?>

		                            	<label class="animated-label"><?php echo esc_attr(homey_option('srh_whr_to_go_exp')); ?></label>
		                            	<input type="text" name="keyword" autocomplete="off" value="<?php echo isset($_GET['keyword']) ? esc_attr($_GET['keyword']) : ''; ?>" class="form-control input-search" placeholder="<?php echo esc_attr(homey_option('srh_whr_to_go_exp')); ?>">

		                            <?php } elseif($location_field == 'country') { ?>

		                            <select name="country" class="selectpicker" data-live-search="true">
		                            <?php
		                            // All Option
		                            echo '<option value="">'.sanitize_text_field(homey_option('srh_whr_to_go_exp')).'</option>';

		                            $experience_country = get_terms (
		                                array(
		                                    "experience_country"
		                                ),
		                                array(
		                                    'orderby' => 'name',
		                                    'order' => 'ASC',
		                                    'hide_empty' => false,
		                                    'parent' => 0
		                                )
		                            );
		                            homey_hirarchical_options('experience_country', $experience_country, $experience_country_pre );
		                            ?>
		                            </select>
		                            
		                            <?php } elseif($location_field == 'state') { ?>

		                            <select name="state" class="selectpicker" data-live-search="true">
		                            <?php
		                            // All Option
		                            echo '<option value="">'.sanitize_text_field(homey_option('srh_whr_to_go_exp')).'</option>';

		                            $experience_state = get_terms (
		                                array(
		                                    "experience_state"
		                                ),
		                                array(
		                                    'orderby' => 'name',
		                                    'order' => 'ASC',
		                                    'hide_empty' => false,
		                                    'parent' => 0
		                                )
		                            );
		                            homey_hirarchical_options('experience_state', $experience_state, $experience_state_pre );
		                            ?>
		                            </select>
		                            
		                            <?php } elseif($location_field == 'city') { ?>

		                            <select name="city" class="selectpicker" data-live-search="true">
		                            <?php
		                            // All Option
		                            echo '<option value="">'.esc_attr(homey_option('srh_whr_to_go_exp')).'</option>';

		                            $experience_city = get_terms (
		                                array(
		                                    "experience_city"
		                                ),
		                                array(
		                                    'orderby' => 'name',
		                                    'order' => 'ASC',
		                                    'hide_empty' => false,
		                                    'parent' => 0
		                                )
		                            );
		                            homey_hirarchical_options('experience_city', $experience_city, $experience_city_pre );
		                            ?>
		                            </select>

		                            <?php } elseif($location_field == 'area') { ?>

		                            <select name="area" class="selectpicker" data-live-search="true">
		                            <?php
		                            // All Option
		                            echo '<option value="">'.esc_attr(homey_option('srh_whr_to_go_exp')).'</option>';

		                            $experience_area = get_terms (
		                                array(
		                                    "experience_area"
		                                ),
		                                array(
		                                    'orderby' => 'name',
		                                    'order' => 'ASC',
		                                    'hide_empty' => false,
		                                    'parent' => 0
		                                )
		                            );
		                            homey_hirarchical_options('experience_area', $experience_area, $experience_area_pre );
		                            ?>
		                            </select>

		                            <?php } ?>
								</div>
                                <?php if( homey_option('enable_radius_exp') && homey_option('show_radius_exp') != 0) { ?>
		                        <div class="search-type search-radius-dropdown">
		                            <select name="radius" data-size="5" class="selectpicker">
		                                <option value=""><?php esc_html_e('Radius','homey');?></option>
		                                <?php
		                                $radius_unit = homey_option('radius_unit_exp', 'km');
		                                $selected_radius = homey_option('default_radius_exp', '30');
		                                if( isset( $_GET['radius'] ) ) {
		                                    $selected_radius = $_GET['radius'];
		                                }
		                                $i = 0;
		                                for( $i = 1; $i <= 100; $i++ ) {
		                                    echo '<option '.selected( $selected_radius, $i, false).' value="'.esc_attr($i).'">'.esc_attr($i).' '.esc_attr($radius_unit).'</option>';
		                                }
		                                ?>
		                            </select>
		                        </div>
		                        <?php
		                        }
                            break;

                            case 'arrive_depart':
                            	?>
                            	<div class="search-date-range main-search-date-range-js">
									<div class="search-date-range-arrive">
										<label class="animated-label"><?php echo esc_attr(homey_option('srh_arrive_label_exp')); ?></label>
										<input name="arrive" autocomplete="off" value="<?php echo esc_attr($arrive); ?>" type="text" class="form-control" placeholder="<?php echo esc_attr(homey_option('srh_arrive_label_exp')); ?>">
									</div>
									<?php get_template_part ('template-parts/search/search-calendar-exp'); ?>
								</div>
                            	<?php
                            break;

                            case 'guests':
                            	?>
                            	<div class="search-guests search-guests-js">
                            		
									<input name="guest" autocomplete="off" value="<?php echo esc_attr($guest); ?>" type="text" class="form-control" placeholder="<?php echo esc_attr(homey_option('srh_guests_label_exp')); ?>">
									<?php get_template_part ('template-parts/search/search-guests-exp'); ?>
								</div>
                            	<?php
                            break;

                            case 'experience_type':
                            	?>
                            	<div class="search-type">
	                            	<select name="experience_type" class="selectpicker" data-live-search="false">
	                                    <?php
	                                    // All Option
	                                    echo '<option value="">'.esc_attr(homey_option('srh_experience_type')).'</option>';

	                                    $experience_type = get_terms (
	                                        array(
	                                            "experience_type"
	                                        ),
	                                        array(
	                                            'orderby' => 'name',
	                                            'order' => 'ASC',
	                                            'hide_empty' => false,
	                                            'parent' => 0
	                                        )
	                                    );
	                                    homey_hirarchical_options('experience_type', $experience_type, $experience_type_pre );
	                                    ?>
	                                </select>
	                            </div>
                            	<?php
                            break;
                     
                        }
                    }
                }
                ?>

				<?php if( $advanced_filter != 0 ) { ?>
				<div class="search-filters">
					<button type="button" class="btn btn-grey-outlined search-filter-btn"><i class="homey-icon homey-icon-settings-slider search-filter-btn-i" aria-hidden="true"></i></button>
				</div>
				<?php } ?>

				<div class="search-button">
					<button type="submit" class="btn btn-primary" style="padding: 0 30px"><?php echo esc_attr($homey_local['search_btn']); ?></button>
				</div>
				
			</div><!-- search-wrap -->

			<div class="search-wrap search-banner-mobile mobile-search-exp-js">
				
				<div class="search-destination search-destination-exp">
					<input sdf value="<?php echo esc_attr($location_search); ?>" type="text" class="form-control" placeholder="<?php echo esc_attr(homey_option('srh_whr_to_go_exp')); ?>" onfocus="blur();">
				</div>
				
			</div><!-- search-wrap -->		
			<?php 
			if( $advanced_filter != 0 ) {
				get_template_part ('template-parts/search/search-filter-full-width-exp');
			}
			?>	
		</form>
	</div>
</div>
