<?php
global $post, $homey_prefix, $homey_local;
$lat     = homey_get_experience_data('geolocation_lat');
$long   = homey_get_experience_data('geolocation_long');
$show_map   = homey_get_experience_data('show_map');
$map_zoom_level = homey_option('singlemap_zoom_level');
$detail_map_pin_type = homey_option('detail_map_pin_type');
$experience_type = wp_get_post_terms($post->ID, 'experience_type', array("fields" => "ids"));

$icon = $retinaIcon = '';
if(!empty($experience_type)) {
	$icon = get_term_meta( $experience_type[0], 'homey_exp_marker_icon', true);
	$retinaIcon = get_term_meta( $experience_type[0], 'homey_exp_marker_retina_icon', true);
}
$marker_pin = wp_get_attachment_image_src($icon, 'full' );
$marker_pin_retina = wp_get_attachment_image_src($retinaIcon, 'full' );

if($show_map) {
?>
<div id="map-section" class="map-section">
    <div class="block">
        <h3 class="title">Map</h3>
        <div id="homey-single-map" 
        data-zoom="<?php echo intval($map_zoom_level); ?>"
        data-pin-type="<?php echo esc_attr($detail_map_pin_type); ?>"
        <?php if(isset($marker_pin[0])){ ?>
             data-marker-pin="<?php echo esc_url($marker_pin[0]); ?>"
        <?php } ?>
        <?php if(isset($marker_pin_retina[0])){ ?>
            data-marker-pin-retina="<?php echo esc_url($marker_pin_retina[0]); ?>"
        <?php } ?>
        data-lat="<?php echo esc_attr($lat);?>"
        data-long="<?php echo esc_attr($long);?>" class="map-section-map">
        </div>
    </div><!-- block -->
</div>
<?php } ?>