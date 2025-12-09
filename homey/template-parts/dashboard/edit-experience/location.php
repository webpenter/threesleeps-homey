<?php
global $homey_local, $experience_data, $experience_meta_data;
//echo '<pre> dilber '; print_r($experience_meta_data);
$hide_fields = homey_option('experience_add_hide_fields');

$experience_id = $experience_data->ID;
$geo_country_limit = homey_option('geo_country_limit');
$geocomplete_country = '';
if( $geo_country_limit != 0 ) {
    $geocomplete_country = homey_option('geocomplete_country');
}
$add_location_lat = homey_get_field_meta('geolocation_lat');
$add_location_long = homey_get_field_meta('geolocation_long');

if( empty($add_location_lat) ) {
    $add_location_lat = homey_option('add_location_lat');
}

if( empty($add_location_long) ) {
    $add_location_long = homey_option('add_location_long');
}
//echo '<pre>'; print_r($experience_meta_data);
$class = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'location') {
    $class = 'in active';
}
?>

<div id="location-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
        <h3 class="title"><?php echo esc_attr(homey_option('experience_ad_location')); ?></h3>
    </div>
    <div class="block-body">

        <div class="row">
            <?php if($hide_fields['experience_address'] != 1) { ?>
            <div class="col-sm-8">
                <div class="form-group">
                    <label for="experience_address"><?php echo esc_attr(homey_option('experience_ad_address')).homey_req('experience_address'); ?></label>
                    <input type="text" autocomplete="false" name="experience_address" <?php homey_required('experience_address'); ?> class="form-control" value="<?php echo isset($experience_meta_data['homey_experience_address'][0]) ? $experience_meta_data['homey_experience_address'][0] : '' ; ?>" id="experience_address" placeholder="<?php echo esc_attr(homey_option('ad_experience_address_placeholder')); ?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['experience_aptSuit'] != 1) { ?>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="aptSuit"> <?php echo esc_attr(homey_option('experience_ad_aptSuit')).homey_req('experience_aptSuit'); ?> </label>
                    <input type="text" autocomplete="false" name="aptSuit" <?php homey_required('experience_aptSuit'); ?> class="form-control" value="<?php echo isset($experience_meta_data['homey_aptSuit'][0]) ? $experience_meta_data['homey_aptSuit'][0] : ''; ?>" id="aptSuit" placeholder="<?php echo esc_attr(homey_option('experience_ad_aptSuit_placeholder')); ?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['experience_city'] != 1) { ?>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="city"><?php echo esc_attr(homey_option('experience_ad_city')).homey_req('experience_city'); ?></label>
                    <input type="text" autocomplete="false" name="locality" <?php homey_required('experience_city'); ?> value="<?php echo homey_get_taxonomy_title($experience_id, 'experience_city'); ?>" id="city" class="form-control" placeholder="<?php echo esc_attr(homey_option('experience_ad_city_placeholder')); ?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['experience_state'] != 1) { ?>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="state"><?php echo esc_attr(homey_option('experience_ad_state')).homey_req('experience_state'); ?></label>
                    <input type="text" autocomplete="false" name="administrative_area_level_1" <?php homey_required('experience_state'); ?> value="<?php echo homey_get_taxonomy_title($experience_id, 'experience_state'); ?>" id="countyState"  class="form-control" id="state" placeholder="<?php echo esc_attr(homey_option('experience_ad_state_placeholder')); ?>">

                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['experience_zipcode'] != 1) { ?>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="zip"><?php echo esc_attr(homey_option('experience_ad_zipcode')).homey_req('experience_zip'); ?></label>
                    <input type="text" autocomplete="false" name="zip" <?php homey_required('experience_zip'); ?> class="form-control" value="<?php echo $experience_meta_data['homey_zip'][0]; ?>"  id="zip" placeholder="<?php echo esc_attr(homey_option('experience_ad_zipcode_placeholder')); ?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['experience_area'] != 1) { ?>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="neighborhood"><?php echo esc_attr(homey_option('experience_ad_area')).homey_req('experience_area'); ?></label>
                    <input class="form-control" autocomplete="false" name="neighborhood" <?php homey_required('experience_area'); ?> value="<?php echo homey_get_taxonomy_title($experience_id, 'experience_area'); ?>" id="area" placeholder="<?php echo esc_attr(homey_option('experience_ad_area_placeholder')); ?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['experience_country'] != 1) { ?>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="country"><?php echo esc_attr(homey_option('experience_ad_country')).homey_req('experience_country'); ?></label>
                    <input class="form-control" autocomplete="false" name="country" <?php homey_required('experience_country'); ?> value="<?php echo homey_get_taxonomy_title($experience_id, 'experience_country'); ?>" id="homey_country" placeholder="<?php echo esc_attr(homey_option('experience_ad_country_placeholder')); ?>">
                    <input name="country_short" type="hidden" value="">
                </div>
            </div>
            <?php } ?>
            
        </div>
        <div id="homey_edit_map" class="row add-experience-map">
            <div class="col-sm-12">
                <div class="form-group">
                    <label><?php echo esc_attr(homey_option('experience_ad_drag_pin')); ?></label>
                    <div class="map_canvas" data-add-lat="<?php echo esc_attr($add_location_lat); ?>" data-add-long="<?php echo esc_attr($add_location_long); ?>" id="map">
                    </div>
                </div>
            </div>
        </div>
        <div class="row add-experience-map">
            <div class="col-sm-12">
                <label><?php echo esc_attr(homey_option('experience_ad_find_address')); ?></label>
            </div>
            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <div class="form-group">
                    <input type="text" name="lat" id="lat" value="<?php echo $experience_meta_data['homey_geolocation_lat'][0]; ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('experience_ad_lat')); ?>">
                </div>
            </div>
            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <div class="form-group">
                    <input type="text" name="lng" id="lng" value="<?php echo $experience_meta_data['homey_geolocation_long'][0]; ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('experience_ad_long')); ?>">
                </div>
            </div>
            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <span id="find" class="btn btn-primary btn-full-width"><?php echo esc_attr(homey_option('experience_ad_find_address_btn')); ?></span>
            </div>
        </div>
        
    </div>
</div>
